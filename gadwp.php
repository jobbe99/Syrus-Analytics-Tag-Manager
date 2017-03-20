<?php
/**
 * Plugin Name: Syrus Analytics & Tag Manager
 * Description: Displays Google Analytics Reports and Real-Time Statistics in your Dashboard. Automatically inserts the tracking code in every page of your website. Also provide Google Tag Manager service in Settings -> General
 * Author: Syrus Industry
 * Version: 1.0
 * Author URI: http://www.syrusindustry.com
 * Text Domain: syrus-analytics-tag-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Manager' ) ) {

	final class GADWP_Manager {

		private static $instance = null;

		public $config = null;

		public $frontend_actions = null;

		public $common_actions = null;

		public $backend_actions = null;

		public $tracking = null;

		public $frontend_item_reports = null;

		public $backend_setup = null;

		public $frontend_setup = null;

		public $backend_widgets = null;

		public $backend_item_reports = null;

		public $gapi_controller = null;

		/**
		 * Construct forbidden
		 */
		private function __construct() {
			if ( null !== self::$instance ) {
				_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'syrus-analytics-tag-manager' ), '4.6' );
			}
		}

		/**
		 * Clone warning
		 */
		private function __clone() {
			_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'syrus-analytics-tag-manager' ), '4.6' );
		}

		/**
		 * Wakeup warning
		 */
		private function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'syrus-analytics-tag-manager' ), '4.6' );
		}

		/**
		 * Creates a single instance for GADWP and makes sure only one instance is present in memory.
		 *
		 * @return GADWP_Manager
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
				self::$instance->config = new GADWP_Config();
			}
			return self::$instance;
		}

		/**
		 * Defines constants and loads required resources
		 */
		private function setup() {

			// Plugin Path
			if ( ! defined( 'GADWP_DIR' ) ) {
				define( 'GADWP_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL
			if ( ! defined( 'GADWP_URL' ) ) {
				define( 'GADWP_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin main File
			if ( ! defined( 'GADWP_FILE' ) ) {
				define( 'GADWP_FILE', __FILE__ );
			}

			/*
			 * Load Tools class
			 */
			include_once ( GADWP_DIR . 'tools/tools.php' );

			/*
			 * Load Config class
			 */
			include_once ( GADWP_DIR . 'config.php' );

			/*
			 * Load GAPI Controller class
			 */
			include_once ( GADWP_DIR . 'tools/gapi.php' );

			/*
			 * Plugin i18n
			 */
			add_action( 'init', array( self::$instance, 'load_i18n' ) );

			/*
			 * Plugin Init
			 */
			add_action( 'init', array( self::$instance, 'load' ) );

			/*
			 * Include Install
			 */
			include_once ( GADWP_DIR . 'install/install.php' );
			register_activation_hook( GADWP_FILE, array( 'GADWP_Install', 'install' ) );

			/*
			 * Include Uninstall
			 */
			include_once ( GADWP_DIR . 'install/uninstall.php' );
			register_uninstall_hook( GADWP_FILE, array( 'GADWP_Uninstall', 'uninstall' ) );

			/*
			 * Load Frontend Widgets
			 * (needed during ajax)
			 */
			include_once ( GADWP_DIR . 'front/widgets.php' );

			/*
			 * Add Frontend Widgets
			 * (needed during ajax)
			 */
			add_action( 'widgets_init', array( self::$instance, 'add_frontend_widget' ) );
		}

		/**
		 * Load i18n
		 */
		public function load_i18n() {
			load_plugin_textdomain( 'syrus-analytics-tag-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Register Frontend Widgets
		 */
		public function add_frontend_widget() {
			register_widget( 'GADWP_Frontend_Widget' );
		}

		/**
		 * Conditional load
		 */
		public function load() {
			if ( is_admin() ) {
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
					if ( GADWP_Tools::check_roles( self::$instance->config->options['ga_dash_access_back'] ) ) {
						/*
						 * Load Backend ajax actions
						 */
						include_once ( GADWP_DIR . 'admin/ajax-actions.php' );
						self::$instance->backend_actions = new GADWP_Backend_Ajax();
					}

					/*
					 * Load Frontend ajax actions
					 */
					include_once ( GADWP_DIR . 'front/ajax-actions.php' );
					self::$instance->frontend_actions = new GADWP_Frontend_Ajax();

					/*
					 * Load Common ajax actions
					 */
					include_once ( GADWP_DIR . 'common/ajax-actions.php' );
					self::$instance->common_actions = new GADWP_Common_Ajax();
				} else if ( GADWP_Tools::check_roles( self::$instance->config->options['ga_dash_access_back'] ) ) {
					/*
					 * Load Backend Setup
					 */
					include_once ( GADWP_DIR . 'admin/setup.php' );
					self::$instance->backend_setup = new GADWP_Backend_Setup();

					if ( self::$instance->config->options['dashboard_widget'] ) {
						/*
						 * Load Backend Widget
						 */
						include_once ( GADWP_DIR . 'admin/widgets.php' );
						self::$instance->backend_widgets = new GADWP_Backend_Widgets();
					}

					if ( self::$instance->config->options['backend_item_reports'] ) {
						/*
						 * Load Backend Item Reports
						 */
						include_once ( GADWP_DIR . 'admin/item-reports.php' );
						self::$instance->backend_item_reports = new GADWP_Backend_Item_Reports();
					}
				}
			} else {
				if ( GADWP_Tools::check_roles( self::$instance->config->options['ga_dash_access_front'] ) ) {
					/*
					 * Load Frontend Setup
					 */
					include_once ( GADWP_DIR . 'front/setup.php' );
					self::$instance->frontend_setup = new GADWP_Frontend_Setup();

					if ( self::$instance->config->options['frontend_item_reports'] ) {
						/*
						 * Load Frontend Item Reports
						 */
						include_once ( GADWP_DIR . 'front/item-reports.php' );
						self::$instance->frontend_item_reports = new GADWP_Frontend_Item_Reports();
					}
				}

				if ( ! GADWP_Tools::check_roles( self::$instance->config->options['ga_track_exclude'], true ) && self::$instance->config->options['ga_dash_tracking'] ) {
					/*
					 * Load tracking class
					 */
					include_once ( GADWP_DIR . 'front/tracking.php' );
					self::$instance->tracking = new GADWP_Tracking();
				}
			}
		}
	}
}

class google_tag_manager {

    public static $printed_noscript_tag = false;

    public static function go() {
        add_filter( 'admin_init',     array( __CLASS__, 'register_fields' ) );
        add_action( 'wp_head',        array( __CLASS__, 'print_tag' ) );
        add_action( 'genesis_before', array( __CLASS__, 'print_noscript_tag' ) ); // Genesis
        add_action( 'tha_body_top',   array( __CLASS__, 'print_noscript_tag' ) ); // Theme Hook Alliance
        add_action( 'body_top',       array( __CLASS__, 'print_noscript_tag' ) ); // THA Unprefixed
        add_action( 'wp_footer',      array( __CLASS__, 'print_noscript_tag' ) ); // Fallback!
    }
    public static function register_fields() {
        register_setting( 'general', 'google_tag_manager_id', 'esc_attr' );
        add_settings_field( 'google_tag_manager_id', '<label for="google_tag_manager_id">' . __( 'Google Tag Manager ID' , 'google_tag_manager' ) . '</label>' , array( __CLASS__, 'fields_html') , 'general' );
    }
    public static function fields_html() {
        ?>
        <input type="text" id="google_tag_manager_id" name="google_tag_manager_id" placeholder="ABC-DEFG" class="regular-text code" value="<?php echo get_option( 'google_tag_manager_id', '' ); ?>" />
        <p class="description"><?php _e( 'L\' ID fornito dal codice di Google (marcato):', 'google_tag_manager' ); ?><br />
            <code>&lt;noscript&gt;&lt;iframe src="//www.googletagmanager.com/ns.html?id=<strong style="color:#c00;">ABC-DEFG</strong>"</code></p>
        <p class="description"><?php _e( 'Puoi riceverlo <a href="https://www.google.com/tagmanager/">qui</a>!', 'google_tag_manager' ); ?></p>
        <?php
    }
    public static function print_tag() {
        if ( ! $id = get_option( 'google_tag_manager_id', '' ) ) return;
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo esc_js( $id ); ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    }
    public static function print_noscript_tag() {
        // Make sure we only print the noscript tag once.
        // This is because we're trying for multiple hooks.
        if ( self::$printed_noscript_tag ) {
            return;
        }
        self::$printed_noscript_tag = true;

        if ( ! $id = get_option( 'google_tag_manager_id', '' ) ) return;
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $id ); ?>"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }
}

google_tag_manager::go();

/**
 * Returns a unique instance of GADWP
 */
function GADWP() {
	return GADWP_Manager::instance();
}

/*
 * Start GADWP
 */
GADWP();
