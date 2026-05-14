<?php
/**
 * VIEW ADMINS - Emergency Administrative Session Management
 * This utility allows for the identification and manual reset of administrative sessions.
 * Note: For security, please delete this file or move it to a secure location after use.
 */

// Bootstrap Laravel to access Models and Database
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Handle Reset Action
$message = '';
if (isset($_POST['reset_id'])) {
    $id = (int)$_POST['reset_id'];
    $user = User::find($id);
    if ($user && $user->is_admin) {
        $user->is_online = false;
        $user->save();
        
        // Log the action for audit integrity
        try {
            \App\Models\SystemLog::create([
                'user_id' => null,
                'event_type' => 'SECURITY',
                'action' => 'EMERGENCY_SESSION_RESET',
                'description' => "Administrative session for {$user->name} (@{$user->username}) was manually terminated via VIEW ADMINS emergency utility.",
                'severity' => 'warning',
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        } catch (\Exception $e) {}
        
        $message = "Session for <b>{$user->name}</b> has been successfully terminated. You may now attempt to log in.";
    }
}

$admins = User::where('is_admin', true)->get();

// SELF-REPAIR: If the person viewing this page is an authenticated admin, 
// ensure their status is synced to ONLINE immediately.
if (Auth::check() && Auth::user()->is_admin) {
    if (!Auth::user()->is_online) {
        Auth::user()->update(['is_online' => true]);
        // Refresh the collection to show updated data
        $admins = User::where('is_admin', true)->get();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW ADMINS | Strategic Command Utility</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --danger: #ef4444;
            --success: #10b981;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: -0.05em;
            text-transform: uppercase;
            background: linear-gradient(to right, #818cf8, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .header p {
            color: #94a3b8;
            font-size: 0.95rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .alert {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            text-align: left;
            padding: 15px 20px;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #94a3b8;
            border: 1px solid var(--border);
        }

        .name-box b {
            display: block;
            font-size: 1rem;
            color: #f8fafc;
        }

        .name-box span {
            font-size: 0.8rem;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-online {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .status-offline {
            background: rgba(148, 163, 184, 0.1);
            color: #94a3b8;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-online .status-dot {
            box-shadow: 0 0 10px #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.4; }
            100% { opacity: 1; }
        }

        .btn-reset {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background: var(--danger);
            color: white;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
            transform: translateY(-2px);
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(1);
        }

        .footer-note {
            text-align: center;
            margin-top: 30px;
            color: #475569;
            font-size: 0.8rem;
        }

        .footer-note code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>VIEW ADMINS</h1>
            <p>Strategic Command Emergency Protocol: Direct visibility into administrative presence and session authority.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Administrator Identity</th>
                        <th>Presence Status</th>
                        <th>Last Active</th>
                        <th>Action Protocol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #475569;">
                            #<?php echo $admin->id; ?>
                        </td>
                        <td>
                            <div class="admin-info">
                                <div class="avatar">
                                    <?php 
                                        $initials = '';
                                        $names = explode(' ', $admin->name);
                                        foreach($names as $n) $initials .= strtoupper($n[0]);
                                        echo substr($initials, 0, 2);
                                    ?>
                                </div>
                                <div class="name-box">
                                    <b><?php echo $admin->name; ?></b>
                                    <span>@<?php echo $admin->username; ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($admin->is_online): ?>
                                <div class="status-badge status-online">
                                    <div class="status-dot"></div>
                                    ONLINE
                                </div>
                            <?php else: ?>
                                <div class="status-badge status-offline">
                                    <div class="status-dot"></div>
                                    OFFLINE
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #94a3b8; font-family: 'JetBrains Mono', monospace;">
                                <?php echo $admin->last_login_at ? $admin->last_login_at->format('Y-m-d H:i') : 'Never'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="reset_id" value="<?php echo $admin->id; ?>">
                                <button type="submit" class="btn-reset <?php echo !$admin->is_online ? 'btn-disabled' : ''; ?>" <?php echo !$admin->is_online ? 'disabled' : ''; ?>>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
                                    TERMINATE SESSION
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-note">
            Security Advisory: Delete <code>view_admins.php</code> from the public directory after restoring access.
        </div>
    </div>

</body>
</html>
