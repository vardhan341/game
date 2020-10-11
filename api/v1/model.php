<?php

class GameDB
{

    public $dbc;
    public $cfg;

    public function __construct($maindbc, $mainconfig)
    {
        $this->dbc = $maindbc;
        $this->cfg = $mainconfig;
    }

    function get_user($data, $where)
    {
        $details = $this->get(USER, $data, $where);
        return $details;
    }

    function update_user($data, $where)
    {
        $details = $this->put(USER, $data, $where);
        return $details;
    }

    function add_token($data)
    {
        $details = $this->post(TOKEN, $data);
        return $details;
    }

    function get_token($data, $where)
    {
        $details = $this->get(TOKEN, $data, $where);
        return $details;
    }

    function update_token($data, $where)
    {
        $details = $this->put(TOKEN, $data, $where);
        return $details;
    }

    function add_user($data)
    {
        $details = $this->post(USER, $data);
        return $details;
    }

    function get_games_list($data, $where)
    {
        $details = $this->get(GAMES_LIST, $data, $where);
        return $details;
    }

    function update_games_list($data, $where)
    {
        $details = $this->put(GAMES_LIST, $data, $where);
        return $details;
    }

    function add_games_list($data)
    {
        $details = $this->post(GAMES_LIST, $data);
        return $details;
    }

    function get_game_log($data, $where)
    {
        $details = $this->get(MATCH_LOG, $data, $where);
        return $details;
    }

    function update_game_log($data, $where)
    {
        $details = $this->put(MATCH_LOG, $data, $where);
        return $details;
    }

    function add_game_log($data)
    {
        $details = $this->post(MATCH_LOG, $data);
        return $details;
    }

    function delete_auth_data($where)
    {
        $details = $this->delete(TOKEN, $where);
        return $details;
    }

    function filter_input($data)
    {
        $type = gettype($data);
        if ($type == "array") {
            foreach ($data as $key => $value) {
                $data[$key] = mysqli_real_escape_string($this->dbc->conn, !$this->isJson($value) ? htmlentities(trim($value), ENT_QUOTES) : $value);
            }
        } else if ($type == "integer") {
            $data = intval($data);
        } else if ($type == "string") {
            $data = mysqli_real_escape_string($this->dbc->conn, !$this->isJson($data) ? htmlentities(trim($data), ENT_QUOTES) : $data);
        }
        return $data;
    }

    function get_count($table, $data, $where)
    {
        $select_fields = "";
        if (isset($data) && is_array($data) && !empty($data)) {
            $data = $this->filter_input($data);
            foreach ($data as $key => $value) {
                $select_fields .= " $key = '" . $value . "', ";
            }
            $select_fields = rtrim($select_fields, ", ");
        } else {
            $select_fields = "*";
        }


        $where_str = "";
        if (isset($where) && is_array($where)) {
            $where_str .= " WHERE ";
            $where = $this->filter_input($where);
            foreach ($where as $key => $value) {
                $where_str .= " $key= '" . $value . "' AND ";
            }
            $where_str = rtrim($where_str, " AND ");
        }

        $query = "SELECT $select_fields FROM " . $table . " $where_str";

        $x = $this->dbc->execute($query);
        $count = $this->dbc->row_count($x);
        if ($count > 0) {
            return $count;
        }
        return false;
    }

    function start_transaction()
    {
        $query = 'START TRANSACTION;';
        $x = $this->dbc->execute($query);
        if ($x) {
            return true;
        } else {
            return false;
        }
    }

    function commit_transaction()
    {
        $query = 'COMMIT;';
        $x = $this->dbc->execute($query);
        if ($x) {
            return true;
        } else {
            return false;
        }
    }

    function rollback_transaction()
    {
        $query = 'ROLLBACK;';
        $x = $this->dbc->execute($query);
        if ($x) {
            return true;
        } else {
            return false;
        }
    }

