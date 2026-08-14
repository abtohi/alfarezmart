<?php
/**
 * SupplierDetector
 *
 * Automatically detects the supplier format of an invoice based on:
 * 1. Supplier selected by user in UI ($supplierId)
 * 2. Visual / text signatures (e.g., "PT Medan Distribusindo Raya", bank account numbers, header patterns)
 *
 * @package AlfarezMart\Services\Invoice
 */
class SupplierDetector
{
    /** @var \PDO */
    private $db;

    /**
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Detect the supplier skill key from context and/or text signatures.
     *
     * @param int|null $supplierId Optional supplier ID from UI
     * @param string $extractedText Optional OCR / image text snippet
     * @return array{
     *   skill_key: string,
     *   supplier_id: int|null,
     *   supplier_name: string,
     *   confidence: float,
     *   detection_method: string
     * }
     */
    public function detect(?int $supplierId = null, string $extractedText = ''): array
    {
        $textLower = strtolower($extractedText);

        // 1. Check text signatures for PT Medan Distribusindo Raya (MDR)
        if (!empty($textLower)) {
            $mdrSignatures = [
                'medan distribusindo raya',
                'medan distribusindo',
                'distribusindo raya',
                '0228-01-001633-30-1',
                '022801001633301',
                'pt. mdr',
                'pt mdr',
                'faktur penjualan mdr'
            ];

            foreach ($mdrSignatures as $sig) {
                if (strpos($textLower, $sig) !== false) {
                    $foundId = $supplierId ?: $this->findSupplierIdByNamePattern('Medan Distribusindo');
                    return [
                        'skill_key'        => 'mdr',
                        'supplier_id'      => $foundId,
                        'supplier_name'    => 'PT Medan Distribusindo Raya (MDR)',
                        'confidence'       => 0.98,
                        'detection_method' => 'text_signature'
                    ];
                }
            }
        }

        // 2. Check Supplier Name from Database using $supplierId
        if ($supplierId && $supplierId > 0) {
            try {
                $stmt = $this->db->prepare("SELECT id, name FROM suppliers WHERE id = ?");
                $stmt->execute([$supplierId]);
                $supplier = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($supplier) {
                    $sName = strtolower($supplier['name']);
                    if (strpos($sName, 'medan distribusindo') !== false || strpos($sName, 'mdr') !== false || strpos($sName, 'wings') !== false) {
                        return [
                            'skill_key'        => 'mdr',
                            'supplier_id'      => (int)$supplier['id'],
                            'supplier_name'    => $supplier['name'],
                            'confidence'       => 0.95,
                            'detection_method' => 'selected_supplier'
                        ];
                    }

                    return [
                        'skill_key'        => 'general',
                        'supplier_id'      => (int)$supplier['id'],
                        'supplier_name'    => $supplier['name'],
                        'confidence'       => 0.80,
                        'detection_method' => 'selected_supplier_general'
                    ];
                }
            } catch (\Throwable $e) {
                error_log("SupplierDetector error: " . $e->getMessage());
            }
        }

        // 3. Fallback to General Skill
        return [
            'skill_key'        => 'general',
            'supplier_id'      => $supplierId,
            'supplier_name'    => 'General / Standar Supplier',
            'confidence'       => 0.70,
            'detection_method' => 'default_fallback'
        ];
    }

    private function findSupplierIdByNamePattern(string $pattern): ?int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM suppliers WHERE name LIKE ? LIMIT 1");
            $stmt->execute(['%' . $pattern . '%']);
            $id = $stmt->fetchColumn();
            return $id ? (int)$id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
