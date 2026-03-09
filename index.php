<?php
/**
 * YoutubeTS API Tester
 * 
 * A simple PHP script to test the YoutubeTS Transcript API.
 * No database required. Just set your API key and secret below.
 * 
 * GitHub: https://github.com/YOUR_USERNAME/youtubets-api-tester
 * API Docs: https://youtubets.com/api/v1/info
 * 
 * @license MIT
 */

// ============================================================
// CONFIGURATION - Set your API credentials here
// ============================================================
$config = [
    'api_key'    => 'ytts_your_api_key_here',   // Your YoutubeTS API key
    'api_secret' => 'your_secret_here',          // Your YoutubeTS API secret
    'base_url'   => 'https://youtubets.com/api/v1',
];

// ============================================================
// API FUNCTIONS
// ============================================================

function apiRequest(string $endpoint, string $method = 'GET', array $body = [], array $config = []): array
{
    $url = $config['base_url'] . $endpoint;

    $headers = [
        'Authorization: Bearer ' . $config['api_key'],
        'X-API-Secret: ' . $config['api_secret'],
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response   = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'cURL Error: ' . $curlError, 'http_code' => 0];
    }

    $data = json_decode($response, true) ?? [];
    $data['http_code'] = $httpCode;

    return $data;
}

function getTranscript(string $videoInput, array $config): array
{
    // Determine if it's a URL or video ID
    $body = [];
    if (filter_var($videoInput, FILTER_VALIDATE_URL) || str_contains($videoInput, 'youtube.com') || str_contains($videoInput, 'youtu.be')) {
        $body['video_url'] = $videoInput;
    } else {
        $body['video_id'] = trim($videoInput);
    }

    return apiRequest('/transcript', 'POST', $body, $config);
}

function getUsage(array $config): array
{
    return apiRequest('/usage', 'GET', [], $config);
}

function getPlans(array $config): array
{
    return apiRequest('/plans', 'GET', [], $config);
}

function checkHealth(array $config): array
{
    // Health endpoint is public, no auth needed
    $ch = curl_init($config['base_url'] . '/health');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true) ?? [];
    $data['http_code'] = $httpCode;
    return $data;
}

// ============================================================
// HANDLE FORM SUBMISSIONS
// ============================================================

$result      = null;
$action      = $_POST['action'] ?? $_GET['action'] ?? null;
$videoInput  = trim($_POST['video_input'] ?? '');
$activeTab   = 'transcript';

