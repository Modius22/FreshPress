<?php
/**
 * Customize API: BackgroundImageSetting class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customizer Background Image Setting class.
 *
 * @since 3.4.0
 *
 * @see Setting
 */
class BackgroundImageSetting extends Setting
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
