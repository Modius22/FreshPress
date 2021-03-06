<?php
/**
 * Customize API: Devtronic\FreshPress\Components\Customize\ImageControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Image Control class.
 *
 * @since 3.4.0
 *
 * @see UploadControl
 */
class ImageControl extends UploadControl
{
    public $type = 'image';
    public $mime_type = 'image';

    /**
     * Constructor.
     *
     * @since 3.4.0
     * @uses UploadControl::__construct()
     *
     * @param Manager $manager Customizer bootstrap instance.
     * @param string $id Control ID.
     * @param array $args Optional. Arguments to override class property defaults.
     */
    public function __construct($manager, $id, $args = [])
    {
        parent::__construct($manager, $id, $args);

        $this->button_labels = wp_parse_args($this->button_labels, [
            'select' => __('Select Image'),
            'change' => __('Change Image'),
            'remove' => __('Remove'),
            'default' => __('Default'),
            'placeholder' => __('No image selected'),
            'frame_title' => __('Select Image'),
            'frame_button' => __('Choose Image'),
        ]);
    }

    /**
     * @since 3.4.2
     * @deprecated 4.1.0
     */
    public function prepare_control()
    {
    }

    /**
     * @since 3.4.0
     * @deprecated 4.1.0
     *
     * @param string $id
     * @param string $label
     * @param mixed $callback
     */
    public function add_tab($id, $label, $callback)
    {
    }

    /**
     * @since 3.4.0
     * @deprecated 4.1.0
     *
     * @param string $id
     */
    public function remove_tab($id)
    {
    }

    /**
     * @since 3.4.0
     * @deprecated 4.1.0
     *
     * @param string $url
     * @param string $thumbnail_url
     */
    public function print_tab_image($url, $thumbnail_url = null)
    {
    }
}
