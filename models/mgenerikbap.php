<?php

/**
 * mgenerikbap.php
 * <br />Generik Model to use in almost all table
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/models/MgenerikBAP.php
 */
class MgenerikBAP extends CI_Model
{
    /**
     *
     * @var type current table name
     */
    private $table;
    
    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    function __construct()
    {
	parent::__construct();
        $this->load->library('DatabaseHelperBAP');
    }
    
    /**
     * Set current table
     * @param type $table current table name
     */
    function setTable($table)
    {
        $this->table    = $table;
    }
    
    /**
     * Adds a SELECT clause to a query, automatically add join, automatically add partition filter, then execute $this->CI->db->get() (current table, $this->table)
     * @param type $select      The SELECT portion of a query
     * @param type $where       Filter / where portion of a query (array)
     * @param type $limit       The LIMIT clause
     * @param type $offset      The OFFSET clause
     * @return type CI_DB_result instance (same as $this->CI->db->get())
     */
    function select($select, $where = array(), $limit = null, $offset = null)
    {
        $this->databasehelperbap->registerTable($this->table);
        return $this->databasehelperbap->get_selectfrom($select, $this->table, $where, $limit, $offset);
    }
    
    /**
     * Adds a SELECT clause to a query, automatically add join, automatically add partition filter, then execute $this->CI->db->get() (table defined in $table)
     * @param type $select      The SELECT portion of a query
     * @param type $where       Filter / where portion of a query (array)
     * @param type $limit       The LIMIT clause
     * @param type $offset      The OFFSET clause
     * @return type CI_DB_result instance (same as $this->CI->db->get())
     */
    function select_multitable($select, $table, $where = array(), $limit = null, $offset = null)
    {
        $this->databasehelperbap->registerTable($table);
        return $this->databasehelperbap->get_selectfrom($select, $table, $where, $limit, $offset);
    }
    
    /**
     * Compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     */
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
    }
    
    /**
     * Compiles and executes batch INSERT statement
     * @param type $data    Multidimentional associative array of field/value pairs
     */
    public function insert_batch($data)
    {
        $this->db->insert_batch($this->table, $data);
    }
    
    /**
     * Executes an DELETE statement if there is an existing KEY, then compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     */
    public function insert_replaceonduplicate($data)
    {
        $this->db->replace($this->table, $data);
    }
    
    /**
     * Compiles and executes an UPDATE statement
     * @param type $data    An associative array of field/value pairs
     * @param type $where   The WHERE clause
     */
    function update($data, $where)
    {
        $this->db->where($where);
        $this->db->update($this->table, $data);
    }
    
    /**
     * Compiles and executes batch UPDATE statement
     * @param type $data    An associative array of field/value pairs
     * @param type $where   The WHERE clause
     */
    function update_batch($data, $where)
    {
        $this->db->where($where);
        $this->db->update_batch($this->table, $data);
    }
    
    /**
     * Compiles and executes a DELETE query
     * @param type $where   The WHERE clause
     */
    function delete($where)
    {
        $this->db->delete($this->table, $where);
    }
}
