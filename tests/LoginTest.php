<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        require 'db_connect.php';
        $this->pdo = $pdo;
    }

    public function testEmptyCredentials()
    {
        $email = '';
        $password = '';
        $this->assertTrue(empty($email) || empty($password));
    }

    public function testWrongCredentials()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->execute(['wrong@example.com', 'wrongpass']);
        $result = $stmt->fetch();
        $this->assertFalse($result);
    }

    public function testValidLogin()
{
    $email = 'seni@gmail.com';
    $password = '1234';

    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $this->assertNotFalse($user, "User not found");

    // Verify password hash matches
    $this->assertTrue(password_verify($password, $user['password']));
}

}
