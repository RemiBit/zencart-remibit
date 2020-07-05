<?php
/*
RemiBit Payment Module
Modified April 15th 2020 by Blockchain Remittance Ltd.
Adapted to handle calls to RemiBit API.
*/

/**
 * @package paymentMethod
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:30:21 2018 -0500 Modified in v1.5.6 $
 */
class remibit
{
    var $code, $title, $description, $enabled;

// class constructor
    function __construct()
    {
        $this->code = 'remibit';
        $this->title = MODULE_PAYMENT_REMIBIT_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_REMIBIT_TEXT_DESCRIPTION;
        $this->api_version = '1.0';
        if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_PAYMENT_REMIBIT_TEXT_TITLE; // Payment module title in Admin
        }
        $this->sort_order = defined('MODULE_PAYMENT_REMIBIT_SORT_ORDER') ? MODULE_PAYMENT_REMIBIT_SORT_ORDER : null;
        $this->enabled = (defined('MODULE_PAYMENT_REMIBIT_STATUS') && MODULE_PAYMENT_REMIBIT_STATUS == 'True');
        if (null === $this->sort_order) return;
        if (defined('MODULE_PAYMENT_REMIBIT_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_REMIBIT_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_REMIBIT_ORDER_STATUS_ID;
        }
    }


    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        return array('id' => $this->code,
            'module' => $this->title);
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        return false;
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        if (isset($_POST['x_trans_id']) && isset($_POST['x_invoice_num'])) {
            if ($this->validate()) {
                return true;
            } else {
                zen_redirect(zen_href_link('checkout_payment', '', 'SSL'));
            }
        } else {
            global $order;
            $currency = $_SESSION['currency'];
            $order_id = $_SESSION['cartID'];
            $timeStamp = time();
            $order_total = number_format($order->info['total'], 2);
            $transactionKey = MODULE_PAYMENT_REMIBIT_TRANSACTION_KEY;
            if (function_exists('hash_hmac')) {
                $hash_d = hash_hmac('md5', sprintf('%s^%s^%s^%s^%s',
                    MODULE_PAYMENT_REMIBIT_LOGIN_ID,
                    $order_id,
                    $timeStamp,
                    $order_total,
                    $currency
                ), $transactionKey);
            } else {
                $hash_d = bin2hex(mhash(MHASH_MD5, sprintf('%s^%s^%s^%s^%s',
                    MODULE_PAYMENT_REMIBIT_LOGIN_ID,
                    $order_id,
                    $timeStamp,
                    $order_total,
                    $currency
                ), $transactionKey));
            }

            $params = array(
                'x_login' => MODULE_PAYMENT_REMIBIT_LOGIN_ID,
                'x_amount' => $order_total,
                'x_invoice_num' => $order_id,
                'x_relay_response' => 'TRUE',
                'x_fp_sequence' => $order_id,
                'x_fp_hash' => $hash_d,
                'x_show_form' => 'PAYMENT_FORM',
                'x_version' => $this->api_version,
                'x_type' => 'AUTH_CAPTURE',
                'x_relay_url' => zen_href_link('extras/remibit/callback.php', '', 'SSL', true, false, true),
                'x_currency_code' => $currency,
                'x_fp_timestamp' => $timeStamp,
                'x_first_name' => $order->billing['firstname'],
                'x_last_name' => $order->billing['lastname'],
                'x_company' => $order->billing['company'],
                'x_address' => $order->billing['street_address'],
                'x_city' => $order->billing['city'],
                'x_state' => $order->billing['state'],
                'x_zip' => $order->billing['postcode'],
                'x_country' => $order->billing['country']['title'],
                'x_phone' => $order->customer['telephone'],
                'x_email' => $order->customer['email_address'],
                'x_tax' => number_format($order->info['tax'], 2),
                'x_cancel_url' => zen_href_link('checkout_payment', '', 'SSL'),
                'x_cancel_url_text' => 'Cancel Payment',
                'x_test_request' => 'FALSE',
                'x_ship_to_first_name' => $order->delivery['firstname'],
                'x_ship_to_last_name' => $order->delivery['lastname'],
                'x_ship_to_company' => $order->delivery['company'],
                'x_ship_to_address' => $order->delivery['street_address'],
                'x_ship_to_city' => $order->delivery['city'],
                'x_ship_to_state' => $order->delivery['state'],
                'x_ship_to_zip' => $order->delivery['postcode'],
                'x_ship_to_country' => $order->delivery['country']['title'],
                'x_freight' => number_format($order->info['shipping_cost'], 2)
            );

            $post_string = array();

            foreach ($params as $key => $value) {
                $post_string[] = "<input type='hidden' name='$key' value='$value'/>";
            }

            $gateway_url = MODULE_PAYMENT_REMIBIT_GATEWAY_URL;

            $this->sendTransactionToGateway($gateway_url, $post_string);
        }
        return true;
    }


    function validate()
    {
        if(isset($_POST['x_trans_id'])){
            $hashData = implode('^', [
                $_POST['x_trans_id'],
                $_POST['x_test_request'],
                $_POST['x_response_code'],
                $_POST['x_auth_code'],
                $_POST['x_cvv2_resp_code'],
                $_POST['x_cavv_response'],
                $_POST['x_avs_code'],
                $_POST['x_method'],
                $_POST['x_account_number'],
                $_POST['x_amount'],
                $_POST['x_company'],
                $_POST['x_first_name'],
                $_POST['x_last_name'],
                $_POST['x_address'],
                $_POST['x_city'],
                $_POST['x_state'],
                $_POST['x_zip'],
                $_POST['x_country'],
                $_POST['x_phone'],
                $_POST['x_fax'],
                $_POST['x_email'],
                $_POST['x_ship_to_company'],
                $_POST['x_ship_to_first_name'],
                $_POST['x_ship_to_last_name'],
                $_POST['x_ship_to_address'],
                $_POST['x_ship_to_city'],
                $_POST['x_ship_to_state'],
                $_POST['x_ship_to_zip'],
                $_POST['x_ship_to_country'],
                $_POST['x_invoice_num'],
            ]);

            $digest = strtoupper(HASH_HMAC('sha512', "^" . $hashData . "^", hex2bin(MODULE_PAYMENT_REMIBIT_SIGNATURE_KEY)));
            if ($_POST['x_response_code'] != '' && (strtoupper($_POST['x_SHA2_Hash']) == $digest)) {
                return true;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }


    function sendTransactionToGateway($url, $parameters)
    {
        $loading = ' <div style="width: 100%; height: 100%;top: 50%; padding-top: 10px;padding-left: 10px;  left: 50%; transform: translate(40%, 40%)"><div style="width: 150px;height: 150px;border-top: #CC0000 solid 5px; border-radius: 50%;animation: a1 2s linear infinite;position: absolute"></div> </div> <style>*{overflow: hidden;}@keyframes a1 {to{transform: rotate(360deg)}}</style>';

        $html_form = '<form action="' . $url . '" method="post" id="authorize_payment_form">' . implode('', $parameters) . '<input type="submit" id="submit_authorize_payment_form" style="display: none"/>' . $loading . '</form><script>document.getElementById("submit_authorize_payment_form").click();</script>';

        echo $html_form;
        die();
    }

    function after_process()
    {
        global $insert_id;

        zen_update_orders_history($insert_id,'Payment successful. Ref Number/Transaction ID: '.$_POST['x_trans_id'], 'now()',-1);
    }

    function get_error()
    {
        return false;
    }

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_REMIBIT_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    function install()
    {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_REMIBIT_STATUS')) {
            $messageStack->add_session('remibit module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=remibit', 'NONSSL'));
            return 'failed';
        }
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable RemiBit Module', 'MODULE_PAYMENT_REMIBIT_STATUS', 'True', 'Do you want to accept RemiBit payment method?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_REMIBIT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login ID', 'MODULE_PAYMENT_REMIBIT_LOGIN_ID', '', 'The Login ID used for your RemiBit account.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Key', 'MODULE_PAYMENT_REMIBIT_TRANSACTION_KEY', '', 'The Transaction Key used for your RemiBit account.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Signature Key', 'MODULE_PAYMENT_REMIBIT_SIGNATURE_KEY', '', 'The Signature Key used for your RemiBit account.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MD5 Hash Value', 'MODULE_PAYMENT_REMIBIT_MD5_HASH', '', 'The MD5 Hash used for your RemiBit account.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Endpoint URL', 'MODULE_PAYMENT_REMIBIT_GATEWAY_URL', 'https://app.remibit.com/pay', 'The Endpoint URL used for your RemiBit account.', '6', '0', now())");

    }

    function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array('MODULE_PAYMENT_REMIBIT_STATUS', 'MODULE_PAYMENT_REMIBIT_SORT_ORDER', 'MODULE_PAYMENT_REMIBIT_LOGIN_ID', 'MODULE_PAYMENT_REMIBIT_TRANSACTION_KEY', 'MODULE_PAYMENT_REMIBIT_SIGNATURE_KEY', 'MODULE_PAYMENT_REMIBIT_MD5_HASH', 'MODULE_PAYMENT_REMIBIT_GATEWAY_URL');
    }
}
