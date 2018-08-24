<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * formbap_helper.php
 * <br />Additional CI Form Helper
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/helpers/formbap_helper.php
 */

/**
 * Print check box form
 * @param array   $data       field attributes data
 * @param array   $datavalue  data from database result
 * @param string  $valuefield field name using as value in the checkbox
 * @param string  $labelfield field name using as label in the checkbox
 * @param array   $checked    list of selected values
 * @param mixed   $extra      extra attributes to be added to the tag either as an array or a literal string
 */
function form_checkbox_bap($data, $datavalue, $valuefield, $labelfield, $checked, $extra='')
{
    $fc = "";
    $selected   = (empty($checked) || !is_array($checked)) ? array() : $checked;
    foreach ($datavalue as $row)
    {  
        $fc .= form_checkbox($data, $row[$valuefield], in_array($row[$valuefield], $selected), $extra).' '.$row[$labelfield].'<br />'; 
    }
    return $fc;
}
