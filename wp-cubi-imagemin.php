<?php

/**
 * Plugin Name:         wp-cubi-imagemin
 * Plugin URI:          https://github.com/globalis-ms/wp-cubi-imagemin
 * Description:         Standalone image minification WordPress plugin
 * Author:              Pierre Dargham, Globalis Media Systems
 * Author URI:          https://www.globalis-ms.com/
 * License:             GPL2
 *
 * Version:             0.1.0
 * Requires at least:   4.6.0
 * Tested up to:        4.9.2
 */

namespace Globalis\WP\Cubi\ImageMin;

add_action('plugins_loaded', function () {
    require_once __DIR__ . '/src/ImageMin.php';
    ImageMin::hooks();
});
