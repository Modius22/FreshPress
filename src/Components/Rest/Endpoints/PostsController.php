<?php
/**
 * REST API: PostsController class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Rest\Endpoints;

use Devtronic\FreshPress\Components\Query\Query;
use Devtronic\FreshPress\Components\Rest\Fields\PostMetaFields;
use Devtronic\FreshPress\Components\Rest\Request;
use Devtronic\FreshPress\Components\Rest\Response;
use Devtronic\FreshPress\Components\Rest\Server;
use Devtronic\FreshPress\Core\Error;
use Devtronic\FreshPress\Entity\Post;
use WP_Post_Type;

/**
 * Core class to access posts via the REST API.
 *
 * @since 4.7.0
 *
 * @see Controller
 */
class PostsController extends Controller
{

    /**
     * Post type.
     *
     * @since 4.7.0
     * @access protected
     * @var string
     */
    protected $post_type;

    /**
     * Instance of a post meta fields object.
     *
     * @since 4.7.0
     * @access protected
     * @var PostMetaFields
     */
    protected $meta;

    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $post_type Post type.
     */
    public function __construct($post_type)
    {
        $this->post_type = $post_type;
        $this->namespace = 'wp/v2';
        $obj = get_post_type_object($post_type);
        $this->rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;

        $this->meta = new PostMetaFields($this->post_type);
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
            [
                'methods' => Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(Server::CREATABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        $schema = $this->get_item_schema();
        $get_item_args = [
            'context' => $this->get_context_param(['default' => 'view']),
        ];
        if (isset($schema['properties']['password'])) {
            $get_item_args['password'] = [
                'description' => __('The password for the post if it is password protected.'),
                'type' => 'string',
            ];
        }
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            'args' => [
                'id' => [
                    'description' => __('Unique identifier for the object.'),
                    'type' => 'integer',
                ],
            ],
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => $get_item_args,
            ],
            [
                'methods' => Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(Server::EDITABLE),
            ],
            [
                'methods' => Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args' => [
                    'force' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => __('Whether to bypass trash and force deletion.'),
                    ],
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Checks if a given request has access to read posts.
     *
     * @since 4.7.0
     * @access public
     *
     * @param  Request $request Full details about the request.
     * @return true|Error True if the request has read access, Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        $post_type = get_post_type_object($this->post_type);

        if ('edit' === $request['context'] && !current_user_can($post_type->cap->edit_posts)) {
            return new Error(
                'rest_forbidden_context',
                __('Sorry, you are not allowed to edit posts in this post type.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Retrieves a collection of posts.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function get_items($request)
    {

        // Ensure a search string is set in case the orderby is set to 'relevance'.
        if (!empty($request['orderby']) && 'relevance' === $request['orderby'] && empty($request['search'])) {
            return new Error(
                'rest_no_search_term_defined',
                __('You need to define a search term to order by relevance.'),
                ['status' => 400]
            );
        }

        // Ensure an include parameter is set in case the orderby is set to 'include'.
        if (!empty($request['orderby']) && 'include' === $request['orderby'] && empty($request['include'])) {
            return new Error(
                'rest_orderby_include_missing_include',
                __('You need to define an include parameter to order by include.'),
                ['status' => 400]
            );
        }

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();
        $args = [];

        /*
         * This array defines mappings between public API query parameters whose
         * values are accepted as-passed, and their internal Query parameter
         * name equivalents (some are the same). Only values which are also
         * present in $registered will be set.
         */
        $parameter_mappings = [
            'author' => 'author__in',
            'author_exclude' => 'author__not_in',
            'exclude' => 'post__not_in',
            'include' => 'post__in',
            'menu_order' => 'menu_order',
            'offset' => 'offset',
            'order' => 'order',
            'orderby' => 'orderby',
            'page' => 'paged',
            'parent' => 'post_parent__in',
            'parent_exclude' => 'post_parent__not_in',
            'search' => 's',
            'slug' => 'post_name__in',
            'status' => 'post_status',
        ];

        /*
         * For each known parameter which is both registered and present in the request,
         * set the parameter's value on the query $args.
         */
        foreach ($parameter_mappings as $api_param => $wp_param) {
            if (isset($registered[$api_param], $request[$api_param])) {
                $args[$wp_param] = $request[$api_param];
            }
        }

        // Check for & assign any parameters which require special handling or setting.
        $args['date_query'] = [];

        // Set before into date query. Date query must be specified as an array of an array.
        if (isset($registered['before'], $request['before'])) {
            $args['date_query'][0]['before'] = $request['before'];
        }

        // Set after into date query. Date query must be specified as an array of an array.
        if (isset($registered['after'], $request['after'])) {
            $args['date_query'][0]['after'] = $request['after'];
        }

        // Ensure our per_page parameter overrides any provided posts_per_page filter.
        if (isset($registered['per_page'])) {
            $args['posts_per_page'] = $request['per_page'];
        }

        if (isset($registered['sticky'], $request['sticky'])) {
            $sticky_posts = get_option('sticky_posts', []);
            if (!is_array($sticky_posts)) {
                $sticky_posts = [];
            }
            if ($request['sticky']) {
                /*
                 * As post__in will be used to only get sticky posts,
                 * we have to support the case where post__in was already
                 * specified.
                 */
                $args['post__in'] = $args['post__in'] ? array_intersect(
                    $sticky_posts,
                    $args['post__in']
                ) : $sticky_posts;

                /*
                 * If we intersected, but there are no post ids in common,
                 * Query won't return "no posts" for post__in = array()
                 * so we have to fake it a bit.
                 */
                if (!$args['post__in']) {
                    $args['post__in'] = [0];
                }
            } elseif ($sticky_posts) {
                /*
                 * As post___not_in will be used to only get posts that
                 * are not sticky, we have to support the case where post__not_in
                 * was already specified.
                 */
                $args['post__not_in'] = array_merge($args['post__not_in'], $sticky_posts);
            }
        }

        // Force the post_type argument, since it's not a user input variable.
        $args['post_type'] = $this->post_type;

        /**
         * Filters the query arguments for a request.
         *
         * Enables adding extra arguments or setting defaults for a post collection request.
         *
         * @since 4.7.0
         *
         * @link https://developer.wordpress.org/reference/classes/wp_query/
         *
         * @param array $args Key value array of query var to query value.
         * @param Request $request The request used.
         */
        $args = apply_filters("rest_{$this->post_type}_query", $args, $request);
        $query_args = $this->prepare_items_query($args, $request);

        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);

        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;
            $tax_exclude = $base . '_exclude';

            if (!empty($request[$base])) {
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy->name,
                    'field' => 'term_id',
                    'terms' => $request[$base],
                    'include_children' => false,
                ];
            }

            if (!empty($request[$tax_exclude])) {
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy->name,
                    'field' => 'term_id',
                    'terms' => $request[$tax_exclude],
                    'include_children' => false,
                    'operator' => 'NOT IN',
                ];
            }
        }

        $posts_query = new Query();
        $query_result = $posts_query->query($query_args);

        // Allow access to all password protected posts if the context is edit.
        if ('edit' === $request['context']) {
            add_filter('post_password_required', '__return_false');
        }

        $posts = [];

        foreach ($query_result as $post) {
            if (!$this->check_read_permission($post)) {
                continue;
            }

            $data = $this->prepare_item_for_response($post, $request);
            $posts[] = $this->prepare_response_for_collection($data);
        }

        // Reset filter.
        if ('edit' === $request['context']) {
            remove_filter('post_password_required', '__return_false');
        }

        $page = (int)$query_args['paged'];
        $total_posts = $posts_query->found_posts;

        if ($total_posts < 1) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset($query_args['paged']);

            $count_query = new Query();
            $count_query->query($query_args);
            $total_posts = $count_query->found_posts;
        }

        $max_pages = ceil($total_posts / (int)$posts_query->query_vars['posts_per_page']);

        if ($page > $max_pages && $total_posts > 0) {
            return new Error(
                'rest_post_invalid_page_number',
                __('The page number requested is larger than the number of pages available.'),
                ['status' => 400]
            );
        }

        $response = rest_ensure_response($posts);

        $response->header('X-WP-Total', (int)$total_posts);
        $response->header('X-WP-TotalPages', (int)$max_pages);

        $request_params = $request->get_query_params();
        $base = add_query_arg($request_params, rest_url(sprintf('%s/%s', $this->namespace, $this->rest_base)));

        if ($page > 1) {
            $prev_page = $page - 1;

            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }

            $prev_link = add_query_arg('page', $prev_page, $base);
            $response->link_header('prev', $prev_link);
        }
        if ($max_pages > $page) {
            $next_page = $page + 1;
            $next_link = add_query_arg('page', $next_page, $base);

            $response->link_header('next', $next_link);
        }

