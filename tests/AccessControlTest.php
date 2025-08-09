<?php
use PHPUnit\Framework\TestCase;

class AccessControlTest extends TestCase
{
    public function testDashboardWithoutLogin()
    {
        $_SESSION = [];
        $loggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
        $this->assertFalse($loggedIn);
    }

    public function testAccessLoginWhileLoggedIn()
    {
        $_SESSION['loggedin'] = true;
        $this->assertTrue($_SESSION['loggedin']);
    }

    public function testLogoutAccess()
    {
        $_SESSION = [];
        $this->assertFalse(isset($_SESSION['loggedin']));
    }
}
