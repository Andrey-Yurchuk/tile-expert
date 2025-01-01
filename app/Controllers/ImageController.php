<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageService;
use JsonException;

class ImageController
{
    /**
     * Обрабатывает входящий запрос с изображениями, проверяет данные и вызывает сервис обработки изображений
     *
     * @throws JsonException
     */

    public function upload(): void
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        $url = htmlspecialchars((string)$data['url'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars((string)$data['text'], ENT_QUOTES, 'UTF-8');
        $minWidth = (int)$data['minWidth'];
        $minHeight = (int)$data['minHeight'];

        if (!$url || !$text || !$minWidth || !$minHeight) {
            echo json_encode(['success' => false, 'message' => 'Некорректные данные'], JSON_THROW_ON_ERROR);
            return;
        }

        if ($minWidth < 200 || $minHeight < 200) {
            echo json_encode(['success' => false, 'message' => 'Минимальная ширина и высота должны быть не менее 200px'], JSON_THROW_ON_ERROR);
            return;
        }

        $imageService = new ImageService();
        $result = $imageService->processImages($url, $text, $minWidth, $minHeight);

        echo json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * Получает список сохранённых изображений из каталога 'uploads'
     *
     * @throws JsonException
     */
    public function getSavedImages(): void
    {
        header('Content-Type: application/json');

        $images = glob('uploads/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

        echo json_encode(['images' => $images], JSON_THROW_ON_ERROR);
    }
}
