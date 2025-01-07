<?php

declare(strict_types=1);

namespace App\Services;

class ImageService
{
    /**
     * Путь к шрифту в docker-контейнере php, используемому для добавления текста на изображения
     */
    private string $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    /**
     * Обрабатывает изображения с указанной страницы, добавляя текст и проверяя размеры
     */
    public function processImages(string $url, string $text, int $minWidth, int $minHeight): array
    {
        $html = $this->fetchHtml($url);
        if (!$html) {
            return ['success' => false, 'message' => 'Не удалось получить страницу'];
        }

        $images = $this->extractImages($html);

        $processedImages = [];

        foreach ($images as $imageUrl) {
            $imageData = $this->downloadImage($imageUrl, $url);
            if (!$imageData) {
                continue;
            }

            $processedImage = $this->imageHandler($imageData, $text, $minWidth, $minHeight, $imageUrl);
            if ($processedImage) {
                $processedImages[] = $processedImage;
            }
        }

        return ['success' => true, 'images' => $processedImages];
    }

    /**
     * Получает HTML-код страницы по указанному URL
     */
    private function fetchHtml(string $url): ?string
    {
        return file_get_contents($url);
    }

    /**
     * Извлекает все изображения с HTML-кода страницы
     */
    private function extractImages(string $html): array
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $html, $matches);

        return $matches[1];
    }

    /**
     * Скачивает изображение по указанному URL
     */
    private function downloadImage(string $imageUrl, string $baseUrl): ?string
    {
        $imageUrl = strtok($imageUrl, '?');

        if (!str_contains($imageUrl, 'http')) {
            $imageUrl = $baseUrl . '/' . ltrim($imageUrl, '/');
        }

        return file_get_contents($imageUrl);
    }

    /**
     * Обрабатывает одно изображение: изменяет размер, добавляет текст и сохраняет
     */
    private function imageHandler(string $imageData, string $text, int $minWidth, int $minHeight, string $imageUrl): ?string
    {
        [$width, $height] = getimagesizefromstring($imageData);

        if ($width < $minWidth || $height < $minHeight) {
            return null;
        }

        $ext = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $filename = 'uploads/' . uniqid('', true) . '.' . $ext;

        $image = imagecreatefromstring($imageData);
        if (!$image) {
            return null;
        }

        $newWidth = $newHeight = 200;
        $croppedImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($croppedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, min($width, $height), min($width, $height));

        $black = imagecolorallocate($croppedImage, 0, 0, 0);

        $bbox = imagettfbbox(14, 0, $this->fontPath, $text);
        $x = (int)(($newWidth - ($bbox[2] - $bbox[0])) / 2);
        $y = $newHeight - 20;

        imagettftext($croppedImage, 14, 0, $x, $y, $black, $this->fontPath, $text);

        if ($ext === 'jpg' || $ext === 'jpeg') {
            imagejpeg($croppedImage, $filename);
        } elseif ($ext === 'png') {
            imagepng($croppedImage, $filename);
        } elseif ($ext === 'gif') {
            imagegif($croppedImage, $filename);
        } elseif ($ext === 'webp') {
            imagewebp($croppedImage, $filename);
        }

        imagedestroy($image);
        imagedestroy($croppedImage);

        return $filename;
    }
}
