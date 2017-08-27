<?php

namespace Devtronic\FreshPress\Components\Text;

/**
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class DiffOpCopy extends DiffOp
{

    /**
     * PHP5 constructor.
     */
    public function __construct($orig, $final = false)
    {
        if (!is_array($final)) {
            $final = $orig;
        }
        $this->orig = $orig;
        $this->final = $final;
    }

    public function &reverse()
    {
        $reverse = new DiffOpCopy($this->final, $this->orig);
        return $reverse;
    }
}
