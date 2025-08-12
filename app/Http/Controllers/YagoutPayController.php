<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;



class YagoutPayController extends Controller
{


    /**
     * PHP encryption function (as provided by YagoutPay sources)
     * You might put this in a helper file, a service class, or directly here for demonstration.
     * @param string $text The plain text to encrypt
     * @param string $key The base64-encoded encryption key
     * @param int $type The type parameter (not explicitly used in source logic but part of function signature)
     * @return string Base64 encoded encrypted string
     */
    private function encrypt($text, $key, $type)
    { // [4]
        $iv = "0123456789abcdef"; // Static IV [4]
        $size = 16;
        $pad = $size - (strlen($text) % $size);
        $padtext = $text . str_repeat(chr($pad), $pad);
        $crypt = openssl_encrypt($padtext, "AES-256-CBC", base64_decode($key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv); // [4]
        return base64_encode($crypt); // [4]
    }
    private function generateSha256Hash($input)
    { // [5]
        return hash('sha256', $input); // [5]
    }
    public function initiatePayment(Request $request)
    {
        // --- Configuration Values (Shared by YagoutPay upon registration) [5, 6] ---
        // $merchantId = "YOUR_MERCHANT_ID"; // Example: "202504290002" [7, 8]
        // $encryptionKey = "YOUR_ENCRYPTION_KEY"; // Example: "neTdYIKd87JEj4C6ZoYjaeBiCoeOr40ZKBEI8EU/8lo=" [5, 6]
        $postUrl = "https://uatcheckout.yagoutpay.com/ms-transaction-core-1-0/paymentRedirection/checksumGatewayPage"; // UAT URL [5, 6]
        $merchantId = env('YAGOUT_MERCHANT_ID', '202504290002');
        $encryptionKey = env('YAGOUT_MERCHANT_KEY', 'neTdYIKd87JEj4C6ZoYjaeBiCoeOr40ZKBEI8EU/8lo=');
        // $merchantKeyRaw = base64_decode($merchantKeyBase64, true);
        $iv = "0123456789abcdef";
        // --- Transaction Details (Txn_Details - Required Parameters) ---
        $ag_id = "yagout"; // Static value [8]
        $order_no = "ORDER_" . uniqid(); // Generate a unique ID per request [8]
        $amount = "1.00"; // Transaction amount, up to two decimal places. E.g., "150.00" [8]
        $country = "ETH"; // Country code: "ETH" for Ethiopia [8]
        $currency = "ETB"; // Currency: "ETB" for Ethiopian Birr [9]
        $txn_type = "SALE"; // Static value "SALE" [9]
        $success_url = route('payment.callback.success'); // Your success return URL [9]
        $failure_url = route('payment.callback.fail'); // Your failure return URL [9]
        $channel = "WEB"; // "WEB" for website, "MOBILE" for mobile app [9]

        // Construct Txn_Details string. Each attribute separated by pipe (|). [10]
        $txn_details = implode('|', [
            $ag_id,
            $merchantId,
            $order_no,
            $amount,
            $country,
            $currency,
            $txn_type,
            $success_url,
            $failure_url,
            $channel
        ]);

        // --- Payment Gateway Details (pg_details - Optional, Must be blank for Aggregator Hosted) ---
        // Values must be blank for Aggregator Hosted (Non-Seamless) [11]
        $pg_id = "";
        $paymode = "";
        $scheme = "";
        $wallet_type = "";
        $pg_details = implode('|', [$pg_id, $paymode, $scheme, $wallet_type]);

        // --- Card Details (card_details - Optional, Must be blank for Aggregator Hosted) ---
        // Values must be blank for Aggregator Hosted (Non-Seamless) [12]
        $card_no = "";
        $exp_month = "";
        $exp_year = "";
        $cvv = "";
        $card_name = "";
        $card_details = implode('|', [$card_no, $exp_month, $exp_year, $cvv, $card_name]);

        // --- Customer Details (cust_details - email_id and mobile_no are Mandatory) ---
        $cust_name = "John Doe"; // Optional [13]
        $email_id = "customer@example.com"; // Mandatory for fraud protection [13]
        $mobile_no = "0967072576"; // Mandatory for fraud protection [13]
        $unique_id = ""; // Required for stored card feature, leave blank if not used [13]
        $is_logged_in = "Y"; // Pass "Y" if user is logged in [13]
        $cust_details = implode('|', [$cust_name, $email_id, $mobile_no, $unique_id, $is_logged_in]);

        // --- Billing Details (Bill_details - Optional) ---
        // Required for fraud protection [14]
        $bill_address = "";
        $bill_city = "";
        $bill_state = "";
        $bill_country = "";
        $bill_zip = "";
        $bill_details = implode('|', [$bill_address, $bill_city, $bill_state, $bill_country, $bill_zip]);

        // --- Shipping Details (Ship_details - Optional, for physical delivery) ---
        // Required in case of physical delivery of goods [15, 16]
        $ship_state = "";
        $ship_country = "";
        $ship_zip = "";
        $ship_days = "";
        $address_count = "";
        $ship_address = "";
        $ship_city = "";
        $ship_details = implode('|', [$ship_state, $ship_country, $ship_zip, $ship_days, $address_count, $ship_address, $ship_city]);

        // --- Item Details (Item_details - Optional) ---
        $item_count = ""; // Count of items [17]
        $item_value = ""; // Comma-separated item-wise cost [17]
        $item_category = ""; // Comma-separated item category (e.g., “DVD”) [16]
        $item_details = implode('|', [$item_count, $item_value, $item_category]);

        // --- UPI Details (UPI_details - Section mentioned, no parameters specified in sources) --- [10]
        $upi_details = "";

        // --- User Defined Fields (UDFs - Optional, for additional info) ---
        // Can be used for sending additional information and will be sent back in the response [10, 18, 19]
        $udf_1 = "";
        $udf_2 = "";
        $udf_3 = "";
        $udf_4 = "";
        $udf_5 = "";
        $other_details = implode('|', [$udf_1, $udf_2, $udf_3, $udf_4, $udf_5]); // Assuming 'other_details' refers to UDFs

        // Combine all sections with tilde (~) separator [1]
        $all_values = $txn_details . '~' . $pg_details . '~' . $card_details . '~' . $cust_details . '~' . $bill_details . '~' . $ship_details . '~' . $item_details . '~' . $upi_details . '~' . $other_details;

        // Encrypt the combined string to form 'merchant_request' [1]
        $merchant_request_encrypted = $this->encrypt($all_values, $encryptionKey, 256);

        // --- Hash Generation Logic ---
        // Step 1: Create the hash input string [2]
        // Example: "202506240002~ORDER_76e05de1~10~ETH~ETB" [2]
        // CURRENCY_FROM maps to 'country' and CURRENCY_TO maps to 'currency' from Txn_Details.
        $hash_input = implode('~', [
            $merchantId,
            $order_no,
            $amount,
            $country, // CURRENCY_FROM [5]
            $currency  // CURRENCY_TO [5]
        ]);

        // Step 2: Create the SHA-256 hash [5]
        $sha256_hash = $this->generateSha256Hash($hash_input);

        // Step 3: Encrypt the SHA-256 hash using AES-CBC [20]
        $hash_value = $this->encrypt($sha256_hash, $encryptionKey, 256); // [20]

        // Pass the prepared data to the view
        return view('yagoutpay.form', compact(
            'merchantId',
            'merchant_request_encrypted',
            'hash_value',
            'postUrl'
        ));
    }
}
