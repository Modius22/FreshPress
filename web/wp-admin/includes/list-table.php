<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

use Devtronic\FreshPress\Components\Admin\Screen;
use Devtronic\FreshPress\Components\ListTables\CommentsListTable;
use Devtronic\FreshPress\Components\ListTables\LinksListTable;
use Devtronic\FreshPress\Components\ListTables\ListTableCompat;
use Devtronic\FreshPress\Components\ListTables\MediaListTable;
use Devtronic\FreshPress\Components\ListTables\MSSitesListTable;
use Devtronic\FreshPress\Components\ListTables\MSThemesListTable;
use Devtronic\FreshPress\Components\ListTables\MSUsersListTable;
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
        PostsListTable::class,
        MediaListTable::class,
        TermsListTable::class,
        UsersListTable::class,
        CommentsListTable::class,
        PostCommentsListTable::class,
        LinksListTable::class,
        PluginInstallListTable::class,
        ThemesListTable::class,
        ThemeInstallListTable::class,
        PluginsListTable::class,
        // Network Admin
        MSSitesListTable::class,
        MSUsersListTable::class,
        MSThemesListTable::class,
    );

    if (in_array($class, $core_classes)) {
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
    new ListTableCompat($screen, $columns);
}

/**
 * Prints column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string|Screen $screen The screen hook name or screen object.
 * @param bool $with_id Whether to set the id attribute or not.
 */
function print_column_headers($screen, $with_id = true)
{
    $wp_list_table = new ListTableCompat($screen);

    $wp_list_table->print_column_headers($with_id);
}
