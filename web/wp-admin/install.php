<?php
/**
 * FreshPress Installer
 *
 * @package FreshPress
 * @subpackage Administration
 */

use Devtronic\FreshPress\Components\I18n\Locale;
use Devtronic\FreshPress\DependencyInjection\ServiceContainer;

/**
 * We are installing FreshPress.
 *
 * @since 1.5.1
 * @var bool
 */
define('WP_INSTALLING', true);

/** Load FreshPress Bootstrap */
require_once(dirname(dirname(__FILE__)) . '/wp-load.php');

/** Load FreshPress Administration Upgrade API */
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/** Load FreshPress Translation Install API */
require_once(ABSPATH . 'wp-admin/includes/translation-install.php');

nocache_headers();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;

/**
 * Returns the install base template parameters
 *
 * @param array $bodyClasses
 * @return array
 */
function getTemplateParameters($bodyClasses = [], $scripts_to_print = [])
{
    $bodyClasses = (array)$bodyClasses;
    $bodyClasses[] = 'wp-core-ui';
    if (is_rtl()) {
        $bodyClasses[] = 'rtl';
    }

    ob_start();
    wp_admin_css('install', true);
    wp_admin_css('dashicons', true);
    $styles = ob_get_contents();
    ob_end_clean();

    ob_start();
    wp_print_scripts($scripts_to_print);
    $scripts = ob_get_contents();
    ob_end_clean();

    return [
        'body_classes' => $bodyClasses,
        'direction' => (is_rtl() ? 'dir="rtl"' : ''),
        'styles' => $styles,
        'scripts' => $scripts,
        'project_url' => esc_url(__('https://wordpress.org/')),
        'is_mobile' => wp_is_mobile(),
    ];
}

/**
 * Display installer setup form.
 *
 * @since 2.8.0
 *
 * @param string|null $error
 */
function getSetupFormParameters($error = null)
{
    global $wpdb;

    $sql = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($wpdb->users));
    $user_table = ($wpdb->get_var($sql) != null);

    // Ensure that Blogs appear in search engines by default.
    $blog_public = 1;
    if (isset($_POST['weblog_title'])) {
        $blog_public = isset($_POST['blog_public']);
    }

    $weblog_title = isset($_POST['weblog_title']) ? trim(wp_unslash($_POST['weblog_title'])) : '';
    $user_name = isset($_POST['user_name']) ? trim(wp_unslash($_POST['user_name'])) : '';
    $admin_email = isset($_POST['admin_email']) ? trim(wp_unslash($_POST['admin_email'])) : '';

    ob_start();
    do_action('blog_privacy_selector');
    $privacySelector = ob_get_contents();
    ob_end_clean();
    $context = [
        'setup_form' => true,
        'error' => $error,
        'blog_title' => $weblog_title,
        'user_exists' => $user_table,
        'username' => sanitize_user($user_name, true),
        'password' => (isset($_POST['admin_password']) ? stripslashes($_POST['admin_password']) : wp_generate_password(18)),
        'password_masked' => (int)isset($_POST['admin_password']),
        'admin_mail' => $admin_email,
        'has_privacy_selector' => has_action('blog_privacy_selector'),
        'blog_public' => $blog_public,
        'privacy_selector' => $privacySelector,
        'language' => (isset($_REQUEST['language']) ? $_REQUEST['language'] : '')
    ];

    return $context;
}

// Let's check to make sure WP isn't already installed.
if (is_blog_installed()) {
    $context = getTemplateParameters();
    $context['login_url'] = wp_login_url();
    echo $twig->render('installer/error_already_installed.html.twig', $context);
    exit;
}

/**
 * @global string $wp_version
 * @global string $required_php_version
 * @global string $required_mysql_version
 * @global wpdb $wpdb
 */
global $wp_version, $required_php_version, $required_mysql_version;

$php_version = phpversion();
$mysql_version = $wpdb->db_version();
$php_compat = version_compare($php_version, $required_php_version, '>=');
$mysql_compat = version_compare(
        $mysql_version,
        $required_mysql_version,
        '>='
    ) || file_exists(WP_CONTENT_DIR . '/db.php');

if (!$mysql_compat || !$php_compat) {
    $compat = sprintf(
        __('You cannot install because FreshPress %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'),
        $wp_version,
        $required_php_version,
        $required_mysql_version,
        $php_version,
        $mysql_version
    );
}

if (!$mysql_compat || !$php_compat) {
    $context = getTemplateParameters();
    $context['message'] = $compat;

    echo $twig->render('installer/error_requirements.html.twig', $context);
    exit;
}
if (!is_string($wpdb->base_prefix) || '' === $wpdb->base_prefix) {
    $context = getTemplateParameters();
    $format =__('Your %s file has an empty database table prefix, which is not supported.');
    $context['message'] = sprintf($format, '<code>app/config/parameters.yml</code>');

    echo $twig->render('installer/error_configuration.html.twig', $context);
    exit;
}

