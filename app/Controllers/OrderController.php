<?php

namespace App\Controllers;

class OrderController
{
    /**
     * Get user orders
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $model = new \App\Models\Model();
        $orders = $model->query($sql, [':user_id' => $_SESSION['user_id']]);
        return $this->response($orders, 200);
    }

    /**
     * Get order details
     */
    public function show($orderId)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $model = new \App\Models\Model();
        $sql = "SELECT * FROM orders WHERE id = :id AND user_id = :user_id";
        $orders = $model->query($sql, [':id' => $orderId, ':user_id' => $_SESSION['user_id']]);

        if (!$orders) {
            return $this->response(['error' => 'Order not found'], 404);
        }

        $order = $orders[0];
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $order['items'] = $model->query($sql, [':order_id' => $orderId]);

        return $this->response($order, 200);
    }

    /**
     * Create order from cart
     */
    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateOrder($data)) {
            return $this->response(['error' => 'Invalid input'], 400);
        }

        $pdo = \App\Database\Connection::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            // Get cart items
            $sql = "SELECT ci.*, p.price FROM cart_items ci 
                    JOIN products p ON ci.product_id = p.id 
                    WHERE ci.user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $cartItems = $stmt->fetchAll();

            if (empty($cartItems)) {
                return $this->response(['error' => 'Cart is empty'], 400);
            }

            // Calculate totals
            $totalPrice = 0;
            foreach ($cartItems as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            $discountAmount = $data['discount_amount'] ?? 0;
            $finalPrice = $totalPrice - $discountAmount + ($data['shipping_cost'] ?? 0);

            // Create order
            $orderNumber = 'ORD-' . time() . '-' . $_SESSION['user_id'];
            $sql = "INSERT INTO orders (user_id, order_number, total_price, discount_amount, final_price, shipping_address, shipping_city, shipping_country, shipping_cost) 
                    VALUES (:user_id, :order_number, :total_price, :discount_amount, :final_price, :shipping_address, :shipping_city, :shipping_country, :shipping_cost)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':order_number' => $orderNumber,
                ':total_price' => $totalPrice,
                ':discount_amount' => $discountAmount,
                ':final_price' => $finalPrice,
                ':shipping_address' => $data['shipping_address'],
                ':shipping_city' => $data['shipping_city'] ?? '',
                ':shipping_country' => $data['shipping_country'] ?? '',
                ':shipping_cost' => $data['shipping_cost'] ?? 0
            ]);

            $orderId = $pdo->lastInsertId();

            // Add order items
            foreach ($cartItems as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) 
                        VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :total_price)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['name'] ?? 'Product',
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['price'],
                    ':total_price' => $item['price'] * $item['quantity']
                ]);
            }

            // Clear cart
            $sql = "DELETE FROM cart_items WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $_SESSION['user_id']]);

            $pdo->commit();

            return $this->response([
                'message' => 'Order created successfully',
                'order_id' => $orderId,
                'order_number' => $orderNumber
            ], 201);
        } catch (\Exception $e) {
            $pdo->rollBack();
            return $this->response(['error' => 'Order creation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate order data
     */
    private function validateOrder($data)
    {
        return isset($data['shipping_address']) && !empty($data['shipping_address']);
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