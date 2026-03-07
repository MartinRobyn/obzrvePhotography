<?php
/**
 * Portfolio Image API — Album Mode
 *
 * Scans the image/ directory and groups photos into albums by subfolder.
 * Returns JSON with albums for each category section.
 *
 * Folder structure:
 *   image/
 *     product/              → Product Photography section
 *       product 1/          → "Product 1" album
 *       product 2/          → "Product 2" album
 *       loose-file.jpg      → goes into "General" album
 *     wedding/              → Wedding Photography section
 *       wedding/            → "Wedding" album
 *       wedding 2/          → "Wedding 2" album
 *     event/                → Event Photography section
 *     portait/ or portrait/ → Portrait Photography section
 *     slider/ or carousel/  → Hero carousel background images
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$imageRoot   = __DIR__ . '/image';
$allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

$categoryMap = [
    'product'   => 'productGallery',
    'wedding'   => 'weddingGallery',
    'event'     => 'eventGallery',
    'portait'   => 'portraitGallery',
    'portrait'  => 'portraitGallery',
    'slider'    => 'heroCarousel',
    'carousel'  => 'heroCarousel',
];

function scanFolder(string $dir, array $allowedExts): array {
    $images = [];
    $items = @scandir($dir);
    if ($items === false) return $images;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($full)) {
            $images = array_merge($images, scanFolder($full, $allowedExts));
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExts, true)) {
                $images[] = $full;
            }
        }
    }
    return $images;
}

function buildRelativePath(string $fullPath, string $imageRoot): string {
    $rel = str_replace(
        [$imageRoot . DIRECTORY_SEPARATOR, $imageRoot . '/'],
        '',
        $fullPath
    );
    return 'image/' . str_replace('\\', '/', $rel);
}

$result = [];

if (is_dir($imageRoot)) {
    foreach (scandir($imageRoot) as $catFolder) {
        if ($catFolder === '.' || $catFolder === '..') continue;
        $catPath = $imageRoot . DIRECTORY_SEPARATOR . $catFolder;
        if (!is_dir($catPath)) continue;

        $key = strtolower($catFolder);
        $galleryId = $categoryMap[$key] ?? null;
        if ($galleryId === null) continue;

        // Hero carousel stays flat (no albums)
        if ($galleryId === 'heroCarousel') {
            if (!isset($result[$galleryId])) $result[$galleryId] = [];
            foreach (scanFolder($catPath, $allowedExts) as $f) {
                $result[$galleryId][] = [buildRelativePath($f, $imageRoot)];
            }
            continue;
        }

        if (!isset($result[$galleryId])) $result[$galleryId] = ['albums' => []];

        // Collect loose files in category root → "General" album
        $looseImages = [];
        // Collect subfolders → named albums
        $subAlbums = [];

        foreach (scandir($catPath) as $item) {
            if ($item === '.' || $item === '..') continue;
            $itemPath = $catPath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($itemPath)) {
                $albumName = ucwords(str_replace(['_', '-'], ' ', $item));
                $albumImages = [];
                foreach (scanFolder($itemPath, $allowedExts) as $f) {
                    $albumImages[] = [
                        'src'  => buildRelativePath($f, $imageRoot),
                        'date' => date('n/j/Y', @filemtime($f) ?: time()),
                    ];
                }
                if (!empty($albumImages)) {
                    $subAlbums[] = [
                        'name'   => $albumName,
                        'cover'  => $albumImages[0]['src'],
                        'count'  => count($albumImages),
                        'images' => $albumImages,
                    ];
                }
            } else {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExts, true)) {
                    $looseImages[] = [
                        'src'  => buildRelativePath($itemPath, $imageRoot),
                        'date' => date('n/j/Y', @filemtime($itemPath) ?: time()),
                    ];
                }
            }
        }

        if (!empty($looseImages)) {
            array_unshift($subAlbums, [
                'name'   => 'General',
                'cover'  => $looseImages[0]['src'],
                'count'  => count($looseImages),
                'images' => $looseImages,
            ]);
        }

        $result[$galleryId]['albums'] = array_merge(
            $result[$galleryId]['albums'],
            $subAlbums
        );
    }
}

echo json_encode($result, JSON_UNESCAPED_SLASHES);

