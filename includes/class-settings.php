<?php
// AffiliateWP VIP Affiliates settings.

/**
 * Implements a settings tab, as well as affiliate meta.
 *
 * @since 1.0
 */
class AffWP_VIP_Affiliates_Settings {

	/**
	 * Sets up the settings tab and saving operations.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function __construct() {

		// Settings tab and settings.
		add_filter( 'affwp_settings_tabs', array( $this, 'settings_tab' ) );
		add_filter( 'affwp_settings',      array( $this, 'register_settings' ) );

		// Add affiliate leaders meta.
		add_action( 'affwp_edit_affiliate_end', array( $this, 'edit_affiliate' ) );

		add_action( 'affwp_new_affiliate_end', array( $this, 'edit_affiliate' ) );

		add_action( 'affwp_insert_affiliate', array( $this, 'process_add_vip_affiliate_meta' ) );

		// Update new affiliate meta from the edit affiliate screen.
		add_action( 'affwp_update_affiliate', array( $this, 'update_setting' ), -1 );
	}

	/**
	 * Adds the 'VIP Affiliates' settings tab.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  array $tabs Settings tabs array.
	 * @return array       Modified settings tabs array.
	 */
	public function settings_tab( $tabs ) {
		$tabs['affwp_vip_affiliates'] = __( 'VIP Affiliates', 'affiliatewp-vip-affiliates' );

		return $tabs;
	}

	/**
	 * Registers settings.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  array $settings  AffiliateWP settings.
	 * @return array            AffiliateWP settings with plugin settings registered.
	 */
	public function register_settings( $settings ) {
		$settings[ 'affwp_vip_affiliates' ] = array(
			'affwp_vip_affiliates_header' => array(
				'name' => '<strong>' . __( 'VIP Affiliates', 'affiliatewp-vip-affiliates' ) . '</strong>',
				'type' => 'header'
			),
			'affwp_vip_affiliates_enabled' => array(
				'name' => __( 'Enable affiliate leaders', 'affiliatewp-vip-affiliates' ),
				'desc' => __( 'Check this option to enable affiliate leaders.', 'affiliatewp-vip-affiliates' ),
				'type' => 'checkbox'
			)
		);

		return $settings;
	}

	/**
	 * Checkbox callback.
	 *
	 * Renders checkboxes.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of Options
	 * @return void
	 */
	function checkbox_callback( $args ) {

		$checked  = isset( $this->options[ $args['id'] ] ) ? checked( 1, $this->options[ $args['id'] ], false) : '';
		$disabled = $this->is_setting_disabled( $args ) ? disabled( $args['disabled'], true, false ) : '';

		$html = '<label for="affwp_settings[' . $args['id'] . ']">';
		$html .= '<input type="checkbox" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']" value="1" ' . $checked . ' ' . $disabled . '/>&nbsp;';
		$html .= $args['desc'];
		$html .= '</label>';

		echo $html;
	}

	/**
	 * Edit Affiliate screen options
	 *
	 * @access public
	 * @since 1.0
	 */
	public function edit_affiliate( $affiliate ) {

		$enabled = affiliate_wp()->settings->get( 'affwp_vip_affiliates_enabled' );

		if ( ! $enabled ) {
			return;
		}

		$affiliate_id = $affiliate->affiliate_id;

		$vip_affiliate = affwp_get_affiliate_meta( $affiliate_id, 'vip_affiliate', true );

		?>

		<tr class="form-row">
			<th scope="row">
				<label for="vip_affiliate"><?php _e( 'Make this account a VIP affiliate.', 'affiliatewp-vip-affiliates' ); ?></label>
			</th>
			<td>
					<select name="vip_affiliate" id="vip_affiliate">

						<?php if ( 'yes' === $vip_affiliate ) { ?>
							<option value="yes" <?php selected( $vip_affiliate, 'yes' ); ?>><?php _e( 'Yes', 'affiliatewp-vip-affiliates' ); ?></option>
							<option value="no"><?php _e( 'No', 'affiliatewp-vip-affiliates' ); ?></option>
						<?php } else { ?>

						<option value="yes"><?php _e( 'Yes', 'affiliatewp-vip-affiliates' ); ?></option>
						<option value="no" <?php selected( $vip_affiliate, 'no' ); ?>><?php _e( 'No', 'affiliatewp-vip-affiliates' ); ?></option>
						<?php } ?>
					</select>
					<p class="description"><?php _e( 'Select whether or not this affiliate should be a VIP.', 'affiliatewp-vip-affiliates' ); ?></p>
				</td>
		</tr>

		<?php
	}

	/**
	 * Update slug from the edit affiliate screen
	 *
	 * @since 1.0
	 */
	public function update_setting( $data ) {

		if ( ! is_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			wp_die( __( 'You do not have permission to manage affiliates', 'affiliatewp-vip-affiliates' ), __( 'Error', 'affiliatewp-vip-affiliates' ), array( 'response' => 403 ) );
		}

		// field is not empty
		if ( ! empty( $_POST['vip_affiliate'] ) ) {

			affwp_update_affiliate_meta( $data['affiliate_id'], 'vip_affiliate', $data['vip_affiliate'] );
		}

	}

	/**
	 * Save the custom affiliate meta
	 *
	 * @since 1.0
	 */
	public function process_add_vip_affiliate_meta( $affiliate_id = 0 ) {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			wp_die( __( 'You do not have permission to manage affiliates', 'affiliatewp-vip-affiliates' ), __( 'Error', 'affiliatewp-vip-affiliates' ), array( 'response' => 403 ) );
		}

		// field is not empty
		if ( ! empty( $_POST['vip_affiliate'] ) ) {

			affwp_update_affiliate_meta( $data['affiliate_id'], 'vip_affiliate', $data['vip_affiliate'] );
		}

	}


}

new AffWP_VIP_Affiliates_Settings;
