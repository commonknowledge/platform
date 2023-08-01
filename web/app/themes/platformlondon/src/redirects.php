<?php

$slug_redirects = [
    "multimedia" => "media"
];

$url = strtok($_SERVER["REQUEST_URI"], '?');
$path = trim($url, "/");

foreach ($slug_redirects as $from => $to) {
    if ($path === $from) {
        wp_redirect("/media/");
        exit();
    }
}