// Set error message if DO_NOT_UPGRADE_GLOBAL_TABLES isn't set as it will break install.
if (defined('DO_NOT_UPGRADE_GLOBAL_TABLES')) {
    $context = getTemplateParameters();
    $format =__('The constant %s cannot be defined when installing FreshPress.');
    $context['message'] = sprintf($format, '<code>DO_NOT_UPGRADE_GLOBAL_TABLES</code>');

    echo $twig->render('installer/error_configuration.html.twig', $context);
    exit;
}

/**
 * @global string $wp_local_package
 * @global Locale $wp_locale
 */
$language = '';
if (!empty($_REQUEST['language'])) {
    $language = preg_replace('/[^a-zA-Z_]/', '', $_REQUEST['language']);
} elseif (isset($GLOBALS['wp_local_package'])) {
    $language = $GLOBALS['wp_local_package'];
}

$scripts_to_print = ['jquery'];

/** @var Twig_Environment $twig */
$twig = ServiceContainer::getInstance()->get('twig');

if ($step == 0 && wp_can_install_language_pack() && empty($language) && ($languages = wp_get_available_translations())) {
    $scripts_to_print[] = 'language-chooser';
    $context = getTemplateParameters('language-chooser', $scripts_to_print);

    ob_start();
    wp_install_language_form($languages);
    $languageForm = ob_get_contents();
    ob_end_clean();
    $context['language_form'] = $languageForm;
    $context['next_step'] = 1;
    echo $twig->render('installer/select_language.html.twig', $context);
} elseif ($step == 0 || $step == 1) {
    if (!empty($language)) {
        $loaded_language = wp_download_language_pack($language);
        if ($loaded_language) {
            load_default_textdomain($loaded_language);
            $GLOBALS['wp_locale'] = new Locale();
        }
    }

    $scripts_to_print[] = 'user-profile';
    $context = getTemplateParameters('', $scripts_to_print);
    $setupContext = getSetupFormParameters();

    echo $twig->render('installer/setup.html.twig', $context + $setupContext);
} elseif ($step == 2) {
    $loaded_language = 'en_US';
    if (!empty($language) && load_default_textdomain($language)) {
        $loaded_language = $language;
        $GLOBALS['wp_locale'] = new Locale();
    }

    if (!empty($wpdb->error)) {
        $message = $wpdb->error;
        if (!is_string($message)) {
            $message = $message->get_error_message();
        }
        wp_die($message);
    }

    $scripts_to_print[] = 'user-profile';
    $context = getTemplateParameters('', $scripts_to_print);

    // Fill in the data we gathered
    $weblog_title = isset($_POST['weblog_title']) ? trim(wp_unslash($_POST['weblog_title'])) : '';
    $user_name = isset($_POST['user_name']) ? trim(wp_unslash($_POST['user_name'])) : '';
    $admin_password = isset($_POST['admin_password']) ? wp_unslash($_POST['admin_password']) : '';
    $admin_password_check = isset($_POST['admin_password2']) ? wp_unslash($_POST['admin_password2']) : '';
    $admin_email = isset($_POST['admin_email']) ? trim(wp_unslash($_POST['admin_email'])) : '';
    $public = isset($_POST['blog_public']) ? (int)$_POST['blog_public'] : 1;

    // Check email address.
    $error = false;
    if (empty($user_name)) {
        $error = 'Please provide a valid username.';
    } elseif ($user_name != sanitize_user($user_name, true)) {
        $error = 'The username you provided has invalid characters.';
    } elseif ($admin_password != $admin_password_check) {
        $error = 'Your passwords do not match. Please try again.';
    } elseif (empty($admin_email)) {
        $error = 'You must provide an email address.';
    } elseif (!is_email($admin_email)) {
        $error = 'Sorry, that isn&#8217;t a valid email address. Email addresses look like <code>username@example.com</code>.';
    }

    if ($error !== false) {
        $context += getSetupFormParameters($error);
    } else {
        ob_start();
        $wpdb->show_errors();
        $result = wp_install(
            $weblog_title,
            $user_name,
            $admin_email,
            $public,
            '',
            wp_slash($admin_password),
            $loaded_language
        );
        $context['setup_result'] = ob_get_contents();
        ob_end_clean();

        $context['username'] = sanitize_user($user_name, true);
        $context['login_url'] = esc_url(wp_login_url());
        $context['generated_password'] = (empty($admin_password_check) ? $result['password'] : '');
        $context['password_message'] = $result['password_message'];
    }
    echo $twig->render('installer/setup.html.twig', $context);
}
