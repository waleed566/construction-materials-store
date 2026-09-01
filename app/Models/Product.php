<?php

namespace App\Models;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['category_id', 'seller_id', 'name', 'description', 'price', 'discount_price', 'quantity', 'unit', 'image_url', 'is_active'];

    /**
     * Get products by category
     */
    public function getByCategory($category_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE category_id = :category_id AND is_active = 1";
        return $this->query($query, [':category_id' => $category_id]);
    }

    /**
     * Search products
     */
    public function search($keyword)
    {
        $query = "SELECT * FROM {$this->table} WHERE (name LIKE :keyword OR description LIKE :keyword) AND is_active = 1";
        $keyword = '%' . $keyword . '%';
        return $this->query($query, [':keyword' => $keyword]);
    }

    /**
     * Get products by seller
     */
    public function getBySeller($seller_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE seller_id = :seller_id";
        return $this->query($query, [':seller_id' => $seller_id]);
    }
}