if ($action === 'transcript' && $videoInput !== '') {
    $result    = getTranscript($videoInput, $config);
    $activeTab = 'transcript';
} elseif ($action === 'usage') {
    $result    = getUsage($config);
    $activeTab = 'usage';
} elseif ($action === 'plans') {
    $result    = getPlans($config);
    $activeTab = 'plans';
} elseif ($action === 'health') {
    $result    = checkHealth($config);
    $activeTab = 'health';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YoutubeTS API Tester</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f1117;
            color: #e1e4e8;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 0.3rem;
        }

        .header h1 span { color: #ff4444; }

        .header p {
            color: #8b949e;
            font-size: 0.9rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.5rem 1.2rem;
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            color: #8b949e;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .tab-btn:hover { border-color: #58a6ff; color: #58a6ff; }
        .tab-btn.active { background: #1f6feb; border-color: #1f6feb; color: #fff; }

        /* Cards */
        .card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #fff;
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #8b949e;
            font-size: 0.85rem;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 0.6rem 0.8rem;
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            color: #e1e4e8;
            font-size: 0.9rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #58a6ff;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            background: #238636;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover { background: #2ea043; }
        .btn-blue { background: #1f6feb; }
        .btn-blue:hover { background: #388bfd; }

        /* Results */
        .result-box {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 1rem;
            overflow-x: auto;
        }

        .result-box pre {
            font-family: 'Fira Code', 'Consolas', monospace;
            font-size: 0.8rem;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .badge-green { background: #23863633; color: #3fb950; }
        .badge-red { background: #f8514933; color: #f85149; }

        /* Transcript viewer */
        .transcript-lines {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 1rem;
        }

        .transcript-line {
            display: flex;
            gap: 1rem;
            padding: 0.4rem 0;
            border-bottom: 1px solid #21262d;
            font-size: 0.85rem;
        }

        .transcript-line .time {
            color: #58a6ff;
            font-family: monospace;
            white-space: nowrap;
            min-width: 60px;
        }

        .transcript-line .text { color: #c9d1d9; }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 0.8rem;
        }

        .info-item .label { color: #8b949e; font-size: 0.75rem; text-transform: uppercase; }
        .info-item .value { color: #fff; font-size: 1.1rem; margin-top: 0.3rem; }

        .copy-btn {
            padding: 0.3rem 0.8rem;
            background: #30363d;
            border: 1px solid #484f58;
            border-radius: 4px;
            color: #8b949e;
            cursor: pointer;
            font-size: 0.75rem;
            float: right;
        }

        .copy-btn:hover { color: #fff; }

        .footer {
            text-align: center;
            color: #484f58;
            font-size: 0.8rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #21262d;
        }

        .footer a { color: #58a6ff; text-decoration: none; }

        .alert {
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .alert-warning {
            background: #bb800926;
            border: 1px solid #bb800966;
            color: #d29922;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Youtube<span>TS</span> API Tester</h1>
        <p>Test the YoutubeTS Transcript API &mdash; No database required</p>
    </div>

    <?php if ($config['api_key'] === 'ytts_your_api_key_here'): ?>
    <div class="alert alert-warning">
        ⚠️ You haven't set your API credentials yet. Edit the <code>$config</code> array at the top of <code>index.php</code>.
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn <?= $activeTab === 'transcript' ? 'active' : '' ?>" onclick="showTab('transcript')">📄 Get Transcript</button>
        <button class="tab-btn <?= $activeTab === 'usage' ? 'active' : '' ?>" onclick="submitAction('usage')">📊 Check Usage</button>
        <button class="tab-btn <?= $activeTab === 'plans' ? 'active' : '' ?>" onclick="submitAction('plans')">💰 View Plans</button>
        <button class="tab-btn <?= $activeTab === 'health' ? 'active' : '' ?>" onclick="submitAction('health')">💚 Health Check</button>
    </div>

    <!-- Transcript Form -->
    <div class="card" id="tab-transcript" style="<?= $activeTab !== 'transcript' && $result ? 'display:none' : '' ?>">
        <h2>Extract Transcript</h2>
        <form method="POST">
            <input type="hidden" name="action" value="transcript">
            <div class="form-group">
                <label for="video_input">YouTube Video URL or Video ID</label>
                <input type="text" id="video_input" name="video_input" 
                       value="<?= htmlspecialchars($videoInput) ?>"
                       placeholder="e.g. https://youtube.com/watch?v=dQw4w9WgXcQ or dQw4w9WgXcQ">
            </div>
            <button type="submit" class="btn">🚀 Get Transcript</button>
        </form>
    </div>

    <!-- Hidden form for tab actions -->
    <form id="actionForm" method="POST" style="display:none">
        <input type="hidden" name="action" id="actionInput">
    </form>

    <!-- Results -->
    <?php if ($result !== null): ?>
    <div class="card">
        <h2>
            Response
            <?php
                $isSuccess = ($result['success'] ?? false) === true;
                $httpCode  = $result['http_code'] ?? '?';
            ?>
            <span class="badge <?= $isSuccess ? 'badge-green' : 'badge-red' ?>">
                HTTP <?= $httpCode ?> — <?= $isSuccess ? 'SUCCESS' : 'ERROR' ?>
            </span>
        </h2>

        <?php if ($action === 'transcript' && $isSuccess && isset($result['data'])): ?>
            <!-- Transcript pretty view -->
            <?php $d = $result['data']; ?>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Video Title</div>
                    <div class="value" style="font-size:0.85rem"><?= htmlspecialchars($d['video_title'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Duration</div>
                    <div class="value"><?= htmlspecialchars($d['video_duration'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Language</div>
                    <div class="value"><?= htmlspecialchars($d['language'] ?? 'N/A') ?> (<?= htmlspecialchars($d['language_code'] ?? '') ?>)</div>
                </div>
                <div class="info-item">
                    <div class="label">Auto-generated</div>
                    <div class="value"><?= ($d['is_generated'] ?? false) ? 'Yes' : 'No' ?></div>
                </div>
            </div>

            <?php if (!empty($d['transcript'])): ?>
            <h3 style="font-size:0.95rem; margin-bottom:0.5rem; color:#fff;">
                Transcript Lines (<?= count($d['transcript']) ?>)
                <button class="copy-btn" onclick="copyText('fullText')">📋 Copy Full Text</button>
            </h3>
            <div class="transcript-lines">
                <?php foreach ($d['transcript'] as $line): ?>
                <div class="transcript-line">
                    <span class="time"><?= gmdate('i:s', (int)($line['start'] ?? 0)) ?></span>
                    <span class="text"><?= htmlspecialchars($line['text'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <textarea id="fullText" style="position:absolute;left:-9999px"><?= htmlspecialchars($d['full_text'] ?? '') ?></textarea>
            <?php endif; ?>

            <?php if (isset($result['meta'])): ?>
            <div style="margin-top:1rem; color:#8b949e; font-size:0.8rem;">
                Credits used: <?= $result['meta']['api_calls_used'] ?? '?' ?> | 
                Remaining: <?= $result['meta']['api_calls_remaining'] ?? '?' ?>
            </div>
            <?php endif; ?>

        <?php elseif ($action === 'usage' && $isSuccess && isset($result['data'])): ?>
            <?php $d = $result['data']; ?>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Plan</div>
                    <div class="value"><?= htmlspecialchars($d['plan']['name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Calls Used</div>
                    <div class="value"><?= $d['usage']['calls_used'] ?? 0 ?> / <?= $d['plan']['calls_per_month'] ?? 0 ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Remaining</div>
                    <div class="value"><?= $d['usage']['calls_remaining'] ?? 0 ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Next Reset</div>
                    <div class="value" style="font-size:0.8rem"><?= $d['reset']['next_reset'] ?? 'N/A' ?></div>
                </div>
            </div>

        <?php else: ?>
            <!-- Raw JSON for other responses -->
            <div class="result-box">
                <button class="copy-btn" onclick="copyText('rawJson')">📋 Copy</button>
                <pre id="rawJsonDisplay"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                <textarea id="rawJson" style="position:absolute;left:-9999px"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>YoutubeTS API Tester &mdash; <a href="https://youtubets.com" target="_blank">youtubets.com</a> &mdash; MIT License</p>
    </div>
</div>

<script>
function showTab(tab) {
    // Just show the transcript form
    document.getElementById('tab-transcript').style.display = '';
}

function submitAction(action) {
    document.getElementById('actionInput').value = action;
    document.getElementById('actionForm').submit();
}

function copyText(id) {
    const el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
    alert('Copied to clipboard!');
}
</script>
</body>
</html>
