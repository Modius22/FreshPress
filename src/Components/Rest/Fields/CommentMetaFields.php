<?php
/**
 * REST API: CommentMetaFields class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Rest\Fields;

/**
 * Core class to manage comment meta via the REST API.
 *
 * @since 4.7.0
 *
 * @see MetaFields
 */
class CommentMetaFields extends MetaFields
{

    /**
     * Retrieves the object type for comment meta.
     *
     * @since 4.7.0
     * @access protected
     *
     * @return string The meta type.
     */
    protected function get_meta_type()
    {
        return 'comment';
    }

    /**
     * Retrieves the type for register_rest_field() in the context of comments.
     *
     * @since 4.7.0
     * @access public
     *
     * @return string The REST field type.
     */
    public function get_rest_field_type()
    {
        return 'comment';
    }
}
