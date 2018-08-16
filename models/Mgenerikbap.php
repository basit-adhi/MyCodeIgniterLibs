<?php

/**
 * Mgenerikbap.php
 * <br />Generik Model to use in almost all table
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/models/Mgenerikbap.php
 */
class Mgenerikbap extends CI_Model
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
    
    // --------------------------------------------------------------------
        
    /**
     * Set current table
     * @param type $table current table name
     */
    function setTable($table)
    {
        $this->table    = $table;
        $this->databasehelperbap->registerTable($this->table);
    }
    
    // --------------------------------------------------------------------
    
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
        return $this->databasehelperbap->get_selectfrom($select, $this->table, $where, $limit, $offset);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Adds a SELECT clause to a query, automatically add join, automatically add partition filter, then execute $this->CI->db->get() (table defined in $table)
     * @param type $select      The SELECT portion of a query
     * @param type $where       Filter / where portion of a query (array)
     * @param type $limit       The LIMIT clause
     * @param type $offset      The OFFSET clause
     * @return type CI_DB_result instance (same as $this->CI->db->get())
     */
    function select_multitable($select, $tables, $where = array(), $limit = null, $offset = null)
    {
        $this->databasehelperbap->registerTable($tables);
        return $this->databasehelperbap->get_selectfrom($select, $tables, $where, $limit, $offset);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert($data)
    {
        return $this->databasehelperbap->insert($this->table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes batch INSERT statement
     * @param type $data    Multidimensional associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_batch($data)
    {
        return $this->databasehelperbap->insert_batch($this->table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Executes an DELETE statement if there is an existing KEY, then compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_replaceonduplicate($data)
    {
        return $this->databasehelperbap->insert_replaceonduplicate($this->table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes an INSERT statement if there is not existing KEY
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_ignoreduplicate($data)
    {
        return $this->databasehelperbap->insert_ignoreduplicate($this->table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes an UPDATE statement
     * @param type $data    An associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update($data, $id)
    {
        return $this->databasehelperbap->update($this->table, $data, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes batch UPDATE statement
     * @param type $data    Multidimensional associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_batch($data, $id)
    {
        return $this->databasehelperbap->update_batch($this->table, $data, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * $id not null: Compiles and executes an UPDATE statement, $id null: Compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_or_insert($data, $id = null)
    {
        return ($id == null) ? $this->insert($data) : $this->update($data, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * $id not null: Compiles and executes an UPDATE statement, $id null: Executes an DELETE statement if there is an existing KEY, then compiles and executes an INSERT statement
     * @param type $data    An associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_or_insert_replaceonduplicate($data, $id = null)
    {
        return ($id == null) ? $this->insert_replaceonduplicate($data) : $this->update($data, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * $id not null: Compiles and executes an UPDATE statement, $id null: Compiles and executes an INSERT statement if there is not existing KEY
     * @param type $data    An associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_or_insert_ignoreduplicate($data, $id = null)
    {
        return ($id == null) ? $this->insert_ignoreduplicate($data) : $this->update($data, $id);
    }
    
    // --------------------------------------------------------------------
   
    /**
     * $id not null: Compiles and executes batch UPDATE statement, $id null: Compiles and executes batch INSERT statement
     * @param type $data    Multidimensional associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_batch_or_insert_batch($data, $id = null)
    {
        return ($id == null) ? $this->insert_batch($data) : $this->update_batch($data, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes a DELETE query
     * @param type $id      Key value to DELETE
     * @return type result of execution
     */
    function delete($id)
    {
       return  $this->databasehelperbap->delete($this->table, $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Show last query has been executed
     * @return type Last query string
     */
    function debug()
    {
        return $this->databasehelperbap->debug();
    }
}
