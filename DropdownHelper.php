<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Database Result Dropdown Class
 *
 * This is a DB_result converter to an associative array of options to be listed at form_dropdown() 
 * Example is in the end of this class
 * 
 * @author		Basit Adhi Prabowo
 * @link		https://codeigniter.com/user_guide/database/
 */
class DropdownHelper
{
    /**
     * Query result to "dropdown"-data.
     * @param   array   $data       data from database result
     * @param	string	$valuefield field name using as value in the dropdown
     * @param	string	$labelfield field name using as label in the dropdown
     * @return	array
     */
    function result_dropdown($data, $valuefield, $labelfield)
    {
            $returned_array = array();
            foreach ($data as $row)
            {
                $returned_array[$row[$valuefield]] = $row[$labelfield];
            }

            return $returned_array;
    }

    // --------------------------------------------------------------------

    /**
     * Query result to "json dropdown"-data.
     *
     * @param   array   $data       data from database result
     * @param	string	$labelfield field name using as label in the dropdown
     * @return	array
     */
    function result_dropdown_json($data, $labelfield)
    {
            $returned_array = array();
            foreach ($data as $row)
            {
                $returned_array[htmlentities(json_encode($row))] = $row[$labelfield];
            }

            return $returned_array;
    }
    
    /**
     * Convert "json dropdown"-data to an array
     * 
     * @param string $postdata  string from POST Method
     * @return array
     */
    function result_dropdown_json_decode($postdata)
    {
        return (array) json_decode(html_entity_decode($postdata));
    }
}

/**
EXAMPLE
 * ------------------------------
Model application/models/Mexample.php
 * ------------------------------
<?php
class Mexample extends CI_Model {

    public function getData()
    {
        $this->load->library('DropdownHelper');
        $this->db->select('id, comments');
        $this->db->from('example_table');

        $query = $this->db->get();
        return ($query->num_rows()) ? $this->dropdownhelper->result_dropdown_json($query->result('array'), 'comments') : false;
    }	
}

 * ---------------------------------------
Controller application/controllers/CSample.php
 * ---------------------------------------
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CSample extends CI_Controller {
    
    function __construct (){
        parent::__construct();
        $this->load->model('Mexample','', TRUE);
    }

    function index()
    {
        $data['dropdownoption'] = $this->MExample->getData();
        $this->load->view('VTest',$data);
    }
 
    function process()
    {
        $dropdownname = $this->dropdownhelper->result_dropdown_json_decode($this->input->post('dropdownname'));
        $id           = $dropdownname['id'];
        $comments     = $dropdownname['comments'];
        redirect('next_page');
    }
}

 * -------------------------
View application/views/VTest.html
 * -------------------------
<?
<!DOCTYPE html>
<html lang="en">
  <head>
  </head>
  <body>
     <?php 
          $attributes = array('name' => 'formname');
          echo form_open('CSample/process', $attributes);
          echo form_dropdown('dropdownname', $dropdownoption);
          echo "</form>";
     ?>
  </body>
</html>

 * ------
Generated
 * ------
If we have a data like this
id  comments
1   Hallo
2   Hai
Then it will generate dropdown like this
<select name="dropdownname">
<option value="{&quot;id&quot;:&quot;1&quot;,&quot;comments&quot;:&quot;Hallo&quot;}">Hallo</option>
<option value="{&quot;id&quot;:&quot;2&quot;,&quot;comments&quot;:&quot;Hai&quot;}">Hai</option>
<option value="{&quot;idtahunanggaran&quot;:&quot;2016&quot;,&quot;tahunanggaran&quot;:&quot;2016-2017&quot;}">2016-2017</option>
</select>
 */
