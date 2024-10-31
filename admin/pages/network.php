<?php
/**
 * @package Admin
 */

if ( !defined('WPSEO_VERSION') ) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

global $wpseo_admin_pages;

$options = get_site_option( 'wpseo_ms' );

if ( isset( $_POST[ 'wpseo_submit' ] ) ) {
	check_admin_referer( 'wpseo-network-settings' );

	foreach ( array( 'access', 'defaultblog' ) as $opt ) {
		$options[ $opt ] = $_POST[ 'wpseo_ms' ][ $opt ];
	}
	update_site_option( 'wpseo_ms', $options );
	echo '<div id="message" class="updated"><p>' . __( 'Settings Updated.', 'seo-rotator-for-images' ) . '</p></div>';
}

if ( isset( $_POST[ 'wpseo_restore_blog' ] ) ) {
	check_admin_referer( 'wpseo-network-restore' );
	if ( isset( $_POST[ 'wpseo_ms' ][ 'restoreblog' ] ) && is_numeric( $_POST[ 'wpseo_ms' ][ 'restoreblog' ] ) ) {
		$blog = get_blog_details( $_POST[ 'wpseo_ms' ][ 'restoreblog' ] );
		if ( $blog ) {
			foreach ( get_wpseo_options_arr() as $option ) {
				$new_options = get_blog_option( $options[ 'defaultblog' ], $option );
				if ( count( $new_options ) > 0 )
					update_blog_option( $_POST[ 'wpseo_ms' ][ 'restoreblog' ], $option, $new_options );
			}
			echo '<div id="message" class="updated"><p>' . $blog->blogname . ' ' . __( 'restored to default SEO settings.', 'seo-rotator-for-images' ) . '</p></div>';
		}
	}
}

$wpseo_admin_pages->admin_header( false );

$content = '<form method="post" accept-charset="' . get_bloginfo( 'charset' ) . '">';
$content .= wp_nonce_field( 'wpseo-network-settings', '_wpnonce', true, false );
$content .= $wpseo_admin_pages->select( 'access', __( 'Who should have access to the Super SEO settings', 'seo-rotator-for-images' ),
	array(
		'admin'      => __( 'Site Admins (default)', 'seo-rotator-for-images' ),
		'superadmin' => __( 'Super Admins only', 'seo-rotator-for-images' )
	), 'wpseo_ms'
);
$content .= $wpseo_admin_pages->textinput( 'defaultblog', __( 'New blogs get the SEO settings from this blog', 'seo-rotator-for-images' ), 'wpseo_ms' );
$content .= '<p>' . __( 'Enter the Blog ID for the site whose settings you want to use as default for all sites that are added to your network. Leave empty for none.', 'seo-rotator-for-images' ) . '</p>';
$content .= '<input type="submit" name="wpseo_submit" class="button-primary" value="' . __( 'Save MultiSite Settings', 'seo-rotator-for-images' ) . '"/>';
$content .= '</form>';

$wpseo_admin_pages->postbox( 'wpseo_export', __( 'MultiSite Settings', 'seo-rotator-for-images' ), $content );

$content = '<form method="post" accept-charset="' . get_bloginfo( 'charset' ) . '">';
$content .= wp_nonce_field( 'wpseo-network-restore', '_wpnonce', true, false );
$content .= '<p>' . __( 'Using this form you can reset a site to the default SEO settings.', 'seo-rotator-for-images' ) . '</p>';
$content .= $wpseo_admin_pages->textinput( 'restoreblog', __( 'Blog ID', 'seo-rotator-for-images' ), 'wpseo_ms' );
$content .= '<input type="submit" name="wpseo_restore_blog" value="' . __( 'Restore site to defaults', 'seo-rotator-for-images' ) . '" class="button"/>';
$content .= '</form>';

$wpseo_admin_pages->postbox( 'wpseo_export', __( 'Restore site to default settings', 'seo-rotator-for-images' ), $content );

$wpseo_admin_pages->admin_footer( false );