<?php

use PHPUnit\Framework\TestCase;

require_once '/var/www/html/lib/Users/Users.php';

class UsersTest extends TestCase
{
    private Users $users;

    protected function setUp(): void
    {
        $this->users = new Users();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    public function testGetAllUsers(): void
    {
        $users = $this->users->getAllUsers();
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
    }

    public function testSetOrderingValues(): void
    {
        $ordering = $this->users->setOrderingValues();
        $this->assertIsArray($ordering);
        $this->assertArrayHasKey('id', $ordering);
        $this->assertArrayHasKey('username', $ordering);
        $this->assertArrayHasKey('type', $ordering);
    }

    public function testCreateAndDeleteUser(): void
    {
        $db = getDbInstance();
        $uniqueUsername = 'test_user_' . time();
        $dataToDb = [
            'username' => $uniqueUsername,
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'type' => 'admin'
        ];
        $userId = $db->insert('users', $dataToDb);
        $this->assertGreaterThan(0, $userId);

        $db = getDbInstance();
        $db->where('id', $userId);
        $result = $db->getOne('users');
        $this->assertEquals($uniqueUsername, $result['username']);
        $this->assertEquals('admin', $result['type']);

        $db = getDbInstance();
        $db->where('id', $userId);
        $db->delete('users');

        $db = getDbInstance();
        $db->where('id', $userId);
        $deleted = $db->getOne('users');
        $this->assertNull($deleted);
    }

    public function testPasswordHashing(): void
    {
        $password = 'testPassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrongPassword', $hash));
    }

    public function testGetUserById(): void
    {
        $user = $this->users->getUser(1);
        $this->assertNotNull($user);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('username', $user);
    }
}
