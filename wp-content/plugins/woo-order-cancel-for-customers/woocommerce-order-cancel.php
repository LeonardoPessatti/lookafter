<?php
/**
 * Plugin Name: WooCommerce Order Cancel For Customers
 * Plugin URI: https://github.com/WPManageNinja/WooCommerce-Order-Cancel-for-Customers
 * Description: A tiny plugin that will enable customers to cancel woocommerce order within a certain amount of time.
 * Version: 1.1
 * Author: WPManageNinja
 * Author URI: https://wpmanageninja.com
 * Requires at least: 4.4
 * Tested up to: 4.9.5
 *
 * Text Domain: wco
 *
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'libs/class-wco-woo-settings.php';

if ( ! class_exists( 'WCOCancelOrder' ) ) :
	class WCOCancelOrder {

		/**
		 * Eligible Order statuses for cancel
		 *
		 * @type array
		 */
		private $eligibleCancelStatuses;

		/**
		 * New Order Status for Woocommerce Order, It must have wc prefix
		 *
		 * @type string
		 */
		private $custom_order_status_name = 'wc-customer-cancel';

		/**
		 * Declare all the action and filter hooks
		 *
		 */
		public function init_plugin() {


			add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ),
				array( $this, 'wco_plugin_settings_link' ) );


			$cancelOrderStatus = get_option( 'wc_wco_settings_activate' );
			if ( $cancelOrderStatus != 'yes' ) {
				return;
			}

			$eligible_order_statuses = get_option( 'wc_wco_settings_eligible_statuses', array() );

			$this->eligibleCancelStatuses = apply_filters(
				'wco_eligible_cancel_order_statuses', $eligible_order_statuses
			);

			$this->register_custom_order_status();

			add_filter( 'wc_order_statuses', array( $this, 'custom_wc_order_statuses' ) );
			add_action( 'admin_head', array( $this, 'cancel_order_font_icon' ) );
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'order_cancel_button' ), 10, 1 );
			add_action( 'wp', array( $this, 'process_cancel_order' ) );
			add_action( 'wco_after_order_cancel_action', array( $this, 'send_email_notification_to_shop_admin' ), 10,
				2 );
			add_filter( 'wco_notification_email_subject', array( $this, 'parse_text_with_order_fields' ), 10, 2 );
			add_filter( 'wco_notification_email_body', array( $this, 'parse_text_with_order_fields' ), 10, 2 );
		}

		public function wco_plugin_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wc-settings&tab=wco_settings">' . __( 'Settings', 'wco' )
			                 . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}


		/**
		 * Register New order status for Woocommerce
		 *
		 * @uses register_post_status()
		 */
		public function register_custom_order_status() {
			register_post_status( $this->custom_order_status_name, array(
				'label'                     => __( 'Cancelled By Customer', 'wco' ),
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				'exclude_from_search'       => false,
				'label_count'               => _n_noop( 'Cancelled By Customer <span class="count">(%s)</span>',
					'Cancelled By Customers <span class="count">(%s)</span>' )
			) );
		}

		/**
		 * Append newly registered order-status in woocommerce status lists
		 *
		 * @param array $order_statuses
		 *
		 * @return array $new_order_statuses
		 */
		public function custom_wc_order_statuses( $order_statuses ) {
			$new_order_statuses = array();

			foreach ( $order_statuses as $key => $status ) {

				$new_order_statuses[ $key ] = $status;

				if ( 'wc-cancelled' === $key ) {
					$new_order_statuses[ $this->custom_order_status_name ] = __( 'Cancelled By Customer', 'wco' );
				}
			}

			return $new_order_statuses;
		}

		/**
		 * Add order cancel button
		 *
		 * @uses $this->can_customer_cancel_order($order)
		 *
		 * @param $order
		 */
		public function order_cancel_button( $order ) {
			$can_cancel = apply_filters( 'wco_can_customer_cancel_order', $this->can_customer_cancel_order( $order ),
				$order );
			if ( $can_cancel ) {
				$cancel_url = $this->get_cancel_url( $order->get_id() );

				do_action( 'wco_before_cancel_button_wrapper', $order );
				?>
                <div class="ico_cancel_order_wrapper">

                    <p><i><?php _e( 'Gostaria de cancelar esse pedido?', 'wco' ); ?></i> <a class="wco_cancel-button"
                                                                                     href="<?php echo $cancel_url; ?>"><?php _e( 'Click Aqui!',
								'wco' ); ?></a></p>
                </div>
				<?php
				do_action( 'wco_after_cancel_button_wrapper', $order );
			}
			else{
                do_action( 'wco_before_cancel_button_wrapper', $order );
                ?>
                <div class="ico_cancel_order_wrapper">

                    <p><i><?= "Pedido não pode ser cancelado pois já foi enviado." ?></i> <a class="wco_cancel-button"
                </div>
                <?php
                do_action( 'wco_after_cancel_button_wrapper', $order );
            }
		}

		public function get_cancel_url( $order_id ) {
			$urlData = build_query( array(
				'wco_order_cancel_id' => $order_id,
				'_nonce'              => wp_create_nonce( 'wco_customer_cancel_order_' . $order_id ),
				'action'              => 'process_cancel_order'
			) );

			return site_url() . '?' . $urlData;
		}

		/**
		 * Process Cancel Order once user request to cancel
		 */
		public function process_cancel_order() {
			if ( isset( $_REQUEST['wco_order_cancel_id'] ) ) {
				$order_id = $_REQUEST['wco_order_cancel_id'];

				if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'wco_customer_cancel_order_' . $order_id ) ) {
					wp_die( 'Security Error, Please try again!', 'wco' );
				}

				$order = wc_get_order( $order_id );

				if ( $this->can_customer_cancel_order( $order ) ) {

					$redirectUrl = esc_url( wc_get_endpoint_url( get_option( 'woocommerce_myaccount_orders_endpoint',
						'orders' ), '', wc_get_page_permalink( 'myaccount' ) ) );

					$redirectUrl = apply_filters( 'wco_after_cancel_redirect_url', $redirectUrl, $order_id );

					// cancel the order now
					$order_status_change_message = __( 'Customer wants to cancel the order and want to get refund.',
						'wco' );
					$order_status_change_message = apply_filters( 'wco_after_order_cancel_note',
						$order_status_change_message, $order_id );

					$success_message = get_option( 'wc_wco_settings_cancel_success_message',
						__( 'Your order has been submitted as "Cancelled by Customer"!', 'wco' ) );
					$success_message = apply_filters( 'wco_after_order_cancel_message', $success_message, $order_id );

					if ( ! wc_has_notice( $success_message ) ) {
						wc_add_notice( $success_message, 'success' );
					}

					$order = new WC_Order( $order_id );
					$order->update_status( $this->custom_order_status_name, $order_status_change_message );
					do_action( 'wco_after_order_cancel_action', $order, get_current_user_id() );
					wp_redirect( $redirectUrl );
					die();
				} else {
					wp_die( 'Sorry! You can not cancel this order now!', 'wco' );
				}
			}

		}

		/**
		 * Determine if customer can cancel a selected order
		 *
		 * @param $order
		 *
		 * @return bool
		 */
		private function can_customer_cancel_order( $order ) {
			$cancelOrderStatus = get_option( 'wc_wco_settings_activate' );
			if ( $cancelOrderStatus != 'yes' ) {
				return false;
			}

			$cancelTimeValidityMinutes = apply_filters( 'wco_cancel_validity_minutes',
				get_option( 'wc_wco_settings_cancel_order_threshold_time', 0 ), $order );
			$cancelTimeValidity        = $cancelTimeValidityMinutes * 60; // in seconds

			$customer_id = $order->get_customer_id();
			$user_ID     = get_current_user_id();

			$order_timestamp_diff = strtotime( current_time( 'mysql' ) ) - strtotime( $order->get_date_created() );

			if ( $cancelTimeValidity > $order_timestamp_diff && $customer_id == $user_ID
			     && in_array( $order->get_status(), $this->eligibleCancelStatuses )
			) {
				return true;
			}

			return false;
		}

		/**
		 * CSS for Cancel Order Icon
		 */
		public function cancel_order_font_icon() {
			echo '<style>
                    mark.customer-cancel:after{
                        font-family:WooCommerce;
                        speak:none;
                        font-weight:400;
                        font-variant:normal;
                        text-transform:none;
                        line-height:1;
                        -webkit-font-smoothing:antialiased;
                        margin:0;
                        text-indent:0;
                        position:absolute;
                        top:0;
                        left:0;
                        width:100%;
                        height:100%;
                        text-align:center;
                    }
    
                    mark.customer-cancel:after{
                        content:"\e012";
                        color:#ff0000;
                    }
          </style>';
		}

		/**
		 * Send email notification to admin once the customer cancel an order
		 *
		 * @param $order
		 * @param $user_id
		 */
		public function send_email_notification_to_shop_admin( $order, $user_id ) {
			$email_to = apply_filters( 'wco_notification_email', get_option( 'wc_wco_settings_shop_owner_email' ),
				$order );

			if ( ! $email_to ) {
				return;
			}

			$email_subject = apply_filters( 'wco_notification_email_subject',
				get_option( 'wc_wco_settings_notification_email_subject' ), $order );

			$email_body = apply_filters( 'wco_notification_email_body',
				get_option( 'wc_wco_settings_notification_email_body' ), $order );

			$email_headers = array(
				'Content-Type: text/html; charset=UTF-8'
			);
			$email_headers = apply_filters( 'wco_notification_email_headers', $email_headers, $order );

			$mail_result = wp_mail( $email_to, $email_subject, $email_body, $email_headers );

			do_action( 'wco_after_cancel_notification_email_sent_action', $mail_result, $order );
		}

		/**
		 * Parse text with order shortcodes for email
		 *
		 * @param $text
		 * @param $order
		 *
		 * @return mixed
		 */
		public function parse_text_with_order_fields( $text, $order ) {
			$replace_fields = array(
				'%%order_id%%'        => $order->get_id(),
				'%%customer_name%%'   => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'%%order_admin_url%%' => get_edit_post_link( $order->get_id() )
			);

			apply_filters( 'wco_parse_email_replace_fields', $replace_fields, $order );

			$replaces     = array_keys( $replace_fields );
			$replacesWith = array_values( $replace_fields );

			$parsed_text = str_replace( $replaces, $replacesWith, $text );

			return $parsed_text;
		}
	}
endif;

/**
 * Boot this plugin
 */
function wco_boot_plugin() {
	$cancelOrderClass = new WCOCancelOrder();
	$cancelOrderClass->init_plugin();

	if ( is_admin() ) {
		WCO_Woo_Settings::init();
	}
}

add_action( 'init', 'wco_boot_plugin' );