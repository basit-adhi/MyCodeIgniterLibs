<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * VariableBAP.php
 * <br />All Variable Handle Class
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/variableBAP.php
 */
class VariableBAP
{
    /**
     * return non-null variable
     * @param type $input       input
     * @param type $alternative alternative if input is null or not set yet
     * @return type non null output
     */
    function ifnull($input, $alternative)
    {
        return (!isset($input) || is_null($input) || trim($input) == "") ? $alternative : $input;
    }
}
