# ESC-POS Optimization Documentation

## Thermal Printer 58mm Setup untuk AlfarezMart

### Overview
Aplikasi AlfarezMart telah dioptimasi untuk mendukung printer thermal receipt Bluetooth 58mm dengan menggunakan protokol ESC-POS (Epson Standard Code Point of Sale).

### Spesifikasi ESC-POS Commands yang Digunakan

#### 1. **Initialization & Setup**
```
ESC @ (0x1B 0x40)     - Full reset printer
ESC ! n (0x1B 0x21)   - Set print mode
ESC t (0x1B 0x74)     - Character set (28 = UTF-8)
ESC 3 (0x1B 0x33)     - Line spacing 1/8"
```

#### 2. **Text Formatting**
```
ESC E n (0x1B 0x45)   - Bold (1=on, 0=off)
ESC a n (0x1B 0x61)   - Alignment (0=left, 1=center, 2=right)
```

#### 3. **Paper Handling**
```
LF (0x0A)             - Line feed
CR (0x0D)             - Carriage return
```

#### 4. **Paper Cut**
```
GS V m (0x1D 0x56)    - Paper cut (0=full, 1=partial)
```

### Receipt Format untuk 58mm Printer

**Line Width**: 32 characters per line

```
================================  (32 chars separator)
        ALFAREZMART                (Centered store name)
    Jl. Sudirman No. 1            (Centered address)
    Telp: +62-123-456789          (Centered phone)
================================

No         : INV-20260516-001
Tgl        : 16 May 2026 14:30
Bayar      : Tunai
--------------------------------

ITEM
................................

Produk Name A
2 pcs x Rp100.000     Rp200.000
................................

Produk Name B (If name is longer
it will wrap to next line)
1 box x Rp50.000      Rp50.000
................................

TOTAL: Rp250.000

    Terima Kasih
   Semoga Puas Belanja
   ============
 Barang sudah dibeli
  tidak dapat ditukar
    ============

(Paper feed + partial cut)
```

### Connection Specifications

#### Bluetooth Protocol
- **Protocol**: RFCOMM (Serial over Bluetooth)
- **Baud Rate**: 9600 bps (default)
- **Data Bits**: 8
- **Stop Bits**: 1
- **Parity**: None

#### Web Bluetooth API (Android Chrome/Edge)
```javascript
// Services scanned
- '000018f0-0000-1000-8000-00805f9b34fb' (Serial Port Service)

// Optional services
- 'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
- '49535343-fe7d-4ae5-8fa9-9fafd205e455'

// Characteristic: Write (with or without response)
```

### Data Transmission

#### Chunk Size
- **Optimal**: 64 bytes per chunk
- **Min Delay**: 40ms between chunks
- **Max Payload**: Usually no limit for receipt data

#### Timing
- Connection: ~2-3 seconds
- Print: ~5-10 seconds (depends on content)
- Paper cut: ~1 second
- Soft disconnect: Immediate

### Character Encoding

- **Input**: UTF-8 (JavaScript native)
- **Transmission**: UTF-8 encoded bytes
- **Output**: Printer-native encoding (usually CP850, CP437, or UTF-8)

### Text Wrapping Algorithm

**58mm printer = 32 characters per line**

```javascript
function wrapText(text, width) {
    const words = text.split(/\s+/);
    const lines = [];
    let line = '';
    
    words.forEach(word => {
        const test = line ? `${line} ${word}` : word;
        if (test.length <= width) {
            line = test;
        } else {
            if (line) lines.push(line);
            line = word.length > width ? word.substring(0, width) : word;
        }
    });
    
    if (line) lines.push(line);
    return lines;
}
```

### Line Formatting

#### Centered Line
```javascript
function centerLine(text, width) {
    const pad = Math.floor((width - text.length) / 2);
    return ' '.repeat(pad) + text;
}
```

#### Padded Line (Left + Right content)
```javascript
function padLine(left, right, width) {
    const spaces = width - left.length - right.length;
    if (spaces < 1) {
        return left.substring(0, width - right.length - 1) + ' ' + right;
    }
    return left + ' '.repeat(spaces) + right;
}
```

### Price Formatting

```javascript
// Indonesian Rupiah format
function formatPrice(num) {
    return 'Rp' + Math.round(num).toLocaleString('id-ID');
}

// Example output:
// Rp100000    -> "Rp100.000"
// Rp1000000   -> "Rp1.000.000"
```

### Error Handling

#### Connection Errors
```
NotFoundError       - No device found
NotAllowedError     - User rejected pairing
NetworkError        - Bluetooth not supported
SecurityError       - User not authorized
```

#### Write Errors
```
InvalidStateError   - Device disconnected
NetworkError        - Transmission failed
NotSupportedError   - Characteristic not writable
```

### Automatic Reconnection

**Strategy**: "Soft Disconnect with Device Reference"

1. After successful print, disconnect GATT connection
2. Keep `device` object reference in memory
3. On next print, silently reconnect to stored device
4. No user interaction needed if device is in range

**Benefits**:
- Avoids Bluetooth stack hang on repeated connections
- Faster reconnection (no device picker dialog)
- Better user experience (automatic reconnect)

### Performance Metrics

For typical receipt with 3-5 items:

| Metric | Value |
|--------|-------|
| Data Size | 800-1200 bytes |
| Transmission Time | 2-3 seconds |
| Print Time | 3-5 seconds |
| Total Time | 5-8 seconds |
| Chunks Sent | 12-19 chunks |

### Compatibility Matrix

| Printer | Model | Support | Notes |
|---------|-------|---------|-------|
| XPrinter | XP-58IIH | ✅ Full | Recommended |
| RONGTA | RPOS58 | ✅ Full | Tested |
| Sunmi | L2 | ✅ Full | Works great |
| Epson | TM-M30 | ✅ Partial | 80mm but works |
| Generic | ESC-POS 58mm | ✅ Most | Follow ESC-POS spec |

### Troubleshooting ESC-POS Issues

#### Problem: Text appears garbled
**Solution**: Check character set setting and printer encoding

#### Problem: Receipt too wide or narrow
**Solution**: Adjust `thermal_printer_width` setting (58 or 80)

#### Problem: Special characters not printing correctly
**Solution**: Ensure UTF-8 encoding and printer supports multi-byte

#### Problem: Lines not cut properly
**Solution**: 
- Use partial cut (GS V 1) instead of full cut (GS V 0)
- Some printers may need longer delay before cut command

#### Problem: Repeated connections fail
**Solution**: Implement soft disconnect after print to prevent stack issues

### References

- **ESC-POS Specification**: https://www.epson.co.jp/
- **Web Bluetooth API**: https://webbluetoothcg.github.io/web-bluetooth/
- **Common Thermal Printers**: XPrinter, RONGTA, Sunmi, Epson, Star

---

**Document Version**: 1.0  
**Last Updated**: May 16, 2026  
**Status**: Production Ready ✅
