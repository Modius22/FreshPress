<?php
/**
 * Bootstrap file for setting the ABSPATH constant.
 *
 * If the app/config/parameters.yml file is not found then an error
 * will be displayed asking the visitor to set up the
 * app/config/parameters.yml file.
 *
 * @package WordPress
 */

use Devtronic\FreshPress\DependencyInjection\ServiceContainer;

/** Include FreshPress Bootstrap */
require_once(__DIR__ . '/../src/Bootstrap.php');

/** Define ABSPATH as this file's directory */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);

/*
 * If the app/config/parameters.yml does not exist, initiate loading the setup process.
 */
if (file_exists(ABSPATH . '../app/config/parameters.yml')) {


    /** Sets up WordPress vars and included files. */
    require_once(__DIR__ . '/wp-settings.php');
    /** @var Twig_Loader_Filesystem $loader */
    $loader = ServiceContainer::getInstance()->get('twig_loader');
    $loader->addPath(get_template_directory(), 'Theme');
} else {

    // A config file doesn't exist
    define('WPINC', 'wp-includes');
    require_once(ABSPATH . WPINC . '/load.php');

    // Standardize $_SERVER variables across setups.
    wp_fix_server_vars();

    require_once(ABSPATH . WPINC . '/functions.php');

    $path = wp_guess_url() . '/wp-admin/setup-config.php';

    /*
     * We're going to redirect to setup-config.php. While this shouldn't result
     * in an infinite loop, that's a silly thing to assume, don't you think? If
     * we're traveling in circles, our last-ditch effort is "Need more help?"
     */
    if (false === strpos($_SERVER['REQUEST_URI'], 'setup-config')) {
        header('Location: ' . $path);
        exit;
    }

    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    require_once(ABSPATH . WPINC . '/version.php');

    wp_check_php_mysql_versions();
    wp_load_translations_early();

    // Die with an error message
    $die = sprintf(
        /* translators: %s: app/config/parameters.yml */
            __("There doesn't seem to be a %s file. I need this before we can get started."),
            '<code>app/config/parameters.yml</code>'
        ) . '</p>';
    $die .= '<p>' . sprintf(
        /* translators: %s: app/config/parameters.yml */
            __("You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file."),
            '<code>app/config/parameters.yml</code>'
        ) . '</p>';
    $die .= '<p><a href="' . $path . '" class="button button-large">' . __("Create a Configuration File") . '</a>';

    wp_die($die, __('WordPress &rsaquo; Error'));
}
