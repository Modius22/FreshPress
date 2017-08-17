<?php
/**
 * Wrapper to keep back compatibility to WordPress
 */

$classes = array_change_key_case([
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
    'WP_MS_Users_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\MSUsersListTable',
    'WP_MS_Themes_List_Table' => 'Devtronic\\FreshPress\\Components\\ListTables\\MSThemesListTable',
    '_WP_List_Table_Compat' => 'Devtronic\\FreshPress\\Components\\ListTables\\ListTableCompat',
    'wpdb' => 'Devtronic\\FreshPress\\Core\\WPDB',
    'WP_Filesystem_Base' => 'Devtronic\\FreshPress\\Components\\Filesystem\\BaseFilesystem',
    'WP_Filesystem_Direct' => 'Devtronic\\FreshPress\\Components\\Filesystem\\DirectFilesystem',
    'WP_Filesystem_ftpsockets' => 'Devtronic\\FreshPress\\Components\\Filesystem\\FTPSocketsFilesystem',
    'WP_Filesystem_SSH2' => 'Devtronic\\FreshPress\\Components\\Filesystem\\SSH2FileSystem',
    'WP_Filesystem_FTPext' => 'Devtronic\\FreshPress\\Components\\Filesystem\\FTPExtFileSystem',
    'Walker' => 'Devtronic\\FreshPress\\Components\\Walker\\Walker',
    'Walker_Page' => 'Devtronic\\FreshPress\\Components\\Walker\\PageWalker',
    'Walker_Comment' => 'Devtronic\\FreshPress\\Components\\Walker\\CommentWalker',
    'Walker_Category' => 'Devtronic\\FreshPress\\Components\\Walker\\CategoryWalker',
    'Walker_Category_Checklist' => 'Devtronic\\FreshPress\\Components\\Walker\\CategoryChecklistWalker',
    'Walker_PageDropdown' => 'Devtronic\\FreshPress\\Components\\Walker\\PageDropdownWalker',
    'Walker_CategoryDropdown' => 'Devtronic\\FreshPress\\Components\\Walker\\CategoryDropdownWalker',
    'Walker_Nav_Menu' => 'Devtronic\\FreshPress\\Components\\Walker\\NavMenuWalker',
    'Walker_Nav_Menu_Edit' => 'Devtronic\\FreshPress\\Components\\Walker\\NavMenuEditWalker',
    'Walker_Nav_Menu_Checklist' => 'Devtronic\\FreshPress\\Components\\Walker\\NavMenuChecklistWalker',
    'WP_Customize_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\Control',
    'WP_Customize_Color_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\ColorControl',
    'WP_Customize_Media_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\MediaControl',
    'WP_Customize_Background_Image_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\BackgroundImageControl',
    'WP_Customize_Image_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\ImageControl',
    'WP_Customize_Upload_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\UploadControl',
    'WP_Customize_Background_Position_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\BackgroundPositionControl',
    'WP_Customize_Cropped_Image_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\CroppedImageControl',
    'WP_Customize_Header_Image_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\HeaderImageControl',
    'WP_Customize_Nav_Menu_Auto_Add_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuAutoAddControl',
    'WP_Customize_Nav_Menu_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuControl',
    'WP_Customize_Nav_Menu_Item_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuItemControl',
    'WP_Customize_Nav_Menu_Location_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuLocationControl',
]);

$abstractClasses = array_change_key_case([
    'WP_Widget_Media' => 'Devtronic\\FreshPress\\Components\\Widgets\\MediaWidget',
]);

// Classes
spl_autoload_register(function ($oldClass) use ($classes) {
    $oldClass = strtolower($oldClass);
    if (isset($classes[$oldClass])) {
        eval(sprintf('class %s extends %s {}', $oldClass, $classes[$oldClass]));
    }
});

// Abstract Classes
spl_autoload_register(function ($oldClass) use ($abstractClasses) {
    $oldClass = strtolower($oldClass);
    if (isset($classes[$oldClass])) {
        eval(sprintf('abstract class %s extends %s {}', $oldClass, $abstractClasses[$oldClass]));
    }
});
