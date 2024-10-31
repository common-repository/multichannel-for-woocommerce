<?php
/*
 * Plugin Name: Multichannel for WooCommerce
 * Description: This plugin turns your WooCommerce into a multichannel order management hub, connecting to Walmart, Amazon, eBay, Overstock, Etsy, and other platforms.
 * Version: 1.0
 * Author: GeekSeller.com
 * Author URI: https://www.geekseller.com
 * Text Domain: multichannel-for-woocommerce
 * Domain Path: /i18n/languages/
 * License: GPL2+
 * Requires PHP: 5.6
 * Requires at least: 5.9
 * Tested up to: 6.1
 * WC requires at least: 5.8
 * WC tested up to: 7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcwc_load_scripts_and_styles() {
    wp_register_style( 'mcwc_style', plugins_url( 'assets/css/style.css', __FILE__ ) );
    wp_enqueue_style( 'mcwc_style' );
    wp_enqueue_script( 'mcwc_script', plugins_url( 'assets/js/admin.js', __FILE__ ), array('jquery') );
}

function mcwc_get_datetime_format() {
    $datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    // Add timezone abbreviation when default time format does not end with timezone
    if ( !in_array( substr( $datetime_format, -1 ), ['e', 'O', 'P', 'p', 'T', 'Z'] ) ) {
        $datetime_format .= ' T';
    }
    return $datetime_format;
}

function mcwc_add_admin_menu_item() {
    add_menu_page( 'Multichannel', 'Multichannel', 'manage_options', 'multichannel-for-woocommerce-settings', 'mcwc_settings', 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjIuODggMTE0LjU4Ij48dGl0bGU+cHJvZHVjdDwvdGl0bGU+PHBhdGggZmlsbD0id2hpdGUiIGQ9Ik0xMTguMTMsOS41NGEzLjI1LDMuMjUsMCwwLDEsMi4yLjQxLDMuMjgsMy4yOCwwLDAsMSwyLDNsLjU3LDc4LjgzYTMuMjksMy4yOSwwLDAsMS0xLjU5LDNMODkuMTIsMTEzLjkzYTMuMjksMy4yOSwwLDAsMS0yLC42NSwzLjA3LDMuMDcsMCwwLDEtLjUzLDBMMy4xMSwxMDUuMjVBMy4yOCwzLjI4LDAsMCwxLDAsMTAyVjIxLjc4SDBBMy4yOCwzLjI4LDAsMCwxLDIsMTguN0w0My44OS4yN2gwQTMuMTksMy4xOSwwLDAsMSw0NS42MywwbDcyLjUsOS41MVptLTM3LjI2LDEuNy0yNC42NywxNCwzMC4zOCwzLjg4LDIyLjUtMTQuMTgtMjguMjEtMy43Wm0tMjksMjBMNTAuNzUsNjQuNjIsMzguMjMsNTYuMDksMjUuNzIsNjMuMTdsMi41My0zNC45MUw2LjU1LDI1LjQ5Vjk5LjA1bDc3LjMzLDguNlYzNS4zNmwtMzItNC4wOVptLTE5LjctOS4wOUw1Ni4xMiw4LDQ1LjcsNi42MiwxNS4yNCwyMGwxNi45NSwyLjE3Wk05MC40NCwzNC40MXY3MS4xMmwyNS45LTE1LjQ0LS41Mi03MS42OC0yNS4zOCwxNloiLz48L3N2Zz4' );
}

function mcwc_add_action_links ( $actions ) {
    array_unshift( $actions, '<a href="' . admin_url( 'admin.php?page=multichannel-for-woocommerce-settings' ) . '">' . esc_html__( 'Settings' , 'multichannel-for-woocommerce' ) . '</a>' );
    return $actions;
}

function mcwc_shop_order_search_fields( $search_fields ) {
    if ( !is_admin() ) {
        return $search_fields;
    }
    return array_merge( $search_fields, array( '_external_order_id', '_alt_external_order_id' ) );
}

function mcwc_show_admin_notices() {
    if ( !mcwc_check_connection() ) {
        /* translators: %s is replaced with a link to the plugin settings */
        echo '<div class="error notice"><p>' . sprintf( esc_html__( 'Transform your WooCommerce store into a multichannel hub. Complete the setup of the %s plugin.', 'multichannel-for-woocommerce' ), '<strong><a href="' . admin_url( 'admin.php?page=multichannel-for-woocommerce-settings' ) . '">Multichannel for WooCommerce</a></strong>' ) . '</p></div>';
    }
}

