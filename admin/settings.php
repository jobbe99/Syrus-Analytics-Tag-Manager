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

final class GADWP_Settings {

	private static function update_options( $who ) {
		$gadwp = GADWP();
		$network_settings = false;
		$options = $gadwp->config->options; // Get current options
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && isset( $_POST['options'] ) && ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) && $who != 'Reset' ) {
			$new_options = $_POST['options'];
			if ( $who == 'tracking' ) {
				$options['ga_dash_anonim'] = 0;
				$options['ga_event_tracking'] = 0;
				$options['ga_enhanced_links'] = 0;
				$options['ga_dash_remarketing'] = 0;
				$options['ga_dash_adsense'] = 0;
				$options['ga_event_bouncerate'] = 0;
				$options['ga_crossdomain_tracking'] = 0;
				$options['ga_aff_tracking'] = 0;
				$options['ga_hash_tracking'] = 0;
				if ( isset( $_POST['options']['ga_tracking_code'] ) ) {
					$new_options['ga_tracking_code'] = trim( $new_options['ga_tracking_code'], "\t" );
				}
				if ( empty( $new_options['ga_track_exclude'] ) ) {
					$new_options['ga_track_exclude'] = array();
				}
			} else if ( $who == 'backend' ) {
				$options['switch_profile'] = 0;
				$options['backend_item_reports'] = 0;
				$options['dashboard_widget'] = 0;
				if ( empty( $new_options['ga_dash_access_back'] ) ) {
					$new_options['ga_dash_access_back'][] = 'administrator';
				}
			} else if ( $who == 'frontend' ) {
				$options['frontend_item_reports'] = 0;
				if ( empty( $new_options['ga_dash_access_front'] ) ) {
					$new_options['ga_dash_access_front'][] = 'administrator';
				}
			} else if ( $who == 'general' ) {
				$options['ga_dash_userapi'] = 0;
				if ( ! is_multisite() ) {
					$options['automatic_updates_minorversion'] = 0;
				}
			} else if ( $who == 'network' ) {
				$options['ga_dash_userapi'] = 0;
				$options['ga_dash_network'] = 0;
				$options['ga_dash_excludesa'] = 0;
				$options['automatic_updates_minorversion'] = 0;
				$network_settings = true;
			}
			$options = array_merge( $options, $new_options );
			$gadwp->config->options = $options;
			$gadwp->config->set_plugin_options( $network_settings );
		}
		return $options;
	}

	private static function navigation_tabs( $tabs ) {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab' id='tab-$tab' href='#top#gadwp-$tab'>$name</a>";
		}
		echo '</h2>';
	}

	public static function frontend_settings() {
		$gadwp = GADWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'frontend' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated'><p>" . __( "Settings saved.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Frontend Settings", 'syrus-analytics-tag-manager' ) . "</h2>"; ?><hr>
    </div>
    <div id="poststuff" class="gadwp">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="gadwp-settings-options">
                            <tr>
                                <td colspan="2"><?php echo "<h2>" . __( "Permessi", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td class="roles gadwp-settings-title"><label for="ga_dash_access_front"><?php _e("Mostra statistiche a:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                <td class="gadwp-settings-roles">
                               		<?php
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$i = 0;
		?>
                                <table>
                                        <tr>
		<?php
		foreach ( $wp_roles->role_names as $role => $name ) {
			if ( $role != 'subscriber' ) {
				$i++;
				?>
		        <td><label> <input type="checkbox" name="options[ga_dash_access_front][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_dash_access_front']) || $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> /><?php echo $name; ?>
		          </label></td>
		                                    <?php
			}
			if ( $i % 4 == 0 ) {
				?>
                                        </tr>
                                        <tr>
                <?php
			}
		}
		?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
                                    <div class="button-primary gadwp-settings-switchoo">
                                        <input type="checkbox" name="options[frontend_item_reports]" value="1" class="gadwp-settings-switchoo-checkbox" id="frontend_item_reports" <?php checked( $options['frontend_item_reports'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="frontend_item_reports">
                                            <div class="gadwp-settings-switchoo-inner"></div>
                                            <div class="gadwp-settings-switchoo-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php echo " ".__("abilita i report delle pagine web sul frontend", 'syrus-analytics-tag-manager' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'syrus-analytics-tag-manager' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security');?>
</form>
<?php
		self::output_sidebar();
	}

	public static function backend_settings() {
		$gadwp = GADWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'backend' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated'><p>" . __( "Settings saved.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Impostazioni Backend", 'syrus-analytics-tag-manager' ) . "</h2>"; ?><hr>
    </div>
    <div id="poststuff" class="gadwp">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="gadwp-settings-options">
                            <tr>
                                <td colspan="2"><?php echo "<h2>" . __( "Permessi", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td class="roles gadwp-settings-title"><label for="ga_dash_access_back"><?php _e("Mostra statistiche a:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                <td class="gadwp-settings-roles">
									<?php
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$i = 0;
		?>
									<table>
                                        <tr>
        <?php

		foreach ( $wp_roles->role_names as $role => $name ) {
			if ( $role != 'subscriber' ) {
				$i++;
				?>
                                        	<td><label> <input type="checkbox" name="options[ga_dash_access_back][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_dash_access_back']) || $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> />
                                            	<?php echo $name; ?>
                            				</label></td>
                <?php
			}
			if ( $i % 4 == 0 ) {
				?>
                                        </tr>
                                        <tr>
                                    		<?php
			}
		}
		?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
                                    <div class="button-primary gadwp-settings-switchoo">
                                        <input type="checkbox" name="options[switch_profile]" value="1" class="gadwp-settings-switchoo-checkbox" id="switch_profile" <?php checked( $options['switch_profile'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="switch_profile">
                                            <div class="gadwp-settings-switchoo-inner"></div>
                                            <div class="gadwp-settings-switchoo-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "abilita funzionalità di Switch View", 'syrus-analytics-tag-manager' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
                                    <div class="button-primary gadwp-settings-switchoo">
                                        <input type="checkbox" name="options[backend_item_reports]" value="1" class="gadwp-settings-switchoo-checkbox" id="backend_item_reports" <?php checked( $options['backend_item_reports'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="backend_item_reports">
                                            <div class="gadwp-settings-switchoo-inner"></div>
                                            <div class="gadwp-settings-switchoo-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "abilita i repost sulla Lista dei Post e la Lista delle Pagine", 'syrus-analytics-tag-manager' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
                                    <div class="button-primary gadwp-settings-switchoo">
                                        <input type="checkbox" name="options[dashboard_widget]" value="1" class="gadwp-settings-switchoo-checkbox" id="dashboard_widget" <?php checked( $options['dashboard_widget'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="dashboard_widget">
                                            <div class="gadwp-settings-switchoo-inner"></div>
                                            <div class="gadwp-settings-switchoo-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "abilita il Widget sulla Dashboard principale", 'syrus-analytics-tag-manager' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Impostazioni Real-Time", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title"> <?php _e("Numero massimo di pagine da mostrare sulla real-time tab:", 'syrus-analytics-tag-manager'); ?>
    								<input type="number" name="options[ga_realtime_pages]" id="ga_realtime_pages" value="<?php echo (int)$options['ga_realtime_pages']; ?>" size="3">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Impostazioni locazione", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
									<?php echo __("Geo Map paese target:", 'syrus-analytics-tag-manager'); ?>
									<input type="text" style="text-align: center;" name="options[ga_target_geomap]" value="<?php echo esc_attr($options['ga_target_geomap']); ?>" size="3">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
									<?php echo __("Maps API Key:", 'syrus-analytics-tag-manager'); ?>
									<input type="text" style="text-align: center;" name="options[maps_api_key]" value="<?php echo esc_attr($options['maps_api_key']); ?>" size="50">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "404 Report Errore", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="gadwp-settings-title">
									<?php echo __("404 Messaggio di pagina:", 'syrus-analytics-tag-manager'); ?>
									<input type="text" style="text-align: center;" name="options[pagetitle_404]" value="<?php echo esc_attr($options['pagetitle_404']); ?>" size="20">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'syrus-analytics-tag-manager' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>

</form>
<?php
		self::output_sidebar();
	}

	public static function tracking_settings() {
		$gadwp = GADWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'tracking' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated'><p>" . __( "Settings saved.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		if ( ! $options['ga_dash_tracking'] ) {
			$message = "<div class='error'><p>" . __( "The tracking component is disabled. You should set <strong>Tracking Options</strong> to <strong>Enabled</strong>", 'syrus-analytics-tag-manager' ) . ".</p></div>";
		}
		?>
<form name="ga_dash_form" method="post" action="<?php  esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Tracking Code", 'syrus-analytics-tag-manager' ) . "</h2>"; ?>
	</div>
    <div id="poststuff" class="gadwp">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
                    <?php
		$tabs = array( 'basic' => __( "Basic Settings", 'syrus-analytics-tag-manager' ), 'events' => __( "Events Tracking", 'syrus-analytics-tag-manager' ), 'custom' => __( "Custom Definitions", 'syrus-analytics-tag-manager' ), 'exclude' => __( "Exclude Tracking", 'syrus-analytics-tag-manager' ), 'advanced' => __( "Advanced Settings", 'syrus-analytics-tag-manager' ) );
		self::navigation_tabs( $tabs );
		if ( isset( $message ) )
			echo $message;
		?>
					    <div id="gadwp-basic">
                            <table class="gadwp-settings-options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Tracking Settings", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_dash_tracking"><?php _e("Tracking Options:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_dash_tracking" name="options[ga_dash_tracking]" onchange="this.form.submit()">
                                            <option value="0" <?php selected( $options['ga_dash_tracking'], 0 ); ?>><?php _e("Disabled", 'syrus-analytics-tag-manager');?></option>
                                            <option value="1" <?php selected( $options['ga_dash_tracking'], 1 ); ?>><?php _e("Enabled", 'syrus-analytics-tag-manager');?></option>
                                    </select></td>
                                </tr>
								<?php if ($options['ga_dash_tracking']) {?>
								<tr>
                                    <td class="gadwp-settings-title"></td>
                                    <td>
                                        <?php $profile_info = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']); ?>
                                        <?php echo '<pre>' . __("Nome vista:", 'syrus-analytics-tag-manager') . "\t" . esc_html($profile_info[0]) . "<br />" . __("Tracking ID:", 'syrus-analytics-tag-manager') . "\t" . esc_html($profile_info[2]) . "<br />" . __("Default URL:", 'syrus-analytics-tag-manager') . "\t" . esc_html($profile_info[3]) . "<br />" . __("Time Zone:", 'syrus-analytics-tag-manager') . "\t" . esc_html($profile_info[5]) . '</pre>';?>
                                    </td>
                                </tr>
								<?php }?>
								<tr>
                                    <td colspan="2"><hr><?php echo "<h2>" . __( "Basic Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_dash_tracking_type"><?php _e("Tracking Type:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_dash_tracking_type" name="options[ga_dash_tracking_type]">
                                            <option value="classic" <?php selected( $options['ga_dash_tracking_type'], 'classic' ); ?>><?php _e("Classic Analytics", 'syrus-analytics-tag-manager');?></option>
                                            <option value="universal" <?php selected( $options['ga_dash_tracking_type'], 'universal' ); ?>><?php _e("Universal Analytics", 'syrus-analytics-tag-manager');?></option>
                                    </select></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_dash_anonim]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_anonim" <?php checked( $options['ga_dash_anonim'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_dash_anonim">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("anonymize IPs while tracking", 'syrus-analytics-tag-manager' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_dash_remarketing]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_remarketing" <?php checked( $options['ga_dash_remarketing'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_dash_remarketing">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable remarketing, demographics and interests reports", 'syrus-analytics-tag-manager' );?></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-events">
                            <table class="gadwp-settings-options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Events Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_event_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_event_tracking" <?php checked( $options['ga_event_tracking'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_event_tracking">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track downloads, mailto and outbound links", 'syrus-analytics-tag-manager' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_event_downloads"><?php _e("Downloads Regex:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_event_downloads" name="options[ga_event_downloads]" value="<?php echo esc_attr($options['ga_event_downloads']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_aff_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_aff_tracking" <?php checked( $options['ga_aff_tracking'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_aff_tracking">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track affiliate links matching this regex", 'syrus-analytics-tag-manager' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_event_affiliates"><?php _e("Affiliates Regex:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_event_affiliates" name="options[ga_event_affiliates]" value="<?php echo esc_attr($options['ga_event_affiliates']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_hash_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_hash_tracking" <?php checked( $options['ga_hash_tracking'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_hash_tracking">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track fragment identifiers, hashmarks (#) in URI links", 'syrus-analytics-tag-manager' ); ?></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-custom">
                            <table class="gadwp-settings-options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Custom Definitions", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_author_dimindex"><?php _e("Authors:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_author_dimindex" name="options[ga_author_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_author_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_pubyear_dimindex"><?php _e("Publication Year:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_pubyear_dimindex" name="options[ga_pubyear_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_pubyear_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
								<tr>
                                    <td class="gadwp-settings-title"><label for="ga_pubyearmonth_dimindex"><?php _e("Publication Month:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_pubyearmonth_dimindex" name="options[ga_pubyearmonth_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_pubyearmonth_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_category_dimindex"><?php _e("Categories:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_category_dimindex" name="options[ga_category_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_category_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_user_dimindex"><?php _e("User Type:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_user_dimindex" name="options[ga_user_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_user_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_tag_dimindex"><?php _e("Tags:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td><select id="ga_tag_dimindex" name="options[ga_tag_dimindex]">
									<?php for ($i=0;$i<21;$i++){?>
									<option value="<?php echo $i;?>" <?php selected( $options['ga_tag_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
									<?php }?>
							</select></td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-advanced">
                            <table class="gadwp-settings-options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Advanced Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_speed_samplerate"><?php _e("Page Speed SR:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="number" id="ga_speed_samplerate" name="options[ga_speed_samplerate]" value="<?php echo (int)($options['ga_speed_samplerate']); ?>" max="100" min="1"> %</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_event_bouncerate]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_event_bouncerate" <?php checked( $options['ga_event_bouncerate'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_event_bouncerate">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("exclude events from bounce-rate calculation", 'syrus-analytics-tag-manager' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_enhanced_links]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_enhanced_links" <?php checked( $options['ga_enhanced_links'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_enhanced_links">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable enhanced link attribution", 'syrus-analytics-tag-manager' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_dash_adsense]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_adsense" <?php checked( $options['ga_dash_adsense'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_dash_adsense">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable AdSense account linking", 'syrus-analytics-tag-manager' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Cross-domain Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="gadwp-settings-title">
                                        <div class="button-primary gadwp-settings-switchoo">
                                            <input type="checkbox" name="options[ga_crossdomain_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_crossdomain_tracking" <?php checked( $options['ga_crossdomain_tracking'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="ga_crossdomain_tracking">
                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                <div class="gadwp-settings-switchoo-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable cross domain tracking", 'syrus-analytics-tag-manager' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_crossdomain_list"><?php _e("Cross Domains:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_crossdomain_list" name="options[ga_crossdomain_list]" value="<?php echo esc_attr($options['ga_crossdomain_list']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Cookie Customization", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_cookiedomain"><?php _e("Cookie Domain:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_cookiedomain" name="options[ga_cookiedomain]" value="<?php echo esc_attr($options['ga_cookiedomain']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_cookiename"><?php _e("Cookie Name:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_cookiename" name="options[ga_cookiename]" value="<?php echo esc_attr($options['ga_cookiename']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td class="gadwp-settings-title"><label for="ga_cookieexpires"><?php _e("Cookie Expires:", 'syrus-analytics-tag-manager'); ?></label></td>
                                    <td><input type="text" id="ga_cookieexpires" name="options[ga_cookieexpires]" value="<?php echo esc_attr($options['ga_cookieexpires']); ?>" size="10"> seconds</td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-exclude">
                            <table class="gadwp-settings-options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Exclude Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="roles gadwp-settings-title"><label for="ga_track_exclude"><?php _e("Exclude tracking for:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                    <td class="gadwp-settings-roles">
                               		<?php
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$i = 0;
		?>		<table>
                                            <tr>
									<?php
		foreach ( $wp_roles->role_names as $role => $name ) {
			$i++;
			?>
	                                    	<td><label> <input type="checkbox" name="options[ga_track_exclude][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_track_exclude'])) echo 'checked="checked"'; ?> />
	                                        	<?php echo $name; ?>
											</label></td>
	                                    <?php
			if ( $i % 4 == 0 ) {
				?>
                                 			</tr>
                                            <tr>
                                    		<?php
			}
		}
		?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <table class="gadwp-settings-options">
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'syrus-analytics-tag-manager' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
</form>
<?php
		self::output_sidebar();
	}

	public static function errors_debugging() {
		global $wp_version;

		$gadwp = GADWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$anonim = $gadwp->config->options;
		$anonim['wp_version'] = $wp_version;
		$anonim['gadwp_version'] = GADWP_CURRENT_VERSION;
		if ( $anonim['ga_dash_token'] ) {
			$anonim['ga_dash_token'] = 'HIDDEN';
		}
		if ( $anonim['ga_dash_clientid'] ) {
			$anonim['ga_dash_clientid'] = 'HIDDEN';
		}
		if ( $anonim['ga_dash_clientsecret'] ) {
			$anonim['ga_dash_clientsecret'] = 'HIDDEN';
		}
		$options = self::update_options( 'frontend' );
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		?>
<div class="wrap">
    	<?php echo "<h2>" . __( "Google Analytics Errors & Debugging", 'syrus-analytics-tag-manager' ) . "</h2>"; ?>
    	</div>
<div id="poststuff" class="gadwp">
    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
            <div class="settings-wrapper">
                <div class="inside">
    					<?php if (isset($message)) echo $message; ?>
    					<?php
		$tabs = array( 'errors' => __( "Errori & Dettagli", 'syrus-analytics-tag-manager' ), 'config' => __( "Impostazioni Plugin", 'syrus-analytics-tag-manager' ) );
		self::navigation_tabs( $tabs );
		?>
						<div id="gadwp-errors">
                        <table class="gadwp-settings-options">
                            <tr>
                                <td>
                                    <?php echo "<h2>" . __( "Ultimo errore rilevato", 'syrus-analytics-tag-manager' ) . "</h2>"; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                    				<?php
		$errors = print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ? esc_html( print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ) : __( "None", 'syrus-analytics-tag-manager' );
		echo '<pre class="gadwp-settings-logdata">Ultimo errore: ';
		echo $errors;
		?></pre>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Dettagli Errore", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td>
                    				<?php
		echo '<pre class="gadwp-settings-logdata">Dettagli Errore: ';
		$error_details = print_r( GADWP_Tools::get_cache( 'gapi_errors' ), true ) ? "\n" . esc_html( print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ) : __( "None", 'syrus-analytics-tag-manager' );
		echo $error_details;
		?></pre><br />
                                    <hr>
                                </td>


                            <tr>

                        </table>
                    </div>
                    <div id="gadwp-config">
                        <table class="gadwp-settings-options">
                            <tr>
                                <td><?php echo "<h2>" . __( "Configurazione Plugin", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td><pre class="gadwp-settings-logdata"><?php echo esc_html(print_r($anonim, true));?></pre><br />
                                    <hr></td>
                            </tr>
                        </table>
                    </div>
    <?php
		self::output_sidebar();
	}

	public static function general_settings() {
		$gadwp = GADWP();

		global $wp_version;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'general' );
		printf( '<div id="gapi-warning" class="updated"><p>%1$s <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">%2$s</a></p></div>', __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'syrus-analytics-tag-manager' ), __( 'Library conflicts between WordPress plugins', 'syrus-analytics-tag-manager' ) );
		if ( null === $gadwp->gapi_controller ) {
			$gadwp->gapi_controller = new GADWP_GAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_POST['ga_dash_code'] ) ) {
			if ( ! stripos( 'x' . $_POST['ga_dash_code'], 'UA-', 1 ) == 1 ) {
				try {
					$gadwp->gapi_controller->client->authenticate( $_POST['ga_dash_code'] );
					$gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
					$gadwp->config->options['automatic_updates_minorversion'] = 1;
					$gadwp->config->set_plugin_options();
					$options = self::update_options( 'general' );
					$message = "<div class='updated'><p>" . __( "Autorizzazione Plugin effettuata con successo.", 'syrus-analytics-tag-manager' ) . "</p></div>";
					GADWP_Tools::delete_cache( 'gapi_errors' );
					GADWP_Tools::delete_cache( 'last_error' );
					if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
						if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
							$profiles = $gadwp->config->options['ga_dash_profile_list'];
						} else {
							$profiles = $gadwp->gapi_controller->refresh_profiles();
						}
						if ( $profiles ) {
							$gadwp->config->options['ga_dash_profile_list'] = $profiles;
							if ( ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
								$profile = GADWP_Tools::guess_default_domain( $profiles );
								$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
								// $gadwp->config->options['ga_dash_tableid'] = $profile;
							}
							$gadwp->config->set_plugin_options();
							$options = self::update_options( 'general' );
						}
					}
				} catch ( Google_IO_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ), $gadwp->gapi_controller->error_timeout );
					return false;
				} catch ( Google_Service_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( "(" . $e->getCode() . ") " . $e->getMessage() ), $gadwp->gapi_controller->error_timeout );
					GADWP_Tools::set_cache( 'gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout );
					return $e->getCode();
				} catch ( Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout );
					$gadwp->gapi_controller->reset_token( false );
				}
			} else {
				$message = "<div class='error'><p>" . __( "The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'syrus-analytics-tag-manager' ) . ".</p></div>";
			}
		}
		if ( isset( $_POST['Clear'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::clear_cache();
				$message = "<div class='updated'><p>" . __( "Cleared Cache.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->gapi_controller->reset_token( true );
				GADWP_Tools::clear_cache();
				$message = "<div class='updated'><p>" . __( "Token Reseted and Revoked.", 'syrus-analytics-tag-manager' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset_Err'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::delete_cache( 'last_error' );
				GADWP_Tools::delete_cache( 'gapi_errors' );
				$message = "<div class='updated'><p>" . __( "All errors reseted.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && ! isset( $_POST['Clear'] ) && ! isset( $_POST['Reset'] ) && ! isset( $_POST['Reset_Err'] ) ) {
			$message = "<div class='updated'><p>" . __( "Settings saved.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Hide'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$message = "<div class='updated'><p>" . __( "All other domains/properties were removed.", 'syrus-analytics-tag-manager' ) . "</p></div>";
				$lock_profile = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] );
				$gadwp->config->options['ga_dash_profile_list'] = array( $lock_profile );
				$options = self::update_options( 'general' );
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		?>
    <div class="wrap">
	<?php echo "<h2>" . __( "Impostazioni Google Analytics", 'syrus-analytics-tag-manager' ) . "</h2>"; ?>
	                <hr>
                    </div>
                    <div id="poststuff" class="gadwp">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <div class="settings-wrapper">
                                    <div class="inside">
<?php
		if ( $gadwp->gapi_controller->gapi_errors_handler() || GADWP_Tools::get_cache( 'last_error' ) ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		if ( isset( $_POST['Authorize'] ) ) {
			GADWP_Tools::clear_cache();
			$gadwp->gapi_controller->token_request();
			echo "<div class='updated'><p>" . __( "Use the red link (see below) to generate and get your access code!", 'syrus-analytics-tag-manager' ) . "</p></div>";
		} else {
			if ( isset( $message ) ) {
				echo $message;
			}
			?>
					<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                                            <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
						<table class="gadwp-settings-options">
                                                <tr>
                                                    <td colspan="2">
                                                        <?php echo "<h2>" . __( "Autorizzazione Plugin", 'syrus-analytics-tag-manager' ) . "</h2>";?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="gadwp-settings-info">
                                                        <?php printf(__('Dovresti guardare %1$s e leggere %2$s prima di procede all\'autorizzazione. E\' necessario un account configurato Google Analytics', 'syrus-analytics-tag-manager'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'syrus-analytics-tag-manager')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'syrus-analytics-tag-manager')));?>
						                            </td>
                                                </tr>
						                          <?php if (! $options['ga_dash_token'] || $options['ga_dash_userapi']) {?>
						                          <tr>
                                                    <td colspan="2" class="gadwp-settings-info"><input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" <?php echo ($options['ga_dash_network'])?'disabled="disabled"':''; ?> /><?php echo " ".__("utilizza le tue credenziali API Project", 'syrus-analytics-tag-manager' );?>
							                            </td>
                                                </tr>
                                				  <?php } if ($options['ga_dash_userapi']) { ?>
                                                <tr>
                                                    <td class="gadwp-settings-title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'syrus-analytics-tag-manager'); ?></label></td>
                                                    <td><input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required"></td>
                                                </tr>
                                                <tr>
                                                    <td class="gadwp-settings-title"><label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'syrus-analytics-tag-manager'); ?></label></td>
                                                    <td><input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required"> <input type="hidden" name="options[ga_dash_hidden]" value="Y">
									                    <?php wp_nonce_field('gadash_form','gadash_security'); ?>
								                    </td>
                                                </tr>
						<?php
			}
			if ( $options['ga_dash_token'] ) {
				?>
					                            <tr>
                                                    <td colspan="2"><input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Cancella Autorizzazione", 'syrus-analytics-tag-manager' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Svuota Cache", 'syrus-analytics-tag-manager' ); ?>" /> <input type="submit" name="Reset_Err" class="button button-secondary" value="<?php _e( "Reset Errori", 'syrus-analytics-tag-manager' ); ?>" /></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><?php echo "<h2>" . __( "Impostazioni Generali", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="gadwp-settings-title"><label for="ga_dash_tableid_jail"><?php _e("Seleziona vista:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                                    <td><select id="ga_dash_tableid_jail" <?php disabled(empty($options['ga_dash_profile_list']) || 1 == count($options['ga_dash_profile_list']), true); ?> name="options[ga_dash_tableid_jail]">
                                    								<?php
				if ( ! empty( $options['ga_dash_profile_list'] ) ) {
					foreach ( $options['ga_dash_profile_list'] as $items ) {
						if ( $items[3] ) {
							echo '<option value="' . esc_attr( $items[1] ) . '" ' . selected( $items[1], $options['ga_dash_tableid_jail'], false );
							echo ' title="' . __( "Nome vista:", 'syrus-analytics-tag-manager' ) . ' ' . esc_attr( $items[0] ) . '">' . esc_html( GADWP_Tools::strip_protocol( $items[3] ) ) . ' &#8658; ' . esc_attr( $items[0] ) . '</option>';
						}
					}
				} else {
					echo '<option value="">' . __( "Property not found", 'syrus-analytics-tag-manager' ) . '</option>';
				}
				?>
                                    							</select>
                                    							<?php
				if ( count( $options['ga_dash_profile_list'] ) > 1 ) {
					?>&nbsp;<input type="submit" name="Hide" class="button button-secondary" value="<?php _e( "Lock Selection", 'syrus-analytics-tag-manager' ); ?>" /><?php
				}
				?>
							                         </td>
                                                </tr>
							<?php
				if ( $options['ga_dash_tableid_jail'] ) {
					?>
							<tr>
                                                    <td class="gadwp-settings-title"></td>
                                                    <td><?php
					$profile_info = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] );
					echo '<pre>' . __( "Nome vista:", 'syrus-analytics-tag-manager' ) . "\t" . esc_html( $profile_info[0] ) . "<br />" . __( "Tracking ID:", 'syrus-analytics-tag-manager' ) . "\t" . esc_html( $profile_info[2] ) . "<br />" . __( "Default URL:", 'syrus-analytics-tag-manager' ) . "\t" . esc_html( $profile_info[3] ) . "<br />" . __( "Time Zone:", 'syrus-analytics-tag-manager' ) . "\t" . esc_html( $profile_info[5] ) . '</pre>';
					?></td>
                                                </tr>
							<?php
				}
				?>
							                     <tr>
                                                    <td class="gadwp-settings-title"><label for="ga_dash_style"><?php _e("Colore tema:", 'syrus-analytics-tag-manager' ); ?></label></td>
                                                    <td><input type="text" id="ga_dash_style" class="ga_dash_style" name="options[ga_dash_style]" value="<?php echo esc_attr($options['ga_dash_style']); ?>" size="10"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
												<?php if ( !is_multisite()) {?>
												<tr>
                                                    <td colspan="2"><?php echo "<h2>" . __( "Aggiornamenti Automatici", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="gadwp-settings-title">
                                                        <div class="button-primary gadwp-settings-switchoo">
                                                            <input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="gadwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="automatic_updates_minorversion">
                                                                <div class="gadwp-settings-switchoo-inner"></div>
                                                                <div class="gadwp-settings-switchoo-switch"></div>
                                                            </label>
                                                        </div>
                                                        <div class="switch-desc"><?php echo " ".__( "aggiornamenti automatici per versioni minori (solo sicurezza e manutenzione)", 'syrus-analytics-tag-manager' );?></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
												<?php }?>
                                                <tr>
                                                    <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'syrus-analytics-tag-manager' ) ?>" /></td>
                                                </tr>
		<?php } else {?>
							                    <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Autorizza Plugin", 'syrus-analytics-tag-manager' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Svuota Cache", 'syrus-analytics-tag-manager' ); ?>" /></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                            </table>
                                        </form>
			<?php
				self::output_sidebar();
				return;
			}
			?>
					</table>
                                        </form>
<?php
		}
		self::output_sidebar();
	}
	// Network Settings
	public static function general_settings_network() {
		$gadwp = GADWP();
		global $wp_version;

		if ( ! current_user_can( 'manage_network_options' ) ) {
			return;
		}
		$options = self::update_options( 'network' );
		/*
		 * Include GAPI
		 */
		echo '<div id="gapi-warning" class="updated"><p>' . __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'syrus-analytics-tag-manager' ) . ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">Library conflicts between WordPress plugins</a></p></div>';

		if ( null === $gadwp->gapi_controller ) {
			$gadwp->gapi_controller = new GADWP_GAPI_Controller();
		}

		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_POST['ga_dash_code'] ) ) {
			if ( ! stripos( 'x' . $_POST['ga_dash_code'], 'UA-', 1 ) == 1 ) {
				try {
					$gadwp->gapi_controller->client->authenticate( $_POST['ga_dash_code'] );
					$gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
					$gadwp->config->options['automatic_updates_minorversion'] = 1;
					$gadwp->config->set_plugin_options( true );
					$options = self::update_options( 'network' );
					$message = "<div class='updated'><p>" . __( "Autorizzazione Plugin effettuata con successo.", 'syrus-analytics-tag-manager' ) . "</p></div>";
					if ( is_multisite() ) { // Cleanup errors on the entire network
						foreach ( GADWP_Tools::get_sites( array( 'number' => apply_filters( 'gadwp_sites_limit', 100 ) ) ) as $blog ) {
							switch_to_blog( $blog['blog_id'] );
							GADWP_Tools::delete_cache( 'gapi_errors' );
							restore_current_blog();
						}
					} else {
						GADWP_Tools::delete_cache( 'gapi_errors' );
					}
					if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
						if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
							$profiles = $gadwp->config->options['ga_dash_profile_list'];
						} else {
							$profiles = $gadwp->gapi_controller->refresh_profiles();
						}
						if ( $profiles ) {
							$gadwp->config->options['ga_dash_profile_list'] = $profiles;
							if ( isset( $gadwp->config->options['ga_dash_tableid_jail'] ) && ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
								$profile = GADWP_Tools::guess_default_domain( $profiles );
								$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
								// $gadwp->config->options['ga_dash_tableid'] = $profile;
							}
							$gadwp->config->set_plugin_options( true );
							$options = self::update_options( 'network' );
						}
					}
				} catch ( Google_IO_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ), $gadwp->gapi_controller->error_timeout );
					return false;
				} catch ( Google_Service_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( "(" . $e->getCode() . ") " . $e->getMessage() ), $gadwp->gapi_controller->error_timeout );
					GADWP_Tools::set_cache( 'gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout );
					return $e->getCode();
				} catch ( Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout );
					$gadwp->gapi_controller->reset_token( false );
				}
			} else {
				$message = "<div class='error'><p>" . __( "The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'syrus-analytics-tag-manager' ) . ".</p></div>";
			}
		}
		if ( isset( $_POST['Refresh'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->config->options['ga_dash_profile_list'] = array();
				$message = "<div class='updated'><p>" . __( "Properties refreshed.", 'syrus-analytics-tag-manager' ) . "</p></div>";
				$options = self::update_options( 'network' );
				if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
					if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
						$profiles = $gadwp->config->options['ga_dash_profile_list'];
					} else {
						$profiles = $gadwp->gapi_controller->refresh_profiles();
					}
					if ( $profiles ) {
						$gadwp->config->options['ga_dash_profile_list'] = $profiles;
						if ( isset( $gadwp->config->options['ga_dash_tableid_jail'] ) && ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
							$profile = GADWP_Tools::guess_default_domain( $profiles );
							$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
							// $gadwp->config->options['ga_dash_tableid'] = $profile;
						}
						$gadwp->config->set_plugin_options( true );
						$options = self::update_options( 'network' );
					}
				}
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Clear'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::clear_cache();
				$message = "<div class='updated'><p>" . __( "Cleared Cache.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->gapi_controller->reset_token( true );
				GADWP_Tools::clear_cache();
				$message = "<div class='updated'><p>" . __( "Token Reseted and Revoked.", 'syrus-analytics-tag-manager' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && ! isset( $_POST['Clear'] ) && ! isset( $_POST['Reset'] ) && ! isset( $_POST['Refresh'] ) ) {
			$message = "<div class='updated'><p>" . __( "Settings saved.", 'syrus-analytics-tag-manager' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Hide'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$message = "<div class='updated'><p>" . __( "All other domains/properties were removed.", 'syrus-analytics-tag-manager' ) . "</p></div>";
				$lock_profile = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] );
				$gadwp->config->options['ga_dash_profile_list'] = array( $lock_profile );
				$options = self::update_options( 'network' );
			} else {
				$message = "<div class='error'><p>" . __( "Cheating Huh?", 'syrus-analytics-tag-manager' ) . "</p></div>";
			}
		}
		?>
	<div class="wrap">
		<?php echo "<h2>" . __( "Impostazioni Google Analytics", 'syrus-analytics-tag-manager' ) . "</h2>"; ?><hr>
                                        </div>
                                        <div id="poststuff" class="gadwp">
                                            <div id="post-body" class="metabox-holder columns-2">
                                                <div id="post-body-content">
                                                    <div class="settings-wrapper">
                                                        <div class="inside">
						<?php
		if ( $gadwp->gapi_controller->gapi_errors_handler() || GADWP_Tools::get_cache( 'last_error' ) ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'syrus-analytics-tag-manager' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'syrus-analytics-tag-manager' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'syrus-analytics-tag-manager' ) ) ) );
		}
		if ( isset( $_POST['Authorize'] ) ) {
			GADWP_Tools::clear_cache();
			$gadwp->gapi_controller->token_request();
			echo "<div class='updated'><p>" . __( "Use the red link (see below) to generate and get your access code!", 'syrus-analytics-tag-manager' ) . "</p></div>";
		} else {
			if ( isset( $message ) ) {
				echo $message;
			}
			?>
						<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                                                                <input type="hidden" name="options[ga_dash_hidden]" value="Y">
							<?php wp_nonce_field('gadash_form','gadash_security'); ?>
							<table class="gadwp-settings-options">
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Network Setup", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="gadwp-settings-title">
                                                                            <div class="button-primary gadwp-settings-switchoo">
                                                                                <input type="checkbox" name="options[ga_dash_network]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_network" <?php checked( $options['ga_dash_network'], 1); ?> onchange="this.form.submit()"> <label class="gadwp-settings-switchoo-label" for="ga_dash_network">
                                                                                    <div class="gadwp-settings-switchoo-inner"></div>
                                                                                    <div class="gadwp-settings-switchoo-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__("use a single Google Analytics account for the entire network", 'syrus-analytics-tag-manager' );?></div>
                                                                        </td>
                                                                    </tr>
								<?php if ($options['ga_dash_network']){  //Network Mode check?>
								<tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Autorizzazione Plugin", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="gadwp-settings-info">
							                                                 <?php printf(__('Dovresti guardare %1$s e leggere %2$s prima di procede all\'autorizzazione. E\' necessario un account configurato Google Analytics', 'syrus-analytics-tag-manager'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'syrus-analytics-tag-manager')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'syrus-analytics-tag-manager')));?>
							                                             </td>
                                                                    </tr>
							<?php
				if ( ! $options['ga_dash_token'] || $options['ga_dash_userapi'] ) {
					?>
							                                        <tr>
                                                                        <td colspan="2" class="gadwp-settings-info"><input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" /><?php echo " ".__("utilizza le tue credenziali API Project", 'syrus-analytics-tag-manager' );?>
								                                        </td>
                                                                    </tr>
							<?php
				}
				if ( $options['ga_dash_userapi'] ) {
					?>
                                                                    <tr>
                                                                        <td class="gadwp-settings-title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'syrus-analytics-tag-manager'); ?></label></td>
                                                                        <td><input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="gadwp-settings-title"><label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'syrus-analytics-tag-manager'); ?></label></td>
                                                                        <td><input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required"> <input type="hidden" name="options[ga_dash_hidden]" value="Y">
										<?php wp_nonce_field('gadash_form','gadash_security'); ?>
									</td>
                                                                    </tr>
							<?php
				}
				if ( $options['ga_dash_token'] ) {
					?>
						<tr>
                                                                        <td colspan="2"><input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Cancella Autorizzazione", 'syrus-analytics-tag-manager' ); ?>" /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Svuota Cache", 'syrus-analytics-tag-manager' ); ?>" /> <input type="submit" name="Refresh" class="button button-secondary" value="<?php _e( "Refresh Properties", 'syrus-analytics-tag-manager' ); ?>" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Properties/Views Settings", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                                    </tr>
								<?php
					if ( isset( $options['ga_dash_tableid_network'] ) ) {
						$options['ga_dash_tableid_network'] = json_decode( json_encode( $options['ga_dash_tableid_network'] ), false );
					}
					foreach ( GADWP_Tools::get_sites( array( 'number' => apply_filters( 'gadwp_sites_limit', 100 ) ) ) as $blog ) {
						?>
							                                         <tr>
                                                                        <td class="gadwp-settings-title-s"><label for="ga_dash_tableid_network"><?php echo '<strong>'.$blog['domain'].$blog['path'].'</strong>: ';?></label></td>
                                                                        <td><select id="ga_dash_tableid_network" <?php disabled(!empty($options['ga_dash_profile_list']),false);?> name="options[ga_dash_tableid_network][<?php echo $blog['blog_id'];?>]">
									<?php
						if ( ! empty( $options['ga_dash_profile_list'] ) ) {
							foreach ( $options['ga_dash_profile_list'] as $items ) {
								if ( $items[3] ) {
									$temp_id = $blog['blog_id'];
									echo '<option value="' . esc_attr( $items[1] ) . '" ' . selected( $items[1], isset( $options['ga_dash_tableid_network']->$temp_id ) ? $options['ga_dash_tableid_network']->$temp_id : '', false );
									echo ' title="' . __( "Nome vista:", 'syrus-analytics-tag-manager' ) . ' ' . esc_attr( $items[0] ) . '">' . esc_html( GADWP_Tools::strip_protocol( $items[3] ) ) . ' &#8658; ' . esc_attr( $items[0] ) . '</option>';
								}
							}
						} else {
							echo '<option value="">' . __( "Property not found", 'syrus-analytics-tag-manager' ) . '</option>';
						}
						?>
    								                                    </select> <br /></td>
                                                                    </tr>
							<?php
					}
					?>
                    												<tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Aggiornamenti Automatici", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="gadwp-settings-title">
                                                                            <div class="button-primary gadwp-settings-switchoo">
                                                                                <input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="gadwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>> <label class="gadwp-settings-switchoo-label" for="automatic_updates_minorversion">
                                                                                    <div class="gadwp-settings-switchoo-inner"></div>
                                                                                    <div class="gadwp-settings-switchoo-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'syrus-analytics-tag-manager' );?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr><?php echo "<h2>" . __( "Exclude Tracking", 'syrus-analytics-tag-manager' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="gadwp-settings-title">
                                                                            <div class="button-primary gadwp-settings-switchoo">
                                                                                <input type="checkbox" name="options[ga_dash_excludesa]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_excludesa"<?php checked( $options['ga_dash_excludesa'], 1); ?>"> <label class="gadwp-settings-switchoo-label" for="ga_dash_excludesa">
                                                                                    <div class="gadwp-settings-switchoo-inner"></div>
                                                                                    <div class="gadwp-settings-switchoo-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__("exclude Super Admin tracking for the entire network", 'syrus-analytics-tag-manager' );?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'syrus-analytics-tag-manager' ) ?>" /></td>
                                                                    </tr>
			<?php
				} else {
					?>
								                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Autorizza Plugin", 'syrus-analytics-tag-manager' ); ?>" /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Svuota Cache", 'syrus-analytics-tag-manager' ); ?>" /></td>
                                                                    </tr>
								   <?php }  //Network Mode check?>
									                                <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                </table>
                                                            </form>
				<?php
				self::output_sidebar();
				return;
			}
			?>
						</table>
                                                            </form>
	<?php
		}
		self::output_sidebar();
	}

	public static function output_sidebar() {
		global $wp_version;

		$gadwp = GADWP();
		?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--<div id="postbox-container-1" class="postbox-container">
                                                    <div class="meta-box-sortables">
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Setup Tutorial & Demo",'syrus-analytics-tag-manager') ?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=video&utm_campaign=gadwp" target="_blank"><img src="<?php echo plugins_url( 'images/google-analytics-dashboard.png' , __FILE__ );?>" width="100%" alt="" /></a>
                                                            </div>
                                                        </div>
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Follow & Review",'syrus-analytics-tag-manager')?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <div class="gadash-desc">
                                                                    <div style="margin-left: -10px;">
                                                                        <div class="g-page" data-width="273" data-href="//plus.google.com/+Deconfcom" data-layout="landscape" data-showtagline="false" data-showcoverphoto="false" data-rel="publisher"></div>
                                                                    </div>
                                                                    <script type="text/javascript">
																	  (function() {
																	    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
																	    po.src = 'https://apis.google.com/js/platform.js';
																	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
																	  })();
																	</script>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-desc">
                                                                    <a href="https://twitter.com/deconfcom" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @deconfcom</a>
                                                                    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="http://wordpress.org/support/view/plugin-reviews/syrus-analytics-tag-manager#plugin-info"><img src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Your feedback and review are both important, %s!', 'syrus-analytics-tag-manager'), sprintf('<a href="http://wordpress.org/support/view/plugin-reviews/syrus-analytics-tag-manager#plugin-info">%s</a>', __('rate this plugin', 'syrus-analytics-tag-manager')));?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Further Reading",'syrus-analytics-tag-manager')?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('%s service with users tracking at IP level.', 'syrus-analytics-tag-manager'), sprintf('<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp">%s</a>', __('Web Analytics', 'syrus-analytics-tag-manager')));?>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/ssl.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('%s by moving your website to HTTPS/SSL.', 'syrus-analytics-tag-manager'), sprintf('<a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp">%s</a>', __('Improve search rankings', 'syrus-analytics-tag-manager')));?>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/wp.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Other %s written by the same author', 'syrus-analytics-tag-manager'), sprintf('<a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp">%s</a>', __('WordPress Plugins', 'syrus-analytics-tag-manager')));?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>-->
                                            </div>
                                        </div>
<?php
		//Dismiss the admin update notice
		if ( version_compare( $wp_version, '4.2', '<' ) && current_user_can('manage_options') ) {
			delete_option('gadwp_got_updated');
		}
	}
}
