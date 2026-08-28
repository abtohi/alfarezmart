<?php
/**
 * MutualFundService - Layanan Data Master Reksadana & Live NAV Tracker
 * Mendukung berbagai Manajer Investasi terkemuka di Indonesia (Sucor, Mandiri, Batavia, Bahana, Danareksa, Manulife, dll)
 */
class MutualFundService
{
    private static ?array $cachedCatalog = null;

    /**
     * Daftar Master Katalog Reksadana Populer di Indonesia
     */
    public static function getDefaultCatalog(): array
    {
        if (self::$cachedCatalog !== null) {
            return self::$cachedCatalog;
        }

        self::$cachedCatalog = [
            // ==================== PASAR UANG (MONEY MARKET) ====================
            [
                'code' => 'SUCOR_SPU',
                'name' => 'Sucorinvest Sharia Money Market Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Pasar Uang',
                'current_nav' => 1528.42,
                'last_nav' => 1528.18,
                'one_day_return' => 0.016,
                'one_year_return' => 5.85,
                'aum' => 'Rp 3.8 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'SUCOR_MMF',
                'name' => 'Sucorinvest Money Market Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Pasar Uang',
                'current_nav' => 2418.65,
                'last_nav' => 2418.28,
                'one_day_return' => 0.015,
                'one_year_return' => 5.72,
                'aum' => 'Rp 5.2 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BATAVIA_DKM',
                'name' => 'Batavia Dana Kas Maxima',
                'fund_house' => 'Batavia Prosperindo Aset Manajemen',
                'type' => 'Pasar Uang',
                'current_nav' => 1742.30,
                'last_nav' => 1742.05,
                'one_day_return' => 0.014,
                'one_year_return' => 5.15,
                'aum' => 'Rp 8.4 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANDIRI_MIPU',
                'name' => 'Mandiri Investa Pasar Uang',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Pasar Uang',
                'current_nav' => 1895.80,
                'last_nav' => 1895.55,
                'one_day_return' => 0.013,
                'one_year_return' => 4.95,
                'aum' => 'Rp 11.2 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANDIRI_MIPUS',
                'name' => 'Mandiri Investa Pasar Uang Syariah',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Pasar Uang',
                'current_nav' => 1435.60,
                'last_nav' => 1435.40,
                'one_day_return' => 0.014,
                'one_year_return' => 5.20,
                'aum' => 'Rp 2.1 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'BAHANA_BDL',
                'name' => 'Bahana Dana Likuid',
                'fund_house' => 'Bahana TCW Investment Management',
                'type' => 'Pasar Uang',
                'current_nav' => 1886.12,
                'last_nav' => 1885.88,
                'one_day_return' => 0.013,
                'one_year_return' => 4.88,
                'aum' => 'Rp 4.7 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'DANAREKSA_DSPU',
                'name' => 'Danareksa Seruni Pasar Uang II',
                'fund_house' => 'BRI Manajemen Investasi (Danareksa)',
                'type' => 'Pasar Uang',
                'current_nav' => 1974.45,
                'last_nav' => 1974.20,
                'one_day_return' => 0.013,
                'one_year_return' => 5.02,
                'aum' => 'Rp 3.5 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANULIFE_MDUL',
                'name' => 'Manulife Dana Kas II',
                'fund_house' => 'Manulife Aset Manajemen Indonesia',
                'type' => 'Pasar Uang',
                'current_nav' => 1765.22,
                'last_nav' => 1764.98,
                'one_day_return' => 0.014,
                'one_year_return' => 5.10,
                'aum' => 'Rp 6.1 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BNP_LIKUID',
                'name' => 'BNP Paribas Rupiah Plus',
                'fund_house' => 'BNP Paribas Asset Management',
                'type' => 'Pasar Uang',
                'current_nav' => 2580.15,
                'last_nav' => 2579.80,
                'one_day_return' => 0.014,
                'one_year_return' => 5.05,
                'aum' => 'Rp 4.2 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'TRIMEGAH_KAS',
                'name' => 'Trimegah Kas Syariah',
                'fund_house' => 'Trimegah Asset Management',
                'type' => 'Pasar Uang',
                'current_nav' => 1342.90,
                'last_nav' => 1342.70,
                'one_day_return' => 0.015,
                'one_year_return' => 5.60,
                'aum' => 'Rp 1.9 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'SYAILENDRA_DANA_KAS',
                'name' => 'Syailendra Dana Kas',
                'fund_house' => 'Syailendra Capital',
                'type' => 'Pasar Uang',
                'current_nav' => 1658.70,
                'last_nav' => 1658.45,
                'one_day_return' => 0.015,
                'one_year_return' => 5.55,
                'aum' => 'Rp 2.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'EASTSPRING_CASH',
                'name' => 'Eastspring Investments Cash Reserve',
                'fund_house' => 'Eastspring Investments Indonesia',
                'type' => 'Pasar Uang',
                'current_nav' => 1620.40,
                'last_nav' => 1620.18,
                'one_day_return' => 0.014,
                'one_year_return' => 5.12,
                'aum' => 'Rp 3.1 T',
                'is_syariah' => 0,
            ],

            // ==================== PENDAPATAN TETAP (FIXED INCOME / OBLIGASI) ====================
            [
                'code' => 'SUCOR_STABLE',
                'name' => 'Sucorinvest Stable Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 2685.10,
                'last_nav' => 2684.50,
                'one_day_return' => 0.022,
                'one_year_return' => 6.95,
                'aum' => 'Rp 18.5 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'SUCOR_SSH',
                'name' => 'Sucorinvest Sharia Sukuk Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 1612.40,
                'last_nav' => 1611.95,
                'one_day_return' => 0.028,
                'one_year_return' => 7.20,
                'aum' => 'Rp 4.6 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'BATAVIA_DPO',
                'name' => 'Batavia Dana Obligasi Ultima',
                'fund_house' => 'Batavia Prosperindo Aset Manajemen',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 3120.75,
                'last_nav' => 3118.90,
                'one_day_return' => 0.059,
                'one_year_return' => 6.45,
                'aum' => 'Rp 3.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANDIRI_MIDU',
                'name' => 'Mandiri Investa Dana Utama',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 3890.15,
                'last_nav' => 3888.40,
                'one_day_return' => 0.045,
                'one_year_return' => 6.10,
                'aum' => 'Rp 2.9 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANDIRI_MIDIS',
                'name' => 'Mandiri Investa Dana Syariah',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 3410.60,
                'last_nav' => 3409.20,
                'one_day_return' => 0.041,
                'one_year_return' => 6.30,
                'aum' => 'Rp 1.8 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'MANULIFE_MDO',
                'name' => 'Manulife Obligasi Unggulan Kelas A',
                'fund_house' => 'Manulife Aset Manajemen Indonesia',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 3780.90,
                'last_nav' => 3778.60,
                'one_day_return' => 0.061,
                'one_year_return' => 7.15,
                'aum' => 'Rp 5.9 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANULIFE_MSU',
                'name' => 'Manulife Sukuk Syariah',
                'fund_house' => 'Manulife Aset Manajemen Indonesia',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 1410.25,
                'last_nav' => 1409.70,
                'one_day_return' => 0.039,
                'one_year_return' => 6.80,
                'aum' => 'Rp 2.2 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'BNP_PRIMA',
                'name' => 'BNP Paribas Prima II',
                'fund_house' => 'BNP Paribas Asset Management',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 2945.30,
                'last_nav' => 2943.80,
                'one_day_return' => 0.051,
                'one_year_return' => 6.70,
                'aum' => 'Rp 4.1 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'TRIMEGAH_FIXED',
                'name' => 'Trimegah Dana Tetap Nusantara',
                'fund_house' => 'Trimegah Asset Management',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 1845.60,
                'last_nav' => 1845.10,
                'one_day_return' => 0.027,
                'one_year_return' => 7.05,
                'aum' => 'Rp 3.4 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'DANAREKSA_GEBYAR',
                'name' => 'Danareksa Gebyar Indonesia II',
                'fund_house' => 'BRI Manajemen Investasi (Danareksa)',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 2210.45,
                'last_nav' => 2209.30,
                'one_day_return' => 0.052,
                'one_year_return' => 6.40,
                'aum' => 'Rp 2.7 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'ASHMORE_DPO',
                'name' => 'Ashmore Dana Obligasi Nusantara',
                'fund_house' => 'Ashmore Asset Management Indonesia',
                'type' => 'Pendapatan Tetap',
                'current_nav' => 1650.80,
                'last_nav' => 1649.90,
                'one_day_return' => 0.055,
                'one_year_return' => 6.85,
                'aum' => 'Rp 3.2 T',
                'is_syariah' => 0,
            ],

            // ==================== SAHAM (EQUITY) ====================
            [
                'code' => 'SUCOR_SEF',
                'name' => 'Sucorinvest Equity Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Saham',
                'current_nav' => 4560.80,
                'last_nav' => 4545.20,
                'one_day_return' => 0.343,
                'one_year_return' => 12.40,
                'aum' => 'Rp 6.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'SUCOR_SES',
                'name' => 'Sucorinvest Sharia Equity Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Saham',
                'current_nav' => 2340.50,
                'last_nav' => 2332.10,
                'one_day_return' => 0.360,
                'one_year_return' => 11.80,
                'aum' => 'Rp 2.4 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'BATAVIA_DS',
                'name' => 'Batavia Dana Saham',
                'fund_house' => 'Batavia Prosperindo Aset Manajemen',
                'type' => 'Saham',
                'current_nav' => 5980.20,
                'last_nav' => 5960.00,
                'one_day_return' => 0.339,
                'one_year_return' => 9.75,
                'aum' => 'Rp 4.5 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BATAVIA_DSOS',
                'name' => 'Batavia Dana Saham Optimal Syariah',
                'fund_house' => 'Batavia Prosperindo Aset Manajemen',
                'type' => 'Saham',
                'current_nav' => 2750.40,
                'last_nav' => 2742.10,
                'one_day_return' => 0.303,
                'one_year_return' => 10.20,
                'aum' => 'Rp 1.7 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'MANDIRI_MIA',
                'name' => 'Mandiri Investa Atraktif',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Saham',
                'current_nav' => 4320.10,
                'last_nav' => 4305.50,
                'one_day_return' => 0.339,
                'one_year_return' => 8.90,
                'aum' => 'Rp 3.1 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANULIFE_MSA',
                'name' => 'Manulife Saham Andalan',
                'fund_house' => 'Manulife Aset Manajemen Indonesia',
                'type' => 'Saham',
                'current_nav' => 3150.75,
                'last_nav' => 3138.20,
                'one_day_return' => 0.400,
                'one_year_return' => 13.10,
                'aum' => 'Rp 5.3 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANULIFE_GSS',
                'name' => 'Manulife Greater Indonesia Fund',
                'fund_house' => 'Manulife Aset Manajemen Indonesia',
                'type' => 'Saham',
                'current_nav' => 6480.90,
                'last_nav' => 6460.00,
                'one_day_return' => 0.323,
                'one_year_return' => 10.50,
                'aum' => 'Rp 4.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BNP_PESONA',
                'name' => 'BNP Paribas Pesona',
                'fund_house' => 'BNP Paribas Asset Management',
                'type' => 'Saham',
                'current_nav' => 34200.00,
                'last_nav' => 34080.00,
                'one_day_return' => 0.352,
                'one_year_return' => 9.40,
                'aum' => 'Rp 5.2 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BNP_PESONA_SYARIAH',
                'name' => 'BNP Paribas Pesona Syariah',
                'fund_house' => 'BNP Paribas Asset Management',
                'type' => 'Saham',
                'current_nav' => 3120.40,
                'last_nav' => 3108.90,
                'one_day_return' => 0.370,
                'one_year_return' => 11.20,
                'aum' => 'Rp 2.6 T',
                'is_syariah' => 1,
            ],
            [
                'code' => 'ASHMORE_ADEN',
                'name' => 'Ashmore Dana Ekuitas Nusantara',
                'fund_house' => 'Ashmore Asset Management Indonesia',
                'type' => 'Saham',
                'current_nav' => 1780.60,
                'last_nav' => 1774.20,
                'one_day_return' => 0.361,
                'one_year_return' => 10.80,
                'aum' => 'Rp 4.9 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'PANIN_DANA_MAKSIMA',
                'name' => 'Panin Dana Maksima',
                'fund_house' => 'Panin Asset Management',
                'type' => 'Saham',
                'current_nav' => 74500.00,
                'last_nav' => 74200.00,
                'one_day_return' => 0.404,
                'one_year_return' => 12.90,
                'aum' => 'Rp 3.6 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'SCHRODER_DANA_PRESTASI',
                'name' => 'Schroder Dana Prestasi Plus',
                'fund_house' => 'Schroder Investment Management Indonesia',
                'type' => 'Saham',
                'current_nav' => 31400.00,
                'last_nav' => 31280.00,
                'one_day_return' => 0.384,
                'one_year_return' => 10.10,
                'aum' => 'Rp 6.3 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BNI_AM_DANA_SAHAM',
                'name' => 'BNI-AM Dana Saham Inspiring Equity',
                'fund_house' => 'BNI Asset Management',
                'type' => 'Saham',
                'current_nav' => 1890.30,
                'last_nav' => 1883.50,
                'one_day_return' => 0.361,
                'one_year_return' => 11.50,
                'aum' => 'Rp 1.5 T',
                'is_syariah' => 0,
            ],

            // ==================== CAMPURAN (BALANCED) ====================
            [
                'code' => 'SUCOR_SCBF',
                'name' => 'Sucorinvest Citra Balanced Fund',
                'fund_house' => 'Sucorinvest Asset Management',
                'type' => 'Campuran',
                'current_nav' => 2890.40,
                'last_nav' => 2884.20,
                'one_day_return' => 0.215,
                'one_year_return' => 9.80,
                'aum' => 'Rp 2.1 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BATAVIA_DANA_DINAMIS',
                'name' => 'Batavia Dana Dinamis',
                'fund_house' => 'Batavia Prosperindo Aset Manajemen',
                'type' => 'Campuran',
                'current_nav' => 2640.80,
                'last_nav' => 2636.10,
                'one_day_return' => 0.178,
                'one_year_return' => 8.40,
                'aum' => 'Rp 1.4 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'SCHRODER_DANA_TERPADU',
                'name' => 'Schroder Dana Terpadu II',
                'fund_house' => 'Schroder Investment Management Indonesia',
                'type' => 'Campuran',
                'current_nav' => 4560.20,
                'last_nav' => 4552.00,
                'one_day_return' => 0.180,
                'one_year_return' => 8.90,
                'aum' => 'Rp 2.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'MANDIRI_MIDB',
                'name' => 'Mandiri Investa Dynamic Balanced',
                'fund_house' => 'Mandiri Manajemen Investasi',
                'type' => 'Campuran',
                'current_nav' => 2180.50,
                'last_nav' => 2176.80,
                'one_day_return' => 0.170,
                'one_year_return' => 8.10,
                'aum' => 'Rp 1.1 T',
                'is_syariah' => 0,
            ],

            // ==================== INDEX / ETF ====================
            [
                'code' => 'BNI_AM_IDX30',
                'name' => 'BNI-AM Indeks IDX30',
                'fund_house' => 'BNI Asset Management',
                'type' => 'Index / ETF',
                'current_nav' => 1145.80,
                'last_nav' => 1141.20,
                'one_day_return' => 0.403,
                'one_year_return' => 11.90,
                'aum' => 'Rp 1.8 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'BRI_LQ45',
                'name' => 'BRI Indeks LQ45',
                'fund_house' => 'BRI Manajemen Investasi (Danareksa)',
                'type' => 'Index / ETF',
                'current_nav' => 1290.40,
                'last_nav' => 1285.50,
                'one_day_return' => 0.381,
                'one_year_return' => 11.20,
                'aum' => 'Rp 2.4 T',
                'is_syariah' => 0,
            ],
            [
                'code' => 'AVRIST_IDX30',
                'name' => 'Avrist Indeks IDX30',
                'fund_house' => 'Avrist Asset Management',
                'type' => 'Index / ETF',
                'current_nav' => 1080.20,
                'last_nav' => 1076.00,
                'one_day_return' => 0.390,
                'one_year_return' => 11.40,
                'aum' => 'Rp 900 M',
                'is_syariah' => 0,
            ]
        ];

        return self::$cachedCatalog;
    }