function mcwc_init() {
    load_plugin_textdomain( 'multichannel-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
}

function mcwc_settings() {
    add_thickbox();

    echo '<div class="wrap"><h1 style="display: none;">Multichannel for WooCommerce</h1>';

    if (isset($_GET['registered']) && $_GET['registered'] == 1) {
        echo '<div class="notice notice-success"><p>' . esc_html__('Your connection with GeekSeller has been set up.', 'multichannel-for-woocommerce') . '</p></div>';
    }

    $connectionStatus = mcwc_check_connection();

    $contentFromGS = (array)(@json_decode(mcwc_get_content( ['notifications', 'main_info', 'how_to_use'] ), true));

    if ( isset($contentFromGS['notifications']) && is_array($contentFromGS['notifications']) && !empty($contentFromGS['notifications']) ) {
        foreach ($contentFromGS['notifications'] as $notification) {
            if ($notification['type'] == '_box') {
                echo wp_kses_post( $notification['message'] );
            } else {
                if (in_array($notification['type'], ['success', 'info', 'warning', 'error'])) {
                    $notice_class = 'notice-' . sanitize_html_class( $notification['type'] );
                } else {
                    $notice_class = '';
                }
                echo '<div class="notice ' . esc_attr( $notice_class ) . '"><p>' . esc_html($notification['message']) . '</p></div>';
            }
        }
    }

    if ( isset($contentFromGS['main_info']) && $contentFromGS['main_info'] ) {
        echo wp_kses_post( $contentFromGS['main_info'] );
    } else {
        echo '<div class="mcwc_container">
        <h1>Multichannel for WooCommerce</h1>
        <p>by <a href="https://www.geekseller.com/" target="_blank">GeekSeller.com</a></p>
            <p>
                <ul>
                    <li><strong>Your plan:</strong> Free<br>
                    <li><strong>Features:</strong> multichannel order management (more coming soon!)
                    <li><strong>Support:</strong> support@geekseller.com' . ($connectionStatus ? ' and <a id="mcwc_chat_link" href="#">Live Chat</a>' : '') . '
                </ul>
            </p>
        <p></p>
        </div>';
    }

    wp_nonce_field( 'mcwc-connect', 'mcwc_connect_nonce', false, true );
    wp_nonce_field( 'mcwc-manage-integrations', 'mcwc_manage_integrations_nonce', false, true );

    echo '<div class="mcwc_container">';
    if ($connectionStatus) {
        echo '<p>' . esc_html__( 'Plugin status:' , 'multichannel-for-woocommerce' ) . ' <span>&#9989;</span></p>';
        echo '<p><button class="mcwc_button" id="mcwc_manage_integrations">' . esc_html__( 'Manage integrations' , 'multichannel-for-woocommerce' ) . '</button></p>';
    } else {
        echo '<p>' . esc_html__( 'Plugin status:' , 'multichannel-for-woocommerce' ) . ' <span>&#10060;</span> ' . esc_html__( 'not registered' , 'multichannel-for-woocommerce' ) . '</p>';
        echo '<p><button class="mcwc_button" id="mcwc_connect">' . esc_html__( 'Register Now' , 'multichannel-for-woocommerce' ) . '</button></p>';
    }
    echo '</div>';

    echo '';

    if ( isset($contentFromGS['how_to_use']) && $contentFromGS['how_to_use'] ) {
        echo wp_kses_post( $contentFromGS['how_to_use'] );
    } else {
        echo '<div class="mcwc_container">
        <h3>Installation and Configuration</h3>
        <p><a href="https://youtu.be/8o4EAWgz8z4" target="_blank"><img src="' . plugins_url( 'assets/images/woo-installation-video.png', __FILE__ ) . '" class="mcwc_config_img" alt=""></a></p>
        <p><hr></p>
        <h3>How to use this plugin</h3>
        <strong>Step 1 - Installation</strong><br>Use the blue Manage Integrations button (above) to connect your marketplaces.<br><br>
        <strong>Step 2 - Import</strong><br>This plugin will import your orders to the Orders section of your WooCommerce store. If SKUs of products in your WooCommerce store and on marketplaces match, WooCommerce will automatically reduce your inventory in WooCommerce. <img src="' . plugins_url( 'assets/images/woo-step-order-import.png', __FILE__ ) . '" class="mcwc_config_img" alt=""><br><br>
        <strong>Step 3 - Fulfillment</strong><br>Provide a tracking number and carrier on your order. Provide a tracking number and carrier on your order. You can use the Order Shipment box that comes with our plugin, or use compatible <a href="https://woocommerce.com/products/shipment-tracking/" target="_blank">WooCommerce Shipment Tracking</a> or <a href="https://woocommerce.com/document/woocommerce-shipping-and-tax/" target="_blank">WooCommerce Shipping &amp; Tax</a> plugins.
        <img src="' . plugins_url( 'assets/images/woo-step-order-tracking.png', __FILE__ ) . '" class="mcwc_config_img" alt=""><br><br>
        <strong>Step 4 - Export</strong><br>This plugin will automatically take provided shipping information and submit them to the marketplaces.
        <img src="' . plugins_url( 'assets/images/woo-step-order-export.png', __FILE__ ) . '" class="mcwc_config_img" alt=""><br><br>
        </div>';
    }

    echo '</div>';

    echo '<div id="mcwc_tb" style="display:none;">
    <h2 id="mcwc_tb_title"></h2>
    <p id="mcwc_tb_content"></p>
    <div class="mcwc_tb_close_container"><input type="button" value="Close" class="button" onclick="tb_remove(); return false;"></div>
    </div>';
}

function mcwc_initialize_connection() {
    class GS_WC_Auth extends WC_Auth
    {
        public function generateAPIKeys()
        {
            return $this->create_keys('Multichannel for WooCommerce', get_current_user_id(), 'read_write');
        }
    }

    $wcAuth = new GS_WC_Auth();
    $apiKeys = $wcAuth->generateAPIKeys();

    if (empty($apiKeys['consumer_key']) || empty($apiKeys['consumer_secret'])) {
        echo json_encode([
            'success' => false,
            /* translators: %s is replaced with the email link */
            'message' => sprintf( esc_html__( 'API keys for Multichannel for WooCommerce could not be generated. Please contact %s to report it.', 'multichannel-for-woocommerce' ), '<a href="mailto:support@geekseller.com">support@geekseller.com</a>' ),
        ]);
        wp_die();
    }

    $args = array(
        'method'  => 'POST',
        'headers'  => array('Content-Type: application/x-www-form-urlencoded'),
        'body' => array(
            'woocommerce_api_url' => get_site_url(),
            'woocommerce_admin_email' => get_bloginfo('admin_email'),
            'woocommerce_username' => $apiKeys['consumer_key'],
            'woocommerce_password' => $apiKeys['consumer_secret'],
        )
    );

    $response = wp_remote_get( 'https://woo.geekseller.com/api/geekseller/register', $args );

    if (wp_remote_retrieve_response_code ( $response ) !== 200) {
        echo json_encode([
            'success' => false,
            /* translators: %s is replaced with the email link */
            'message' => sprintf( esc_html__( 'An error occurred while registering your account on GeekSeller. Please contact %s to report it.', 'multichannel-for-woocommerce' ), '<a href="mailto:support@geekseller.com">support@geekseller.com</a>' ),
        ]);
        wp_die();
    }

    $registeredUserData = json_decode( wp_remote_retrieve_body( $response ), true);

    if (!$registeredUserData || $registeredUserData['status'] !== 'OK' || !is_array($registeredUserData['user']) || empty($registeredUserData['user']['id']) || empty($registeredUserData['user']['woocommerce_geekseller_password'])) {
        echo json_encode([
            'success' => false,
            /* translators: %s is replaced with the email link */
            'message' => sprintf( esc_html__( 'An error occurred while registering your account on GeekSeller. Please contact %s to report it.', 'multichannel-for-woocommerce' ), '<a href="mailto:support@geekseller.com">support@geekseller.com</a>' ),
            'debug' => $registeredUserData,
        ]);
        wp_die();
    }

    add_option('geekseller_user_id', $registeredUserData['user']['id']);
    add_option('geekseller_password', $registeredUserData['user']['woocommerce_geekseller_password']);

    echo json_encode([
        'success' => true
    ]);

    wp_die();
}

function mcwc_remove_connection() {
    delete_option('geekseller_user_id');
    delete_option('geekseller_password');
}

function mcwc_manage_integrations() {
    $args = array(
        'method'  => 'POST',
        'headers'  => array('Content-Type: application/x-www-form-urlencoded'),
        'body' => array(
            'user_id' => get_option('geekseller_user_id'),
            'password' => get_option('geekseller_password'),
        )
    );

    $response = wp_remote_get( 'https://woo.geekseller.com/api/geekseller/integrations', $args );

    if (wp_remote_retrieve_response_code( $response ) != 200) {
        echo json_encode([
            'success' => false,
            /* translators: %s is replaced with the email link */
            'message' => sprintf( esc_html__( 'We could not redirect you to the Manage Integrations page. Please try again later, if the error persists please contact %s to report it.', 'multichannel-for-woocommerce'), '<a href="mailto:support@geekseller.com">support@geekseller.com</a>' ),
        ]);
        wp_die();
    }

    $redirectData = json_decode(wp_remote_retrieve_body( $response ), true);

    if ($redirectData === null || $redirectData['status'] !== 'OK' || empty($redirectData['url'])) {
        echo json_encode([
            'success' => false,
            /* translators: %s is replaced with the email link */
            'message' => sprintf( esc_html__( 'We could not redirect you to the Manage Integrations page. Please try again later, if the error persists please contact %s to report it.', 'multichannel-for-woocommerce'), '<a href="mailto:support@geekseller.com">support@geekseller.com</a>' ),
        ]);
        wp_die();
    }

    echo json_encode([
        'success' => true,
        'url' => sanitize_url($redirectData['url']),
    ]);

    wp_die();
}

function mcwc_save_shipment() {

    mcwc_check_nonce( 'mcwc-save-shipment' );

    if (empty($_POST['shipment']) || empty($_POST['shipment']['order_id'])) {
        echo json_encode([
            'success' => false,
            'message' => esc_html__( 'Missing shipment data', 'multichannel-for-woocommerce' ),
        ]);
        wp_die();
    }

    $order_id = (int) wc_clean( $_POST['shipment']['order_id'] );

    $shipment_date = wc_clean($_POST['shipment']['shipment_date']);
    $shipment_hour = (int) wc_clean($_POST['shipment']['shipment_hour']);
    $shipment_minute = (int) wc_clean($_POST['shipment']['shipment_minute']);
    if ( !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $shipment_date) || DateTime::createFromFormat('Y-m-d', $shipment_date) ) {
        $shipment_date = date('Y-m-d');
    }
    if ($shipment_hour < 0 || $shipment_hour > 23) {
        $shipment_hour = date('H');
    }
    if ($shipment_minute < 0 || $shipment_minute > 59) {
        $shipment_minute = date('i');
    }

    $shipment_datetime = get_gmt_from_date($shipment_date . 'T' . sprintf('%02d', $shipment_hour) . ':' . sprintf('%02d', $shipment_minute) . ':00' . wp_date('P'), 'c');

    $shipments = (array)get_post_meta( $order_id, '_mcwc_shipments', true );

    $shipment = [
        'id' => md5(rand().rand().rand().microtime()),
        'carrier' => wc_clean($_POST['shipment']['carrier']),
        'method' => wc_clean($_POST['shipment']['method']),
        'tracking_number' => wc_clean($_POST['shipment']['tracking_number']),
        'tracking_url' => sanitize_url($_POST['shipment']['tracking_url']),
        'shipment_date' => $shipment_datetime,
    ];

    $shipments[] = $shipment;

    $id = update_post_meta( $order_id, '_mcwc_shipments', $shipments );

    if ($id) {
        echo json_encode([
            'success' => true,
            'shipment' => $shipment,
            'shipment_html' => mcwc_get_shipment_html( $shipment ),
        ]);
    }

    if (isset($_POST['shipment']['set_order_completed']) && $_POST['shipment']['set_order_completed']) {
        $order = wc_get_order( $order_id );

        if ( $order ) {
            $order->update_status( 'completed' );
        }
    }

    wp_die();
}

