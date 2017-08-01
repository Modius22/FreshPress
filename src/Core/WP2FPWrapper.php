<?php
/**
 * Wrapper to keep back compatibility to WordPress
 */

$classes = [
    'WP_Widget' => 'Devtronic\\FreshPress\\Components\\Widgets\\Widget',
    'WP_Nav_Menu_Widget' => 'Devtronic\\FreshPress\\Components\\Widgets\\NavMenuWidget',
    'WP_Widget_Archives' => 'Devtronic\\FreshPress\\Components\\Widgets\\ArchivesWidget',
    'WP_Widget_Calendar' => 'Devtronic\\FreshPress\\Components\\Widgets\\CalendarWidget',
    'WP_Widget_Categories' => 'Devtronic\\FreshPress\\Components\\Widgets\\CategoriesWidget',
    'WP_Widget_Links' => 'Devtronic\\FreshPress\\Components\\Widgets\\LinksWidget',
    'WP_Widget_Media_Audio' => 'Devtronic\\FreshPress\\Components\\Widgets\\AudioWidget',
    'WP_Widget_Media_Image' => 'Devtronic\\FreshPress\\Components\\Widgets\\ImageWidget',
    'WP_Widget_Media_Video' => 'Devtronic\\FreshPress\\Components\\Widgets\\VideoWidget',
    'WP_Widget_Meta' => 'Devtronic\\FreshPress\\Components\\Widgets\\MetaWidget',
    'WP_Widget_Pages' => 'Devtronic\\FreshPress\\Components\\Widgets\\PagesWidget',
    'WP_Widget_Recent_Comments' => 'Devtronic\\FreshPress\\Components\\Widgets\\RecentCommentsWidget',
    'WP_Widget_Recent_Posts' => 'Devtronic\\FreshPress\\Components\\Widgets\\RecentPostsWidget',
    'WP_Widget_RSS' => 'Devtronic\\FreshPress\\Components\\Widgets\\RssWidget',
    'WP_Widget_Search' => 'Devtronic\\FreshPress\\Components\\Widgets\\SearchWidget',
    'WP_Widget_Tag_Cloud' => 'Devtronic\\FreshPress\\Components\\Widgets\\TagCloudWidget',
    'WP_Widget_Text' => 'Devtronic\\FreshPress\\Components\\Widgets\\TextWidget',
    'PO' => 'POMO\\PO',
    'MO' => 'POMO\\MO',
    'WP_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\ListTable',
    'WP_Media_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\MediaListTable',
    'WP_Plugins_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\PluginsListTable',
    'WP_Links_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\LinksListTable',
    'WP_Posts_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\PostsListTable',
    'WP_Terms_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\TermsListTable',
    'WP_Users_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\UsersListTable',
    'WP_Comments_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\CommentsListTable',
    'WP_Post_Comments_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\PostCommentsListTable',
    'WP_Plugin_Install_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\PluginInstallListTable',
    'WP_Themes_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\ThemesListTable',
    'WP_Theme_Install_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\ThemeInstallListTable',
    'WP_MS_Sites_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\MSSitesListTable',
];

$abstractClasses = [
    'WP_Widget_Media' => 'Devtronic\\FreshPress\\Components\\Widgets\\MediaWidget',
];

// Classes
spl_autoload_register(function ($oldClass) use ($classes) {
    if (isset($classes[$oldClass])) {
        eval(sprintf('class %s extends %s {}', $oldClass, $classes[$oldClass]));
    }
});

// Abstract Classes
spl_autoload_register(function ($oldClass) use ($abstractClasses) {
    if (isset($classes[$oldClass])) {
        eval(sprintf('abstract class %s extends %s {}', $oldClass, $abstractClasses[$oldClass]));
    }
});
