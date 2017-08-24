<?php
/**
 * Retrieves and creates the app/config/parameters.yml file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the app/config/parameters.yml to be created using this page.
 *
 * @package FreshPress
 * @subpackage Administration
 */

use Devtronic\FreshPress\DependencyInjection\ServiceContainer;
use Symfony\Component\Yaml\Yaml;

/** Include FreshPress Bootstrap */
require_once(__DIR__ . '/../../src/Bootstrap.php');

/**
 * We are installing.
 */
define('WP_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('WP_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging
 */
error_reporting(0);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(__FILE__)) . '/');
}

require(ABSPATH . 'wp-settings.php');

/** Load FreshPress Administration Upgrade API */
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/** Load FreshPress Translation Install API */
require_once(ABSPATH . 'wp-admin/includes/translation-install.php');

nocache_headers();
// Support wp-config-sample.php one level up, for the develop repo.
$parameters = '';
$parametersFile = ABSPATH . '../app/config/parameters.yml';
if (file_exists(ABSPATH . '../app/config/parameters.dist.yml')) {
    $parameters = Yaml::parse(file_get_contents(ABSPATH . '../app/config/parameters.dist.yml'));
} else {
    wp_die(__('Sorry, I need a app/config/parameters.dist.yml file to work from. Please re-upload this file to your FreshPress installation.'));
}

