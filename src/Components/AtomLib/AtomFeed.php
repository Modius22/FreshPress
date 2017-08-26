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
 * Structure that store common Atom Feed Properties
 *
 * @package AtomLib
 */
class AtomFeed
{
    /**
     * Stores Links
     * @var array
     * @access public
     */
    public $links = array();
    /**
     * Stores Categories
     * @var array
     * @access public
     */
    public $categories = array();
    /**
     * Stores Entries
     *
     * @var array
     * @access public
     */
    public $entries = array();
}
