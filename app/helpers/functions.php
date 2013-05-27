<?php
/**
 * Helpful functions.
 */

/**
 * Returns the array value at the given key, or the default value if not present.
 *
 * @param array $array  the array to search
 * @param string $key  the key for the value
 * @param object $default  the value to return if the key does not exist
 * @return object  the value at the key, or the default value
 */
function ifseta($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}
