<?php
require 'auth.php';
if (!$pdo) {
    header("Location: index.php");
    exit;
}

$table = $_GET['table'] ?? '';
if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    die("Invalid table name.");
}

$stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$params = [];
$whereClauses = [];

if ($search !== '') {
    foreach ($columns as $col) {
        $whereClauses[] = "`{$col['Field']}` LIKE ?";
        $params[] = '%' . $search . '%';
    }
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(' OR ', $whereClauses);
}

$countQuery = "SELECT COUNT(*) FROM `$table` $whereSql";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$dataQuery = "SELECT * FROM `$table` $whereSql LIMIT $limit OFFSET $offset";
$dataStmt = $pdo->prepare($dataQuery);
$dataStmt->execute($params);
$rows = $dataStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($table) ?> - SQL View</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', Arial, sans-serif; background-color: #fff; color: #202124; margin: 0; padding: 0; }
        
        .header { display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; border-bottom: 1px solid #dadce0; background: #fff; position: sticky; top: 0; z-index: 100;}
        .logo-text { font-size: 22px; font-weight: 500; color: #5f6368; text-decoration: none; }
        .logo-text span.sql { color: #1a73e8; font-weight: 700; }
        
        .btn-icon {
            color: #5f6368; background: transparent; border: none; border-radius: 50%; width: 40px; height: 40px;
            display: inline-flex; align-items: center; justify-content: center; font-size: 20px; transition: background-color 0.2s;
            text-decoration: none; margin-right: 8px;
        }
        .btn-icon:hover { background-color: #f1f3f4; color: #202124; }
        
        .breadcrumb-text { font-size: 18px; color: #3c4043; margin: 0; display: flex; align-items: center;}
        
        .content { margin: 0 auto; padding: 24px; }
        
        .action-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;}
        
        .search-box { position: relative; width: 100%; max-width: 500px; }
        .search-box input {
            width: 100%; padding: 12px 12px 12px 48px; border-radius: 8px; border: 1px solid transparent; background-color: #f1f3f4;
            font-size: 16px; transition: background-color 0.2s, box-shadow 0.2s, border 0.2s;
        }
        .search-box input:focus { background-color: #fff; outline: none; box-shadow: 0 1px 1px 0 rgba(65,69,73,0.3), 0 1px 3px 1px rgba(65,69,73,0.15); border: 1px solid transparent; }
        .search-box i.search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #5f6368; }
        .search-box .clear-icon { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #5f6368; cursor: pointer; display: none; }
        
        .btn-primary-google { background-color: #1a73e8; color: white; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 500; text-decoration: none; transition: background-color 0.2s, box-shadow 0.2s; display: inline-flex; align-items: center; }
        .btn-primary-google:hover { background-color: #1b66c9; color: white; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); }
        .btn-sheets { background-color: #188038; border-radius: 20px; padding: 8px 16px;}
        .btn-sheets:hover { background-color: #157332; }

        .table-responsive-custom {
            border: 1px solid #dadce0; border-radius: 8px; overflow-x: auto;
        }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .data-table th, .data-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #dadce0; }
        .data-table th { background-color: #fff; font-weight: 500; color: #5f6368; border-bottom: 2px solid #dadce0; white-space: nowrap; }
        .data-table tbody tr:hover { background-color: #f8f9fa; }
        .data-table td { color: #202124; border-right: 1px solid #f1f3f4; padding: 8px 16px; vertical-align: middle; }
        .data-table td:last-child { border-right: none; }
        
        .cell-content {
            display: inline-block;
            max-width: 350px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .view-more-link {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
            margin-left: 4px;
            font-size: 13px;
            white-space: nowrap;
        }
        .view-more-link:hover { text-decoration: underline; }
        
        .null-badge { color: #5f6368; font-style: italic; background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        
        .pagination-container { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; color: #5f6368; font-size: 14px;}
        .pagination a { color: #1a73e8; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin: 0 2px; }
        .pagination a:hover { background-color: #f4fafe; }
        .pagination .active { background-color: #e8f0fe; color: #1a73e8; font-weight: 500;}
        .pagination .disabled { color: #aaa; pointer-events: none; }
    </style>
</head>
<body>

<header class="header">
    <div class="d-flex align-items-center">
        <a href="tables.php" class="btn-icon" title="Back to tables"><i class="bi bi-arrow-left"></i></a>
        <h2 class="breadcrumb-text fw-normal" style="font-size: 20px;">
            <i class="bi bi-table me-2 text-muted"></i> <?= htmlspecialchars($table) ?>
        </h2>
    </div>
    <div>
        <a href="export.php?table=<?= urlencode($table) ?>&search=<?= urlencode($search) ?>" class="btn-primary-google btn-sheets">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export
        </a>
    </div>
</header>

<main class="content">

    <div class="action-bar">
        <form class="search-box" method="GET" action="view.php">
            <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="search" placeholder="Filter records in <?= htmlspecialchars($table) ?>" value="<?= htmlspecialchars($search) ?>">
            <?php if ($search !== ''): ?>
                <a href="view.php?table=<?= urlencode($table) ?>" class="clear-icon" style="display: block; text-decoration: none;"><i class="bi bi-x-circle-fill"></i></a>
            <?php endif; ?>
        </form>
        <div style="font-size: 14px; color: #5f6368;">
            <?= number_format($totalRows) ?> records found
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th title="<?= htmlspecialchars($col['Type']) ?>">
                            <?= htmlspecialchars($col['Field']) ?>
                            <?php if(strpos(strtolower($col['Key']), 'pri') !== false): ?>
                                <i class="bi bi-key" style="color: #f29900; margin-left: 4px;" title="Primary Key"></i>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td title="<?= htmlspecialchars($col['Field']) ?>">
                                <?php 
                                    $val = $row[$col['Field']] ?? null;
                                    if ($val === null) {
                                        echo '<span class="null-badge">NULL</span>';
                                    } else {
                                        $strVal = (string)$val;
                                        if (mb_strlen($strVal) > 50) {
                                            $shortText = htmlspecialchars(mb_substr($strVal, 0, 50));
                                            $fullTextEscaped = htmlspecialchars($strVal, ENT_QUOTES, 'UTF-8');
                                            echo '<span class="cell-content">' . $shortText . '</span>';
                                            echo '<a href="#" class="view-more-link" data-fulltext="' . $fullTextEscaped . '">...more</a>';
                                        } else {
                                            echo '<span class="cell-content">' . htmlspecialchars($strVal) . '</span>';
                                        }
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= count($columns) ?>" style="text-align: center; padding: 48px; color: #5f6368;">
                            No records found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container">
        <div>
            Page <?= $page ?> of <?= $totalPages ?>
        </div>
        <div class="pagination">
            <a href="?table=<?= urlencode($table) ?>&search=<?= urlencode($search) ?>&page=<?= max(1, $page - 1) ?>" class="<?= ($page <= 1) ? 'disabled' : '' ?>"><i class="bi bi-chevron-left"></i></a>
            
            <?php
            $startPage = max(1, $page - 3);
            $endPage = min($totalPages, $page + 3);
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?table=<?= urlencode($table) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <a href="?table=<?= urlencode($table) ?>&search=<?= urlencode($search) ?>&page=<?= min($totalPages, $page + 1) ?>" class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- Full Text Modal -->
<div class="modal fade" id="fullTextModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" style="font-weight: 500; font-size: 18px;">Full Cell Content</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modalFullTextContent" style="white-space: pre-wrap; word-break: break-all; font-family: 'Roboto Mono', monospace; font-size: 14px; background: #f8f9fa; padding: 16px; border-radius: 8px; border: 1px solid #dadce0;"></div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 4px;">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewMoreLinks = document.querySelectorAll('.view-more-link');
    const fullTextModal = new bootstrap.Modal(document.getElementById('fullTextModal'));
    const modalContent = document.getElementById('modalFullTextContent');

    viewMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const fullText = this.getAttribute('data-fulltext');
            modalContent.textContent = fullText;
            fullTextModal.show();
        });
    });
});
</script>
</html>
