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
// UNIVERSAL PORTFOLIO/MODULE DOCUMENT GENERATOR
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
<table width='100%' style='margin-bottom: 30px; border-bottom: 3px solid #2c7be5; font-family: Arial, sans-serif;'>
    <tr>
        <td style='text-align: center; padding-bottom: 20px;'>
            <h1 style='font-size: 28px; font-weight: bold; color: #2c3e50; margin: 0; text-transform: uppercase;'>{$header_title}</h1>
            <div style='font-size: 16px; color: #6e84a3; margin-top: 5px;'>{$header_subtitle}</div>
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

foreach ($modules_to_run as $mod) {
    
    // Category Header (Table)
    $html_chunks[] = "
    <table width='100%' cellpadding='10' cellspacing='0' style='margin-top: 30px; margin-bottom: 15px; border-collapse: collapse;'>
        <tr>
            <td style='background-color: #2c7be5; color: #ffffff; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; text-transform: uppercase;'>
                {$mod['title']}
            </td>
        </tr>
    </table>";
    
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

    $query = "SELECT r.*, d.department, CONCAT(d.firstName, ' ', d.familyName) AS Lead_Researcher, {$co_author_subquery} AS Co_Researchers FROM {$mod['table']} r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE 1=1";

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
            // DYNAMIC DETAIL EXTRACTOR (Captures everything like Excel!)
            // =========================================================
            $exclude_keys = ['id', 'researcherid', 'title', 'stat', 'status_exct', 'status', 'so_file', 'moa_file', 'department', 'lead_researcher', 'co_researchers', 'user_created_on'];
            
            $detail_str = "<table width='100%' cellpadding='4' cellspacing='0' style='font-size: 13px; color: #555; margin-top: 10px; border-top: 1px solid #d1d3e2; padding-top: 10px;'>";
            $count = 0;
            
            foreach ($item as $k => $v) {
                $kl = strtolower($k);
                // Exclude system keys and empty fields
                if (in_array($kl, $exclude_keys) || trim((string)$v) === '' || is_numeric($k)) continue;
                
                // Format the Key (e.g., "funding_source" -> "Funding Source")
                $clean_label = ucwords(str_replace('_', ' ', $k));
                $clean_val = htmlspecialchars((string)$v);
                
                // Start a new row every 2 items for a nice grid
                if ($count % 2 == 0) $detail_str .= "<tr>";
                
                $detail_str .= "<td width='50%' valign='top' style='padding-bottom: 8px;'><strong>{$clean_label}:</strong> {$clean_val}</td>";
                
                if ($count % 2 == 1) $detail_str .= "</tr>";
                $count++;
            }
            
            // Close an odd row correctly
            if ($count % 2 != 0) { $detail_str .= "<td width='50%'></td></tr>"; }
            $detail_str .= "</table>";
            
            if ($count === 0) $detail_str = ""; // Clear if no extra details exist
            // =========================================================

            // Table-Based Card Layout (Works for Word & PDF beautifully)
            $html_chunks[] = "
            <table width='100%' cellpadding='15' cellspacing='0' style='margin-bottom: 15px; border-left: 4px solid #e3e6f0; background-color: #f8f9fc; font-family: Arial, sans-serif; page-break-inside: avoid; border-collapse: collapse;'>
                <tr>
                    <td style='border: 1px solid #eaecf4; border-left: none;'>
                        <div style='font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 8px;'>• {$title_clean}</div>
                        <div style='font-size: 14px; color: #333333; margin-bottom: 4px;'><strong>Lead Proponent:</strong> {$item['Lead_Researcher']}</div>";
            
            if (!empty($item['Co_Researchers'])) {
                $html_chunks[] = "<div style='font-size: 14px; color: #2c7be5; margin-bottom: 6px;'><strong>Co-Authors:</strong> {$item['Co_Researchers']}</div>";
            }
            
            if ($mod['has_status']) {
                $s_val = $item['stat'] ?? $item['status_exct'] ?? 'Unknown';
                $bg_color = preg_match('/ongoing|progress/i', $s_val) ? '#f6c23e' : '#1cc88a';
                $text_color = preg_match('/ongoing|progress/i', $s_val) ? '#333333' : '#ffffff';
                
                $html_chunks[] = "<div style='font-size: 14px; color: #555; margin-bottom: 6px;'><strong>Status:</strong> <span style='background-color: {$bg_color}; color: {$text_color}; padding: 4px 8px; font-size: 11px; font-weight: bold;'>$s_val</span></div>";
            }

            // INJECT ALL THE DYNAMIC DETAILS
            $html_chunks[] = $detail_str;

            $html_chunks[] = "
                    </td>
                </tr>
            </table>";
        }
    }
    
    if (!$found_items) {
        $html_chunks[] = "<div style='text-align: center; color: #858796; font-style: italic; padding: 20px; font-family: Arial, sans-serif;'>No records found for this category.</div>";
    }
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
    echo "@page WordSection1 { size: 8.5in 11in; margin: 1.0in; }";
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
            die("Error: mPDF library is not installed. Please make sure you have run 'composer install' in your root directory.");
        }
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4-P', 
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