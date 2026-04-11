<?php
require 'auth.php';
if (!$pdo) {
    header("Location: index.php");
    exit;
}

// If a database is selected via GET
if (isset($_GET['select'])) {
    $_SESSION['db_name'] = $_GET['select'];
    header("Location: tables.php");
    exit;
}

try {
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching databases: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Database - SQL View</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #fff;
            color: #202124;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            border-bottom: 1px solid #dadce0;
        }
        .logo-text {
            font-size: 22px;
            font-weight: 500;
            color: #5f6368;
            text-decoration: none;
        }
        .logo-text span.sql { color: #1a73e8; font-weight: 700; }
        
        .btn-outline {
            border: 1px solid #dadce0;
            color: #1a73e8;
            background: transparent;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 4px;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        .btn-outline:hover {
            background-color: #f4fafe;
        }
        
        .content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 32px;
            text-align: center;
        }
        
        .db-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        
        .db-card {
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }
        .db-card:hover {
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            border-color: #1a73e8;
        }
        
        .db-icon {
            width: 56px;
            height: 56px;
            background-color: #f8f9fa;
            color: #5f6368;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        .db-card:hover .db-icon {
            background-color: #e8f0fe;
            color: #1a73e8;
        }
        
        .db-name {
            font-size: 16px;
            font-weight: 500;
            color: #202124;
            text-align: center;
            word-break: break-all;
        }
        
        .search-container {
            max-width: 500px;
            margin: 0 auto 40px auto;
        }
        .search-box {
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 12px 12px 12px 48px;
            border-radius: 24px;
            border: 1px solid #dadce0;
            background-color: #fff;
            font-size: 16px;
            transition: all 0.2s;
        }
        .search-box input:focus {
            outline: none;
            box-shadow: 0 1px 6px rgba(32,33,36,0.28);
            border-color: rgba(223,225,229,0);
        }
        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="d-flex align-items-center">
        <a href="logout.php" class="logo-text">
            <span class="sql">SQL</span> View
        </a>
    </div>
    <div class="d-flex align-items-center">
        <a href="logout.php" class="btn-outline">Log out</a>
    </div>
</header>

<main class="content">
    <h1 class="page-title">Select a Database</h1>

    <div class="search-container">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="dbSearch" placeholder="Search databases...">
        </div>
    </div>

    <div class="db-grid" id="dbContainer">
        <?php foreach ($databases as $db): ?>
        <a href="?select=<?= urlencode($db) ?>" class="db-card db-item">
            <div class="db-icon">
                <i class="bi bi-layers"></i>
            </div>
            <div class="db-name"><?= htmlspecialchars($db) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</main>

<script>
document.getElementById('dbSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.db-item');
    items.forEach(item => {
        const name = item.querySelector('.db-name').textContent.toLowerCase();
        if (name.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
