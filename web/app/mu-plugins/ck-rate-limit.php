<?php
/**
 * Plugin Name:  Rate Limit
 * Description:  Limits how fast one IP, one IP range or one crawler can request pages. Over the limit gets a 429
 *               for longer each time, then a 30-day 403 ban. Manage with `wp ck-ratelimit`.
 * Version:      1.0.0
 * Author:       Common Knowledge
 * License:      MIT License
 *
 * Only requests that reach PHP are counted: Kinsta serves cached pages and static files before WordPress runs.
 *
 * Each request is counted in up to three buckets:
 *   ip:<address>     one client
 *   range:<cidr>     its /24 (IPv4) or /48 (IPv6), to catch crawlers spread over neighbouring addresses
 *   crawler:<name>   every request whose user agent names the same crawler (e.g. all "petalbot" traffic)
 * A bucket over its per-minute or per-hour limit gets a strike and is blocked for STRIKE_BLOCK_SECONDS[strike].
 *
 * Settings can be overridden in .env, e.g. RATE_LIMIT_IP_PER_MINUTE=20. RATE_LIMIT_ENABLED=false turns it off.
 */

namespace CommonKnowledge\RateLimit;

const DB_VERSION = '1';
const TABLE = 'ck_rate_limit';
const RANGES_OPTION = 'ck_rate_limit_verified_ranges';
const CRON_HOOK = 'ck_rate_limit_daily';

// Block length for each strike. From the 4th strike on, the block is a 403 ban instead of a 429.
const STRIKE_BLOCK_SECONDS = [1 => 600, 2 => 3600, 3 => 86400, 4 => 30 * 86400];
const BAN_STRIKE = 4;
// Strikes are forgotten after this long without a new one
const STRIKE_RESET_SECONDS = 7 * 86400;
// Rows are deleted after this long without a request, unless still blocked or with recent strikes
const ROW_EXPIRY_SECONDS = 30 * 86400;

// Published IP ranges of search engines that are never limited, if the request comes from one of them
const VERIFIED_CRAWLERS = [
    'googlebot' => 'https://developers.google.com/static/search/apis/ipranges/googlebot.json',
    'bingbot' => 'https://www.bing.com/toolbox/bingbot.json',
];

// Link preview fetchers run when people share a link. They still count per IP and range, but not as a crawler.
const LINK_PREVIEW_AGENTS = [
    'facebookexternalhit', 'twitterbot', 'linkedinbot', 'slackbot', 'whatsapp', 'discordbot',
    'telegrambot', 'mastodon', 'bluesky', 'skypeuripreview', 'redditbot',
];

function setting($name, $default)
{
    $value = function_exists('Env\env') ? \Env\env("RATE_LIMIT_$name") : getenv("RATE_LIMIT_$name");
    return ($value === null || $value === false || $value === '') ? $default : $value;
}

function limits()
{
    return [
        'ip' => [(int) setting('IP_PER_MINUTE', 20), (int) setting('IP_PER_HOUR', 120)],
        'range' => [(int) setting('RANGE_PER_MINUTE', 40), (int) setting('RANGE_PER_HOUR', 240)],
        'crawler' => [(int) setting('CRAWLER_PER_MINUTE', 10), (int) setting('CRAWLER_PER_HOUR', 120)],
    ];
}

function table()
{
    global $wpdb;
    return $wpdb->base_prefix . TABLE;
}

function install()
{
    global $wpdb;
    if (get_option('ck_rate_limit_db_version') === DB_VERSION) {
        return;
    }
    $charset = $wpdb->get_charset_collate();
    $wpdb->query(
        "CREATE TABLE IF NOT EXISTS " . table() . " (
            bucket VARCHAR(128) NOT NULL,
            minute_start INT UNSIGNED NOT NULL DEFAULT 0,
            minute_hits INT UNSIGNED NOT NULL DEFAULT 0,
            hour_start INT UNSIGNED NOT NULL DEFAULT 0,
            hour_hits INT UNSIGNED NOT NULL DEFAULT 0,
            total_hits INT UNSIGNED NOT NULL DEFAULT 0,
            strikes TINYINT UNSIGNED NOT NULL DEFAULT 0,
            last_strike INT UNSIGNED NOT NULL DEFAULT 0,
            blocked_until INT UNSIGNED NOT NULL DEFAULT 0,
            last_seen INT UNSIGNED NOT NULL DEFAULT 0,
            user_agent VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (bucket),
            KEY blocked_until (blocked_until),
            KEY last_seen (last_seen)
        ) $charset"
    );
    update_option('ck_rate_limit_db_version', DB_VERSION, true);
}

