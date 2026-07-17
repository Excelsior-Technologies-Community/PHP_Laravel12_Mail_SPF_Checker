<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Mail SPF Checker Workspace Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, #1e1b4b 0%, transparent 35%), radial-gradient(circle at bottom right, #042f2e 0%, transparent 35%), #020617;
            color: #f8fafc;
            overflow-x: hidden;
        }
        .main-title {
            font-size: 38px;
            font-weight: 800;
            background: linear-gradient(to right, #38bdf8, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }
        .form-control, .form-select {
            background: rgba(10, 15, 30, 0.8) !important;
            border: 1px solid #1e293b !important;
            color: #fff !important;
            border-radius: 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.25) !important;
        }
        textarea.form-control { height: auto; resize: none; }
        .btn-gradient {
            border-radius: 12px;
            font-weight: 600;
            background: linear-gradient(to right, #0ea5e9, #10b981);
            border: none;
            transition: 0.3s;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
        }
        .stat-number { font-size: 32px; font-weight: 800; }
        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; margin-top: 4px; }
        .grade-badge { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .grade-pass     { background: #052e16; color: #4ade80; border: 1px solid #166534; }
        .grade-softfail { background: #431407; color: #fb923c; border: 1px solid #9a3412; }
        .grade-hardfail { background: #450a0a; color: #f87171; border: 1px solid #991b1b; }
        .grade-neutral  { background: #1e1b4b; color: #818cf8; border: 1px solid #3730a3; }
        .grade-none     { background: #0f172a; color: #475569; border: 1px solid #1e293b; }
        .score-excellent { color: #4ade80; }
        .score-good      { color: #34d399; }
        .score-fair      { color: #fb923c; }
        .score-poor      { color: #f87171; }
        .metric-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(0,0,0,0.3); border: 1px solid #1e293b; border-radius: 10px; margin-bottom: 10px; font-size: 12px; }
        .score-bar-wrap { background: #0f172a; border-radius: 50px; height: 8px; width: 100%; margin-top: 10px; overflow: hidden; }
        .score-bar-fill { height: 8px; border-radius: 50px; background: linear-gradient(to right, #0ea5e9, #10b981); transition: width 1s ease; }
        .api-block { background: #020617; border: 1px solid #1e293b; border-radius: 12px; padding: 16px; font-family: 'Courier New', monospace; font-size: 11px; color: #94a3b8; overflow-x: auto; white-space: pre; }
        .api-block .key   { color: #38bdf8; }
        .api-block .val   { color: #4ade80; }
        .api-block .route { color: #f472b6; }
        .api-block .method{ color: #fb923c; }
        .table { color: #f8fafc !important; margin-bottom: 0; }
        .table thead th { background: #020617 !important; color: #38bdf8 !important; border-bottom: 1px solid #1e293b !important; padding: 14px 16px !important; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .table tbody td { background: transparent !important; color: #f8fafc !important; border-color: #1e293b !important; padding: 14px 16px !important; font-size: 12px; }
        .table tbody tr:hover { background: rgba(255,255,255,0.02) !important; }
        .dns-record-block { background: #020617; border: 1px solid #1e293b; border-radius: 10px; padding: 14px; margin-top: 14px; max-height: 320px; overflow-y: auto; }
        .dns-type-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 10px; border-radius: 6px; margin-bottom: 8px; display: inline-block; }
        .dns-A   { background: #1e3a5f; color: #38bdf8; }
        .dns-MX  { background: #1a2e1a; color: #4ade80; }
        .dns-TXT { background: #2d1b4e; color: #a78bfa; }
        .dns-NS  { background: #2d1a00; color: #fb923c; }
        .dns-line { font-size: 11px; color: #94a3b8; padding: 4px 0 4px 12px; border-left: 2px solid #1e293b; margin-bottom: 4px; word-break: break-all; }
        .bulk-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: rgba(0,0,0,0.35); border: 1px solid #1e293b; border-radius: 10px; margin-bottom: 8px; font-size: 12px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 12px; border-bottom: 1px solid #1e293b; margin-bottom: 18px; }
        .pagination { justify-content: center; gap: 6px; }
        .page-item .page-link { width: 38px; height: 38px; border-radius: 10px !important; border: 1px solid #1e293b !important; background: #0f172a !important; color: #94a3b8 !important; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; }
        .page-item.active .page-link { background: linear-gradient(to right, #0ea5e9, #10b981) !important; border: none !important; color: #fff !important; }
        .page-item .page-link:hover { background: #1e293b !important; color: #fff !important; }
    </style>
</head>
<body>
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="main-title">Advanced Mail Security & Deliverability Hub</h1>
        <p style="color:#475569; font-size:13px; margin-top:8px; font-family:monospace;">
            Telemetry Framework: SPF &bull; DKIM &bull; DMARC &bull; DNS Lookup &bull; Bulk Engine &bull; API Hub
        </p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <div class="stat-number" style="color:#38bdf8;">{{ $totalChecks }}</div>
                <div class="stat-label">Total Checks Logged</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <div class="stat-number" style="color:#4ade80;">{{ $successfulChecks }}</div>
                <div class="stat-label">High Deliverability (&ge;70)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <div class="stat-number" style="color:#a78bfa;">{{ $history->total() }}</div>
                <div class="stat-label">Total History Records</div>
            </div>
        </div>
    </div>

    @if(session('reportMatrix'))
    @php
        $m = session('reportMatrix');
        $gradeClass = match($m['gradeLabel']) {
            'Excellent' => 'score-excellent',
            'Good'      => 'score-good',
            'Fair'      => 'score-fair',
            default     => 'score-poor',
        };
        $spfClass = match($m['spfGrade']) {
            'Pass'     => 'grade-pass',
            'SoftFail' => 'grade-softfail',
            'HardFail' => 'grade-hardfail',
            'Neutral'  => 'grade-neutral',
            default    => 'grade-none',
        };
    @endphp
    <div class="glass-card p-5 mb-5" style="border-left: 4px solid #0ea5e9;">
        <div class="section-title" style="color:#38bdf8;">
            📊 Automated Deliverability Score Matrix Dashboard
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <div style="font-size:11px; color:#475569; text-transform:uppercase; letter-spacing:1.5px;">
                    Domain: {{ $m['domain'] }} &nbsp;|&nbsp; Server: {{ $m['mailserver'] }}
                </div>
                <div style="font-size:13px; color:#94a3b8; margin-top:4px;">
                    Email: {{ $m['email'] }}
                </div>
            </div>
            <div class="text-center">
                <div class="stat-number {{ $gradeClass }}">{{ $m['score'] }}<span style="font-size:16px; color:#475569;">/100</span></div>
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:2px;" class="{{ $gradeClass }}">
                    {{ $m['gradeLabel'] }}
                </div>
            </div>
        </div>
        <div class="score-bar-wrap mb-4">
            <div class="score-bar-fill" style="width: {{ $m['score'] }}%;"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="metric-row">
                    <div>
                        <div style="font-size:10px; color:#475569; text-transform:uppercase; letter-spacing:1px;">SPF Validation</div>
                        <div style="font-size:13px; font-weight:700; margin-top:4px;">
                            <span class="grade-badge {{ $spfClass }}">{{ $m['spfGrade'] }}</span>
                        </div>
                    </div>
                    <div style="font-size:22px; font-weight:800; color:{{ $m['spf'] ? '#4ade80' : '#f87171' }};">
                        {{ $m['spf'] ? '+40' : '0' }}<span style="font-size:10px; color:#475569;">pts</span>
                    </div>
                </div>
                <div style="font-size:10px; color:#475569; padding: 0 4px;">
                    @if($m['spfGrade'] === 'Pass') ✅ Mail server authorized in SPF record
                    @elseif($m['spfGrade'] === 'SoftFail') ⚠️ SPF ~all &mdash; soft rejection policy
                    @elseif($m['spfGrade'] === 'HardFail') ❌ SPF -all &mdash; hard rejection policy
                    @elseif($m['spfGrade'] === 'Neutral') ℹ️ SPF ?all &mdash; neutral policy
                    @else 🚫 No SPF record found for domain
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-row">
                    <div>
                        <div style="font-size:10px; color:#475569; text-transform:uppercase; letter-spacing:1px;">DKIM Cryptographic Key</div>
                        <div style="font-size:13px; font-weight:700; margin-top:4px; color:{{ $m['dkim'] ? '#4ade80' : '#f87171' }};">
                            {{ $m['dkim'] ? 'FOUND / ACTIVE' : 'NOT DETECTED' }}
                        </div>
                    </div>
                    <div style="font-size:22px; font-weight:800; color:{{ $m['dkim'] ? '#4ade80' : '#f87171' }};">
                        {{ $m['dkim'] ? '+30' : '0' }}<span style="font-size:10px; color:#475569;">pts</span>
                    </div>
                </div>
                <div style="font-size:10px; color:#475569; padding: 0 4px;">
                    {{ $m['dkim'] ? '✅ DKIM selector record verified via DNS lookup' : '❌ No DKIM selector found (default/google/selector1)' }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-row">
                    <div>
                        <div style="font-size:10px; color:#475569; text-transform:uppercase; letter-spacing:1px;">DMARC Policy Enforcement</div>
                        <div style="font-size:13px; font-weight:700; margin-top:4px; color:{{ $m['dmarc'] ? '#4ade80' : '#f87171' }};">
                            {{ $m['dmarc'] ? 'VALID PROTOCOL' : 'NOT CONFIGURED' }}
                        </div>
                    </div>
                    <div style="font-size:22px; font-weight:800; color:{{ $m['dmarc'] ? '#4ade80' : '#f87171' }};">
                        {{ $m['dmarc'] ? '+30' : '0' }}<span style="font-size:10px; color:#475569;">pts</span>
                    </div>
                </div>
                <div style="font-size:10px; color:#475569; padding: 0 4px;">
                    {{ $m['dmarc'] ? '✅ _dmarc TXT record with v=DMARC1 found' : '❌ No _dmarc TXT record found for domain' }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="glass-card p-4 h-100">
                <div class="section-title" style="color:#38bdf8;">
                    🔍 Single Mail Security Diagnostic
                </div>
                <form action="{{ route('mailspf.check') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; color:#94a3b8;">Target Email Address</label>
                      <input type="email" name="email" required class="form-control" placeholder="example@domain.com" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; color:#94a3b8;">Mail Server Host</label>
                        <input type="text" name="mailserver" class="form-control" value="{{ old('mailserver', $defaultServer) }}">
                    </div>
                    @if($errors->any())
                        <div style="font-size:11px; color:#f87171; margin-bottom:10px;">{{ $errors->first() }}</div>
                    @endif
                    <button type="submit" class="btn btn-gradient w-100 text-white py-2" style="font-size:12px;">
                        ▶ Run Security Check
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="glass-card p-4 h-100">
                <div class="section-title" style="color:#4ade80;">
                    📋 Bulk Email Verification Engine
                </div>
                <form action="{{ route('mailspf.bulk') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; color:#94a3b8;">Email List (one per line)</label>
                        <textarea name="emails" rows="4" required class="form-control" placeholder="test@zoho.com&#10;info@example.com"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px; color:#94a3b8;">Mail Server</label>
                        <input type="text" name="mailserver" class="form-control" value="{{ old('mailserver', $defaultServer) }}">
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 text-white py-2" style="font-size:12px;">
                        ▶ Execute Bulk Verification
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('bulkResults'))
    <div class="glass-card p-4 mb-5">
        <div class="section-title" style="color:#fb923c;">
            📦 Bulk Verification Results &mdash; {{ count(session('bulkResults')) }} Emails Processed
        </div>
        <div style="max-height:300px; overflow-y:auto;">
            @foreach(session('bulkResults') as $bulk)
            <div class="bulk-row">
                <div>
                    <div style="font-weight:700; color:#f8fafc; font-size:12px;">{{ $bulk['email'] }}</div>
                    @if(!isset($bulk['error']))
                    <div style="font-size:10px; color:#475569; margin-top:2px;">
                        Domain: {{ $bulk['domain'] ?? '' }} &nbsp;|&nbsp; Server: {{ $bulk['mailserver'] ?? '' }}
                    </div>
                    @endif
                </div>
                @if(isset($bulk['error']))
                    <span style="font-size:10px; font-weight:700; color:#f87171; text-transform:uppercase;">❌ {{ $bulk['error'] }}</span>
                @else
                    <div class="text-end">
                        @php
                            $sc = match($bulk['spfGrade']) {
                                'Pass'     => 'grade-pass',
                                'SoftFail' => 'grade-softfail',
                                'HardFail' => 'grade-hardfail',
                                'Neutral'  => 'grade-neutral',
                                default    => 'grade-none',
                            };
                        @endphp
                        <span class="grade-badge {{ $sc }}">{{ $bulk['spfGrade'] }}</span>
                        <div style="font-size:11px; font-weight:700; margin-top:4px; color:{{ $bulk['score'] >= 70 ? '#4ade80' : ($bulk['score'] >= 40 ? '#fb923c' : '#f87171') }};">
                            {{ $bulk['score'] }}/100 [{{ $bulk['gradeLabel'] }}]
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="glass-card p-4 mb-5">
        <div class="section-title" style="color:#a78bfa;">
            🌐 DNS Infrastructure Lookup Tool &mdash; A / MX / TXT / NS Records
        </div>
        <form action="{{ route('mailspf.dns') }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-sm-9">
                    <label class="form-label" style="font-size:12px; color:#94a3b8;">Domain Name</label>
                    <input type="text" name="domain" required class="form-control" placeholder="e.g. zoho.com, gmail.com" value="{{ session('dnsResults')['domain'] ?? '' }}">
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-gradient w-100 text-white py-2" style="font-size:12px;">
                        🔎 Lookup DNS
                    </button>
                </div>
            </div>
        </form>

        @if(session('dnsResults'))
        @php $dns = session('dnsResults'); @endphp
        <div class="dns-record-block">
            <div style="font-size:10px; color:#475569; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:14px;">
                DNS Records for: <span style="color:#38bdf8; font-weight:700;">{{ $dns['domain'] }}</span>
                &nbsp;|&nbsp; {{ array_sum(array_map('count', $dns['records'])) }} records found
            </div>
            @forelse($dns['records'] as $type => $lines)
            <div style="margin-bottom:16px;">
                <span class="dns-type-label dns-{{ $type }}">{{ $type }} Record</span>
                @foreach($lines as $line)
                <div class="dns-line">{{ $line }}</div>
                @endforeach
            </div>
            @empty
            <div style="font-size:12px; color:#475569;">No DNS records found for this domain.</div>
            @endforelse
        </div>
        @endif
    </div>

    <div class="glass-card p-4 mb-5">
        <div class="section-title" style="color:#f472b6;">
            ⚡ Interactive JSON API Integration Hub &mdash; Developer Reference
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div style="font-size:11px; font-weight:700; color:#38bdf8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Single Email Check</div>
                <div class="api-block"><span class="method">POST</span> <span class="route">/api/v1/check</span>
<span class="key">Content-Type:</span> <span class="val">application/json</span>
{
  <span class="key">"email"</span>:      <span class="val">"user@domain.com"</span>,
  <span class="key">"mailserver"</span>: <span class="val">"smtp.domain.com"</span>
}
<span class="key">Response:</span>
{
  <span class="key">"status"</span>: <span class="val">"success"</span>,
  <span class="key">"data"</span>: {
    <span class="key">"spf"</span>:   { <span class="key">"pass"</span>: <span class="val">true</span>, <span class="key">"grade"</span>: <span class="val">"Pass"</span> },
    <span class="key">"dkim"</span>:  { <span class="key">"pass"</span>: <span class="val">true</span> },
    <span class="key">"dmarc"</span>: { <span class="key">"pass"</span>: <span class="val">true</span> },
    <span class="key">"score"</span>: <span class="val">100</span>,
    <span class="key">"grade_label"</span>: <span class="val">"Excellent"</span>
  }
}</div>
            </div>
            <div class="col-md-4">
                <div style="font-size:11px; font-weight:700; color:#4ade80; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Bulk Email Check</div>
                <div class="api-block"><span class="method">POST</span> <span class="route">/api/v1/bulk-check</span>
<span class="key">Content-Type:</span> <span class="val">application/json</span>
{
  <span class="key">"emails"</span>: [
    <span class="val">"user1@domain.com"</span>,
    <span class="val">"user2@domain.com"</span>
  ],
  <span class="key">"mailserver"</span>: <span class="val">"smtp.domain.com"</span>
}
<span class="key">Response:</span>
{
  <span class="key">"status"</span>: <span class="val">"success"</span>,
  <span class="key">"data"</span>: [
    {
      <span class="key">"email"</span>:       <span class="val">"user1@domain.com"</span>,
      <span class="key">"score"</span>:       <span class="val">70</span>,
      <span class="key">"grade_label"</span>: <span class="val">"Good"</span>
    }
  ]
}</div>
            </div>
            <div class="col-md-4">
                <div style="font-size:11px; font-weight:700; color:#a78bfa; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">DNS Lookup</div>
                <div class="api-block"><span class="method">POST</span> <span class="route">/api/v1/dns-lookup</span>
<span class="key">Content-Type:</span> <span class="val">application/json</span>
{
  <span class="key">"domain"</span>: <span class="val">"zoho.com"</span>
}
<span class="key">Response:</span>
{
  <span class="key">"status"</span>: <span class="val">"success"</span>,
  <span class="key">"domain"</span>: <span class="val">"zoho.com"</span>,
  <span class="key">"records"</span>: { ... }
}</div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded border border-slate-850 overflow-hidden shadow-2xl">
        <div class="p-3 bg-black/20 border-b border-slate-850">
            <h3 class="text-xs uppercase font-bold text-slate-400 font-mono tracking-wider mb-0">📜 Security Check History (DB Mapped Logs)</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-dark align-middle">
                <thead>
                    <tr>
                        <th>Target Email</th>
                        <th>Domain</th>
                        <th>Mail Server</th>
                        <th>Qualifiers (SPF/DKIM/DMARC)</th>
                        <th class="text-end">Deliverability Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                    <tr>
                        <td class="font-bold text-gray-200">
                            {{ $item->email }}
                            <span class="block text-[9px] text-slate-500 font-mono mt-0.5">Timestamp: {{ $item->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="text-slate-400 font-mono">{{ $item->domain }}</td>
                        <td class="text-slate-400 font-mono">{{ $item->mailserver }}</td>
                        <td>
                            <div class="d-flex gap-1 align-items-center">
                                @php
                                    $sc = match($item->spf_grade) {
                                        'Pass'     => 'grade-pass',
                                        'SoftFail' => 'grade-softfail',
                                        'HardFail' => 'grade-hardfail',
                                        'Neutral'  => 'grade-neutral',
                                        default    => 'grade-none',
                                    };
                                @endphp
                                <span class="grade-badge {{ $sc }} font-mono">SPF: {{ $item->spf_grade }}</span>
                                <span class="grade-badge {{ $item->dkim ? 'grade-pass' : 'grade-hardfail' }} font-mono">DKIM</span>
                                <span class="grade-badge {{ $item->dmarc ? 'grade-pass' : 'grade-hardfail' }} font-mono">DMARC</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="bg-black border border-slate-800 text-teal-400 font-mono font-black px-2 py-1.5 rounded shadow-sm">
                                {{ $item->score }}/100 [{{ $item->grade_label }}]
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-slate-500 italic">No historical verification logs mapped inside the sqlite roster.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 bg-black/20 border-t border-slate-850 d-flex justify-content-center">
            {{ $history->links() }}
        </div>
    </div>

</div>
</body>
</html>