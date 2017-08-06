<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 4.7.0
 */

namespace Devtronic\FreshPress\Components\ListTables;

/**
 * Helper class to be used only by back compat functions
 *
 * @since 3.1.0
 */
class ListTableCompat extends ListTable
{
    public $_screen;
    public $_columns;

    public function __construct($screen, $columns = [])
    {
        if (is_string($screen)) {
            $screen = convert_to_screen($screen);
        }

        $this->_screen = $screen;

        if (!empty($columns)) {
            $this->_columns = $columns;
            add_filter('manage_' . $screen->id . '_columns', [$this, 'get_columns'], 0);
        }
    }

    /**
     * @access protected
     *
     * @return array
     */
    protected function get_column_info()
    {
        $columns = get_column_headers($this->_screen);
        $hidden = get_hidden_columns($this->_screen);
        $sortable = [];
        $primary = $this->get_default_primary_column_name();

        return [$columns, $hidden, $sortable, $primary];
    }

    /**
     * @access public
     *
     * @return array
     */
    public function get_columns()
    {
        return $this->_columns;
    }
}
