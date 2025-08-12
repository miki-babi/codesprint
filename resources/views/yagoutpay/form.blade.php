<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to YagoutPay...</title>
</head>
<body>
    <p>Please wait while we redirect you to the YagoutPay secure payment page.</p>
    
    <form name="paymentForm" method="POST" enctype="application/x-www-form-urlencoded"
          action="{{ $postUrl }}">
        
        <!-- Merchant ID (me_id) is sent in plain text -->
        <input name="me_id" value="{{ $merchantId }}" type="hidden"> <!-- [3, 10] -->
        
        <!-- Encrypted transaction request payload -->
        <input name="merchant_request" value="{{ $merchant_request_encrypted }}" type="hidden"> <!-- [3] -->
        
        <!-- Hash parameter (Generation logic NOT provided in sources) -->
        <input name="hash" value="{{ $hash_value }}" type="hidden"> <!-- [3] -->
        
        <noscript>
            <input type="submit" name="submit" value="Click here to proceed if not redirected automatically">
        </noscript>
    </form>

    <script>
        // Automatically submit the form upon page load
        document.paymentForm.submit();
    </script>
</body>
</html>