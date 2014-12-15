<?php
namespace Velox\Framework\Dbal\Mysql;

class Driver {
    protected $mysqli = null;

    public function connect($host, $user, $password, $database) {
        mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            $this->mysqli = new \mysqli($host, $user, $password, $database);
            //$this->query('SET NAMES utf8');
        } catch (\mysqli_sql_exception $e) {
            throw new Exception\ConnectionException('Mysql connection failed.');
        }
    }

    public function query($query, $fail = true) {
        //_dump($query);
        if (is_null($this->mysqli))
            throw new Exception\NotConnectedException();

        $r = $this->mysqli->query($query);
        if ($r === false && $fail) {
            if ($this->mysqli->errno == 1062) {
                throw new Exception\DuplicateKeyException();
            } else {
                throw new Exception\QueryFailedException(sprintf('Query failed with message "%s" : "%s"',
                    mysqli_error($this->mysqli), $query));
            }
        }
        return $r;
    }

    public function multiQuery($query, $fail = true) {
        //_dump($query);
        if (is_null($this->mysqli))
            throw new Exception\NotConnectedException();

        $r = $this->mysqli->multi_query($query);
        if ($r === false && $fail) {
            throw new Exception\QueryFailedException(sprintf('Query failed with message "%s" : "%s"',
                mysqli_error($this->mysqli), $query));
        } else {
            while($this->mysqli->next_result())
                continue;
        }
        return $r;
    }

    public function nextResult() {
        $this->mysqli->next_result();
        return $this->mysqli->store_result();
    }

    public function insert($query, $fail = true) {
        //_dump($query);
        $this->query($query, $fail);
        return $this->mysqli->insert_id;
    }

    public function update($query, $fail = true) {
        //_dump($query);
        $this->query($query, $fail);
        return $this->mysqli->affected_rows;
    }

    public function delete($query, $fail = true) {
        $this->query($query, $fail);
        return $this->mysqli->affected_rows;
    }

    public function escape($str, $quotes = true) {
        if (is_null($this->mysqli))
            throw new Exception\NotConnectedException();
        $str = $this->mysqli->real_escape_string($str);
        return $quotes ? sprintf('"%s"', $str) : $str;
    }

    public function disconnect() {
        if (is_null($this->mysqli))
            return false;

        $this->mysqli->close();
        $this->mysqli = null;
        return true;
    }

    public function __destruct() {
        $this->disconnect();
    }
}
