<?php
// modules/reports/print_journal.php
ini_set('pcre.backtrack_limit', '5000000');
ini_set('max_execution_time', '300'); 

require_once '../../vendor/autoload.php';
include('../../core/rms.php');
$object = new rms();

if (!$object->is_login() || !$object->is_master_user()) { exit; }

$format = $_GET['format'] ?? 'html';
$repp = $_GET['repp'] ?? 'all_modules';
$department = trim($_GET['department'] ?? '');
$researcher_id = trim($_GET['researcher_id'] ?? '');
$status = strtolower(trim($_GET['status'] ?? 'all'));
$from_date = trim($_GET['from_date'] ?? '');
$to_date = trim($_GET['to_date'] ?? '');

$is_all_time = empty($from_date) && empty($to_date);
$from_date_ts = empty($from_date) ? 0 : strtotime($from_date);
$to_date_ts = empty($to_date) ? PHP_INT_MAX : strtotime($to_date) + 86399;

function getSafeRows($obj) {
    try {
        $result = $obj->get_result();
        if ($result && is_object($result) && method_exists($result, 'fetchAll')) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { }
    return [];
}

$html_chunks = [];

// ==============================================================================
// UNIVERSAL PORTFOLIO/MODULE DOCUMENT GENERATOR (TABLE FORMAT)
// ==============================================================================

// Header Setup
$header_title = "Data Extraction Report";
$header_subtitle = "Comprehensive Summary";

if (!empty($researcher_id)) {
    $object->query = "SELECT * FROM tbl_researchdata WHERE id = '$researcher_id'";
    $res_info = getSafeRows($object);
    if (count($res_info) > 0) {
        $name = $res_info[0]['firstName'] . ' ' . $res_info[0]['middleName'] . ' ' . $res_info[0]['familyName'] . ' ' . $res_info[0]['Suffix'];
        $header_title = strtoupper(trim($name));
        $header_subtitle = $res_info[0]['department'] . " • Official Portfolio";
    }
} else {
    $header_title = ($repp === 'all_modules') ? "All Modules Overview" : "Module Report (" . ucwords(str_replace(['tbl_', '_'], ['', ' '], $repp)) . ")";
    $header_subtitle = !empty($department) ? $department : "All Departments";
}

$html_chunks[] = "
<table width='100%' style='margin-bottom: 20px; border-bottom: 3px solid #2c7be5; font-family: Arial, sans-serif;'>
    <tr>
        <td style='text-align: center; padding-bottom: 15px;'>
            <h1 style='font-size: 26px; font-weight: bold; color: #2c3e50; margin: 0; text-transform: uppercase;'>{$header_title}</h1>
            <div style='font-size: 14px; color: #6e84a3; margin-top: 5px;'>{$header_subtitle}</div>
        </td>
    </tr>
</table>";

$all_modules = [
    ['title' => 'Research Conducted', 'table' => 'tbl_researchconducted', 'date_col' => 'started_date', 'has_status' => true],
    ['title' => 'Publications', 'table' => 'tbl_publication', 'date_col' => 'publication_date', 'has_status' => false],
    ['title' => 'Intellectual Property', 'table' => 'tbl_itelectualprop', 'date_col' => 'date_granted', 'has_status' => false],
    ['title' => 'Paper Presentations', 'table' => 'tbl_paperpresentation', 'date_col' => 'date_paper', 'has_status' => false],
    ['title' => 'Extension Projects', 'table' => 'tbl_extension_project_conducted', 'date_col' => 'start_date', 'has_status' => true],
    ['title' => 'Trainings Attended', 'table' => 'tbl_trainingsattended', 'date_col' => 'date_train', 'has_status' => false]
];

$modules_to_run = ($repp === 'all_modules') ? $all_modules : array_filter($all_modules, function($m) use ($repp) { return $m['table'] === $repp; });

// Exclusion keys for the dynamic details column
$exclude_keys = ['id', 'researcherid', 'lead_researcher_id', 'lead_author_id', 'title', 'stat', 'status_exct', 'status', 'so_file', 'moa_file', 'department', 'lead_researcher', 'co_researchers', 'coauth', 'user_created_on', 'has_files', 'all_authors', 'primary_familyname', 'author_db_id'];

foreach ($modules_to_run as $mod) {
    
    // Module Table Title
    $html_chunks[] = "
    <h3 style='color: #2c7be5; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 25px; margin-bottom: 10px;'>
        {$mod['title']}
    </h3>";
    
    // Start Office Table Layout
    $html_chunks[] = "
    <table width='100%' border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; border-color: #d1d3e2; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 20px;'>
        <thead style='background-color: #f8f9fc; color: #4e73df; font-size: 11px; text-transform: uppercase;'>
            <tr>
                <th width='25%' align='left' style='padding: 10px;'>Title</th>
                <th width='15%' align='left' style='padding: 10px;'>Lead Proponent</th>
                <th width='15%' align='left' style='padding: 10px;'>Co-Authors</th>";
                
    if ($mod['has_status']) {
        $html_chunks[] = "<th width='10%' align='center' style='padding: 10px;'>Status</th>";
    }

    $html_chunks[] = "
                <th width='35%' align='left' style='padding: 10px;'>Specific Details</th>
            </tr>
        </thead>
        <tbody>";
    
    // Query Building
    $col_table = ''; $col_fk = '';
    if ($mod['table'] == 'tbl_researchconducted') { $col_table = 'tbl_research_collaborators'; $col_fk = 'research_id'; }
    if ($mod['table'] == 'tbl_publication') { $col_table = 'tbl_publication_collaborators'; $col_fk = 'publication_id'; }
    if ($mod['table'] == 'tbl_paperpresentation') { $col_table = 'tbl_paper_collaborators'; $col_fk = 'paper_id'; }
    
    $co_author_subquery = "''";
    if ($col_table !== '') {
        $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') FROM $col_table col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.$col_fk = r.id AND col.researcher_id != r.researcherID)";
    } elseif ($mod['table'] == 'tbl_itelectualprop') {
        $co_author_subquery = "r.coauth";
    }

    $query = "SELECT r.*, d.department, CONCAT(d.firstName, ' ', d.familyName) AS Lead_Researcher, {$co_author_subquery} AS Co_Researchers FROM {$mod['table']} r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.status = 1";

    if (!empty($researcher_id)) {
        if ($col_table !== '') {
            $query .= " AND (r.researcherID = '$researcher_id' OR r.id IN (SELECT $col_fk FROM $col_table WHERE researcher_id = '$researcher_id'))";
        } else {
            $query .= " AND r.researcherID = '$researcher_id'";
        }
    }
    if (!empty($department)) {
        $query .= " AND d.department = '" . addslashes($department) . "'";
    }

    $object->query = $query;
    $items = getSafeRows($object);
    $found_items = false;
    
    foreach ($items as $item) {
        $date_matched = false;
        if ($is_all_time) { $date_matched = true; } 
        else {
            if (!empty($item[$mod['date_col']])) {
                $val_clean = trim((string)$item[$mod['date_col']]);
                if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) { $val_clean = "01-" . $val_clean; }
                $ts = strtotime($val_clean);
                if ($ts !== false && $ts >= $from_date_ts && $ts <= $to_date_ts) { $date_matched = true; }
            }
        }

        $status_matched = true;
        if ($mod['has_status'] && $status !== 'all') {
            $stat_val = strtolower($item['stat'] ?? $item['status_exct'] ?? $item['status'] ?? '');
            if ($status === 'completed' && strpos($stat_val, 'complet') === false && strpos($stat_val, 'finish') === false) { $status_matched = false; }
            elseif ($status === 'ongoing' && !preg_match('/ongoing|on-going|on going|progress|active|implement/i', $stat_val)) { $status_matched = false; }
        } elseif (!$mod['has_status'] && $status !== 'all') {
            $status_matched = false; 
        }

        if ($date_matched && $status_matched) {
            $found_items = true;
            $title_clean = htmlspecialchars($item['title'] ?? 'Untitled');
            
            // =========================================================
            // DYNAMIC DETAIL EXTRACTOR (Stacks cleanly into the last column)
            // =========================================================
            $detail_str = "";
            foreach ($item as $k => $v) {
                $kl = strtolower($k);
                if (in_array($kl, $exclude_keys) || trim((string)$v) === '' || is_numeric($k)) continue;
                
                $clean_label = ucwords(str_replace('_', ' ', $k));
                $clean_val = htmlspecialchars((string)$v);
                
                $detail_str .= "<div style='margin-bottom: 4px;'><strong style='color:#333333;'>{$clean_label}:</strong> <span style='color:#555555;'>{$clean_val}</span></div>";
            }
            if ($detail_str === "") $detail_str = "<span style='color: #999; font-style: italic;'>No extra details available</span>";
            // =========================================================

            $co_authors_display = !empty($item['Co_Researchers']) ? htmlspecialchars($item['Co_Researchers']) : '<span style="color: #999; font-style: italic;">None</span>';

            // ROW DATA
            $html_chunks[] = "<tr style='page-break-inside: avoid;'>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'><strong>{$title_clean}</strong></td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$item['Lead_Researcher']}</td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$co_authors_display}</td>";
            
            if ($mod['has_status']) {
                $s_val = htmlspecialchars($item['stat'] ?? $item['status_exct'] ?? 'Unknown');
                $html_chunks[] = "<td valign='top' align='center' style='padding: 10px; color: #12263f;'><strong>{$s_val}</strong></td>";
            }

            $html_chunks[] = "<td valign='top' style='padding: 10px;'>{$detail_str}</td>";
            $html_chunks[] = "</tr>";
        }
    }
    
    // Close the table
    if (!$found_items) {
        $colspan = $mod['has_status'] ? 5 : 4;
        $html_chunks[] = "<tr><td colspan='{$colspan}' align='center' style='color: #858796; font-style: italic; padding: 20px;'>No records found for this category.</td></tr>";
    }
    $html_chunks[] = "</tbody></table>";
}

