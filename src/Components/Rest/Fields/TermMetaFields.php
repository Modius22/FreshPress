<?php
/**
 * REST API: TermMetaFields class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Rest\Fields;

/**
 * Core class used to manage meta values for terms via the REST API.
 *
 * @since 4.7.0
 *
 * @see MetaFields
 */
class TermMetaFields extends MetaFields
{

    /**
     * Taxonomy to register fields for.
     *
     * @since 4.7.0
     * @access protected
     * @var string
     */
    protected $taxonomy;

    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $taxonomy Taxonomy to register fields for.
     */
    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    /**
     * Retrieves the object meta type.
     *
     * @since 4.7.0
     * @access protected
     *
     * @return string The meta type.
     */
    protected function get_meta_type()
    {
        return 'term';
    }

    /**
     * Retrieves the type for register_rest_field().
     *
     * @since 4.7.0
     * @access public
     *
     * @return string The REST field type.
     */
    public function get_rest_field_type()
    {
        return 'post_tag' === $this->taxonomy ? 'tag' : $this->taxonomy;
    }
}
