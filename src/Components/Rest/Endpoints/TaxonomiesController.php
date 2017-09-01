<?php
/**
 * REST API: TaxonomiesController class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Rest\Endpoints;

use Devtronic\FreshPress\Components\Rest\Request;
use Devtronic\FreshPress\Components\Rest\Response;
use Devtronic\FreshPress\Components\Rest\Server;
use Devtronic\FreshPress\Core\Error;

/**
 * Core class used to manage taxonomies via the REST API.
 *
 * @since 4.7.0
 *
 * @see Controller
 */
class TaxonomiesController extends Controller
{

    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     */
    public function __construct()
    {
        $this->namespace = 'wp/v2';
        $this->rest_base = 'taxonomies';
    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @since 4.7.0
     * @access public
     *
     * @see register_rest_route()
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_collection_params(),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<taxonomy>[\w-]+)', [
            'args' => [
                'taxonomy' => [
                    'description' => __('An alphanumeric identifier for the taxonomy.'),
                    'type' => 'string',
                ],
            ],
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'context' => $this->get_context_param(['default' => 'view']),
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Checks whether a given request has permission to read taxonomies.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return true|Error True if the request has read access, Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        if ('edit' === $request['context']) {
            if (!empty($request['type'])) {
                $taxonomies = get_object_taxonomies($request['type'], 'objects');
            } else {
                $taxonomies = get_taxonomies('', 'objects');
            }
            foreach ($taxonomies as $taxonomy) {
                if (!empty($taxonomy->show_in_rest) && current_user_can($taxonomy->cap->manage_terms)) {
                    return true;
                }
            }
            return new Error(
                'rest_cannot_view',
                __('Sorry, you are not allowed to manage terms in this taxonomy.'),
                ['status' => rest_authorization_required_code()]
            );
        }
        return true;
    }

    /**
     * Retrieves all public taxonomies.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response Response object on success, or Error object on failure.
     */
    public function get_items($request)
    {

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();

        if (isset($registered['type']) && !empty($request['type'])) {
            $taxonomies = get_object_taxonomies($request['type'], 'objects');
        } else {
            $taxonomies = get_taxonomies('', 'objects');
        }
        $data = [];
        foreach ($taxonomies as $tax_type => $value) {
            if (empty($value->show_in_rest) || ('edit' === $request['context'] && !current_user_can($value->cap->manage_terms))) {
                continue;
            }
            $tax = $this->prepare_item_for_response($value, $request);
            $tax = $this->prepare_response_for_collection($tax);
            $data[$tax_type] = $tax;
        }

        if (empty($data)) {
            // Response should still be returned as a JSON object when it is empty.
            $data = (object)$data;
        }

        return rest_ensure_response($data);
    }

    /**
     * Checks if a given request has access to a taxonomy.
     *
     * @since 4.7.0
     * @access public
     *
     * @param  Request $request Full details about the request.
     * @return true|Error True if the request has read access for the item, otherwise false or Error object.
     */
    public function get_item_permissions_check($request)
    {
        $tax_obj = get_taxonomy($request['taxonomy']);

        if ($tax_obj) {
            if (empty($tax_obj->show_in_rest)) {
                return false;
            }
            if ('edit' === $request['context'] && !current_user_can($tax_obj->cap->manage_terms)) {
                return new Error(
                    'rest_forbidden_context',
                    __('Sorry, you are not allowed to manage terms in this taxonomy.'),
                    ['status' => rest_authorization_required_code()]
                );
            }
        }

        return true;
    }

    /**
     * Retrieves a specific taxonomy.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function get_item($request)
    {
        $tax_obj = get_taxonomy($request['taxonomy']);
        if (empty($tax_obj)) {
            return new Error('rest_taxonomy_invalid', __('Invalid taxonomy.'), ['status' => 404]);
        }
        $data = $this->prepare_item_for_response($tax_obj, $request);
        return rest_ensure_response($data);
    }

    /**
     * Prepares a taxonomy object for serialization.
     *
     * @since 4.7.0
     * @access public
     *
     * @param \stdClass $taxonomy Taxonomy data.
     * @param Request $request Full details about the request.
     * @return Response Response object.
     */
    public function prepare_item_for_response($taxonomy, $request)
    {
        $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;
        $data = [
            'name' => $taxonomy->label,
            'slug' => $taxonomy->name,
            'capabilities' => $taxonomy->cap,
            'description' => $taxonomy->description,
            'labels' => $taxonomy->labels,
            'types' => $taxonomy->object_type,
            'show_cloud' => $taxonomy->show_tagcloud,
            'hierarchical' => $taxonomy->hierarchical,
            'rest_base' => $base,
        ];

        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);

        // Wrap the data in a response object.
        $response = rest_ensure_response($data);

        $response->add_links([
            'collection' => [
                'href' => rest_url(sprintf('%s/%s', $this->namespace, $this->rest_base)),
            ],
            'https://api.w.org/items' => [
                'href' => rest_url(sprintf('wp/v2/%s', $base)),
            ],
        ]);

        /**
         * Filters a taxonomy returned from the REST API.
         *
         * Allows modification of the taxonomy data right before it is returned.
         *
         * @since 4.7.0
         *
         * @param Response $response The response object.
         * @param object $item The original taxonomy object.
         * @param Request $request Request used to generate the response.
         */
        return apply_filters('rest_prepare_taxonomy', $response, $taxonomy, $request);
    }

    /**
     * Retrieves the taxonomy's schema, conforming to JSON Schema.
     *
     * @since 4.7.0
     * @access public
     *
     * @return array Item schema data.
     */
    public function get_item_schema()
    {
        $schema = [
            '$schema' => 'http://json-schema.org/schema#',
            'title' => 'taxonomy',
            'type' => 'object',
            'properties' => [
                'capabilities' => [
                    'description' => __('All capabilities used by the taxonomy.'),
                    'type' => 'object',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'description' => [
                    'description' => __('A human-readable description of the taxonomy.'),
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'hierarchical' => [
                    'description' => __('Whether or not the taxonomy should have children.'),
                    'type' => 'boolean',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'labels' => [
                    'description' => __('Human-readable labels for the taxonomy for various contexts.'),
                    'type' => 'object',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'name' => [
                    'description' => __('The title for the taxonomy.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'slug' => [
                    'description' => __('An alphanumeric identifier for the taxonomy.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'show_cloud' => [
                    'description' => __('Whether or not the term cloud should be displayed.'),
                    'type' => 'boolean',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'types' => [
                    'description' => __('Types associated with the taxonomy.'),
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'rest_base' => [
                    'description' => __('REST base route for the taxonomy.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
            ],
        ];
        return $this->add_additional_fields_schema($schema);
    }

    /**
     * Retrieves the query params for collections.
     *
     * @since 4.7.0
     * @access public
     *
     * @return array Collection parameters.
     */
    public function get_collection_params()
    {
        $new_params = [];
        $new_params['context'] = $this->get_context_param(['default' => 'view']);
        $new_params['type'] = [
            'description' => __('Limit results to taxonomies associated with a specific post type.'),
            'type' => 'string',
        ];
        return $new_params;
    }
}
