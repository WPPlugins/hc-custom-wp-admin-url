=== HC Custom WP-Admin URL ===
Contributors: SomeWebMedia
Tags: plugin, administration, admin, custom url, login, security, wp-admin, wp-login
Requires at least: 3.2
Tested up to: 3.5.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Small and simple security plugin that allows you to change url of wp-admin

== Description ==

Want to reduce the possibility of your website been hacked or hijacked?

With this plugin you can change wp-admin and wp-login.php to any of your choice, making it impossible for the hackers to access your administration login page.
Instead of `http://yourdomain.com/wp-admin/` and `http://yourdomain.com/wp-login.php` you can have `http://yourdomain.com/admin` or `http://yourdomain.com/banana`

Its simple to use.
New field will be added to **Settings** -> **Permalinks** section called **WP-Admin slug**. All you have to do is to write your desired WP Admin slug and save the settings.

== Installation ==

1. Upload `hc-custom-wp-admin-url.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Permalink Options page inside the administration area
4. Set the field **WP-Admin slug** to the desired one

== Frequently Asked Questions ==

= Does this work with Multisite =
We don't know, but we'll look into it soon!

= What about the default Admin URLs? =

Old WP Admin urls `http://yourdomain.com/wp-admin/` and `http://yourdomain.com/wp-login.php` will not be usable until you clear the **WP-Admin slug** field from Permalink Options page or uninstal the plugin

= Anything important I should know? =

Be careful if using this plugin with other plugins which modify .htaccess file. Also if you are having trouble with your .htaccess file not being generated properly, try deleting it and re-saving Permalink options. 

== Screenshots ==

1. Blog Permalinks Options page, where you are able to set your custom URL

== Changelog ==

= 1.3.1 =
* Fixed bug on some WordPress instances where there was a problem with getting the site home path

= 1.3 =
* Fixed redirection bug of password protected pages

= 1.2 =
* If WordPress for some reason haven't applied rewrite rules to .htaccess, default wp-admin url will work

= 1.1.2 =
* Fixed redirection bug when slug set to empty

= 1.1 =
* Fixed URL for "default" permalink settings
* Fixed URL with trailing slash at the end

= 1.0 =
* Initial release
* Sets basic login and admin redirects and checks
* Able to change link through permalink options page