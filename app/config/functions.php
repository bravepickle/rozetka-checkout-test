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