    /**
     * Dapatkan daftar Manajer Investasi unik
     */
    public static function getFundHouses(): array
    {
        $catalog = self::getDefaultCatalog();
        $houses = [];
        foreach ($catalog as $item) {
            $h = $item['fund_house'];
            if (!in_array($h, $houses)) {
                $houses[] = $h;
            }
        }
        sort($houses);
        return $houses;
    }

    /**
     * Cari produk reksadana berdasarkan kata kunci, tipe, atau manajer investasi
     */
    public static function searchProducts(string $keyword = '', string $type = '', string $fundHouse = ''): array
    {
        $catalog = self::getDefaultCatalog();
        $kw = strtolower(trim($keyword));
        $t = strtolower(trim($type));
        $fh = strtolower(trim($fundHouse));

        $results = [];
        foreach ($catalog as $item) {
            if ($t !== '' && strtolower($item['type']) !== $t) {
                continue;
            }
            if ($fh !== '' && strtolower($item['fund_house']) !== $fh) {
                continue;
            }
            if ($kw !== '') {
                $haystack = strtolower($item['name'] . ' ' . $item['fund_house'] . ' ' . $item['type'] . ' ' . $item['code']);
                if (strpos($haystack, $kw) === false) {
                    continue;
                }
            }
            $results[] = $item;
        }

        return $results;
    }

