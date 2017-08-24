<?php
/**
 * Feed API: FeedCache class
 *
 * @package WordPress
 * @subpackage Feed
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\Feed;

/**
 * Core class used to implement a feed cache.
 *
 * @since 2.8.0
 *
 * @see \SimplePie_Cache
 */
class FeedCache extends \SimplePie_Cache
{

    /**
     * Creates a new SimplePie_Cache object.
     *
     * @since 2.8.0
     * @access public
     *
     * @param string $location URL location (scheme is used to determine handler).
     * @param string $filename Unique identifier for cache object.
     * @param string $extension 'spi' or 'spc'.
     * @return FeedCacheTransient Feed cache handler object that uses transients.
     */
    public function create($location, $filename, $extension)
    {
        return new FeedCacheTransient($location, $filename, $extension);
    }
}
