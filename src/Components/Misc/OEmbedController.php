<?php
/**
 * OEmbedController class, used to provide an oEmbed endpoint.
 *
 * @package WordPress
 * @subpackage Embeds
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Misc;

use Devtronic\FreshPress\Components\Rest\Request;
use Devtronic\FreshPress\Components\Rest\Server;
use Devtronic\FreshPress\Core\Error;

/**
 * oEmbed API endpoint controller.
 *
 * Registers the API route and delivers the response data.
 * The output format (XML or JSON) is handled by the REST API.
 *
 * @since 4.4.0
 */
class OEmbedController
{
    /**
     * Register the oEmbed REST API route.
     *
     * @since 4.4.0
     * @access public
     */
    public function register_routes()
    {
        /**
         * Filters the maxwidth oEmbed parameter.
         *
         * @since 4.4.0
         *
         * @param int $maxwidth Maximum allowed width. Default 600.
         */
        $maxwidth = apply_filters('oembed_default_width', 600);

        register_rest_route('oembed/1.0', '/embed', [
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_item'],
                'args' => [
                    'url' => [
                        'required' => true,
                        'sanitize_callback' => 'esc_url_raw',
                    ],
                    'format' => [
                        'default' => 'json',
                        'sanitize_callback' => 'wp_oembed_ensure_format',
                    ],
                    'maxwidth' => [
                        'default' => $maxwidth,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ],
        ]);

        register_rest_route('oembed/1.0', '/proxy', [
            [
                'methods' => Server::READABLE,
                'callback' => [$this, 'get_proxy_item'],
                'permission_callback' => [$this, 'get_proxy_item_permissions_check'],
                'args' => [
                    'url' => [
                        'description' => __('The URL of the resource for which to fetch oEmbed data.'),
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'esc_url_raw',
                    ],
                    'format' => [
                        'description' => __('The oEmbed format to use.'),
                        'type' => 'string',
                        'default' => 'json',
                        'enum' => [
                            'json',
                            'xml',
                        ],
                    ],
                    'maxwidth' => [
                        'description' => __('The maximum width of the embed frame in pixels.'),
                        'type' => 'integer',
                        'default' => $maxwidth,
                        'sanitize_callback' => 'absint',
                    ],
                    'maxheight' => [
                        'description' => __('The maximum height of the embed frame in pixels.'),
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ],
                    'discover' => [
                        'description' => __('Whether to perform an oEmbed discovery request for non-whitelisted providers.'),
                        'type' => 'boolean',
                        'default' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Callback for the embed API endpoint.
     *
     * Returns the JSON object for the post.
     *
     * @since 4.4.0
     * @access public
     *
     * @param Request $request Full data about the request.
     * @return Error|array oEmbed response data or Error on failure.
     */
    public function get_item($request)
    {
        $post_id = url_to_postid($request['url']);

        /**
         * Filters the determined post ID.
         *
         * @since 4.4.0
         *
         * @param int $post_id The post ID.
         * @param string $url The requested URL.
         */
        $post_id = apply_filters('oembed_request_post_id', $post_id, $request['url']);

        $data = get_oembed_response_data($post_id, $request['maxwidth']);

        if (!$data) {
            return new Error('oembed_invalid_url', get_status_header_desc(404), ['status' => 404]);
        }

        return $data;
    }

    /**
     * Checks if current user can make a proxy oEmbed request.
     *
     * @since 4.8.0
     * @access public
     *
     * @return true|Error True if the request has read access, Error object otherwise.
     */
    public function get_proxy_item_permissions_check()
    {
        if (!current_user_can('edit_posts')) {
            return new Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to make proxied oEmbed requests.'),
                ['status' => rest_authorization_required_code()]
            );
        }
        return true;
    }

    /**
     * Callback for the proxy API endpoint.
     *
     * Returns the JSON object for the proxied item.
     *
     * @since 4.8.0
     * @access public
     *
     * @see OEmbed::get_html()
     * @param Request $request Full data about the request.
     * @return Error|array oEmbed response data or Error on failure.
     */
    public function get_proxy_item($request)
    {
        $args = $request->get_params();

        // Serve oEmbed data from cache if set.
        $cache_key = 'oembed_' . md5(serialize($args));
        $data = get_transient($cache_key);
        if (!empty($data)) {
            return $data;
        }

        $url = $request['url'];
        unset($args['url']);

        $data = _wp_oembed_get_object()->get_data($url, $args);

        if (false === $data) {
            return new Error('oembed_invalid_url', get_status_header_desc(404), ['status' => 404]);
        }

        /**
         * Filters the oEmbed TTL value (time to live).
         *
         * Similar to the {@see 'oembed_ttl'} filter, but for the REST API
         * oEmbed proxy endpoint.
         *
         * @since 4.8.0
         *
         * @param int $time Time to live (in seconds).
         * @param string $url The attempted embed URL.
         * @param array $args An array of embed request arguments.
         */
        $ttl = apply_filters('rest_oembed_ttl', DAY_IN_SECONDS, $url, $args);

        set_transient($cache_key, $data, $ttl);

        return $data;
    }
}
