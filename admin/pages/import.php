<?php
/**
 * @package Admin
 */

if ( !defined('WPSEO_VERSION') ) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

global $wpseo_admin_pages;

/**
 * Used for imports, this functions either copies $old_metakey into $new_metakey or just plain replaces $old_metakey with $new_metakey
 *
 * @param string $old_metakey The old name of the meta value.
 * @param string $new_metakey The new name of the meta value, usually the WP SEO name.
 * @param bool   $replace     Whether to replace or to copy the values.
 */
function replace_meta( $old_metakey, $new_metakey, $replace = false ) {
	global $wpdb;
	$oldies = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", $old_metakey ) );
	foreach ( $oldies as $old ) {
		// Prevent inserting new meta values for posts that already have a value for that new meta key
		$check = get_post_meta( $old->post_id, $new_metakey, true );
		if ( !$check || empty($check) )
			update_post_meta( $old->post_id, $new_metakey, $old->meta_value );

		if ( $replace )
			delete_post_meta( $old->post_id, $old_metakey );
	}
}

$msg = '';
if ( isset( $_POST['import'] ) ) {

	check_admin_referer( 'wpseo-import' );

	global $wpdb;
	$replace  = false;
	$deletekw = false;

	if ( isset( $_POST['wpseo']['deleteolddata'] ) && $_POST['wpseo']['deleteolddata'] == 'on' ) {
		$replace = true;
	}
	if ( isset( $_POST['wpseo']['importwoo'] ) ) {
		wpseo_defaults();

		$sep = get_option('seo_woo_seperator');

		$options = get_wpseo_options();

		switch ( get_option( 'seo_woo_home_layout' ) ) {
			case 'a':
				$options['title-home'] = '%%sitename%% '.$sep.' %%sitedesc%%';
				break;
			case 'b':
				$options['title-home'] = '%%sitename%% '.get_option('seo_woo_paged_var').' %%pagenum%%';
				break;
			case 'c':
				$options['title-home'] = '%%sitedesc%%';
				break;
		}
		if ( $replace )
			delete_option('seo_woo_home_layout');

		switch ( get_option( 'seo_woo_single_layout' ) ) {
			case 'a':
				$options['title-post'] = '%%title%% '.$sep.' %%sitename%%';
				break;
			case 'b':
				$options['title-post'] = '%%title%%';
				break;
			case 'c':
				$options['title-post'] = '%%sitename%% '.$sep.' %%title%%';
				break;
			case 'd':
				$options['title-post'] = '%%title%% '.$sep.' %%sitedesc%%';
				break;
			case 'e':
				$options['title-post'] = '%%sitename%% '.$sep.' %%title%% '.$sep.' %%sitedesc%%';
				break;
		}
		if ( $replace )
			delete_option('seo_woo_single_layout');

		switch ( get_option( 'seo_woo_page_layout' ) ) {
			case 'a':
				$options['title-page'] = '%%title%% '.$sep.' %%sitename%%';
				break;
			case 'b':
				$options['title-page'] = '%%title%%';
				break;
			case 'c':
				$options['title-page'] = '%%sitename%% '.$sep.' %%title%%';
				break;
			case 'd':
				$options['title-page'] = '%%title%% '.$sep.' %%sitedesc%%';
				break;
			case 'e':
				$options['title-page'] = '%%sitename%% '.$sep.' %%title%% '.$sep.' %%sitedesc%%';
				break;
		}
		if ( $replace )
			delete_option('seo_woo_page_layout');

		$template = '%%term_title%% '.$sep.' %%page%% '.$sep.' %%sitename%%';
		switch ( get_option( 'seo_woo_archive_layout' ) ) {
			case 'a':
				$template = '%%term_title%% '.$sep.' %%page%% '.$sep.' %%sitename%%';
				break;
			case 'b':
				$template = '%%term_title%%';
				break;
			case 'c':
				$template = '%%sitename%% '.$sep.' %%term_title%% '.$sep.' %%page%%';
				break;
			case 'd':
				$template = '%%term_title%% '.$sep.' %%page%%'.$sep.' %%sitedesc%%';
				break;
			case 'e':
				$template = '%%sitename%% '.$sep.' %%term_title%% '.$sep.' %%page%% '.$sep.' %%sitedesc%%';
				break;
		}
		if ( $replace )
			delete_option('seo_woo_archive_layout');

		foreach ( get_taxonomies( array( 'public' => true ), 'names' ) as $tax ) {
			$options['title-'.$tax] = $template;
		}

			// Import the custom homepage description
		if ( 'c' == get_option( 'seo_woo_meta_home_desc' ) ) {
			$options['metadesc-home'] = get_option( 'seo_woo_meta_home_desc_custom' );
		}
		if ( $replace )
			delete_option('seo_woo_meta_home_desc');

		// Import the custom homepage keywords
		if ( 'c' == get_option( 'seo_woo_meta_home_key' ) ) {
			$options['metakey-home'] = get_option( 'seo_woo_meta_home_key_custom' );
		}
		if ( $replace )
			delete_option('seo_woo_meta_home_key');

		// If WooSEO is set to use the Woo titles, import those
		if ( 'true' == get_option( 'seo_woo_wp_title' ) ) {
			replace_meta( 'seo_title', '_super_wpseo_title', $replace );
		}

		// If WooSEO is set to use the Woo meta descriptions, import those
		if ( 'b' == get_option( 'seo_woo_meta_single_desc' ) ) {
			replace_meta( 'seo_description', '_super_wpseo_metadesc', $replace );
		}

		// If WooSEO is set to use the Woo meta keywords, import those
		if ( 'b' == get_option( 'seo_woo_meta_single_key' ) ) {
			replace_meta( 'seo_keywords', '_super_wpseo_metakeywords', $replace );
		}

		replace_meta( 'seo_follow', '_super_wpseo_meta-robots-nofollow', $replace );
		replace_meta( 'seo_noindex', '_super_wpseo_meta-robots-noindex', $replace );

		$msg .= __( 'WooThemes SEO framework settings &amp; data successfully imported.', 'seo-rotator-for-images' );
	}
	if ( isset( $_POST['wpseo']['importheadspace'] ) ) {
		replace_meta( '_headspace_description', '_super_wpseo_metadesc', $replace );
		replace_meta( '_headspace_keywords', '_super_wpseo_metakeywords', $replace );
		replace_meta( '_headspace_page_title', '_super_wpseo_title', $replace );
		replace_meta( '_headspace_noindex', '_super_wpseo_meta-robots-noindex', $replace );
		replace_meta( '_headspace_nofollow', '_super_wpseo_meta-robots-nofollow', $replace );

		$posts = $wpdb->get_results( "SELECT ID FROM $wpdb->posts" );
		foreach ( $posts as $post ) {
			$custom         = get_post_custom( $post->ID );
			$robotsmeta_adv = '';
			if ( isset( $custom['_headspace_noarchive'] ) ) {
				$robotsmeta_adv .= 'noarchive,';
			}
			if ( isset( $custom['_headspace_noodp'] ) ) {
				$robotsmeta_adv .= 'noodp,';
			}
			if ( isset( $custom['_headspace_noydir'] ) ) {
				$robotsmeta_adv .= 'noydir';
			}
			$robotsmeta_adv = preg_replace( '`,$`', '', $robotsmeta_adv );
			wpseo_set_value( 'meta-robots-adv', $robotsmeta_adv, $post->ID );

			if ( $replace ) {
				foreach ( array( 'noindex', 'nofollow', 'noarchive', 'noodp', 'noydir' ) as $meta ) {
					delete_post_meta( $post->ID, '_headspace_' . $meta );
				}
			}
		}
		$msg .= __('HeadSpace2 data successfully imported','seo-rotator-for-images');
	}
	if ( isset( $_POST['wpseo']['importaioseo'] ) ) {
		replace_meta( '_aioseop_description', '_super_wpseo_metadesc', $replace );
		replace_meta( '_aioseop_keywords', '_super_wpseo_metakeywords', $replace );
		replace_meta( '_aioseop_title', '_super_wpseo_title', $replace );
		$msg .= __( 'All in One SEO data successfully imported.', 'seo-rotator-for-images' );
	}
	if ( isset( $_POST['wpseo']['importaioseoold'] ) ) {
		replace_meta( 'description', '_super_wpseo_metadesc', $replace );
		replace_meta( 'keywords', '_super_wpseo_metakeywords', $replace );
		replace_meta( 'title', '_super_wpseo_title', $replace );
		$msg .= __( 'All in One SEO (Old version) data successfully imported.', 'seo-rotator-for-images' );
	}
	if ( isset( $_POST['wpseo']['importrobotsmeta'] ) ) {
		$posts = $wpdb->get_results( "SELECT ID, robotsmeta FROM $wpdb->posts" );
		foreach ( $posts as $post ) {
			if ( strpos( $post->robotsmeta, 'noindex' ) !== false )
				wpseo_set_value( 'meta-robots-noindex', true, $post->ID );

			if ( strpos( $post->robotsmeta, 'nofollow' ) !== false )
				wpseo_set_value( 'meta-robots-nofollow', true, $post->ID );
		}
		$msg .= __( 'Robots Meta values imported.', 'seo-rotator-for-images' );
	}
	if ( isset( $_POST['wpseo']['importrssfooter'] ) ) {
		$optold = get_option( 'RSSFooterOptions' );
		$optnew = get_option( 'wpseo_rss' );
		if ( $optold['position'] == 'after' ) {
			if ( empty( $optnew['rssafter'] ) )
				$optnew['rssafter'] = $optold['footerstring'];
		} else {
			if ( empty( $optnew['rssbefore'] ) )
				$optnew['rssbefore'] = $optold['footerstring'];
		}
		update_option( 'wpseo_rss', $optnew );
		$msg .= __( 'RSS Footer options imported successfully.', 'seo-rotator-for-images' );
	}
	if ( isset( $_POST['wpseo']['importbreadcrumbs'] ) ) {
		$optold = get_option( 'super_breadcrumbs' );
		$optnew = get_option( 'wpseo_internallinks' );

		if ( is_array( $optold ) ) {
			foreach ( $optold as $opt => $val ) {
				if ( is_bool( $val ) && $val == true )
					$optnew['breadcrumbs-' . $opt] = 'on';
				else
					$optnew['breadcrumbs-' . $opt] = $val;
			}
			update_option( 'wpseo_internallinks', $optnew );
			$msg .= __( 'super Breadcrumbs options imported successfully.', 'seo-rotator-for-images' );
		} else {
			$msg .= __( 'super Breadcrumbs options could not be found', 'seo-rotator-for-images' );
		}
	}
	if ( $replace )
		$msg .= __( ', and old data deleted.', 'seo-rotator-for-images' );
	if ( $deletekw )
		$msg .= __( ', and meta keywords data deleted.', 'seo-rotator-for-images' );
}

