<?php
/**
 * Creates common globals for the rest of WordPress
 *
 * Sets $pagenow global which is the current page. Checks
 * for the browser to set which one is currently being used.
 *
 * Detects which user environment WordPress is being used on.
 * Only attempts to check for Apache, Nginx and IIS -- three web
 * servers with known pretty permalink capability.
 *
 * Note: Though Nginx is detected, WordPress does not currently
 * generate rewrite rules for it. See https://codex.wordpress.org/Nginx
 *
 * @package WordPress
 */

global $pagenow, $is_lynx, $is_gecko, $is_winIE, $is_macIE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone, $is_IE, $is_edge, $is_apache, $is_IIS, $is_iis7, $is_nginx;

// On which page are we ?
$pagenow = 'index.php';
if (preg_match('#([^/]+\.php)([?/].*?)?$#i', $_SERVER['PHP_SELF'], $self_matches)) {
    $pagenow = strtolower($self_matches[1]);
}

if (is_admin()) {
    // wp-admin pages are checked more carefully
    preg_match('#/wp-admin/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches);
    if (is_network_admin()) {
        preg_match('#/wp-admin/network/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches);
    } elseif (is_user_admin()) {
        preg_match('#/wp-admin/user/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches);
    }

    $pagenow = 'index.php';

    $tempPageNow = $self_matches[1];
    $tempPageNow = trim($tempPageNow, '/');
    $tempPageNow = preg_replace('#\?.*?$#', '', $tempPageNow);
    if (!in_array($tempPageNow, ['', 'index', 'index.php'])) {
        preg_match('#(.*?)(/|$)#', $tempPageNow, $self_matches);
        $pagenow = strtolower($self_matches[1]);
        if ('.php' !== substr($pagenow, -4, 4)) {
            $pagenow .= '.php';
        }
        // for Options +Multiviews: /wp-admin/themes/index.php (themes.php is queried)
    }
}
unset($self_matches);

// Simple browser detection
$browserConditions = [
    'Lynx' => $is_lynx = (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false),
    'Edge' => $is_edge = (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false),
    'Chrome' => $is_chrome = (stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false),
    'Safari' => $is_safari = (stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false),
    'WinIE' => $is_winIE = (
        (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false)
        && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false
    ),
    'MacIE' => $is_macIE = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false
        && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false),
    'Gecko' => $is_gecko = (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false),
    'Opera' => $is_opera = (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false),
    'NetScape' => $is_NS4 = (
        strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false
        && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false
    ),
];

$browserConditions['iPhone'] = $is_iphone = ($browserConditions['Safari'] &&
    stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false);
$browserConditions['IE'] = $is_IE = ($browserConditions['WinIE'] || $browserConditions['MacIE']);

// Server detection

$serverConditions = [
    'Apache' => $is_apache = (
        strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false
        || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false
    ),
    'nginx' => $is_nginx = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false),
    'IIS' => $is_IIS = (
        !$is_apache && (
            strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false
            || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false
        )
    ),
    'IIS7' => $is_iis7 = $is_IIS &&
        (intval(substr($_SERVER['SERVER_SOFTWARE'], strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/') + 14)) >= 7),
];

/**
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @since 3.4.0
 *
 * @return bool
 */
function wp_is_mobile()
{
    $isMobile = false;
    $mobileAgents = ['Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi'];

    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        foreach ($mobileAgents as $mobileAgent) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $mobileAgent) !== false) {
                $isMobile = true;
                break;
            }
        }
    }

    return $isMobile;
}
