<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Mail SPF Checker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, #312e81 0%, transparent 30%),
                radial-gradient(circle at bottom right, #0f766e 0%, transparent 30%),
                #020617;
            color: white;
            overflow-x: hidden;
        }

        .main-title {
            font-size: 42px;
            font-weight: 700;
            background: linear-gradient(to right, #38bdf8, #22c55e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
            border-radius: 24px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: 0.4s;
        }

        .glass-card:hover {
            transform: translateY(-5px);
        }

        .stats-card h2 {
            font-size: 34px;
            font-weight: 700;
        }

        .stats-card p {
            color: #94a3b8;
            margin-bottom: 0;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            color: white;
            height: 55px;
            border-radius: 14px;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.95);
            color: white;
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 10px;
        }

        .btn-checker {
            height: 55px;
            border-radius: 14px;
            font-weight: 600;
            border: none;
            background: linear-gradient(to right, #06b6d4, #3b82f6);
            transition: 0.3s;
        }

        .btn-checker:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.35);
        }

        .result-box {
            border-left: 5px solid #22c55e;
        }

        .status-item {
            padding: 14px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.04);
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-item span {
            font-weight: 600;
        }

        .table {
            margin-bottom: 0;
            color: white !important;
        }

        .table thead tr {
            background: #111827 !important;
        }

        .table thead th {
            color: #38bdf8 !important;
            border-bottom: 1px solid #334155 !important;
            padding: 18px !important;
            font-weight: 600;
            background: transparent !important;
        }

        .table tbody td {
            background: transparent !important;
            color: white !important;
            border-color: #334155 !important;
            padding: 18px !important;
        }

        .table tbody tr {
            transition: 0.3s;
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .badge-score {
            padding: 10px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(to right, #22c55e, #06b6d4);
        }

        .pagination {
            justify-content: center;
            gap: 10px;
        }

        .page-item .page-link {
            width: 45px;
            height: 45px;
            border-radius: 12px !important;
            border: 1px solid #334155 !important;
            background: rgba(15, 23, 42, 0.9) !important;
            color: white !important;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            font-weight: 600;
        }

        .page-item .page-link:hover {
            background: linear-gradient(to right, #06b6d4, #3b82f6) !important;
            border-color: transparent !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.35);
        }

        .page-item.active .page-link {
            background: linear-gradient(to right, #06b6d4, #3b82f6) !important;
            border: none !important;
            color: white !important;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.45);
        }

        .page-item.disabled .page-link {
            background: #111827 !important;
            color: #64748b !important;
            border-color: #1e293b !important;
        }

        @media(max-width:768px) {
            .main-title {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="text-center mb-5">

            <h1 class="main-title">
                🚀 Advanced Mail SPF Checker
            </h1>

            <p class="text-secondary mt-3">
                SPF • DKIM • DMARC • Mail Security Analytics
            </p>

        </div>

        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <div class="glass-card stats-card p-4">
                    <h2>{{ $totalChecks }}</h2>
                    <p>Total Checks</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card stats-card p-4">
                    <h2>{{ $successfulChecks }}</h2>
                    <p>Successful Checks</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card stats-card p-4">
                    <h2>{{ $history->count() }}</h2>
                    <p>Recent Records</p>
                </div>
            </div>

        </div>

        <div class="glass-card p-5 mb-5">

            <h3 class="mb-4">
                🔍 Check Email Security
            </h3>

            <form action="{{ route('mailspf.check') }}" method="POST">

                @csrf

                <div class="mb-4">

                    <label class="form-label">
                        Email Address
                    </label>

                    <input type="email" name="email" class="form-control" placeholder="example@domain.com" required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Mail Server
                    </label>

                    <input type="text" name="mailserver" class="form-control" value="smtp.gmail.com">

                </div>

                <button class="btn btn-checker w-100 text-white">
                    Check Security
                </button>

            </form>

        </div>

        @if(session('score') !== null)

            <div class="glass-card result-box p-5 mb-5">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h3>
                        🛡 Security Report
                    </h3>

                    <div class="badge-score">
                        {{ session('score') }}/100 Score
                    </div>

                </div>

                <div class="status-item">

                    <span>SPF Validation</span>

                    <strong>
                        {!! session('spf') ? '✅ Valid' : '❌ Failed' !!}
                    </strong>

                </div>

                <div class="status-item">

                    <span>DKIM Validation</span>

                    <strong>
                        {!! session('dkim') ? '✅ Valid' : '❌ Failed' !!}
                    </strong>

                </div>

                <div class="status-item">

                    <span>DMARC Validation</span>

                    <strong>
                        {!! session('dmarc') ? '✅ Valid' : '❌ Failed' !!}
                    </strong>

                </div>

            </div>

        @endif

        <div class="glass-card p-5">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h3>
                    📜 Security Check History
                </h3>

                <span class="text-secondary">
                    Latest SPF/DKIM/DMARC Reports
                </span>

            </div>

            <div class="table-responsive">

                <table class="table table-dark align-middle">

                    <thead>

                        <tr>
                            <th>Email</th>
                            <th>SPF</th>
                            <th>DKIM</th>
                            <th>DMARC</th>
                            <th>Score</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($history as $item)

                            <tr>

                                <td>{{ $item->email }}</td>

                                <td>
                                    {!! $item->spf ? '✅' : '❌' !!}
                                </td>

                                <td>
                                    {!! $item->dkim ? '✅' : '❌' !!}
                                </td>

                                <td>
                                    {!! $item->dmarc ? '✅' : '❌' !!}
                                </td>

                                <td>
                                    <span class="badge-score">
                                        {{ $item->score }}/100
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center text-secondary py-4">
                                    No Records Found
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">
              {{ $history->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

</body>

</html>