<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Backend_Setup' ) ) {

	final class GADWP_Backend_Setup {

		private $gadwp;

		public function __construct() {
			$this->gadwp = GADWP();

			// Styles & Scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			// Site Menu
			add_action( 'admin_menu', array( $this, 'site_menu' ) );
			// Network Menu
			add_action( 'network_admin_menu', array( $this, 'network_menu' ) );
			// Settings link
			add_filter( "plugin_action_links_" . plugin_basename( GADWP_DIR . 'gadwp.php' ), array( $this, 'settings_link' ) );
			// Updated admin notice
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

		/**
		 * Add Site Menu
		 */
		public function site_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_options' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'syrus-analytics-tag-manager' ), __( "Google Analytics", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "Impostazioni Generali", 'syrus-analytics-tag-manager' ), __( "Impostazioni Generali", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Impostazioni Backend", 'syrus-analytics-tag-manager' ), __( "Impostazioni Backend", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_backend_settings', array( 'GADWP_Settings', 'backend_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Impostazioni Frontend", 'syrus-analytics-tag-manager' ), __( "Impostazioni Frontend", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_frontend_settings', array( 'GADWP_Settings', 'frontend_settings' ) );
				//add_submenu_page( 'gadash_settings', __( "Tracking Code", 'syrus-analytics-tag-manager' ), __( "Tracking Code", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_tracking_settings', array( 'GADWP_Settings', 'tracking_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Errori & Debug", 'syrus-analytics-tag-manager' ), __( "Errori & Debug", 'syrus-analytics-tag-manager' ), 'manage_options', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Add Network Menu
		 */
		public function network_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_netwrok' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'syrus-analytics-tag-manager' ), "Google Analytics", 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "General Settings", 'syrus-analytics-tag-manager' ), __( "General Settings", 'syrus-analytics-tag-manager' ), 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ) );
				add_submenu_page( 'gadash_settings', __( "Errors & Debug", 'syrus-analytics-tag-manager' ), __( "Errors & Debug", 'syrus-analytics-tag-manager' ), 'manage_network', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Styles & Scripts conditional loading (based on current URI)
		 *
		 * @param
		 *            $hook
		 */
		public function load_styles_scripts( $hook ) {
			$new_hook = explode( '_page_', $hook );

			if ( isset( $new_hook[1] ) ) {
				$new_hook = '_page_' . $new_hook[1];
			} else {
				$new_hook = $hook;
			}

			/*
			 * GADWP main stylesheet
			 */
			wp_enqueue_style( 'gadwp', GADWP_URL . 'admin/css/gadwp.css', null, GADWP_CURRENT_VERSION );

			/*
			 * GADWP UI
			 */

			if ( GADWP_Tools::get_cache( 'gapi_errors' ) ) {
				$ed_bubble = '!';
			} else {
				$ed_bubble = '';
			}

			wp_enqueue_script( 'gadwp-backend-ui', plugins_url( 'js/ui.js', __FILE__ ), array( 'jquery' ), GADWP_CURRENT_VERSION, true );

			/* @formatter:off */
			wp_localize_script( 'gadwp-backend-ui', 'gadwp_ui_data', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'gadwp_dismiss_notices' ),
				'ed_bubble' => $ed_bubble,
			)
			);
			/* @formatter:on */

			if ( $this->gadwp->config->options['switch_profile'] && count( $this->gadwp->config->options['ga_dash_profile_list'] ) > 1 ) {
				$views = array();
				foreach ( $this->gadwp->config->options['ga_dash_profile_list'] as $items ) {
					if ( $items[3] ) {
						$views[$items[1]] = esc_js( GADWP_Tools::strip_protocol( $items[3] ) ); // . ' &#8658; ' . $items[0] );
					}
				}
			} else {
				$views = false;
			}

			/*
			 * Main Dashboard Widgets Styles & Scripts
			 */
			$widgets_hooks = array( 'index.php' );

			if ( in_array( $new_hook, $widgets_hooks ) ) {
				if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && $this->gadwp->config->options['dashboard_widget'] ) {

					if ( $this->gadwp->config->options['ga_target_geomap'] ) {
						$country_codes = GADWP_Tools::get_countrycodes();
						if ( isset( $country_codes[$this->gadwp->config->options['ga_target_geomap']] ) ) {
							$region = $this->gadwp->config->options['ga_target_geomap'];
						} else {
							$region = false;
						}
					} else {
						$region = false;
					}

					wp_enqueue_style( 'gadwp-nprogress', GADWP_URL . 'common/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION );

					wp_enqueue_style( 'gadwp-backend-item-reports', GADWP_URL . 'admin/css/admin-widgets.css', null, GADWP_CURRENT_VERSION );

					wp_register_style( 'jquery-ui-tooltip-html', GADWP_URL . 'common/realtime/jquery.ui.tooltip.html.css' );

					wp_enqueue_style( 'jquery-ui-tooltip-html' );

					wp_register_script( 'jquery-ui-tooltip-html', GADWP_URL . 'common/realtime/jquery.ui.tooltip.html.js' );

					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );

					wp_enqueue_script( 'gadwp-nprogress', GADWP_URL . 'common/nprogress/nprogress.js', array( 'jquery' ), GADWP_CURRENT_VERSION );

					wp_enqueue_script( 'gadwp-backend-dashboard-reports', GADWP_URL . 'common/js/reports.js', array( 'jquery', 'googlecharts', 'gadwp-nprogress', 'jquery-ui-tooltip', 'jquery-ui-core', 'jquery-ui-position', 'jquery-ui-tooltip-html' ), GADWP_CURRENT_VERSION, true );

					/* @formatter:off */
					wp_localize_script( 'gadwp-backend-dashboard-reports', 'gadwpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'gadwp_backend_item_reports' ),
						'dateList' => array(
							'realtime' => __( "Real-Time", 'syrus-analytics-tag-manager' ),
							'today' => __( "Today", 'syrus-analytics-tag-manager' ),
							'yesterday' => __( "Yesterday", 'syrus-analytics-tag-manager' ),
							'7daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 7 ),
							'14daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 14 ),
							'30daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 30 ),
							'90daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 90 ),
							'365daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 1, 'syrus-analytics-tag-manager' ), __('One', 'syrus-analytics-tag-manager') ),
							'1095daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 3, 'syrus-analytics-tag-manager' ), __('Three', 'syrus-analytics-tag-manager') ),
						),
						'reportList' => array(
							'sessions' => __( "Sessions", 'syrus-analytics-tag-manager' ),
							'users' => __( "Users", 'syrus-analytics-tag-manager' ),
							'organicSearches' => __( "Organic", 'syrus-analytics-tag-manager' ),
							'pageviews' => __( "Page Views", 'syrus-analytics-tag-manager' ),
							'visitBounceRate' => __( "Bounce Rate", 'syrus-analytics-tag-manager' ),
							'locations' => __( "Location", 'syrus-analytics-tag-manager' ),
							'contentpages' =>  __( "Pages", 'syrus-analytics-tag-manager' ),
							'referrers' => __( "Referrers", 'syrus-analytics-tag-manager' ),
							'searches' => __( "Searches", 'syrus-analytics-tag-manager' ),
							'trafficdetails' => __( "Traffic", 'syrus-analytics-tag-manager' ),
							'technologydetails' => __( "Technology", 'syrus-analytics-tag-manager' ),
							'404errors' => __( "404 Errors", 'syrus-analytics-tag-manager' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'syrus-analytics-tag-manager' ), //0
							__( "Traffic Mediums", 'syrus-analytics-tag-manager' ),
							__( "Visitor Type", 'syrus-analytics-tag-manager' ),
							__( "Search Engines", 'syrus-analytics-tag-manager' ),
							__( "Social Networks", 'syrus-analytics-tag-manager' ),
							__( "Sessions", 'syrus-analytics-tag-manager' ),
							__( "Users", 'syrus-analytics-tag-manager' ),
							__( "Page Views", 'syrus-analytics-tag-manager' ),
							__( "Bounce Rate", 'syrus-analytics-tag-manager' ),
							__( "Organic Search", 'syrus-analytics-tag-manager' ),
							__( "Pages/Session", 'syrus-analytics-tag-manager' ),
							__( "Invalid response", 'syrus-analytics-tag-manager' ),
							__( "Not enough data collected", 'syrus-analytics-tag-manager' ),
							__( "This report is unavailable", 'syrus-analytics-tag-manager' ),
							__( "report generated by", 'syrus-analytics-tag-manager' ), //14
							__( "This plugin needs an authorization:", 'syrus-analytics-tag-manager' ) . ' <a href="' . menu_page_url( 'gadash_settings', false ) . '">' . __( "authorize the plugin", 'syrus-analytics-tag-manager' ) . '</a>.',
							__( "Browser", 'syrus-analytics-tag-manager' ), //16
							__( "Operating System", 'syrus-analytics-tag-manager' ),
							__( "Screen Resolution", 'syrus-analytics-tag-manager' ),
							__( "Mobile Brand", 'syrus-analytics-tag-manager' ),
							__( "REFERRALS", 'syrus-analytics-tag-manager' ), //20
							__( "KEYWORDS", 'syrus-analytics-tag-manager' ),
							__( "SOCIAL", 'syrus-analytics-tag-manager' ),
							__( "CAMPAIGN", 'syrus-analytics-tag-manager' ),
							__( "DIRECT", 'syrus-analytics-tag-manager' ),
							__( "NEW", 'syrus-analytics-tag-manager' ), //25
							__( "Time on Page", 'syrus-analytics-tag-manager' ),
							__( "Page Load Time", 'syrus-analytics-tag-manager' ),
							__( "Session Duration", 'syrus-analytics-tag-manager' ),
						),
						'rtLimitPages' => $this->gadwp->config->options['ga_realtime_pages'],
						'colorVariations' => GADWP_Tools::variations( $this->gadwp->config->options['ga_dash_style'] ),
						'region' => $region,
						'mapsApiKey' => $this->gadwp->config->options['maps_api_key'],
						'language' => get_bloginfo( 'language' ),
						'viewList' => $views,
						'scope' => 'admin-widgets',
					)
					);
					/* @formatter:on */
				}
			}

			/*
			 * Posts/Pages List Styles & Scripts
			 */
			$contentstats_hooks = array( 'edit.php' );
			if ( in_array( $hook, $contentstats_hooks ) ) {
				if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && $this->gadwp->config->options['backend_item_reports'] ) {

					if ( $this->gadwp->config->options['ga_target_geomap'] ) {
						$country_codes = GADWP_Tools::get_countrycodes();
						if ( isset( $country_codes[$this->gadwp->config->options['ga_target_geomap']] ) ) {
							$region = $this->gadwp->config->options['ga_target_geomap'];
						} else {
							$region = false;
						}
					} else {
						$region = false;
					}

					wp_enqueue_style( 'gadwp-nprogress', GADWP_URL . 'common/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION );

					wp_enqueue_style( 'gadwp-backend-item-reports', GADWP_URL . 'admin/css/item-reports.css', null, GADWP_CURRENT_VERSION );

					wp_enqueue_style( "wp-jquery-ui-dialog" );

					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );

					wp_enqueue_script( 'gadwp-nprogress', GADWP_URL . 'common/nprogress/nprogress.js', array( 'jquery' ), GADWP_CURRENT_VERSION );

					wp_enqueue_script( 'gadwp-backend-item-reports', GADWP_URL . 'common/js/reports.js', array( 'gadwp-nprogress', 'googlecharts', 'jquery', 'jquery-ui-dialog' ), GADWP_CURRENT_VERSION, true );

					/* @formatter:off */
					wp_localize_script( 'gadwp-backend-item-reports', 'gadwpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'gadwp_backend_item_reports' ),
						'dateList' => array(
							'today' => __( "Today", 'syrus-analytics-tag-manager' ),
							'yesterday' => __( "Yesterday", 'syrus-analytics-tag-manager' ),
							'7daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 7 ),
							'14daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 14 ),
							'30daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 30 ),
							'90daysAgo' => sprintf( __( "Last %d Days", 'syrus-analytics-tag-manager' ), 90 ),
							'365daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 1, 'syrus-analytics-tag-manager' ), __('One', 'syrus-analytics-tag-manager') ),
							'1095daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 3, 'syrus-analytics-tag-manager' ), __('Three', 'syrus-analytics-tag-manager') ),
						),
						'reportList' => array(
							'uniquePageviews' => __( "Unique Views", 'syrus-analytics-tag-manager' ),
							'users' => __( "Users", 'syrus-analytics-tag-manager' ),
							'organicSearches' => __( "Organic", 'syrus-analytics-tag-manager' ),
							'pageviews' => __( "Page Views", 'syrus-analytics-tag-manager' ),
							'visitBounceRate' => __( "Bounce Rate", 'syrus-analytics-tag-manager' ),
							'locations' => __( "Location", 'syrus-analytics-tag-manager' ),
							'referrers' => __( "Referrers", 'syrus-analytics-tag-manager' ),
							'searches' => __( "Searches", 'syrus-analytics-tag-manager' ),
							'trafficdetails' => __( "Traffic", 'syrus-analytics-tag-manager' ),
							'technologydetails' => __( "Technology", 'syrus-analytics-tag-manager' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'syrus-analytics-tag-manager' ), //0
							__( "Traffic Mediums", 'syrus-analytics-tag-manager' ),
							__( "Visitor Type", 'syrus-analytics-tag-manager' ),
							__( "Social Networks", 'syrus-analytics-tag-manager' ),
							__( "Search Engines", 'syrus-analytics-tag-manager' ),
							__( "Unique Views", 'syrus-analytics-tag-manager' ),
							__( "Users", 'syrus-analytics-tag-manager' ),
							__( "Page Views", 'syrus-analytics-tag-manager' ),
							__( "Bounce Rate", 'syrus-analytics-tag-manager' ),
							__( "Organic Search", 'syrus-analytics-tag-manager' ),
							__( "Pages/Session", 'syrus-analytics-tag-manager' ),
							__( "Invalid response", 'syrus-analytics-tag-manager' ),
							__( "Not enough data collected", 'syrus-analytics-tag-manager' ),
							__( "This report is unavailable", 'syrus-analytics-tag-manager' ),
							__( "report generated by", 'syrus-analytics-tag-manager' ), //14
							__( "This plugin needs an authorization:", 'syrus-analytics-tag-manager' ) . ' <a href="' . menu_page_url( 'gadash_settings', false ) . '">' . __( "authorize the plugin", 'syrus-analytics-tag-manager' ) . '</a>.',
							__( "Browser", 'syrus-analytics-tag-manager' ), //16
							__( "Operating System", 'syrus-analytics-tag-manager' ),
							__( "Screen Resolution", 'syrus-analytics-tag-manager' ),
							__( "Mobile Brand", 'syrus-analytics-tag-manager' ), //19
							__( "Future Use", 'syrus-analytics-tag-manager' ),
							__( "Future Use", 'syrus-analytics-tag-manager' ),
							__( "Future Use", 'syrus-analytics-tag-manager' ),
							__( "Future Use", 'syrus-analytics-tag-manager' ),
							__( "Future Use", 'syrus-analytics-tag-manager' ),
							__( "Future Use", 'syrus-analytics-tag-manager' ), //25
							__( "Time on Page", 'syrus-analytics-tag-manager' ),
							__( "Page Load Time", 'syrus-analytics-tag-manager' ),
							__( "Exit Rate", 'syrus-analytics-tag-manager' ),
						),
						'colorVariations' => GADWP_Tools::variations( $this->gadwp->config->options['ga_dash_style'] ),
						'region' => $region,
						'mapsApiKey' => $this->gadwp->config->options['maps_api_key'],
						'language' => get_bloginfo( 'language' ),
						'viewList' => false,
						'scope' => 'admin-item',
						)
					);
					/* @formatter:on */
				}
			}

			/*
			 * Settings Styles & Scripts
			 */
			$settings_hooks = array( '_page_gadash_settings', '_page_gadash_backend_settings', '_page_gadash_frontend_settings', '_page_gadash_tracking_settings', '_page_gadash_errors_debugging' );

			if ( in_array( $new_hook, $settings_hooks ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-script-handle', plugins_url( 'js/wp-color-picker-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
				wp_enqueue_script( 'gadwp-settings', plugins_url( 'js/settings.js', __FILE__ ), array( 'jquery' ), GADWP_CURRENT_VERSION, true );
			}
		}

		/**
		 * Add "Settings" link in Plugins List
		 *
		 * @param
		 *            $links
		 * @return array
		 */
		public function settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ) . '">' . __( "Settings", 'syrus-analytics-tag-manager' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 *  Add an admin notice after a manual or atuomatic update
		 */
		function admin_notice() {
			$currentScreen = get_current_screen();

			if ( ! current_user_can( 'manage_options' ) || $currentScreen->base != 'dashboard' ) {
				return;
			}

			if ( get_option( 'gadwp_got_updated' ) ) :
				?>
<div id="gadwp-notice" class="notice is-dismissible">
    <p><?php echo sprintf( __('Google Analytics Dashboard for WP has been updated to version %s.', 'syrus-analytics-tag-manager' ), GADWP_CURRENT_VERSION).' '.sprintf( __('For details, check out %1$s and %2$s.', 'syrus-analytics-tag-manager' ), sprintf(' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_notice&utm_medium=link&utm_content=release_notice&utm_campaign=gadwp">%s</a> ', __('the documentation page', 'syrus-analytics-tag-manager') ), sprintf(' <a href="%1$s">%2$s</a>', esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ), __('the plugin&#39;s settings page', 'syrus-analytics-tag-manager') ) ); ?></p>
</div>

			<?php
			endif;
		}
	}
}
