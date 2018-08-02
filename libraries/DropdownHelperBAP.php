<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DropdownHelperBAP.php
 * <br />Database Result Dropdown Class
 * <br />
 * <br />This is a DB_result converter to an associative array of options to be listed at form_dropdown() 
 * <br />Example is in the end of this class
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/DropdownHelperBAP.php
 */
class DropdownHelperBAP
{
    /**
     *
     * @var CI super-object
     */
    protected $CI;

    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
        //--
        $this->CI->load->library('EncryptBAP');
    }
    
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
    function result_dropdown_json($name, $data, $labelfield)
    {
            $returned_array = array();
            foreach ($data as $row)
            {
                //bugs, somehow json_encode and openssl_encrypt cannot decode properly (in CI?)
                //we need add some string, that is for: {"firstkey 
                //from: {"firstkey":0,"secondkey":1}
                $bugsfix = str_repeat("_", strlen(key($row)) + 4);
                $returned_array[$this->CI->encryptbap->encrypt($name, $bugsfix.json_encode($row))] = $row[$labelfield];
            }

            return $returned_array;
    }
    
    /**
     * Convert "json dropdown"-data to an array
     * 
     * @param string $postdata  string from POST Method
     * @return array
     */
    function result_dropdown_json_decode($name, $postdata)
    {
        return (array) json_decode(strstr($this->CI->encryptbap->decrypt($name, $postdata), '{'));
    }
}

/**
EXAMPLE
 * ------------------------------
Model application/models/Mexample.php
 * ------------------------------
<?php
class Mexample extends CI_Model {

    public function getData($name)
    {
        $this->load->library('DropdownHelperBAP');
        $this->db->select('id, comments');
        $this->db->from('example_table');

        $query = $this->db->get();
        return ($query->num_rows()) ? $this->dropdownhelperbap->result_dropdown_json($name, $query->result('array'), 'comments') : false;
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
        $data['dropdownoption'] = $this->MExample->getData('dropdownname');
        $this->load->view('VTest',$data);
    }
 
    function process()
    {
        $dropdownname = $this->dropdownhelperbap->result_dropdown_json_decode('dropdownname', $this->input->post('dropdownname'));
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
