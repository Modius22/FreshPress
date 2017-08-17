<?php
/**
 * Customize API: BackgroundImageControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

use WP_Customize_Image_Control;
use WP_Customize_Manager;

/**
 * Customize Background Image Control class.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Image_Control
 */
class BackgroundImageControl extends WP_Customize_Image_Control
{
    public $type = 'background';

    /**
     * Constructor.
     *
     * @since 3.4.0
     * @uses WP_Customize_Image_Control::__construct()
     *
     * @param WP_Customize_Manager $manager Customizer bootstrap instance.
     */
    public function __construct($manager)
    {
        parent::__construct($manager, 'background_image', [
            'label' => __('Background Image'),
            'section' => 'background_image',
        ]);
    }

    /**
     * Enqueue control related scripts/styles.
     *
     * @since 4.1.0
     */
    public function enqueue()
    {
        parent::enqueue();

        $custom_background = get_theme_support('custom-background');
        wp_localize_script('customize-controls', '_wpCustomizeBackground', [
            'defaults' => !empty($custom_background[0]) ? $custom_background[0] : [],
            'nonces' => [
                'add' => wp_create_nonce('background-add'),
            ],
        ]);
    }
}
