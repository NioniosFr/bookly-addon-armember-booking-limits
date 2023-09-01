<?php
/*
Plugin Name: Bookly ARMember Booking Limits (Add-on)
Plugin URI: https://www.github.com/nioniosfr/bookly-addon-armember-booking-limits
Description: Bookly ARMember Booking Limits add-on allows you to limit customers from booking services outside the active period of their subsriction plan.
Version: 0.0.1
Author: Dionysios Fryganas <dfryganas@gmail.com>
Author URI: https://www.github.com/nioniosfr
Text Domain: baarmbl
Domain Path: /languages
License: MIT
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display a warning in admin sections when the plugin cannot be used.
 */
function dfr_baarmbl_admin_notice() {
	echo '<div class="error"><h3>Bookly ARMember Booking Limits (Add-on)</h3><p>To install this plugin - <strong>Bookly</strong> plugin is required.</p></div>';
}

/**
 * Initialization logic of this plugin.
 */
function dfr_baarmbl_init() {
	if ( ! is_plugin_active( 'bookly-responsive-appointment-booking-tool/main.php' ) ) {

		add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', 'dfr_baarmbl_admin_notice' );
		return;
	}
	add_filter( 'bookly_appointments_limit', 'dfr_baarmbl_subscription_plan_limit', 11, 4 );
}

add_action( 'init', 'dfr_baarmbl_init' );

/**
 * Filter the current limit based on the users group.
 *
 * In bookly-responsive-appointment-booking-tool/lib/entities/Service.php, inside appointmentsLimitReached method, line ~375
 * Add the following filter:
 *
 * $limit = apply_filters( 'bookly_appointments_limit', $this->getAppointmentsLimit(), $service_id, $customer_id, $appointment_dates );
 * if ( $db_count + $cart_count > $limit ) {
 *   return true;
 * }
 *
 * @param int      $default_limit The service limit.
 *
 * @param int      $service_id    The service being checked for limits.
 *
 * @param int      $customer_id   The bookly customer.
 *
 * @param string[] $appointment_dates The dates being booked by the customer.
 *
 * @return int
 */
function dfr_baarmbl_subscription_plan_limit( $default_limit, $service_id, $customer_id, $appointment_dates ) {
	$customer = new \Bookly\Lib\Entities\Customer();
	$customer->load( $customer_id );

	if ( null === $customer || ! $customer->isLoaded() ) {
		return $default_limit;
	}

	$plan_ids = get_user_meta( $customer->getWpUserId(), 'arm_user_plan_ids', true );
	$plan_ids = ! empty( $plan_ids ) ? $plan_ids : array();

	foreach ( $plan_ids as $plan_id ) {
		$plan_data = get_user_meta( $customer->getWpUserId(), 'arm_user_plan_' . $plan_id, true );
		foreach ( $appointment_dates as $appointment_date ) {
			$date = strtotime( $appointment_date );
			if ( $date < $plan_data['arm_start_plan'] || $date > $plan_data['arm_expire_plan'] ) {
				return 0;
			}
		}
	}
	return $default_limit;
}