function mcwc_delete_shipment() {
    mcwc_check_nonce( 'mcwc-delete-shipment' );

    if (empty($_POST['shipment']) || empty($_POST['shipment']['order_id'])) {
        echo json_encode([
            'success' => false,
            'message' => esc_html__( 'Missing shipment data', 'multichannel-for-woocommerce' ),
        ]);
        wp_die();
    }

    $order_id = (int) $_POST['shipment']['order_id'];
    $shipment_id = wc_clean( $_POST['shipment']['shipment_id'] );

    $shipments = (array)get_post_meta( $order_id, '_mcwc_shipments', true );

    foreach ($shipments as $i => $shipment) {
        if ( $shipment['id'] == $shipment_id ) {
            unset($shipments[$i]);
        }
    }

    $id = update_post_meta( $order_id, '_mcwc_shipments', $shipments );

    if ($id) {
        echo json_encode([
            'success' => true,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'id' => $id,
            'shipments' => $shipments,
        ]);
    }
    wp_die();
}

function mcwc_get_notifications( ) {
    $plugin_data = get_plugin_data( __FILE__ );

    $args = array(
        'method'  => 'POST',
        'headers'  => array('Content-Type: application/x-www-form-urlencoded'),
        'body' => array(
            'user_id' => get_option('geekseller_user_id'),
            'password' => get_option('geekseller_password'),
            'connection_status' => mcwc_check_connection(),
            'plugin_version' => $plugin_data['Version'],
            'lang' => get_locale(),
        )
    );

    return wp_remote_retrieve_body( wp_remote_get('https://woo.geekseller.com/api/geekseller/notifications', $args ) );
}

