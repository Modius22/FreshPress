<?php
/**
 * List Table API: PostCommentsListTable class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.4.0
 */

namespace Devtronic\FreshPress\Components\ListTables;

/**
 * Core class used to implement displaying post comments in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see CommentsListTable
 */
class PostCommentsListTable extends CommentsListTable
{

    /**
     *
     * @return array
     */
    protected function get_column_info()
    {
        return [
            [
                'author' => __('Author'),
                'comment' => _x('Comment', 'column name'),
            ],
            [],
            [],
            'comment',
        ];
    }

    /**
     *
     * @return array
     */
    protected function get_table_classes()
    {
        $classes = parent::get_table_classes();
        $classes[] = 'wp-list-table';
        $classes[] = 'comments-box';
        return $classes;
    }

    /**
     *
     * @param bool $output_empty
     */
    public function display($output_empty = false)
    {
        $singular = $this->_args['singular'];

        wp_nonce_field("fetch-list-" . get_class($this), '_ajax_fetch_list_nonce');
        $classes = implode(' ', $this->get_table_classes());
        echo '<table class="' . $classes . '" style="display:none;">';

        $dataList = ($singular ? ' data-wp-lists="list:' . $singular . '"' : '');
        echo '<tbody id="the-comment-list" ' . $dataList . '>';
        if (!$output_empty) {
            $this->display_rows_or_placeholder();
        }

        echo '</tbody>';
        echo '</table>';
    }

    /**
     *
     * @param bool $comment_status
     * @return int
     */
    public function get_per_page($comment_status = false)
    {
        return 10;
    }
}