$doc_name = (!empty($researcher_id)) ? 'Researcher_Portfolio_' : 'Module_Report_';

// Output Generation
if ($format === 'word') {
    $raw_html = implode("\n", $html_chunks);
    
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment;filename=\"" . $doc_name . time() . ".doc\"");
    
    echo "<html xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns:m='http://schemas-microsoft.com/office/2004/12/omml' xmlns='http://www.w3.org/TR/REC-html40'>";
    echo "<head>";
    echo "<meta charset='utf-8'><title>Report</title>";
    echo "<style>";
    // FORCES THE WORD DOC INTO LANDSCAPE MODE SO THE TABLE FITS PERFECTLY!
    echo "@page WordSection1 { size: 11.0in 8.5in; mso-page-orientation: landscape; margin: 0.8in; }";
    echo "div.WordSection1 { page: WordSection1; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='WordSection1'>";
    echo $raw_html;
    echo "</div>";
    echo "</body></html>";
    exit;

} else {
    try {
        if (!class_exists('\Mpdf\Mpdf')) {
            die("Error: mPDF library is not installed.");
        }
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4-L', // FORCES PDF INTO LANDSCAPE MODE (A4-Landscape)
            'tempDir' => __DIR__ . '/tmp'
        ]);
        
        foreach ($html_chunks as $chunk) {
            $mpdf->WriteHTML($chunk);
        }
        
        $mpdf->Output($doc_name . time() . '.pdf', 'D');
        
    } catch (\Mpdf\MpdfException $e) { 
        echo 'Error generating PDF: ' . $e->getMessage(); 
    }
}
?>