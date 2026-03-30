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
