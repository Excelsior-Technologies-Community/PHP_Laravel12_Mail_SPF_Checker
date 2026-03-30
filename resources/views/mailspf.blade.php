<!DOCTYPE html>
<html>
<head>
    <title>Mail SPF Checker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script>
        function copyToClipboard(id) {
            const el = document.getElementById(id);
            navigator.clipboard.writeText(el.innerText).then(() => {
                alert("Copied to clipboard!");
            });
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Mail SPF Checker</h2>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display SPF result --}}
    @if(session('result'))
        @php
            $message = session('result');
            $isGmail = str_contains($message, 'Gmail');
            $alertType = $isGmail ? 'danger' : (str_starts_with($message, '✅') ? 'success' : 'warning');
        @endphp
        <div class="alert alert-{{ $alertType }}">
            {!! $message !!}
        </div>

        {{-- Show TXT record suggestion if it's not Gmail --}}
        @if(!$isGmail && str_contains($message, 'Generate a TXT record'))
            <div class="mb-3">
                <label class="form-label">SPF TXT Record:</label>
                <pre id="spfRecord" style="background:#f8f9fa; padding:10px; border:1px solid #ced4da;">{!! $spfCheckerRecord ?? '' !!}</pre>
                <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('spfRecord')">Copy TXT Record</button>
            </div>
        @endif
    @endif

    <form method="POST" action="{{ route('mailspf.check') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="e.g. test@example.com" value="{{ old('email', $defaultEmail) }}">
        </div>

        <div class="mb-3">
            <label for="mailserver" class="form-label">Mail Server (Optional)</label>
            <input type="text" class="form-control" id="mailserver" name="mailserver"
                   placeholder="e.g. smtp.gmail.com" value="{{ old('mailserver', $defaultServer) }}">
            <small class="text-muted">Leave empty to use default server. Gmail addresses auto-fill smtp.gmail.com</small>
        </div>

        <button type="submit" class="btn btn-primary">Check SPF</button>
    </form>
</div>
</body>
</html>