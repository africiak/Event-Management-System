<?php
session_start();
require 'connection.php';
require_once __DIR__ . '/../vendor/autoload.php'; // mPDF autoloader

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch data
try {
    $stmt = $pdo->prepare("
        SELECT 
            e.name AS event_name,
            e.budget AS allocated_budget,
            IFNULL(SUM(b.amount), 0) AS used_budget,
            (e.budget - IFNULL(SUM(b.amount), 0)) AS remaining_budget,
            ROUND((IFNULL(SUM(b.amount), 0) / e.budget) * 100, 2) AS efficiency
        FROM events e
        LEFT JOIN budget_items b ON e.event_id = b.eventid
        WHERE e.status = 'Approved' AND e.activity_status = 'active'
        GROUP BY e.event_id
        ORDER BY efficiency DESC
    ");
    $stmt->execute();
    $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Convert logo to base64
$logoPath = '../img/logowhite.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}

// Prepare PDF
$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 30, // add top margin to avoid overlap with header
]);

// Set header
$mpdf->SetHTMLHeader('
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
        <img src="' . $logoBase64 . '" height="30" style="margin-right: 10px;">
        <span style="font-family: Poppins, sans-serif; font-size: 18px; font-weight: bold; color: #FF6D1F;">HiveFlow</span>
    </div>
');

// Set footer
$mpdf->SetHTMLFooter('
    <div style="text-align: center; font-size: 10px; font-family: Poppins, sans-serif; border-top: 1px solid #ccc; padding-top: 5px;">
        Generated on ' . date('F j, Y g:i A') . ' | Page {PAGENO} of {nb}
    </div>
');

// HTML content
$html = '
<style>
    body { font-family: "Poppins", sans-serif; font-size: 12px; }
    h2 { text-align: center; color: #14213D; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #999; padding: 6px 10px; text-align: left; }
    th { background-color: #14213D; color: white; }
    .badge { padding: 3px 6px; border-radius: 5px; color: white; font-size: 10px; }
    .efficient { background-color: #28a745; }
    .moderate { background-color: #ffc107; color: black; }
    .inefficient { background-color: #dc3545; }
</style>

<h2>Budget Efficiency Report</h2>
<p>
<strong>Budget Efficiency Guide:</strong><br>
<ul>
    <li><strong>Efficient</strong>: ≥ 90%</li>
    <li><strong>Moderate</strong>: 60% – 89%</li>
    <li><strong>Inefficient</strong>: &lt; 60%</li>
</ul>
</p>

<table>
    <thead>
        <tr>
            <th>Event Name</th>
            <th>Allocated Budget (KES)</th>
            <th>Used Budget (KES)</th>
            <th>Remaining (KES)</th>
            <th>Efficiency (%)</th>
        </tr>
    </thead>
    <tbody>
';

foreach ($report as $row) {
    $eff = (float)$row['efficiency'];
    $badge = $eff >= 90 ? '<span class="badge efficient">Efficient</span>' :
             ($eff >= 60 ? '<span class="badge moderate">Moderate</span>' :
             '<span class="badge inefficient">Inefficient</span>');

    $html .= '<tr>
        <td>' . htmlspecialchars($row['event_name']) . '</td>
        <td>' . number_format($row['allocated_budget'], 2) . '</td>
        <td>' . number_format($row['used_budget'], 2) . '</td>
        <td>' . number_format($row['remaining_budget'], 2) . '</td>
        <td>' . $eff . '% ' . $badge . '</td>
    </tr>';
}

$html .= '
    </tbody>
</table>
';

// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output('budget_efficiency_report.pdf', 'D'); // 'D' to download
?>