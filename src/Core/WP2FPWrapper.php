<?php
/**
 * Wrapper to keep back compatibility to WordPress
 */

$classes = [
    'WP_Widget' => 'Devtronic\\FreshPress\\Widgets\\Widget',
    'WP_Nav_Menu_Widget' => 'Devtronic\\FreshPress\\Widgets\\NavMenuWidget',
    'WP_Widget_Archives' => 'Devtronic\\FreshPress\\Widgets\\ArchivesWidget',
    'WP_Widget_Calendar' => 'Devtronic\\FreshPress\\Widgets\\CalendarWidget',
    'WP_Widget_Categories' => 'Devtronic\\FreshPress\\Widgets\\CategoriesWidget',
    'WP_Widget_Links' => 'Devtronic\\FreshPress\\Widgets\\LinksWidget',
    'WP_Widget_Media_Audio' => 'Devtronic\\FreshPress\\Widgets\\AudioWidget',
    'WP_Widget_Media_Image' => 'Devtronic\\FreshPress\\Widgets\\ImageWidget',
    'WP_Widget_Media_Video' => 'Devtronic\\FreshPress\\Widgets\\VideoWidget',
    'WP_Widget_Meta' => 'Devtronic\\FreshPress\\Widgets\\MetaWidget',
    'WP_Widget_Pages' => 'Devtronic\\FreshPress\\Widgets\\PagesWidget',
    'WP_Widget_Recent_Comments' => 'Devtronic\\FreshPress\\Widgets\\RecentCommentsWidget',
    'WP_Widget_Recent_Posts' => 'Devtronic\\FreshPress\\Widgets\\RecentPostsWidget',
    'WP_Widget_RSS' => 'Devtronic\\FreshPress\\Widgets\\RssWidget',
    'WP_Widget_Search' => 'Devtronic\\FreshPress\\Widgets\\SearchWidget',
    'WP_Widget_Tag_Cloud' => 'Devtronic\\FreshPress\\Widgets\\TagCloudWidget',
    'WP_Widget_Text' => 'Devtronic\\FreshPress\\Widgets\\TextWidget',
    'PO' => 'POMO\\PO',
    'MO' => 'POMO\\MO',
];

$abstractClasses = [
    'WP_Widget_Media' => 'Devtronic\\FreshPress\\Widgets\\MediaWidget',
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
