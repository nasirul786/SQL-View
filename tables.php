<?php
require 'auth.php';
if (!$pdo) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['db_name'])) {
    header("Location: databases.php");
    exit;
}

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Let's get rough row counts for aesthetics
$tableData = [];
foreach ($tables as $t) {
    try {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$t`");
        $tableData[] = [
            'name' => $t,
            'rows' => $countStmt->fetchColumn()
        ];
    } catch (Exception $e) {
        $tableData[] = [
            'name' => $t,
            'rows' => 'N/A'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tables - <?= htmlspecialchars($_SESSION['db_name']) ?></title>
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
        
        .db-badge {
            color: #3c4043;
            font-size: 14px;
            font-weight: 500;
            background: #f1f3f4;
            padding: 6px 16px;
            border-radius: 16px;
            border: 1px solid #dadce0;
        }
        
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 24px;
        }
        
        .action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .search-box {
            position: relative;
            width: 100%;
            max-width: 400px;
        }
        .search-box input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border-radius: 8px;
            border: 1px solid transparent;
            background-color: #f1f3f4;
            font-size: 16px;
            transition: background-color 0.2s, box-shadow 0.2s, border 0.2s;
        }
        .search-box input:focus {
            background-color: #fff;
            outline: none;
            box-shadow: 0 1px 1px 0 rgba(65,69,73,0.3), 0 1px 3px 1px rgba(65,69,73,0.15);
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
        
        .table-card {
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: box-shadow 0.2s;
        }
        .table-card:hover {
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        
        .table-icon {
            width: 40px;
            height: 40px;
            background-color: #e8f0fe;
            color: #1a73e8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 16px;
        }
        
        .table-details {
            flex-grow: 1;
            overflow: hidden;
        }
        .table-details h3 {
            font-size: 16px;
            font-weight: 500;
            margin: 0 0 4px 0;
            color: #202124;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-details p {
            margin: 0;
            font-size: 14px;
            color: #5f6368;
        }
        
        .btn-primary-google {
            background-color: #1a73e8;
            color: white;
            border: none;
            padding: 8px 24px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
        }
        .btn-primary-google:hover {
            background-color: #1b66c9;
            color: white;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        .btn-primary-google i { margin-right: 8px; }

    </style>
</head>
<body>

<header class="header">
    <div class="d-flex align-items-center">
        <a href="tables.php" class="logo-text me-4">
            <span class="sql">SQL</span> View
        </a>
        <div class="db-badge d-none d-md-block">
            <i class="bi bi-database me-1"></i> <?= htmlspecialchars($_SESSION['db_name']) ?>
        </div>
    </div>
    <div class="d-flex align-items-center">
        <a href="export.php?export_all=1" class="btn-outline me-3 d-none d-md-block" style="color: #188038; border-color: #188038;">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Entire DB
        </a>
        <a href="logout.php" class="btn-outline">Log out</a>
    </div>
</header>

<main class="content">
    <div class="action-bar flex-column flex-md-row">
        <h1 class="page-title mb-3 mb-md-0 d-flex align-items-center">
            Database Tables
            <span class="ms-3 text-muted" style="font-size: 16px;">(<?= count($tableData) ?> items)</span>
        </h1>
        
        <div class="d-flex w-100 justify-content-md-end justify-content-start" style="max-width: 400px;">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="tableSearch" placeholder="Search tables">
            </div>
        </div>
    </div>

    <div class="grid-container" id="tableContainer">
        <?php foreach ($tableData as $table): ?>
        <a href="view.php?table=<?= urlencode($table['name']) ?>" class="table-card table-item">
            <div class="table-icon">
                <i class="bi bi-table"></i>
            </div>
            <div class="table-details">
                <h3><?= htmlspecialchars($table['name']) ?></h3>
                <p><?= number_format((int)$table['rows']) ?> rows</p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($tableData)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-folder2-open" style="font-size: 48px; color: #dadce0;"></i>
            <p class="mt-3" style="font-size: 16px;">No tables found.</p>
        </div>
    <?php endif; ?>
</main>

<script>
document.getElementById('tableSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.table-item');
    let hasVisible = false;
    items.forEach(item => {
        const text = item.querySelector('h3').textContent.toLowerCase();
        if (text.includes(term)) {
            item.style.display = 'flex';
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
