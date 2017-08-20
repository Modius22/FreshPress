<?php
/**
 * Customize API: NavMenuSection class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Customize;

/**
 * Customize Menu Section Class
 *
 * Custom section only needed in JS.
 *
 * @since 4.3.0
 *
 * @see Section
 */
class NavMenuSection extends Section
{

    /**
     * Control type.
     *
     * @since 4.3.0
     * @access public
     * @var string
     */
    public $type = 'nav_menu';

    /**
     * Get section parameters for JS.
     *
     * @since 4.3.0
     * @access public
     * @return array Exported parameters.
     */
    public function json()
    {
        $exported = parent::json();
        $exported['menu_id'] = intval(preg_replace('/^nav_menu\[(-?\d+)\]/', '$1', $this->id));

        return $exported;
    }
}
