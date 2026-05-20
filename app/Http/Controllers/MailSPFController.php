<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailCheck;
use App\Services\SPFCheckerService;
use App\Services\DKIMCheckerService;
use App\Services\DMARCCheckerService;

class MailSPFController extends Controller
{
    protected SPFCheckerService $spfChecker;
    protected DKIMCheckerService $dkimChecker;
    protected DMARCCheckerService $dmarcChecker;

    public function __construct(
        SPFCheckerService $spfChecker,
        DKIMCheckerService $dkimChecker,
        DMARCCheckerService $dmarcChecker
    ) {
        $this->spfChecker = $spfChecker;
        $this->dkimChecker = $dkimChecker;
        $this->dmarcChecker = $dmarcChecker;
    }

    public function index()
    {
        $history = MailCheck::oldest()->paginate(3);

        return view('mailspf', [
            'defaultEmail' => config('mail.from.address'),
            'defaultServer' => config('mail.host'),
            'history' => $history,
            'totalChecks' => MailCheck::count(),
            'successfulChecks' => MailCheck::where('score', '>=', 70)->count(),
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mailserver' => 'nullable|string',
        ]);

        $email = $request->email;
        $mailserver = $request->mailserver ?? config('mail.host');

        $domain = substr(strrchr($email, "@"), 1);

        $spf = $this->spfChecker
            ->using($mailserver)
            ->canISendAs($email);

        $dkim = $this->dkimChecker->check($domain);

        $dmarc = $this->dmarcChecker->check($domain);

        $score = 0;

        if ($spf) $score += 40;
        if ($dkim) $score += 30;
        if ($dmarc) $score += 30;

        MailCheck::create([
            'email' => $email,
            'domain' => $domain,
            'mailserver' => $mailserver,
            'spf' => $spf,
            'dkim' => $dkim,
            'dmarc' => $dmarc,
            'score' => $score,
        ]);

        return back()->with([
            'spf' => $spf,
            'dkim' => $dkim,
            'dmarc' => $dmarc,
            'score' => $score,
        ]);
    }
}