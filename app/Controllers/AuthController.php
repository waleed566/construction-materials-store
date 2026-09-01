<?php

namespace App\Controllers;

class AuthController
{
    /**
     * Register a new user
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->response(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateRegistration($data)) {
            return $this->response(['error' => 'Invalid input'], 400);
        }

        $userModel = new \App\Models\User();
        $existingUser = $userModel->findByEmail($data['email']);

        if ($existingUser) {
            return $this->response(['error' => 'Email already exists'], 409);
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => $userModel->hashPassword($data['password']),
            'user_type' => 'customer'
        ];

        $userId = $userModel->create($userData);

        if ($userId) {
            return $this->response(['message' => 'User registered successfully', 'id' => $userId], 201);
        }

        return $this->response(['error' => 'Registration failed'], 500);
    }

    /**
     * Login user
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->response(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->response(['error' => 'Email and password required'], 400);
        }

        $userModel = new \App\Models\User();
        $user = $userModel->findByEmail($data['email']);

        if (!$user || !$userModel->verifyPassword($data['password'], $user['password_hash'])) {
            return $this->response(['error' => 'Invalid credentials'], 401);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];

        return $this->response([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        session_destroy();
        return $this->response(['message' => 'Logout successful'], 200);
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find($_SESSION['user_id']);

        if (!$user) {
            return $this->response(['error' => 'User not found'], 404);
        }

        unset($user['password_hash']);
        return $this->response($user, 200);
    }

    /**
     * Validate registration data
     */
    private function validateRegistration($data)
    {
        return isset($data['name']) && isset($data['email']) && isset($data['password']) &&
               !empty($data['name']) && !empty($data['email']) && !empty($data['password']) &&
               filter_var($data['email'], FILTER_VALIDATE_EMAIL) && strlen($data['password']) >= 6;
    }

    /**
     * Send JSON response
     */
    protected function response($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}