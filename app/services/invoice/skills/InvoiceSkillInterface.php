<?php
/**
 * InvoiceSkillInterface
 *
 * Contract for all supplier-specific invoice AI scanning skills.
 * Allows modular support for different invoice layouts, column orders,
 * calculation formulas, and packaging level determinations.
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
interface InvoiceSkillInterface
{
    /**
     * Get the unique key/identifier of this supplier skill.
     * Example: 'mdr', 'general', 'indomarco', etc.
     */
    public function getSkillKey(): string;

    /**
     * Get the human-readable name of the supplier handled by this skill.
     */
    public function getSupplierName(): string;

    /**
     * Return keywords or patterns used to automatically detect this supplier.
     *
     * @return string[]
     */
    public function getDetectionSignatures(): array;

    /**
     * Build the system prompt specific to this supplier's invoice format.
     */
    public function getSystemPrompt(bool $isCorrectionPass = false): string;

    /**
     * Provide additional contextual hints for the user prompt.
     */
    public function getUserPromptHints(): string;

    /**
     * Parse raw extracted item according to supplier's specific column logic and formulas.
     *
     * @param array $rawItem Extracted JSON item from AI
     * @return array Normalized item with qty, unit_price, total_price, supplier_code, etc.
     */
    public function parseItem(array $rawItem): array;

    /**
     * Determine the best packaging level for this product based on:
     * 1. Price distance (unit_price vs packaging buy_price / last purchase history)
     * 2. Extracted unit text (fallback)
     *
     * @param float $unitPrice Calculated unit price (Total Amount / Qty)
     * @param array $packagings List of product packagings with buy_price and base_qty
     * @param string $extractedUnit Unit string from invoice (e.g. BOX, PCS, CTN)
     * @param float|null $lastBuyPrice Last recorded buy price from supplier history
     * @return array{packaging: array|null, level: int, strategy: string}
     */
    public function determinePackagingLevel(
        float $unitPrice,
        array $packagings,
        string $extractedUnit = '',
        ?float $lastBuyPrice = null
    ): array;
}
