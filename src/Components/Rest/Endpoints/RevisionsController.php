<?php
/**
 * REST API: RevisionsController class
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
use Devtronic\FreshPress\Entity\Post;

/**
 * Core class used to access revisions via the REST API.
 *
 * @since 4.7.0
 *
 * @see Controller
 */
class RevisionsController extends Controller
{

    /**
     * Parent post type.
     *
     * @since 4.7.0
     * @access private
     * @var string
     */
    private $parent_post_type;

    /**
     * Parent controller.
     *
     * @since 4.7.0
     * @access private
     * @var Controller
     */
    private $parent_controller;

    /**
     * The base of the parent controller's route.
     *
     * @since 4.7.0
     * @access private
     * @var string
     */
    private $parent_base;

    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $parent_post_type Post type of the parent.
     */
    public function __construct($parent_post_type)
    {
        $this->parent_post_type = $parent_post_type;
        $this->parent_controller = new PostsController($parent_post_type);
        $this->namespace = 'wp/v2';
        $this->rest_base = 'revisions';
        $post_type_object = get_post_type_object($parent_post_type);
        $this->parent_base = !empty($post_type_object->rest_base) ? $post_type_object->rest_base : $post_type_object->name;
    }

    /**
     * Registers routes for revisions based on post types supporting revisions.
     *
     * @since 4.7.0
     * @access public
     *
     * @see register_rest_route()
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->parent_base . '/(?P<parent>[\d]+)/' . $this->rest_base,
            [
                'args' => [
                    'parent' => [
                        'description' => __('The ID for the parent of the object.'),
                        'type' => 'integer',
                    ],
                ],
                [
                    'methods' => Server::READABLE,
                    'callback' => [$this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->parent_base . '/(?P<parent>[\d]+)/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'parent' => [
                        'description' => __('The ID for the parent of the object.'),
                        'type' => 'integer',
                    ],
                    'id' => [
                        'description' => __('Unique identifier for the object.'),
                        'type' => 'integer',
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
                [
                    'methods' => Server::DELETABLE,
                    'callback' => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'delete_item_permissions_check'],
                    'args' => [
                        'force' => [
                            'type' => 'boolean',
                            'default' => false,
                            'description' => __('Required to be true, as revisions do not support trashing.'),
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * Get the parent post, if the ID is valid.
     *
     * @since 4.7.2
     *
     * @param int $id Supplied ID.
     * @return Post|Error Post object if ID is valid, Error otherwise.
     */
    protected function get_parent($parent)
    {
        $error = new Error('rest_post_invalid_parent', __('Invalid post parent ID.'), ['status' => 404]);
        if ((int)$parent <= 0) {
            return $error;
        }

        $parent = get_post((int)$parent);
        if (empty($parent) || empty($parent->ID) || $this->parent_post_type !== $parent->post_type) {
            return $error;
        }

        return $parent;
    }

    /**
     * Checks if a given request has access to get revisions.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full data about the request.
     * @return true|Error True if the request has read access, Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        $parent = $this->get_parent($request['parent']);
        if (is_wp_error($parent)) {
            return $parent;
        }

        $parent_post_type_obj = get_post_type_object($parent->post_type);
        if (!current_user_can($parent_post_type_obj->cap->edit_post, $parent->ID)) {
            return new Error(
                'rest_cannot_read',
                __('Sorry, you are not allowed to view revisions of this post.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Get the revision, if the ID is valid.
     *
     * @since 4.7.2
     *
     * @param int $id Supplied ID.
     * @return Post|Error Revision post object if ID is valid, Error otherwise.
     */
    protected function get_revision($id)
    {
        $error = new Error('rest_post_invalid_id', __('Invalid revision ID.'), ['status' => 404]);
        if ((int)$id <= 0) {
            return $error;
        }

        $revision = get_post((int)$id);
        if (empty($revision) || empty($revision->ID) || 'revision' !== $revision->post_type) {
            return $error;
        }

        return $revision;
    }

