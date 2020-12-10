<?php

namespace Kansept\Dbi;

abstract class AbstractRepository
{
    /**
     * @var \Kansept\Dbi\Db
     */
    protected $db;

    /**
     * Name a table
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Имя первичного ключа
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Class constructor.
     */
    public function __construct(\Kansept\Dbi\Db $db)
    {
        $this->db = $db;
    }

    /**
     * Generate UUID
     *
     * @return string
     */
    public function uuid()
    {
        return $this->db->uuid();
    }

    /**
     * Find record by id
     *
     * @param integer $id
     * @param string $fields
     * @return array
     */
    public function findById($id, $fields = '*')
    {
        return $this->db->exec("SELECT {$fields} FROM {$this->tableName} WHERE {$this->primaryKey} = ?", [$id])->row;
    }

    /**
     * Find record by filter
     *
     * @param array $where
     * @param array $fields 
     * * fields
     * @return array
     */
    public function findBy(array $where, $option = [])
    {
        $values = array_values($where);

        $orderBy = '';
        if (isset($option['orderBy'])) {
            $orderBy = ' ORDER BY ' . $option['orderBy'];
        }

        if (isset($option['fields'])) {
            $fields = $option['fields'];
        } else {
            $fields = '*';
        }

        $whereFields = array_map(
            function ($value) {
                return "`$value` = ?";
            },
            array_keys($where)
        );
        $where = \implode(' AND ', $whereFields);

        return $this->db->exec("SELECT {$fields} FROM {$this->tableName} WHERE {$where} {$orderBy}", $values)->rows;
    }

    /**
     * Find one record by filter
     *
     * @param integer $id
     * @param string $fields
     * @return array
     */
    public function findOneBy(array $where, $option = [])
    {
        $values = array_values($where);

        $orderBy = '';
        if (isset($option['orderBy'])) {
            $orderBy = ' ORDER BY ' . $option['orderBy'];
        }

        $whereFields = array_map(
            function ($value) {
                return "`$value` = ?";
            },
            array_keys($where)
        );
        $where = \implode(' AND ', $whereFields);

        return $this->db->exec("SELECT * FROM {$this->tableName} WHERE {$where} {$orderBy}", $values)->row;
    }

    /**
     * Update rows
     *
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update($data, $where)
    {
        return $this->db->update($this->tableName, $data, $where);
    }

    /**
     * Insert rows
     *
     * @param array $data
     * @return bool
     */
    public function insert($data)
    {
        return $this->db->insert($this->tableName, $data);
    }

    /**
     * Get all rows in the table
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->exec("SELECT * FROM {$this->tableName}")->rows;
    }

    /**
     * Delete rows
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($where)
    {
        return $this->db->delete($this->tableName, $where);
    }

    /**
     * Удаление записи по ид
     *
     * @param mixed $id
     * @deprecated v1.0
     * @return bool
     */
    public function remove($id)
    {
        return $this->db->exec(
            "DELETE FROM `{$this->tableName}` WHERE {$this->primaryKey} = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Get id used in the latest query
     *
     * @return void
     */
    public function getLastId()
    {
        return $this->db->getLastId();
    }

    /**
     * Получить массив из таблицы со значениями по умолчанию
     *
     * @return array
     */
    public function getFromSheme()
    {
        $sheme = $this->db->exec("SHOW COLUMNS FROM `{$this->tableName}`")->rows;

        $row = [];
        foreach ($sheme as $column) {
            $row[$column['Field']] = $column['Default'];
        }

        return $row;
    }

    /**
     * Get next autoincrement id
     *
     * @return void
     */
    public function getNextId()
    {
        $table = $this->db->exec("SHOW TABLE STATUS LIKE '{$this->tableName}'")->row;
        return $table['Auto_increment'];
    }
}
