<?php

namespace App\Models;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name', 'description', 'slug', 'image_url', 'is_active'];

    /**
     * Find by slug
     */
    public function findBySlug($slug)
    {
        $query = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }

    /**
     * Get active categories
     */
    public function getActive()
    {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll();
    }
}