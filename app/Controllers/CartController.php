<?php

namespace App\Controllers;

class CartController
{
    /**
     * Get cart items
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $sql = "SELECT ci.*, p.name, p.price, p.image_url FROM cart_items ci 
                JOIN products p ON ci.product_id = p.id 
                WHERE ci.user_id = :user_id";
        
        $model = new \App\Models\Model();
        $items = $model->query($sql, [':user_id' => $_SESSION['user_id']]);
        return $this->response($items, 200);
    }

    /**
     * Add item to cart
     */
    public function add()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            return $this->response(['error' => 'Product ID and quantity required'], 400);
        }

        $productModel = new \App\Models\Product();
        if (!$productModel->find($data['product_id'])) {
            return $this->response(['error' => 'Product not found'], 404);
        }

        $cartModel = new \App\Models\Model();
        $cartModel->table = 'cart_items';

        $existingItem = $cartModel->query(
            "SELECT * FROM cart_items WHERE user_id = :user_id AND product_id = :product_id",
            [':user_id' => $_SESSION['user_id'], ':product_id' => $data['product_id']]
        );

        if ($existingItem) {
            $newQuantity = $existingItem[0]['quantity'] + $data['quantity'];
            $cartModel->update($existingItem[0]['id'], ['quantity' => $newQuantity]);
        } else {
            $cartModel->fillable = ['user_id', 'product_id', 'quantity'];
            $cartModel->create([
                'user_id' => $_SESSION['user_id'],
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity']
            ]);
        }

        return $this->response(['message' => 'Item added to cart'], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update($itemId)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['quantity'])) {
            return $this->response(['error' => 'Quantity required'], 400);
        }

        $cartModel = new \App\Models\Model();
        $cartModel->table = 'cart_items';
        $cartModel->fillable = ['quantity'];
        $cartModel->update($itemId, ['quantity' => $data['quantity']]);

        return $this->response(['message' => 'Cart updated'], 200);
    }

    /**
     * Remove item from cart
     */
    public function remove($itemId)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $cartModel = new \App\Models\Model();
        $cartModel->table = 'cart_items';
        $cartModel->delete($itemId);

        return $this->response(['message' => 'Item removed from cart'], 200);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $pdo = \App\Database\Connection::getInstance()->getConnection();
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);

        return $this->response(['message' => 'Cart cleared'], 200);
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