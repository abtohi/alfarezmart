<?php
/**
 * Helper - Global utility functions
 */

if (!class_exists('Helper')) {
    class Helper
    {
    /**
     * Format currency (Indonesian Rupiah)
     */
    public static function rupiah($amount, $prefix = 'Rp')
    {
        return $prefix . number_format((float)$amount, 0, ',', '.');
    }

    /**
     * Format date to Indonesian format
     */
    public static function formatDate($date, $format = 'd M Y')
    {
        if (empty($date)) return '-';
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function formatDateTime($date)
    {
        if (empty($date)) return '-';
        return date('d M Y H:i', strtotime($date));
    }

    /**
     * Generate unique ID (8 char hex)
     */
    public static function generateId($length = 8)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber($prefix = 'INV')
    {
        return $prefix . '-' . date('ymdHis');
    }

    /**
     * Generate purchase code
     */
    public static function generatePurchaseCode()
    {
        return 'PUR-' . date('ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique barcode for products without one
     */
    public static function generateBarcode($prefix = null)
    {
        if ($prefix === null) {
            $prefix = defined('BARCODE_PREFIX') ? BARCODE_PREFIX : 'AM';
        }

        $db = Database::getInstance()->getConnection();
        $maxAttempts = 15;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $prefix . date('ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $stmt = $db->prepare("SELECT COUNT(*) FROM product_packagings WHERE barcode = :bc");
            $stmt->execute([':bc' => $code]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $code;
            }
        }

        return $prefix . date('ymdHis') . mt_rand(100, 999);
    }

    /**
     * Check if barcode is already used
     */
    public static function barcodeExists($barcode, $excludePackagingId = null)
    {
        if (empty($barcode)) {
            return false;
        }

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM product_packagings WHERE barcode = :bc";
        $params = [':bc' => $barcode];

        if ($excludePackagingId) {
            $sql .= " AND id != :id";
            $params[':id'] = (int) $excludePackagingId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get product info that owns a specific barcode (for duplicate detection)
     */
    public static function barcodeOwner($barcode, $excludePackagingId = null)
    {
        if (empty($barcode)) return null;

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT pp.id as packaging_id, pp.product_id, pp.level, pp.barcode,
                       p.full_name, p.short_label, u.name as unit_name
                FROM product_packagings pp
                JOIN products p ON pp.product_id = p.id
                LEFT JOIN units u ON pp.unit_id = u.id
                WHERE pp.barcode = :bc";
        $params = [':bc' => $barcode];

        if ($excludePackagingId) {
            $sql .= " AND pp.id != :id";
            $params[':id'] = (int) $excludePackagingId;
        }

        $sql .= " LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Build product full name from components
     * Format: "Brand Produk Varian (Isi x Berat/Volume)"
     */
    public static function buildProductName($brand, $type, $variant, $qtyLarge, $weightValue, $weightUnit)
    {
        $name = trim($brand);
        if (!empty($type)) $name .= ' ' . trim($type);
        if (!empty($variant)) $name .= ' ' . trim($variant);
        
        $packaging = '';
        if (!empty($qtyLarge) && $qtyLarge > 1) {
            $packaging = $qtyLarge . ' x ';
        }
        if (!empty($weightValue) && !empty($weightUnit)) {
            $packaging .= $weightValue . $weightUnit;
        }
        
        if (!empty($packaging)) {
            $name .= ' (' . $packaging . ')';
        }
        
        return $name;
    }

    /**
     * Build short label for thermal printer
     * Max ~35 chars for 58mm thermal paper
     */
    public static function buildShortLabel($brand, $type, $variant, $weightValue, $weightUnit)
    {
        $label = trim($brand);
        if (!empty($type)) $label .= ' ' . trim($type);
        if (!empty($variant)) $label .= ' ' . trim($variant);
        if (!empty($weightValue) && !empty($weightUnit)) {
            $label .= ' ' . $weightValue . $weightUnit;
        }
        
        // Truncate if too long
        if (strlen($label) > 35) {
            $label = substr($label, 0, 32) . '...';
        }
        
        return $label;
    }

    /**
     * Calculate markup percentage (berbasis harga modal)
     */
    public static function calculateMargin($buyPrice, $sellPrice)
    {
        if ($buyPrice <= 0) return 0;
        return round(($sellPrice - $buyPrice) / $buyPrice, 4);
    }

    /**
     * Calculate sell price from buy price and markup
     */
    public static function calculateSellPrice($buyPrice, $markup)
    {
        return round($buyPrice * (1 + $markup));
    }

    /**
     * Write NDJSON transaction log
     */
    public static function writeTransactionLog($data)
    {
        $date = date('Y-m-d');
        $file = STORAGE_PATH . '/transactions/' . $date . '.ndjson';
        $data['ts'] = date('c');
        $line = json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get base URL
     */
    public static function url($path = '')
    {
        $base = defined('APP_URL') ? APP_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Asset URL
     */
    public static function asset($path)
    {
        return self::url('public/' . ltrim($path, '/'));
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 50)
    {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }

    /**
     * Convert datetime to relative time string (Indonesian)
     */
    public static function timeAgo($datetime)
    {
        if (empty($datetime)) return '';
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) return $diff->y . ' thn lalu';
        if ($diff->m > 0) return $diff->m . ' bln lalu';
        if ($diff->d > 0) return $diff->d . ' hari lalu';
        if ($diff->h > 0) return $diff->h . ' jam lalu';
        if ($diff->i > 0) return $diff->i . ' mnt lalu';
        return 'baru saja';
    }
    }
}