function mcwc_get_content( $content_name ) {
    $plugin_data = get_plugin_data( __FILE__ );

    $args = array(
        'method'  => 'POST',
        'headers'  => array('Content-Type: application/x-www-form-urlencoded'),
        'body' => array(
            'user_id' => get_option('geekseller_user_id'),
            'password' => get_option('geekseller_password'),
            'connection_status' => mcwc_check_connection(),
            'plugin_version' => $plugin_data['Version'],
            'content' => $content_name,
        )
    );

    return wp_remote_retrieve_body( wp_remote_get('https://woo.geekseller.com/api/geekseller/content', $args ) );
}

function mcwc_check_connection() {
    return !empty( get_option( 'geekseller_user_id' ) ) && !empty( get_option( 'geekseller_password' ) );
}

function mcwc_orders_list_add_columns($columns)
{
    $reordered_columns = array();

    foreach ( $columns as $key => $column ) {
        $reordered_columns[$key] = $column;
        if ( $key == 'order_status' ) {
            $reordered_columns['marketplace_status'] = esc_html__( 'Marketplace Status', 'multichannel-for-woocommerce' );
        }
        if ( $key == 'order_total' ) {
            $reordered_columns['source'] = esc_html__( 'Source', 'multichannel-for-woocommerce' );
        }
        if ( $key == 'order_total' ) {
            $reordered_columns['external_order_id'] = esc_html__( 'Marketplace Order ID', 'multichannel-for-woocommerce' );
        }
    }
    return $reordered_columns;
}