    function get($table, $data, $where)
    {
        $select_fields = "";
        if (isset($data) && is_array($data) && !empty($data)) {
            $data = $this->filter_input($data);
            foreach ($data as $key => $value) {
                $select_fields .= $value . ", ";
            }
            $select_fields = rtrim($select_fields, ", ");
        } else {
            $select_fields = "*";
        }


        $where_str = "";
        if (isset($where) && is_array($where) && !empty($where)) {
            $where_str .= " WHERE ";
            $where = $this->filter_input($where);
            foreach ($where as $key => $value) {
                $where_str .= " $key= '" . $value . "' AND ";
            }
            $where_str = rtrim($where_str, " AND ");
        }

        $query = "SELECT $select_fields FROM " . $table . " $where_str";

        $x = $this->dbc->execute($query);
        if ($this->dbc->row_count($x) > 0) {
            while ($row = $this->dbc->get_row_assoc($x)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }

    function get_advance($details)
    {
        if (isset($details["query"])) {
            $query = $details["query"];
        } else {
            $select_fields = "";
            if (isset($details['data']) && is_array($details['data'])) {
                if (empty($details['data'])) {
                    $select_fields = "*";
                } else {
                    $data = $this->filter_input($details['data']);
                    foreach ($data as $key => $value) {
                        $select_fields .= $value . ", ";
                    }
                    $select_fields = rtrim($select_fields, ", ");
                }
            } else {
                $select_fields = "*";
            }


            $where_str = "";
            if (isset($details['where'])) {
                if (is_array($details['where'])) {
                    $where_str .= " WHERE ";
                    $where = $this->filter_input($details['where']);
                    foreach ($where as $key => $value) {
                        $where_str .= " $key = '" . $value . "' AND ";
                    }
                    $where_str = rtrim($where_str, " AND ");
                } else if (is_string($details['where'])) {
                    $where_str .= " WHERE ";
                    $where_str .= str_replace('where', '', $details['where']);
                }
            }

            $order_str = "";
            if (isset($details['orderby'])) {
                $order_str = trim($details['orderby']);
            }

            $group_str = "";
            if (isset($details['groupby'])) {
                $order_str = $details['groupby'];
            }
            $limit_str = "";
            if (isset($details['limit'])) {
                $limit_str = $details['limit'];
            }

            $query = "SELECT $select_fields FROM " . $details['table'] . " $where_str $group_str $order_str $limit_str";
        }

        $x = $this->dbc->execute($query);

        if ($this->dbc->row_count($x) > 0) {
            while ($row = $this->dbc->get_row_assoc($x)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }

    function post($table, $data)
    {
        $field_values = $field_names = '';
        foreach ($data as $key => $value) {
            $val = "'" . mysqli_real_escape_string($this->dbc->conn, !$this->isJson($value) ? htmlentities(trim($value), ENT_QUOTES) : $value) . "', ";
            $field_values .= $val;
            $val = "";

            /* $val = "'" . $value . "', ";
            $field_values .= stripslashes(mysqli_real_escape_string($this->dbc->conn, $val));
            $val = ""; */

            $key = "`" . $key . "`, ";
            $field_names .= mysqli_real_escape_string($this->dbc->conn, $key);
            $key = "";
        }
        $field_values = rtrim($field_values, ", ");
        $field_names = rtrim($field_names, ", ");
        $query = "INSERT INTO $table (" . $field_names . ") VALUES ( " . $field_values . " )";
        return $this->dbc->execute($query);
    }

    function multi_post($table, $data)
    {
        //checking multi dementional array or not
        if (count($data) == count($data, COUNT_RECURSIVE)) {
            return FALSE;
        }
        //checking associative array or not
        if (array_keys($data) !== range(0, count($data) - 1)) {
            return FALSE;
        }
        $field_values = $multiple_values_txt = '';
        foreach ($data as $key_1 => $value_1) {
            $field_names = $field_values = '';
            foreach ($value_1 as $key => $value) {
                $val = "'" . mysqli_real_escape_string($this->dbc->conn, !$this->isJson($value) ? htmlentities(trim($value), ENT_QUOTES) : $value) . "', ";
                $field_values .= $val;
                $val = "";

                //            $val = "'" . $value . "', ";
                //            $field_values .= stripslashes(mysqli_real_escape_string($this->dbc->conn, $val));
                //            $val = "";

                $key = "`" . $key . "`, ";
                $field_names .= mysqli_real_escape_string($this->dbc->conn, $key);
                $key = "";
            }
            $field_values = rtrim($field_values, ", ");
            $field_names = rtrim($field_names, ", ");
            $multiple_values_txt .= "( " . $field_values . " ),";
        }
        $multiple_values_txt = rtrim($multiple_values_txt, ", ");
        $query = "INSERT INTO $table (" . $field_names . ") VALUES " . $multiple_values_txt . ";";
        return $this->dbc->execute($query);
    }

    function put($table, $data, $where)
    {
        $update_fields = "";
        if (
            isset($data) && is_array($data)
        ) {
            $data = $this->filter_input($data);
            foreach ($data as $key => $value) {
                $update_fields .= " $key = '" . $value . "', ";
            }
            $update_fields = rtrim($update_fields, ", ");
        }

        $where_str = "";
        if (isset($where) && is_array($where)) {
            $where_str .= " WHERE ";
            $where = $this->filter_input($where);
            foreach ($where as $key => $value) {
                $where_str .= " $key= '" . $value . "' AND ";
            }
            $where_str = rtrim($where_str, " AND ");
        }

        $query = "UPDATE " . $table . " SET " . $update_fields . $where_str;

        $x = $this->dbc->execute($query);
        if ($this->dbc->affected() > 0) {
            return true;
        }
        return false;
    }

    function put_advance($details)
    {
        if (isset($details["query"])) {
            $query = $details["query"];
        } else {
            $update_fields = "";
            if (isset($details['data']) && is_array($details['data'])) {
                $data = $this->filter_input($details['data']);
                foreach ($data as $key => $value) {
                    $update_fields .= " $key = '" . $value . "', ";
                }
                $update_fields = rtrim($update_fields, ", ");
            }


            $where_str = "";
            if (isset($details['where'])) {
                if (is_array($details['where'])) {
                    $where_str .= " WHERE ";
                    $where = $this->filter_input($details['where']);
                    foreach ($where as $key => $value) {
                        $where_str .= " $key = '" . $value . "' AND ";
                    }
                    $where_str = rtrim($where_str, " AND ");
                } else if (is_string($details['where'])) {
                    $where_str .= " WHERE ";
                    $where_str .= str_replace('where', '', $details['where']);
                }
            }

            $query = "UPDATE " . $details['table'] . " SET $update_fields $where_str";
        }

        $x = $this->dbc->execute($query);
        if ($this->dbc->affected() > 0) {
            return true;
        }
        return false;
    }

    function delete($table, $where)
    {

        $where_str = "";
        if (isset($where) && is_array($where)) {
            $where_str .= " WHERE";
            $where = $this->filter_input($where);
            foreach ($where as $key => $value) {
                $where_str .= " $key = '" . $value . "' AND ";
            }
            $where_str = rtrim($where_str, " AND ");
        }

        $query = "DELETE FROM " . $table . $where_str;

        $x = $this->dbc->execute($query);
        if ($this->dbc->affected($x) > 0) {
            return true;
        }
        return false;
    }

    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
