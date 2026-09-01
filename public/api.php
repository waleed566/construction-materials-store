<?php
session_start();

// Router
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove /api from the path if present
$path = str_replace('/api', '', $request_uri);
if (substr($path, 0, 1) === '/') {
    $path = substr($path, 1);
}

// Route handling
switch ($path) {
    // Auth routes
    case 'auth/register':
        if ($request_method === 'POST') {
            $controller = new \App\Controllers\AuthController();
            $controller->register();
        }
        break;

    case 'auth/login':
        if ($request_method === 'POST') {
            $controller = new \App\Controllers\AuthController();
            $controller->login();
        }
        break;

    case 'auth/logout':
        if ($request_method === 'POST') {
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
        }
        break;

    case 'auth/profile':
        if ($request_method === 'GET') {
            $controller = new \App\Controllers\AuthController();
            $controller->profile();
        }
        break;

    // Product routes
    case 'products':
        if ($request_method === 'GET') {
            if (isset($_GET['q'])) {
                $controller = new \App\Controllers\ProductController();
                $controller->search();
            } else {
                $controller = new \App\Controllers\ProductController();
                $controller->index();
            }
        } elseif ($request_method === 'POST') {
            $controller = new \App\Controllers\ProductController();
            $controller->store();
        }
        break;

    default:
        // Check for dynamic routes
        $parts = explode('/', array_filter(explode('/', $path)));

        if (count($parts) >= 2) {
            $resource = $parts[0];
            $action = $parts[1];
            $id = $parts[2] ?? null;

            switch ($resource) {
                case 'products':
                    if ($request_method === 'GET' && $id) {
                        $controller = new \App\Controllers\ProductController();
                        $controller->show($id);
                    } elseif ($request_method === 'PUT' && $id) {
                        $controller = new \App\Controllers\ProductController();
                        $controller->update($id);
                    } elseif ($request_method === 'DELETE' && $id) {
                        $controller = new \App\Controllers\ProductController();
                        $controller->destroy($id);
                    } elseif ($action === 'category' && $id) {
                        $controller = new \App\Controllers\ProductController();
                        $controller->getByCategory($id);
                    }
                    break;

                case 'cart':
                    if ($action === 'add' && $request_method === 'POST') {
                        $controller = new \App\Controllers\CartController();
                        $controller->add();
                    } elseif ($action === 'items' && $request_method === 'GET') {
                        $controller = new \App\Controllers\CartController();
                        $controller->index();
                    } elseif ($request_method === 'PUT' && $id) {
                        $controller = new \App\Controllers\CartController();
                        $controller->update($id);
                    } elseif ($request_method === 'DELETE' && $id) {
                        $controller = new \App\Controllers\CartController();
                        $controller->remove($id);
                    } elseif ($action === 'clear' && $request_method === 'DELETE') {
                        $controller = new \App\Controllers\CartController();
                        $controller->clear();
                    }
                    break;

                case 'orders':
                    if ($request_method === 'GET' && !$id) {
                        $controller = new \App\Controllers\OrderController();
                        $controller->index();
                    } elseif ($request_method === 'GET' && $id) {
                        $controller = new \App\Controllers\OrderController();
                        $controller->show($id);
                    } elseif ($request_method === 'POST' && $action === 'create') {
                        $controller = new \App\Controllers\OrderController();
                        $controller->create();
                    }
                    break;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
        break;
}