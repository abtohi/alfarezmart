<?php
/**
 * TemplateLearner
 *
 * Learns from successful scans to improve future accuracy for the same supplier.
 *
 * Stores:
 *   - Column mapping detected in this scan (column_map)
 *   - Header aliases that were matched (header_aliases)
 *   - Scan count and timestamps
 *
 * Creates the invoice_scan_templates table automatically if it doesn't exist
 * (following the established pattern in ProductModel::ensureQtyPriceSchema).
 *
 * Template is ONLY saved if avg_confidence >= 0.80.
 *
 * @package AlfarezMart\Services\Invoice
 */
class TemplateLearner
{
    const MIN_CONFIDENCE_TO_SAVE = 0.80;

    /** @var \PDO */
    private $db;

    /** @var bool */
    private $tableReady = false;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Find a previously saved template for a supplier.
     *
     * @param  int|null $supplierId
     * @return array|null
     */
    public function findTemplate(?int $supplierId): ?array
    {
        if ($supplierId === null || $supplierId <= 0) {
            return null;
        }

        try {
            $this->ensureTable();

            $stmt = $this->db->prepare("
                SELECT * FROM invoice_scan_templates
                WHERE supplier_id = :sid
                ORDER BY scan_count DESC, last_scan_at DESC
                LIMIT 1
            ");
            $stmt->execute([':sid' => $supplierId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;

        } catch (\Throwable $e) {
            error_log('TemplateLearner::findTemplate error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save or update the scan template for a supplier.
     * Only saved if confidence is high enough.
     *
     * @param  int|null $supplierId
     * @param  string   $supplierName
     * @param  array    $columnMap       Detected column mapping from LayoutAnalyzer
     * @param  array    $headerAliases   Aliases from LayoutAnalyzer::HEADER_ALIASES
     * @param  float    $avgConfidence   From ConfidenceScorer
     */
    public function saveTemplate(
        ?int $supplierId,
        string $supplierName,
        array $columnMap,
        array $headerAliases,
        float $avgConfidence
    ): void {
        if ($avgConfidence < self::MIN_CONFIDENCE_TO_SAVE) {
            return; // Don't learn from poor-quality scans
        }

        if (empty($columnMap) && empty($headerAliases)) {
            return; // Nothing to save
        }

        try {
            $this->ensureTable();

            $colMapJson     = json_encode($columnMap,     JSON_UNESCAPED_UNICODE);
            $headerAliasJson = json_encode($headerAliases, JSON_UNESCAPED_UNICODE);
            $now            = date('Y-m-d H:i:s');

            if ($supplierId && $supplierId > 0) {
                // Check if template already exists for this supplier
                $stmt = $this->db->prepare("
                    SELECT id, scan_count FROM invoice_scan_templates
                    WHERE supplier_id = :sid
                    LIMIT 1
                ");
                $stmt->execute([':sid' => $supplierId]);
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($existing) {
                    // Update existing
                    $stmt = $this->db->prepare("
                        UPDATE invoice_scan_templates
                        SET column_map = :cm,
                            header_aliases = :ha,
                            scan_count = scan_count + 1,
                            last_scan_at = :now
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':cm'  => $colMapJson,
                        ':ha'  => $headerAliasJson,
                        ':now' => $now,
                        ':id'  => $existing['id'],
                    ]);
                } else {
                    // Insert new
                    $stmt = $this->db->prepare("
                        INSERT INTO invoice_scan_templates
                            (supplier_id, supplier_name, column_map, header_aliases, scan_count, last_scan_at, created_at)
                        VALUES (:sid, :sname, :cm, :ha, 1, :now, :now2)
                    ");
                    $stmt->execute([
                        ':sid'   => $supplierId,
                        ':sname' => $supplierName,
                        ':cm'    => $colMapJson,
                        ':ha'    => $headerAliasJson,
                        ':now'   => $now,
                        ':now2'  => $now,
                    ]);
                }
            }

        } catch (\Throwable $e) {
            error_log('TemplateLearner::saveTemplate error: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Create the invoice_scan_templates table if it doesn't exist.
     * Follows the pattern established in ProductModel::ensureQtyPriceSchema().
     */
    private function ensureTable(): void
    {
        if ($this->tableReady) return;

        try {
            $this->db->query("SELECT 1 FROM invoice_scan_templates LIMIT 1");
            $this->tableReady = true;
        } catch (\PDOException $e) {
            // Table doesn't exist — create it
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS invoice_scan_templates (
                        id              INT AUTO_INCREMENT PRIMARY KEY,
                        supplier_id     INT DEFAULT NULL,
                        supplier_name   VARCHAR(255) DEFAULT NULL,
                        column_map      JSON DEFAULT NULL,
                        header_aliases  JSON DEFAULT NULL,
                        scan_count      INT NOT NULL DEFAULT 1,
                        last_scan_at    DATETIME DEFAULT NULL,
                        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_ist_supplier (supplier_id),
                        INDEX idx_ist_scan_count (scan_count DESC)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->tableReady = true;
            } catch (\PDOException $e2) {
                error_log('TemplateLearner::ensureTable create failed: ' . $e2->getMessage());
                $this->tableReady = true; // Prevent infinite loop
            }
        }
    }
}
