<?php

namespace Devtronic\FreshPress\Components\Text;

use Devtronic\FreshPress\Components\Text\Engine\NativeEngine;
use Devtronic\FreshPress\Components\Text\Engine\ShellEngine;
use Devtronic\FreshPress\Components\Text\Engine\StringEngine;
use Devtronic\FreshPress\Components\Text\Engine\XDiffEngine;

/**
 * General API for generating and formatting diffs - the differences between
 * two sequences of strings.
 *
 * The original PHP version of this code was written by Geoffrey T. Dairiki
 * <dairiki@dairiki.org>, and is used/adapted with his permission.
 *
 * Copyright 2004 Geoffrey T. Dairiki <dairiki@dairiki.org>
 * Copyright 2004-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://opensource.org/licenses/lgpl-license.php.
 *
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */
class Diff
{

    /**
     * Array of changes.
     *
     * @var array
     */
    public $_edits;

    /**
     * Computes diffs between sequences of strings.
     *
     * @param string $engine Name of the diffing engine to use.  'auto'
     *                           will automatically select the best.
     * @param array $params Parameters to pass to the diffing engine.
     *                           Normally an array of two arrays, each
     *                           containing the lines from a file.
     */
    public function __construct($engine, $params)
    {
        // Backward compatibility workaround.
        if (!is_string($engine)) {
            $params = [$engine, $params];
            $engine = 'auto';
        }

        $engine = basename($engine);
        if ($engine == 'auto') {
            $engine = extension_loaded('xdiff') ? 'xdiff' : 'native';
        }

        switch (strtolower($engine)) {
            case 'shell':
                $diff_engine = new ShellEngine();
                break;
            case 'string':
                $diff_engine = new StringEngine();
                break;
            case 'xdiff':
                $diff_engine = new XDiffEngine();
                break;
            default:
                $diff_engine = new NativeEngine();
                break;
        }

        $this->_edits = call_user_func_array([$diff_engine, 'diff'], $params);
    }

    /**
     * Returns the array of differences.
     */
    public function getDiff()
    {
        return $this->_edits;
    }

    /**
     * returns the number of new (added) lines in a given diff.
     *
     * @since Text_Diff 1.1.0
     *
     * @return integer The number of new lines
     */
    public function countAddedLines()
    {
        $count = 0;
        foreach ($this->_edits as $edit) {
            if (is_a($edit, DiffOpAdd::class) ||
                is_a($edit, DiffOpChange::class)) {
                $count += $edit->nfinal();
            }
        }
        return $count;
    }

    /**
     * Returns the number of deleted (removed) lines in a given diff.
     *
     * @since Text_Diff 1.1.0
     *
     * @return integer The number of deleted lines
     */
    public function countDeletedLines()
    {
        $count = 0;
        foreach ($this->_edits as $edit) {
            if (is_a($edit, DiffOpDelete::class) ||
                is_a($edit, DiffOpChange::class)) {
                $count += $edit->norig();
            }
        }
        return $count;
    }

    /**
     * Computes a reversed diff.
     *
     * Example:
     * <code>
     * $diff = new Diff($lines1, $lines2);
     * $rev = $diff->reverse();
     * </code>
     *
     * @return Diff  A Diff object representing the inverse of the
     *                    original diff.  Note that we purposely don't return a
     *                    reference here, since this essentially is a clone()
     *                    method.
     */
    public function reverse()
    {
        if (version_compare(zend_version(), '2', '>')) {
            $rev = clone($this);
        } else {
            $rev = $this;
        }
        $rev->_edits = [];
        foreach ($this->_edits as $edit) {
            $rev->_edits[] = $edit->reverse();
        }
        return $rev;
    }

    /**
     * Checks for an empty diff.
     *
     * @return boolean  True if two sequences were identical.
     */
    public function isEmpty()
    {
        foreach ($this->_edits as $edit) {
            if (!is_a($edit, DiffOpCopy::class)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Computes the length of the Longest Common Subsequence (LCS).
     *
     * This is mostly for diagnostic purposes.
     *
     * @return integer  The length of the LCS.
     */
    public function lcs()
    {
        $lcs = 0;
        foreach ($this->_edits as $edit) {
            if (is_a($edit, DiffOpCopy::class)) {
                $lcs += count($edit->orig);
            }
        }
        return $lcs;
    }

    /**
     * Gets the original set of lines.
     *
     * This reconstructs the $from_lines parameter passed to the constructor.
     *
     * @return array  The original sequence of strings.
     */
    public function getOriginal()
    {
        $lines = [];
        foreach ($this->_edits as $edit) {
            if ($edit->orig) {
                array_splice($lines, count($lines), 0, $edit->orig);
            }
        }
        return $lines;
    }

    /**
     * Gets the final set of lines.
     *
     * This reconstructs the $to_lines parameter passed to the constructor.
     *
     * @return array  The sequence of strings.
     */
    public function getFinal()
    {
        $lines = [];
        foreach ($this->_edits as $edit) {
            if ($edit->final) {
                array_splice($lines, count($lines), 0, $edit->final);
            }
        }
        return $lines;
    }

    /**
     * Removes trailing newlines from a line of text. This is meant to be used
     * with array_walk().
     *
     * @param string $line The line to trim.
     * @param integer $key The index of the line in the array. Not used.
     */
    public static function trimNewlines(&$line, $key)
    {
        $line = str_replace(["\n", "\r"], '', $line);
    }

    /**
     * Determines the location of the system temporary directory.
     *
     * @static
     *
     * @access protected
     *
     * @return string  A directory name which can be used for temp files.
     *                 Returns false if one could not be found.
     */
    public function _getTempDir()
    {
        $tmp_locations = [
            '/tmp',
            '/var/tmp',
            'c:\WUTemp',
            'c:\temp',
            'c:\windows\temp',
            'c:\winnt\temp'
        ];

        /* Try PHP's upload_tmp_dir directive. */
        $tmp = ini_get('upload_tmp_dir');

        /* Otherwise, try to determine the TMPDIR environment variable. */
        if (!strlen($tmp)) {
            $tmp = getenv('TMPDIR');
        }

        /* If we still cannot determine a value, then cycle through a list of
         * preset possibilities. */
        while (!strlen($tmp) && count($tmp_locations)) {
            $tmp_check = array_shift($tmp_locations);
            if (@is_dir($tmp_check)) {
                $tmp = $tmp_check;
            }
        }

        /* If it is still empty, we have failed, so return false; otherwise
         * return the directory determined. */
        return strlen($tmp) ? $tmp : false;
    }

    /**
     * Checks a diff for validity.
     *
     * This is here only for debugging purposes.
     */
    public function _check($from_lines, $to_lines)
    {
        if (serialize($from_lines) != serialize($this->getOriginal())) {
            trigger_error("Reconstructed original doesn't match", E_USER_ERROR);
        }
        if (serialize($to_lines) != serialize($this->getFinal())) {
            trigger_error("Reconstructed final doesn't match", E_USER_ERROR);
        }

        $rev = $this->reverse();
        if (serialize($to_lines) != serialize($rev->getOriginal())) {
            trigger_error("Reversed original doesn't match", E_USER_ERROR);
        }
        if (serialize($from_lines) != serialize($rev->getFinal())) {
            trigger_error("Reversed final doesn't match", E_USER_ERROR);
        }

        $prevtype = null;
        foreach ($this->_edits as $edit) {
            if ($prevtype == get_class($edit)) {
                trigger_error("Edit sequence is non-optimal", E_USER_ERROR);
            }
            $prevtype = get_class($edit);
        }

        return true;
    }
}
