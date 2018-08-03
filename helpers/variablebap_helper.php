<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * VariableBAP.php
 * <br />All Variable Handle Helpers
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/helpers/VariableBAP.php
 */

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

/**
 * Checks if a value exists in an multidimensional array
 * source: https://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
 * @param type $needle      The searched value. If needle is a string, the comparison is done in a case-sensitive manner.
 * @param type $haystack    The array
 * @param type $strict      If the third parameter strict is set to TRUE then the in_array function will also check the types of the needle in the haystack
 * @return boolean          TRUE if needle is found in the array, FALSE otherwise
 */
function in_array_r($needle, $haystack, $strict = false) 
{
    foreach ($haystack as $item) 
    {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) 
        {
            return true;
        }
    }
    return false;
}

/**
 * Join 2 array element with a string
 * source: https://stackoverflow.com/questions/23241554/is-it-possible-to-implode-two-different-arrays-in-php
 * @param string $glue      Array's glue
 * @param array $pieces1    First array to implode
 * @param array $pieces2    Second array to implode
 * @return string           a string containing a string representation of all the array elements in the same order, with the glue string between each element
 */
function implode_2a($glue, $pieces1,  $pieces2)
{
    return join($glue, array_map(  function ($p1, $p2) { return $p1." ".$p2; }, $pieces1, $pieces2));
}
