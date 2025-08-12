<form action="{{ route('payment.initiate') }}" method="POST">
    @csrf
    <button type="submit">pay</button>
</form>