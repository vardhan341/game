<?php

/* * *******************************************************************************************
  #                                  Database.inc.php                                           #
  # This file defines some of the php-mysql funcitons and it fecilitates to switch between      #
  # master and replication databases. When used GetInstance(true) it will switch to replication #
  # and when used GetInstance() it will use the master server.                                  #
  #                                                                                             #
  # Note: Replication is used for all select statements and Master                              #
  #       is used to all inserts deletes                                                        #
 * ***************************************************************************************** */


#/**
# * List of databases
# */
//$__HOST__ = $global_config["main"]["dbhost"];
//$__USER__ = $global_config["main"]["dbuname"];
//$__PASSWD__ = $global_config["main"]["dbpass"];
//
//$__RHOST__ = $global_config["main"]["dbhost"];
//$__RUSER__ = $global_config["main"]["dbuname"];
//$__RPASSWD__ = "";

class Database {

    var $conn;

    /**
     * @note User will not be able to use this constructor. Must only
     *       use Database::GetInstance
     */
    public function __construct($db_host, $db_user, $db_password, $db_name, $db_port) {
        /* ($password == "")? mysql_pconnect($host, $user_name): */
        $this->conn = mysqli_connect($db_host, $db_user, $db_password, $db_name, $db_port);
        return $this->conn;
    }

    public function __destruct() {
        if ($this->conn)
            mysqli_close($this->conn);
    }

    /**
     * @desc   This is the function that heart is the heart of the class. 
     *         The database object cannot be created  
     * @return  
     */
    static public function GetInstance($delayed_readonly = false) {
        static $database, $rdatabase;
        global $__HOST__, $__USER__, $__PASSWD__;    // Assuming the global connection details are given
        global $__RHOST__, $__RUSER__, $__RPASSWD__; // Assuming the global replcation details are set.
        # If the user wants to connect to a replication db for readonly
        # he should see that the delayed_readonly switch is on &
        # the replication db details are given.
        if ($delayed_readonly && $__RHOST__ && $__RUSER__) {
            if (!$rdatabase)
                $rdatabase = new Database($__RHOST__, $__RUSER__, $__RPASSWD__);
            return $rdatabase;
        }

        # Failsafe - MUST return a database conn
        if (!$database)
            $database = new Database($__HOST__, $__USER__, $__PASSWD__);
        return $database;
    }

    public function select_db($dbname) {
        mysqli_select_db($this->conn, $dbname);
    }

    public function execute($query) {
        $rs = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));// ." --- ".$query);
        return $rs;
    }

    public function multi_query($query) {
        $link = $this->conn;
        $res = $link->multi_query($query) or die(mysqli_error($this->conn));
        $rs = $link->store_result();
        return $rs;
    }

    public function error() {
        return mysqli_error($this->conn);
    }

    public function errno() {
        return mysqli_errno($this->conn);
    }

    /**
     * $result is the result which is returned by mysqli_query
     * $cursor is the cursor created in Oracle
     * $column_num is the count of the field being requested. Count starts from 0.
     */
    public function field_name($result, $cursor, $column_num) {
        return mysqli_field_name($result, $column_num, $this->conn);
    }

    /**
     * For all the below fucntions $query is the query that has to be executed !!
     * @return the number of fields in a result set.
     */
    public function num_fields($query) {
        return mysqli_num_fields($query, $this->conn);
    }

    /**
     * @return the number of rows in a result set
     */
    public function row_count($resultset) {
        return mysqli_num_rows($resultset);
    }

    /**
     * @return result row as an enumerated array 
     */
    public function fetch_row($resultset) {
        return mysqli_fetch_row($resultset);
    }

    /**
     * @result returns a row as a hash.
     */
    public function get_row_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    /**
     * @result returns a row as a array (hash or enumerated array)
     */
    public function fetch_array($result, $result_type = MYSQL_ASSOC) {
        return mysqli_fetch_array($result, $result_type);
    }

    /**
     * @changes the pointer to next row
     */
    public function move_next() {
        return mysqli_next_result($this->conn);
    }

    public function more_result() {
        return mysqli_more_results($this->conn);
    }

    public function affected() {
        return mysqli_affected_rows($this->conn);
    }

    public function last_query() {
        return mysqli_info($this->conn);
    }

    public function insert_data($data, $table) {
        $count = 0;
        $fields = $field_names = '';
        foreach ($data as $key => $value) {
            $val = "'" . mysqli_real_escape_string($this->conn, htmlentities(trim($value), ENT_QUOTES)) . "', ";
            $fields .= $val;
            $val = "";

            $key = "`" . mysqli_real_escape_string($this->conn, htmlentities(trim($key), ENT_QUOTES)) . "`, ";
            $field_names .= $key;
            $key = "";
        }
        $fields = rtrim($fields, ', ');
        $field_names = rtrim($field_names, ', ');
        $query = "INSERT INTO $table (" . $field_names . ") VALUES ( " . $fields . " )";
        return $this->execute($query);
    }

}
