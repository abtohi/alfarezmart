<?php
/**
 * SelfCorrectionEngine
 *
 * Triggered when:
 *   - One or more items have confidence < CORRECTION_THRESHOLD
 *   - Or validation_failed = true on one or more items
 *
 * Strategy:
 *   1. Build a targeted correction prompt that describes the problem
 *   2. Make a second AI call with the same image + correction context
 *   3. Re-run the full LayoutAnalyzer → TableParser → Validator pipeline on the result
 *   4. Compare original vs corrected items per item
 *   5. Keep the version with higher confidence per item
 *
 * Max correction attempts: 1 (to avoid timeout)
 *
 * @package AlfarezMart\Services\Invoice
 */
class SelfCorrectionEngine
{
    /** Maximum number of correction passes */
    const MAX_PASSES = 1;

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Run self-correction if needed.
     *
     * @param  array    $originalItems      Items with confidence scores
     * @param  bool     $hasLowConfidence
     * @param  array    $correctionHints    Hints from InvoiceValidator
     * @param  string   $imageB64           Original image (raw base64, no prefix)
     * @param  string   $apiKey             OpenRouter API key
     * @param  string   $modelName          OpenRouter model
     * @param  callable $promptBuilderFn    fn(correctionHints): array{system, user}
     * @param  callable $aiCallFn           fn(system, user, imageB64): array (parsed AI response)
     * @param  callable $pipelineFn         fn(aiResponse): array of items (with confidence)
     * @return array  Final items (merged from original + correction)
     */
    public function correct(
        array $originalItems,
        bool $hasLowConfidence,
        array $correctionHints,
        string $imageB64,
        string $apiKey,
        string $modelName,
        callable $promptBuilderFn,
        callable $aiCallFn,
        callable $pipelineFn
    ): array {
        // Only trigger if we actually have something to correct
        if (!$hasLowConfidence && empty($correctionHints)) {
            return $originalItems;
        }

        // Identify items that need correction
        $needsCorrectionNames = [];
        foreach ($originalItems as $item) {
            if (($item['needs_review'] ?? false) || ($item['validation_failed'] ?? false)) {
                $needsCorrectionNames[] = $item['name'] ?? '';
            }
        }

        if (empty($needsCorrectionNames)) {
            return $originalItems;
        }

        try {
            // 1. Build correction prompt
            $prompts = $promptBuilderFn($correctionHints);

            // 2. Make second AI call
            $rawCorrectedResponse = $aiCallFn(
                $prompts['system'],
                $prompts['user'],
                $imageB64,
                $apiKey,
                $modelName
            );

            if (empty($rawCorrectedResponse)) {
                // Second call failed or returned empty — keep originals
                return $originalItems;
            }

            // 3. Run pipeline on corrected response
            $correctedItems = $pipelineFn($rawCorrectedResponse);

            if (empty($correctedItems)) {
                return $originalItems;
            }

            // 4. Merge: keep the better version per item
            $merged = $this->mergeItemSets($originalItems, $correctedItems);

            return $merged;

        } catch (\Throwable $e) {
            // Self-correction failed — gracefully return originals
            error_log('SelfCorrectionEngine: correction pass failed: ' . $e->getMessage());
            return $originalItems;
        }
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Merge two item sets, keeping the version with higher confidence per item.
     *
     * Items are matched by name similarity.
     * Items in original but not in corrected are kept.
     * Items in corrected but not in original are added.
     */
    private function mergeItemSets(array $original, array $corrected): array
    {
        if (empty($corrected)) return $original;

        $result  = [];
        $usedIdx = [];

        foreach ($original as $origItem) {
            $origName    = strtolower(trim($origItem['name'] ?? ''));
            $origConf    = $origItem['confidence']['final'] ?? 0;
            $bestMatch   = null;
            $bestSimIdx  = -1;
            $bestSim     = 0;

            // Find best matching item in corrected set
            foreach ($corrected as $idx => $corrItem) {
                if (isset($usedIdx[$idx])) continue;

                $corrName = strtolower(trim($corrItem['name'] ?? ''));
                similar_text($origName, $corrName, $sim);

                if ($sim > $bestSim) {
                    $bestSim    = $sim;
                    $bestMatch  = $corrItem;
                    $bestSimIdx = $idx;
                }
            }

            if ($bestMatch !== null && $bestSim >= 60) {
                // Found a corresponding corrected item
                $usedIdx[$bestSimIdx] = true;
                $corrConf = $bestMatch['confidence']['final'] ?? 0;

                if ($corrConf > $origConf + 0.05) {
                    // Corrected version is meaningfully better — use it
                    $bestMatch['_self_corrected'] = true;
                    $result[] = $bestMatch;
                } else {
                    // Keep original
                    $result[] = $origItem;
                }
            } else {
                // No matching corrected item — keep original
                $result[] = $origItem;
            }
        }

        // Add any NEW items found in corrected that weren't in original
        foreach ($corrected as $idx => $corrItem) {
            if (!isset($usedIdx[$idx])) {
                $corrItem['_from_correction'] = true;
                $result[] = $corrItem;
            }
        }

        return $result;
    }
}
