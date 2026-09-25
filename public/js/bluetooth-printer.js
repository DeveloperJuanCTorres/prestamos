class BluetoothPrinter {
    constructor() {
        this.device = null;
        this.characteristic = null;
        this.isSupported = this.checkSupport();
    }

    checkSupport() {
        if (!('bluetooth' in navigator)) return false;

        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        if (isIOS) return false;

        const isAndroid = /Android/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent);
        const isEdge = /Edg/.test(navigator.userAgent);

        return (isAndroid && (isChrome || isEdge)) || (!isAndroid && (isChrome || isEdge));
    }

    async connect() {
        try {
            this.device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: [
                    '00001101-0000-1000-8000-00805f9b34fb',
                    '0000180a-0000-1000-8000-00805f9b34fb',
                    '0000180f-0000-1000-8000-00805f9b34fb',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455'
                ]
            });

            const server = await this.device.gatt.connect();

            let connected = false;

            try {
                const service = await server.getPrimaryService('00001101-0000-1000-8000-00805f9b34fb');
                const characteristics = await service.getCharacteristics();

                for (const char of characteristics) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        this.characteristic = char;
                        connected = true;
                        break;
                    }
                }
            } catch {}

            if (!connected) {
                const services = await server.getPrimaryServices();
                for (const service of services) {
                    try {
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                this.characteristic = char;
                                connected = true;
                                break;
                            }
                        }
                        if (connected) break;
                    } catch {}
                }
            }

            if (!connected) throw new Error('No se encontró característica de escritura');

            return true;

        } catch (error) {
            throw new Error('Error de conexión: ' + error.message);
        }
    }

    generateESCPOSCommands(ticketData) {
        const commands = [];
        const ESC = 0x1B;
        const LF = 0x0A;

        // Reset
        commands.push(ESC, 0x40);

        // Header (Centrado y Negrita)
        commands.push(ESC, 0x61, 0x01);
        commands.push(ESC, 0x45, 0x01);
        commands.push(...this.stringToBytes('CREDI-ANRO'));
        commands.push(LF);
        commands.push(...this.stringToBytes('TICKET DE PAGO'));
        commands.push(ESC, 0x45, 0x00);
        commands.push(LF);

        // Línea divisoria
        commands.push(...this.stringToBytes('--------------------------------'));
        commands.push(LF);

        // Cliente (Izquierda)
        commands.push(ESC, 0x61, 0x00);
        commands.push(...this.stringToBytes('Cliente: ' + ticketData.cliente));
        commands.push(LF);
        commands.push(...this.stringToBytes('Doc: ' + ticketData.documento));
        commands.push(LF);

        // Línea divisoria
        commands.push(...this.stringToBytes('--------------------------------'));
        commands.push(LF);

        // Detalle Préstamo
        commands.push(...this.stringToBytes('Prestamo: #' + ticketData.prestamo_id));
        commands.push(LF);
        if (ticketData.monto_prestamo) {
            commands.push(...this.stringToBytes('Monto Total: S/ ' + ticketData.monto_prestamo));
            commands.push(LF);
        }
        if (ticketData.total_a_pagar) {
            commands.push(...this.stringToBytes('A Pagar: S/ ' + ticketData.total_a_pagar));
            commands.push(LF);
        }
        if (ticketData.tipo) {
            commands.push(...this.stringToBytes('Tipo: ' + ticketData.tipo));
            commands.push(LF);
        }
        commands.push(...this.stringToBytes('Cuota: ' + ticketData.cuota + '/' + ticketData.total_cuotas));
        commands.push(LF);
        commands.push(...this.stringToBytes('Monto Pagado: S/ ' + ticketData.monto));
        commands.push(LF);
        commands.push(...this.stringToBytes('Saldo Restante: S/ ' + ticketData.saldo));
        commands.push(LF);
        if (ticketData.fecha_vencimiento) {
            commands.push(...this.stringToBytes('F. Vencimiento: ' + ticketData.fecha_vencimiento));
            commands.push(LF);
        }
        commands.push(...this.stringToBytes('F. Pago: ' + ticketData.fecha));
        commands.push(LF);

        const condicionStr = (ticketData.dias_atraso && ticketData.dias_atraso > 0)
            ? ('Atraso (' + ticketData.dias_atraso + (ticketData.dias_atraso == 1 ? ' dia)' : ' dias)'))
            : 'Pago Puntual';
        commands.push(...this.stringToBytes('Condicion: ' + condicionStr));
        commands.push(LF);

        // Línea divisoria
        commands.push(...this.stringToBytes('--------------------------------'));
        commands.push(LF);

        // Footer (Centrado)
        commands.push(ESC, 0x61, 0x01);
        commands.push(...this.stringToBytes('!Gracias por su pago!'));
        commands.push(LF);

        const now = new Date();
        const dateStr = now.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
                        now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false });
        commands.push(...this.stringToBytes(dateStr));
        commands.push(LF, LF, LF);

        // Cortar papel / avance
        commands.push(ESC, 0x64, 0x03);

        return new Uint8Array(commands);
    }

    stringToBytes(str) {
        const normalized = str
            .replace(/[áàäâã]/gi, 'a')
            .replace(/[éèëê]/gi, 'e')
            .replace(/[íìïî]/gi, 'i')
            .replace(/[óòöôõ]/gi, 'o')
            .replace(/[úùüû]/gi, 'u')
            .replace(/ñ/gi, 'n');

        return Array.from(normalized, c => c.charCodeAt(0));
    }

    async print(ticketData) {
        if (!this.characteristic) {
            throw new Error('No hay conexión con la impresora');
        }

        const commands = this.generateESCPOSCommands(ticketData);
        const chunkSize = 64;

        for (let i = 0; i < commands.length; i += chunkSize) {
            const chunk = commands.slice(i, i + chunkSize);

            if (this.characteristic.properties.writeWithoutResponse) {
                await this.characteristic.writeValueWithoutResponse(chunk);
            } else {
                await this.characteristic.writeValue(chunk);
            }

            await new Promise(r => setTimeout(r, 20));
        }

        return true;
    }

    disconnect() {
        if (this.device?.gatt?.connected) {
            this.device.gatt.disconnect();
        }
        this.device = null;
        this.characteristic = null;
    }

    getDeviceInfo() {
        return {
            connected: this.device?.gatt?.connected || false,
            supported: this.isSupported,
            deviceName: this.device?.name || null
        };
    }
}

window.bluetoothPrinter = new BluetoothPrinter();
