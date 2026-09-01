<?php

namespace App\Controllers;

class ProductController
{
    /**
     * Get all products
     */
    public function index()
    {
        $productModel = new \App\Models\Product();
        $products = $productModel->all();
        return $this->response($products, 200);
    }

    /**
     * Get product by ID
     */
    public function show($id)
    {
        $productModel = new \App\Models\Product();
        $product = $productModel->find($id);

        if (!$product) {
            return $this->response(['error' => 'Product not found'], 404);
        }

        return $this->response($product, 200);
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId)
    {
        $productModel = new \App\Models\Product();
        $products = $productModel->getByCategory($categoryId);
        return $this->response($products, 200);
    }

    /**
     * Search products
     */
    public function search()
    {
        if (!isset($_GET['q'])) {
            return $this->response(['error' => 'Search query required'], 400);
        }

        $keyword = $_GET['q'];
        $productModel = new \App\Models\Product();
        $products = $productModel->search($keyword);
        return $this->response($products, 200);
    }

    /**
     * Create product (seller only)
     */
    public function store()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateProduct($data)) {
            return $this->response(['error' => 'Invalid input'], 400);
        }

        $productData = [
            'category_id' => $data['category_id'],
            'seller_id' => $_SESSION['user_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'unit' => $data['unit'] ?? null,
            'image_url' => $data['image_url'] ?? null
        ];

        $productModel = new \App\Models\Product();
        $productId = $productModel->create($productData);

        if ($productId) {
            return $this->response(['message' => 'Product created successfully', 'id' => $productId], 201);
        }

        return $this->response(['error' => 'Creation failed'], 500);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $productModel = new \App\Models\Product();
        $product = $productModel->find($id);

        if (!$product) {
            return $this->response(['error' => 'Product not found'], 404);
        }

        if ($product['seller_id'] !== $_SESSION['user_id']) {
            return $this->response(['error' => 'Forbidden'], 403);
        }

        $updated = $productModel->update($id, $data);
        if ($updated) {
            return $this->response(['message' => 'Product updated successfully'], 200);
        }

        return $this->response(['error' => 'Update failed'], 500);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response(['error' => 'Unauthorized'], 401);
        }

        $productModel = new \App\Models\Product();
        $product = $productModel->find($id);

        if (!$product) {
            return $this->response(['error' => 'Product not found'], 404);
        }

        if ($product['seller_id'] !== $_SESSION['user_id']) {
            return $this->response(['error' => 'Forbidden'], 403);
        }

        $deleted = $productModel->delete($id);
        if ($deleted) {
            return $this->response(['message' => 'Product deleted successfully'], 200);
        }

        return $this->response(['error' => 'Deletion failed'], 500);
    }

    /**
     * Validate product data
     */
    private function validateProduct($data)
    {
        return isset($data['name']) && isset($data['category_id']) && isset($data['price']) &&
               !empty($data['name']) && !empty($data['category_id']) && !empty($data['price']);
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