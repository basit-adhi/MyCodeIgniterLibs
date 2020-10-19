<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pageaccessrightbap.php
 * <br />Page Access Right Class, will check if a user is allow to access current page
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/Pageaccessrightbap.php
 */
class Pageaccessrightbap
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
        $this->CI->load->library('databasehelperbap');
    }
    
    /**
     * Register all current user's access page right
     * @param type $commaseparated_pagerightlist_field  Field name that hold every page's right (stored in csv format)
     * @param type $page_accessright_table              Table name where page's right is stored
     * @param type $pageid_field                        Field name that hold page's ID information
     * @param type $commaseparated_userrightlist_field  Field name that hold every user's right (stored in csv format)
     * @param type $user_accessright_table              Table name where user's right is stored
     * @param type $userid_field                        Field name that hold user's ID information
     * @param type $userid                              User ID value
     */
    function registerAccessRight($commaseparated_pagerightlist_field, $page_accessright_table, $pageid_field, $commaseparated_userrightlist_field, $user_accessright_table, $userid_field, $userid)
    {
        $userrightonpage = array();
        //get all user right
        //select $commaseparated_userrightlist_field from $user_accessright_table where $userid_field=$userid
        $resultuserright = $this->CI->databasehelperbap->get_selectfrom($commaseparated_userrightlist_field, $user_accessright_table, array($userid_field => $userid));
        if ($resultuserright->result_id->num_rows > 0)
        {
            $userrights = explode(",", $resultuserright->result_array()[0][$commaseparated_userrightlist_field]);
            foreach ($userrights as $userright_) 
            {
                //get all page right on every user right
                //select $pageid_field from $page_accessright_table where find_in_set('$userright_', '$commaseparated_pagerightlist_field')
                $this->CI->db->where("find_in_set('$userright_', '$commaseparated_pagerightlist_field')");
                $resultpageright = $this->CI->databasehelperbap->get_selectfrom($pageid_field, $page_accessright_table);
                if ($resultuserright->result_id->num_rows > 0)
                {
                    foreach($resultpageright->result_array() as $pageright_)
                    {
                        $userrightonpage[$pageright_[$pageid_field]] = $pageright_[$pageid_field];
                    }
                }
            }
            $this->CI->session->set_userdata("PageAccessRightBAP", json_encode($userrightonpage));
        }
    }

    /**
     * Is current user have right to access current page?
     * @param type $page_id Page ID
     * @return type TRUE or FALSE
     */
    function checkAccessRight($page_id)
    {
        return in_array($page_id, (array) json_decode(ifnull($this->CI->session->userdata("PageAccessRightBAP"), "[]")));
    }
}
