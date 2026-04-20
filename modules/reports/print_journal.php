<?php
// modules/reports/print_journal.php
ini_set('pcre.backtrack_limit', '5000000');
ini_set('max_execution_time', '300'); 

require_once '../../vendor/autoload.php';
include('../../core/rms.php');
$object = new rms();

if (!$object->is_login() || !$object->is_master_user()) { exit; }

$format = $_GET['format'] ?? 'html';
$repp = $_GET['repp'] ?? 'all';
$department = trim($_GET['department'] ?? 'all');
$academic_rank = trim($_GET['academic_rank'] ?? 'all');
$program = trim($_GET['program'] ?? 'all');

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

$all_modules = [
    ['title' => 'Research Conducted', 'table' => 'tbl_researchconducted', 'date_col' => 'started_date', 'has_status' => true],
    ['title' => 'Publications', 'table' => 'tbl_publication', 'date_col' => 'publication_date', 'has_status' => false],
    ['title' => 'Intellectual Property', 'table' => 'tbl_itelectualprop', 'date_col' => 'date_granted', 'has_status' => false],
    ['title' => 'Paper Presentations', 'table' => 'tbl_paperpresentation', 'date_col' => 'date_paper', 'has_status' => false],
    ['title' => 'Extension Projects', 'table' => 'tbl_extension_project_conducted', 'date_col' => 'start_date', 'has_status' => true],
    ['title' => 'Trainings Attended', 'table' => 'tbl_trainingsattended', 'date_col' => 'date_train', 'has_status' => false]
];

$repp_array = explode(',', $repp);
$modules_to_run = ($repp === 'all' || $repp === 'all_modules') 
    ? $all_modules 
    : array_filter($all_modules, function($m) use ($repp_array) { return in_array($m['table'], $repp_array); });

if (empty($modules_to_run)) {
    die("No valid modules selected for export.");
}

// Header Setup
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
    if ($repp === 'all' || $repp === 'all_modules') {
        $header_title = "All Modules Overview";
    } elseif (count($modules_to_run) > 1) {
        $header_title = "Multi-Module Report";
    } else {
        $m_title = reset($modules_to_run)['table'] ?? '';
        $header_title = "Module Report (" . ucwords(str_replace(['tbl_', '_'], ['', ' '], $m_title)) . ")";
    }
    $header_subtitle = ($department !== 'all') ? "Filtered Departments" : "All Departments";
}

$html_chunks[] = "
<table width='100%' style='margin-bottom: 20px; border-bottom: 3px solid #2c7be5; font-family: Arial, sans-serif;'>
<table width='100%' style='margin-bottom: 20px; border-bottom: 3px solid #2c7be5; font-family: Arial, sans-serif;'>
    <tr>
        <td style='text-align: center; padding-bottom: 15px;'>
            <h1 style='font-size: 26px; font-weight: bold; color: #2c3e50; margin: 0; text-transform: uppercase;'>{$header_title}</h1>
            <div style='font-size: 14px; color: #6e84a3; margin-top: 5px;'>{$header_subtitle}</div>
        <td style='text-align: center; padding-bottom: 15px;'>
            <h1 style='font-size: 26px; font-weight: bold; color: #2c3e50; margin: 0; text-transform: uppercase;'>{$header_title}</h1>
            <div style='font-size: 14px; color: #6e84a3; margin-top: 5px;'>{$header_subtitle}</div>
        </td>
    </tr>
</table>";

$exclude_keys = ['id', 'researcherid', 'lead_researcher_id', 'lead_author_id', 'title', 'stat', 'status_exct', 'status', 'so_file', 'moa_file', 'department', 'lead_researcher', 'co_researchers', 'coauth', 'user_created_on', 'has_files', 'all_authors', 'primary_familyname', 'author_db_id'];

