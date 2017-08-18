<?php
/**
 * REST API: WP_REST_User_Meta_Fields class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

use Devtronic\FreshPress\Components\Rest\Fields\MetaFields;

/**
 * Core class used to manage meta values for users via the REST API.
 *
 * @since 4.7.0
 *
 * @see MetaFields
 */
class WP_REST_User_Meta_Fields extends MetaFields
{

    /**
     * Retrieves the object meta type.
     *
     * @since 4.7.0
     * @access protected
     *
     * @return string The user meta type.
     */
    protected function get_meta_type()
    {
        return 'user';
    }

    /**
     * Retrieves the type for register_rest_field().
     *
     * @since 4.7.0
     * @access public
     *
     * @return string The user REST field type.
     */
    public function get_rest_field_type()
    {
        return 'user';
    }
}
