<?php
/**
 * Front controller
 *
 * This file handles all requests and routes them to the appropriate controller
 */

// Load configuration
require_once 'app/Config/config.php';

// Load helper functions
require_once 'app/Helpers/functions.php';

// Load Composer autoloader if it exists
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

// Autoload core classes
spl_autoload_register(function($className) {
    // Core classes
    if (file_exists('app/Core/' . $className . '.php')) {
        require_once 'app/Core/' . $className . '.php';
    }
});

// Initialize the application
$app = new App();
