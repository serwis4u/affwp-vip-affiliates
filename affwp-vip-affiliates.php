<?php
/**
 * Plugin Name: AffiliateWP - VIP Affiliates
 * Plugin URI: https://affiliatewp.com
 * Description: Adds affiliate meta to designate any affiliate as a "VIP". Also provides filterable content via the affiliate-vip-content shortcode, shown only to "VIP" affiliates.
 * Author: ramiabraham
 * Author URI: https://ramiabraham.com
 * Version: 1.0
 * Text Domain: affiliatewp-affiliate-vips
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AffWP_VIP_Affiliates' ) ) {

	final class AffWP_VIP_Affiliates {

		/**
		 * Class instance.
		 *
		 * @since 1.0
		 */
		private static $instance;

        /**
         * The plugin directory for this plugin.
         *
         * @var [type]
         */
		private static $plugin_dir;

        /**
         * The plugin version.
         *
         * @var [type]
         */
		private static $version;

		/**
		 * Debug variable.
		 *
		 * @var bool True if AffiliateWP core debug is active.
		 */
        public $debug;

		/**
		 * Logging class object.
		 *
		 * @access  public
		 * @since   1.0
		 * @var     Affiliate_WP_Logging
		 */
		public $logs;

		/**
		 * Main AffWP_VIP_Affiliates instance.
		 *
		 * Insures that only one instance of AffWP_VIP_Affiliates exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 * @static
		 * @staticvar array $instance
		 * @return The one true AffWP_VIP_Affiliates
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffWP_VIP_Affiliates ) ) {
				self::$instance = new AffWP_VIP_Affiliates;

				self::$plugin_dir = plugin_dir_path( __FILE__ );
				self::$version    = '1.0';

                self::$instance->setup_constants();
                self::$instance->includes();
				self::$instance->hooks();

			}

			return self::$instance;
		}

		/**
		 * Writes a log message.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param string $message Optional. Message to log. Default empty.
		 */
		public function log( $message = '' ) {

			if ( $this->debug ) {
				$this->logs->log( $message );
			}
		}

        /**
         * Sets up plugin constants.
         *
         * @access private
         * @since  1.0.0
         */
        private function setup_constants() {
            // Plugin version
            if ( ! defined( 'AFFWP_VIP_AFFILIATES_VERSION' ) ) {
                define( 'AFFWP_VIP_AFFILIATES_VERSION', self::$version );
            }

            // Plugin Folder Path
            if ( ! defined( 'AFFWP_VIP_AFFILIATES_PLUGIN_DIR' ) ) {
                define( 'AFFWP_VIP_AFFILIATES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL
            if ( ! defined( 'AFFWP_VIP_AFFILIATES_PLUGIN_URL' ) ) {
                define( 'AFFWP_VIP_AFFILIATES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

            // Plugin Root File
            if ( ! defined( 'AFFWP_VIP_AFFILIATES_PLUGIN_FILE' ) ) {
                define( 'AFFWP_VIP_AFFILIATES_PLUGIN_FILE', __FILE__ );
            }
        }

        /**
         * Includes necessary files.
         *
         * @access private
         * @since  1.0.0
         */
        private function includes() {

            if ( is_admin() ) {
                require_once AFFWP_VIP_AFFILIATES_PLUGIN_DIR . 'includes/class-settings.php';
            }
        }

		public function hooks() {

			$this->debug = (bool) affiliate_wp()->settings->get( 'debug_mode', false );

			if ( $this->debug ) {
				$this->logs = new Affiliate_WP_Logging;
			}

			// Register the affiliate-vip-content shortcode.
			add_shortcode( 'affiliate-vip-content', array( $this, 'shortcode' ) );
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-affiliate-vips' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-affiliate-vips' ), '1.0' );
		}

        /**
         * Returns whether affiliate vips functionality is enabled.
         *
         * @since  1.0
         *
         * @param  int     $affiliate_id The affiliate ID.
         *
         * @return boolean True if enabled.
         */
        public function is_enabled() {
            return (bool) affiliate_wp()->settings->get( 'affwp_vip_affiliates_enabled' );
        }

        /**
         * Checks whether the current affiliate is marked as an affiliate vip or not.
         *
         * @since  1.0
         *@param   int     $affiliate_id  The affiliate ID
         * @return boolean                True if the "Mark as affiliate vip" option is checked for the current affiliate account.
         */
        public function is_affiliate_vip( $affiliate_id ) {

            global $current_user;

            wp_get_current_user();

            $user_id = $current_user->ID;

            $affiliate_id = affwp_get_affiliate_id( $user_id );

            if ( ! $this->is_enabled() ) {

                return false;
            }

            $affiliate_vip = affwp_get_affiliate_meta( $affiliate_id, 'affiliate_vip', true );

            if ( 'yes' === $affiliate_vip ) {
                return true;
            } else {
                return false;
            }

            return false;
        }

		/**
		 * Register the `affiliate-vip-content` shortcode.
		 *
		 * @since  1.0
		 *
		 * @return mixed boolean|string  false|$content  Returns enclosed content if the currently logged-in user is marked as a VIP Affiliate.
		 */
		public function shortcode( $atts, $content = null ) {

            global $current_user;

            wp_get_current_user();

            $user_id = $current_user->ID;
            $affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );
            $affiliate_id = $affiliate->affiliate_id;

            if ( $this->is_affiliate_vip( $affiliate_id ) ) {

                /**
                 * Outputs the content enclosed within the affiliate-vip-content shortcode.
                 * Pass additional strings by appending or prepending to the `$content` var.
                 *
                 * @since 1.0
                 * @var   $content The shortcode content.
                 */
                $return_string = apply_filter( 'affwp_vip_affiliate_shortcode_content', $content );

                return $return_string;

            } else {
                return false;
            }

            return false;
		}

	}
}

/**
 * The main function responsible for returning the one true AffWP_VIP_Affiliates
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $affwp_vip_affiliates = affwp_vip_affiliates(); ?>
 *
 * @since 1.0
 * @return object The one true AffWP_VIP_Affiliates Instance
 */
function affwp_vip_affiliates() {
	if ( ! class_exists( 'Affiliate_WP' ) ) {
		if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
			return;
		}

		$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();
	} else {
		return AffWP_VIP_Affiliates::instance();
	}
}
add_action( 'plugins_loaded', 'affwp_vip_affiliates', 100 );