function mcwc_orders_list_add_columns_content( $column, $post_id )
{
    if ( $column == 'source' ) {
        $order_marketplace = get_post_meta( $post_id, '_source_marketplace', true );
        if (!empty($order_marketplace)) {
            echo esc_html(mcwc_get_marketplace_display_name($order_marketplace));
        } else {
            echo 'WooCommerce';
        }
    }

    if ( $column == 'external_order_id' ) {
        $external_order_id = get_post_meta( $post_id, '_external_order_id', true );
        if (!empty($external_order_id)) {
            echo esc_html($external_order_id);
        } else {
            echo '–';
        }
    }

    if ( $column == 'marketplace_status' ) {
        $marketplace_status = get_post_meta( $post_id, '_marketplace_status', true );
        if ( !empty($marketplace_status) ) {
            $markCSSClass = '';
            if ( strtolower($marketplace_status) == 'open' ) {
                $markCSSClass = 'status-processing';
            } elseif ( strtolower($marketplace_status) == 'inprogress' ) {
                $markCSSClass = 'status-processing';
            } elseif ( strtolower($marketplace_status) == 'shipped' ) {
                $markCSSClass = 'status-completed';
            } elseif ( strtolower($marketplace_status) == 'cancelled' ) {
                $markCSSClass = 'status-cancelled';
            }
            echo '<mark class="order-status ' . esc_attr( $markCSSClass ) . '"><span>' . esc_html( ucfirst($marketplace_status) ) . '</span></mark>';
        } else {
            echo '–';
        }
    }
}

function mcwc_check_order_has_external_tracking( $post ) {
    $wc_shipment_tracking_items = get_post_meta( $post->ID, '_wc_shipment_tracking_items', true );
    $wc_connect_labels = get_post_meta( $post->ID, 'wc_connect_labels', true );

    if ( is_array($wc_connect_labels) && !empty($wc_connect_labels) ) {
        return __( 'You already generated at least one shipping label for this order using WooCommerce Shipping & Tax plugin.', 'multichannel-for-woocommerce' );
    } elseif ( is_array($wc_shipment_tracking_items) && !empty($wc_shipment_tracking_items) ) {
        return __( 'You already added at least one tracking number for this order using WooCommerce Shipment Tracking.', 'multichannel-for-woocommerce' );
    }
    return null;
}

