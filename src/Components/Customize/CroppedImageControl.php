<?php
/**
 * Customize API: CroppedImageControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Cropped Image Control class.
 *
 * @since 4.3.0
 *
 * @see ImageControl
 */
class CroppedImageControl extends ImageControl
{

    /**
     * Control type.
     *
     * @since 4.3.0
     * @access public
     * @var string
     */
    public $type = 'cropped_image';

    /**
     * Suggested width for cropped image.
     *
     * @since 4.3.0
     * @access public
     * @var int
     */
    public $width = 150;

    /**
     * Suggested height for cropped image.
     *
     * @since 4.3.0
     * @access public
     * @var int
     */
    public $height = 150;

    /**
     * Whether the width is flexible.
     *
     * @since 4.3.0
     * @access public
     * @var bool
     */
    public $flex_width = false;

    /**
     * Whether the height is flexible.
     *
     * @since 4.3.0
     * @access public
     * @var bool
     */
    public $flex_height = false;

    /**
     * Enqueue control related scripts/styles.
     *
     * @since 4.3.0
     * @access public
     */
    public function enqueue()
    {
        wp_enqueue_script('customize-views');

        parent::enqueue();
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     *
     * @since 4.3.0
     * @access public
     *
     * @see Control::to_json()
     */
    public function to_json()
    {
        parent::to_json();

        $this->json['width'] = absint($this->width);
        $this->json['height'] = absint($this->height);
        $this->json['flex_width'] = absint($this->flex_width);
        $this->json['flex_height'] = absint($this->flex_height);
    }
}
