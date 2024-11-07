<?php

namespace Globalis\WP\Cubi\ImageMin;

class ImageMin
{
    const DEFAULT_JPEG_LEVEL = 85;

    private static $optimizer;

    private static $imageExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
    ];

    private static $constBinaries   = [
        'WP_CUBI_IMAGEMIN_PATH_BIN_ADVPNG'    => 'advpng_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_GIFSICLE'  => 'gifsicle_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_JPEGOPTIM' => 'jpegoptim_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_JPEGTRAN'  => 'jpegtran_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_OPTIPNG'   => 'optipng_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_PNGCRUSH'  => 'pngcrush_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_PNGOUT'    => 'pngout_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_PNGQUANT'  => 'pngquant_bin',
        'WP_CUBI_IMAGEMIN_PATH_BIN_SVGO'      => 'svgo_bin',
    ];

    public static function optimizeMedia($metadata, $attachment_id)
    {
        if (isset($metadata['file'])) {
            $file_uploaded = $metadata['file'];
        } else {
            $file_uploaded = get_post_meta($attachment_id, '_wp_attached_file', true);
        }

        foreach (self::getAllSizes($file_uploaded, $metadata) as $file) {
            if (self::isImagePath($file)) {
                self::optimizeImage($file);
            }
        }

        return $metadata;
    }

    public static function isImagePath($path)
    {
        $pathinfo = pathinfo($path);
        return in_array(strtolower($pathinfo['extension']), self::$imageExtensions);
    }

    protected static function getAllSizes($file_uploaded, $metadata)
    {
        $pathinfo   = pathinfo($file_uploaded);
        $upload_dir = wp_upload_dir();
        $basedir    = $upload_dir['basedir'] . '/' . $pathinfo['dirname'] . '/';
        $basename   = basename($file_uploaded);
        $files      = [$basedir . $basename];

        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $thumnail) {
                $files[] = $basedir . $thumnail['file'];
            }
        }

        return $files;
    }

    public static function optimizeImage($file, $jpeg_level = self::DEFAULT_JPEG_LEVEL)
    {
        // Backup original file
        $backup = $file . '.bak-' . uniqid();
        copy($file, $backup);
        $size_before = filesize($file);
        self::getOptimizer($jpeg_level)->optimize($file);
        clearstatcache(true, $file);
        $size_after = filesize($file);
        $reduced = $size_before - $size_after;

        if ($reduced <= 0) {
            // Restore backup
            unlink($file);
            rename($backup, $file);
            $reduced = 0;
        } else {
            // Remove backup
            unlink($backup);
        }

        return ['reduced' => $reduced, 'size_before' => $size_before, 'size_after' => $size_after];
    }

    public static function getOptimizer($jpeg_level = self::DEFAULT_JPEG_LEVEL)
    {
        if (!isset(self::$optimizer)) {
            $options = [
                'execute_only_first_png_optimizer'  => false,
                'execute_only_first_jpeg_optimizer' => false,
                'jpegoptim_options'                 => ['--strip-all', '--all-progressive', '-m' . $jpeg_level],
            ];

            foreach (self::$constBinaries as $constant => $optionName) {
                if (defined($constant)) {
                    $options[$optionName] = constant($constant);
                }
            }

            $options = apply_filters('wp-cubi-imagemin\options', $options);

            self::$optimizer = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
        }

        return self::$optimizer;
    }
}
