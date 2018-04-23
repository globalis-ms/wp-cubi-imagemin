# [wp-cubi-imagemin](https://github.com/globalis-ms/wp-cubi-imagemin)

[![Latest Stable Version](https://poser.pugx.org/globalis/wp-cubi-imagemin/v/stable)](https://packagist.org/packages/globalis/wp-cubi-imagemin)
[![License](https://poser.pugx.org/globalis/wp-cubi-imagemin/license)](https://github.com/globalis-ms/wp-cubi-imagemin/blob/master/LICENSE.md)

Standalone image minification WordPress plugin

[![wp-cubi](https://github.com/wp-globalis-tools/wp-cubi-logo/raw/master/wp-cubi-500x175.jpg)](https://github.com/globalis-ms/wp-cubi/)

## Overview

**wp-cubi-imagemin** is a very simple image minification plugin for WordPress, meant to be used in a composer installation. It uses a couple of image minification tools to optimize uploaded images (**jpg**, **png**, **gif** and **svg**).

**wp-cubi-imagemin** is essentially a WordPress wrapper for [psliwa/image-optimizer](https://github.com/psliwa/image-optimizer).

## Installation

- `composer require globalis/wp-cubi-imagemin`

## Configuration

The plugin will try to find the image minification tools it needs on the system. But you can provide your own binaries instead, and the plugin will use them.

To use your own binaries, just define the following constants in your configuration files, pointing to your binaries paths :

```php
define('WP_CUBI_IMAGEMIN_PATH_BIN_ADVPNG', '/var/www/your-project/bin/advpng');
define('WP_CUBI_IMAGEMIN_PATH_BIN_GIFSICLE', '/var/www/your-project/bin/gifsicle');
define('WP_CUBI_IMAGEMIN_PATH_BIN_JPEGOPTIM', '/var/www/your-project/bin/jpegoptim');
define('WP_CUBI_IMAGEMIN_PATH_BIN_JPEGTRAN', '/var/www/your-project/bin/jpegtran');
define('WP_CUBI_IMAGEMIN_PATH_BIN_OPTIPNG', '/var/www/your-project/bin/optipng');
define('WP_CUBI_IMAGEMIN_PATH_BIN_PNGCRUSH', '/var/www/your-project/bin/pngcrush');
define('WP_CUBI_IMAGEMIN_PATH_BIN_PNGOUT', '/var/www/your-project/bin/pngout');
define('WP_CUBI_IMAGEMIN_PATH_BIN_PNGQUANT', '/var/www/your-project/bin/pngquant');
define('WP_CUBI_IMAGEMIN_PATH_BIN_SVGO', '/var/www/your-project/bin/svgo');
```

If you do not define one ot the binaries paths, the plugin will try to use the system version. If it doesn't find an installed version on the system, it will just skip this tool and use the other ones.

You don't need to have all the tools working, but it is recommanded to have at least **pngquant**, **jpegoptim** and **gifsicle** to provide a meaningfull level of minification.

**Note:** binary files must have execution permissions.

## Hooks

- `apply_filters('wp-cubi-imagemin\options', $options)` : Filter the options of `ImageOptimizer\OptimizerFactory` (see the [complete list](https://github.com/psliwa/image-optimizer#configuration)), such as the JPG compression level (default to 85 in wp-cubi-imagemin).

## Bulk optimization

Bulk image optimization can be done using [wp-cli](http://wp-cli.org/) :

- Install **wp-cli** and ensure **wp-cubi-imagemin** is activated
- Usage: `wp media optimize <directories>... [--jpeg_level=<jpeg_level>]`
- Help: `wp help media optimize`

**Note:** thumbnails regeneration commands such as [`wp media regenerate`](https://developer.wordpress.org/cli/commands/media/regenerate/) will trigger plugin optimization functions as well.
