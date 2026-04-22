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

// THE FIX: Universal Date Parser added for Exporter
if (!function_exists('safe_strtotime')) {
    function safe_strtotime($date_str) {
        if (empty($date_str) || $date_str === '0000-00-00' || $date_str === 'null') return false;
        $date_str = trim(str_replace('/', '-', $date_str));
        $parts = explode('-', $date_str);
        if (count($parts) === 1 && strlen($parts[0]) === 4) { $date_str = $parts[0] . '-01-01'; }
        if (count($parts) === 2) {
            if (strlen($parts[1]) === 4) $date_str = $parts[1] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-01';
            else if (strlen($parts[0]) === 4) $date_str = $parts[0] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-01';
        }
        if (count($parts) === 3) {
            if (strlen($parts[2]) === 4) $date_str = $parts[2] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        }
        return strtotime($date_str);
    }
}

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

$logo_path = '../../img/serdac.png';
$logo_html = '';

if (file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_src = 'data:image/png;base64,' . $logo_data;
    $logo_html = "<img src='{$logo_src}' width='750' style='max-width: 90%; height: auto;' alt='RDEC-SDMU Header Logo' />";
}

$html_chunks[] = "
<table width='100%' cellpadding='0' cellspacing='0' style='font-family: Arial, Helvetica, sans-serif; margin-bottom: 25px;'>
    <tr>
        <td align='center' style='padding-bottom: 15px;'>
            {$logo_html}
        </td>
    </tr>
    <tr>
        <td align='center' style='border-top: 2px solid #2c7be5; border-bottom: 2px solid #2c7be5; padding: 12px 0; background-color: #fcfcfc;'>
            <h1 style='font-size: 22px; font-weight: bold; color: #12263f; margin: 0; padding: 0; text-transform: uppercase; letter-spacing: 1px;'>{$header_title}</h1>
            <div style='font-size: 12px; color: #6e84a3; margin-top: 4px; text-transform: uppercase; letter-spacing: 2px;'>{$header_subtitle}</div>
        </td>
    </tr>
</table>";

$exclude_keys = ['id', 'researcherid', 'lead_researcher_id', 'lead_author_id', 'title', 'stat', 'status_exct', 'status', 'so_file', 'moa_file', 'department', 'lead_researcher', 'co_researchers', 'coauth', 'user_created_on', 'has_files', 'all_authors', 'primary_familyname', 'author_db_id'];

