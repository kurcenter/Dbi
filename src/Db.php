<?php

namespace Kurcenter\Dbi;

class Db
{
    /**
     * @var \mysqli
     */
    private $connection;

    /**
     * Constructor class
     *
     * @param \mysqli $connection
     */
    public function __construct(\mysqli $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute query
     *
     * @deprecated 1.0
     * @param string $sql
     * @param array $params
     * @return \stdClass
     */
    public function query($sql, array $params = null)
    {
        if ($params !== null) {
            foreach ($params as $key => $value) {
                $sql = str_replace($key, '"' . $this->escape($value) . '"', $sql);
            }
        }

        $query = $this->connection->query($sql);
        if (!$this->connection->errno) {
            if ($query instanceof \mysqli_result) {
                $data = array();
                while ($row = $query->fetch_assoc()) {
                    $data[] = $row;
                }
                $result = new \stdClass();
                $result->num_rows = $query->num_rows;
                $result->row = isset($data[0]) ? $data[0] : array();
                $result->rows = $data;
                $query->close();

                return $result;
            } else {
                return true;
            }
        } else {
            throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql);
        }
    }

    /**
     * Execute query
     *
     * @param string $sql
     * @param array $param
     * @throws \Kurcenter\Dbi\DbException
     * @return ResultSet|bool
     */
    public function exec($sql, array $param = [])
    {
        $stmt = $this->connection->prepare($sql);
        if (!empty($this->connection->error)) {
            throw new DbException($this->connection->error);
        }
        $types = '';
        $values = [];
        foreach ($param as $field => $value) {
            if (is_int($value)) {
                $types .= 'i';
            } else if (is_double($value)) {
                $types .= 'd';
            } else if (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $values[] = &$param[$field];
        }
        if (!empty($param)) {
            call_user_func_array(array($stmt, "bind_param"), array_merge(array($types), $values));
        }
        $status = $stmt->execute();
        $query = $stmt->get_result();

        if ($query instanceof \mysqli_result) {
            return new ResultSet($stmt, $query);
        } else {
            return $status;
        }
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    /**
     * Gets the number of affected rows in a previous MySQL operation
     *
     * @return int
     */
    public function countAffected()
    {
        return $this->connection->affected_rows;
    }

    /**
     * Returns id used in the latest query
     *
     * @return int
     */
    public function getLastId()
    {
        return $this->connection->insert_id;
    }

    /**
     * Check connection
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->connection->ping();
    }

    /**
     * Returns description of the last error
     *
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     *  Sets the default client character set
     *
     * @param string $charset
     * @return boolean
     */
    public function setCharset($charset)
    {
        $this->connection->set_charset($charset);
    }

    /**
     * Returns UUID
     * 
     * @param string $serverID
     * @return string
     */
    public function uuid($serverID = 1)
    {
        $t = explode(' ', microtime());
        return sprintf(
            '%04x-%08s-%08s-%04s-%04x%04x',
            $serverID,
            $this->clientIPToHex(),
            substr('00000000' . dechex($t[1]), -8),
            substr('0000' . dechex(round($t[0] * 65536)), -4),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Convert IP to HEX
     *
     * @param string $ip
     * @return string
     */
    private function clientIPToHex($ip = '')
    {
        $hex = '';
        if ($ip == '') $ip = getEnv('REMOTE_ADDR');
        $part = explode('.', $ip);
        for ($i = 0; $i <= count($part) - 1; $i++) {
            $hex .= substr('0' . dechex($part[$i]), -2);
        }
        return $hex;
    }

    /**
     * Insert method to add new row
     *
     * @param string $table
     * @param array $data
     * @return bool
     */
    public function insert($table, $data)
    {
        $keys = '`' . implode('`, `', array_keys($data)) . '`';

        $values = array_map(
            function ($value) {
                return '?';
            },
            $data
        );
        $values = implode(',', $values);

        $sql = "INSERT INTO `{$table}` ({$keys}) VALUES ({$values})";

        return $this->exec($sql, array_values($data));
    }

    /**
     * Update query
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update($table, array $data, array $where)
    {
        $keys = array_map(
            function ($value) {
                return "`$value` = ?";
            },
            array_keys($data)
        );
        $key_str = implode(',', $keys);

        $whereKeys = array_map(
            function ($value) {
                return "`$value` = ?";
            },
            array_keys($where)
        );
        $whereStr = \implode(' AND ', $whereKeys);

        $param = array_merge(array_values($data), array_values($where));
        $sql = "UPDATE `{$table}` SET {$key_str} WHERE {$whereStr}";

        return $this->exec($sql, $param);
    }

    /**
     * Delete query
     *
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function delete($table, array $where)
    {
        $whereKeys = array_map(
            function ($value) {
                return "`$value` = ?";
            },
            array_keys($where)
        );
        $whereStr = \implode(' AND ', $whereKeys);

        $param = array_values($where);
        $sql = "DELETE FROM `{$table}` WHERE {$whereStr}";

        return $this->exec($sql, $param);
    }
}
