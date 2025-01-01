<?php

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\ImageController;

if ($_SERVER['REQUEST_URI'] === '/process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ImageController();
    $controller->upload();
} elseif ($_SERVER['REQUEST_URI'] === '/images') {
    $controller = new ImageController();
    $controller->getSavedImages();
} else {
    $indexPath = __DIR__ . '/public/index.html';
    if (file_exists($indexPath)) {
        include $indexPath;
    } else {
        die('Страница не найдена');
    }
}
