<?php
# CustomCode

/**
 * FreshPress Bootstrapper
 */

use Devtronic\FreshPress\Core\Twig\CoreExtension;
use Devtronic\FreshPress\DependencyInjection\ServiceContainer;

require_once __DIR__ . '/../vendor/autoload.php';

/** Wrapper for back compatibility */
require_once(__DIR__ . '/Core/WP2FPWrapper.php');

// Setup ServiceContainer
$serviceContainer = ServiceContainer::getInstance();
$serviceContainer->addParameter('core.install_path', dirname(__DIR__));

if (is_file(__DIR__ . '/../app/config/parameters.yml')) {
    $serviceContainer->loadParametersYAML(__DIR__ . '/../app/config/parameters.yml');

    $table_prefix = ServiceContainer::getInstance()->getParameter('database.prefix');
    define('WP_DEBUG', false);
    define('WP_AUTO_UPDATE_CORE', false);
}

$serviceContainer->loadYAML(__DIR__ . '/../app/config/services.yml');
/** @var Twig_Environment $twig */
$twig = $serviceContainer->get('twig');

$twig->addExtension(new CoreExtension());