$label_map = [
    'journal' => 'Journal',
    'vol_num_issue_num' => 'Volume / Issue',
    'issn_isbn' => 'ISSN / ISBN',
    'indexing' => 'Indexing',
    'publication_date' => 'Published',
    'date_applied' => 'Date Applied',
    'date_granted' => 'Date Granted',
    'date_paper' => 'Date of Paper',
    'start' => 'Start Date',
    'end' => 'End Date',
    'start_date' => 'Start Date',
    'completed_date' => 'Completed',
    'started_date' => 'Started',
    'funding_source' => 'Funding Source',
    'approved_budget' => 'Approved Budget',
    'research_agenda_cluster' => 'Agenda Cluster',
    'target_beneficiaries_communities' => 'Beneficiaries',
    'type_learning_dev' => 'Learning & Dev Type',
    'sponsor_org' => 'Sponsor Org',
    'totnh' => 'Total Hours',
    'conference_title' => 'Conference',
    'conference_venue' => 'Venue',
    'conference_organizer' => 'Organizer',
    'sdgs' => 'SDGs',
    'lvl' => 'Level',
    'type' => 'Type',
    'venue' => 'Venue',
    'discipline' => 'Discipline',
    'partners' => 'Partners',
    'terminal_report' => 'Terminal Report'
];

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
                <th width='18%' align='left' style='padding: 10px;'>Co-Authors</th>";
                
    if ($mod['has_status']) {
        $html_chunks[] = "<th width='10%' align='center' style='padding: 10px;'>Status</th>";
    }

    $html_chunks[] = "
                <th width='32%' align='left' style='padding: 10px;'>Specific Details</th>
            </tr>
        </thead>
        <tbody>";
    
    $col_table = ''; $col_fk = '';
    if ($mod['table'] == 'tbl_researchconducted') { $col_table = 'tbl_research_collaborators'; $col_fk = 'research_id'; }
    if ($mod['table'] == 'tbl_publication') { $col_table = 'tbl_publication_collaborators'; $col_fk = 'publication_id'; }
    if ($mod['table'] == 'tbl_paperpresentation') { $col_table = 'tbl_paper_collaborators'; $col_fk = 'paper_id'; }
    // THE FIX: Allow Intellectual Property to dynamically fetch co-authors!
    if ($mod['table'] == 'tbl_itelectualprop') { $col_table = 'tbl_ip_collaborators'; $col_fk = 'ip_id'; }

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
    
    if ($department !== 'all' && !empty($department)) {
        $arr = explode(',', $department);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $in_str = implode(',', $escaped);
        if ($col_table !== '') {
            $query .= " AND (d.department IN ($in_str) OR r.id IN (SELECT sub_col.$col_fk FROM $col_table sub_col JOIN tbl_researchdata sub_d ON sub_col.researcher_id = sub_d.id WHERE sub_d.department IN ($in_str)))";
        } else {
            $query .= " AND d.department IN ($in_str)";
        }
    }
    if ($academic_rank !== 'all' && !empty($academic_rank)) {
        $arr = explode(',', $academic_rank);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $in_str = implode(',', $escaped);
        if ($col_table !== '') {
            $query .= " AND (d.academic_rank IN ($in_str) OR r.id IN (SELECT sub_col.$col_fk FROM $col_table sub_col JOIN tbl_researchdata sub_d ON sub_col.researcher_id = sub_d.id WHERE sub_d.academic_rank IN ($in_str)))";
        } else {
            $query .= " AND d.academic_rank IN ($in_str)";
        }
    }
    if ($program !== 'all' && !empty($program)) {
        $arr = explode(',', $program);
        $escaped = array_map(function($v) { return "'" . addslashes(trim($v)) . "'"; }, $arr);
        $in_str = implode(',', $escaped);
        if ($col_table !== '') {
            $query .= " AND (d.program IN ($in_str) OR r.id IN (SELECT sub_col.$col_fk FROM $col_table sub_col JOIN tbl_researchdata sub_d ON sub_col.researcher_id = sub_d.id WHERE sub_d.program IN ($in_str)))";
        } else {
            $query .= " AND d.program IN ($in_str)";
        }
    }

    $object->query = $query;
    $items = getSafeRows($object);
    $found_items = false;
    
    $unique_items = [];
    foreach ($items as $itm) {
        $unique_items[$itm['id']] = $itm;
    }
    
    foreach ($unique_items as $item) {
        // =========================================================================
        // THE FIX: Strict Date Bounding Logic for Exporter
        // =========================================================================
        $date_matched = false;
        if ($is_all_time) { 
            $date_matched = true; 
        } else {
            $col_start = '';
            $col_end = '';
            
            if ($mod['table'] == 'tbl_researchconducted') {
                $col_start = 'started_date'; $col_end = 'completed_date';
            } else if ($mod['table'] == 'tbl_extension_project_conducted') {
                $col_start = 'start_date'; $col_end = 'completed_date';
            } else {
                $col_start = $mod['date_col']; $col_end = $mod['date_col'];
            }

            $row_start_ts = safe_strtotime($item[$col_start] ?? '');
            $row_end_ts = safe_strtotime($item[$col_end] ?? '');

            if ($col_start === $col_end) {
                if ($row_start_ts !== false && $row_start_ts >= $from_date_ts && $row_start_ts <= $to_date_ts) {
                    $date_matched = true;
                }
            } else {
                if ($row_start_ts !== false && $row_end_ts !== false) {
                    // STRICT BOUNDS: Must start inside AND end inside the selected years
                    if ($row_start_ts >= $from_date_ts && $row_end_ts <= $to_date_ts) {
                        $date_matched = true;
                    }
                } else if ($row_start_ts !== false) {
                    if ($row_start_ts >= $from_date_ts && $row_start_ts <= $to_date_ts) {
                        $date_matched = true;
                    }
                } else if ($row_end_ts !== false) {
                    if ($row_end_ts >= $from_date_ts && $row_end_ts <= $to_date_ts) {
                        $date_matched = true;
                    }
                }
            }
        }
        // =========================================================================

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
                if (in_array($kl, ['file', 'terminal_report_file', 'attachments', 'so_file', 'moa_file', 'coauth'])) continue;
                
                // =========================================================================
                // THE FIX: The "Magic Replacer" V2 (Crash-Proof!)
                // =========================================================================
                if (trim((string)$v) === 'Legacy Replaced') {
                    $real_data = '';
                    $cat = '';
                    
                    if ($kl === 'terminal_report') $cat = 'Terminal Report';
                    if ($kl === 'moa') $cat = 'MOA';
                    if ($kl === 'so') $cat = 'SO';
                    
                    if ($cat !== '') {
                        $file_table = '';
                        $file_col = '';
                        
                        if ($mod['table'] == 'tbl_extension_project_conducted') {
                            $file_table = 'tbl_extension_files';
                            $file_col = 'extension_id';
                        } else if ($mod['table'] == 'tbl_researchconducted') {
                            $file_table = 'tbl_research_files'; 
                            $file_col = 'research_id';
                        }
                        
                        if ($file_table !== '') {
                            try {
                                $sub_obj = new rms();
                                $sub_obj->query = "SELECT file_name FROM $file_table WHERE $file_col = '".$item['id']."' AND file_category = '$cat'";
                                $files = clone $sub_obj;
                                $files_result = getSafeRows($files);
                                $fnames = [];
                                if ($files_result) { foreach($files_result as $f) { $fnames[] = $f['file_name']; } }
                                if(count($fnames) > 0) { $real_data = implode(", ", $fnames); }
                                else { $real_data = "No File Attached"; }
                            } catch (Exception $e) {
                                // Failsafe: Never crash the document builder, just show a safe string
                                $real_data = "See Attached Files"; 
                            }
                        }
                    }
                    
                    if ($real_data !== '') { 
                        $v = $real_data; 
                    } else { 
                        $v = 'See Attached Files'; 
                    }
                }
                // =========================================================================

                $clean_label = isset($label_map[$kl]) ? $label_map[$kl] : ucwords(str_replace('_', ' ', $k));
                $clean_val = htmlspecialchars((string)$v);
                
                if (($kl === 'approved_budget' || $kl === 'budget') && is_numeric(str_replace(['₱', ',', ' '], '', $clean_val))) {
                    $clean_val = '₱' . number_format((float)str_replace(['₱', ',', ' '], '', $clean_val), 2);
                }
                
                $detail_str .= "<div style='margin-bottom: 4px; line-height: 1.4;'><span style='color:#7a869a; font-size:10px;'>{$clean_label}:</span> <span style='color:#12263f; font-weight:bold; font-size:11px;'>{$clean_val}</span></div>";
            }
            if ($detail_str === "") $detail_str = "<span style='color: #999; font-style: italic;'>No extra details available</span>";

            $lead_name = htmlspecialchars($item['Lead_Researcher'] ?? 'Unknown');
            $lead_rank = htmlspecialchars($item['academic_rank'] ?? '');
            $lead_prog = htmlspecialchars($item['program'] ?? '');
            
            $lead_display = "<strong>{$lead_name}</strong>";
            $lead_meta = [];
            if (!empty($lead_rank)) $lead_meta[] = $lead_rank;
            if (!empty($lead_prog)) $lead_meta[] = $lead_prog;
            
            if (!empty($lead_meta)) {
                $lead_display .= "<br><span style='font-size: 10px; color: #555555;'>" . implode(" • ", $lead_meta) . "</span>";
            }

            $co_authors_display = '<span style="color: #999; font-style: italic;">None</span>';
            if ($col_table !== '') {
                $lead_id = !empty($item['lead_researcher_id']) ? $item['lead_researcher_id'] : $item['researcherID'];
                $object->query = "SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName, '|', IFNULL(d2.academic_rank, ''), '|', IFNULL(d2.program, '')) SEPARATOR '||') as coauths FROM $col_table col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.$col_fk = '".$item['id']."' AND col.researcher_id != '".$lead_id."'";
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
                            $c_meta = [];
                            if (!empty($c_rank)) $c_meta[] = $c_rank;
                            if (!empty($c_prog)) $c_meta[] = $c_prog;

                            if (!empty($c_meta)) {
                                $c_display .= " <span style='font-size: 9.5px; color: #666;'>(" . implode(" • ", $c_meta) . ")</span>";
                            }
                            $co_html_arr[] = "<div style='margin-bottom: 3px;'>{$c_display}</div>";
                        }
                    }
                    $co_authors_display = implode("", $co_html_arr);
                }
            }

            $html_chunks[] = "<tr style='page-break-inside: avoid;'>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'><strong>{$title_clean}</strong></td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$lead_display}</td>";
            $html_chunks[] = "<td valign='top' style='padding: 10px; color: #12263f;'>{$co_authors_display}</td>";
            
            if ($mod['has_status']) {
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
    }
    $html_chunks[] = "</tbody></table>";
}

$doc_name = (!empty($researcher_id)) ? 'Researcher_Portfolio_' : 'Module_Report_';

if ($format === 'word') {
    $raw_html = implode("\n", $html_chunks);
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment;filename=\"" . $doc_name . time() . ".doc\"");
    echo "<html xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns:m='http://schemas-microsoft.com/office/2004/12/omml' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Report</title><style>@page WordSection1 { size: 11.0in 8.5in; mso-page-orientation: landscape; margin: 0.8in; } div.WordSection1 { page: WordSection1; }</style></head><body><div class='WordSection1'>" . $raw_html . "</div></body></html>";
    exit;
} else {
    try {
        if (!class_exists('\Mpdf\Mpdf')) { die("Error: mPDF library is not installed."); }
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L', 'tempDir' => __DIR__ . '/tmp']);
        foreach ($html_chunks as $chunk) { $mpdf->WriteHTML($chunk); }
        $mpdf->Output($doc_name . time() . '.pdf', 'D');
    } catch (\Mpdf\MpdfException $e) { 
        echo 'Error generating PDF: ' . $e->getMessage(); 
    }
}
?>