foreach ($modules_to_run as $mod) {
    
    $html_chunks[] = "
    <h3 style='color: #2c7be5; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 25px; margin-bottom: 10px;'>
        {$mod['title']}
    </h3>";
    
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

    // THE FIX: Fetch Academic Rank and Program in the main SQL query
    $query = "SELECT r.*, d.department, CONCAT(d.firstName, ' ', d.familyName) AS Lead_Researcher, d.academic_rank, d.program 
              FROM {$mod['table']} r 
              JOIN tbl_researchdata d ON r.researcherID = d.id 
              WHERE r.status = 1";

    if (!empty($researcher_id)) {
        if ($col_table !== '') {
            $query .= " AND (r.researcherID = '$researcher_id' OR r.id IN (SELECT $col_fk FROM $col_table WHERE researcher_id = '$researcher_id'))";
        } else {
            $query .= " AND r.researcherID = '$researcher_id'";
        }
    }
    
    // Apply Multi-Select In Clauses
    if ($department !== 'all' && !empty($department)) {
        $arr = explode(',', $department);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $query .= " AND d.department IN (" . implode(',', $escaped) . ")";
    }
    if ($academic_rank !== 'all' && !empty($academic_rank)) {
        $arr = explode(',', $academic_rank);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $query .= " AND d.academic_rank IN (" . implode(',', $escaped) . ")";
    }
    if ($program !== 'all' && !empty($program)) {
        $arr = explode(',', $program);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $query .= " AND d.program IN (" . implode(',', $escaped) . ")";
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
            $stat_val = strtolower($item['stat'] ?? $item['status_exct'] ?? '');
            if ($status === 'completed') {
                if (strpos($stat_val, 'complet') === false && strpos($stat_val, 'finish') === false) { $status_matched = false; }
            } elseif ($status === 'ongoing') {
                if (!preg_match('/ongoing|on-going|on going|progress|active|implement/i', $stat_val)) { $status_matched = false; }
            }
        }

        if ($date_matched && $status_matched) {
            $found_items = true;
            $title_clean = htmlspecialchars($item['title'] ?? 'Untitled');
            
            $detail_str = "";
            foreach ($item as $k => $v) {
                $kl = strtolower($k);
                if (in_array($kl, $exclude_keys) || trim((string)$v) === '' || is_numeric($k) || $kl === 'academic_rank' || $kl === 'program') continue;
                
                $clean_label = ucwords(str_replace('_', ' ', $k));
                $clean_val = htmlspecialchars((string)$v);
                
                $detail_str .= "<div style='margin-bottom: 4px;'><strong style='color:#333333;'>{$clean_label}:</strong> <span style='color:#555555;'>{$clean_val}</span></div>";
            }
            if ($detail_str === "") $detail_str = "<span style='color: #999; font-style: italic;'>No extra details available</span>";

            // THE FIX: Format Lead Researcher with Rank and Program
            $lead_name = htmlspecialchars($item['Lead_Researcher'] ?? 'Unknown');
            $lead_rank = htmlspecialchars($item['academic_rank'] ?? '');
            $lead_prog = htmlspecialchars($item['program'] ?? '');
            
            $lead_display = "<strong>{$lead_name}</strong>";
            if (!empty($lead_rank)) {
                $lead_display .= "<br><span style='font-size: 10px; color: #555555;'>{$lead_rank}</span>";
            }
            if (!empty($lead_prog)) {
                $lead_display .= "<br><span style='font-size: 10px; color: #777777;'><em>{$lead_prog}</em></span>";
            }

            // THE FIX: Fetch Co-Authors with their Rank and Program attached
            $co_authors_display = '<span style="color: #999; font-style: italic;">None</span>';
            if ($col_table !== '') {
                $object->query = "SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName, '|', IFNULL(d2.academic_rank, ''), '|', IFNULL(d2.program, '')) SEPARATOR '||') as coauths FROM $col_table col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.$col_fk = '".$item['id']."' AND col.researcher_id != '".$item['researcherID']."'";
                $co_res = getSafeRows($object);
                
                if (!empty($co_res[0]['coauths'])) { 
                    $co_list = explode('||', $co_res[0]['coauths']);
                    $co_html_arr = [];
                    
                    foreach ($co_list as $co_data) {
                        $parts = explode('|', $co_data);
                        if (count($parts) >= 1) {
                            $c_name = htmlspecialchars($parts[0]);
                            $c_rank = isset($parts[1]) && $parts[1] !== '' ? htmlspecialchars($parts[1]) : '';
                            $c_prog = isset($parts[2]) && $parts[2] !== '' ? htmlspecialchars($parts[2]) : '';

                            $c_display = "<strong>{$c_name}</strong>";
                            if (!empty($c_rank)) {
                                $c_display .= "<br><span style='font-size: 10px; color: #555555;'>{$c_rank}</span>";
                            }
                            if (!empty($c_prog)) {
                                $c_display .= "<br><span style='font-size: 10px; color: #777777;'><em>{$c_prog}</em></span>";
                            }
                            $co_html_arr[] = "<div style='margin-bottom: 8px;'>{$c_display}</div>";
                        }
                    }
                    $co_authors_display = implode("", $co_html_arr);
                }
            } elseif ($mod['table'] == 'tbl_itelectualprop' && !empty($item['coauth'])) {
                $co_authors_display = htmlspecialchars($item['coauth']);
            }

            $html_chunks[] = "<tr style='page-break-inside: avoid;'>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'><strong>{$title_clean}</strong></td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$lead_display}</td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$co_authors_display}</td>";
            
            if ($mod['has_status']) {
                $s_val = htmlspecialchars($item['stat'] ?? $item['status_exct'] ?? 'Unknown');
                $html_chunks[] = "<td valign='top' align='center' style='padding: 10px; color: #12263f;'><strong>{$s_val}</strong></td>";
                $s_val = htmlspecialchars($item['stat'] ?? $item['status_exct'] ?? 'Unknown');
                $html_chunks[] = "<td valign='top' align='center' style='padding: 10px; color: #12263f;'><strong>{$s_val}</strong></td>";
            }

            $html_chunks[] = "<td valign='top' style='padding: 10px;'>{$detail_str}</td>";
            $html_chunks[] = "</tr>";
        }
    }
    
    if (!$found_items) {
        $colspan = $mod['has_status'] ? 5 : 4;
        $html_chunks[] = "<tr><td colspan='{$colspan}' align='center' style='color: #858796; font-style: italic; padding: 20px;'>No records found for this category.</td></tr>";
        $colspan = $mod['has_status'] ? 5 : 4;
        $html_chunks[] = "<tr><td colspan='{$colspan}' align='center' style='color: #858796; font-style: italic; padding: 20px;'>No records found for this category.</td></tr>";
    }
    $html_chunks[] = "</tbody></table>";
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
            die("Error: mPDF library is not installed.");
        }
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4-L', 
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