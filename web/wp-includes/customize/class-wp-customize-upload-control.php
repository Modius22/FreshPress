<?php
/**
 * Customize API: WP_Customize_Upload_Control class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

use Devtronic\FreshPress\Components\Customize\MediaControl;

/**
 * Customize Upload Control Class.
 *
 * @since 3.4.0
 *
 * @see MediaControl
 */
class WP_Customize_Upload_Control extends MediaControl
{
    public $type = 'upload';
    public $mime_type = '';
    public $button_labels = array();
    public $removed = ''; // unused
    public $context; // unused
    public $extensions = array(); // unused

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     *
     * @since 3.4.0
     *
     * @uses MediaControl::to_json()
     */
    public function to_json()
    {
        parent::to_json();

        $value = $this->value();
        if ($value) {
            // Get the attachment model for the existing file.
            $attachment_id = attachment_url_to_postid($value);
            if ($attachment_id) {
                $this->json['attachment'] = wp_prepare_attachment_for_js($attachment_id);
            }
        }
    }
}