    /**
     * Gets a collection of revisions.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full data about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function get_items($request)
    {
        $parent = $this->get_parent($request['parent']);
        if (is_wp_error($parent)) {
            return $parent;
        }

        $revisions = wp_get_post_revisions($request['parent']);

        $response = [];
        foreach ($revisions as $revision) {
            $data = $this->prepare_item_for_response($revision, $request);
            $response[] = $this->prepare_response_for_collection($data);
        }
        return rest_ensure_response($response);
    }

    /**
     * Checks if a given request has access to get a specific revision.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full data about the request.
     * @return bool|Error True if the request has read access for the item, Error object otherwise.
     */
    public function get_item_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }

    /**
     * Retrieves one revision from the collection.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full data about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function get_item($request)
    {
        $parent = $this->get_parent($request['parent']);
        if (is_wp_error($parent)) {
            return $parent;
        }

        $revision = $this->get_revision($request['id']);
        if (is_wp_error($revision)) {
            return $revision;
        }

        $response = $this->prepare_item_for_response($revision, $request);
        return rest_ensure_response($response);
    }

    /**
     * Checks if a given request has access to delete a revision.
     *
     * @since 4.7.0
     * @access public
     *
     * @param  Request $request Full details about the request.
     * @return bool|Error True if the request has access to delete the item, Error object otherwise.
     */
    public function delete_item_permissions_check($request)
    {
        $parent = $this->get_parent($request['parent']);
        if (is_wp_error($parent)) {
            return $parent;
        }

        $revision = $this->get_revision($request['id']);
        if (is_wp_error($revision)) {
            return $revision;
        }

        $response = $this->get_items_permissions_check($request);
        if (!$response || is_wp_error($response)) {
            return $response;
        }

        $post_type = get_post_type_object('revision');
        return current_user_can($post_type->cap->delete_post, $revision->ID);
    }

    /**
     * Deletes a single revision.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return true|Error True on success, or Error object on failure.
     */
    public function delete_item($request)
    {
        $revision = $this->get_revision($request['id']);
        if (is_wp_error($revision)) {
            return $revision;
        }

        $force = isset($request['force']) ? (bool)$request['force'] : false;

        // We don't support trashing for revisions.
        if (!$force) {
            return new Error(
                'rest_trash_not_supported',
                __('Revisions do not support trashing. Set force=true to delete.'),
                ['status' => 501]
            );
        }

        $previous = $this->prepare_item_for_response($revision, $request);

        $result = wp_delete_post($request['id'], true);

        /**
         * Fires after a revision is deleted via the REST API.
         *
         * @since 4.7.0
         *
         * @param (mixed) $result The revision object (if it was deleted or moved to the trash successfully)
         *                        or false (failure). If the revision was moved to to the trash, $result represents
         *                        its new state; if it was deleted, $result represents its state before deletion.
         * @param Request $request The request sent to the API.
         */
        do_action('rest_delete_revision', $result, $request);

        if (!$result) {
            return new Error('rest_cannot_delete', __('The post cannot be deleted.'), ['status' => 500]);
        }

        $response = new Response();
        $response->set_data(['deleted' => true, 'previous' => $previous->get_data()]);
        return $response;
    }

    /**
     * Prepares the revision for the REST response.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Post $post Post revision object.
     * @param Request $request Request object.
     * @return Response Response object.
     */
    public function prepare_item_for_response($post, $request)
    {
        $GLOBALS['post'] = $post;

        setup_postdata($post);

        $schema = $this->get_item_schema();

        $data = [];

        if (!empty($schema['properties']['author'])) {
            $data['author'] = (int)$post->post_author;
        }

        if (!empty($schema['properties']['date'])) {
            $data['date'] = $this->prepare_date_response($post->post_date_gmt, $post->post_date);
        }

        if (!empty($schema['properties']['date_gmt'])) {
            $data['date_gmt'] = $this->prepare_date_response($post->post_date_gmt);
        }

        if (!empty($schema['properties']['id'])) {
            $data['id'] = $post->ID;
        }

        if (!empty($schema['properties']['modified'])) {
            $data['modified'] = $this->prepare_date_response($post->post_modified_gmt, $post->post_modified);
        }

        if (!empty($schema['properties']['modified_gmt'])) {
            $data['modified_gmt'] = $this->prepare_date_response($post->post_modified_gmt);
        }

        if (!empty($schema['properties']['parent'])) {
            $data['parent'] = (int)$post->post_parent;
        }

        if (!empty($schema['properties']['slug'])) {
            $data['slug'] = $post->post_name;
        }

        if (!empty($schema['properties']['guid'])) {
            $data['guid'] = [
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters('get_the_guid', $post->guid),
                'raw' => $post->guid,
            ];
        }

        if (!empty($schema['properties']['title'])) {
            $data['title'] = [
                'raw' => $post->post_title,
                'rendered' => get_the_title($post->ID),
            ];
        }

        if (!empty($schema['properties']['content'])) {
            $data['content'] = [
                'raw' => $post->post_content,
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters('the_content', $post->post_content),
            ];
        }

        if (!empty($schema['properties']['excerpt'])) {
            $data['excerpt'] = [
                'raw' => $post->post_excerpt,
                'rendered' => $this->prepare_excerpt_response($post->post_excerpt, $post),
            ];
        }

        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);
        $response = rest_ensure_response($data);

        if (!empty($data['parent'])) {
            $response->add_link(
                'parent',
                rest_url(sprintf('%s/%s/%d', $this->namespace, $this->parent_base, $data['parent']))
            );
        }

        /**
         * Filters a revision returned from the API.
         *
         * Allows modification of the revision right before it is returned.
         *
         * @since 4.7.0
         *
         * @param Response $response The response object.
         * @param Post $post The original revision object.
         * @param Request $request Request used to generate the response.
         */
        return apply_filters('rest_prepare_revision', $response, $post, $request);
    }

    /**
     * Checks the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param string $date_gmt GMT publication time.
     * @param string|null $date Optional. Local publication time. Default null.
     * @return string|null ISO8601/RFC3339 formatted datetime, otherwise null.
     */
    protected function prepare_date_response($date_gmt, $date = null)
    {
        if ('0000-00-00 00:00:00' === $date_gmt) {
            return null;
        }

        if (isset($date)) {
            return mysql_to_rfc3339($date);
        }

        return mysql_to_rfc3339($date_gmt);
    }

    /**
     * Retrieves the revision's schema, conforming to JSON Schema.
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
            'title' => "{$this->parent_post_type}-revision",
            'type' => 'object',
            // Base properties for every Revision.
            'properties' => [
                'author' => [
                    'description' => __('The ID for the author of the object.'),
                    'type' => 'integer',
                    'context' => ['view', 'edit', 'embed'],
                ],
                'date' => [
                    'description' => __("The date the object was published, in the site's timezone."),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit', 'embed'],
                ],
                'date_gmt' => [
                    'description' => __('The date the object was published, as GMT.'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                ],
                'guid' => [
                    'description' => __('GUID for the object, as it exists in the database.'),
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'id' => [
                    'description' => __('Unique identifier for the object.'),
                    'type' => 'integer',
                    'context' => ['view', 'edit', 'embed'],
                ],
                'modified' => [
                    'description' => __("The date the object was last modified, in the site's timezone."),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                ],
                'modified_gmt' => [
                    'description' => __('The date the object was last modified, as GMT.'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                ],
                'parent' => [
                    'description' => __('The ID for the parent of the object.'),
                    'type' => 'integer',
                    'context' => ['view', 'edit', 'embed'],
                ],
                'slug' => [
                    'description' => __('An alphanumeric identifier for the object unique to its type.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                ],
            ],
        ];

        $parent_schema = $this->parent_controller->get_item_schema();

        if (!empty($parent_schema['properties']['title'])) {
            $schema['properties']['title'] = $parent_schema['properties']['title'];
        }

        if (!empty($parent_schema['properties']['content'])) {
            $schema['properties']['content'] = $parent_schema['properties']['content'];
        }

        if (!empty($parent_schema['properties']['excerpt'])) {
            $schema['properties']['excerpt'] = $parent_schema['properties']['excerpt'];
        }

        if (!empty($parent_schema['properties']['guid'])) {
            $schema['properties']['guid'] = $parent_schema['properties']['guid'];
        }

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

    /**
     * Checks the post excerpt and prepare it for single post output.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param string $excerpt The post excerpt.
     * @param Post $post Post revision object.
     * @return string Prepared excerpt or empty string.
     */
    protected function prepare_excerpt_response($excerpt, $post)
    {

        /** This filter is documented in wp-includes/post-template.php */
        $excerpt = apply_filters('the_excerpt', $excerpt, $post);

        if (empty($excerpt)) {
            return '';
        }

        return $excerpt;
    }
}