$wpseo_admin_pages->admin_header( false );
if ( $msg != '' )
	echo '<div id="message" class="message updated" style="width:94%;"><p>' . esc_html( $msg ) . '</p></div>';

$content = "<p>" . __( "No doubt you've used an SEO plugin before if this site isn't new. Let's make it easy on you, you can import the data below. If you want, you can import first, check if it was imported correctly, and then import &amp; delete. No duplicate data will be imported.", 'seo-rotator-for-images' ) . "</p>";
$content .= '<p>' . sprintf( __( "If you've used another SEO plugin, try the %sSEO Data Transporter%s plugin to move your data into this plugin, it rocks!", 'seo-rotator-for-images' ), "<a href='http://wordpress.org/extend/plugins/seo-data-transporter/'>", "</a>" ) . '</p>';
$content .= '<form action="" method="post" accept-charset="' . get_bloginfo( 'charset' ) . '">';
$content .= wp_nonce_field( 'wpseo-import', '_wpnonce', true, false );
$content .= $wpseo_admin_pages->checkbox( 'importheadspace', __( 'Import from HeadSpace2?', 'seo-rotator-for-images' ) );
$content .= $wpseo_admin_pages->checkbox( 'importaioseo', __( 'Import from All-in-One SEO?', 'seo-rotator-for-images' ) );
$content .= $wpseo_admin_pages->checkbox( 'importaioseoold', __( 'Import from OLD All-in-One SEO?', 'seo-rotator-for-images' ) );
$content .= $wpseo_admin_pages->checkbox( 'importwoo', __( 'Import from WooThemes SEO framework?', 'seo-rotator-for-images' ) );
$content .= '<br/>';
$content .= $wpseo_admin_pages->checkbox( 'deleteolddata', __( 'Delete the old data after import? (recommended)', 'seo-rotator-for-images' ) );
$content .= '<br/>';
$content .= '<input type="submit" class="button-primary" name="import" value="' . __( 'Import', 'seo-rotator-for-images' ) . '" />';
$content .= '<br/><br/>';
$content .= '<h2>' . __( 'Import settings from other plugins', 'seo-rotator-for-images' ) . '</h2>';
$content .= $wpseo_admin_pages->checkbox( 'importrobotsmeta', __( 'Import from Robots Meta (by superseo.com)?', 'seo-rotator-for-images' ) );
$content .= $wpseo_admin_pages->checkbox( 'importrssfooter', __( 'Import from RSS Footer (by superseo.com)?', 'seo-rotator-for-images' ) );
$content .= $wpseo_admin_pages->checkbox( 'importbreadcrumbs', __( 'Import from super Breadcrumbs?', 'seo-rotator-for-images' ) );
$content .= '<br/>';
$content .= '<input type="submit" class="button-primary" name="import" value="' . __( 'Import', 'seo-rotator-for-images' ) . '" />';
$content .= '</form><br/>';

