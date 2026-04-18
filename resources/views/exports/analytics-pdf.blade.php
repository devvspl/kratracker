<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KRA Analytics Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { color: #0d9488; font-size: 24px; margin-bottom: 10px; }
        h2 { color: #334155; font-size: 18px; margin-top: 20px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .header { margin-bottom: 30px; }
        .info { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KRA Analytics Report</h1>
        <div class="info"><strong>Employee:</strong> {{ $user->name }}</div>
        <div class="info"><strong>Report Date:</strong> {{ $date }}</div>
    </div>

    <h2>Work Logs Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>KRA</th>
                <th>Sub-KRA</th>
                <th>Title</th>
                <th>Achievement</th>
                <th>Score</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workLogs as $log)
            <tr>
                <td>{{ $log->log_date->format('M d, Y') }}</td>
                <td>{{ $log->subKra->kra->name }}</td>
                <td>{{ $log->subKra->name }}</td>
                <td>{{ $log->title }}</td>
                <td>{{ $log->achievement_value }}</td>
                <td>{{ number_format($log->score_calculated, 2) }}%</td>
                <td>{{ $log->status->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