        return $response;
    }

    /**
     * Get the post, if the ID is valid.
     *
     * @since 4.7.2
     *
     * @param int $id Supplied ID.
     * @return Post|Error Post object if ID is valid, Error otherwise.
     */
    protected function get_post($id)
    {
        $error = new Error('rest_post_invalid_id', __('Invalid post ID.'), ['status' => 404]);
        if ((int)$id <= 0) {
            return $error;
        }

        $post = get_post((int)$id);
        if (empty($post) || empty($post->ID) || $this->post_type !== $post->post_type) {
            return $error;
        }

        return $post;
    }

    /**
     * Checks if a given request has access to read a post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return bool|Error True if the request has read access for the item, Error object otherwise.
     */
    public function get_item_permissions_check($request)
    {
        $post = $this->get_post($request['id']);
        if (is_wp_error($post)) {
            return $post;
        }

        if ('edit' === $request['context'] && $post && !$this->check_update_permission($post)) {
            return new Error(
                'rest_forbidden_context',
                __('Sorry, you are not allowed to edit this post.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if ($post && !empty($request['password'])) {
            // Check post password, and return error if invalid.
            if (!hash_equals($post->post_password, $request['password'])) {
                return new Error(
                    'rest_post_incorrect_password',
                    __('Incorrect post password.'),
                    ['status' => 403]
                );
            }
        }

        // Allow access to all password protected posts if the context is edit.
        if ('edit' === $request['context']) {
            add_filter('post_password_required', '__return_false');
        }

        if ($post) {
            return $this->check_read_permission($post);
        }

        return true;
    }

    /**
     * Checks if the user can access password-protected content.
     *
     * This method determines whether we need to override the regular password
     * check in core with a filter.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Post $post Post to check against.
     * @param Request $request Request data to check.
     * @return bool True if the user can access password-protected content, otherwise false.
     */
    public function can_access_password_content($post, $request)
    {
        if (empty($post->post_password)) {
            // No filter required.
            return false;
        }

        // Edit context always gets access to password-protected posts.
        if ('edit' === $request['context']) {
            return true;
        }

        // No password, no auth.
        if (empty($request['password'])) {
            return false;
        }

        // Double-check the request password.
        return hash_equals($post->post_password, $request['password']);
    }

    /**
     * Retrieves a single post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function get_item($request)
    {
        $post = $this->get_post($request['id']);
        if (is_wp_error($post)) {
            return $post;
        }

        $data = $this->prepare_item_for_response($post, $request);
        $response = rest_ensure_response($data);

        if (is_post_type_viewable(get_post_type_object($post->post_type))) {
            $response->link_header('alternate', get_permalink($post->ID), ['type' => 'text/html']);
        }

        return $response;
    }

    /**
     * Checks if a given request has access to create a post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return true|Error True if the request has access to create items, Error object otherwise.
     */
    public function create_item_permissions_check($request)
    {
        if (!empty($request['id'])) {
            return new Error('rest_post_exists', __('Cannot create existing post.'), ['status' => 400]);
        }

        $post_type = get_post_type_object($this->post_type);

        if (!empty($request['author']) && get_current_user_id() !== $request['author'] && !current_user_can($post_type->cap->edit_others_posts)) {
            return new Error(
                'rest_cannot_edit_others',
                __('Sorry, you are not allowed to create posts as this user.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!empty($request['sticky']) && !current_user_can($post_type->cap->edit_others_posts)) {
            return new Error(
                'rest_cannot_assign_sticky',
                __('Sorry, you are not allowed to make posts sticky.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!current_user_can($post_type->cap->create_posts)) {
            return new Error(
                'rest_cannot_create',
                __('Sorry, you are not allowed to create posts as this user.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!$this->check_assign_terms_permission($request)) {
            return new Error(
                'rest_cannot_assign_term',
                __('Sorry, you are not allowed to assign the provided terms.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Creates a single post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function create_item($request)
    {
        if (!empty($request['id'])) {
            return new Error('rest_post_exists', __('Cannot create existing post.'), ['status' => 400]);
        }

        $prepared_post = $this->prepare_item_for_database($request);

        if (is_wp_error($prepared_post)) {
            return $prepared_post;
        }

        $prepared_post->post_type = $this->post_type;

        $post_id = wp_insert_post(wp_slash((array)$prepared_post), true);

        if (is_wp_error($post_id)) {
            if ('db_insert_error' === $post_id->get_error_code()) {
                $post_id->add_data(['status' => 500]);
            } else {
                $post_id->add_data(['status' => 400]);
            }

            return $post_id;
        }

        $post = get_post($post_id);

        /**
         * Fires after a single post is created or updated via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         * @since 4.7.0
         *
         * @param Post $post Inserted or updated post object.
         * @param Request $request Request object.
         * @param bool $creating True when creating a post, false when updating.
         */
        do_action("rest_insert_{$this->post_type}", $post, $request, true);

        $schema = $this->get_item_schema();

        if (!empty($schema['properties']['sticky'])) {
            if (!empty($request['sticky'])) {
                stick_post($post_id);
            } else {
                unstick_post($post_id);
            }
        }

        if (!empty($schema['properties']['featured_media']) && isset($request['featured_media'])) {
            $this->handle_featured_media($request['featured_media'], $post_id);
        }

        if (!empty($schema['properties']['format']) && !empty($request['format'])) {
            set_post_format($post, $request['format']);
        }

        if (!empty($schema['properties']['template']) && isset($request['template'])) {
            $this->handle_template($request['template'], $post_id);
        }

        $terms_update = $this->handle_terms($post_id, $request);

        if (is_wp_error($terms_update)) {
            return $terms_update;
        }

        if (!empty($schema['properties']['meta']) && isset($request['meta'])) {
            $meta_update = $this->meta->update_value($request['meta'], $post_id);

            if (is_wp_error($meta_update)) {
                return $meta_update;
            }
        }

        $post = get_post($post_id);
        $fields_update = $this->update_additional_fields_for_object($post, $request);

        if (is_wp_error($fields_update)) {
            return $fields_update;
        }

        $request->set_param('context', 'edit');

        $response = $this->prepare_item_for_response($post, $request);
        $response = rest_ensure_response($response);

        $response->set_status(201);
        $response->header('Location', rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $post_id)));

        return $response;
    }

    /**
     * Checks if a given request has access to update a post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return true|Error True if the request has access to update the item, Error object otherwise.
     */
    public function update_item_permissions_check($request)
    {
        $post = $this->get_post($request['id']);
        if (is_wp_error($post)) {
            return $post;
        }

        $post_type = get_post_type_object($this->post_type);

        if ($post && !$this->check_update_permission($post)) {
            return new Error(
                'rest_cannot_edit',
                __('Sorry, you are not allowed to edit this post.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!empty($request['author']) && get_current_user_id() !== $request['author'] && !current_user_can($post_type->cap->edit_others_posts)) {
            return new Error(
                'rest_cannot_edit_others',
                __('Sorry, you are not allowed to update posts as this user.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!empty($request['sticky']) && !current_user_can($post_type->cap->edit_others_posts)) {
            return new Error(
                'rest_cannot_assign_sticky',
                __('Sorry, you are not allowed to make posts sticky.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        if (!$this->check_assign_terms_permission($request)) {
            return new Error(
                'rest_cannot_assign_term',
                __('Sorry, you are not allowed to assign the provided terms.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Updates a single post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function update_item($request)
    {
        $valid_check = $this->get_post($request['id']);
        if (is_wp_error($valid_check)) {
            return $valid_check;
        }

        $post = $this->prepare_item_for_database($request);

        if (is_wp_error($post)) {
            return $post;
        }

        // convert the post object to an array, otherwise wp_update_post will expect non-escaped input.
        $post_id = wp_update_post(wp_slash((array)$post), true);

        if (is_wp_error($post_id)) {
            if ('db_update_error' === $post_id->get_error_code()) {
                $post_id->add_data(['status' => 500]);
            } else {
                $post_id->add_data(['status' => 400]);
            }
            return $post_id;
        }

        $post = get_post($post_id);

        /** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
        do_action("rest_insert_{$this->post_type}", $post, $request, false);

        $schema = $this->get_item_schema();

        if (!empty($schema['properties']['format']) && !empty($request['format'])) {
            set_post_format($post, $request['format']);
        }

        if (!empty($schema['properties']['featured_media']) && isset($request['featured_media'])) {
            $this->handle_featured_media($request['featured_media'], $post_id);
        }

        if (!empty($schema['properties']['sticky']) && isset($request['sticky'])) {
            if (!empty($request['sticky'])) {
                stick_post($post_id);
            } else {
                unstick_post($post_id);
            }
        }

        if (!empty($schema['properties']['template']) && isset($request['template'])) {
            $this->handle_template($request['template'], $post->ID);
        }

        $terms_update = $this->handle_terms($post->ID, $request);

        if (is_wp_error($terms_update)) {
            return $terms_update;
        }

        if (!empty($schema['properties']['meta']) && isset($request['meta'])) {
            $meta_update = $this->meta->update_value($request['meta'], $post->ID);

            if (is_wp_error($meta_update)) {
                return $meta_update;
            }
        }

        $post = get_post($post_id);
        $fields_update = $this->update_additional_fields_for_object($post, $request);

        if (is_wp_error($fields_update)) {
            return $fields_update;
        }

        $request->set_param('context', 'edit');

        $response = $this->prepare_item_for_response($post, $request);

        return rest_ensure_response($response);
    }

    /**
     * Checks if a given request has access to delete a post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return true|Error True if the request has access to delete the item, Error object otherwise.
     */
    public function delete_item_permissions_check($request)
    {
        $post = $this->get_post($request['id']);
        if (is_wp_error($post)) {
            return $post;
        }

        if ($post && !$this->check_delete_permission($post)) {
            return new Error(
                'rest_cannot_delete',
                __('Sorry, you are not allowed to delete this post.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        return true;
    }

    /**
     * Deletes a single post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Request $request Full details about the request.
     * @return Response|Error Response object on success, or Error object on failure.
     */
    public function delete_item($request)
    {
        $post = $this->get_post($request['id']);
        if (is_wp_error($post)) {
            return $post;
        }

        $id = $post->ID;
        $force = (bool)$request['force'];

        $supports_trash = (EMPTY_TRASH_DAYS > 0);

        if ('attachment' === $post->post_type) {
            $supports_trash = $supports_trash && MEDIA_TRASH;
        }

        /**
         * Filters whether a post is trashable.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         * Pass false to disable trash support for the post.
         *
         * @since 4.7.0
         *
         * @param bool $supports_trash Whether the post type support trashing.
         * @param Post $post The Post object being considered for trashing support.
         */
        $supports_trash = apply_filters("rest_{$this->post_type}_trashable", $supports_trash, $post);

        if (!$this->check_delete_permission($post)) {
            return new Error(
                'rest_user_cannot_delete_post',
                __('Sorry, you are not allowed to delete this post.'),
                ['status' => rest_authorization_required_code()]
            );
        }

        $request->set_param('context', 'edit');


        // If we're forcing, then delete permanently.
        if ($force) {
            $previous = $this->prepare_item_for_response($post, $request);
            $result = wp_delete_post($id, true);
            $response = new Response();
            $response->set_data(['deleted' => true, 'previous' => $previous->get_data()]);
        } else {
            // If we don't support trashing for this type, error out.
            if (!$supports_trash) {
                return new Error(
                    'rest_trash_not_supported',
                    __('The post does not support trashing. Set force=true to delete.'),
                    ['status' => 501]
                );
            }

            // Otherwise, only trash if we haven't already.
            if ('trash' === $post->post_status) {
                return new Error(
                    'rest_already_trashed',
                    __('The post has already been deleted.'),
                    ['status' => 410]
                );
            }

            // (Note that internally this falls through to `wp_delete_post` if
            // the trash is disabled.)
            $result = wp_trash_post($id);
            $post = get_post($id);
            $response = $this->prepare_item_for_response($post, $request);
        }

        if (!$result) {
            return new Error('rest_cannot_delete', __('The post cannot be deleted.'), ['status' => 500]);
        }

        /**
         * Fires immediately after a single post is deleted or trashed via the REST API.
         *
         * They dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         * @since 4.7.0
         *
         * @param object $post The deleted or trashed post.
         * @param Response $response The response data.
         * @param Request $request The request sent to the API.
         */
        do_action("rest_delete_{$this->post_type}", $post, $response, $request);

        return $response;
    }

    /**
     * Determines the allowed query_vars for a get_items() response and prepares
     * them for Devtronic\FreshPress\Components\Query\Query.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param array $prepared_args Optional. Prepared Query arguments. Default empty array.
     * @param Request $request Optional. Full details about the request.
     * @return array Items query arguments.
     */
    protected function prepare_items_query($prepared_args = [], $request = null)
    {
        $query_args = [];

        foreach ($prepared_args as $key => $value) {
            /**
             * Filters the query_vars used in get_items() for the constructed query.
             *
             * The dynamic portion of the hook name, `$key`, refers to the query_var key.
             *
             * @since 4.7.0
             *
             * @param string $value The query_var value.
             */
            $query_args[$key] = apply_filters("rest_query_var-{$key}", $value);
        }

        if ('post' !== $this->post_type || !isset($query_args['ignore_sticky_posts'])) {
            $query_args['ignore_sticky_posts'] = true;
        }

        // Map to proper Query orderby param.
        if (isset($query_args['orderby']) && isset($request['orderby'])) {
            $orderby_mappings = [
                'id' => 'ID',
                'include' => 'post__in',
                'slug' => 'post_name',
            ];

            if (isset($orderby_mappings[$request['orderby']])) {
                $query_args['orderby'] = $orderby_mappings[$request['orderby']];
            }
        }

        return $query_args;
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
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    protected function prepare_date_response($date_gmt, $date = null)
    {
        // Use the date if passed.
        if (isset($date)) {
            return mysql_to_rfc3339($date);
        }

        // Return null if $date_gmt is empty/zeros.
        if ('0000-00-00 00:00:00' === $date_gmt) {
            return null;
        }

        // Return the formatted datetime.
        return mysql_to_rfc3339($date_gmt);
    }

    /**
     * Prepares a single post for create or update.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param Request $request Request object.
     * @return \stdClass|Error Post object or Error.
     */
    protected function prepare_item_for_database($request)
    {
        $prepared_post = new \stdClass;

        // Post ID.
        if (isset($request['id'])) {
            $existing_post = $this->get_post($request['id']);
            if (is_wp_error($existing_post)) {
                return $existing_post;
            }

            $prepared_post->ID = $existing_post->ID;
        }

        $schema = $this->get_item_schema();

        // Post title.
        if (!empty($schema['properties']['title']) && isset($request['title'])) {
            if (is_string($request['title'])) {
                $prepared_post->post_title = $request['title'];
            } elseif (!empty($request['title']['raw'])) {
                $prepared_post->post_title = $request['title']['raw'];
            }
        }

        // Post content.
        if (!empty($schema['properties']['content']) && isset($request['content'])) {
            if (is_string($request['content'])) {
                $prepared_post->post_content = $request['content'];
            } elseif (isset($request['content']['raw'])) {
                $prepared_post->post_content = $request['content']['raw'];
            }
        }

        // Post excerpt.
        if (!empty($schema['properties']['excerpt']) && isset($request['excerpt'])) {
            if (is_string($request['excerpt'])) {
                $prepared_post->post_excerpt = $request['excerpt'];
            } elseif (isset($request['excerpt']['raw'])) {
                $prepared_post->post_excerpt = $request['excerpt']['raw'];
            }
        }

        // Post type.
        if (empty($request['id'])) {
            // Creating new post, use default type for the controller.
            $prepared_post->post_type = $this->post_type;
        } else {
            // Updating a post, use previous type.
            $prepared_post->post_type = get_post_type($request['id']);
        }

        $post_type = get_post_type_object($prepared_post->post_type);

        // Post status.
        if (!empty($schema['properties']['status']) && isset($request['status'])) {
            $status = $this->handle_status_param($request['status'], $post_type);

            if (is_wp_error($status)) {
                return $status;
            }

            $prepared_post->post_status = $status;
        }

        // Post date.
        if (!empty($schema['properties']['date']) && !empty($request['date'])) {
            $date_data = rest_get_date_with_gmt($request['date']);

            if (!empty($date_data)) {
                list($prepared_post->post_date, $prepared_post->post_date_gmt) = $date_data;
                $prepared_post->edit_date = true;
            }
        } elseif (!empty($schema['properties']['date_gmt']) && !empty($request['date_gmt'])) {
            $date_data = rest_get_date_with_gmt($request['date_gmt'], true);

            if (!empty($date_data)) {
                list($prepared_post->post_date, $prepared_post->post_date_gmt) = $date_data;
                $prepared_post->edit_date = true;
            }
        }

        // Post slug.
        if (!empty($schema['properties']['slug']) && isset($request['slug'])) {
            $prepared_post->post_name = $request['slug'];
        }

        // Author.
        if (!empty($schema['properties']['author']) && !empty($request['author'])) {
            $post_author = (int)$request['author'];

            if (get_current_user_id() !== $post_author) {
                $user_obj = get_userdata($post_author);

                if (!$user_obj) {
                    return new Error('rest_invalid_author', __('Invalid author ID.'), ['status' => 400]);
                }
            }

            $prepared_post->post_author = $post_author;
        }

        // Post password.
        if (!empty($schema['properties']['password']) && isset($request['password'])) {
            $prepared_post->post_password = $request['password'];

            if ('' !== $request['password']) {
                if (!empty($schema['properties']['sticky']) && !empty($request['sticky'])) {
                    return new Error(
                        'rest_invalid_field',
                        __('A post can not be sticky and have a password.'),
                        ['status' => 400]
                    );
                }

                if (!empty($prepared_post->ID) && is_sticky($prepared_post->ID)) {
                    return new Error(
                        'rest_invalid_field',
                        __('A sticky post can not be password protected.'),
                        ['status' => 400]
                    );
                }
            }
        }

        if (!empty($schema['properties']['sticky']) && !empty($request['sticky'])) {
            if (!empty($prepared_post->ID) && post_password_required($prepared_post->ID)) {
                return new Error(
                    'rest_invalid_field',
                    __('A password protected post can not be set to sticky.'),
                    ['status' => 400]
                );
            }
        }

        // Parent.
        if (!empty($schema['properties']['parent']) && isset($request['parent'])) {
            if (0 === (int)$request['parent']) {
                $prepared_post->post_parent = 0;
            } else {
                $parent = get_post((int)$request['parent']);
                if (empty($parent)) {
                    return new Error('rest_post_invalid_id', __('Invalid post parent ID.'), ['status' => 400]);
                }
                $prepared_post->post_parent = (int)$parent->ID;
            }
        }

        // Menu order.
        if (!empty($schema['properties']['menu_order']) && isset($request['menu_order'])) {
            $prepared_post->menu_order = (int)$request['menu_order'];
        }

        // Comment status.
        if (!empty($schema['properties']['comment_status']) && !empty($request['comment_status'])) {
            $prepared_post->comment_status = $request['comment_status'];
        }

        // Ping status.
        if (!empty($schema['properties']['ping_status']) && !empty($request['ping_status'])) {
            $prepared_post->ping_status = $request['ping_status'];
        }

        /**
         * Filters a post before it is inserted via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         * @since 4.7.0
         *
         * @param \stdClass $prepared_post An object representing a single post prepared
         *                                       for inserting or updating the database.
         * @param Request $request Request object.
         */
        return apply_filters("rest_pre_insert_{$this->post_type}", $prepared_post, $request);
    }

    /**
     * Determines validity and normalizes the given status parameter.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param string $post_status Post status.
     * @param object $post_type Post type.
     * @return string|Error Post status or Error if lacking the proper permission.
     */
    protected function handle_status_param($post_status, $post_type)
    {
        switch ($post_status) {
            case 'draft':
            case 'pending':
                break;
            case 'private':
                if (!current_user_can($post_type->cap->publish_posts)) {
                    return new Error(
                        'rest_cannot_publish',
                        __('Sorry, you are not allowed to create private posts in this post type.'),
                        ['status' => rest_authorization_required_code()]
                    );
                }
                break;
            case 'publish':
            case 'future':
                if (!current_user_can($post_type->cap->publish_posts)) {
                    return new Error(
                        'rest_cannot_publish',
                        __('Sorry, you are not allowed to publish posts in this post type.'),
                        ['status' => rest_authorization_required_code()]
                    );
                }
                break;
            default:
                if (!get_post_status_object($post_status)) {
                    $post_status = 'draft';
                }
                break;
        }

        return $post_status;
    }

    /**
     * Determines the featured media based on a request param.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param int $featured_media Featured Media ID.
     * @param int $post_id Post ID.
     * @return bool|Error Whether the post thumbnail was successfully deleted, otherwise Error.
     */
    protected function handle_featured_media($featured_media, $post_id)
    {
        $featured_media = (int)$featured_media;
        if ($featured_media) {
            $result = set_post_thumbnail($post_id, $featured_media);
            if ($result) {
                return true;
            } else {
                return new Error(
                    'rest_invalid_featured_media',
                    __('Invalid featured media ID.'),
                    ['status' => 400]
                );
            }
        } else {
            return delete_post_thumbnail($post_id);
        }
    }

    /**
     * Sets the template for a post.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $template Page template filename.
     * @param integer $post_id Post ID.
     */
    public function handle_template($template, $post_id)
    {
        if (in_array($template, array_keys(wp_get_theme()->get_page_templates(get_post($post_id))), true)) {
            update_post_meta($post_id, '_wp_page_template', $template);
        } else {
            update_post_meta($post_id, '_wp_page_template', '');
        }
    }

    /**
     * Updates the post's terms from a REST request.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param int $post_id The post ID to update the terms form.
     * @param Request $request The request object with post and terms data.
     * @return null|Error Error on an error assigning any of the terms, otherwise null.
     */
    protected function handle_terms($post_id, $request)
    {
        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);

        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

            if (!isset($request[$base])) {
                continue;
            }

            $result = wp_set_object_terms($post_id, $request[$base], $taxonomy->name);

            if (is_wp_error($result)) {
                return $result;
            }
        }
    }

    /**
     * Checks whether current user can assign all terms sent with the current request.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param Request $request The request object with post and terms data.
     * @return bool Whether the current user can assign the provided terms.
     */
    protected function check_assign_terms_permission($request)
    {
        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);
        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

            if (!isset($request[$base])) {
                continue;
            }

            foreach ($request[$base] as $term_id) {
                // Invalid terms will be rejected later.
                if (!get_term($term_id, $taxonomy->name)) {
                    continue;
                }

                if (!current_user_can('assign_term', (int)$term_id)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if a given post type can be viewed or managed.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param object|string $post_type Post type name or object.
     * @return bool Whether the post type is allowed in REST.
     */
    protected function check_is_post_type_allowed($post_type)
    {
        if (!is_object($post_type)) {
            $post_type = get_post_type_object($post_type);
        }

        if (!empty($post_type) && !empty($post_type->show_in_rest)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a post can be read.
     *
     * Correctly handles posts with the inherit status.
     *
     * @since 4.7.0
     * @access public
     *
     * @param object $post Post object.
     * @return bool Whether the post can be read.
     */
    public function check_read_permission($post)
    {
        $post_type = get_post_type_object($post->post_type);
        if (!$this->check_is_post_type_allowed($post_type)) {
            return false;
        }

        // Is the post readable?
        if ('publish' === $post->post_status || current_user_can($post_type->cap->read_post, $post->ID)) {
            return true;
        }

        $post_status_obj = get_post_status_object($post->post_status);
        if ($post_status_obj && $post_status_obj->public) {
            return true;
        }

        // Can we read the parent if we're inheriting?
        if ('inherit' === $post->post_status && $post->post_parent > 0) {
            $parent = get_post($post->post_parent);
            if ($parent) {
                return $this->check_read_permission($parent);
            }
        }

        /*
         * If there isn't a parent, but the status is set to inherit, assume
         * it's published (as per get_post_status()).
         */
        if ('inherit' === $post->post_status) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a post can be edited.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param object $post Post object.
     * @return bool Whether the post can be edited.
     */
    protected function check_update_permission($post)
    {
        $post_type = get_post_type_object($post->post_type);

        if (!$this->check_is_post_type_allowed($post_type)) {
            return false;
        }

        return current_user_can($post_type->cap->edit_post, $post->ID);
    }

    /**
     * Checks if a post can be created.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param object $post Post object.
     * @return bool Whether the post can be created.
     */
    protected function check_create_permission($post)
    {
        $post_type = get_post_type_object($post->post_type);

        if (!$this->check_is_post_type_allowed($post_type)) {
            return false;
        }

        return current_user_can($post_type->cap->create_posts);
    }

    /**
     * Checks if a post can be deleted.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param object $post Post object.
     * @return bool Whether the post can be deleted.
     */
    protected function check_delete_permission($post)
    {
        $post_type = get_post_type_object($post->post_type);

        if (!$this->check_is_post_type_allowed($post_type)) {
            return false;
        }

        return current_user_can($post_type->cap->delete_post, $post->ID);
    }

    /**
     * Prepares a single post output for response.
     *
     * @since 4.7.0
     * @access public
     *
     * @param Post $post Post object.
     * @param Request $request Request object.
     * @return Response Response object.
     */
    public function prepare_item_for_response($post, $request)
    {
        $GLOBALS['post'] = $post;

        setup_postdata($post);

        $schema = $this->get_item_schema();

        // Base fields for every post.
        $data = [];

        if (!empty($schema['properties']['id'])) {
            $data['id'] = $post->ID;
        }

        if (!empty($schema['properties']['date'])) {
            $data['date'] = $this->prepare_date_response($post->post_date_gmt, $post->post_date);
        }

        if (!empty($schema['properties']['date_gmt'])) {
            // For drafts, `post_date_gmt` may not be set, indicating that the
            // date of the draft should be updated each time it is saved (see
            // #38883).  In this case, shim the value based on the `post_date`
            // field with the site's timezone offset applied.
            if ('0000-00-00 00:00:00' === $post->post_date_gmt) {
                $post_date_gmt = get_gmt_from_date($post->post_date);
            } else {
                $post_date_gmt = $post->post_date_gmt;
            }
            $data['date_gmt'] = $this->prepare_date_response($post_date_gmt);
        }

        if (!empty($schema['properties']['guid'])) {
            $data['guid'] = [
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters('get_the_guid', $post->guid),
                'raw' => $post->guid,
            ];
        }

        if (!empty($schema['properties']['modified'])) {
            $data['modified'] = $this->prepare_date_response($post->post_modified_gmt, $post->post_modified);
        }

        if (!empty($schema['properties']['modified_gmt'])) {
            // For drafts, `post_modified_gmt` may not be set (see
            // `post_date_gmt` comments above).  In this case, shim the value
            // based on the `post_modified` field with the site's timezone
            // offset applied.
            if ('0000-00-00 00:00:00' === $post->post_modified_gmt) {
                $post_modified_gmt = date(
                    'Y-m-d H:i:s',
                    strtotime($post->post_modified) - (get_option('gmt_offset') * 3600)
                );
            } else {
                $post_modified_gmt = $post->post_modified_gmt;
            }
            $data['modified_gmt'] = $this->prepare_date_response($post_modified_gmt);
        }

        if (!empty($schema['properties']['password'])) {
            $data['password'] = $post->post_password;
        }

        if (!empty($schema['properties']['slug'])) {
            $data['slug'] = $post->post_name;
        }

        if (!empty($schema['properties']['status'])) {
            $data['status'] = $post->post_status;
        }

        if (!empty($schema['properties']['type'])) {
            $data['type'] = $post->post_type;
        }

        if (!empty($schema['properties']['link'])) {
            $data['link'] = get_permalink($post->ID);
        }

        if (!empty($schema['properties']['title'])) {
            add_filter('protected_title_format', [$this, 'protected_title_format']);

            $data['title'] = [
                'raw' => $post->post_title,
                'rendered' => get_the_title($post->ID),
            ];

            remove_filter('protected_title_format', [$this, 'protected_title_format']);
        }

        $has_password_filter = false;

        if ($this->can_access_password_content($post, $request)) {
            // Allow access to the post, permissions already checked before.
            add_filter('post_password_required', '__return_false');

            $has_password_filter = true;
        }

        if (!empty($schema['properties']['content'])) {
            $data['content'] = [
                'raw' => $post->post_content,
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => post_password_required($post) ? '' : apply_filters('the_content', $post->post_content),
                'protected' => (bool)$post->post_password,
            ];
        }

        if (!empty($schema['properties']['excerpt'])) {
            /** This filter is documented in wp-includes/post-template.php */
            $excerpt = apply_filters('the_excerpt', apply_filters('get_the_excerpt', $post->post_excerpt, $post));
            $data['excerpt'] = [
                'raw' => $post->post_excerpt,
                'rendered' => post_password_required($post) ? '' : $excerpt,
                'protected' => (bool)$post->post_password,
            ];
        }

        if ($has_password_filter) {
            // Reset filter.
            remove_filter('post_password_required', '__return_false');
        }

        if (!empty($schema['properties']['author'])) {
            $data['author'] = (int)$post->post_author;
        }

        if (!empty($schema['properties']['featured_media'])) {
            $data['featured_media'] = (int)get_post_thumbnail_id($post->ID);
        }

        if (!empty($schema['properties']['parent'])) {
            $data['parent'] = (int)$post->post_parent;
        }

        if (!empty($schema['properties']['menu_order'])) {
            $data['menu_order'] = (int)$post->menu_order;
        }

        if (!empty($schema['properties']['comment_status'])) {
            $data['comment_status'] = $post->comment_status;
        }

        if (!empty($schema['properties']['ping_status'])) {
            $data['ping_status'] = $post->ping_status;
        }

        if (!empty($schema['properties']['sticky'])) {
            $data['sticky'] = is_sticky($post->ID);
        }

        if (!empty($schema['properties']['template'])) {
            if ($template = get_page_template_slug($post->ID)) {
                $data['template'] = $template;
            } else {
                $data['template'] = '';
            }
        }

        if (!empty($schema['properties']['format'])) {
            $data['format'] = get_post_format($post->ID);

            // Fill in blank post format.
            if (empty($data['format'])) {
                $data['format'] = 'standard';
            }
        }

        if (!empty($schema['properties']['meta'])) {
            $data['meta'] = $this->meta->get_value($post->ID, $request);
        }

        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);

        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

            if (!empty($schema['properties'][$base])) {
                $terms = get_the_terms($post, $taxonomy->name);
                $data[$base] = $terms ? array_values(wp_list_pluck($terms, 'term_id')) : [];
            }
        }

        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);

        // Wrap the data in a response object.
        $response = rest_ensure_response($data);

        $response->add_links($this->prepare_links($post));

        /**
         * Filters the post data for a response.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         * @since 4.7.0
         *
         * @param Response $response The response object.
         * @param Post $post Post object.
         * @param Request $request Request object.
         */
        return apply_filters("rest_prepare_{$this->post_type}", $response, $post, $request);
    }

    /**
     * Overwrites the default protected title format.
     *
     * By default, WordPress will show password protected posts with a title of
     * "Protected: %s", as the REST API communicates the protected status of a post
     * in a machine readable format, we remove the "Protected: " prefix.
     *
     * @since 4.7.0
     * @access public
     *
     * @return string Protected title format.
     */
    public function protected_title_format()
    {
        return '%s';
    }

    /**
     * Prepares links for the request.
     *
     * @since 4.7.0
     * @access protected
     *
     * @param Post $post Post object.
     * @return array Links for the given post.
     */
    protected function prepare_links($post)
    {
        $base = sprintf('%s/%s', $this->namespace, $this->rest_base);

        // Entity meta.
        $links = [
            'self' => [
                'href' => rest_url(trailingslashit($base) . $post->ID),
            ],
            'collection' => [
                'href' => rest_url($base),
            ],
            'about' => [
                'href' => rest_url('wp/v2/types/' . $this->post_type),
            ],
        ];

        if ((in_array($post->post_type, ['post', 'page'], true) || post_type_supports($post->post_type, 'author'))
            && !empty($post->post_author)) {
            $links['author'] = [
                'href' => rest_url('wp/v2/users/' . $post->post_author),
                'embeddable' => true,
            ];
        }

        if (in_array($post->post_type, ['post', 'page'], true) || post_type_supports(
                $post->post_type,
                'comments'
            )) {
            $replies_url = rest_url('wp/v2/comments');
            $replies_url = add_query_arg('post', $post->ID, $replies_url);

            $links['replies'] = [
                'href' => $replies_url,
                'embeddable' => true,
            ];
        }

        if (in_array($post->post_type, ['post', 'page'], true) || post_type_supports(
                $post->post_type,
                'revisions'
            )) {
            $links['version-history'] = [
                'href' => rest_url(trailingslashit($base) . $post->ID . '/revisions'),
            ];
        }

        $post_type_obj = get_post_type_object($post->post_type);

        if ($post_type_obj->hierarchical && !empty($post->post_parent)) {
            $links['up'] = [
                'href' => rest_url(trailingslashit($base) . (int)$post->post_parent),
                'embeddable' => true,
            ];
        }

        // If we have a featured media, add that.
        if ($featured_media = get_post_thumbnail_id($post->ID)) {
            $image_url = rest_url('wp/v2/media/' . $featured_media);

            $links['https://api.w.org/featuredmedia'] = [
                'href' => $image_url,
                'embeddable' => true,
            ];
        }

        if (!in_array($post->post_type, ['attachment', 'nav_menu_item', 'revision'], true)) {
            $attachments_url = rest_url('wp/v2/media');
            $attachments_url = add_query_arg('parent', $post->ID, $attachments_url);

            $links['https://api.w.org/attachment'] = [
                'href' => $attachments_url,
            ];
        }

        $taxonomies = get_object_taxonomies($post->post_type);

        if (!empty($taxonomies)) {
            $links['https://api.w.org/term'] = [];

            foreach ($taxonomies as $tax) {
                $taxonomy_obj = get_taxonomy($tax);

                // Skip taxonomies that are not public.
                if (empty($taxonomy_obj->show_in_rest)) {
                    continue;
                }

                $tax_base = !empty($taxonomy_obj->rest_base) ? $taxonomy_obj->rest_base : $tax;

                $terms_url = add_query_arg(
                    'post',
                    $post->ID,
                    rest_url('wp/v2/' . $tax_base)
                );

                $links['https://api.w.org/term'][] = [
                    'href' => $terms_url,
                    'taxonomy' => $tax,
                    'embeddable' => true,
                ];
            }
        }

        return $links;
    }

    /**
     * Retrieves the post's schema, conforming to JSON Schema.
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
            'title' => $this->post_type,
            'type' => 'object',
            // Base properties for every Post.
            'properties' => [
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
                    'description' => __('The globally unique identifier for the object.'),
                    'type' => 'object',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                    'properties' => [
                        'raw' => [
                            'description' => __('GUID for the object, as it exists in the database.'),
                            'type' => 'string',
                            'context' => ['edit'],
                            'readonly' => true,
                        ],
                        'rendered' => [
                            'description' => __('GUID for the object, transformed for display.'),
                            'type' => 'string',
                            'context' => ['view', 'edit'],
                            'readonly' => true,
                        ],
                    ],
                ],
                'id' => [
                    'description' => __('Unique identifier for the object.'),
                    'type' => 'integer',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'link' => [
                    'description' => __('URL to the object.'),
                    'type' => 'string',
                    'format' => 'uri',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'modified' => [
                    'description' => __("The date the object was last modified, in the site's timezone."),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'modified_gmt' => [
                    'description' => __('The date the object was last modified, as GMT.'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'slug' => [
                    'description' => __('An alphanumeric identifier for the object unique to its type.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'arg_options' => [
                        'sanitize_callback' => [$this, 'sanitize_slug'],
                    ],
                ],
                'status' => [
                    'description' => __('A named status for the object.'),
                    'type' => 'string',
                    'enum' => array_keys(get_post_stati(['internal' => false])),
                    'context' => ['view', 'edit'],
                ],
                'type' => [
                    'description' => __('Type of Post for the object.'),
                    'type' => 'string',
                    'context' => ['view', 'edit', 'embed'],
                    'readonly' => true,
                ],
                'password' => [
                    'description' => __('A password to protect access to the content and excerpt.'),
                    'type' => 'string',
                    'context' => ['edit'],
                ],
            ],
        ];

        $post_type_obj = get_post_type_object($this->post_type);

        if ($post_type_obj->hierarchical) {
            $schema['properties']['parent'] = [
                'description' => __('The ID for the parent of the object.'),
                'type' => 'integer',
                'context' => ['view', 'edit'],
            ];
        }

        $post_type_attributes = [
            'title',
            'editor',
            'author',
            'excerpt',
            'thumbnail',
            'comments',
            'revisions',
            'page-attributes',
            'post-formats',
            'custom-fields',
        ];
        $fixed_schemas = [
            'post' => [
                'title',
                'editor',
                'author',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'post-formats',
                'custom-fields',
            ],
            'page' => [
                'title',
                'editor',
                'author',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'page-attributes',
                'custom-fields',
            ],
            'attachment' => [
                'title',
                'author',
                'comments',
                'revisions',
                'custom-fields',
            ],
        ];
        foreach ($post_type_attributes as $attribute) {
            if (isset($fixed_schemas[$this->post_type]) && !in_array(
                    $attribute,
                    $fixed_schemas[$this->post_type],
                    true
                )) {
                continue;
            } elseif (!isset($fixed_schemas[$this->post_type]) && !post_type_supports($this->post_type, $attribute)) {
                continue;
            }

            switch ($attribute) {

                case 'title':
                    $schema['properties']['title'] = [
                        'description' => __('The title for the object.'),
                        'type' => 'object',
                        'context' => ['view', 'edit', 'embed'],
                        'arg_options' => [
                            'sanitize_callback' => null,
                            // Note: sanitization implemented in self::prepare_item_for_database()
                        ],
                        'properties' => [
                            'raw' => [
                                'description' => __('Title for the object, as it exists in the database.'),
                                'type' => 'string',
                                'context' => ['edit'],
                            ],
                            'rendered' => [
                                'description' => __('HTML title for the object, transformed for display.'),
                                'type' => 'string',
                                'context' => ['view', 'edit', 'embed'],
                                'readonly' => true,
                            ],
                        ],
                    ];
                    break;

                case 'editor':
                    $schema['properties']['content'] = [
                        'description' => __('The content for the object.'),
                        'type' => 'object',
                        'context' => ['view', 'edit'],
                        'arg_options' => [
                            'sanitize_callback' => null,
                            // Note: sanitization implemented in self::prepare_item_for_database()
                        ],
                        'properties' => [
                            'raw' => [
                                'description' => __('Content for the object, as it exists in the database.'),
                                'type' => 'string',
                                'context' => ['edit'],
                            ],
                            'rendered' => [
                                'description' => __('HTML content for the object, transformed for display.'),
                                'type' => 'string',
                                'context' => ['view', 'edit'],
                                'readonly' => true,
                            ],
                            'protected' => [
                                'description' => __('Whether the content is protected with a password.'),
                                'type' => 'boolean',
                                'context' => ['view', 'edit', 'embed'],
                                'readonly' => true,
                            ],
                        ],
                    ];
                    break;

                case 'author':
                    $schema['properties']['author'] = [
                        'description' => __('The ID for the author of the object.'),
                        'type' => 'integer',
                        'context' => ['view', 'edit', 'embed'],
                    ];
                    break;

                case 'excerpt':
                    $schema['properties']['excerpt'] = [
                        'description' => __('The excerpt for the object.'),
                        'type' => 'object',
                        'context' => ['view', 'edit', 'embed'],
                        'arg_options' => [
                            'sanitize_callback' => null,
                            // Note: sanitization implemented in self::prepare_item_for_database()
                        ],
                        'properties' => [
                            'raw' => [
                                'description' => __('Excerpt for the object, as it exists in the database.'),
                                'type' => 'string',
                                'context' => ['edit'],
                            ],
                            'rendered' => [
                                'description' => __('HTML excerpt for the object, transformed for display.'),
                                'type' => 'string',
                                'context' => ['view', 'edit', 'embed'],
                                'readonly' => true,
                            ],
                            'protected' => [
                                'description' => __('Whether the excerpt is protected with a password.'),
                                'type' => 'boolean',
                                'context' => ['view', 'edit', 'embed'],
                                'readonly' => true,
                            ],
                        ],
                    ];
                    break;

                case 'thumbnail':
                    $schema['properties']['featured_media'] = [
                        'description' => __('The ID of the featured media for the object.'),
                        'type' => 'integer',
                        'context' => ['view', 'edit', 'embed'],
                    ];
                    break;

                case 'comments':
                    $schema['properties']['comment_status'] = [
                        'description' => __('Whether or not comments are open on the object.'),
                        'type' => 'string',
                        'enum' => ['open', 'closed'],
                        'context' => ['view', 'edit'],
                    ];
                    $schema['properties']['ping_status'] = [
                        'description' => __('Whether or not the object can be pinged.'),
                        'type' => 'string',
                        'enum' => ['open', 'closed'],
                        'context' => ['view', 'edit'],
                    ];
                    break;

                case 'page-attributes':
                    $schema['properties']['menu_order'] = [
                        'description' => __('The order of the object in relation to other object of its type.'),
                        'type' => 'integer',
                        'context' => ['view', 'edit'],
                    ];
                    break;

                case 'post-formats':
                    // Get the native post formats and remove the array keys.
                    $formats = array_values(get_post_format_slugs());

                    $schema['properties']['format'] = [
                        'description' => __('The format for the object.'),
                        'type' => 'string',
                        'enum' => $formats,
                        'context' => ['view', 'edit'],
                    ];
                    break;

                case 'custom-fields':
                    $schema['properties']['meta'] = $this->meta->get_field_schema();
                    break;

            }
        }

        if ('post' === $this->post_type) {
            $schema['properties']['sticky'] = [
                'description' => __('Whether or not the object should be treated as sticky.'),
                'type' => 'boolean',
                'context' => ['view', 'edit'],
            ];
        }

        $schema['properties']['template'] = [
            'description' => __('The theme file to use to display the object.'),
            'type' => 'string',
            'enum' => array_merge(array_keys(wp_get_theme()->get_page_templates(null, $this->post_type)), ['']),
            'context' => ['view', 'edit'],
        ];

        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);
        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;
            $schema['properties'][$base] = [
                /* translators: %s: taxonomy name */
                'description' => sprintf(__('The terms assigned to the object in the %s taxonomy.'), $taxonomy->name),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'context' => ['view', 'edit'],
            ];
        }

        return $this->add_additional_fields_schema($schema);
    }

    /**
     * Retrieves the query params for the posts collection.
     *
     * @since 4.7.0
     * @access public
     *
     * @return array Collection parameters.
     */
    public function get_collection_params()
    {
        $query_params = parent::get_collection_params();

        $query_params['context']['default'] = 'view';

        $query_params['after'] = [
            'description' => __('Limit response to posts published after a given ISO8601 compliant date.'),
            'type' => 'string',
            'format' => 'date-time',
        ];

        if (post_type_supports($this->post_type, 'author')) {
            $query_params['author'] = [
                'description' => __('Limit result set to posts assigned to specific authors.'),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];
            $query_params['author_exclude'] = [
                'description' => __('Ensure result set excludes posts assigned to specific authors.'),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];
        }

        $query_params['before'] = [
            'description' => __('Limit response to posts published before a given ISO8601 compliant date.'),
            'type' => 'string',
            'format' => 'date-time',
        ];

        $query_params['exclude'] = [
            'description' => __('Ensure result set excludes specific IDs.'),
            'type' => 'array',
            'items' => [
                'type' => 'integer',
            ],
            'default' => [],
        ];

        $query_params['include'] = [
            'description' => __('Limit result set to specific IDs.'),
            'type' => 'array',
            'items' => [
                'type' => 'integer',
            ],
            'default' => [],
        ];

        if ('page' === $this->post_type || post_type_supports($this->post_type, 'page-attributes')) {
            $query_params['menu_order'] = [
                'description' => __('Limit result set to posts with a specific menu_order value.'),
                'type' => 'integer',
            ];
        }

        $query_params['offset'] = [
            'description' => __('Offset the result set by a specific number of items.'),
            'type' => 'integer',
        ];

        $query_params['order'] = [
            'description' => __('Order sort attribute ascending or descending.'),
            'type' => 'string',
            'default' => 'desc',
            'enum' => ['asc', 'desc'],
        ];

        $query_params['orderby'] = [
            'description' => __('Sort collection by object attribute.'),
            'type' => 'string',
            'default' => 'date',
            'enum' => [
                'author',
                'date',
                'id',
                'include',
                'modified',
                'parent',
                'relevance',
                'slug',
                'title',
            ],
        ];

        if ('page' === $this->post_type || post_type_supports($this->post_type, 'page-attributes')) {
            $query_params['orderby']['enum'][] = 'menu_order';
        }

        $post_type = get_post_type_object($this->post_type);

        if ($post_type->hierarchical || 'attachment' === $this->post_type) {
            $query_params['parent'] = [
                'description' => __('Limit result set to items with particular parent IDs.'),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];
            $query_params['parent_exclude'] = [
                'description' => __('Limit result set to all items except those of a particular parent ID.'),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];
        }

        $query_params['slug'] = [
            'description' => __('Limit result set to posts with one or more specific slugs.'),
            'type' => 'array',
            'items' => [
                'type' => 'string',
            ],
            'sanitize_callback' => 'wp_parse_slug_list',
        ];

        $query_params['status'] = [
            'default' => 'publish',
            'description' => __('Limit result set to posts assigned one or more statuses.'),
            'type' => 'array',
            'items' => [
                'enum' => array_merge(array_keys(get_post_stati()), ['any']),
                'type' => 'string',
            ],
            'sanitize_callback' => [$this, 'sanitize_post_statuses'],
        ];

        $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects'), ['show_in_rest' => true]);

        foreach ($taxonomies as $taxonomy) {
            $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

            $query_params[$base] = [
                /* translators: %s: taxonomy name */
                'description' => sprintf(
                    __('Limit result set to all items that have the specified term assigned in the %s taxonomy.'),
                    $base
                ),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];

            $query_params[$base . '_exclude'] = [
                /* translators: %s: taxonomy name */
                'description' => sprintf(
                    __('Limit result set to all items except those that have the specified term assigned in the %s taxonomy.'),
                    $base
                ),
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
                'default' => [],
            ];
        }

        if ('post' === $this->post_type) {
            $query_params['sticky'] = [
                'description' => __('Limit result set to items that are sticky.'),
                'type' => 'boolean',
            ];
        }

        /**
         * Filter collection parameters for the posts controller.
         *
         * The dynamic part of the filter `$this->post_type` refers to the post
         * type slug for the controller.
         *
         * This filter registers the collection parameter, but does not map the
         * collection parameter to an internal Query parameter. Use the
         * `rest_{$this->post_type}_query` filter to set Query parameters.
         *
         * @since 4.7.0
         *
         * @param array $query_params JSON Schema-formatted collection parameters.
         * @param WP_Post_Type $post_type Post type object.
         */
        return apply_filters("rest_{$this->post_type}_collection_params", $query_params, $post_type);
    }

    /**
     * Sanitizes and validates the list of post statuses, including whether the
     * user can query private statuses.
     *
     * @since 4.7.0
     * @access public
     *
     * @param  string|array $statuses One or more post statuses.
     * @param  Request $request Full details about the request.
     * @param  string $parameter Additional parameter to pass to validation.
     * @return array|Error A list of valid statuses, otherwise Error object.
     */
    public function sanitize_post_statuses($statuses, $request, $parameter)
    {
        $statuses = wp_parse_slug_list($statuses);

        // The default status is different in Devtronic\FreshPress\Components\Rest\Endpoints\AttachmentsController
        $attributes = $request->get_attributes();
        $default_status = $attributes['args']['status']['default'];

        foreach ($statuses as $status) {
            if ($status === $default_status) {
                continue;
            }

            $post_type_obj = get_post_type_object($this->post_type);

            if (current_user_can($post_type_obj->cap->edit_posts)) {
                $result = rest_validate_request_arg($status, $request, $parameter);
                if (is_wp_error($result)) {
                    return $result;
                }
            } else {
                return new Error(
                    'rest_forbidden_status',
                    __('Status is forbidden.'),
                    ['status' => rest_authorization_required_code()]
                );
            }
        }

        return $statuses;
    }
}
