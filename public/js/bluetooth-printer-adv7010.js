class BluetoothPrinter {
    constructor() {
        this.device = null;
        this.characteristic = null;
        this.isSupported = this.checkSupport();
    }

    /**
     * Verificar soporte de Web Bluetooth
     */
    checkSupport() {
        if (!('bluetooth' in navigator)) {
            console.warn('Web Bluetooth no soportado en este dispositivo/navegador');
            return false;
        }

        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            console.warn('Web Bluetooth no soportado en iOS');
            return false;
        }

        const isAndroid = /Android/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);
        
        if (isAndroid && (isChrome || isEdge)) {
            console.log('Web Bluetooth soportado - Android Chrome/Edge');
            return true;
        }

        if (!isAndroid && !isIOS && (isChrome || isEdge)) {
            console.log('Web Bluetooth soportado - Desktop Chrome/Edge');
            return true;
        }

        console.warn('Navegador no soporta Web Bluetooth de forma confiable');
        return false;
    }

    /**
     * Conectar a la impresora Bluetooth
     * Mejorado para Advance ADV-7010
     */
    async connect() {
        try {
            console.log('🔍 Buscando impresoras Bluetooth...');
            
            // Solicitar dispositivo Bluetooth con filtros específicos para ADV-7010
            this.device = await navigator.bluetooth.requestDevice({
                // Aceptar TODAS las impresoras Bluetooth disponibles
                acceptAllDevices: true,
                optionalServices: [
                    '00001101-0000-1000-8000-00805f9b34fb', // Serial Port Profile (SPP)
                    '0000180a-0000-1000-8000-00805f9b34fb', // Device Information
                    '0000180f-0000-1000-8000-00805f9b34fb', // Battery Service
                    '000018f0-0000-1000-8000-00805f9b34fb', // Algunos servicios custom
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455'  // Microchip serial service
                ]
            });

            console.log('📱 Dispositivo seleccionado:', this.device.name || 'Sin nombre');

            // Conectar al GATT server
            const server = await this.device.gatt.connect();
            console.log('🔗 Conectado al GATT server');

            // Intentar diferentes métodos de conexión
            let connected = false;

            // Método 1: Intentar Serial Port Profile (SPP) - más común
            try {
                console.log('Intentando SPP...');
                const service = await server.getPrimaryService('00001101-0000-1000-8000-00805f9b34fb');
                const characteristics = await service.getCharacteristics();
                
                for (const char of characteristics) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        this.characteristic = char;
                        console.log('✅ Característica SPP encontrada:', char.uuid);
                        connected = true;
                        break;
                    }
                }
            } catch (e) {
                console.log('SPP no disponible, intentando otros métodos...');
            }

            // Método 2: Buscar en todos los servicios disponibles
            if (!connected) {
                console.log('Buscando en todos los servicios...');
                const services = await server.getPrimaryServices();
                
                for (const service of services) {
                    console.log('Servicio encontrado:', service.uuid);
                    try {
                        const characteristics = await service.getCharacteristics();
                        
                        for (const char of characteristics) {
                            console.log('  Característica:', char.uuid, char.properties);
                            
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                this.characteristic = char;
                                console.log('✅ Característica escribible encontrada:', char.uuid);
                                connected = true;
                                break;
                            }
                        }
                        
                        if (connected) break;
                    } catch (e) {
                        console.log('  No se pudo acceder a este servicio');
                    }
                }
            }

            if (!connected || !this.characteristic) {
                throw new Error('No se encontró característica de escritura en la impresora');
            }

            console.log('✅ ¡Conectado exitosamente a la impresora!');
            console.log('   Nombre:', this.device.name || 'Sin nombre');
            console.log('   ID:', this.device.id);
            
            return true;

        } catch (error) {
            console.error('❌ Error conectando a Bluetooth:', error);
            
            if (error.name === 'NotFoundError') {
                throw new Error('No se encontró ninguna impresora Bluetooth. Asegúrate de que esté encendida y en modo de emparejamiento.');
            } else if (error.name === 'SecurityError') {
                throw new Error('Permisos de Bluetooth denegados. Ve a Configuración de Chrome y activa los permisos.');
            } else if (error.name === 'NotAllowedError') {
                throw new Error('Selección de dispositivo cancelada.');
            } else if (error.name === 'NetworkError') {
                throw new Error('No se pudo conectar a la impresora. Asegúrate de que esté cerca y encendida.');
            } else {
                throw new Error(`Error de conexión: ${error.message}`);
            }
        }
    }

    /**
     * Generar comandos ESC/POS para ticket
     * Compatible con POS-58 Series (según manual oficial)
     */
    generateESCPOSCommands(ticketData) {
        const commands = [];
        
        // Comandos según manual POS-58
        const ESC = 0x1B;  // 27 decimal
        const LF = 0x0A;   // 10 decimal - Print and line feed
        
        // 1. ESC @ - Inicializar impresora (página 19 del manual)
        commands.push(ESC, 0x40);
        
        // 2. ESC a 1 - Centrar texto (página 26 del manual)
        commands.push(ESC, 0x61, 0x01);
        
        // Espacio inicial
        commands.push(LF);
        
        // 3. Título en NEGRITA - ESC E 1 (página 21 del manual)
        commands.push(ESC, 0x45, 0x01); // Negrita ON
        commands.push(...this.stringToBytes('TICKET DE PAGO'));
        commands.push(ESC, 0x45, 0x00); // Negrita OFF
        commands.push(LF, LF);
        
        // 4. Línea separadora
        commands.push(...this.stringToBytes('================================'));
        commands.push(LF, LF);
        
        // 5. ESC a 0 - Alinear a izquierda (página 26 del manual)
        commands.push(ESC, 0x61, 0x00);
        
        // Información del cliente
        commands.push(...this.stringToBytes('CLIENTE:'));
        commands.push(LF);
        commands.push(...this.stringToBytes(ticketData.cliente || 'N/A'));
        commands.push(LF);
        commands.push(...this.stringToBytes('Doc: ' + (ticketData.documento || 'N/A')));
        commands.push(LF, LF);
        
        // 6. Detalles del préstamo
        commands.push(...this.stringToBytes('================================'));
        commands.push(LF);
        commands.push(ESC, 0x45, 0x01); // Negrita ON
        commands.push(...this.stringToBytes('DETALLES DEL PAGO'));
        commands.push(ESC, 0x45, 0x00); // Negrita OFF
        commands.push(LF, LF);
        
        commands.push(...this.stringToBytes('Prestamo:  #' + (ticketData.prestamo_id || 'N/A')));
        commands.push(LF);
        commands.push(...this.stringToBytes('Cuota:     ' + (ticketData.cuota || 'N/A') + '/' + (ticketData.total_cuotas || 'N/A')));
        commands.push(LF);
        commands.push(...this.stringToBytes('Fecha:     ' + (ticketData.fecha || new Date().toLocaleDateString('es-PE'))));
        commands.push(LF, LF);
        
        // 7. Monto destacado (centrado)
        commands.push(ESC, 0x61, 0x01); // Centrar
        commands.push(ESC, 0x45, 0x01); // Negrita ON
        commands.push(...this.stringToBytes('MONTO PAGADO'));
        commands.push(LF);
        commands.push(...this.stringToBytes('S/ ' + (ticketData.monto || '0.00')));
        commands.push(ESC, 0x45, 0x00); // Negrita OFF
        commands.push(LF, LF);
        
        // 8. Saldo restante (izquierda)
        commands.push(ESC, 0x61, 0x00); // Izquierda
        commands.push(...this.stringToBytes('Saldo restante: S/ ' + (ticketData.saldo || '0.00')));
        commands.push(LF, LF);
        
        // 9. Línea final
        commands.push(...this.stringToBytes('================================'));
        commands.push(LF, LF);
        
        // 10. Mensaje de agradecimiento (centrado)
        commands.push(ESC, 0x61, 0x01); // Centrar
        commands.push(...this.stringToBytes('Gracias por su pago'));
        commands.push(LF);
        commands.push(...this.stringToBytes('Conserve este ticket'));
        commands.push(LF, LF, LF);
        
        // 11. ESC d 3 - Feed 3 líneas (página 27 del manual)
        commands.push(ESC, 0x64, 0x03);
        
        return new Uint8Array(commands);
    }

    /**
     * Convertir string a bytes (codificación compatible)
     */
    stringToBytes(str) {
        // Normalizar caracteres especiales del español
        const normalized = str
            .replace(/[áàäâã]/gi, 'a')
            .replace(/[éèëê]/gi, 'e')
            .replace(/[íìïî]/gi, 'i')
            .replace(/[óòöôõ]/gi, 'o')
            .replace(/[úùüû]/gi, 'u')
            .replace(/ñ/gi, 'n');
        
        return Array.from(normalized, char => char.charCodeAt(0));
    }

    /**
     * Imprimir ticket
     */
    async print(ticketData) {
        if (!this.characteristic) {
            throw new Error('No hay conexión con la impresora. Primero debes conectarte.');
        }

        try {
            console.log('📄 Generando ticket...');
            const commands = this.generateESCPOSCommands(ticketData);
            
            console.log('📤 Enviando comandos a impresora...');
            console.log('   Total bytes:', commands.length);
            
            // Según el manual POS-58, enviar en chunks de 64 bytes para mejor compatibilidad
            const chunkSize = 64;
            
            for (let i = 0; i < commands.length; i += chunkSize) {
                const chunk = commands.slice(i, Math.min(i + chunkSize, commands.length));
                
                try {
                    if (this.characteristic.properties.writeWithoutResponse) {
                        await this.characteristic.writeValueWithoutResponse(chunk);
                    } else if (this.characteristic.properties.write) {
                        await this.characteristic.writeValue(chunk);
                    }
                } catch (e) {
                    console.warn('Error enviando chunk en posición', i, ':', e);
                    // Reintentar con chunk más pequeño
                    await new Promise(resolve => setTimeout(resolve, 100));
                    for (let j = 0; j < chunk.length; j++) {
                        await this.characteristic.writeValue(new Uint8Array([chunk[j]]));
                    }
                }
                
                // Pausa entre chunks
                await new Promise(resolve => setTimeout(resolve, 20));
            }
            
            console.log('✅ Ticket enviado correctamente!');
            
            return true;
            
        } catch (error) {
            console.error('❌ Error imprimiendo:', error);
            throw new Error(`Error al imprimir: ${error.message}`);
        }
    }

    /**
     * Imprimir ticket de prueba
     */
    async printTest() {
        const testData = {
            cliente: 'Cliente de Prueba',
            documento: '12345678',
            prestamo_id: '001',
            cuota: '1',
            total_cuotas: '10',
            monto: '150.00',
            saldo: '1350.00',
            fecha: new Date().toLocaleDateString('es-PE')
        };

        return await this.print(testData);
    }

    /**
     * Verificar estado de conexión
     */
    isConnected() {
        return this.device && this.device.gatt && this.device.gatt.connected;
    }

    /**
     * Desconectar
     */
    disconnect() {
        if (this.device && this.device.gatt.connected) {
            this.device.gatt.disconnect();
            console.log('📴 Desconectado de la impresora');
        }
        this.device = null;
        this.characteristic = null;
    }

    /**
     * Obtener información del dispositivo
     */
    getDeviceInfo() {
        const isAndroid = /Android/.test(navigator.userAgent);
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);

        return {
            isAndroid,
            isIOS,
            isChrome,
            isEdge,
            bluetoothSupported: this.isSupported,
            connected: this.isConnected(),
            deviceName: this.device ? this.device.name : null
        };
    }
}

// Instancia global
window.bluetoothPrinter = new BluetoothPrinter();

// Funciones auxiliares para fácil uso
window.connectPrinter = async () => {
    try {
        await window.bluetoothPrinter.connect();
        alert('✅ Conectado a la impresora');
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
};

window.printTestTicket = async () => {
    try {
        await window.bluetoothPrinter.printTest();
        alert('✅ Ticket impreso');
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
};

window.disconnectPrinter = () => {
    window.bluetoothPrinter.disconnect();
    alert('📴 Desconectado');
};

console.log('🖨️ BluetoothPrinter cargado y listo');
console.log('Funciones disponibles:');
console.log('  - connectPrinter()');
console.log('  - printTestTicket()');
console.log('  - disconnectPrinter()');
