<?php
require 'auth.php';
require 'vendor/autoload.php';
use Shuchkin\SimpleXLSXGen;

if (!$pdo) {
    header("Location: index.php");
    exit;
}

$dbName = $_SESSION['db_name'];
$exportAll = $_GET['export_all'] ?? false;
$table = $_GET['table'] ?? '';
$search = $_GET['search'] ?? '';

// Validate request
if (!$exportAll && (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table))) {
    die("Invalid request.");
}

$tablesToExport = [];
if ($exportAll) {
    $stmt = $pdo->query("SHOW TABLES");
    $tablesToExport = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $tablesToExport = [$table];
}

$xlsx = null;

foreach ($tablesToExport as $currentTable) {
    $whereSql = '';
    $params = [];
    $stmtCols = $pdo->query("SHOW COLUMNS FROM `$currentTable`");
    $columns = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
    $headerFields = [];
    
    // Applying the exact style requested by user: black background, yellow text, bold, centered
    foreach ($columns as $col) {
        $headerFields[] = "<center><style fill=\"#000000\" font-weight=\"bold\" color=\"#FFFF00\">" . htmlspecialchars($col['Field']) . "</style></center>";
    }
    
    $sheetData = [];
    $sheetData[] = $headerFields;
    
    // If exporting a single table with search string
    if (!$exportAll && $search !== '') {
        $whereClauses = [];
        foreach ($columns as $col) {
            $whereClauses[] = "`{$col['Field']}` LIKE ?";
            $params[] = '%' . $search . '%';
        }
        if (!empty($whereClauses)) {
            $whereSql = "WHERE " . implode(' OR ', $whereClauses);
        }
    }

    $dataQuery = "SELECT * FROM `$currentTable` $whereSql";
    // We maintain buffered because we are fetching into an array.
    // For exceedingly large database tables >100M rows, PHP memory limit might hit.
    // However, generating XLSX requires memory anyway in SimpleXLSXGen unless streamed.
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute($params);

    while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
        // Replace NULL with explicitly empty or string version if needed
        $cleanRow = [];
        foreach($row as $val) {
            $cleanRow[] = $val === null ? '' : (string)$val;
        }
        $sheetData[] = $cleanRow;
    }
    
    // User requested sheet name to be the database name if possible. 
    // This is possible if we are only exporting a single specific table.
    // Excel cannot have multiple duplicate sheet names.
    $rawSheetName = $exportAll ? $currentTable : $dbName;
    $sheetName = substr(str_replace(['[', ']', '*', '/', '\\', '?', ':'], '', $rawSheetName), 0, 31);
    
    // Fallback if sheetname becomes empty
    if (empty($sheetName)) $sheetName = "Sheet"; 

    if ($xlsx === null) {
        $xlsx = SimpleXLSXGen::fromArray($sheetData, $sheetName);
    } else {
        $xlsx->addSheet($sheetData, $sheetName);
    }
}

// Ensure safe filename
$dateStr = date('Y-m-d_His');
if ($exportAll) {
    $filename = "{$dbName}_FullDB_GoogleSheets_{$dateStr}.xlsx";
} else {
    $filename = "{$dbName}_{$table}_GoogleSheets_{$dateStr}.xlsx";
}

if ($xlsx !== null) {
    $xlsx->downloadAs($filename);
} else {
    echo "No data to export.";
}
exit;
