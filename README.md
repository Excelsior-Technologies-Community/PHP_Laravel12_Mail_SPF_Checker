# PHP_Laravel12_Mail_SPF_Checker

## Introduction

Mail SPF Checker is a Laravel 12 web application that allows users to check whether an email address can potentially send emails via a specified mail server using SPF (Sender Policy Framework) records.

It performs basic SPF validation by analyzing DNS TXT records and helps developers and learners understand SPF configuration, email deliverability, and spoofing prevention.

---

## Project Overview

This project provides:

- A simple web form to enter an email address and mail server.

- Basic SPF record validation by checking DNS TXT records of the domain.

- Special handling for Gmail addresses (since SPF cannot be modified for Gmail).

- Suggestions on how to configure SPF for non-Gmail domains.

- Visual feedback:
   Green if sending is allowed
   Red if sending is not allowed

- Optional copy-to-clipboard feature for suggested TXT records.

It is designed for learning, local development, and demonstration purposes, and does not implement full SPF parsing (such as include/redirect mechanisms).

---

# Project Setup

## Step 1: Create Laravel 12 Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Mail_SPF_Checker "12.*"
cd PHP_Laravel12_Mail_SPF_Checker
```

---

## Step 2: Setup Database

Update .env

```.env
APP_NAME=MailSPFChecker
APP_ENV=local
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=true
APP_URL=http://localhost

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Step 3: Package Installation 

Run:

```bash
composer require dietercoopman/mailspfchecker
```

---

## Step 4: Configuration

Create config/mailspf.php:

```php
<?php

return [
    'default_mailserver' => env('MAIL_HOST', 'smtp.gmail.com'),
];
```

---

## Step 5: Controller 

Run:

```bash
php artisan make:controller MailSPFController
```

File: `app/Http/Controllers/MailSPFController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SPFCheckerService;

class MailSPFController extends Controller
{
    protected SPFCheckerService $spfChecker;

    public function __construct(SPFCheckerService $spfChecker)
    {
        $this->spfChecker = $spfChecker;
    }

    // Show the form
    public function index()
    {
        return view('mailspf', [
            'defaultEmail' => config('mail.from.address'),
            'defaultServer' => config('mail.host')
        ]);
    }

    // Handle form submission
    public function check(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'mailserver' => 'nullable|string',
        ]);

        $email = $request->email ?? config('mail.from.address');
        $mailserver = $request->mailserver ?? config('mail.host');

        $domain = substr(strrchr($email, "@"), 1);

        // Gmail check
        if (in_array(strtolower($domain), ['gmail.com', 'googlemail.com'])) {
            $message = "❌ You cannot send email with this address ({$email}) via {$mailserver}. "
                . "You cannot modify SPF for Gmail addresses. Use Gmail SMTP with authentication.";
            return back()->with('result', $message)->withInput();
        }

        // Non-Gmail: run SPF check
        $canSend = $this->spfChecker->using($mailserver)->canISendAs($email);
        $spfRecord = $this->spfChecker->howCanISendAs($email);

        $message = $canSend
            ? "✅ You can send email with this address ({$email}) via {$mailserver}."
            : "❌ You cannot send email with this address ({$email}) via {$mailserver}. $spfRecord";

        return back()->with([
            'result' => $message,
            'spfCheckerRecord' => $spfRecord
        ])->withInput();
    }
}
```

---

## Step 6: Service 

File: app/Services/SPFCheckerService.php

```php
<?php

namespace App\Services;

class SPFCheckerService
{
    protected string $mailerHost = 'smtp.gmail.com'; // default

    public function using(string $mailserver): self
    {
        $this->mailerHost = $mailserver ?: 'smtp.gmail.com';
        return $this;
    }

    /**
     * Check if email can be sent via the specified mail server
     */
    public function canISendAs(string $email): bool
    {
        if (empty($this->mailerHost)) {
            return false;
        }

        $domain = $this->extractDomain($email);

        // Gmail/Google Workspace
        if ($this->isGoogleDomain($domain)) {
            return false;
        }

        // SAFE DNS CALL (avoid crash)
        $spfRecord = @dns_get_record($domain, DNS_TXT);

        if (!$spfRecord) {
            return false;
        }

        // Normalize mail server (remove smtp.)
        $cleanHost = str_replace('smtp.', '', strtolower($this->mailerHost));

        foreach ($spfRecord as $txt) {
            if (isset($txt['txt'])) {
                $record = strtolower($txt['txt']);

                // Match both full host and cleaned host
                if (
                    stripos($record, strtolower($this->mailerHost)) !== false ||
                    stripos($record, $cleanHost) !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Give suggestions on how to send
     */
    public function howCanISendAs(string $email): string
    {
        $domain = $this->extractDomain($email);

        if ($this->isGoogleDomain($domain)) {
            return "You cannot modify SPF for Gmail addresses. Use Gmail SMTP with authentication.";
        }

        return "Generate a TXT record for {$domain} with value: v=spf1 include:{$this->mailerHost} -all";
    }

    protected function extractDomain(string $email): string
    {
        return substr(strrchr($email, "@"), 1);
    }

    protected function isGoogleDomain(string $domain): bool
    {
        return in_array(strtolower($domain), ['gmail.com', 'googlemail.com']);
    }
}
```

## Step 7: Blade View 

File: `resources/views/mailspf.blade.php`

```blade
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
```

---

## Step 8: Web Routes 

File: routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailSPFController;

Route::get('/mailspf', [MailSPFController::class, 'index'])->name('mailspf.index');
Route::post('/mailspf/check', [MailSPFController::class, 'check'])->name('mailspf.check');

Route::get('/', function () {
    return view('welcome');
});
```

---

## Step 9: Testing

### Create a feature test 

File: `tests/Feature/SPFCheckerTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SPFCheckerTest extends TestCase
{
    public function test_spf_page_loads()
    {
        $response = $this->get('/mailspf');
        $response->assertStatus(200);
        $response->assertSee('Mail SPF Checker');
    }
}
```

Run tests:

```bash
php artisan test
```

---

## Step 10: Run Server

Run:

```bash
php artisan serve
```
Open in browser:

```bash
http://127.0.0.1:8000/mailspf
```

---

## Best Working Example

```
Email: test@zoho.com  
Server: zoho.com
```

- This works because the domain’s SPF record includes zoho.com.

---

## Output

<img src="screenshots/Screenshot 2026-03-30 172355.png" width="1000">

<img src="screenshots/Screenshot 2026-03-30 180100.png" width="1000">

---

## Project Structure

```
PHP_Laravel12_Mail_SPF_Checker/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── MailSPFController.php
│   └── Services/
│       └── SPFCheckerService.php
│
├── config/
│   └── mailspf.php
│
├── resources/
│   └── views/
│       └── mailspf.blade.php
│
├── routes/
│   └── web.php
│
├── tests/
│   └── Feature/
│       └── SPFCheckerTest.php
│
├── .env
│
└── composer.json
```

---

Your PHP_Laravel12_Mail_SPF_Checker Project is now ready!

