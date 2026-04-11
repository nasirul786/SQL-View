<?php
require_once 'auth.php';
if (isset($_SESSION['db_host'])) {
    if (empty($_SESSION['db_name'])) {
        header("Location: databases.php");
    } else {
        header("Location: tables.php");
    }
    exit;
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL View - Sign in</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Roboto', Arial, sans-serif; 
            background-color: #fff;
            color: #202124;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 40px 20px;
            background: #fff;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 500;
            color: #202124;
            margin-bottom: 8px;
            text-align: center;
        }
        .logo-text span.sql { color: #1a73e8; font-weight: 700; }
        .logo-text span.view { color: #5f6368; }
        
        .signin-heading {
            font-size: 24px;
            font-weight: 400;
            text-align: center;
            margin-bottom: 8px;
            color: #202124;
        }
        .signin-subheading {
            font-size: 16px;
            text-align: center;
            color: #202124;
            margin-bottom: 32px;
            font-weight: 400;
        }
        .form-floating > .form-control {
            border-radius: 4px;
            border: 1px solid #dadce0;
        }
        .form-floating > .form-control:focus {
            border-color: #1a73e8;
            box-shadow: none;
            border-width: 2px;
            padding-left: 11px; /* offset the pixel width jump */
        }
        .form-floating > label {
            color: #5f6368;
        }
        .btn-google {
            background-color: #1a73e8;
            color: white;
            font-weight: 500;
            border-radius: 4px;
            padding: 10px 24px;
            border: none;
            letter-spacing: 0.25px;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .btn-google:hover {
            background-color: #1b66c9;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        .error-message {
            color: #d93025;
            font-size: 14px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .error-message i { margin-right: 8px; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="logo-text">
        <span class="sql">SQL</span><span class="view">View</span>
    </div>
    <h1 class="signin-heading">Sign in</h1>
    <p class="signin-subheading">to access your database</p>
    
    <?php if ($error): ?>
        <div class="error-message">
            <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form action="login.php" method="POST" id="loginForm">
        <div class="row g-3 mb-3">
            <div class="col-sm-8">
                <div class="form-floating">
                    <input type="text" class="form-control" id="host" name="host" placeholder="Host" value="localhost" required>
                    <label for="host">Host</label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-floating">
                    <input type="number" class="form-control" id="port" name="port" placeholder="Port" value="3306" required>
                    <label for="port">Port</label>
                </div>
            </div>
        </div>
        
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="database" name="database" placeholder="Database">
            <label for="database">Database Name (Optional)</label>
        </div>
        
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            <label for="username">Username</label>
        </div>
        
        <div class="form-floating mb-4">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            <label for="password">Password</label>
        </div>
        
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-google">Connect</button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.startsWith('mysql ')) {
            parseMysqlCommand(value);
        }
    });
});

function parseMysqlCommand(cmd) {
    const parts = cmd.split(/\s+/);
    const config = {
        host: 'localhost',
        port: '3306',
        user: '',
        pass: '',
        db: ''
    };

    for (let i = 0; i < parts.length; i++) {
        const part = parts[i];
        if (part === '-h' || part === '--host') {
            config.host = parts[++i];
        } else if (part.startsWith('-h')) {
            config.host = part.substring(2);
        } else if (part === '-u' || part === '--user') {
            config.user = parts[++i];
        } else if (part.startsWith('-u')) {
            config.user = part.substring(2);
        } else if (part === '-p' || part === '--password') {
            // Check if next part is not another flag
            if (parts[i+1] && !parts[i+1].startsWith('-')) {
                config.pass = parts[++i];
            }
        } else if (part.startsWith('-p')) {
            config.pass = part.substring(2);
        } else if (part === '-P' || part === '--port') {
            config.port = parts[++i];
        } else if (part.startsWith('-P')) {
            config.port = part.substring(2);
        } else if (!part.startsWith('-') && part !== 'mysql' && i === parts.length - 1) {
            // Very last part often the DB name
            config.db = part;
        }
    }

    if (config.host) document.getElementById('host').value = config.host;
    if (config.port) document.getElementById('port').value = config.port;
    if (config.user) document.getElementById('username').value = config.user;
    if (config.pass) document.getElementById('password').value = config.pass;
    if (config.db) document.getElementById('database').value = config.db;
}
</script>
</body>
</html>
