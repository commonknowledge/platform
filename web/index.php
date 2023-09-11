<?php
/**
 * WordPress View Bootstrapper
 */
define('WP_USE_THEMES', true);

define('START_TIME', $_SERVER['HTTP_X_START_TIME']);

echo "<!-- Server time: " . START_TIME . " -->\n";

function print_execution_time($tag)
{
    $time = microtime(true) - START_TIME;
    $seconds = $time / 1000;
    $str = number_format($seconds, 3);
    echo "<!-- $tag: {$str}s -->\n";
}

print_execution_time("Start");

require __DIR__ . '/wp/wp-blog-header.php';

print_execution_time("Done");