$wpseo_admin_pages->postbox( 'import', __( 'Import', 'seo-rotator-for-images' ), $content );

do_action( 'wpseo_import', $this );

$content = '<h4>' . __( 'Export', 'seo-rotator-for-images' ) . '</h4>';
$content .= '<form method="post" accept-charset="' . get_bloginfo( 'charset' ) . '">';
$content .= wp_nonce_field( 'wpseo-export', '_wpnonce', true, false );
$content .= '<p>' . __( 'Export your Website SEO settings here, to import them again later or to import them on another site.', 'seo-rotator-for-images' ) . '</p>';
if ( phpversion() > 5.2 )
	$content .= $wpseo_admin_pages->checkbox( 'include_taxonomy_meta', __( 'Include Taxonomy Metadata', 'seo-rotator-for-images' ) );
$content .= '<br/><input type="submit" class="button" name="wpseo_export" value="' . __( 'Export settings', 'seo-rotator-for-images' ) . '"/>';
$content .= '</form>';
if ( isset( $_POST['wpseo_export'] ) ) {
	check_admin_referer( 'wpseo-export' );
	$include_taxonomy = false;
	if ( isset( $_POST['wpseo']['include_taxonomy_meta'] ) )
		$include_taxonomy = true;
	$url = $wpseo_admin_pages->export_settings( $include_taxonomy );
	if ( $url ) {
		$content .= '<script type="text/javascript">
			document.location = \'' . $url . '\';
		</script>';
	} else {
		$content .= 'Error: ' . $url;
	}
}

