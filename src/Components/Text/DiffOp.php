<?php

namespace Devtronic\FreshPress\Components\Text;

/**
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class DiffOp
{
    public $orig;
    public $final;

    public function &reverse()
    {
        trigger_error('Abstract method', E_USER_ERROR);
    }

    public function norig()
    {
        return $this->orig ? count($this->orig) : 0;
    }

    public function nfinal()
    {
        return $this->final ? count($this->final) : 0;
    }
}
