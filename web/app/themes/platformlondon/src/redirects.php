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

/**
 * The previous site used ?issues=, ?places= and ?players= filters. WordPress ignores them
 * (the taxonomies are queried with pl_issue, pl_place and pl_player), so these URLs render
 * the same page as without them, uncached. Crawlers request endless combinations of them, so
 * redirect to the URL without the old parameters: people following old links see the same page.
 */
$legacy_params = ["issues", "places", "players"];
$query_string = $_SERVER["QUERY_STRING"] ?? "";
if ($query_string !== "") {
    $parts = explode("&", $query_string);
    $kept_parts = array_filter($parts, function ($part) use ($legacy_params) {
        return !in_array(urldecode(explode("=", $part, 2)[0]), $legacy_params, true);
    });
    if (count($kept_parts) !== count($parts)) {
        $target = $url . ($kept_parts ? "?" . implode("&", $kept_parts) : "");
        header("Cache-Control: public, max-age=86400");
        wp_redirect($target, 301);
        exit();
    }
}
