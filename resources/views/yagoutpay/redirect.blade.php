<form method="POST" action="{{ $paymentUrl }}">
    <input type="hidden" name="me_id" value="{{ $merchantId }}">
    <input type="hidden" name="merchant_request" value="{{ $merchantRequest }}">
    <input type="hidden" name="hash" value="{{ $hash }}">
    <button type="submit">Pay Now</button>
</form>
<script>
    // Auto-submit the form
    document.forms[0].submit();
</script>
