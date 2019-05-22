<?php

namespace Globalis\WP\Cubi\ImageMin;

class WonologAdaptaterLogger extends \Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        if (!defined('Inpsyde\Wonolog\LOG')) {
            return;
        }

        $channel = apply_filters('wp-cubi-imagemin\wonolog_channel', \Inpsyde\Wonolog\Channels::DEBUG);

        do_action('wonolog.log', $message, $level, $channel, $context);
    }
}
