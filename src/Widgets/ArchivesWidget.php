<?php
/**
 * Widget API: ArchivesWidget class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Widgets;

/**
 * Core class used to implement the Archives widget.
 *
 * @since 2.8.0
 *
 * @see Widget
 */
class ArchivesWidget extends Widget
{

    /**
     * Sets up a new Archives widget instance.
     *
     * @since 2.8.0
     * @access public
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'widget_archive',
            'description' => __('A monthly archive of your site&#8217;s Posts.'),
            'customize_selective_refresh' => true,
        );
        parent::__construct('archives', __('Archives'), $widget_ops);
    }

    /**
     * Outputs the content for the current Archives widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $args Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Archives widget instance.
     */
    public function widget($args, $instance)
    {
        $c = !empty($instance['count']) ? '1' : '0';
        $d = !empty($instance['dropdown']) ? '1' : '0';

        /** This filter is documented in src/Widgets/PagesWidget.php */
        $title = apply_filters(
            'widget_title',
            empty($instance['title']) ? __('Archives') : $instance['title'],
            $instance,
            $this->id_base
        );

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if ($d) {
            $dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
            $escapedDropDownId = esc_attr($dropdown_id);

            /**
             * Filters the arguments for the Archives widget drop-down.
             *
             * @since 2.8.0
             *
             * @see wp_get_archives()
             *
             * @param array $args An array of Archives widget drop-down arguments.
             */
            $dropDownArgs = apply_filters('widget_archives_dropdown_args', array(
                'type' => 'monthly',
                'format' => 'option',
                'echo' => false,
                'show_post_count' => $c
            ));

            $label = 'Select Post';
            $labelMap = [
                'yearly' => 'Select Year',
                'monthly' => 'Select Month',
                'daily' => 'Select Day',
                'weekly' => 'Select Week',
            ];
            $type = $dropDownArgs['type'];
            if (isset($labelMap[$type])) {
                $label = $labelMap[$type];
            }
            $label = __($label);
            $escapedLabel = esc_attr($label);
            $dropDownCode = wp_get_archives($dropDownArgs);
            echo <<<HTML
                <label class="screen-reader-text" for="{$escapedDropDownId}">{$title}</label>
                <select id="{$escapedDropDownId}" name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'>
                    <option value="">{$escapedLabel}</option>
                    {$dropDownCode}
                </select>
HTML;
        } else {
            ?>
            <ul>
                <?php
                /**
                 * Filters the arguments for the Archives widget.
                 *
                 * @since 2.8.0
                 *
                 * @see wp_get_archives()
                 *
                 * @param array $args An array of Archives option arguments.
                 */
                wp_get_archives(apply_filters('widget_archives_args', array(
                    'type' => 'monthly',
                    'show_post_count' => $c
                ))); ?>
            </ul>
            <?php
        }

        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current Archives widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via ArchivesWidget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $new_instance = wp_parse_args((array)$new_instance, array('title' => '', 'count' => 0, 'dropdown' => ''));
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['count'] = $new_instance['count'] ? 1 : 0;
        $instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

        return $instance;
    }

    /**
     * Outputs the settings form for the Archives widget.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array)$instance, array('title' => '', 'count' => 0, 'dropdown' => ''));
        $title = sanitize_text_field($instance['title']); ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input
                    class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                    name="<?php echo $this->get_field_name('title'); ?>"
                    value="<?php echo esc_attr($title); ?>"/></p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked($instance['dropdown']); ?>
                   id="<?php echo $this->get_field_id('dropdown'); ?>"
                   name="<?php echo $this->get_field_name('dropdown'); ?>"/> <label
                    for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
            <br/>
            <input class="checkbox" type="checkbox"<?php checked($instance['count']); ?>
                   id="<?php echo $this->get_field_id('count'); ?>"
                   name="<?php echo $this->get_field_name('count'); ?>"/> <label
                    for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
        </p>
        <?php
    }
}
