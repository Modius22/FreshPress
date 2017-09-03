<?php
/**
 * REST API: PostTypesController class
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
 * Core class to access post types via the REST API.
 *
 * @since 4.7.0
 *
 * @see Controller
 */
class PostTypesController extends Controller
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
        $this->rest_base = 'types';
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<type>[\w-]+)', [
            'args' => [
                'type' => [
                    'description' => __('An alphanumeric identifier for the post type.'),
                    'type' => 'string',
                ],
            ],
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_item'],
                'args' => [
                    'context' => $this->get_context_param(['default' => 'view']),
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Checks whether a given request has permission to read types.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Error|true True if the request has read access, Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        if ('edit' === $request['context']) {
            foreach (get_post_types([], 'object') as $post_type) {
                if (!empty($post_type->show_in_rest) && current_user_can($post_type->cap->edit_posts)) {
                    return true;
                }
            }

            return new Error(
                'rest_cannot_view',
                __('Sorry, you are not allowed to edit posts in this post type.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Retrieves all public post types.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Error|Response Response object on success, or Error object on failure.
     */
    public function get_items($request)
    {
        $data = [];

        foreach (get_post_types([], 'object') as $obj) {
            if (empty($obj->show_in_rest) || ('edit' === $request['context'] && !current_user_can($obj->cap->edit_posts))) {
                continue;
            }

            $post_type = $this->prepare_item_for_response($obj, $request);
            $data[$obj->name] = $this->prepare_response_for_collection($post_type);
        }

        return rest_ensure_response($data);
    }

    /**
     * Retrieves a specific post type.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Error|Response Response object on success, or Error object on failure.
     */
    public function get_item($request)
    {
        $obj = get_post_type_object($request['type']);

        if (empty($obj)) {
            return new Error('rest_type_invalid', __('Invalid post type.'), ['status' => 404]);
        }

        if (empty($obj->show_in_rest)) {
            return new Error(
                'rest_cannot_read_type',
                __('Cannot view post type.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if ('edit' === $request['context'] && !current_user_can($obj->cap->edit_posts)) {
            return new Error(
                'rest_forbidden_context',
                __('Sorry, you are not allowed to edit posts in this post type.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        $data = $this->prepare_item_for_response($obj, $request);

        return rest_ensure_response($data);
    }

    /**
     * Prepares a post type object for serialization.
     *
     * @since 4.7.0
     * @access public
     *
     * @param \stdClass $post_type Post type data.
     * @param Request $request Full details about the request.
     * @return Response Response object.
     */
    public function prepare_item_for_response($post_type, $request)
    {
        $taxonomies = wp_list_filter(get_object_taxonomies($post_type->name, 'objects'), ['show_in_rest' => true]);
        $taxonomies = wp_list_pluck($taxonomies, 'name');
        $base = !empty($post_type->rest_base) ? $post_type->rest_base : $post_type->name;
        $supports = get_all_post_type_supports($post_type->name);

        $data = [
            'capabilities' => $post_type->cap,
            'description' => $post_type->description,
            'hierarchical' => $post_type->hierarchical,
            'labels' => $post_type->labels,
            'name' => $post_type->label,
            'slug' => $post_type->name,
            'supports' => $supports,
            'taxonomies' => array_values($taxonomies),
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
         * Filters a post type returned from the API.
         *
         * Allows modification of the post type data right before it is returned.
         *
         * @since 4.7.0
         *
         * @param Response $response The response object.
         * @param object $item The original post type object.
         * @param Request $request Request used to generate the response.
         */
        return apply_filters('rest_prepare_post_type', $response, $post_type, $request);
    }

    /**
     * Retrieves the post type's schema, conforming to JSON Schema.
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
            'title' => 'type',
            'type' => 'object',
            'properties' => [
                'capabilities' => [
                    'description' => __('All capabilities used by the post type.'),
                    'type' => 'object',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'description' => [
                    'description' => __('A human-readable description of the post type.'),
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'hierarchical' => [
                    'description' => __('Whether or not the post type should have children.'),
                    'type' => 'boolean',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'labels' => [
                    'description' => __('Human-readable labels for the post type for various contexts.'),
                    'type' => 'object',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'name' => [
                    'description' => __('The title for the post type.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'slug' => [
                    'description' => __('An alphanumeric identifier for the post type.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'supports' => [
                    'description' => __('All features, supported by the post type.'),
                    'type' => 'object',
                    'context' => ['edit'],
                    'readonly' => true,
                ],
                'taxonomies' => [
                    'description' => __('Taxonomies associated with post type.'),
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'rest_base' => [
                    'description' => __('REST base route for the post type.'),
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
        return [
            'context' => $this->get_context_param(['default' => 'view']),
        ];
    }
}
