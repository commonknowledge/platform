# Platform WordPress Site

A WordPress project based off the [Common Knowledge WordPress Starter](https://github.com/commonknowledge/wordpress-starter-template).

This folder structure uses the [Bedrock](https://roots.io/bedrock/) pattern, a modern WordPress stack.

## Requirements

- Docker [installed](https://docs.docker.com/install/)
- PHP and Composer installed locally. If not, you can use them in containers provided, prefixing all Composer commands below with `docker compose run composer <command>`

## Run locally

1. Copy `.env.example` to `.env`. There should be no need to change this file.
2. Start the docker containers with `docker compose up -d`.
3. Wait for the mysql container to be running (check with `docker compose logs -f mysql`)
   then import the seed SQL: `mysql -uroot -ppassword -P 3308 < platform.sql`
4. Install JS/CSS requirements: `cd web/app/themes; npm i`
5. While still in the `theme` directory, start the CSS processor: `npm run watch`
6. The site should now be up at http://localhost:8082.
7. The admin system is at http://localhost:8082/wp/wp-admin and the default login/password is admin/admin.

# Full development documentation

## Updating WordPress

1. Update WordPress in the `composer.json` file by increasing the version on the `roots/wordpress-no-content` line.
2. Run `composer update roots/wordpress`. This will update the `composer.lock` file as needed.
3. Push the change to GitHub.

## WP-CLI

[WP-CLI](https://wp-cli.org/) is installed in the `wordpress` container.

```

docker compose run wordpress wp --allow-root <command>

```

Note WP-CLI will not work on the host machine, as WordPress configuration refers to databases within the Docker network, not the host machine.

## Adding WordPress Plugins

Run `docker compose run composer require wpackagist-plugin/plugin-name`.

## Further Documentation

Documentation for Bedrock is available at [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

## Hosting

### Using Kinsta

The following are instructions on how to setup a new site on Kinsta.

You will need to have "Company developer" or above permissions in order to create a site.

These are exhaustive manual instructions, but should not be required after initial setup.

1. Create [a new site on Kinsta](https://kinsta.com/knowledgebase/new-site/). You want to select "Don't install WordPress" and choose the London data centre. It will provision after around ten minutes.
2. On Kinsta we have a Live environment and a staging environment. Start with the Live environment for a new build. Then after, you can create a staging environment as needed.
3. For the next steps, you will need to [add your SSH key to Kinsta](https://kinsta.com/feature-updates/add-ssh-keys/). This is to allow you to log into Kinsta over SSH.
4. You will also need to [add a SSH key to GitHub](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account). This is so you can check out the theme from GitHub over SSH when logged into Kinsta.
5. Kinsta needs to be set up to forward you GitHub SSH key to it when you connect to it over SSH and checkout the theme with Git. You can look up the precise SSH details on Kinsta under the "SFTP/SSH" in the live environment section of the admin panel. You can then add them to a `~/.ssh/config` block to look something like this.
   ```
    host <your site>_live
        User <see "SFTP/SSH" details for Live Environment>
        Hostname <see "SFTP/SSH" details for Live Environment>
        Port <see "SFTP/SSH" details for Live Environment>
        IdentityFile <Location on your local machine of the SSH key>
        ForwardAgent yes
   ```
6. Add you GitHub SSH key to `ssh-agent` [following these instructions](https://help.github.com/en/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent#adding-your-ssh-key-to-the-ssh-agent). The command will be something like `ssh-add ~/.ssh/gitlab_key_rsa`
7. We are going to begin with the staging environment. SSH onto Kinsta staging environment using this command `ssh nurses_united_staging`.
8. Test GitHub works by running the command `ssh -T git@github.com`. After accepting the authenticity of the host you should see a friendly message from GitHub.
9. Remove the `public` directory with `rm -r public`. Clone the code into the `public/` directiory: `git clone git@github.com:commonknowledge/pluto-press.git public`.
10. `cd public && composer install`.
11 Return to the `public` directory, `cd ~/public`. If this is the first time, copy `.env.example` to `.env` with `cp .env.example .env` and modify details appropriately [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/). You can use Vim on the server. The details of the database are on the Kinsta admin panel under the site itself then "Database Access". The `WP_HOME` can be `http://localhost`.
12. You need to create database tables for WordPress. Run `wp core install --url=<URL from Primary domain in Kinsta admin panel> --title=<site name> --admin_user=<desired username> --admin_email=<desired password>`. This will output the password for the user you have just created to the terminal. Save it for when you need to login.
13. Ask Kinsta to update NGINX to point at `public/web` on Intercom chat inside the Kinsta control panel. Note, not `public/current/web`, which is the directory if you are deploy Bedrock with [Trellis](https://github.com/roots/trellis). This installation does not use Trellis.
14. You can now point the domains to this installation of WordPress following [Kinsta's instructions](https://kinsta.com/knowledgebase/add-domain/).
15. Head to Tools for the site in the live environment. Use "SSL certificate" to generate a new Let's Encrypt SSL certificate and wait for this to complete.
16. Still on the tools page, setup Force HTTPS by clicking on Modify and dollowing your nose, selecting "Force all traffic to the primary domain" along the way. Edit the `.env` file created in step 12, to have `WP_HOME` include `https` not `http`.
17. In Domains, change the DNS pointed domain to your primary one selecting "Make primary".
18. WordPress should now work at the site URL. You can login to the administration dashboard with the password you just created.
19. Repeat steps 7 through 20, but creating a development environment.


## Deploying changes

WordPress core and plugins update themselves on the server (minor versions, see
`web/app/mu-plugins/enable-minor-auto-updates.php`) and can be updated from the admin, so the live
versions are usually newer than `composer.lock`. `composer install` puts the locked versions back,
so only run it when `composer.json` or `composer.lock` changed:

```
cd ~/public
before=$(git rev-parse HEAD) && git pull && { git diff --quiet "$before" HEAD -- composer.json composer.lock || composer install --no-dev; }
```

Before changing `composer.lock`, update the WordPress and plugin versions in it to at least what the live site runs.

## Rate limiting

`web/app/mu-plugins/ck-rate-limit.php` limits how fast pages can be requested, to stop crawlers running up
the hosting bill. It only sees requests that reach PHP (Kinsta serves cached pages and static files first).

Each request counts against its IP, its IP range (/24 or /48) and, if the user agent names one, its crawler
(e.g. every `PetalBot` request, whatever the IP). Going over a limit is a strike:

| Strike | Response | For |
|---|---|---|
| 1 | 429 Too Many Requests | 10 minutes |
| 2 | 429 | 1 hour |
| 3 | 429 | 24 hours |
| 4+ | 403 Forbidden | 30 days |

Strikes are forgotten after 7 days without a new one. Logged-in users, WP-Cron and Googlebot/Bingbot
(checked against their published IP ranges, refreshed daily) are never limited.

Default limits (per minute / per hour): IP 20 / 120, range 40 / 240, crawler 10 / 120. Override them in
`.env` with `RATE_LIMIT_IP_PER_MINUTE`, `RATE_LIMIT_IP_PER_HOUR`, `RATE_LIMIT_RANGE_PER_MINUTE`,
`RATE_LIMIT_RANGE_PER_HOUR`, `RATE_LIMIT_CRAWLER_PER_MINUTE` and `RATE_LIMIT_CRAWLER_PER_HOUR`.
`RATE_LIMIT_ENABLED=false` turns it off.

Each new strike is written to the PHP error log as `[ck-rate-limit]`. On the server:

```
wp ck-ratelimit top              # busiest IPs, ranges and crawlers this hour
wp ck-ratelimit blocked          # everything blocked now
wp ck-ratelimit unban 203.0.113.5
wp ck-ratelimit unban range:203.0.113.0/24
wp ck-ratelimit ban crawler:examplebot --days=30
```

Locally, requests come from a private Docker address and are not limited. To test, send
`-H 'X-Test-Client-IP: 203.0.113.5'` (only honoured when `WP_ENV=development`).
