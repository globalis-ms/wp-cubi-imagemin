<?php

namespace Globalis\WP\Cubi\ImageMin;

class ImageMin
{

    const DEFAULT_JPEG_LEVEL = 85;
    const FIELD_OPTION_IMAGEMIN_DISABLED = '_wp_cubi_imagemin_disabled';

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

    public static function hooks()
    {
        add_filter('upload_post_params', [__CLASS__, 'addOptionToPost']);
        add_filter('plupload_default_params', [__CLASS__, 'addOptionToPost']);
        add_action('post-upload-ui', [__CLASS__, 'addOptionMedia']);
        add_action('post-upload-ui', [__CLASS__, 'addScripts']);
    }

    public static function addOptionMedia()
    {
        ?>
        <input type="checkbox" name="<?= self::FIELD_OPTION_IMAGEMIN_DISABLED ?>" id="wp_cubi_imagemin" value="1"> <label for="wp_cubi_imagemin">Désactiver l'optimisation des images</label>
        <?php
    }

    public static function addOptionToPost($params)
    {
        $params[self::FIELD_OPTION_IMAGEMIN_DISABLED] = isset($_REQUEST[self::FIELD_OPTION_IMAGEMIN_DISABLED]) ? intval($_REQUEST[self::FIELD_OPTION_IMAGEMIN_DISABLED]) : 0;
        return $params;
    }

    public static function addScripts()
    {
        wp_enqueue_script('plupload-handlers-imagemin', plugin_dir_url(__FILE__) . '../assets/plupload-imagemin.js', ['plupload-handlers']);
    }

    public static function optimizeMedia($metadata, $attachment_id)
    {
        if (self::isDisable()) {
            return $metadata;
        }

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

    private static function isDisable()
    {
        if (isset($_REQUEST[self::FIELD_OPTION_IMAGEMIN_DISABLED]) && '1' === $_REQUEST[self::FIELD_OPTION_IMAGEMIN_DISABLED]) {
            return true;
        }
        return false;
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

    public static function optimizeImage($file)
    {
        return self::getOptimizer()->optimize($file);
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

            self::$optimizer = (new \ImageOptimizer\OptimizerFactory(apply_filters('wp-cubi-imagemin\options', $options)))->get();
        }

        return self::$optimizer;
    }
}
