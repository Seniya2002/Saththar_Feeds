<?php
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testDatabaseConnection()
    {
        require 'db_connect.php';
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testSelectQuery()
    {
        require 'db_connect.php';
        $stmt = $pdo->query("SELECT 1");
        $this->assertEquals(1, $stmt->fetchColumn());
    }
}
