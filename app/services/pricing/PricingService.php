<?php
/**
 * PricingService - Single Source of Truth untuk Kalkulasi Harga, Modal Nett, Margin & Tier Pricing
 * 
 * Modul terpusat untuk memastikan konsistensi hitungan harga antara:
 * - Pembelian / Input Barang Masuk (PurchasesController)
 * - Kasir / Point of Sale (PosController)
 * - Master Produk (ProductModel & PackagingModel)
 * 
 * @package AlfarezMart\Services\Pricing
 */
class PricingService
{
    /**
     * Hitung harga modal bersih (Harga Nett) setelah PPN dan Diskon
     *
     * @param float  $buyPrice      Harga beli / modal kotor
     * @param float  $ppnPercent    Persentase PPN (misal 11 untuk 11%)
     * @param string $discountMode   'rp' (nominal rupiah) atau 'pct' (persentase)
     * @param float  $discountValue Nilai diskon
     * @param float  $quantity      Jumlah barang (untuk membagi diskon nominal Rp)
     * @return float Harga modal bersih per unit
     */
    public static function calculateNettPrice(
        float $buyPrice,
        float $ppnPercent = 0.0,
        string $discountMode = 'rp',
        float $discountValue = 0.0,
        float $quantity = 1.0
    ): float {
        $buy = max(0.0, $buyPrice);
        $ppn = max(0.0, $ppnPercent);
        $dv  = max(0.0, $discountValue);
        $qty = max(1.0, $quantity);

        $ppnAmount = $buy * ($ppn / 100.0);

        if ($discountMode === 'pct') {
            $discAmount = $buy * ($dv / 100.0);
        } else {
            // Mode nominal Rp dibagi per kuantitas
            $discAmount = $qty > 0 ? ($dv / $qty) : 0.0;
        }

        $nett = $buy + $ppnAmount - $discAmount;
        return max(0.0, round($nett, 2));
    }

    /**
     * Hitung persentase markup keuntungan terhadap modal nett
     *
     * @param float $nettPrice Modal bersih
     * @param float $sellPrice Harga jual
     * @return float|null Persentase markup (null jika nett <= 0)
     */
    public static function calculateMarkupPercent(float $nettPrice, float $sellPrice): ?float
    {
        if ($nettPrice <= 0.0 || $sellPrice <= 0.0) {
            return null;
        }

        $profit = $sellPrice - $nettPrice;
        return round(($profit / $nettPrice) * 100.0, 2);
    }

    /**
     * Hitung nominal profit rupiah
     *
     * @param float $nettPrice Modal bersih
     * @param float $sellPrice Harga jual
     * @return float Keuntungan nominal dalam rupiah
     */
    public static function calculateProfitAmount(float $nettPrice, float $sellPrice): float
    {
        return round($sellPrice - $nettPrice, 2);
    }

    /**
     * Hitung proporsi harga beli dan harga modal nett antar kemasan bertingkat
     *
     * @param array $packagings Daftar kemasan produk
     * @param int   $activeLevel Level kemasan yang sedang aktif diedit
     * @param float $activeBuyPrice Harga beli kemasan aktif
     * @return array Kemasan dengan harga yang sudah dipropagasi
     */
    public static function propagatePackagingPrices(
        array $packagings,
        int $activeLevel,
        float $activeBuyPrice
    ): array {
        if (empty($packagings)) {
            return [];
        }

        $activePkg = null;
        foreach ($packagings as $pkg) {
            if ((int)($pkg['level'] ?? 1) === $activeLevel) {
                $activePkg = $pkg;
                break;
            }
        }

        if (!$activePkg) {
            $activePkg = $packagings[0];
        }

        $activeBaseQty = max(1.0, (float)($activePkg['base_qty'] ?? 1.0));
        $buyPerPcs     = $activeBuyPrice / $activeBaseQty;

        foreach ($packagings as &$p) {
            $bq = max(1.0, (float)($p['base_qty'] ?? 1.0));
            // Jika kemasan tidak dikunci harga custom, hitung proporsional
            if (empty($p['buy_custom'])) {
                $p['buy_price'] = round($buyPerPcs * $bq, 2);
            }
        }

        return $packagings;
    }

    /**
     * Validasi tier pricing agar tidak di bawah modal bersih
     *
     * @param float $nettPrice Modal bersih
     * @param array $tierRows  Array baris tier: [['min_qty' => 3, 'unit_price' => 12000], ...]
     * @return array Hasil validasi ['is_valid' => bool, 'violations' => array]
     */
    public static function validateTierPricing(float $nettPrice, array $tierRows): array
    {
        $violations = [];
        foreach ($tierRows as $idx => $tier) {
            $price = (float)($tier['unit_price'] ?? 0.0);
            $qty   = (float)($tier['min_qty'] ?? 1.0);
            if ($price > 0 && $price < $nettPrice) {
                $violations[] = [
                    'index'      => $idx,
                    'min_qty'    => $qty,
                    'unit_price' => $price,
                    'nett_price' => $nettPrice,
                    'loss'       => round($nettPrice - $price, 2)
                ];
            }
        }

        return [
            'is_valid'   => empty($violations),
            'violations' => $violations
        ];
    }
}
