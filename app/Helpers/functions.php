<?php
/**
 * Helper functions for the application
 */

/**
 * Get the base URL of the application
 *
 * @param string $path The path to append to the base URL
 * @return string The complete URL
 */
function base_url($path = '') {
    global $config;
    return $config['base_url'] . '/' . ltrim($path, '/');
}

/**
 * Get the URL for an asset
 *
 * @param string $path The path to the asset
 * @return string The complete URL to the asset
 */
function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to a URL
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Get a value from the session
 *
 * @param string $key The key to get
 * @param mixed $default The default value if the key doesn't exist
 * @return mixed The value from the session
 */
function session($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Set a value in the session
 *
 * @param string $key The key to set
 * @param mixed $value The value to set
 * @return void
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Remove a value from the session
 *
 * @param string $key The key to remove
 * @return void
 */
function session_remove($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Flash a message to the session
 *
 * @param string $type The type of message (success, error, info, warning)
 * @param string $message The message to flash
 * @return void
 */
function flash($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }

    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get all flash messages and clear them from the session
 *
 * @return array The flash messages
 */
function get_flashes() {
    $messages = isset($_SESSION['flash_messages']) ? $_SESSION['flash_messages'] : [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Escape HTML entities
 *
 * @param string $string The string to escape
 * @return string The escaped string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if the current user is authenticated
 *
 * @return bool True if the user is authenticated, false otherwise
 */
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user
 *
 * @return array|null The user data or null if not authenticated
 */
function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Debug function to print variables
 *
 * @param mixed $var The variable to print
 * @param bool $die Whether to die after printing
 * @return void
 */
function debug($var, $die = true) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Check if the current URL matches the given path
 *
 * @param string $path The path to check
 * @return bool True if the current URL matches the path, false otherwise
 */
function is_current_url($path) {
    // Get the current URL from the $_GET['url'] parameter
    $current_url = isset($_GET['url']) ? $_GET['url'] : '';

    // If path is empty, check if we're on the homepage
    if (empty($path)) {
        return empty($current_url);
    }

    // Remove trailing slashes for comparison
    $path = rtrim($path, '/');
    $current_url = rtrim($current_url, '/');

    // Check if the current URL starts with the given path
    return $current_url === $path || strpos($current_url, $path . '/') === 0;
}
