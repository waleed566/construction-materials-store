<?php

namespace App\Models;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'password_hash', 'address', 'city', 'country', 'user_type', 'is_active'];

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}