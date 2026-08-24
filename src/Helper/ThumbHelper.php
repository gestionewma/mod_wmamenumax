<?php

/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
* @author      Team Developer by WMA Web Maker Agency <wmaextension@gmail.com>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.wma.ovh
 * @version     1.0.11
 * @date        20/08/2026
 * @file        src/Helper/ThumbHelper.php
 */

namespace Wma\Module\WmaMenumax\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/**
 * Generates square thumbnails into images/miniature.
 *
 * @since  1.0.0
 */
class ThumbHelper
{
    /**
     * Absolute path of the thumbnails folder.
     *
     * @var    string
     * @since  1.0.0
     */
    private string $basePath;

    /**
     * Base URL of the thumbnails folder.
     *
     * @var    string
     * @since  1.0.0
     */
    private string $baseUrl;

    /**
     * Constructor.
     *
     * @since  1.0.0
     */
    public function __construct()
    {
        $this->basePath = JPATH_ROOT . '/images/miniature';
        $this->baseUrl  = rtrim(Uri::root(true), '/') . '/images/miniature';
    }

    /**
     * Return the small and large square versions of a source image,
     * generating them if missing.
     *
     * @param   string  $source     Source image path (relative to root) or URL.
     * @param   int     $thumbSize  Small square size in px.
     * @param   int     $hoverSize  Large square size in px.
     *
     * @return  array  ['thumb' => string, 'hover' => string]
     *
     * @since   1.0.0
     */
    public function getThumbs(string $source, int $thumbSize, int $hoverSize): array
    {
        if ($source === '') {
            return ['thumb' => '', 'hover' => ''];
        }

        // External URLs are returned as-is (no local thumbnail possible).
        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://') || str_starts_with($source, '//')) {
            return ['thumb' => $source, 'hover' => $source];
        }

        $sourcePath = $this->resolveSourcePath($source);

        if (!$sourcePath || !is_file($sourcePath)) {
            return ['thumb' => '', 'hover' => ''];
        }

        $this->ensureDir();

        $hash      = md5($source);
        $thumbFile = $hash . '_' . $thumbSize . '.jpg';
        $hoverFile = $hash . '_' . $hoverSize . '.jpg';

        if (!is_file($this->basePath . '/' . $thumbFile)) {
            $this->generate($sourcePath, $thumbFile, $thumbSize);
        }

        if (!is_file($this->basePath . '/' . $hoverFile)) {
            $this->generate($sourcePath, $hoverFile, $hoverSize);
        }

        return [
            'thumb' => $this->baseUrl . '/' . $thumbFile,
            'hover' => $this->baseUrl . '/' . $hoverFile,
        ];
    }

    /**
     * Delete all generated thumbnails.
     *
     * @return  int  Number of deleted files.
     *
     * @since   1.0.0
     */
    public function clearCache(): int
    {
        if (!is_dir($this->basePath)) {
            return 0;
        }

        $count = 0;

        foreach (glob($this->basePath . '/*.jpg') ?: [] as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate a single square jpeg thumb.
     *
     * @param   string  $sourcePath  Absolute path of the source.
     * @param   string  $fileName    Target file name.
     * @param   int     $size        Square size in px.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function generate(string $sourcePath, string $fileName, int $size): void
    {
        $info = @getimagesize($sourcePath);

        if (!$info || $info[0] <= 0 || $info[1] <= 0) {
            return;
        }

        $cropSize = min($info[0], $info[1]);
        $cropX    = (int) (($info[0] - $cropSize) / 2);
        $cropY    = (int) (($info[1] - $cropSize) / 2);

        if (function_exists('imagecreatetruecolor')) {
            $this->generateGd($sourcePath, $fileName, $size, $info['mime'], $cropX, $cropY, $cropSize);
        } elseif (class_exists('Imagick')) {
            $this->generateImagick($sourcePath, $fileName, $size, $cropX, $cropY, $cropSize);
        }
    }

    /**
     * GD implementation.
     *
     * @param   string  $sourcePath  Absolute path of the source.
     * @param   string  $fileName    Target file name.
     * @param   int     $size        Square size in px.
     * @param   string  $mime        Mime type of the source.
     * @param   int     $cropX       Crop offset X.
     * @param   int     $cropY       Crop offset Y.
     * @param   int     $cropSize    Crop square size.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function generateGd(string $sourcePath, string $fileName, int $size, string $mime, int $cropX, int $cropY, int $cropSize): void
    {
        $src = false;

        switch ($mime) {
            case 'image/jpeg':
                $src = @imagecreatefromjpeg($sourcePath);
                break;

            case 'image/png':
                $src = @imagecreatefrompng($sourcePath);
                break;

            case 'image/gif':
                $src = @imagecreatefromgif($sourcePath);
                break;

            case 'image/webp':
                $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false;
                break;
        }

        if (!$src) {
            return;
        }

        $dst = imagecreatetruecolor($size, $size);
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
        imagejpeg($dst, $this->basePath . '/' . $fileName, 85);

        imagedestroy($dst);
        imagedestroy($src);
    }

    /**
     * Imagick implementation.
     *
     * @param   string  $sourcePath  Absolute path of the source.
     * @param   string  $fileName    Target file name.
     * @param   int     $size        Square size in px.
     * @param   int     $cropX       Crop offset X.
     * @param   int     $cropY       Crop offset Y.
     * @param   int     $cropSize    Crop square size.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function generateImagick(string $sourcePath, string $fileName, int $size, int $cropX, int $cropY, int $cropSize): void
    {
        try {
            $img = new \Imagick($sourcePath);

            $img->cropImage($cropSize, $cropSize, $cropX, $cropY);
            $img->thumbnailImage($size, $size, true, true);
            $img->setImageFormat('jpeg');
            $img->setImageCompressionQuality(85);
            $img->writeImage($this->basePath . '/' . $fileName);

            $img->clear();
            $img->destroy();
        } catch (\Throwable $e) {
            // Ignore generation failures.
        }
    }

    /**
     * Ensure the thumbnails folder exists with an empty index.html.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function ensureDir(): void
    {
        if (!is_dir($this->basePath)) {
            @mkdir($this->basePath, 0755, true);
        }

        if (!is_file($this->basePath . '/index.html')) {
            @file_put_contents($this->basePath . '/index.html', '<!DOCTYPE html><title></title>');
        }
    }

    /**
     * Resolve a relative image path into an absolute filesystem path,
     * preventing directory traversal.
     *
     * @param   string  $source  The image path.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function resolveSourcePath(string $source): string
    {
        if (str_contains($source, '..')) {
            return '';
        }

        return JPATH_ROOT . '/' . ltrim($source, '/\\');
    }
}
