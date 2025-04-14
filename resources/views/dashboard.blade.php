<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Job Log Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        font-family: 'Segoe UI', sans-serif;
        margin-top: 20px;
    }
    th, td {
        padding: 14px 18px;
        text-align: left;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
    }
    thead th, tbody td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    }

    th {
        font-weight: bold;
        color: #374151;
    }
    tbody tr:hover {
        background-color: #f9fafb;
    }
    .success {
        background-color: #d1fae5;
        color: #065f46;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 10px;
        display: inline-block;
    }
    .failed {
        background-color: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 10px;
        display: inline-block;
    }
    .starting {
        background-color: #e0f2fe;
        color: #1e3a8a;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 10px;
        display: inline-block;
    }
    .cancel-button,
    .retry-button {
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
        .cancel-button:disabled, .retry-button:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
        .retry-button {
            background-color: orange; 
        }
        .stat-card {
    flex: 1;
    min-width: 200px;
    padding: 20px;
    border-radius: 12px;
    color: white;
    font-family: Arial, sans-serif;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.stat-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
}

.stat-icon {
    font-size: 24px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    margin: 10px 0;
}

.stat-sub {
    font-size: 14px;
    opacity: 0.9;
}
.stat-button {
    all: unset; 
    display: block;
    width: 100%;
    cursor: pointer;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 16px;
    background-color: #4285f4; 
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.stat-button:hover {
    background-color: #3367d6;
    transform: translateY(-2px);
}

.centered {
    text-align: center;
}

.stat-title {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
}
.table-container {
    max-height: 500px; 
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-top: 20px;
}


.table-container table thead th {
    position: sticky;
    top: 0;
    background-color: #f9f9f9;
    z-index: 1;
}

.blue { background: linear-gradient(to right, #4facfe, #00f2fe); }
.green { background: linear-gradient(to right, #43e97b, #38f9d7); }
.yellow { background: linear-gradient(to right, #fbcf33, #fce38a); color: #333; }
.pink { background: linear-gradient(to right, #ff758c, #ff7eb3); }

    </style>
</head>
<body>

    <h1>Job Execution Logs</h1>
    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
    <button id="dispatchJobButton" class="stat-card blue stat-button" type="button">
    <div class="stat-title">Dispatch Job</div>
    </button>




    <div class="stat-card green">
        <div class="stat-title">Total Fail</div>
        <div class="stat-icon">❌</div>
        <div class="stat-value">{{ $totalFailed }}</div>
    </div>

    <div class="stat-card yellow">
        <div class="stat-title">Total Success</div>
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $totalSucceeded }}</div>
    </div>

    <div class="stat-card pink">
        <div class="stat-title">Total Starting</div>
        <div class="stat-icon">🔄</div>
        <div class="stat-value">{{ $totalStarting }}</div>
    </div>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Status</th>
                <th>Job</th>
                <th>Attempt</th>
                <th>Error</th>
                <th>Delay</th> {{-- Delay Column --}}
                <th>Retry Count</th> {{-- Retry Count Column --}}
                <th>Cancel</th> {{-- Cancel Column --}}
                <th>Retry</th> {{-- Retry Column --}}
            </tr>
        </thead>
        <tbody>
        @foreach($logs as $log)
            @php
                preg_match('/\[(.*?)\]\s+(🔄|❌|✅)\s+\[(.*?)\](?:\s+Attempt\s+(\d+)\s+failed:)?\s*(.*)?/', $log, $matches);

                $timestamp = $matches[1] ?? '';
                $icon = $matches[2] ?? '';
                $job = $matches[3] ?? '';
                $attempt = $matches[4] ?? '-';
                $extra = trim($matches[5] ?? '');

                // Extract delay from extra info
                preg_match('/\(Delayed for (\d+) seconds\)/', $extra, $delayMatch);
                $delay = $delayMatch[1] ?? '0';

                // Extract retry count
                preg_match('/Retry Count: (\d+)/', $extra, $retryMatch);
                $retryCount = $retryMatch[1] ?? '0';

                // Extract job ID
                preg_match('/\[(.*?)\]\s+/', $log, $idMatch);
                $jobId = $idMatch[1] ?? '';

                if ($icon === '✅') {
                    $statusText = 'Success';
                    $statusClass = 'success';
                    $error = '-';
                } elseif ($icon === '❌') {
                    $statusText = 'Failed';
                    $statusClass = 'failed';
                    $error = $extra ?: '-';
                } elseif ($icon === '🔄') {
                    $statusText = 'Starting';
                    $statusClass = 'starting';
                    $error = '-';
                } else {
                    $statusText = 'Unknown';
                    $statusClass = '';
                }
            @endphp

            @if($timestamp && $job)
                <tr>
                    <td>{{ $timestamp }}</td>
                    <td class="{{ $statusClass }}">{{ $icon }} {{ $statusText }}</td>
                    <td>{{ $job }}</td>
                    <td>{{ $attempt }}</td>
                    <td>{{ $error }}</td>
                    <td>{{ $delay }} seconds</td> {{-- Delay Column --}}
                    <td>{{ $retryCount }}</td> {{-- Retry Count Column --}}
                    <td>
                        @if($statusText === 'Starting') {{-- Only show cancel button for running jobs --}}
                            <form action="{{ route('cancel_job') }}" method="POST">
                                @csrf
                                <input type="hidden" name="job_id" value="{{ $jobId }}">
                                <button type="submit" class="cancel-button">Cancel</button>
                            </form>
                        @else
                            <button class="cancel-button" disabled>Cancelled</button>
                        @endif
                    </td> {{-- Cancel Column --}}

                    <td>
                        @if($statusText === 'Failed') {{-- Only show retry button for failed jobs --}}
                            <form action="{{ route('retry_job') }}" method="POST">
                                @csrf
                                <input type="hidden" name="job_id" value="{{ $jobId }}">
                                <button type="submit" class="retry-button">Retry</button>
                            </form>
                        @else
                            <button class="retry-button" disabled>Retry</button>
                        @endif
                    </td> {{-- Retry Column --}}
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>

</body>
<script>
document.getElementById('dispatchJobButton').addEventListener('click', function () {
    fetch('/test-fire-job')
        .then(response => response.json())
        .then(data => {
            alert(data.message); 
        })
        .catch(error => {
            alert('❌ Failed to dispatch job: ' + error.message);
        });
});
</script>
<script>
function cancelJob(jobId) {
    if (!confirm("Are you sure you want to cancel this job?")) return;

    fetch("{{ route('cancel_job') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-job-id="${jobId}"]`);
            if (row) row.remove();
        } else {
            alert("❌ " + data.message || "Failed to cancel job.");
        }
    })
    .catch(err => {
        alert("Error canceling job: " + err.message);
    });
}
</script>
</html>
