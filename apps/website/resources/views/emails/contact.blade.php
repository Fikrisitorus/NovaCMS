{{--
    Template email untuk pesan yang diterima dari form kontak publik.
--}}
<p><strong>Nama:</strong> {{ $payload['name'] }}</p>
<p><strong>Email:</strong> {{ $payload['email'] }}</p>
<p><strong>Pesan:</strong></p>
<p style="white-space: pre-line;">{{ $payload['message'] }}</p>