function client_ip()
{
    // Kinsta passes the visitor's address as REMOTE_ADDR. Local development is behind Docker, so allow faking it there.
    if (defined('WP_ENV') && WP_ENV === 'development' && !empty($_SERVER['HTTP_X_TEST_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_TEST_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function is_public_ip($ip)
{
    return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

function ip_range($ip)
{
    $packed = @inet_pton($ip);
    if ($packed === false) {
        return null;
    }
    if (strlen($packed) === 4) {
        return inet_ntop(substr($packed, 0, 3) . "\0") . '/24';
    }
    return inet_ntop(substr($packed, 0, 6) . str_repeat("\0", 10)) . '/48';
}

function ip_in_cidr($ip, $cidr)
{
    [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
    $ip_bin = @inet_pton($ip);
    $subnet_bin = @inet_pton($subnet);
    if ($ip_bin === false || $subnet_bin === false || strlen($ip_bin) !== strlen($subnet_bin)) {
        return false;
    }
    $bits = $bits === null ? strlen($ip_bin) * 8 : (int) $bits;
    $bytes = intdiv($bits, 8);
    if (substr($ip_bin, 0, $bytes) !== substr($subnet_bin, 0, $bytes)) {
        return false;
    }
    $remainder = $bits % 8;
    if ($remainder === 0) {
        return true;
    }
    $mask = (0xff << (8 - $remainder)) & 0xff;
    return (ord($ip_bin[$bytes]) & $mask) === (ord($subnet_bin[$bytes]) & $mask);
}

/**
 * The crawler named in a user agent, e.g. "petalbot" for "Mozilla/5.0 (compatible; PetalBot; ...)".
 */
function crawler_name($user_agent)
{
    if (preg_match('/([a-z][\w.-]*?(?:bot|spider|crawler|externalagent|externalhit))\b/i', $user_agent, $m)) {
        return strtolower($m[1]);
    }
    return null;
}

function is_verified_crawler($ip, $crawler)
{
    if (!isset(VERIFIED_CRAWLERS[$crawler])) {
        return false;
    }
    $ranges = get_option(RANGES_OPTION);
    foreach ($ranges[$crawler] ?? [] as $cidr) {
        if (ip_in_cidr($ip, $cidr)) {
            return true;
        }
    }
    return false;
}

function refresh_verified_ranges()
{
    $ranges = get_option(RANGES_OPTION) ?: [];
    foreach (VERIFIED_CRAWLERS as $crawler => $url) {
        $response = wp_remote_get($url, ['timeout' => 15]);
        $data = is_wp_error($response) ? null : json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['prefixes'])) {
            // Keep the previous list rather than locking a search engine out
            error_log("[ck-rate-limit] Could not refresh $crawler IP ranges from $url");
            continue;
        }
        $ranges[$crawler] = array_values(array_filter(array_map(function ($prefix) {
            return $prefix['ipv4Prefix'] ?? $prefix['ipv6Prefix'] ?? null;
        }, $data['prefixes'])));
    }
    update_option(RANGES_OPTION, $ranges, false);
    return $ranges;
}

function cleanup()
{
    global $wpdb;
    $now = time();
    $wpdb->query($wpdb->prepare(
        "DELETE FROM " . table() . " WHERE last_seen < %d AND blocked_until < %d AND last_strike < %d",
        $now - ROW_EXPIRY_SECONDS,
        $now,
        $now - STRIKE_RESET_SECONDS
    ));
}

function has_logged_in_cookie()
{
    foreach (array_keys($_COOKIE) as $name) {
        if (str_starts_with($name, 'wordpress_logged_in_')) {
            return true;
        }
    }
    return false;
}

/**
 * The buckets this request counts against, or [] if it is exempt.
 */
function request_buckets()
{
    if ((defined('WP_CLI') && WP_CLI) || wp_doing_cron() || !setting_enabled()) {
        return [];
    }
    $ip = client_ip();
    if (!is_public_ip($ip)) {
        return [];
    }
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $crawler = crawler_name($user_agent);
    if ($crawler && is_verified_crawler($ip, $crawler)) {
        return [];
    }
    $buckets = ['ip' => "ip:$ip"];
    $range = ip_range($ip);
    if ($range) {
        $buckets['range'] = "range:$range";
    }
    if ($crawler && !in_array($crawler, LINK_PREVIEW_AGENTS, true)) {
        $buckets['crawler'] = "crawler:$crawler";
    }
    return $buckets;
}

function setting_enabled()
{
    return filter_var(setting('ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN);
}

/**
 * Count this request and work out whether it must be refused.
 *
 * @return array|null ['status' => 429|403, 'retry_after' => seconds] to refuse the request, or null to allow it.
 */
function check()
{
    global $wpdb;
    $buckets = request_buckets();
    if (!$buckets) {
        return null;
    }
    install();

    $now = time();
    $table = table();
    $placeholders = implode(',', array_fill(0, count($buckets), '%s'));
    $rows = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE bucket IN ($placeholders)", array_values($buckets)),
        OBJECT_K
    );

    // Already blocked: refuse without counting
    $refusal = null;
    foreach ($rows as $row) {
        if ((int) $row->blocked_until > $now) {
            $refusal = refusal_for($row, $now, $refusal);
        }
    }
    if ($refusal) {
        return $refusal;
    }

    // Count the request in fixed per-minute and per-hour windows
    $minute = $now - ($now % 60);
    $hour = $now - ($now % 3600);
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $values = [];
    foreach ($buckets as $bucket) {
        $values[] = $wpdb->prepare('(%s, %d, 1, %d, 1, 1, %d, %s)', $bucket, $minute, $hour, $now, $user_agent);
    }
    $wpdb->query(
        "INSERT INTO $table (bucket, minute_start, minute_hits, hour_start, hour_hits, total_hits, last_seen, user_agent)
        VALUES " . implode(',', $values) . "
        ON DUPLICATE KEY UPDATE
            minute_hits = IF(minute_start = VALUES(minute_start), minute_hits + 1, 1),
            minute_start = VALUES(minute_start),
            hour_hits = IF(hour_start = VALUES(hour_start), hour_hits + 1, 1),
            hour_start = VALUES(hour_start),
            total_hits = total_hits + 1,
            last_seen = VALUES(last_seen),
            user_agent = VALUES(user_agent)"
    );

    // Strike any bucket that has just gone over a limit
    $limits = limits();
    foreach ($buckets as $type => $bucket) {
        $row = $rows[$bucket] ?? null;
        $minute_hits = ($row && (int) $row->minute_start === $minute) ? $row->minute_hits + 1 : 1;
        $hour_hits = ($row && (int) $row->hour_start === $hour) ? $row->hour_hits + 1 : 1;
        [$per_minute, $per_hour] = $limits[$type];
        if ($minute_hits <= $per_minute && $hour_hits <= $per_hour) {
            continue;
        }
        $strikes = ($row && (int) $row->last_strike > $now - STRIKE_RESET_SECONDS) ? (int) $row->strikes : 0;
        $strikes = min($strikes + 1, BAN_STRIKE);
        $blocked_until = $now + STRIKE_BLOCK_SECONDS[$strikes];
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET strikes = %d, last_strike = %d, blocked_until = %d WHERE bucket = %s",
            $strikes,
            $now,
            $blocked_until,
            $bucket
        ));
        error_log(sprintf(
            '[ck-rate-limit] %s strike %d (%d/min, %d/hour), blocked for %ds. UA: %s',
            $bucket,
            $strikes,
            $minute_hits,
            $hour_hits,
            STRIKE_BLOCK_SECONDS[$strikes],
            $user_agent
        ));
        $refusal = refusal_for((object) ['strikes' => $strikes, 'blocked_until' => $blocked_until], $now, $refusal);
    }
    return $refusal;
}

/**
 * The refusal for a blocked row, keeping whichever of it and $current is more severe.
 */
function refusal_for($row, $now, $current)
{
    $refusal = [
        'status' => (int) $row->strikes >= BAN_STRIKE ? 403 : 429,
        'retry_after' => (int) $row->blocked_until - $now,
    ];
    if (!$current || $refusal['status'] > $current['status'] || $refusal['retry_after'] > $current['retry_after']) {
        return $refusal;
    }
    return $current;
}

function refuse($refusal)
{
    status_header($refusal['status']);
    header('Cache-Control: no-store, private');
    header('Content-Type: text/plain; charset=utf-8');
    if ($refusal['status'] === 429) {
        header('Retry-After: ' . $refusal['retry_after']);
        $minutes = max(1, (int) ceil($refusal['retry_after'] / 60));
        echo "Too many requests. Please try again in $minutes minute" . ($minutes === 1 ? '' : 's') . ".\n";
    } else {
        echo "Access from your network has been blocked because of too many requests.\n";
    }
    exit;
}

function run()
{
    if (!has_logged_in_cookie()) {
        $refusal = check();
        if ($refusal) {
            refuse($refusal);
        }
        return;
    }
    // Logged-in users are not limited (the block editor makes many requests). The login cookie can only be checked
    // once pluggable.php has loaded, so wait until then, and limit the request as normal if it is not genuine.
    add_action('plugins_loaded', function () {
        if (wp_validate_auth_cookie('', 'logged_in')) {
            return;
        }
        $refusal = check();
        if ($refusal) {
            refuse($refusal);
        }
    }, PHP_INT_MIN);
}

run();

add_action(CRON_HOOK, function () {
    refresh_verified_ranges();
    cleanup();
});
add_action('init', function () {
    if (!wp_next_scheduled(CRON_HOOK)) {
        wp_schedule_event(time(), 'daily', CRON_HOOK);
    }
    if (get_option(RANGES_OPTION) === false) {
        // First run: fetch the ranges in the background so search engines are recognised straight away
        wp_schedule_single_event(time(), CRON_HOOK);
    }
});

if (defined('WP_CLI') && WP_CLI) {
    /**
     * Inspect and manage rate limiting.
     */
    \WP_CLI::add_command('ck-ratelimit', new class {
        /**
         * List the busiest buckets (IPs, IP ranges and crawlers) by requests in the current hour.
         *
         * [--limit=<n>]
         * : How many to list. Default 25.
         */
        public function top($args, $assoc_args)
        {
            global $wpdb;
            install();
            $hour = time() - (time() % 3600);
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT bucket, IF(hour_start = %d, hour_hits, 0) AS this_hour, total_hits, strikes, blocked_until,
                    last_seen, user_agent FROM " . table() . " ORDER BY this_hour DESC, total_hits DESC LIMIT %d",
                $hour,
                (int) ($assoc_args['limit'] ?? 25)
            ), ARRAY_A);
            print_rows($rows);
        }

        /**
         * List blocked buckets.
         */
        public function blocked()
        {
            global $wpdb;
            install();
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT bucket, strikes, blocked_until, total_hits, last_seen, user_agent FROM " . table() . "
                WHERE blocked_until > %d ORDER BY blocked_until DESC",
                time()
            ), ARRAY_A);
            print_rows($rows);
        }

        /**
         * Lift a block and clear the strikes.
         *
         * <bucket>
         * : An IP address, or a bucket as listed, e.g. range:203.0.113.0/24 or crawler:petalbot.
         */
        public function unban($args)
        {
            global $wpdb;
            install();
            $bucket = bucket_arg($args[0]);
            $updated = $wpdb->query($wpdb->prepare(
                "UPDATE " . table() . " SET strikes = 0, last_strike = 0, blocked_until = 0, minute_hits = 0, hour_hits = 0
                WHERE bucket = %s",
                $bucket
            ));
            $updated ? \WP_CLI::success("Unblocked $bucket.") : \WP_CLI::warning("No record of $bucket.");
        }

        /**
         * Ban a bucket (403) straight away.
         *
         * <bucket>
         * : An IP address, or a bucket, e.g. range:203.0.113.0/24 or crawler:petalbot.
         *
         * [--days=<days>]
         * : How long for. Default 30.
         */
        public function ban($args, $assoc_args)
        {
            global $wpdb;
            install();
            $bucket = bucket_arg($args[0]);
            $now = time();
            $until = $now + (int) round((float) ($assoc_args['days'] ?? 30) * 86400);
            $wpdb->query($wpdb->prepare(
                "INSERT INTO " . table() . " (bucket, strikes, last_strike, blocked_until, last_seen)
                VALUES (%s, %d, %d, %d, %d)
                ON DUPLICATE KEY UPDATE strikes = VALUES(strikes), last_strike = VALUES(last_strike),
                    blocked_until = VALUES(blocked_until)",
                $bucket,
                BAN_STRIKE,
                $now,
                $until,
                $now
            ));
            \WP_CLI::success("Banned $bucket until " . gmdate('Y-m-d H:i', $until) . ' UTC.');
        }

        /**
         * Download Googlebot's and Bingbot's IP ranges now (otherwise done daily).
         *
         * @subcommand refresh-ranges
         */
        public function refreshRanges()
        {
            $ranges = refresh_verified_ranges();
            foreach ($ranges as $crawler => $cidrs) {
                \WP_CLI::log("$crawler: " . count($cidrs) . ' ranges');
            }
        }
    });

    function bucket_arg($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) ? "ip:$value" : $value;
    }

    function print_rows($rows)
    {
        $now = time();
        foreach ($rows as &$row) {
            $row['blocked_until'] = $row['blocked_until'] > $now ? gmdate('Y-m-d H:i', $row['blocked_until']) : '';
            $row['last_seen'] = $row['last_seen'] ? gmdate('Y-m-d H:i', $row['last_seen']) : '';
            $row['user_agent'] = substr($row['user_agent'] ?? '', 0, 60);
        }
        \WP_CLI\Utils\format_items('table', $rows, $rows ? array_keys($rows[0]) : ['bucket']);
    }
}
