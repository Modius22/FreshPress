# FreshPress Survival Guide

## First Things First
Welcome. WordPress is a nice extensible and extensive software. Because they're so many people working on it,
the code is not as nice as the UI... (no offense 😉).
That's the reason why I started the work on FreshPress.
My goal is building a WordPress with an better ecosystem which bases on PSR, composer autoloading and uses a template engine etc.
Anyone is welcome to contribute to FreshPress. 

**Note**: Since this is a hard fork, there is no git log from the original repository.  
FreshPress bases on the original source (Tag v4.8)

— Julian Finkler

## Branches
Currently there are 2 branches: dev and master. The `dev` branch is updated daily and might be not stable.
`dev` is weekly merged into `master`. You could say that the `master` is a 'weekly build'.

## Installation: Famous 10-minute install
1. Clone the repository (or `$ composer create-project -s dev devtronic/freshpress`, go to step 3)
2. Run `$ composer install`
3. Tell your http server, the web root is /web
4. Open `wp-admin/install.php` in your browser. It will take you through the process to set up a `wp-config.php` file with your database connection details.
    1. If for some reason this doesn't work, don't worry. It doesn't work on all web hosts. Open up `wp-config-sample.php` with a text editor like WordPad or similar and fill in your database connection details.
    2. Save the file as `wp-config.php` and upload it.
    3. Open `wp-admin/install.php` in your browser.
5. Once the configuration file is set up, the installer will set up the tables needed for your blog. If there is an error, double check your `wp-config.php` file, and try again. If it fails again, please go to the [support forums](https://wordpress.org/support/ "WordPress support") with as much data as you can gather.
6. **If you did not enter a password, note the password given to you.** If you did not provide a username, it will be `admin`.
7. The installer should then send you to the login page (wp-login.php). Sign in with the username and password you chose during the installation. If a password was generated for you, you can then click on "Profile" to change the password.

**Note**: Later I will provide a complete build for download, that you have only upload it to your server.

## Updating
FreshPress actually does not support auto updating.  
**NEVER START ANY UPDATE FROM THE ADMIN! THE UPDATER WILL OVERWRITE THE FRESHPRESS SOURCE PERMANENTLY**

## Tips & Tricks
Look in the `docs/` Folder 🙂

## System Requirements
- [PHP](https://secure.php.net/) version **5.6** or higher.
- [MySQL](https://www.mysql.com/) version **5.0** or higher.

## Community
Join our Slack Commuity Channel: https://freshpress.slack.com

### Recommendations
1. [PHP](https://secure.php.net/) version **7** or higher.
2. [MySQL](https://www.mysql.com/) version **5.6** or higher.
3. The [mod_rewrite](https://httpd.apache.org/docs/2.2/mod/mod_rewrite.html) Apache module.
4. [HTTPS](https://wordpress.org/news/2016/12/moving-toward-ssl/) support.

## License
FreshPress is free software, and is released under the terms of the GPL version 2 or (at your option) any later version. See [LICENSE.md](LICENSE.md).
