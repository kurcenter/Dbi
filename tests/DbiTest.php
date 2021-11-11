<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

final class DbiTest extends TestCase
{
    /**
     * @var \Kurcenter\Dbi\Db
     */
    private $db;

    protected function setUp()
    {
        $mysqli = new mysqli('db.mynetwork', 'root', 'root', 'test');
        $mysqli->set_charset('UTF-8');

        $this->db = new \Kurcenter\Dbi\Db($mysqli);

        $this->db->exec("
            DROP TABLE IF EXISTS `demo` CASCADE;
        ");

        $this->db->exec("
            CREATE TABLE `demo`( 
                `id` Int( 255 ) AUTO_INCREMENT NOT NULL,
                `name` VarChar( 255 ) NOT NULL,
                `value` Float NOT NULL,
                PRIMARY KEY ( `id` ) )
            ENGINE = InnoDB;
        ");
        $this->db->insert('demo', ['name' => 'test0', 'value' => 1]);

    }

    public function testInsert()
    {
        $result = $this->db->insert('demo', ['name' => 'test1', 'value' => 1]);
        $this->assertEquals($result, true);
    }

    public function testSelectRow()
    {
        $result = $this->db->exec("SELECT * FROM `demo` WHERE id = 1")->row;
        $this->assertEquals($result, ['id' => 1, 'name' => 'test0', 'value' => 1.0]);
    }

    public function testSelectRows()
    {
        $result = $this->db->exec("SELECT * FROM `demo` WHERE id = 1")->rows;
        $this->assertEquals($result, [['id' => 1, 'name' => 'test0', 'value' => 1.0]]);
    }

    public function testSelectOne()
    {
        $result = $this->db->exec("SELECT * FROM `demo` WHERE id = 1")->one();
        $this->assertEquals($result->name, 'test0');
    }
}
