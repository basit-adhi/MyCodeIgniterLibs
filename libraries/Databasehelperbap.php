<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Databasehelperbap.php
 * <br />Database Helper Class
 * <br />
 * <br />This is a DB helper for consistency and simplicity
 * 
 * @author Basit Adhi Prabowo, S.T. <basit@unisayogya.ac.id>
 * @access public
 * @link https://github.com/basit-adhi/MyCodeIgniterLibs/blob/master/libraries/Databasehelperbap.php
 */
class Databasehelperbap
{
    /**
     *
     * @var array session value that stored in session
     */
    private $session_ofpartitionfield;
    /**
     *
     * @var array database's table
     */
    private $tables;
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
        $this->CI->load->helper('variablebap');
        $this->loadSession();
        $this->tables = new TableStructure();
    }

    // --------------------------------------------------------------------
    
    /**
     * Initial table structure
     * Customize this function, change all variable inside this function to fit your needs
     */
    private function loadSession()
    {
//Example:
//        $this->session_ofpartitionfield = array("tahunanggaran" => ifnull($this->CI->session->userdata("idtahunanggaran"), 0));
	  
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Register table(s) definition
     * Example 1:
     * $this->databasehelperbap->registerTable("table1");           - will register a table
     * Example 2:
     * $this->databasehelperbap->registerTable("table1, table2");   - will register more than one table
     * Customize this function (conditional switch), change to fit your tables definition
     * @param type $tablename   table(s) to select
     */
    function registerTable($tablename)
    {
        /* if $tablename consist more than 1 table (use comma), then register it 1 by 1 */
        if (strpos($tablename, ",") !== false)
        {
           foreach (explode(",", $tablename) as $singletablename)
           {
               $this->registerTable(trim($singletablename));
           }
        }
        /* register 1 table */
        elseif (!array_key_exists($tablename, $this->tables->name))
        {
            //Customize this conditional switch, change to fit your tables definition
//Example:
//            switch ($tablename)
//            {
//                case "ueu_tbl_tahunanggaran"    : $this->tables->addTableStructure($tablename, "ta", "idtahunanggaran", array(), array()); break;
//                case "ueu_tbl_unit"             : $this->tables->addTableStructure($tablename, "tu", "id_unit", array(), array("tahunanggaran" => "tahunanggaran")); break;
//                case "ueu_tbl_user"             : $this->tables->addTableStructure($tablename, "tus", "idlog", array("ueu_tbl_unit" => "id_unit"), array("tahunanggaran" => "tahunanggaran")); break;
//                default: break;
//            }
            switch ($tablename)
            {
                case "login_user"               : $this->tables->addTableStructure($tablename, "lu", "iduser", array(), array()); break;
                case "login_halaman"            : $this->tables->addTableStructure($tablename, "lh", "idhalaman", array(), array()); break;
                case "login_peran"              : $this->tables->addTableStructure($tablename, "lp", "idperan", array(), array()); break;
                case "login_hakaksesuser"       : $this->tables->addTableStructure($tablename, "lhau", "idhakaksesuser", array("login_user" => "id_user"), array()); break;
                case "login_hakakseshalaman"    : $this->tables->addTableStructure($tablename, "lhah", "idhakakseshalaman", array("login_halaman" => "idhalaman"), array()); break;
                case "login_log"                : $this->tables->addTableStructure($tablename, "ll", "idlog", array(), array()); break;
                default: break;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generate Partition Filter
     * @param type $table   table to select
     */
    private function generatePartitionFilter($table)
    {
        /* generate partition filter */
        if (!empty($this->tables->partitionkey[$table])) 
        {
            foreach ($this->tables->partitionkey[$table] as $fieldPartitionInTable => $indexPartitionInSession)
            {
                $this->CI->db->where($this->tables->tablealias[$table].".".$fieldPartitionInTable, $this->session_ofpartitionfield[$indexPartitionInSession]);
            }
        }
    }
    
    /**
     * Generate Join
     * @param type $table   table to select
     */
    private function generateJoin($table)
    {
        if (!empty($this->tables->onjoin[$table])) 
        {
            foreach ($this->tables->onjoin[$table] as $tablejoin => $field)
            {
                //only join registered tables, not all
                if (in_array($tablejoin, $this->tables->name)) 
                {
                    $this->CI->db->where($this->tables->tablealias[$table].".".$field."=".((is_array($this->tables->tablealias[$tablejoin]))?$this->tables->tablealias[$tablejoin][0].".".$this->tables->tablealias[$tablejoin][1]:$this->tables->tablealias[$tablejoin].".".$this->tables->key[$tablejoin]));
                }
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Adds a SELECT clause to a query, automatically add join, automatically add partition filter
     * @param type $select      The SELECT portion of a query
     * @param type $fromtable   table(s) to select, example1 : "table1", example2: "table1, table2" 
     */
    private function selectfrom($select, $fromtable)
    {
        $this->loadSession();
        /* convert select to array */
        $fromtables     = explode(",", $fromtable);
        /* generate select and from */
        $this->CI->db->select($select);
        $this->CI->db->from(implode_2a(",", $fromtables, select_array_from_values($this->tables->tablealias, $fromtables)));
        /* try generate join and partition filter */
        foreach ($fromtables as $table)
        {
            $this->generateJoin($table);
            $this->generatePartitionFilter($table);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Adds a SELECT clause to a query, automatically add join, automatically add partition filter, then execute $this->CI->db->get(). You can combine with CI Query Builder before use this function.
     * Same as if you use CI:
     * $this->db->select($select);
     * $this->db->from($fromtable);
     * $this->db->where($join1, $join2);        - implicit, see registerTable()
     * $this->db->where($partitionkey, $value); - implicit, see registerTable()
     * $this->db->get();
     * Example 1:
     * $this->databasehelperbap->get_selectfrom("*", "ueu_tbl_unit");
     * Example 2:
     * $this->db->order_by($order);
     * $this->db->where($customwhere);
     * $this->databasehelperbap->get_selectfrom("*", "ueu_tbl_unit");
     * @param type $select      The SELECT portion of a query
     * @param type $fromtable   table(s) to select, example1 : "table1", example2: "table1, table2" 
     * @param type $where       Filter / where portion of a query (array)
     * @param type $limit       The LIMIT clause
     * @param type $offset      The OFFSET clause
     * @return type CI_DB_result instance (same as $this->CI->db->get())
     */
    function get_selectfrom($select, $fromtable, $where = array(), $limit = null, $offset = null)
    {
        if ($limit != NULL)
        {
            $this->CI->db->limit($limit, ifnull($offset, 0));
        }
        $this->selectfrom($select, $fromtable);
        if (is_array($where) && count($where) > 0)
        {
            $this->CI->db->where($where);
        }
        return $this->get();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes an INSERT statement
     * @param type $table   table to insert
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert($table, $data)
    {
        $this->CI->db->insert($table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes batch INSERT statement
     * @param type $table   table to insert
     * @param type $data    Multidimensional associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_batch($table, $data)
    {
        $this->CI->db->insert_batch($table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Executes an DELETE statement if there is an existing KEY, then compiles and executes an INSERT statement
     * @param type $table   table to insert
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_replaceonduplicate($table, $data)
    {
        return $this->CI->db->replace($table, $data);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Executes an DELETE statement if there is an existing KEY, then compiles and executes an INSERT statement
     * @param type $table   table to insert
     * @param type $data    An associative array of field/value pairs
     * @return type result of execution
     */
    public function insert_ignoreduplicate($table, $data)
    {
        if (array_key_exists($table, $this->tables->key) && count($data) > 0)
        {
            return $this->CI->db->query("INSERT INTO $table (".implode(",", array_keys($data)).") VALUES ('".implode("','", $data)."') ON DUPLICATE KEY UPDATE ".$this->tables->key[$table]."=".$this->tables->key[$table]);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes an UPDATE statement
     * @param type $table   table to update
     * @param type $data    An associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update($table, $data, $id)
    {
        if (array_key_exists($table, $this->tables->key))
        {
            $this->generatePartitionFilter($table);
            $this->CI->db->where($this->tables->key[$table], $id);
            return $this->CI->db->update($table, $data);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Compiles and executes batch UPDATE statement
     * @param type $table   table to update
     * @param type $data    Multidimensional associative array of field/value pairs
     * @param type $id      Key value to UPDATE
     * @return type result of execution
     */
    function update_batch($table, $data, $id)
    {
        if (array_key_exists($table, $this->tables->key))
        {
            $this->generatePartitionFilter($table);
            $this->CI->db->where($this->tables->key[$table], $id);
            return $this->CI->db->update_batch($table, $data);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Adds a DELETE clause to a query
     * @param type $table   table to delete
     * @param type $id      Key value to DELETE
     * @return type result of execution
     */
    function delete($table, $id)
    {
        if (array_key_exists($table, $this->tables->key))
        {
            $this->generatePartitionFilter($table);
            $this->CI->db->where($this->tables->key[$table], $id);
            return $this->CI->db->delete($table);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Show last query has been executed
     */
    function debug()
    {
        echo $this->CI->db->last_query();;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * return CI DB
     * @return type CI DB
     */
    function db()
    {
        return $this->CI->db;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generates a platform-specific query string that counts all records returned by an Query Builder query
     * @return type CI_DB_result instance (same as $this->CI->db->get())
     */
    function get()
    {
        return $this->CI->db->get();
    }
}

class TableStructure
{
    /**
     *
     * @var array   table's name
     */
    var $name               = array();
    /**
     *
     * @var array   primary key of the table
     */
    var $key                = array();
    /**
     *
     * @var array   alias of the table
     */
    var $tablealias         = array();
    /**
     *
     * @var array   partition key 
     */
    var $partitionkey       = array();
    /**
     *
     * @var array   other table that join in the current table
     */
    var $onjoin             = array();
    
    /**
     * Add an table structure
     * @param string $tablename     table's name
     * @param string $tablealias    alias of the table
     * @param string $key           primary key of the table (without alias)
     * @param array $onjoin         other table that join in the current table (without alias), format: array("other table name" => "field in the current table that join to other table", ...)
     * @param array $partitionkey   partition key (without alias), format: array("partition key in the table" => "session index", ...)
     */
    function addTableStructure($tablename, $tablealias, $key, $onjoin = array(), $partitionkey = array())
    {
        if (!in_array_r($tablename, $this->name))
        {
            if (!in_array_r($tablealias, $this->tablealias))
            {
                $this->tablealias[$tablename]   = $tablealias;
            }
            else
            {
                echo "ERROR: Duplicate Table Alias!!";
            }
            $this->name[]                       = $tablename;
            $this->key[$tablename]              = $key;
            $this->onjoin[$tablename]           = $onjoin;
            $this->partitionkey[$tablename]     = $partitionkey;
        }
    }
        
}
/**
EXAMPLE
 * ------------------------------
Model application/models/Mexample.php
 * ------------------------------
<?php
class Mexample extends CI_Model {

    var $tabledef;

    function __construct()
    {
	parent::__construct();
        $this->load->library('DatabaseHelperBAP');
        $this->databasehelperbap->registerTable("ueu_tbl_unit,ueu_tbl_user");
    }

    //generate: select * from ueu_tbl_unit tu, ueu_tbl_user tus where tus.id_unit=tu.id_unit and tu.tahunanggaran=$this->CI->session->userdata("idtahunanggaran") and tus.tahunanggaran=$this->CI->session->userdata("idtahunanggaran")
    public function getDataSample1($name)
    {
        return $this->databasehelperbap->get_selectfrom("*", "ueu_tbl_unit,ueu_tbl_user");
    }

    //generate: select * from ueu_tbl_unit tu, ueu_tbl_user tus where tus.id_unit=tu.id_unit and tu.tahunanggaran=$this->CI->session->userdata("idtahunanggaran") and tus.tahunanggaran=$this->CI->session->userdata("idtahunanggaran") and filter1=$filter1
    public function getDataSample2($name, $filter1)
    {
        $this->databasehelperbap->db()->where("fiter1", $filter1);
        return $this->databasehelperbap->get_selectfrom("*", "ueu_tbl_unit,ueu_tbl_user");
    }
}
 */
