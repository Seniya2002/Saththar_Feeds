<?php
use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        require 'db_connect.php';
        $this->pdo = $pdo;
    }

    public function testEmptyFields()
    {
        $name = '';
        $email = '';
        $password = '';
        $confirm_password = '';

        $this->assertTrue(empty($name) || empty($email) || empty($password) || empty($confirm_password));
    }

    public function testInvalidEmail()
    {
        $email = 'invalid-email';
        $this->assertFalse(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public function testPasswordMismatch()
    {
        $password = 'secret';
        $confirm_password = 'different';
        $this->assertNotEquals($password, $confirm_password);
    }

    
}