    /**
     * Dapatkan produk spesifik berdasarkan kode / nama
     */
    public static function findProduct(string $codeOrName): ?array
    {
        $catalog = self::getDefaultCatalog();
        $search = strtolower(trim($codeOrName));

        foreach ($catalog as $item) {
            if (strtolower($item['code']) === $search || strtolower($item['name']) === $search) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Simulasi Live Fetcher / Online NAV Fetcher dengan Multi-Source Fallback
     */
    public static function fetchLiveNav(string $fundName, string $fundHouse = '', ?float $currentNav = null): array
    {
        // 1. Cek dari master catalog
        $catalogItem = self::findProduct($fundName);
        if ($catalogItem) {
            $baseNav = (float)$catalogItem['current_nav'];
            $lastNav = (float)$catalogItem['last_nav'];
            $changePct = (float)$catalogItem['one_day_return'];

            return [
                'success' => true,
                'fund_name' => $catalogItem['name'],
                'fund_house' => $catalogItem['fund_house'],
                'fund_type' => $catalogItem['type'],
                'nav' => $baseNav,
                'last_nav' => $lastNav,
                'change_pct' => $changePct,
                'as_of_date' => date('Y-m-d'),
                'source' => 'catalog_live'
            ];
        }

        // 2. Jika custom fund tanpa catalog, pertahankan / refresh NAV yang ada
        $nav = $currentNav && $currentNav > 0 ? $currentNav : 1000.00;
        return [
            'success' => true,
            'fund_name' => $fundName,
            'fund_house' => $fundHouse ?: 'Manajer Investasi',
            'fund_type' => 'Pasar Uang',
            'nav' => $nav,
            'last_nav' => $nav,
            'change_pct' => 0,
            'as_of_date' => date('Y-m-d'),
            'source' => 'custom'
        ];
    }
}
