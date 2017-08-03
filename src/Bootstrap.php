<?php
# CustomCode

/**
 * FreshPress Bootstrapper
 */

use Devtronic\FreshPress\DependencyInjection\ServiceContainer;

require_once __DIR__ . '/../vendor/autoload.php';

/** Wrapper for back compatibility */
require_once(__DIR__ . '/Core/WP2FPWrapper.php');

// Setup ServiceContainer
$serviceContainer = ServiceContainer::getInstance();

if (is_file(__DIR__ . '/../app/config/parameters.yml')) {
    $serviceContainer->loadParametersYAML(__DIR__ . '/../app/config/parameters.yml');
}

$serviceContainer->loadYAML(__DIR__ . '/../app/config/services.yml');