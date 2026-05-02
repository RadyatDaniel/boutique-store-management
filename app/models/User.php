<?php

/**
 * User Model
 */

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * Get user's role
     */
    public function getRole()
    {
        $result = $this->db->query(
            "SELECT r.* FROM roles r WHERE r.id = ?",
            [$this->role_id]
        );
        return $result->fetch_assoc();
    }

    /**
     * Get user's permissions
     */
    public function getPermissions()
    {
        $result = $this->db->query(
            "SELECT permission FROM permissions WHERE role_id = ?",
            [$this->role_id]
        );

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        return $permissions;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        $permissions = $this->getPermissions();
        return in_array($permission, $permissions, true);
    }

    /**
     * Get user by username
     */
    public static function findByUsername($username)
    {
        return static::where('username', $username);
    }

    /**
     * Get user by email
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hash and set password
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        return $this;
    }

    /**
     * Update login timestamp
     */
    public function updateLastLogin()
    {
        $this->last_login = date('Y-m-d H:i:s');
        $this->login_attempts = 0;
        return $this->save();
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts()
    {
        $this->login_attempts++;

        if ($this->login_attempts >= AUTH_MAX_LOGIN_ATTEMPTS) {
            $this->locked_until = date('Y-m-d H:i:s', time() + AUTH_LOCKOUT_DURATION);
        }

        return $this->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked()
    {
        if (!$this->locked_until) {
            return false;
        }

        $lockedUntil = strtotime($this->locked_until);
        if (time() > $lockedUntil) {
            $this->locked_until = null;
            $this->login_attempts = 0;
            $this->save();
            return false;
        }

        return true;
    }

    /**
     * Unlock account
     */
    public function unlock()
    {
        $this->locked_until = null;
        $this->login_attempts = 0;
        return $this->save();
    }
}