$content .= '<h4>' . __( 'Import', 'seo-rotator-for-images' ) . '</h4>';
if ( !isset( $_FILES['settings_import_file'] ) || empty( $_FILES['settings_import_file'] ) ) {
	$content .= '<p>' . __( 'Import settings by locating <em>settings.zip</em> and clicking', 'seo-rotator-for-images' ) . ' "' . __( 'Import settings', 'seo-rotator-for-images' ) . '":</p>';
	$content .= '<form method="post" enctype="multipart/form-data" accept-charset="' . get_bloginfo( 'charset' ) . '">';
	$content .= wp_nonce_field( 'wpseo-import-file', '_wpnonce', true, false );
	$content .= '<input type="file" name="settings_import_file"/>';
	$content .= '<input type="hidden" name="action" value="wp_handle_upload"/>';
	$content .= '<input type="submit" class="button" value="' . __( 'Import settings', 'seo-rotator-for-images' ) . '"/>';
	$content .= '</form><br/>';
} else if ( isset( $_FILES['settings_import_file'] ) ) {
	check_admin_referer( 'wpseo-import-file' );
	$file = wp_handle_upload( $_FILES['settings_import_file'] );

	if ( isset( $file['file'] ) && !is_wp_error( $file ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		$zip      = new PclZip( $file['file'] );
		$unzipped = $zip->extract( $p_path = WP_CONTENT_DIR . '/wpseo-import/' );
		if ( $unzipped[0]['stored_filename'] == 'settings.ini' ) {
			$options = parse_ini_file( WP_CONTENT_DIR . '/wpseo-import/settings.ini', true );
			foreach ( $options as $name => $optgroup ) {
				if ( $name != 'wpseo_taxonomy_meta' ) {
					update_option( $name, $optgroup );
				} else {
					update_option( $name, json_decode( urldecode( $optgroup['wpseo_taxonomy_meta'] ), true ) );
				}
			}
			@unlink( WP_CONTENT_DIR . '/wpseo-import/' );
			@unlink( $file['file'] );

			$content .= '<p><strong>' . __( 'Settings successfully imported.', 'seo-rotator-for-images' ) . '</strong></p>';
		} else {
			$content .= '<p><strong>' . __( 'Settings could not be imported:', 'seo-rotator-for-images' ) . ' ' . __( 'Unzipping failed.', 'seo-rotator-for-images' ) . '</strong></p>';
		}
	} else {
		if ( is_wp_error( $file ) )
			$content .= '<p><strong>' . __( 'Settings could not be imported:', 'seo-rotator-for-images' ) . ' ' . $file['error'] . '</strong></p>';
		else
			$content .= '<p><strong>' . __( 'Settings could not be imported:', 'seo-rotator-for-images' ) . ' ' . __( 'Upload failed.', 'seo-rotator-for-images' ) . '</strong></p>';
	}
}
$wpseo_admin_pages->postbox( 'wpseo_export', __( 'Export & Import SEO Settings', 'seo-rotator-for-images' ), $content );

$wpseo_admin_pages->admin_footer( false );