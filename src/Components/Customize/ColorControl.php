<?php
/**
 * Customize API: ColorControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Color Control class.
 *
 * @since 3.4.0
 *
 * @see Control
 */
class ColorControl extends Control
{
    /**
     * Type.
     *
     * @access public
     * @var string
     */
    public $type = 'color';

    /**
     * Statuses.
     *
     * @access public
     * @var array
     */
    public $statuses;

    /**
     * Mode.
     *
     * @since 4.7.0
     * @access public
     * @var string
     */
    public $mode = 'full';

    /**
     * Constructor.
     *
     * @since 3.4.0
     * @uses Control::__construct()
     *
     * @param Manager $manager Customizer bootstrap instance.
     * @param string $id Control ID.
     * @param array $args Optional. Arguments to override class property defaults.
     */
    public function __construct($manager, $id, $args = [])
    {
        $this->statuses = ['' => __('Default')];
        parent::__construct($manager, $id, $args);
    }

    /**
     * Enqueue scripts/styles for the color picker.
     *
     * @since 3.4.0
     */
    public function enqueue()
    {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     *
     * @since 3.4.0
     * @uses Control::to_json()
     */
    public function to_json()
    {
        parent::to_json();
        $this->json['statuses'] = $this->statuses;
        $this->json['defaultValue'] = $this->setting->default;
        $this->json['mode'] = $this->mode;
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     *
     * @since 3.4.0
     */
    public function render_content()
    {
    }

    /**
     * Render a JS template for the content of the color picker control.
     *
     * @since 4.1.0
     */
    public function content_template()
    {
        ?>
        <# var defaultValue = '#RRGGBB', defaultValueAttr = '',
                isHueSlider = data.mode === 'hue';

                if ( data.defaultValue && ! isHueSlider ) {
                if ( '#' !== data.defaultValue.substring( 0, 1 ) ) {
                defaultValue = '#' + data.defaultValue;
                } else {
                defaultValue = data.defaultValue;
                }
                defaultValueAttr = ' data-default-color=' + defaultValue; // Quotes added automatically.
                } #>
            <label>
                <# if ( data.label ) { #>
                    <span class="customize-control-title">{{{ data.label }}}</span>
                    <# } #>
                        <# if ( data.description ) { #>
                            <span class="description customize-control-description">{{{ data.description }}}</span>
                            <# } #>
                                <div class="customize-control-content">
                                    <# if ( isHueSlider ) { #>
                                        <input class="color-picker-hue" type="text" data-type="hue"/>
                                        <# } else { #>
                                            <input class="color-picker-hex" type="text" maxlength="7"
                                                   placeholder="{{ defaultValue }}" {{ defaultValueAttr }}/>
                                            <# } #>
                                </div>
            </label>
        <?php
    }
}
