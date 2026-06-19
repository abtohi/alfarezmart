<?php
/**
 * ImagePreprocessor
 *
 * Validates and analyzes the image before sending it to AI.
 * Does NOT do actual pixel-level processing (that's handled by the
 * canvas in the frontend). Instead it:
 *   1. Validates the base64 string
 *   2. Estimates resolution / size
 *   3. Detects image format
 *   4. Generates quality hints that are appended to the AI prompt
 *
 * @package AlfarezMart\Services\Invoice
 */
class ImagePreprocessor
{
    /** Maximum accepted payload size in bytes (5 MB decoded) */
    const MAX_SIZE_BYTES = 5 * 1024 * 1024;

    /** Minimum acceptable estimated pixel count (width × height) */
    const MIN_PIXELS = 200_000; // ~450×450

    /**
     * Validate the base64 image and return analysis metadata.
     *
     * @param  string $imageB64  Raw base64 string (no data-URI prefix)
     * @return array{
     *   valid: bool,
     *   error: string|null,
     *   format: string,
     *   estimated_bytes: int,
     *   hints: string[]
     * }
     */
    public function analyze(string $imageB64): array
    {
        $result = [
            'valid'           => true,
            'error'           => null,
            'format'          => 'jpeg',
            'estimated_bytes' => 0,
            'hints'           => [],
        ];

        // --- 1. Basic non-empty check ---
        $imageB64 = trim($imageB64);
        if (empty($imageB64)) {
            return array_merge($result, ['valid' => false, 'error' => 'Gambar invoice kosong']);
        }

        // --- 2. Strip data-URI prefix if still present ---
        if (strpos($imageB64, 'base64,') !== false) {
            $imageB64 = substr($imageB64, strpos($imageB64, 'base64,') + 7);
        }

        // --- 3. Validate base64 characters ---
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $imageB64)) {
            return array_merge($result, [
                'valid' => false,
                'error' => 'Format base64 gambar tidak valid',
            ]);
        }

        // --- 4. Estimate decoded byte size ---
        // base64 encodes 3 bytes as 4 chars, so decoded_bytes ≈ len * 0.75
        $estimatedBytes = (int)(strlen($imageB64) * 0.75);
        $result['estimated_bytes'] = $estimatedBytes;

        if ($estimatedBytes > self::MAX_SIZE_BYTES) {
            return array_merge($result, [
                'valid' => false,
                'error' => sprintf(
                    'Ukuran gambar terlalu besar (%.1f MB). Maksimum 5 MB. Gunakan resolusi lebih rendah.',
                    $estimatedBytes / (1024 * 1024)
                ),
            ]);
        }

        // --- 5. Detect image format from magic bytes ---
        $headerBytes = base64_decode(substr($imageB64, 0, 16));
        if ($headerBytes !== false) {
            if (substr($headerBytes, 0, 2) === "\xFF\xD8") {
                $result['format'] = 'jpeg';
            } elseif (substr($headerBytes, 0, 4) === "\x89PNG") {
                $result['format'] = 'png';
            } elseif (substr($headerBytes, 0, 4) === 'RIFF' || substr($headerBytes, 8, 4) === 'WEBP') {
                $result['format'] = 'webp';
            } elseif (substr($headerBytes, 0, 4) === "\x47\x49\x46\x38") {
                $result['format'] = 'gif';
            }
        }

        // --- 6. Generate quality hints for AI prompt ---
        $hints = [];

        if ($estimatedBytes < 50_000) {
            $hints[] = 'PERINGATAN: Ukuran gambar sangat kecil. Teks mungkin tidak terbaca jelas.';
        } elseif ($estimatedBytes < 150_000) {
            $hints[] = 'Gambar berukuran kecil. Perhatikan teks yang mungkin buram.';
        } elseif ($estimatedBytes > 3 * 1024 * 1024) {
            $hints[] = 'Gambar beresolusi tinggi — hasil OCR seharusnya sangat baik.';
        }

        if ($result['format'] === 'webp') {
            $hints[] = 'Format: WebP (hasil kamera/canvas browser).';
        } elseif ($result['format'] === 'jpeg') {
            $hints[] = 'Format: JPEG.';
        }

        $result['hints'] = $hints;
        return $result;
    }

    /**
     * Build the image_url block for the OpenRouter API payload.
     *
     * @param  string $imageB64  Raw base64 (no prefix)
     * @param  string $format    'jpeg' | 'png' | 'webp'
     * @return array
     */
    public function buildImageUrlBlock(string $imageB64, string $format = 'jpeg'): array
    {
        // Strip data-URI prefix if still present
        if (strpos($imageB64, 'base64,') !== false) {
            $imageB64 = substr($imageB64, strpos($imageB64, 'base64,') + 7);
        }

        // Always use standard MIME for maximum compatibility with vision models
        $mime = in_array($format, ['png', 'webp', 'gif']) ? "image/{$format}" : 'image/jpeg';

        return [
            'type'      => 'image_url',
            'image_url' => ['url' => "data:{$mime};base64,{$imageB64}"],
        ];
    }
}
