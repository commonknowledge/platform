<?php
/**
 * Plugin Name:  Enable Minor Automatic Updates
 * Description:  Lets WordPress install minor core and plugin updates in the background on Bedrock. Major updates stay manual.
 * Version:      1.0.0
 * Author:       Common Knowledge
 * License:      MIT License
 */

// The site is deployed as a Git clone, which WordPress treats as a reason to skip all background updates.
add_filter('automatic_updates_is_vcs_checkout', '__return_false', 1);

// Background updates can write files even while DISALLOW_FILE_MODS blocks the admin.
add_filter('file_mod_allowed', function ($allowed, $context) {
    return $context === 'automatic_updater' ? true : $allowed;
}, 10, 2);

// Core: WordPress's default is minor releases only (e.g. 6.8.3 -> 6.8.4), so WP_AUTO_UPDATE_CORE stays unset.

/**
 * Whether going from $from to $to keeps the same major version: the first number, or the first two
 * for 0.x versions (where a minor bump can break things).
 */
function ck_is_minor_update($from, $to)
{
    $a = array_map('intval', explode('.', (string) $from));
    $b = array_map('intval', explode('.', (string) $to));
    $depth = ($a[0] ?? 0) === 0 ? 2 : 1;
    for ($i = 0; $i < $depth; $i++) {
        if (($a[$i] ?? 0) !== ($b[$i] ?? 0)) {
            return false;
        }
    }
    return version_compare((string) $to, (string) $from, '>');
}

// Plugins: minor updates install themselves; majors wait for a person. CK Health (the security
// scanner's plugin) always updates.
add_filter('auto_update_plugin', function ($update, $item) {
    $file = $item->plugin ?? '';
    if ($file === 'ck-health/ck-health.php') {
        return true;
    }
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $installed = get_plugins()[$file]['Version'] ?? null;
    if ($installed === null || empty($item->new_version)) {
        return $update;
    }
    return ck_is_minor_update($installed, $item->new_version);
}, 10, 2);
