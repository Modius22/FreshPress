<?php
/**
 * Customize API: WP_Customize_Background_Image_Setting class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

use Devtronic\FreshPress\Components\Customize\Setting;

/**
 * Customizer Background Image Setting class.
 *
 * @since 3.4.0
 *
 * @see Setting
 */
final class WP_Customize_Background_Image_Setting extends Setting
{
    public $id = 'background_image_thumb';

    /**
     * @since 3.4.0
     *
     * @param $value
     */
    public function update($value)
    {
        remove_theme_mod('background_image_thumb');
    }
}
