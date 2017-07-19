<?php
/*
Plugin Name: HC Custom WP-Admin URL
Version: 1.3.1
Plugin URI: http://wordpress.org/plugins/hc-custom-wp-admin-url/
Description: Small and simple plugin that allows you to change url of wp-admin
Author: Some Web Media
Author URI: http://someweblog.com/
*/

if (!class_exists('HC_CustomWPAdminURL')) {

	class HC_CustomWPAdminURL {

		function rewrite_admin_url($wp_rewrite) {
			// be sure rules are written every time permalinks are updated
			$rule = get_option('custom_wpadmin_slug');
			if ($rule != '') {
				add_rewrite_rule($rule.'/?$', 'wp-login.php', 'top');
			}
		}

		function custom_admin_url() {
			if (isset($_POST['custom_wpadmin_slug'])) {

				// sanitize input
				$wpadmin_slug = trim(sanitize_key(wp_strip_all_tags($_POST['custom_wpadmin_slug'])));
				$home_path = get_home_path();

				// check if permalinks are turned off, if so force push rules to .htaccess
				if (isset($_POST['selection']) && $_POST['selection'] == '' && $wpadmin_slug != '') {
					// check if .htaccess is writable
					if ((!file_exists($home_path . '.htaccess') && is_writable($home_path)) || is_writable($home_path . '.htaccess')) {

						// taken from wp-includes/rewrite.php
						$home_root = parse_url(home_url());
						if (isset($home_root['path'])) {
							$home_root = trailingslashit($home_root['path']);
						} else {
							$home_root = '/';
						}
						// create rules
						$rules = "<IfModule mod_rewrite.c>\n";
						$rules .= "RewriteEngine On\n";
						$rules .= "RewriteRule ^$wpadmin_slug/?$ ".$home_root."wp-login.php [QSA,L]\n";
						$rules .= "</IfModule>";
						// write to .htaccess
						insert_with_markers($home_path . '.htaccess', 'WPAdminURL', explode("\n", $rules));
					}
				} else if (isset($_POST['selection']) || (isset($_POST['selection']) && $_POST['selection'] == '' && $wpadmin_slug == '')) {
					// remove rules if permalinks were enabled
					$markerdata = explode("\n", implode('', file($home_path . '.htaccess')));
					$found = false;
					$newdata = '';
					foreach ($markerdata as $line) {
						if ($line == '# BEGIN WPAdminURL') {
							$found = true;
						}
						if (!$found) {
							$newdata .= "$line\n";
						}
						if ($line == '# END WPAdminURL') {
							$found = false;
						}
					}
					// write back
					$f = @fopen($home_path . '.htaccess', 'w');
					fwrite($f, $newdata);
				}

				// save to db
				update_option('custom_wpadmin_slug', $wpadmin_slug);

				// write rewrite rules right away
				if ($wpadmin_slug != '') {
					add_rewrite_rule($wpadmin_slug.'/?$', 'wp-login.php', 'top');
				} else {
					flush_rewrite_rules();
				}
			}
			add_settings_field('custom_wpadmin_slug', 'WP-Admin slug', array($this, 'options_page'), 'permalink', 'optional', array('label_for' => 'custom_wpadmin_slug'));
			register_setting('permalink', 'custom_wpadmin_slug', 'strval');
		}

		function options_page() {
			?>
			<input id="custom_wpadmin_slug" name="custom_wpadmin_slug" type="text" class="regular-text code" value="<?php echo get_option('custom_wpadmin_slug'); ?>">
			<p class="howto">Allowed characters are a-z, 0-9, - and _</p>
			<?php
		}

		// custom login url
		function custom_login() {
			// start session if doesn't exist
			if (!session_id()) session_start();
			$url = $this->get_url();
			// see referal url by the web client
			list($file, $arguments) = explode("?", $_SERVER['REQUEST_URI']);
			// on localhost remove subdir
			$file = ($url['rewrite_base']) ? implode("", explode("/" . $url['rewrite_base'], $file)) : $file;

			if ("/wp-login.php?loggedout=true" == $file . "?" . $arguments) {
				session_destroy();
				header("location: " . $url['scheme'] . "://" . $url['domain'] . "/" . $url['rewrite_base']);
				exit();
			} else if ("action=logout" == substr($arguments, 0, 13)) {
				unset($_SESSION['valid_login']);
			} else if ('action=lostpassword' == $url['query'] || 'action=postpass' == $url['query']) {
				// let user to this pages
			} else if ($file == "/" . get_option('custom_wpadmin_slug') || $file == "/" . get_option('custom_wpadmin_slug') . "/") {
				$_SESSION['valid_login'] = true;
			}  else if (isset($_SESSION['valid_login'])) {
				// let them pass
			} else {
				header("location: " . $url['scheme'] . "://" . $url['domain'] . "/" . $url['rewrite_base']);
				exit();
			}
		}

		// return parsed url
		function get_url() {
			$url = array();
			$url['scheme'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off" ? "https" : "http";
			$url['domain'] = $_SERVER['HTTP_HOST'];
			$url['port'] = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] ? $_SERVER["SERVER_PORT"] : "";
			$url['rewrite_base'] = ($host = explode( (($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'], get_bloginfo('url') ) ) ? preg_replace("/^\//", "", implode("", $host)) : "";
			$url['path'] = $url['rewrite_base'] ? implode("", explode("/" . $url['rewrite_base'], $_SERVER["SCRIPT_NAME"])) : $_SERVER["SCRIPT_NAME"];
			$url['query'] = $_SERVER['QUERY_STRING'];
			return $url;
		}

		function check_login() {
			// just chek if we are on the right place
			if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')) && (get_option('custom_wpadmin_slug') != false && get_option('custom_wpadmin_slug') != '')) {

				// check if our plugin have written necesery line to .htaccess, sometimes WP doesn't write correctly so we don't want to disable login in that case
				$markerdata = explode("\n", implode('', file($this->get_home_path() . '.htaccess')));
				$found = false;
				$url = $this->get_url();
				foreach ($markerdata as $line) {
					if (trim($line) == 'RewriteRule ^' . get_option('custom_wpadmin_slug') . '/?$ ' . ($url['rewrite_base'] ? '/'.$url['rewrite_base'] : '') . '/wp-login.php [QSA,L]') {
						$found = true;
					}
				}
				if ($found) {
					$this->custom_login();
				}
			}
		}

		/* Taken from "/wp-admin/includes/file.php" */
		function get_home_path() {
			$home = get_option( 'home' );
			$siteurl = get_option( 'siteurl' );
			if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
				$wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
				$pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
				$home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
				$home_path = trailingslashit( $home_path );
			} else {
				$home_path = ABSPATH;
			}
			return $home_path;
		}
	}

	$hc_custom_wpadmin_url = new HC_CustomWPAdminURL();

	// add hooks
	add_filter('generate_rewrite_rules', array($hc_custom_wpadmin_url, 'rewrite_admin_url'));
	add_action('admin_init', array($hc_custom_wpadmin_url, 'custom_admin_url'));
	add_action('login_init', array($hc_custom_wpadmin_url, 'check_login'));

}

