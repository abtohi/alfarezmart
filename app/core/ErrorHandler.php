<?php
/**
 * ErrorHandler - Centralized Error & Exception Handler
 * 
 * Captures all uncaught errors, exceptions, and fatal shutdowns.
 * Ensures APIs always return structured JSON (never broken HTML) and logs all traces.
 * 
 * @package AlfarezMart\Core
 */
class ErrorHandler
{
    private static $registered = false;

    /**
     * Daftarkan seluruh global error handler
     */
    public static function register(): void
    {
        if (self::$registered) return;
        self::$registered = true;

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP Notice / Warning / Error
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Don't log if suppressed with @
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $level = in_array($severity, [E_USER_ERROR, E_RECOVERABLE_ERROR]) ? Logger::LEVEL_ERROR : Logger::LEVEL_WARNING;

        Logger::log($level, "PHP Error: {$message}", [
            'file'     => $file,
            'line'     => $line,
            'severity' => $severity,
        ]);

        // Throw ErrorException for critical errors so exception handler catches it
        if ($severity === E_USER_ERROR || $severity === E_RECOVERABLE_ERROR) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }

        return false;
    }

    /**
     * Handle Uncaught Exceptions
     */
    public static function handleException(Throwable $e): void
    {
        $errorId = 'ERR_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);

        Logger::critical("Uncaught Exception [{$errorId}]: " . $e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 6),
        ]);

        self::renderErrorResponse($e, $errorId);
    }

    /**
     * Handle Fatal PHP Shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorId = 'FATAL_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);

            Logger::critical("Fatal Shutdown [{$errorId}]: {$error['message']}", [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type'],
            ]);

            self::renderErrorResponse(
                new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']),
                $errorId
            );
        }
    }

    /**
     * Render clean error response (JSON for API, HTML for web)
     */
    private static function renderErrorResponse(Throwable $e, string $errorId): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code(500);

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isApi = (strpos($uri, '/api/') !== false) || (strpos($accept, 'application/json') !== false);
        $isDebug = (defined('APP_DEBUG') && (APP_DEBUG === 'true' || APP_DEBUG === true));

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            $response = [
                'success'    => false,
                'error'      => $isDebug ? $e->getMessage() : 'Terjadi kesalahan sistem internal pada server. Silakan coba kembali.',
                'error_code' => $errorId,
            ];
            if ($isDebug) {
                $response['file'] = $e->getFile() . ':' . $e->getLine();
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Web view error display
        $view500 = (defined('APP_PATH') ? APP_PATH : __DIR__ . '/..') . '/views/errors/500.php';
        if (file_exists($view500)) {
            $errorTitle = '500 - Kesalahan Sistem Internal';
            $errorMessage = $isDebug ? $e->getMessage() : 'Sistem mengalami kendala saat memproses permintaan Anda.';
            include $view500;
        } else {
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>500 - Kesalahan Server</title>";
            echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
            echo "<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}";
            echo ".box{background:#1e293b;padding:32px;border-radius:16px;max-width:440px;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,0.4);border:1px solid #334155;}";
            echo "h1{color:#ef4444;margin:0 0 10px;font-size:24px;}p{color:#94a3b8;font-size:14px;line-height:1.6;}";
            echo "a{display:inline-block;margin-top:16px;padding:10px 20px;background:#ef4444;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;}";
            echo "</style></head><body><div class='box'>";
            echo "<h1>⚠️ Terjadi Kendala Sistem</h1>";
            echo "<p>Permintaan Anda tidak dapat diproses saat ini.<br><small style='color:#64748b;'>Kode Error: {$errorId}</small></p>";
            if ($isDebug) {
                echo "<p style='text-align:left;font-size:12px;color:#fca5a5;background:#450a0a;padding:8px;border-radius:6px;word-break:break-all;'>" . htmlspecialchars($e->getMessage()) . "</p>";
            }
            echo "<a href='/'>Kembali ke Beranda</a></div></body></html>";
        }
        exit;
    }
}
