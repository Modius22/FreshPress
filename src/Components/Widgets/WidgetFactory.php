<?php
/**
 * Widget API: WidgetFactory class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\Widgets;

/**
 * Singleton that registers and instantiates Widget classes.
 *
 * @since 2.8.0
 * @since 4.4.0 Moved to its own file from wp-includes/widgets.php
 */
class WidgetFactory
{

    /**
     * Widgets array.
     *
     * @since 2.8.0
     * @access public
     * @var array
     */
    public $widgets = [];

    /**
     * PHP5 constructor.
     *
     * @since 4.3.0
     * @access public
     */
    public function __construct()
    {
        add_action('widgets_init', [$this, '_register_widgets'], 100);
    }

    /**
     * Memory for the number of times unique class instances have been hashed.
     *
     * This can be eliminated in favor of straight spl_object_hash() when 5.3
     * is the minimum requirement for PHP.
     *
     * @since 4.6.0
     * @access private
     * @var array
     *
     * @see WidgetFactory::hash_object()
     */
    private $hashed_class_counts = [];

    /**
     * Hashes an object, doing fallback of `spl_object_hash()` if not available.
     *
     * This can be eliminated in favor of straight spl_object_hash() when 5.3
     * is the minimum requirement for PHP.
     *
     * @since 4.6.0
     * @access private
     *
     * @param Widget $widget Widget.
     * @return string Object hash.
     */
    private function hash_object($widget)
    {
        if (function_exists('spl_object_hash')) {
            return spl_object_hash($widget);
        } else {
            $class_name = get_class($widget);
            $hash = $class_name;
            if (!isset($widget->_wp_widget_factory_hash_id)) {
                if (!isset($this->hashed_class_counts[$class_name])) {
                    $this->hashed_class_counts[$class_name] = 0;
                }
                $this->hashed_class_counts[$class_name] += 1;
                $widget->_wp_widget_factory_hash_id = $this->hashed_class_counts[$class_name];
            }
            $hash .= ':' . $widget->_wp_widget_factory_hash_id;
            return $hash;
        }
    }

    /**
     * Registers a widget subclass.
     *
     * @since 2.8.0
     * @since 4.6.0 Updated the `$widget` parameter to also accept a Widget instance object
     *              instead of simply a `Widget` subclass name.
     * @access public
     *
     * @param string|Widget $widget Either the name of a `Widget` subclass or an instance of a `Widget` subclass.
     */
    public function register($widget)
    {
        if ($widget instanceof Widget) {
            $this->widgets[$this->hash_object($widget)] = $widget;
        } else {
            $this->widgets[$widget] = new $widget();
        }
    }

    /**
     * Un-registers a widget subclass.
     *
     * @since 2.8.0
     * @since 4.6.0 Updated the `$widget` parameter to also accept a Widget instance object
     *              instead of simply a `Widget` subclass name.
     * @access public
     *
     * @param string|Widget $widget Either the name of a `Widget` subclass or an instance of a `Widget` subclass.
     */
    public function unregister($widget)
    {
        if ($widget instanceof Widget) {
            unset($this->widgets[$this->hash_object($widget)]);
        } else {
            unset($this->widgets[$widget]);
        }
    }

    /**
     * Serves as a utility method for adding widgets to the registered widgets global.
     *
     * @since 2.8.0
     * @access public
     *
     * @global array $wp_registered_widgets
     */
    public function _register_widgets()
    {
        global $wp_registered_widgets;
        $keys = array_keys($this->widgets);
        $registered = array_keys($wp_registered_widgets);
        $registered = array_map('_get_widget_id_base', $registered);

        foreach ($keys as $key) {
            // don't register new widget if old widget with the same id is already registered
            if (in_array($this->widgets[$key]->id_base, $registered, true)) {
                unset($this->widgets[$key]);
                continue;
            }

            $this->widgets[$key]->_register();
        }
    }
}
