<?php

namespace Kurcenter\Dbi;

class ResultSet
{
    private $stmt;

    /**
     * @var \mysqli_result
     */
    private $query;

    /**
     * @var string
     */
    private $fetchType = 'object';

    /**
     * Class constructor.
     */
    public function __construct($stmt, \mysqli_result $query)
    {
        $this->stmt = $stmt;
        $this->query = $query;

        return $this;
    }

    public function toArray() {
        $this->fetchType = 'array';
        return $this;
    }

    public function toObject() {
        $this->fetchType = 'object';
        return $this;
    }

    public function one() 
    {
        $row = $this->queryFetch($this->fetchType);
        $this->stmt->close();
        
        return $row;
    }

    public function all() 
    {
        $data = [];
        while ($row = $this->queryFetch('array')) {
            $data[] = $row;
        }
        $this->stmt->close();
        return $data;
    }

    /**
     * Количество записей в выборке
     *
     * @return int
     */
    public function count() 
    {
        return $this->query->num_rows;
    }

    private function queryFetch($fetchType) {
        if ($fetchType === 'array') {
            return $this->query->fetch_assoc();
        } else {
            return $this->query->fetch_object();
        }
    }
    
    /**
     * Для совместимости
     *
     * @param string $name
     * @return mixed
     */
    public function __get ($name) 
    {
        switch ($name) {
            case 'rows':
                return $this->rows();
                break;
            case 'row':
                return $this->row();
                break;
            case 'num_rows':
                return $this->count();
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Полить результат выборки
     *
     * @return array
     */
    public function row() {
        $data = $this->queryFetch('array');

        $this->stmt->close();
        return $data;
    }

    /**
     * Полить результат выборки
     *
     * @return array
     */
    public function rows() {
        $data = [];
        while ($row = $this->queryFetch('array')) {
            $data[] = $row;
        }
        $this->stmt->close();
        return $data;
    }

    /**
     * Получить результат в виде генератора
     *
     * @return yield
     */
    public function next() 
    {
        while ($row = $this->queryFetch($this->fetchType)) {
             yield $row;
        }
        $this->stmt->close();
    }
}
