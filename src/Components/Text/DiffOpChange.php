<?php

namespace Devtronic\FreshPress\Components\Text;

/**
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class DiffOpChange extends DiffOp
{

    /**
     * PHP5 constructor.
     */
    public function __construct($orig, $final)
    {
        $this->orig = $orig;
        $this->final = $final;
    }

    public function &reverse()
    {
        $reverse = new DiffOpChange($this->final, $this->orig);
        return $reverse;
    }
}