// Check if app/config/parameters.yml has been created
if (file_exists(ABSPATH . '../app/config/parameters.yml')) {
    wp_die(
        '<p>' .
        sprintf(
            __("The file 'parameters.yml' already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='%s'>installing now</a>."),
            'install.php'
        ) . '</p>'
    );
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : -1;

/**
 * Returns the install base template parameters
 * @param array $bodyClasses
 * @return array
 */
function getTemplateParameters($bodyClasses = [])
{
    $bodyClasses = (array)$bodyClasses;
    $bodyClasses[] = 'wp-core-ui';
    if (is_rtl()) {
        $bodyClasses[] = 'rtl';
    }

    ob_start();
    wp_admin_css('install', true);
    $styles = ob_get_contents();
    ob_end_clean();

    ob_start();
    wp_print_scripts('language-chooser');
    $scripts = ob_get_contents();
    ob_end_clean();

    return [
        'body_classes' => $bodyClasses,
        'direction' => (is_rtl() ? 'dir="rtl"' : ''),
        'styles' => $styles,
        'scripts' => $scripts,
        'project_url' => esc_url(__('https://wordpress.org/')),
    ];
}

$language = '';
if (!empty($_REQUEST['language'])) {
    $language = preg_replace('/[^a-zA-Z_]/', '', $_REQUEST['language']);
} elseif (isset($GLOBALS['wp_local_package'])) {
    $language = $GLOBALS['wp_local_package'];
}

/** @var Twig_Environment $twig */
$twig = ServiceContainer::getInstance()->get('twig');

if ($step == -1 && wp_can_install_language_pack() && empty($language) && ($languages = wp_get_available_translations())) {
    $context = getTemplateParameters('language-chooser');
    $context['nextStep'] = 0;
    ob_start();
    wp_install_language_form($languages);
    $languageForm = ob_get_contents();
    ob_end_clean();
    $context['language_form'] = $languageForm;
    echo $twig->render('installer/select_language.html.twig', $context);
} elseif ($step == -1 || $step == 0) {
    if (!empty($language)) {
        $loaded_language = wp_download_language_pack($language);
        if ($loaded_language) {
            load_default_textdomain($loaded_language);
            $GLOBALS['wp_locale'] = new WP_Locale();
        }
    }

    $step_1 = 'setup-config.php?step=1';
    if (isset($_REQUEST['noapi'])) {
        $step_1 .= '&amp;noapi';
    }
    if (!empty($loaded_language)) {
        $step_1 .= '&amp;language=' . $loaded_language;
    }

    $context = getTemplateParameters();
    $context['config_file'] = 'app/config/parameters.yml';

    $context['next_step'] = $step_1;

    echo $twig->render('installer/introduction.html.twig', $context);
} elseif ($step == 1) {
    load_default_textdomain($language);
    $GLOBALS['wp_locale'] = new WP_Locale();

    $context = getTemplateParameters();
    $context['noapi'] = isset($_GET['noapi']);
    $context['language'] = $language;

    echo $twig->render('installer/setup_database.html.twig', $context);
} elseif ($step == 2) {
    load_default_textdomain($language);
    $GLOBALS['wp_locale'] = new WP_Locale();

    $dbname = trim(wp_unslash($_POST['dbname']));
    $uname = trim(wp_unslash($_POST['uname']));
    $pwd = trim(wp_unslash($_POST['pwd']));
    $dbhost = trim(wp_unslash($_POST['dbhost']));
    $prefix = trim(wp_unslash($_POST['prefix']));

    $step_1 = 'setup-config.php?step=1';
    $install = 'install.php';
    if (isset($_REQUEST['noapi'])) {
        $step_1 .= '&amp;noapi';
    }

    if (!empty($language)) {
        $step_1 .= '&amp;language=' . $language;
        $install .= '?language=' . $language;
    } else {
        $install .= '?language=en_US';
    }

    $tryagain_link = '</p><p class="step"><a href="' . $step_1 . '" onclick="javascript:history.go(-1);return false;" class="button button-large">' . __('Try again') . '</a>';

    if (empty($prefix)) {
        wp_die(__('<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link));
    }

    // Validate $prefix: it can only contain letters, numbers and underscores.
    if (preg_match('|[^a-z0-9_]|i', $prefix)) {
        wp_die(__('<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link));
    }


    // Test the db connection.
    /**#@+
     * @ignore
     */
    define('DB_NAME', $dbname);
    define('DB_USER', $uname);
    define('DB_PASSWORD', $pwd);
    define('DB_HOST', $dbhost);
    /**#@-*/

    // Re-construct $wpdb with these new values.
    unset($wpdb);
    $wpdb = new \Devtronic\FreshPress\Core\WPDB(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

    /*
     * The wpdb constructor bails when WP_SETUP_CONFIG is set, so we must
     * fire this manually. We'll fail here if the values are no good.
     */
    $wpdb->db_connect();
    if (!empty($wpdb->error)) {
        $message = $wpdb->error;
        if (!is_string($wpdb->error)) {
            $message = $message->get_error_message();
        }
        wp_die($message . $tryagain_link);
    }

    $wpdb->query("SELECT $prefix");
    if (!$wpdb->last_error) {
        // MySQL was able to parse the prefix as a value, which we don't want. Bail.
        wp_die(__('<strong>ERROR</strong>: "Table Prefix" is invalid.'));
    }

    // Generate keys and salts using secure CSPRNG; fallback to API if enabled; further fallback to original wp_generate_password().
    $secret_keys = [];
    try {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < 8; $i++) {
            $key = '';
            for ($j = 0; $j < 64; $j++) {
                $key .= substr($chars, random_int(0, $max), 1);
            }
            $secret_keys[] = $key;
        }
    } catch (Exception $ex) {
        $no_api = isset($_POST['noapi']);

        if (!$no_api) {
            $secret_keys = wp_remote_get('https://api.wordpress.org/secret-key/1.1/salt/');
        }

        if ($no_api || is_wp_error($secret_keys)) {
            $secret_keys = [];
            for ($i = 0; $i < 8; $i++) {
                $secret_keys[] = wp_generate_password(64, true, true);
            }
        } else {
            $secret_keys = explode("\n", wp_remote_retrieve_body($secret_keys));
            foreach ($secret_keys as $k => $v) {
                $secret_keys[$k] = substr($v, 28, 64);
            }
        }
    }


    $parameters['parameters']['database']['prefix'] = $prefix;
    $parameters['parameters']['database']['host'] = DB_HOST;
    $parameters['parameters']['database']['user'] = DB_USER;
    $parameters['parameters']['database']['pass'] = DB_PASSWORD;
    $parameters['parameters']['database']['name'] = DB_NAME;

    if ('utf8mb4' === $wpdb->charset || (!$wpdb->charset && $wpdb->has_cap('utf8mb4'))) {
        $parameters['parameters']['database']['charset'] = 'utf8mb4';
    }

    $parameters['parameters']['security']['auth_key'] = $secret_keys[0];
    $parameters['parameters']['security']['secure_auth_key'] = $secret_keys[1];
    $parameters['parameters']['security']['logged_in_key'] = $secret_keys[2];
    $parameters['parameters']['security']['nonce_key'] = $secret_keys[3];
    $parameters['parameters']['security']['auth_salt'] = $secret_keys[4];
    $parameters['parameters']['security']['secure_auth_salt'] = $secret_keys[5];
    $parameters['parameters']['security']['logged_in_salt'] = $secret_keys[6];
    $parameters['parameters']['security']['nonce_salt'] = $secret_keys[7];

    $configContent = Yaml::dump($parameters, 3, 4);

    $context = getTemplateParameters();

    $context['config_file'] = $parametersFile;
    $context['install_link'] = $install;
    if (!is_writable(dirname($parametersFile))) {
        $context['parameters_data'] = $configContent;

        echo $twig->render('installer/config_creation_failed.html.twig', $context);
    } else {
        $configFile = ABSPATH . '../app/config/config.yml';
        $configTemplateFile = ABSPATH . '../app/config/config.dist.yml';
        if (!is_file($configFile) && is_file($configTemplateFile)) {
            copy($configTemplateFile, $configFile);
        }
        $handle = fopen($parametersFile, 'w');
        fputs($handle, $configContent);
        fclose($handle);
        chmod($parametersFile, 0666);

        echo $twig->render('installer/config_created.html.twig', $context);
    }
}
