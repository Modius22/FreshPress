<?php
/**
 * Widget API: MetaWidget class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Widgets;

/**
 * Core class used to implement a Meta widget.
 *
 * Displays log in/out, RSS feed links, etc.
 *
 * @since 2.8.0
 *
 * @see Widget
 */
class MetaWidget extends Widget
{

    /**
     * Sets up a new Meta widget instance.
     *
     * @since 2.8.0
     * @access public
     */
    public function __construct()
    {
        $widget_ops = [
            'classname' => 'widget_meta',
            'description' => __('Login, RSS, &amp; WordPress.org links.'),
            'customize_selective_refresh' => true,
        ];
        parent::__construct('meta', __('Meta'), $widget_ops);
    }

    /**
     * Outputs the content for the current Meta widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $args Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Meta widget instance.
     */
    public function widget($args, $instance)
    {
        /** This filter is documented in src/Widgets/PagesWidget.php */
        $title = apply_filters(
            'widget_title',
            empty($instance['title']) ? __('Meta') : $instance['title'],
            $instance,
            $this->id_base
        );

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        $escapedUrl = esc_url(__('https://wordpress.org/'));
        $escapedTitle = esc_attr__('Powered by WordPress, state-of-the-art semantic personal publishing platform.');
        $escapedText = _x('WordPress.org', 'meta widget link text');
        $code = '<li><a href="' . $escapedUrl . '" title="' . $escapedTitle . '">' . $escapedText . '</a></li>';

        $links = [
            wp_register('', '', false),
            wp_loginout('', false),
            '<a href="' . esc_url(get_bloginfo('rss2_url')) . '">' . _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>') . '</a>',
            '<a href="' . esc_url(get_bloginfo('comments_rss2_url')) . '">' . _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>') . '</a>',
        ];
        $li = '';
        foreach ($links as $link) {
            $li .= sprintf('<li>%s</li>', $link);
        }
        $li .= apply_filters('widget_meta_poweredby', $code);

        ob_start();
        wp_meta();
        $li .= ob_get_contents();
        ob_end_clean();
        echo <<<HTML
        <ul>
            {$li}
        </ul>
HTML;
        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current Meta widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);

        return $instance;
    }

    /**
     * Outputs the settings form for the Meta widget.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array)$instance, ['title' => '']);
        $title = sanitize_text_field($instance['title']); ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input
                    class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                    name="<?php echo $this->get_field_name('title'); ?>"
                    value="<?php echo esc_attr($title); ?>"/></p>
        <?php
    }
}
