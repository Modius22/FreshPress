<?php

namespace Devtronic\FreshPress\Components\Text;

/**
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class DiffOpAdd extends DiffOp
{

    /**
     * PHP5 constructor.
     */
    public function __construct($lines)
    {
        $this->final = $lines;
        $this->orig = false;
    }

    public function &reverse()
    {
        $reverse = new DiffOpDelete($this->final);
        return $reverse;
    }
}
