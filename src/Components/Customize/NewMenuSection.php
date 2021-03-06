<?php
/**
 * Customize API: NewMenuSection class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Menu Section Class
 *
 * Implements the new-menu-ui toggle button instead of a regular section.
 *
 * @since 4.3.0
 *
 * @see Section
 */
class NewMenuSection extends Section
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
     * Render the section, and the controls that have been added to it.
     *
     * @since 4.3.0
     * @access protected
     */
    protected function render()
    {
        ?>
        <li id="accordion-section-<?php echo esc_attr($this->id); ?>" class="accordion-section-new-menu">
            <button type="button" class="button add-new-menu-item add-menu-toggle" aria-expanded="false">
                <?php echo esc_html($this->title); ?>
            </button>
            <ul class="new-menu-section-content"></ul>
        </li>
        <?php
    }
}
