<?php

if ( ! class_exists( 'WCO_Woo_Settings' ) ) :
	class WCO_Woo_Settings {
		/**
		 * Bootstraps the class and hooks required actions & filters.
		 *
		 */
		public static function init() {
			add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
			add_action( 'woocommerce_settings_tabs_wco_settings', __CLASS__ . '::settings_tab' );
			add_action( 'woocommerce_update_options_wco_settings', __CLASS__ . '::update_settings' );
		}
		
		/**
		 * Add a new settings tab to the WooCommerce settings tabs array.
		 *
		 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
		 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
		 */
		public static function add_settings_tab( $settings_tabs ) {
			$settings_tabs['wco_settings'] = __( 'Cancel Order Settings', 'wco' );
			return $settings_tabs;
		}
		/**
		 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
		 *
		 * @uses woocommerce_admin_fields()
		 * @uses self::get_settings()
		 */
		public static function settings_tab() {
			woocommerce_admin_fields( self::get_settings() );
		}
		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
		 *
		 * @uses woocommerce_update_options()
		 * @uses self::get_settings()
		 */
		public static function update_settings() {
			woocommerce_update_options( self::get_settings() );
		}
		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
		 *
		 * @return array Array of settings for @see woocommerce_admin_fields() function.
		 */
		public static function get_settings() {
			$settings = array(
				'section_title' => array(
					'name'     => __( 'Section Title', 'wco' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wc_wco_settings_section_title'
				),
				'activate' => array(
					'name' => __( 'Activate Cancel Order', 'wco' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this if you want to activate cancel order feature', 'wco' ),
					'id'   => 'wc_wco_settings_activate'
				),
				'eligible_statuses' => array(
					'name' => __( 'Eligible Order Status for Cancel', 'wco' ),
					'type' => 'multiselect',
					'desc' => __( 'Select the order status(es) for eligible for cancel', 'wco' ),
					'id'   => 'wc_wco_settings_eligible_statuses',
					'class' => 'chosen_select',
					'options' => [
						'processing' => 'processing', 
						'pending' => 'pending', 
						'on-hold' => 'on-hold'
					]
				),
				'cancel_order_threshold_time' => array(
					'name' => __( 'Threshold Time for Cancel order ( in minutes )', 'wco' ),
					'type' => 'number',
					'default' => 120,
					'desc' => __( 'Number of minutes by when a customer can cancel an order', 'wco' ),
					'id'   => 'wc_wco_settings_cancel_order_threshold_time',
					'class' => 'input-text regular-input '
				),
				'cancel_success_message' => array(
					'name' => __( 'Order Cancel success message', 'wco' ),
					'type' => 'textarea',
					'default' => 'Your order has been submitted as "Cancelled by Customer"!',
					'desc' => __( 'This success message will be shown once the customer successfully cancel an order', 'wco' ),
					'id'   => 'wc_wco_settings_cancel_success_message',
					'class' => 'input-text wide-input '
				),
				'shop_owner_email' => array(
					'name' => __( 'Notification Email id', 'wco' ),
					'type' => 'email',
					'default' => get_option('admin_email'),
					'desc' => __( 'When a customer cancel an order then this email id will get a notification', 'wco' ),
					'id'   => 'wc_wco_settings_shop_owner_email',
					'class' => 'input-text regular-input '
				),
				'notification_email_subject' => array(
					'name' => __( 'Notification Email Subject', 'wco' ),
					'type' => 'text',
					'default' => '#%%order_id%% Order Cancelled by customer',
					'desc' => __( 'Email Subject for the notification email, The following shortcode is available: %%order_id%%, %%customer_name%%', 'wco' ),
					'id'   => 'wc_wco_settings_notification_email_subject',
					'class' => 'input-text regular-input '
				),
				'notification_email_body' => array(
					'name' => __( 'Notification Email Body', 'wco' ),
					'type' => 'textarea',
					'desc' => __( 'Email body for order cancel notification. You may use html. The following shortcode is available: %%order_id%%, %%customer_name%%, %%order_admin_url%% ', 'wco' ),
					'default' => self::get_default_email_body(),
					'id'   => 'wc_wco_settings_notification_email_body',
					'class' => 'wide-input',
					'custom_attributes' => array(
						'cols' => 10,
						'rows' => 10
					)
				),
				'section_end' => array(
					'type' => 'sectionend',
					'id' => 'wc_wco_settings_section_end'
				)
			);
			return apply_filters( 'wc_wco_admin_settings', $settings );
		}
	
	
		/**
		 * Get Default email copy for admin interface
		 * @return string
		 */
		public static function get_default_email_body() {
			$email_body = "Dear Admin,<br />
	The order id: %%order_id%% has been canceled by %%customer_name%%. Please go to your admin panel and check the request.<br /><br />
	Please remember that the refund can be done manually and after refund, you can mark the order as 'refunded' <br /><br />
	Order Admin Url: %%order_admin_url%% <br />
	Thanks";
			return $email_body;
		}
	}
endif;