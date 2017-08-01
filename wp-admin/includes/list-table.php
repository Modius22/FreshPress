<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

use Devtronic\FreshPress\Components\ListTables\CommentsListTable;
use Devtronic\FreshPress\Components\ListTables\LinksListTable;
use Devtronic\FreshPress\Components\ListTables\MediaListTable;
use Devtronic\FreshPress\Components\ListTables\MSSitesListTable;
use Devtronic\FreshPress\Components\ListTables\PluginInstallListTable;
use Devtronic\FreshPress\Components\ListTables\PluginsListTable;
use Devtronic\FreshPress\Components\ListTables\PostCommentsListTable;
use Devtronic\FreshPress\Components\ListTables\PostsListTable;
use Devtronic\FreshPress\Components\ListTables\TermsListTable;
use Devtronic\FreshPress\Components\ListTables\ThemeInstallListTable;
use Devtronic\FreshPress\Components\ListTables\ThemesListTable;
use Devtronic\FreshPress\Components\ListTables\UsersListTable;

/**
 * Fetch an instance of a ListTable class.
 *
 * @access private
 * @since 3.1.0
 *
 * @global string $hook_suffix
 *
 * @param string $class The type of the list table, which is the class name.
 * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
 * @return object|bool Object on success, false if the class does not exist.
 */
function _get_list_table($class, $args = array())
{
    $core_classes = array(
        //Site Admin
        PostsListTable::class => 'posts',
        MediaListTable::class => 'media',
        TermsListTable::class => 'terms',
        UsersListTable::class => 'users',
        CommentsListTable::class => 'comments',
        PostCommentsListTable::class => array('comments', 'post-comments'),
        LinksListTable::class => 'links',
        PluginInstallListTable::class => 'plugin-install',
        ThemesListTable::class => 'themes',
        ThemeInstallListTable::class => array('themes', 'theme-install'),
        PluginsListTable::class => 'plugins',
        // Network Admin
        MSSitesListTable::class => 'ms-sites',
        'WP_MS_Users_List_Table' => 'ms-users',
        'WP_MS_Themes_List_Table' => 'ms-themes',
    );

    if (isset($core_classes[$class])) {
        // @todo remove after every list is psr-4
        if(!stristr($class, '\\')){
            foreach ((array)$core_classes[$class] as $required) {
                require_once(ABSPATH . 'wp-admin/includes/class-wp-' . $required . '-list-table.php');
            }
        }

        if (isset($args['screen'])) {
            $args['screen'] = convert_to_screen($args['screen']);
        } elseif (isset($GLOBALS['hook_suffix'])) {
            $args['screen'] = get_current_screen();
        } else {
            $args['screen'] = null;
        }

        return new $class($args);
    }

    return false;
}

/**
 * Register column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string $screen The handle for the screen to add help to. This is usually the hook name returned by the add_*_page() functions.
 * @param array $columns An array of columns with column IDs as the keys and translated column names as the values
 * @see get_column_headers(), print_column_headers(), get_hidden_columns()
 */
function register_column_headers($screen, $columns)
{
    new _WP_List_Table_Compat($screen, $columns);
}

/**
 * Prints column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string|WP_Screen $screen The screen hook name or screen object.
 * @param bool $with_id Whether to set the id attribute or not.
 */
function print_column_headers($screen, $with_id = true)
{
    $wp_list_table = new _WP_List_Table_Compat($screen);

    $wp_list_table->print_column_headers($with_id);
}
