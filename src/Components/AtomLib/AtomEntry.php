<?php
/**
 * Atom Syndication Format PHP Library
 *
 * @package AtomLib
 * @link http://code.google.com/p/phpatomlib/
 *
 * @author Elias Torres <elias@torrez.us>
 * @version 0.4
 * @since 2.3.0
 */

namespace Devtronic\FreshPress\Components\AtomLib;

/**
 * Structure that store Atom Entry Properties
 *
 * @package AtomLib
 */
class AtomEntry
{
    /**
     * Stores Links
     * @var array
     * @access public
     */
    public $links = [];
    /**
     * Stores Categories
     * @var array
     * @access public
     */
    public $categories = [];
}
