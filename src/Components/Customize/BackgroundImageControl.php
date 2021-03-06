<?php
/**
 * Customize API: BackgroundImageControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Background Image Control class.
 *
 * @since 3.4.0
 *
 * @see ImageControl
 */
class BackgroundImageControl extends ImageControl
{
    public $type = 'background';

    /**
     * Constructor.
     *
     * @since 3.4.0
     * @uses ImageControl::__construct()
     *
     * @param Manager $manager Customizer bootstrap instance.
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
