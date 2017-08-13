# Configure FreshPress

One of the goals of FreshPress is a better way to configure it.
So the installer creates now a parameters.yml under app/config which replaces the wp-config.php.
The parameters.yml is loaded by the ServiceContainer. The parameters can be passed to the services (e.g. %database.host% to the WPDB).
Additionally FreshPress is using a config.yml to configure the core functionality (enable debugging...).
The config.yml is loaded by the Devtronic\FreshPress\Core\Util\ConfigMapper. The ConfigMapper maps the config.yml to the
old WP_* constants. In future these constants will be completely replaced. 