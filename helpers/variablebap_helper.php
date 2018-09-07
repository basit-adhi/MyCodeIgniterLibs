<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * variablebap_helper.php
 * <br />All Variable Handle Helpers
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/helpers/variablebap_helper.php
 */

/**
 * return non-null value. If input non null then return input else return alternative
 * Example:
 * $a = 12;
 * echo ifnull($a, 0);  - output: 12
 * echo ifnull($x, 0);  - output: 0
 * @param type $input       Input to check
 * @param type $alternative Alternative if input is null or not set yet
 * @return type non null output
 */
function ifnull($input, $alternative)
{
    return (!isset($input) || is_null($input) || trim($input) == "") ? $alternative : $input;
}

/**
 * return non-null value caused by no index found. If index is found then return array[index] else return alternative
 * Example:
 * $a = array("an" => 12, "two" => b);
 * echo ifnoindexarray("an", $a, 0); - output: 12
 * echo ifnoindexarray("z", $a, 0);  - output: 0
 * @param type $index       Value to check
 * @param type $array       An array with keys to check
 * @param type $alternative Alternative if input is null or not set yet
 * @return type non null output
 */
function ifnoindexarray($index, $array, $alternative)
{
    return (!array_key_exists($index, $array)) ? $alternative : $array[$index];
}

/**
 * Checks if a value exists in an multidimensional array
 * source: https://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
 * Example:
 * $a = array(array(1,2), array("x" => 4));
 * echo in_array_r("x", $a);    - output: FALSE, remember: this function will not checks array index
 * echo in_array_r(1, $a);      - output: TRUE
 * @param type $needle      The searched value. If needle is a string, the comparison is done in a case-sensitive manner.
 * @param type $haystack    The array
 * @param type $strict      If the third parameter strict is set to TRUE then the in_array function will also check the types of the needle in the haystack
 * @return boolean          TRUE if needle is found in the array, FALSE otherwise
 */
function in_array_r($needle, $haystack, $strict = false) 
{
    reset($haystack);
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
 * Checks if a key exists in an multidimensional array
 * just like in_array_r()
 * Example:
 * $a = array(array(1,2), array("x" => 4));
 * echo array_key_exists_r("x", $a);    - output: TRUE, remember: this function will checks array index
 * echo in_array_r(1, $a);              - output: FALSE
 * @param type $key         Value to check
 * @param type $array       The array
 * @return boolean          TRUE if key is found as key in the array, FALSE otherwise
 */
function array_key_exists_r($key, $array) 
{
    reset($array);
    foreach ($array as $k=>$item) 
    {
        if (($k == $key) || (is_array($item) && array_key_exists_r($key, $item))) 
        {
            return true;
        }
    }
    return false;
}

/**
 * Join 2 array element with a string
 * source: https://stackoverflow.com/questions/23241554/is-it-possible-to-implode-two-different-arrays-in-php
 * Example:
 * $a = array(1, 2, 3);
 * $b = array("one", "two", "three");
 * implode_2a(",", $a, $b);             - output: 1 one, 2 two, 3 three
 * @param string $glue      Array's glue
 * @param array $pieces1    First array to implode
 * @param array $pieces2    Second array to implode
 * @return string           a string containing a string representation of all the array elements in the same order, with the glue string between each element
 */
function implode_2a($glue, $pieces1,  $pieces2)
{
    return join($glue, array_map(  function ($p1, $p2) { return $p1." ".$p2; }, $pieces1, $pieces2));
}

/**
 * Select an array from array of value
 * Example:
 * $a = array("s", "xl");
 * $b = array("s" => "small", "l" => "large", "xl" => "extra large");
 * print_r(select_array_from_values($b, $a));                           - output: Array ( [s] => small [xl] => extra large ) 
 * @param type $array       Array to select by index
 * @param type $arrayvalues Array of value use to select the Array
 * @return type Selected array
 */
function select_array_from_values($array, $arrayvalues)
{
    $selectedarray  = array();
    reset($arrayvalues);
    foreach ($arrayvalues as $value)
    {
        if (array_key_exists(trim($value), $array)) 
        {
            $selectedarray[trim($value)]  = $array[trim($value)];
        }
    }
    return $selectedarray;
}

/**
 * Create a text from text of index that select on the array
 * Example
 * $a = Array ( "1" => "one", "2" => "two" );
 * echo text_from_array(",", "2,1,2", $a);                              - output: two,one,two
 * @param type $delimiter   The boundary of string
 * @param type $indextext   Selected index
 * @param type $array       Array source
 * @return type Text from array
 */
function text_from_array($delimiter, $indextext, $array)
{
    reset($array);
    $result     = array();
    $indexarray = explode_ns($delimiter, $indextext);
    foreach ($indexarray as $itext)
    {
        if (array_key_exists($itext, $array))
        {
            $result[]   = $array[$itext];
        }
    }
    return implode($delimiter, $result);
}

/**
 * Create new 1d array from array
 * Example
 * $a = Array ( "0" => Array ( "id" => 1, "code" => 1, "name" => "academic" ), "1" => Array ( "id" => 9, "code" => 2, "name" => "resource" ) );
 * print_r(array_from_array($a, "code", "name"));                       -  output: Array ( [1] => academic [2] => resource ) 
 * @param type $array           source array
 * @param type $indexasindex    index of $array as index in the new array
 * @param type $indexasvalue    value of $array as index in the new array
 * @return type New array
 */
function array_from_array($array, $indexasindex, $indexasvalue)
{
    if (array_key_exists_r($indexasindex, $array) && array_key_exists_r($indexasvalue, $array))
    {
        reset($array);
        foreach($array as $arr)
        {
            $newarray[$arr[$indexasindex]]  = $arr[$indexasvalue];
        }
        return $newarray;
    }
    else
    {
        return array();
    }
}

/**
 * Split a string by string, but trim any space (no space)
 * @param type $delimiter   The boundary string
 * @param type $string      The input string
 * @param type $limit       The limit
 * @return type array from splitting text
 */
function explode_ns($delimiter, $string, $limit = INT_MAX)
{
    return explode($delimiter, str_replace(" ", "", $string), $limit);
}
