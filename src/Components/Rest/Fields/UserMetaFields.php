<?php
/**
 * REST API: UserMetaFields class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Rest\Fields;

/**
 * Core class used to manage meta values for users via the REST API.
 *
 * @since 4.7.0
 *
 * @see MetaFields
 */
class UserMetaFields extends MetaFields
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
