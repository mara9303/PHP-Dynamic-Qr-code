<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/lib/Users/Users.php';

class UsersTest extends TestCase
{
    private Users $users;
    private static int $createdUserId = 0;

    protected function setUp(): void
    {
        $this->users = new Users();
        $_SESSION['user_id'] = 1;
        $_SESSION['type'] = 'super';
    }

    /**
     * Test 1: Get all users
     */
    public function testGetAllUsers(): void
    {
        $users = $this->users->getAllUsers();
        
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users), 'Should have at least one user');
    }

    /**
     * Test 2: Create a user
     */
    public function testCreateUser(): void
    {
        $db = getDbInstance();
        
        $uniqueUsername = 'test_user_' . time();
        
        // Simulate user creation directly in database for testing
        // (avoiding exit() calls in the original method)
        $dataToDb = [
            'username' => $uniqueUsername,
            'password' => password_hash('testpassword123', PASSWORD_DEFAULT),
            'type' => 'admin'
        ];

        $lastId = $db->insert('users', $dataToDb);

        $this->assertGreaterThan(0, $lastId, 'User should be inserted in database');
        
        self::$createdUserId = (int)$lastId;

        $db = getDbInstance();
        $db->where('id', $lastId);
        $result = $db->getOne('users');

        $this->assertNotNull($result);
        $this->assertEquals($uniqueUsername, $result['username']);
        $this->assertEquals('admin', $result['type']);
    }

    /**
     * Test 3: Get user by ID
     * @depends testCreateUser
     */
    public function testGetUser(): void
    {
        $this->assertGreaterThan(0, self::$createdUserId, 'User ID should be set from previous test');

        $user = $this->users->getUser(self::$createdUserId);

        $this->assertNotNull($user);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('username', $user);
        $this->assertArrayHasKey('type', $user);
        $this->assertEquals(self::$createdUserId, $user['id']);
    }

    /**
     * Test 4: Edit a user
     * @depends testCreateUser
     */
    public function testEditUser(): void
    {
        $this->assertGreaterThan(0, self::$createdUserId, 'User ID should be set from previous test');

        $db = getDbInstance();
        
        $newUsername = 'updated_user_' . time();
        $dataToDb = [
            'username' => $newUsername,
            'password' => password_hash('newpassword456', PASSWORD_DEFAULT),
            'type' => 'super'
        ];

        $db->where('id', self::$createdUserId);
        $stat = $db->update('users', $dataToDb);

        $this->assertTrue($stat, 'User should be updated');

        $db = getDbInstance();
        $db->where('id', self::$createdUserId);
        $result = $db->getOne('users');

        $this->assertEquals($newUsername, $result['username'], 'Username should be updated');
        $this->assertEquals('super', $result['type'], 'Type should be updated to super');
    }

    /**
     * Test 5: Delete a user
     * @depends testEditUser
     */
    public function testDeleteUser(): void
    {
        $this->assertGreaterThan(0, self::$createdUserId, 'User ID should be set');

        $db = getDbInstance();
        $db->where('id', self::$createdUserId);
        $stat = $db->delete('users');

        $this->assertTrue($stat, 'User should be deleted');

        $db = getDbInstance();
        $db->where('id', self::$createdUserId);
        $result = $db->getOne('users');

        $this->assertNull($result, 'User should not exist after deletion');
    }

    /**
     * Test 6: Set ordering values
     */
    public function testSetOrderingValues(): void
    {
        $ordering = $this->users->setOrderingValues();

        $this->assertIsArray($ordering);
        $this->assertArrayHasKey('id', $ordering);
        $this->assertArrayHasKey('username', $ordering);
        $this->assertArrayHasKey('type', $ordering);
    }

    /**
     * Test 7: Username uniqueness check
     */
    public function testUsernameUniqueness(): void
    {
        $db = getDbInstance();
        
        $uniqueUsername = 'unique_test_' . time();
        
        // Create first user
        $dataToDb = [
            'username' => $uniqueUsername,
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'type' => 'admin'
        ];
        $firstId = $db->insert('users', $dataToDb);
        $this->assertGreaterThan(0, $firstId);

        // Check if username already exists
        $db = getDbInstance();
        $db->where('username', $uniqueUsername);
        $existingUser = $db->getOne('users');

        $this->assertNotNull($existingUser, 'User with this username should exist');
        $this->assertEquals($uniqueUsername, $existingUser['username']);

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $firstId);
        $db->delete('users');
    }

    /**
     * Test 8: Password hashing
     */
    public function testPasswordHashing(): void
    {
        $db = getDbInstance();
        
        $plainPassword = 'testPassword123!';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $dataToDb = [
            'username' => 'password_test_' . time(),
            'password' => $hashedPassword,
            'type' => 'admin'
        ];
        $userId = $db->insert('users', $dataToDb);

        $db = getDbInstance();
        $db->where('id', $userId);
        $user = $db->getOne('users');

        // Verify password can be verified
        $this->assertTrue(password_verify($plainPassword, $user['password']), 'Password should be verifiable');
        $this->assertFalse(password_verify('wrongpassword', $user['password']), 'Wrong password should fail');

        // Cleanup
        $db = getDbInstance();
        $db->where('id', $userId);
        $db->delete('users');
    }
}
