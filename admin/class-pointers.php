<?php
/**
 * @package Admin
 */

if ( ! defined( 'WPSEO_VERSION' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    die;
}

/**
 * This class handles the pointers used in the introduction tour.
 *
 * @todo Add an introdutory pointer on the edit post page too.
 */
class WPSEO_Pointers {

    /**
	 * Class constructor.
	 */
	function __construct() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.4', '<' ) )
			return false;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue styles and scripts needed for the pointers.
	 */
	function enqueue() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$options = get_option( 'wpseo' );
		if ( ! isset( $options['super_tracking'] ) || ( ! isset( $options['ignore_tour'] ) || ! $options['ignore_tour'] ) ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		}
		if ( ! isset( $options['tracking_popup'] ) && ! isset( $_GET['allow_tracking'] ) ) {
			add_action( 'admin_print_footer_scripts', array( $this, 'tracking_request' ) );
		}
		else if ( ! isset( $options['ignore_tour'] ) || ! $options['ignore_tour'] ) {
			add_action( 'admin_print_footer_scripts', array( $this, 'intro_tour' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
		}
	}

	/**
	 * Shows a popup that asks for permission to allow tracking.
	 */
	function tracking_request() {
		$id    = '#wpadminbar';
		$nonce = wp_create_nonce( 'wpseo_activate_tracking' );

		$content = '<h3>' . __( 'Help improve Super SEO', 'seo-rotator-for-images' ) . '</h3>';
		$content .= '<p>' . __( 'You\'ve just installed Super SEO. Please helps us improve it by allowing us to gather anonymous usage stats so we know which configurations, plugins and themes to test with.', 'seo-rotator-for-images' ) . '</p>';
		$opt_arr = array(
			'content'  => $content,
			'position' => array( 'edge' => 'top', 'align' => 'center' )
		);
		$button2 = __( 'Allow tracking', 'seo-rotator-for-images' );

		$function2 = 'wpseo_store_answer("yes","' . $nonce . '")';
		$function1 = 'wpseo_store_answer("no","' . $nonce . '")';

		$this->print_scripts( $id, $opt_arr, __( 'Do not allow tracking', 'seo-rotator-for-images' ), $button2, $function2, $function1 );
	}

	/**
	 * Load the introduction tour
	 */
	function intro_tour() {
		global $pagenow, $current_user;

		$adminpages = array(
			'wpseo_dashboard'      => array(
				'content'  => '<h3>' . __( 'Dashboard', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'This is the Super SEO Dashboard, here you can restart this tour or revert the WP SEO settings to default.', 'seo-rotator-for-images' ) . '</p>'
						. '<p><strong>' . __( 'More Super SEO', 'seo-rotator-for-images' ) . '</strong><br/>' . sprintf( __( 'There\'s more to learn about WordPress & SEO than just using this plugin.', 'seo-rotator-for-images' ), '' ) . '</p>'
						. '<p><strong>' . __( 'Webmaster Tools', 'seo-rotator-for-images' ) . '</strong><br/>' . __( 'Underneath the General Settings, you can add the verification codes for the different Webmaster Tools programs, I highly encourage you to check out both Google and Bing\'s Webmaster Tools.', 'seo-rotator-for-images' ) . '</p>'
						. '<p><strong>' . __( 'About This Tour', 'seo-rotator-for-images' ) . '</strong><br/>' . __( 'Clicking Next below takes you to the next page of the tour. If you want to stop this tour, click "Close".', 'seo-rotator-for-images' ) . '</p>'
						. '<p><strong>' . __( 'Like this plugin?', 'seo-rotator-for-images' ) . '</strong><br/>' . sprintf( __( 'If you like this plugin, please %srate it 5 stars on WordPress.org%s and consider making a donation by clicking the button on the right!', 'seo-rotator-for-images' ), '<a target="_blank" href="http://wordpress.org/extend/plugins/seo-rotator-for-images/">', '</a>' ) . '</p>' .
						'<p>' .
						'</p></form>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_titles' ) . '";'
			),
			'wpseo_titles'         => array(
				'content'  => "<h3>" . __( "Title &amp; Description settings", 'seo-rotator-for-images' ) . "</h3>"
						. "<p>" . __( "This is where you set the templates for your titles and descriptions of all the different types of pages on your blog, be it your homepage, posts & pages (under post types), category or tag archives (under taxonomy archives), or even custom post type archives and custom posts: all of that is done from here.", 'seo-rotator-for-images' ) . "</p>"
						. "<p><strong>" . __( "Templates", 'seo-rotator-for-images' ) . "</strong><br/>"
						. __( "The templates are built using variables, the help tab for all the different variables available to you to use in these.", 'seo-rotator-for-images' ) . "</p>"
						. "<p><strong>" . __( "Sitewide settings", 'seo-rotator-for-images' ) . "</strong><br/>"
						. __( "You can also set some settings for the entire site here to add specific meta tags or to remove some unneeded cruft.", 'seo-rotator-for-images' ) . "</p>",
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_social' ) . '";'
			),
			'wpseo_social'         => array(
				'content'  => "<h3>" . __( "Social settings", 'seo-rotator-for-images' ) . "</h3>"
						. "<p><strong>" . __( 'Facebook Open Graph', 'seo-rotator-for-images' ) . '</strong><br/>'
						. __( "On this page you can enable the Open Graph functionality from this plugin, as well as assign a Facebook user or Application to be the admin of your site, so you can view the Facebook insights.", 'seo-rotator-for-images' ) . "</p>"
						. '<p>' .  "</p>"
						. "<p><strong>" . __( 'Twitter Cards', 'seo-rotator-for-images' ) . '</strong><br/>'
						.  "</p>",
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_xml' ) . '";'
			),
			'wpseo_xml'            => array(
				'content'  => '<h3>' . __( 'XML Sitemaps', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'This plugin adds an XML sitemap to your site. It\'s automatically updated when you publish a new post, page or custom post and Google and Bing will be automatically notified.', 'seo-rotator-for-images' ) . '</p><p>' . __( 'Be sure to check whether post types or taxonomies are showing that search engines shouldn\'t be indexing, if so, check the box before them to hide them from the XML sitemaps.', 'seo-rotator-for-images' ) . '</p>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_permalinks' ) . '";'
			),
			'wpseo_permalinks'     => array(
				'content'  => '<h3>' . __( 'Permalink Settings', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'All of the options here are for advanced users only, if you don\'t know whether you should check any, don\'t touch them.', 'seo-rotator-for-images' ) . '</p>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_internal-links' ) . '";'
			),
			'wpseo_internal-links' => array(
				'content'  => '<h3>' . __( 'Breadcrumbs Settings', 'seo-rotator-for-images' ) . '</h3><p>'  . '</p>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_rss' ) . '";'
			),
			'wpseo_rss'            => array(
				'content'  => '<h3>' . __( 'RSS Settings', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'This incredibly powerful function allows you to add content to the beginning and end of your posts in your RSS feed. This helps you gain links from people who steal your content!', 'seo-rotator-for-images' ) . '</p>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_import' ) . '";'
			),
			'wpseo_import'         => array(
				'content'  => '<h3>' . __( 'Import &amp; Export', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'Just switched over from another SEO plugin? Use the options here to switch your data over. If you were using some of my older plugins like Robots Meta &amp; RSS Footer, you can import the settings here too.', 'seo-rotator-for-images' ) . '</p><p>' . __( 'If you have multiple blogs and you\'re happy with how you\'ve configured this blog, you can export the settings and import them on another blog so you don\'t have to go through this process twice!', 'seo-rotator-for-images' ) . '</p>',
				'button2'  => __( 'Next', 'seo-rotator-for-images' ),
				'function' => 'window.location="' . admin_url( 'admin.php?page=wpseo_files' ) . '";'
			),
			'wpseo_files'          => array(
				'content' => '<h3>' . __( 'File Editor', 'seo-rotator-for-images' ) . '</h3><p>' . __( 'Here you can edit the .htaccess and robots.txt files, two of the most powerful files in your WordPress install. Only touch these files if you know what you\'re doing!', 'seo-rotator-for-images' ) . '</p>'
						. '<p>' . sprintf( __( 'The tour ends here, thank you for using my plugin and good luck with your SEO!<br/><br/>Best,<br/>', 'seo-rotator-for-images' ), '', '' ) . '</p>',
			),
		);

		if ( ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) ) {
			unset( $adminpages['wpseo_files'] );
			$adminpages['wpseo_import']['function'] = '';
			unset( $adminpages['wpseo_import']['button2'] );
			$adminpages['wpseo_import']['content'] .= '<p>' . sprintf( __( 'The tour ends here,thank you for using my plugin and good luck with your SEO!<br/><br/>Best,<br/>', 'seo-rotator-for-images' ), '', '' ) . '</p>';
		}
		$page = '';
		if ( isset( $_GET['page'] ) )
			$page = $_GET['page'];

		$function = '';
		$button2  = '';
		$opt_arr  = array();
		$id       = '#wpseo-title';
		if ( 'admin.php' != $pagenow || ! array_key_exists( $page, $adminpages ) ) {
			$id      = 'li.toplevel_page_wpseo_dashboard';
			$content = '<h3>' . __( 'Congratulations!', 'seo-rotator-for-images' ) . '</h3>';
			$content .= '<p>' . __( 'You\'ve just installed Super SEO! Click "Start Tour" to view a quick introduction of this plugins core functionality.', 'seo-rotator-for-images' ) . '</p>';
			$opt_arr  = array(
				'content'  => $content,
				'position' => array( 'edge' => 'top', 'align' => 'center' )
			);
			$button2  = __( "Start Tour", 'seo-rotator-for-images' );
			$function = 'document.location="' . admin_url( 'admin.php?page=wpseo_dashboard' ) . '";';
		}
		else {
			if ( '' != $page && in_array( $page, array_keys( $adminpages ) ) ) {
				$align   = ( is_rtl() ) ? 'right' : 'left';
				$opt_arr = array(
					'content'      => $adminpages[$page]['content'],
					'position'     => array( 'edge' => 'top', 'align' => $align ),
					'pointerWidth' => 400
				);
				if ( isset( $adminpages[$page]['button2'] ) ) {
					$button2 = $adminpages[$page]['button2'];
				}
				if ( isset( $adminpages[$page]['function'] ) ) {
					$function = $adminpages[$page]['function'];
				}
			}
		}

		$this->print_scripts( $id, $opt_arr, __( "Close", 'seo-rotator-for-images' ), $button2, $function );
	}

	/**
	 * Load a tiny bit of CSS in the head
	 */
	function admin_head() {
		// Depreciated, marked for removal
		// No longer needed as the original code is now being handle by an external CSS files that supports RTL
		?>
		<style type="text/css" media="screen">
		</style>
	<?php
	}

	/**
	 * Prints the pointer script
	 *
	 * @param string      $selector         The CSS selector the pointer is attached to.
	 * @param array       $options          The options for the pointer.
	 * @param string      $button1          Text for button 1
	 * @param string|bool $button2          Text for button 2 (or false to not show it, defaults to false)
	 * @param string      $button2_function The JavaScript function to attach to button 2
	 * @param string      $button1_function The JavaScript function to attach to button 1
	 */
	function print_scripts( $selector, $options, $button1, $button2 = false, $button2_function = '', $button1_function = '' ) {
		?>
		<script type="text/javascript">
			//<![CDATA[
			(function ($) {
				var wpseo_pointer_options = <?php echo json_encode( $options ); ?>, setup;

				function wpseo_store_answer(input, nonce) {
					var wpseo_tracking_data = {
						action        : 'wpseo_allow_tracking',
						allow_tracking: input,
						nonce         : nonce
					}
					jQuery.post(ajaxurl, wpseo_tracking_data, function () {
						jQuery('#wp-pointer-0').remove();
					});
				}

				wpseo_pointer_options = $.extend(wpseo_pointer_options, {
					buttons: function (event, t) {
						button = jQuery('<a id="pointer-close" style="margin-left:5px" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
						button.bind('click.pointer', function () {
							t.element.pointer('close');
						});
						return button;
					},
					close  : function () {
					}
				});

				setup = function () {
					$('<?php echo $selector; ?>').pointer(wpseo_pointer_options).pointer('open');
					<?php if ( $button2 ) { ?>
					jQuery('#pointer-close').after('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
					jQuery('#pointer-primary').click(function () {
						<?php echo $button2_function; ?>
					});
					jQuery('#pointer-close').click(function () {
						<?php if ( $button1_function == '' ) { ?>
						wpseo_setIgnore("tour", "wp-pointer-0", "<?php echo wp_create_nonce( 'wpseo-ignore' ); ?>");
						<?php } else { ?>
						<?php echo $button1_function; ?>
						<?php } ?>
					});
					<?php } ?>
				};

				if (wpseo_pointer_options.position && wpseo_pointer_options.position.defer_loading)
					$(window).bind('load.wp-pointers', setup);
				else
					$(document).ready(setup);
			})(jQuery);
			//]]>
		</script>
	<?php
	}
}

$wpseo_pointers = new WPSEO_Pointers;