function mcwc_order_details_meta_box_callback( $post ) {
    $order_marketplace = get_post_meta( $post->ID, '_source_marketplace', true );
    $external_order_id = get_post_meta( $post->ID, '_external_order_id', true );
    $alt_external_order_id = get_post_meta( $post->ID, '_alt_external_order_id', true );
    $order_placed_date = get_post_meta( $post->ID, '_order_placed_date', true );
    $marketplace_status = get_post_meta( $post->ID, '_marketplace_status', true );
    $requested_shipping_carrier = get_post_meta( $post->ID, '_requested_shipping_carrier', true );
    $requested_shipping_method = get_post_meta( $post->ID, '_requested_shipping_method', true );
    $requested_ship_by_date = get_post_meta( $post->ID, '_requested_ship_by_date', true );
    $requested_delivery_by_date = get_post_meta( $post->ID, '_requested_delivery_date', true );

    $order_warnings = get_post_meta( $post->ID, '_order_warnings', true );
    if ( !empty($order_warnings['messages']) ) {
        foreach ( $order_warnings['messages'] as $order_warning ) {
            echo '<p class="mcwc_order_warning"><strong>' . esc_html__('Warning:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html($order_warning) . '</p>';
        }
    }

    if ( !empty($order_marketplace) ) {
        echo '<p><strong>' . esc_html__('Source:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html(mcwc_get_marketplace_display_name($order_marketplace)) . '</p>';
    }

    if ( !empty($external_order_id) ) {
        echo '<p><strong>' . esc_html__('Order ID:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html($external_order_id) . '</p>';
    }

    if ( !empty($alt_external_order_id) ) {
        echo '<p><strong>' . esc_html__('Alt Order ID:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html($alt_external_order_id) . '</p>';
    }

    if ( !empty($order_placed_date) ) {
        echo '<p><strong>' . esc_html__('Order date:', 'multichannel-for-woocommerce' ) . '</strong> ' . wp_date( mcwc_get_datetime_format(), strtotime(esc_html($order_placed_date)) ) . '</p>';
    }

    if ( !empty($order_placed_date) ) {
        echo '<p><strong>' . esc_html__('Marketplace status:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html(ucfirst($marketplace_status)) . '</p>';
    }

    if ( !empty($requested_shipping_carrier) ) {
        echo '<p><strong>' . esc_html__('Requested carrier:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html($requested_shipping_carrier) . '</p>';
    }

    if ( !empty($requested_shipping_method) ) {
        echo '<p><strong>' . esc_html__('Requested shipping method:', 'multichannel-for-woocommerce' ) . '</strong> ' . esc_html($requested_shipping_method) . '</p>';
    }

    if ( !empty($requested_ship_by_date) ) {
        echo '<p><strong>' . esc_html__('Ship by:', 'multichannel-for-woocommerce' ) . '</strong> ' . wp_date( mcwc_get_datetime_format(), strtotime(esc_html($requested_ship_by_date)) ) . '</p>';
    }

    if ( !empty($requested_delivery_by_date) ) {
        echo '<p><strong>' . esc_html__('Requested delivery date:', 'multichannel-for-woocommerce' ) . '</strong> ' . wp_date( mcwc_get_datetime_format(), strtotime(esc_html($requested_delivery_by_date)) ) . '</p>';
    }
}

function mcwc_get_shipment_html( $shipment )
{
    if ( empty($shipment) || !is_array($shipment) ) {
        return '';
    }
    return '<li class="note" rel="' . esc_attr($shipment['id']) . '">
        <div class="note_content">
        <p>
        <strong>' . esc_html($shipment['carrier']) . ' ' . esc_html($shipment['method']) . '</strong><br>
        '. ( !empty($shipment['tracking_url']) ? '<a href="' . esc_url($shipment['tracking_url']) . '" target="_blank">' . esc_html($shipment['tracking_number']) . '</a>' : esc_html($shipment['tracking_number']) ).'
        </p>
        </div>
        <p class="meta"><abbr class="exact-date" title="' . esc_attr($shipment['shipment_date']) . '">' . wp_date( mcwc_get_datetime_format(), strtotime(esc_html($shipment['shipment_date'])) ) . '</abbr> <a href="#" class="delete_note mcwc_delete_shipment" role="button">' . esc_html__('Delete', 'multichannel-for-woocommerce' ) . '</a>' . '</p>
        </li>';
}

function mcwc_order_shipment_meta_box_callback( $post ) {
    $CARRIERS = include plugin_dir_path( __FILE__ ) . 'carriers.php';
    $METHODS = include plugin_dir_path( __FILE__ ) . 'methods.php';

    $source_marketplace = get_post_meta( $post->ID, '_source_marketplace', true );
    $carriersList = isset($CARRIERS[$source_marketplace]) ? $CARRIERS[$source_marketplace] : $CARRIERS['_default'];
    $methodsList = isset($METHODS[$source_marketplace]) ? $METHODS[$source_marketplace] : $METHODS['_default'];

    $shipments = get_post_meta( $post->ID, '_mcwc_shipments', true );

    wp_nonce_field( 'mcwc-save-shipment', 'mcwc_save_shipment_nonce', false, true );
    wp_nonce_field( 'mcwc-delete-shipment', 'mcwc_delete_shipment_nonce', false, true );

    echo '<ul id="mcwc_shipments" class="order_notes">';
    $external_trackings = mcwc_check_order_has_external_tracking( $post );
    if ($external_trackings) {
        echo '<li class="note mcwc-tracking-info"><div class="note_content"><p>' . esc_html($external_trackings) . '</p></div></li>';
    }
    if (is_array($shipments)) {
        foreach ($shipments as $shipment) {
            echo mcwc_get_shipment_html($shipment);
        }
    }
    echo '</ul>';

    echo '<button id="mcwc_show_shipment_form" class="button" type="button">' . esc_html__('Add Tracking Number', 'multichannel-for-woocommerce' ) . '</button>';
    echo '<div id="mcwc_shipment_form">
        <input type="hidden" id="mcwc_shipment_order_id" name="order_id" value="' . get_the_ID() . '">
        <p>
		    <label for="mcwc_shipment_carrier">' . esc_html__('Carrier:', 'multichannel-for-woocommerce' ) . '</label>
		    <select name="shipment_carrier" id="mcwc_shipment_carrier">
		    <option value="">Select</option>';
            foreach ($carriersList as $carrier) {
                echo '<option value="' . esc_attr($carrier) . '">' . esc_html($carrier) . '</option>';
            }
            echo '<option value="other">Other</option>
            </select>
        </p>
        <p>
		    <input type="text" name="shipment_carrier_other" id="mcwc_shipment_carrier_other" style="display: none;">
		</p>
		<p>
		    <label for="mcwc_shipment_method">' . esc_html__('Method:', 'multichannel-for-woocommerce' ) . '</label>
            <select name="shipment_method" id="mcwc_shipment_method">
            <option value="">Select</option>';
            foreach ($methodsList as $method) {
                echo '<option value="' . esc_attr($method) . '">' . esc_html($method) . '</option>';
            }
            echo '<option value="other">Other</option>
            </select>
		</p>
        <p>
		    <input type="text" name="shipment_method_other" id="mcwc_shipment_method_other" style="display: none;">
		</p>
		<p>
		    <label for="mcwc_tracking_number">' . esc_html__('Tracking number:', 'multichannel-for-woocommerce' ) . '</label>
		    <input type="text" name="tracking_number" id="mcwc_tracking_number">
		</p>
		<p id="mcwc_tracking_url_field" style="display: none;">
		    <label for="mcwc_tracking_url">' . esc_html__('Tracking URL:', 'multichannel-for-woocommerce' ) . '</label>
		    <input type="text" name="tracking_url" id="mcwc_tracking_url">
		</p>
		<p>
		    <label for="mcwc_shipment_date">' . /* translators: %s is replaced with the time zone abbreviation or offset */ sprintf( esc_html__('Shipment date (%s):', 'multichannel-for-woocommerce' ), wp_date('T') ) . '</label>
		    <input type="text" class="date date-picker" name="shipment_date" id="mcwc_shipment_date" maxlength="10" value="' . wp_date('Y-m-d') . '" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
		    @
		    <input type="number" class="hour" placeholder="h" name="shipment_hour" id="mcwc_shipment_hour" min="0" max="23" step="1" value="' . wp_date('H') . '" pattern="([01]?[0-9]{1}|2[0-3]{1})">
		    :
		    <input type="number" class="minute" placeholder="m" name="shipment_minute" id="mcwc_shipment_minute" min="0" max="59" step="1" value="' . wp_date('i') . '" pattern="[0-5]{1}[0-9]{1}">
		</p>
		<p class="checkbox-field">
		    <input type="checkbox" id="mcwc_shipment_set_order_completed" name="shipment_set_order_completed" value="1" checked>
		    <label for="mcwc_shipment_set_order_completed">' . esc_html__('Change order status to Completed', 'multichannel-for-woocommerce' ) . '</label>
		</p>
		<button type="button" class="button button-primary" id="mcwc_save_shipment" name="save_shipment" value="Save">' . esc_html__('Save', 'multichannel-for-woocommerce' ) . '</button>
    </div>';
}

function mcwc_order_details_add_meta_box( $post_type, $post ) {
    add_meta_box(
        'mcwc_order_details', esc_html__( 'Order Details', 'multichannel-for-woocommerce' ),
        'mcwc_order_details_meta_box_callback',
        'shop_order',
        'side',
        'high'
    );

    add_meta_box(
        'mcwc_order_shipment', esc_html__( 'Order Shipment', 'multichannel-for-woocommerce' ),
        'mcwc_order_shipment_meta_box_callback',
        'shop_order',
        'side',
        'high'
    );
}

function mcwc_notify_active_state( $active ) {
    if ( !mcwc_check_connection() ) {
        return;
    }

    $args = array(
        'method'  => 'POST',
        'headers'  => array('Content-Type: application/x-www-form-urlencoded'),
        'body' => array(
            'user_id' => get_option('geekseller_user_id'),
            'password' => get_option('geekseller_password'),
            'integration_active' => (bool) $active,
        )
    );

    wp_remote_get( 'https://woo.geekseller.com/api/geekseller/activation', $args );
}

function mcwc_activate_account() {
    mcwc_notify_active_state(true);
}

function mcwc_deactivate_account() {
    mcwc_notify_active_state(false);
}

function mcwc_add_orders_filters_fields() {
    if ( !isset($_GET['post_type']) || $_GET['post_type'] !== 'shop_order' ) {
        return;
    }

    global $wpdb;
    $all_source_marketplaces = $wpdb->get_results("SELECT DISTINCT pm.meta_value FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON p.ID = pm.post_id
			WHERE pm.meta_key = '_source_marketplace' 
			AND p.post_type = 'shop_order'
			ORDER BY pm.meta_value ASC", ARRAY_A);

    if ( isset( $_GET['_source_marketplace'] ) ) {
        $_source_marketplace = sanitize_text_field($_GET['_source_marketplace']);
    } else {
        $_source_marketplace = '';
    }

    echo '<select name="_source_marketplace">
        <option value="">' . esc_html__('Filter by source', 'multichannel-for-woocommerce' ) . '</option>
        <option value="woocommerce" ' . ( $_source_marketplace == 'woocommerce' ? 'selected' : '' ) . '>WooCommerce</option>';
    foreach ( $all_source_marketplaces as $source_marketplace ) {
        echo '<option value="' . esc_attr($source_marketplace['meta_value']) . '" ' . ( $_source_marketplace == $source_marketplace['meta_value'] ? 'selected' : '' ) . '>' . esc_html(ucfirst($source_marketplace['meta_value'])) . '</option>';
    }
    echo '</select>';

    if ( isset( $_GET['_marketplace_status'] ) ) {
        $_marketplace_status = sanitize_text_field($_GET['_marketplace_status']);
    } else {
        $_marketplace_status = '';
    }

    echo '<select name="_marketplace_status">
        <option value="">' . esc_html__('Filter by marketplace status', 'multichannel-for-woocommerce' ) . '</option>
        <option value="Open" ' . ( $_marketplace_status == 'Open' ? 'selected' : '' ) . '>' . esc_html__('Open', 'multichannel-for-woocommerce' ) . '</option>
        <option value="In progress" ' . ( $_marketplace_status == 'In progress' ? 'selected' : '' ) . '>' . esc_html__('In progress', 'multichannel-for-woocommerce' ) . '</option>
        <option value="Shipped" ' . ( $_marketplace_status == 'Shipped' ? 'selected' : '' ) . '>' . esc_html__('Shipped', 'multichannel-for-woocommerce' ) . '</option>
        <option value="Cancelled" ' . ( $_marketplace_status == 'Cancelled' ? 'selected' : '' ) . '>' . esc_html__('Cancelled', 'multichannel-for-woocommerce' ) . '</option>
    </select>';
}

function mcwc_add_orders_filters( $query ) {
    if ( !is_admin() || $query->query['post_type'] !== 'shop_order' ) {
        return;
    }

    $meta_query = array();

    if ( !empty($_GET['_source_marketplace']) ) {
        $_source_marketplace = sanitize_text_field($_GET['_source_marketplace']);
        if ( strtolower( $_source_marketplace ) != 'woocommerce' ) {
            $meta_query[] = array(
                'key' => '_source_marketplace',
                'value' => $_source_marketplace,
                'compare' => '='
            );
        } else {
            $meta_query[] = array(
                'key' => '_source_marketplace',
                'compare' => 'NOT EXISTS'
            );
        }
    }

    if ( !empty($_GET['_marketplace_status']) ) {
        $_marketplace_status = sanitize_text_field($_GET['_marketplace_status']);
        $meta_query[] = array(
            'key' => '_marketplace_status',
            'value' => $_marketplace_status,
            'compare' => '='
        );
    }

    $query->set( 'meta_query', $meta_query );
}

function mcwc_get_marketplace_display_name( $marketplace ) {
    $marketplaces = [
        'amazon' => 'Amazon',
        'ebay' => 'eBay',
        'walmart' => 'Walmart',
        'woocommerce' => 'WooCommerce',
        'shopify' => 'Shopify',
        'etsy' => 'Etsy',
        'bigcommerce' => 'BigCommerce',
        'walmartca' => 'Walmart CA',
        'walmartdsv' => 'Walmart DSV',
        'overstock' => 'Overstock',
        'bluefly' => 'Bluefly',
        'googleexpress' => 'Google',
        'fba' => 'FBA',
        'fbm' => 'FBM',
        'mercadolibre' => 'Mercado Libre'
    ];
    if ( isset($marketplaces[$marketplace]) && !empty($marketplaces[$marketplace]) ) {
        return $marketplaces[$marketplace];
    }
    return ucfirst( $marketplace );
}

function mcwc_check_nonce( $action ) {
    if ( !isset( $_REQUEST['security'] ) || !wp_verify_nonce( sanitize_text_field($_REQUEST['security']), $action ) ) {
        echo json_encode([
            'success' => false,
            'message' => esc_html__( 'Invalid nonce', 'multichannel-for-woocommerce' ),
        ]);
        wp_die();
    }
}


register_activation_hook( __FILE__, 'mcwc_activate_account' );

register_deactivation_hook( __FILE__, 'mcwc_deactivate_account' );

add_action( 'init', 'mcwc_init' );

add_action( 'admin_enqueue_scripts', 'mcwc_load_scripts_and_styles' );

add_action( 'add_meta_boxes', 'mcwc_order_details_add_meta_box', 10, 2 );

add_filter( 'woocommerce_shop_order_search_fields', 'mcwc_shop_order_search_fields', 10, 1 );

add_filter( 'manage_edit-shop_order_columns', 'mcwc_orders_list_add_columns', 20 );

add_action( 'manage_shop_order_posts_custom_column' , 'mcwc_orders_list_add_columns_content', 20, 2 );

add_action( 'admin_menu', 'mcwc_add_admin_menu_item');

add_action( 'admin_notices', 'mcwc_show_admin_notices' );

add_action( 'wp_ajax_mcwc_initialize_connection', 'mcwc_initialize_connection' );

add_action( 'wp_ajax_mcwc_manage_integrations', 'mcwc_manage_integrations' );

add_action( 'wp_ajax_mcwc_save_shipment', 'mcwc_save_shipment' );

add_action( 'wp_ajax_mcwc_delete_shipment', 'mcwc_delete_shipment' );

add_action( 'restrict_manage_posts', 'mcwc_add_orders_filters_fields' );

add_action( 'pre_get_posts', 'mcwc_add_orders_filters', 10, 1);

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'mcwc_add_action_links' );
