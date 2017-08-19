<?php
/**
 * The User Interface "Skins" for the WordPress File Upgrader
 *
 * @package WordPress
 * @subpackage Upgrader
 * @since 2.8.0
 */

_deprecated_file(basename(__FILE__), '4.7.0', 'class-wp-upgrader.php');

/** Bulk_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-upgrader-skin.php';

/** Bulk_Plugin_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-plugin-upgrader-skin.php';

/** Bulk_Theme_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-theme-upgrader-skin.php';
