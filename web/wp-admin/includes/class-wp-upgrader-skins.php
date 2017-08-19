<?php
/**
 * The User Interface "Skins" for the WordPress File Upgrader
 *
 * @package WordPress
 * @subpackage Upgrader
 * @since 2.8.0
 */

_deprecated_file(basename(__FILE__), '4.7.0', 'class-wp-upgrader.php');

/** Theme_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader-skin.php';

/** Bulk_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-upgrader-skin.php';

/** Bulk_Plugin_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-plugin-upgrader-skin.php';

/** Bulk_Theme_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-bulk-theme-upgrader-skin.php';

/** Plugin_Installer_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php';

/** Automatic_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

/** WP_Ajax_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
