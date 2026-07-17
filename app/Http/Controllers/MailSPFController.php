<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailCheck;
use App\Services\SPFCheckerService;
use App\Services\DKIMCheckerService;
use App\Services\DMARCCheckerService;

class MailSPFController extends Controller
{
    public function __construct(
        protected SPFCheckerService $spfChecker,
        protected DKIMCheckerService $dkimChecker,
        protected DMARCCheckerService $dmarcChecker
    ) {}

    public function index()
    {
        return view('mailspf', [
            'defaultEmail'     => config('mail.from.address'),
            'defaultServer'    => config('mail.host'),
            'history'          => MailCheck::latest()->paginate(10),
            'totalChecks'      => MailCheck::count(),
            'successfulChecks' => MailCheck::where('score', '>=', 70)->count(),
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'mailserver' => 'nullable|string',
        ]);

        $result = $this->runCheck($request->email, $request->mailserver ?? config('mail.host'));
        return back()->with('reportMatrix', $result);
    }

    public function bulkCheck(Request $request)
    {
        $request->validate(['emails' => 'required|string']);
        $mailserver = $request->mailserver ?? config('mail.host');
        $emails     = array_filter(array_map('trim', explode("\n", $request->emails)));
        $results    = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $results[] = array_merge(['email' => $email], $this->runCheck($email, $mailserver));
            } else {
                $results[] = ['email' => $email, 'error' => 'Invalid email format'];
            }
        }

        return back()->with('bulkResults', $results);
    }

    public function dnsLookup(Request $request)
    {
        $request->validate(['domain' => 'required|string']);
        $domain  = trim($request->domain);
        $records = [];
        $types   = ['A' => DNS_A, 'MX' => DNS_MX, 'TXT' => DNS_TXT, 'NS' => DNS_NS];

        foreach ($types as $label => $type) {
            $raw = @dns_get_record($domain, $type);
            if ($raw) {
                foreach ($raw as $r) {
                    $records[$label][] = match ($label) {
                        'A'     => $r['ip'] ?? '',
                        'MX'    => ($r['target'] ?? '') . ' (priority: ' . ($r['pri'] ?? 0) . ')',
                        'TXT'   => $r['txt'] ?? '',
                        'NS'    => $r['target'] ?? '',
                        default => json_encode($r),
                    };
                }
            }
        }

        return back()->with('dnsResults', ['domain' => $domain, 'records' => $records]);
    }

    public function apiCheck(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'mailserver' => 'nullable|string',
        ]);

        $result = $this->runCheck($request->email, $request->mailserver ?? 'smtp.gmail.com');
        return response()->json([
            'status' => 'success',
            'data'   => [
                'email'       => $request->email,
                'mailserver'  => $request->mailserver ?? 'smtp.gmail.com',
                'spf'         => ['pass' => $result['spf'], 'grade' => $result['spfGrade']],
                'dkim'        => ['pass' => $result['dkim']],
                'dmarc'       => ['pass' => $result['dmarc']],
                'score'       => $result['score'],
                'grade_label' => $result['gradeLabel'],
            ],
        ]);
    }

    public function apiBulk(Request $request)
    {
        $request->validate([
            'emails'     => 'required|array',
            'emails.*'   => 'email',
            'mailserver' => 'nullable|string',
        ]);

        $mailserver = $request->mailserver ?? 'smtp.gmail.com';
        $results    = [];

        foreach ($request->emails as $email) {
            $r = $this->runCheck($email, $mailserver);
            $results[] = [
                'email'       => $email,
                'spf'         => ['pass' => $r['spf'], 'grade' => $r['spfGrade']],
                'dkim'        => ['pass' => $r['dkim']],
                'dmarc'       => ['pass' => $r['dmarc']],
                'score'       => $r['score'],
                'grade_label' => $r['gradeLabel'],
            ];
        }

        return response()->json(['status' => 'success', 'data' => $results]);
    }

    public function apiDns(Request $request)
    {
        $request->validate(['domain' => 'required|string']);
        $domain  = trim($request->domain);
        $records = [];
        $types   = ['A' => DNS_A, 'MX' => DNS_MX, 'TXT' => DNS_TXT, 'NS' => DNS_NS];

        foreach ($types as $label => $type) {
            $raw = @dns_get_record($domain, $type);
            if ($raw) $records[$label] = $raw;
        }

        return response()->json(['status' => 'success', 'domain' => $domain, 'records' => $records]);
    }

    private function runCheck(string $email, string $mailserver): array
    {
        $domain   = substr(strrchr($email, '@'), 1);
        $spfGrade = $this->spfChecker->using($mailserver)->getGrade($email);
        $spf      = $spfGrade === 'Pass';
        $dkim     = $this->dkimChecker->check($domain);
        $dmarc    = $this->dmarcChecker->check($domain);

        $spfScore  = match ($spfGrade) { 'Pass' => 40, 'Neutral' => 20, 'SoftFail' => 10, default => 0 };
        $score     = $spfScore + ($dkim ? 30 : 0) + ($dmarc ? 30 : 0);
        $gradeLabel = match (true) {
            $score >= 90 => 'Excellent',
            $score >= 70 => 'Good',
            $score >= 40 => 'Fair',
            default      => 'Poor',
        };

        MailCheck::create([
            'email'       => $email,
            'domain'      => $domain,
            'mailserver'  => $mailserver,
            'spf'         => $spf,
            'dkim'        => $dkim,
            'dmarc'       => $dmarc,
            'spf_grade'   => $spfGrade,
            'grade_label' => $gradeLabel,
            'score'       => $score,
        ]);

        return compact('spf', 'spfGrade', 'dkim', 'dmarc', 'score', 'gradeLabel', 'domain', 'email', 'mailserver');
    }
}