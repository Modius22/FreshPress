<?php
/**
 * Customize API: WP_Customize_Filter_Setting class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

use Devtronic\FreshPress\Components\Customize\Setting;

/**
 * A setting that is used to filter a value, but will not save the results.
 *
 * Results should be properly handled using another setting or callback.
 *
 * @since 3.4.0
 *
 * @see Setting
 */
class WP_Customize_Filter_Setting extends Setting
{

    /**
     * Saves the value of the setting, using the related API.
     *
     * @since 3.4.0
     * @access public
     *
     * @param mixed $value The value to update.
     */
    public function update($value)
    {
    }
}
