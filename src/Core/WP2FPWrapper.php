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
    'WP_Customize_Nav_Menu_Name_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuNameControl',
    'WP_Customize_New_Menu_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\NewMenuControl',
    'WP_Customize_Site_Icon_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\SiteIconControl',
    'WP_Customize_Theme_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\ThemeControl',
    'WP_Widget_Area_Customize_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\WidgetAreaControl',
    'WP_Widget_Form_Customize_Control' => 'Devtronic\\FreshPress\\Components\\Customize\\WidgetFormControl',
    'WP_Customize_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\Setting',
    'WP_Customize_Filter_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\FilterSetting',
    'WP_Customize_Nav_Menu_Item_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuItemSetting',
    'WP_Customize_Nav_Menu_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuSetting',
    'WP_Customize_Section' => 'Devtronic\\FreshPress\\Components\\Customize\\Section',
    'WP_Customize_Nav_Menu_Section' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenuSection',
    'WP_Customize_New_Menu_Section' => 'Devtronic\\FreshPress\\Components\\Customize\\NewMenuSection',
    'WP_Customize_Sidebar_Section' => 'Devtronic\\FreshPress\\Components\\Customize\\SidebarSection',
    'WP_Customize_Themes_Section' => 'Devtronic\\FreshPress\\Components\\Customize\\ThemesSection',
    'WP_Customize_Panel' => 'Devtronic\\FreshPress\\Components\\Customize\\Panel',
    'WP_Customize_Nav_Menus_Panel' => 'Devtronic\\FreshPress\\Components\\Customize\\NavMenusPanel',
    'WP_Customize_Partial' => 'Devtronic\\FreshPress\\Components\\Customize\\Partial',
    'WP_REST_Server' => 'Devtronic\\FreshPress\\Components\\Rest\\Server',
    'WP_REST_Request' => 'Devtronic\\FreshPress\\Components\\Rest\\Request',
    'WP_REST_Response' => 'Devtronic\\FreshPress\\Components\\Rest\\Response',
    'WP_REST_Attachments_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\AttachmentsController',
    'WP_REST_Comments_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\CommentsController',
    'WP_REST_Post_Statuses_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\PostStatusController',
    'WP_REST_Post_Types_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\PostTypesController',
    'WP_REST_Posts_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\PostsController',
    'WP_REST_Revisions_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\RevisionsController',
    'WP_REST_Settings_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\SettingsController',
    'WP_REST_Taxonomies_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\TaxonomiesController',
    'WP_REST_Terms_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\TermsController',
    'WP_REST_Users_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\UsersController',
    'WP_REST_Comment_Meta_Fields' => 'Devtronic\\FreshPress\\Components\\Rest\\Fields\\CommentMetaFields',
    'WP_REST_Post_Meta_Fields' => 'Devtronic\\FreshPress\\Components\\Rest\\Fields\\PostMetaFields',
    'WP_REST_Term_Meta_Fields' => 'Devtronic\\FreshPress\\Components\\Rest\\Fields\\TermMetaFields',
    'WP_REST_User_Meta_Fields' => 'Devtronic\\FreshPress\\Components\\Rest\\Fields\\UserMetaFields',
    'WP_Upgrader_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\UpgraderSkin',
    'Plugin_Upgrader_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\PluginUpgraderSkin',
    'Language_Pack_Upgrader_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\LanguageUpgraderSkin',
    'Theme_Installer_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\ThemeInstallerSkin',
    'Plugin_Installer_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\PluginInstallerSkin',
    'WP_Ajax_Upgrader_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\AjaxUpgraderSkin',
    'Automatic_Upgrader_Skin' => 'Devtronic\\FreshPress\\Components\\Upgrader\\AutomaticUpgraderSkin',
]);

$abstractClasses = array_change_key_case([
    'WP_Widget_Media' => 'Devtronic\\FreshPress\\Components\\Widgets\\MediaWidget',
    'WP_REST_Controller' => 'Devtronic\\FreshPress\\Components\\Rest\\Endpoints\\Controller',
    'WP_REST_Meta_Fields' => 'Devtronic\\FreshPress\\Components\\Rest\\Fields\\MetaFields',
]);

$finalClasses = array_change_key_case([
    'WP_Customize_Background_Image_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\BackgroundImageSetting',
    'WP_Customize_Custom_CSS_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\CustomCssSetting',
    'WP_Customize_Header_Image_Setting' => 'Devtronic\\FreshPress\\Components\\Customize\\HeaderImageSetting',
    'WP_Customize_Selective_Refresh' => 'Devtronic\\FreshPress\\Components\\Customize\\SelectiveRefresh',
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

// Final Classes
spl_autoload_register(function ($oldClass) use ($finalClasses) {
    $oldClass = strtolower($oldClass);
    if (isset($classes[$oldClass])) {
        eval(sprintf('abstract class %s extends %s {}', $oldClass, $finalClasses[$oldClass]));
    }
});
