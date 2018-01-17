<?php

namespace Globalis\WP\Cubi\ImageMin;

class ImageMin
{

    private static $optimizer;

    private static $imageExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
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
    ];

    public static function hooks()
    {
        add_filter('wp_generate_attachment_metadata', [__CLASS__, 'optimizeMedia'], 10, 2);
    }

    public static function optimizeMedia($metadata, $attachment_id)
    {
        if (self::isImage($metadata)) {
            foreach (self::getAllSizes($metadata) as $file) {
                self::optimizeImage($file);
            }
        }

        return $metadata;
    }

    protected static function isImage($metadata)
    {
        $pathinfo = pathinfo($metadata['file']);
        return in_array(strtolower($pathinfo['extension']), self::$imageExtensions);
    }

    protected static function getAllSizes($metadata)
    {
        $pathinfo   = pathinfo($metadata['file']);
        $upload_dir = wp_upload_dir();
        $basedir    = $upload_dir['basedir'] . '/' . $pathinfo['dirname'] . '/';
        $basename   = basename($metadata['file']);
        $files      = [$basedir . $basename];

        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $thumnail) {
                $files[] = $basedir . $thumnail['file'];
            }
        }

        return $files;
    }

    public static function optimizeImage($file)
    {
        return self::getOptimizer()->optimize($file);
    }

    public static function getOptimizer()
    {
        if (!isset(self::$optimizer)) {
            $options = [
                'execute_only_first_png_optimizer'  => false,
                'execute_only_first_jpeg_optimizer' => false,
                'jpegoptim_options'                 => ['--strip-all', '--all-progressive', '-m85'],
            ];

            foreach (self::$constBinaries as $constant => $optionName) {
                if (defined($constant)) {
                    $options[$optionName] = constant($constant);
                }
            }

            self::$optimizer = (new \ImageOptimizer\OptimizerFactory(apply_filters('wp-cubi-imagemin\options', $options)))->get();
        }

        return self::$optimizer;
    }
}

/*
    @todo :
    - hook trigered on regenerate thumbnails ?
    - YES : optimize($attachment_id) ?
    - YES : optimize($directory)
    - CLI command line optimize-attachment / optimize-directory
    - README.md
*/
