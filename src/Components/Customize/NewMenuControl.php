<?php
/**
 * Customize API: NewMenuControl class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize control class for new menus.
 *
 * @since 4.3.0
 *
 * @see Control
 */
class NewMenuControl extends Control
{

    /**
     * Control type.
     *
     * @since 4.3.0
     * @access public
     * @var string
     */
    public $type = 'new_menu';

    /**
     * Render the control's content.
     *
     * @since 4.3.0
     * @access public
     */
    public function render_content()
    {
        ?>
        <button type="button" class="button button-primary"
                id="create-new-menu-submit"><?php _e('Create Menu'); ?></button>
        <span class="spinner"></span>
        <?php
    }
}
