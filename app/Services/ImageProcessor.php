<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Turns an uploaded photo into a large and a thumbnail WebP using GD
 * (bundled with PHP — no extra dependency).
 */
class ImageProcessor
{
    /** @return array{image_path: string, thumb_path: string, width: int, height: int} */
    public function storeGalleryImage(string $sourcePath, string $directory = 'gallery'): array
    {
        $info = getimagesize($sourcePath);
        $source = match ($info['mime'] ?? null) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => throw new RuntimeException('Unsupported image type.'),
        };

        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $source = $this->applyExifOrientation($source, $sourcePath);
        }

        $name = Str::uuid()->toString();
        $large = $this->resize($source, 1600);
        $thumb = $this->resize($source, 720);

        $imagePath = "$directory/$name.webp";
        $thumbPath = "$directory/$name-720.webp";
        Storage::disk('public')->put($imagePath, $this->encode($large['image'], 80));
        Storage::disk('public')->put($thumbPath, $this->encode($thumb['image'], 75));

        return [
            'image_path' => $imagePath,
            'thumb_path' => $thumbPath,
            'width' => $thumb['width'],
            'height' => $thumb['height'],
        ];
    }

    private function resize(\GdImage $source, int $maxWidth): array
    {
        $w = imagesx($source);
        $h = imagesy($source);
        $newW = min($w, $maxWidth);
        $newH = (int) round($h * $newW / $w);

        $canvas = imagecreatetruecolor($newW, $newH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return ['image' => $canvas, 'width' => $newW, 'height' => $newH];
    }

    private function encode(\GdImage $image, int $quality): string
    {
        ob_start();
        imagewebp($image, null, $quality);

        return (string) ob_get_clean();
    }

    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        $orientation = @exif_read_data($path)['Orientation'] ?? 1;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
