<?php

namespace Devtronic\FreshPress\Components\Text;

/**
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class DiffOpDelete extends DiffOp
{

    /**
     * PHP5 constructor.
     */
    public function __construct($lines)
    {
        $this->orig = $lines;
        $this->final = false;
    }

    public function &reverse()
    {
        $reverse = new DiffOpAdd($this->orig);
        return $reverse;
    }
}
