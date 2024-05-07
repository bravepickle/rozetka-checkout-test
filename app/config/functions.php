<?php
/**
 * Introduce utility functions
 */

/**
 * Read ENV param with fallback to defined value
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function env(string $name, mixed $default = null): mixed {
    $valueFromEnv = getenv($name);

    return $valueFromEnv === false ? $default : $valueFromEnv;
}


/**
 * Log error
 * @param string $message
 * @param array $context
 * @return void
 */
function log_error(string $message, array $context = []): void
{
    if (!defined('APP_LOG') || !APP_LOG) {
        return;
    }

    try {
        $fh = fopen(APP_LOG, 'ab+');

        $log = sprintf(
            '[%s] app.ERROR: %s %s',
            date('Y-m-d H:i:s'),
            $message,
            $context ? json_encode(
                $context,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) : '{}'
        );

        fwrite($fh, $log . PHP_EOL);
    } catch (\Throwable $e) {
        trigger_error('[LOG ERROR] ' . $e->getMessage() . '. Message: ' . $message, E_USER_ERROR);
    } finally {
        fclose($fh);
    }
}
