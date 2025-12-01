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
        // Verificar si Web Bluetooth está disponible
        if (!('bluetooth' in navigator)) {
            console.warn('Web Bluetooth no soportado en este dispositivo/navegador');
            return false;
        }

        // Detectar iOS (donde nunca funcionará)
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            console.warn('Web Bluetooth no soportado en iOS');
            return false;
        }

        // Detectar Android
        const isAndroid = /Android/.test(navigator.userAgent);
        
        // Verificar si es Chrome o Edge en Android
        const isChrome = /Chrome/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);
        
        if (isAndroid && (isChrome || isEdge)) {
            console.log('Web Bluetooth soportado - Android Chrome/Edge');
            return true;
        }

        // Desktop Chrome/Edge
        if (!isAndroid && !isIOS && (isChrome || isEdge)) {
            console.log('Web Bluetooth soportado - Desktop Chrome/Edge');
            return true;
        }

        console.warn('Navegador no soporta Web Bluetooth de forma confiable');
        return false;
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
            recommendedAction: this.getRecommendedAction()
        };
    }

    /**
     * Obtener acción recomendada según dispositivo
     */
    getRecommendedAction() {
        if (this.isSupported) {
            return 'bluetooth';
        }
        
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        if (isIOS) {
            return 'share'; // PDF para compartir via AirPrint o apps
        }
        
        return 'download'; // Descargar PDF
    }

    /**
     * Conectar a la impresora Bluetooth
     */
    async connect() {
        try {
            // Solicitar dispositivo Bluetooth - Buscar impresoras térmicas
            this.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { namePrefix: "POS" },          // POS-58, POS-80
                    { namePrefix: "Thermal" },      // Thermal Printer
                    { namePrefix: "Receipt" },      // Receipt Printer
                    { namePrefix: "BlueTooth" },    // BlueTooth Printer
                    { namePrefix: "BT" },           // BT Printer
                    { namePrefix: "58mm" },         // 58mm Thermal
                    { namePrefix: "80mm" }          // 80mm Thermal
                ],
                // Usar Serial Port Profile para la mayoría de impresoras térmicas
                optionalServices: [
                    '00001101-0000-1000-8000-00805f9b34fb', // Serial Port Profile (SPP)
                    '0000180a-0000-1000-8000-00805f9b34fb', // Device Information Service
                    '0000180f-0000-1000-8000-00805f9b34fb'  // Battery Service (algunas impresoras)
                ]
            });

            console.log('Dispositivo seleccionado:', this.device.name);

            // Conectar al GATT server
            const server = await this.device.gatt.connect();
            console.log('Conectado al GATT server');

            // Intentar conectar usando Serial Port Profile
            const service = await server.getPrimaryService('00001101-0000-1000-8000-00805f9b34fb');
            console.log('Servicio SPP encontrado');

            // Buscar característica de escritura
            // La mayoría de impresoras usan estas características estándar
            const characteristics = await service.getCharacteristics();
            
            // Buscar característica escribible
            for (const char of characteristics) {
                if (char.properties.write || char.properties.writeWithoutResponse) {
                    this.characteristic = char;
                    console.log('Característica de escritura encontrada:', char.uuid);
                    break;
                }
            }

            if (!this.characteristic) {
                throw new Error('No se encontró característica de escritura en la impresora');
            }

            console.log('✅ Conectado exitosamente a:', this.device.name);
            return true;

        } catch (error) {
            console.error('❌ Error conectando a Bluetooth:', error);
            
            // Errores más específicos según el tipo
            if (error.name === 'NotFoundError') {
                throw new Error('No se encontró ninguna impresora Bluetooth compatible');
            } else if (error.name === 'SecurityError') {
                throw new Error('Permisos de Bluetooth denegados');
            } else if (error.name === 'NotAllowedError') {
                throw new Error('Usuario canceló la selección de dispositivo');
            } else {
                throw new Error(`Error de conexión: ${error.message}`);
            }
        }
    }

    /**
     * Generar comandos ESC/POS para ticket
     */
    generateESCPOSCommands(ticketData) {
        const commands = [];
        
        // Comandos ESC/POS básicos
        const ESC = 0x1B;
        const GS = 0x1D;
        
        // Inicializar impresora
        commands.push(ESC, 0x40);
        
        // Centrar texto
        commands.push(ESC, 0x61, 0x01);
        
        // Título en negrita
        commands.push(ESC, 0x45, 0x01); // Negrita ON
        commands.push(...this.stringToBytes('TICKET DE PAGO'));
        commands.push(0x0A, 0x0A); // Salto de línea doble
        commands.push(ESC, 0x45, 0x00); // Negrita OFF
        
        // Línea divisoria
        commands.push(...this.stringToBytes('--------------------------------'));
        commands.push(0x0A);
        
        // Alinear a izquierda
        commands.push(ESC, 0x61, 0x00);
        
        // Información del ticket
        commands.push(...this.stringToBytes(`Cliente: ${ticketData.cliente}`));
        commands.push(0x0A);
        commands.push(...this.stringToBytes(`Documento: ${ticketData.documento}`));
        commands.push(0x0A, 0x0A);
        
        commands.push(...this.stringToBytes(`Prestamo: #${ticketData.prestamo_id}`));
        commands.push(0x0A);
        commands.push(...this.stringToBytes(`Cuota: ${ticketData.cuota}/${ticketData.total_cuotas}`));
        commands.push(0x0A);
        commands.push(...this.stringToBytes(`Monto: S/ ${ticketData.monto}`));
        commands.push(0x0A);
        commands.push(...this.stringToBytes(`Saldo: S/ ${ticketData.saldo}`));
        commands.push(0x0A);
        commands.push(...this.stringToBytes(`Fecha: ${ticketData.fecha}`));
        commands.push(0x0A, 0x0A);
        
        // Línea divisoria
        commands.push(...this.stringToBytes('--------------------------------'));
        commands.push(0x0A);
        
        // Centrar mensaje final
        commands.push(ESC, 0x61, 0x01);
        commands.push(...this.stringToBytes('Gracias por su pago'));
        commands.push(0x0A, 0x0A, 0x0A);
        
        // Cortar papel
        commands.push(GS, 0x56, 0x00);
        
        return new Uint8Array(commands);
    }

    /**
     * Convertir string a bytes
     */
    stringToBytes(str) {
        return Array.from(str, char => char.charCodeAt(0));
    }

    /**
     * Imprimir ticket
     */
    async print(ticketData) {
        if (!this.characteristic) {
            throw new Error('No hay conexión con la impresora');
        }

        try {
            const commands = this.generateESCPOSCommands(ticketData);
            
            console.log('Enviando comandos a impresora...');
            
            // Enviar comandos en chunks más pequeños para mejor compatibilidad
            const chunkSize = 20; // Enviar en fragmentos de 20 bytes
            
            for (let i = 0; i < commands.length; i += chunkSize) {
                const chunk = commands.slice(i, i + chunkSize);
                
                // Usar writeValue si está disponible, sino writeWithoutResponse
                if (this.characteristic.properties.write) {
                    await this.characteristic.writeValue(chunk);
                } else if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                }
                
                // Pequeña pausa entre chunks
                await new Promise(resolve => setTimeout(resolve, 50));
            }
            
            console.log('✅ Comandos enviados exitosamente');
            
        } catch (error) {
            console.error('❌ Error enviando a impresora:', error);
            throw new Error(`Error imprimiendo: ${error.message}`);
        }
    }

    /**
     * Desconectar
     */
    disconnect() {
        if (this.device && this.device.gatt.connected) {
            this.device.gatt.disconnect();
        }
    }
}

// Instancia global
window.bluetoothPrinter = new BluetoothPrinter();