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
$serviceContainer->loadYAML(__DIR__ . '/../app/config/services.yml');