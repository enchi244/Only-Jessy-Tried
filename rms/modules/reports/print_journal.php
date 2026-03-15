<?php
// generate_pdf.php

ini_set('pcre.backtrack_limit', '5000000');
ini_set('max_execution_time', '300'); 

require_once __DIR__ . '/vendor/autoload.php';
include('../../core/rms.php');

$object = new rms();

if (!$object->is_login()) { echo 'Error: User not logged in.'; exit; }
if (!$object->is_cashier_user() && !$object->is_master_user()) { echo 'Error: Insufficient permissions.'; exit; }

$raw_from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$raw_to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$repp = isset($_GET['repp']) ? $_GET['repp'] : '';

$is_all_time = empty($raw_from_date) && empty($raw_to_date);

if ($is_all_time) {
    $from_date_display = 'Beginning of Records';
    $to_date_display = 'Present';
    $from_date_ts = 0;
    $to_date_ts = PHP_INT_MAX;
} else {
    $from_date_display = date('m-d-Y', strtotime($raw_from_date));
    $to_date_display = date('m-d-Y', strtotime($raw_to_date));
    $from_date_ts = strtotime($raw_from_date);
    $to_date_ts = strtotime($raw_to_date) + 86399; 
}

function filter_and_group_data_by_dates($raw_results, $start_ts, $end_ts, $is_all_time) {
    $grouped = [];
    $titles_in_range = []; 

    foreach ($raw_results as $row) {
        $date_matched = false;
        
        if ($is_all_time) {
            $date_matched = true;
        } else {
            foreach ($row as $key => $val) {
                $key_lower = strtolower($key);
                if (strpos($key_lower, 'date') !== false || strpos($key_lower, 'start') !== false || strpos($key_lower, 'end') !== false || $key_lower === 'datestarted' || $key_lower === 'datecompleted' || $key_lower === 'datetrain' || $key_lower === 'datepaper') {
                    $val_clean = trim((string)$val);
                    if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) { $val_clean = "01-" . $val_clean; }
                    $ts = strtotime($val_clean);
                    if ($ts !== false && $ts >= $start_ts && $ts <= $end_ts) {
                        $date_matched = true;
                        break;
                    }
                }
            }
        }
        
        if(isset($row['name'])) {
            $row['name'] = preg_replace('/\s+/', ' ', $row['name']); 
            $row['name'] = str_replace(' ,', ',', $row['name']); 
            $row['name'] = trim($row['name']);
        }

        $title_val = isset($row['title']) ? $row['title'] : '';
        $row_hash = !empty($title_val) ? md5(strtolower(trim($title_val))) : md5(implode('|', $row));
        
        if ($date_matched) { $titles_in_range[$row_hash] = true; }
        
        if (!isset($grouped[$row_hash])) {
            // Check attached status logically
            $row['so_attached'] = (!empty($row['so_file'])) ? 'Yes' : 'None';
            $row['moa_attached'] = (!empty($row['moa_file'])) ? 'Yes' : 'None';
            $grouped[$row_hash] = $row;
        } else {
            $existing_names = $grouped[$row_hash]['name'];
            $new_name = $row['name'];
            if (!empty($new_name) && strpos($existing_names, $new_name) === false) {
                $grouped[$row_hash]['name'] .= '; ' . $new_name;
            }
            // Merge attached file indicators if any co-researcher has it
            if (!empty($row['so_file'])) { $grouped[$row_hash]['so_attached'] = 'Yes'; }
            if (!empty($row['moa_file'])) { $grouped[$row_hash]['moa_attached'] = 'Yes'; }
        }
    }
    
    $final_grouped = [];
    foreach ($grouped as $hash => $data) {
        if (isset($titles_in_range[$hash])) { $final_grouped[] = $data; }
    }
    return array_values($final_grouped);
}

$html_chunks = [];

$header_dept_title = ($department == "") ? "All Departments" : $department;
$doc_title = ($repp === 'all_modules') ? "Comprehensive Research Report" : "Data Extraction Report";
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

$header_html = '<div style="text-align: center;">';
if ($format !== 'word') {
    $header_html .= '<img src="img/2.png" style="height: 80px; margin-bottom: 10px;" alt="Logo">';
}
$header_html .= '<h1 style="font-size: 20px; margin: 0;">' . $doc_title . '</h1>
    <h2 style="font-size: 18px; margin: 0;">' . $header_dept_title . '</h2>
    <p style="font-size: 14px; margin: 5px 0;">Date From: ' . $from_date_display . ' | Date To: ' . $to_date_display . '</p>
</div><hr style="margin: 20px 0;">';

$html_chunks[] = $header_html;

$modules_to_run = ($repp === 'all_modules') ? ['tbl_publication', 'tbl_researchconducted', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended'] : [$repp];

foreach ($modules_to_run as $current_module) {
    $dept_safe = addslashes($department);
    $dept_clause = ($department !== "") ? " WHERE tbl_researchdata.department='$dept_safe' " : "";

    // Module 1: Publications
    if ($current_module == "tbl_publication") {
        $object->query = "SELECT tbl_publication.`title` AS title,GROUP_CONCAT(CONCAT(tbl_researchdata.familyName,', ',tbl_researchdata.firstName,' ',tbl_researchdata.middleName,' ',tbl_researchdata.Suffix) SEPARATOR ', ') AS name, MAX(tbl_researchdata.so_file) AS so_file, MAX(tbl_publication.moa_file) AS moa_file, tbl_publication.`start` AS start,tbl_publication.end AS end, tbl_publication.journal AS journal,tbl_publication.vol_num_issue_num AS vol_num_issue_num, tbl_publication.issn_isbn AS issn_isbn,tbl_publication.indexing AS indexing, tbl_publication.publication_date AS publication_date FROM `tbl_publication` JOIN tbl_researchdata ON tbl_publication.researcherID=tbl_researchdata.id " . $dept_clause . " GROUP BY tbl_publication.`title`,tbl_publication.`start`,tbl_publication.`end`,tbl_publication.journal,tbl_publication.issn_isbn,tbl_publication.publication_date ORDER BY `publication_date` ASC;";
        $filtered_data = filter_and_group_data_by_dates($object->get_result(), $from_date_ts, $to_date_ts, $is_all_time);

        if (!empty($filtered_data)) {
            $html_chunks[] = '<h3 style="font-family: Arial, sans-serif; font-size: 16px; color: #2c3e50; border-bottom: 2px solid #2c7be5; padding-bottom: 5px; page-break-after: avoid;">Publication Index</h3>';
            $table_head = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 30px;">
            <thead><tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th style="border: 1px solid #000; width: 3%;">#</th><th style="border: 1px solid #000; width: 22%;">Title</th><th style="border: 1px solid #000; width: 14%;">Author\'s Name</th><th style="border: 1px solid #000; width: 4%;">SO</th><th style="border: 1px solid #000; width: 4%;">MOA</th><th style="border: 1px solid #000; white-space: nowrap;">Start</th><th style="border: 1px solid #000; white-space: nowrap;">End</th><th style="border: 1px solid #000; width: 10%;">Journal</th><th style="border: 1px solid #000; width: 10%;">ISSN/ISBN</th><th style="border: 1px solid #000; width: 8%;">Indexing</th><th style="border: 1px solid #000; white-space: nowrap;">Date</th>
            </tr></thead><tbody>';
            $current_table = $table_head; $count = 0; $row_counter = 0;
            
            foreach ($filtered_data as $row) {
                $count++; $row_counter++;
                $current_table .= '<tr style="text-align: center;">
                    <td style="border: 1px solid #000;">' . $count . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['title'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['name'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['so_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['moa_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['start'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['end'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['journal'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['issn_isbn'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['indexing'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['publication_date'] ?: 'N/A') . '</td>
                </tr>';

                if ($row_counter >= 100) { $current_table .= '</tbody></table>'; $html_chunks[] = $current_table; $current_table = $table_head; $row_counter = 0; }
            }
            if ($row_counter > 0) { $html_chunks[] = $current_table . '</tbody></table>'; }
        }

    // Module 2: Research Conducted
    } elseif ($current_module == "tbl_researchconducted") {
        $object->query = "SELECT tbl_researchconducted.title AS title,GROUP_CONCAT(CONCAT(tbl_researchdata.familyName,', ',tbl_researchdata.firstName,' ',tbl_researchdata.middleName,' ',tbl_researchdata.Suffix) SEPARATOR ', ') AS name, MAX(tbl_researchdata.so_file) AS so_file, MAX(tbl_researchconducted.moa_file) AS moa_file, tbl_researchconducted.research_agenda_cluster AS agenda, tbl_researchconducted.sdgs AS sdgs,tbl_researchconducted.started_date AS datestarted,tbl_researchconducted.completed_date AS datecompleted, tbl_researchconducted.funding_source AS fund,tbl_researchconducted.approved_budget AS budget, tbl_researchconducted.stat AS stat, tbl_researchconducted.terminal_report AS terminal_report FROM `tbl_researchconducted` JOIN tbl_researchdata ON tbl_researchconducted.researcherID=tbl_researchdata.id " . $dept_clause . " GROUP BY tbl_researchconducted.`title`,tbl_researchconducted.research_agenda_cluster,tbl_researchconducted.sdgs,tbl_researchconducted.started_date,tbl_researchconducted.completed_date,tbl_researchconducted.funding_source,tbl_researchconducted.approved_budget,tbl_researchconducted.stat,tbl_researchconducted.terminal_report ORDER BY `completed_date` ASC;";
        $filtered_data = filter_and_group_data_by_dates($object->get_result(), $from_date_ts, $to_date_ts, $is_all_time);

        if (!empty($filtered_data)) {
            $html_chunks[] = '<h3 style="font-family: Arial, sans-serif; font-size: 16px; color: #2c3e50; border-bottom: 2px solid #2c7be5; padding-bottom: 5px; page-break-after: avoid;">Research Conducted</h3>';
            $table_head = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 30px;">
            <thead><tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th style="border: 1px solid #000; width: 3%;">#</th><th style="border: 1px solid #000; width: 20%;">Title</th><th style="border: 1px solid #000; width: 14%;">Proponent\'s Name</th><th style="border: 1px solid #000; width: 4%;">SO</th><th style="border: 1px solid #000; width: 4%;">MOA</th><th style="border: 1px solid #000; width: 8%;">Agenda</th><th style="border: 1px solid #000; width: 8%;">SDGS</th><th style="border: 1px solid #000; white-space: nowrap;">Date Started</th><th style="border: 1px solid #000; white-space: nowrap;">Date Ended</th><th style="border: 1px solid #000; width: 8%;">Fund</th><th style="border: 1px solid #000; white-space: nowrap;">Budget</th><th style="border: 1px solid #000; white-space: nowrap;">Status</th><th style="border: 1px solid #000; white-space: nowrap;">Terminal</th>
            </tr></thead><tbody>';
            $current_table = $table_head; $count = 0; $row_counter = 0;

            foreach ($filtered_data as $row) {
                $count++; $row_counter++;
                $current_table .= '<tr style="text-align: center;">
                    <td style="border: 1px solid #000;">' . $count . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['title'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . ($row['name'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['so_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['moa_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['agenda'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['sdgs'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['datestarted'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['datecompleted'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['fund'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['budget'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['stat'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['terminal_report'] ?: 'N/A') . '</td>
                </tr>';

                if ($row_counter >= 100) { $current_table .= '</tbody></table>'; $html_chunks[] = $current_table; $current_table = $table_head; $row_counter = 0; }
            }
            if ($row_counter > 0) { $html_chunks[] = $current_table . '</tbody></table>'; }
        }

    // Module 3: Intellectual Property
    } elseif ($current_module == "tbl_itelectualprop") {
        $object->query = "SELECT tbl_itelectualprop.title AS title,GROUP_CONCAT( CONCAT(tbl_researchdata.familyName,', ',tbl_researchdata.firstName,' ',tbl_researchdata.middleName,' ',tbl_researchdata.Suffix) SEPARATOR ', ' ) AS name, MAX(tbl_researchdata.so_file) AS so_file, MAX(tbl_itelectualprop.moa_file) AS moa_file, tbl_itelectualprop.coauth AS coauth, tbl_itelectualprop.type AS type,tbl_itelectualprop.date_applied AS date_applied, tbl_itelectualprop.date_granted AS date_granted FROM `tbl_itelectualprop` JOIN tbl_researchdata ON tbl_itelectualprop.researcherID=tbl_researchdata.id " . $dept_clause . " GROUP BY tbl_itelectualprop.title,tbl_itelectualprop.coauth, tbl_itelectualprop.type, tbl_itelectualprop.date_applied,tbl_itelectualprop.date_granted ORDER BY `date_granted` ASC;";
        $filtered_data = filter_and_group_data_by_dates($object->get_result(), $from_date_ts, $to_date_ts, $is_all_time);

        if (!empty($filtered_data)) {
            $html_chunks[] = '<h3 style="font-family: Arial, sans-serif; font-size: 16px; color: #2c3e50; border-bottom: 2px solid #2c7be5; padding-bottom: 5px; page-break-after: avoid;">Intellectual Property</h3>';
            $table_head = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 30px;">
            <thead><tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th style="border: 1px solid #000; width: 3%;">#</th><th style="border: 1px solid #000; width: 25%;">Title</th><th style="border: 1px solid #000; width: 15%;">Proponent\'s Name</th><th style="border: 1px solid #000; width: 5%;">SO</th><th style="border: 1px solid #000; width: 5%;">MOA</th><th style="border: 1px solid #000; width: 15%;">Co-Author</th><th style="border: 1px solid #000; width: 12%;">Type</th><th style="border: 1px solid #000; white-space: nowrap;">Date Applied</th><th style="border: 1px solid #000; white-space: nowrap;">Date Granted</th>
            </tr></thead><tbody>';
            $current_table = $table_head; $count = 0; $row_counter = 0;

            foreach ($filtered_data as $row) {
                $count++; $row_counter++;
                $current_table .= '<tr style="text-align: center;">
                    <td style="border: 1px solid #000;">' . $count . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['title'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . ($row['name'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['so_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['moa_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['coauth'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['type'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['date_applied'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['date_granted'] ?: 'N/A') . '</td>
                </tr>';

                if ($row_counter >= 100) { $current_table .= '</tbody></table>'; $html_chunks[] = $current_table; $current_table = $table_head; $row_counter = 0; }
            }
            if ($row_counter > 0) { $html_chunks[] = $current_table . '</tbody></table>'; }
        }

    // Module 4: Paper Presentation
    } elseif ($current_module == "tbl_paperpresentation") {
        $object->query = "SELECT tbl_paperpresentation.title AS title,GROUP_CONCAT( CONCAT(tbl_researchdata.familyName,', ',tbl_researchdata.firstName,' ',tbl_researchdata.middleName,' ',tbl_researchdata.Suffix) SEPARATOR ', ' ) AS name, MAX(tbl_researchdata.so_file) AS so_file, MAX(tbl_paperpresentation.moa_file) AS moa_file, tbl_paperpresentation.conference_title AS conference, tbl_paperpresentation.conference_venue AS venue, tbl_paperpresentation.conference_organizer AS organizer, tbl_paperpresentation.date_paper AS datepaper,tbl_paperpresentation.type AS type, tbl_paperpresentation.discipline AS discipline FROM `tbl_paperpresentation` JOIN tbl_researchdata ON tbl_paperpresentation.researcherID=tbl_researchdata.id " . $dept_clause . " GROUP BY tbl_paperpresentation.title,tbl_paperpresentation.conference_title,tbl_paperpresentation.conference_venue,tbl_paperpresentation.conference_organizer,tbl_paperpresentation.date_paper,tbl_paperpresentation.type, tbl_paperpresentation.discipline ORDER BY `date_paper` ASC;";
        $filtered_data = filter_and_group_data_by_dates($object->get_result(), $from_date_ts, $to_date_ts, $is_all_time);

        if (!empty($filtered_data)) {
            $html_chunks[] = '<h3 style="font-family: Arial, sans-serif; font-size: 16px; color: #2c3e50; border-bottom: 2px solid #2c7be5; padding-bottom: 5px; page-break-after: avoid;">Paper Presentation</h3>';
            $table_head = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 30px;">
            <thead><tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th style="border: 1px solid #000; width: 3%;">#</th><th style="border: 1px solid #000; width: 22%;">Title</th><th style="border: 1px solid #000; width: 14%;">Proponent\'s Name</th><th style="border: 1px solid #000; width: 4%;">SO</th><th style="border: 1px solid #000; width: 4%;">MOA</th><th style="border: 1px solid #000; width: 10%;">Conference</th><th style="border: 1px solid #000; width: 10%;">Venue</th><th style="border: 1px solid #000; width: 10%;">Organizer</th><th style="border: 1px solid #000; white-space: nowrap;">Date Paper</th><th style="border: 1px solid #000; width: 8%;">Type</th><th style="border: 1px solid #000; width: 8%;">Discipline</th>
            </tr></thead><tbody>';
            $current_table = $table_head; $count = 0; $row_counter = 0;

            foreach ($filtered_data as $row) {
                $count++; $row_counter++;
                $current_table .= '<tr style="text-align: center;">
                    <td style="border: 1px solid #000;">' . $count . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['title'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . ($row['name'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['so_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['moa_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['conference'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['venue'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['organizer'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['datepaper'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['type'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['discipline'] ?: 'N/A') . '</td>
                </tr>';

                if ($row_counter >= 100) { $current_table .= '</tbody></table>'; $html_chunks[] = $current_table; $current_table = $table_head; $row_counter = 0; }
            }
            if ($row_counter > 0) { $html_chunks[] = $current_table . '</tbody></table>'; }
        }

    // Module 5: Trainings Attended
    } elseif ($current_module == "tbl_trainingsattended") {
        $object->query = "SELECT tbl_trainingsattended.title AS title,GROUP_CONCAT( CONCAT(tbl_researchdata.familyName,', ',tbl_researchdata.firstName,' ',tbl_researchdata.middleName,' ',tbl_researchdata.Suffix) SEPARATOR ', ' ) AS name, MAX(tbl_researchdata.so_file) AS so_file, MAX(tbl_trainingsattended.moa_file) AS moa_file, tbl_trainingsattended.type AS type, tbl_trainingsattended.venue AS venue, tbl_trainingsattended.date_train AS datetrain,tbl_trainingsattended.lvl AS lvl, tbl_trainingsattended.type_learning_dev AS learning, tbl_trainingsattended.sponsor_org AS sponsor, tbl_trainingsattended.totnh AS hr FROM `tbl_trainingsattended` JOIN tbl_researchdata ON tbl_trainingsattended.researcherID=tbl_researchdata.id " . $dept_clause . " GROUP BY tbl_trainingsattended.title,tbl_trainingsattended.type,tbl_trainingsattended.venue,tbl_trainingsattended.date_train,tbl_trainingsattended.lvl, tbl_trainingsattended.type_learning_dev,tbl_trainingsattended.sponsor_org,tbl_trainingsattended.totnh ORDER BY `date_train` ASC;";
        $filtered_data = filter_and_group_data_by_dates($object->get_result(), $from_date_ts, $to_date_ts, $is_all_time);

        if (!empty($filtered_data)) {
            $html_chunks[] = '<h3 style="font-family: Arial, sans-serif; font-size: 16px; color: #2c3e50; border-bottom: 2px solid #2c7be5; padding-bottom: 5px; page-break-after: avoid;">Trainings Attended</h3>';
            $table_head = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; margin-bottom: 30px;">
            <thead><tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th style="border: 1px solid #000; width: 3%;">#</th><th style="border: 1px solid #000; width: 20%;">Title</th><th style="border: 1px solid #000; width: 14%;">Proponent\'s Name</th><th style="border: 1px solid #000; width: 4%;">SO</th><th style="border: 1px solid #000; width: 4%;">MOA</th><th style="border: 1px solid #000; width: 10%;">Type</th><th style="border: 1px solid #000; width: 12%;">Venue</th><th style="border: 1px solid #000; white-space: nowrap;">Date Trained</th><th style="border: 1px solid #000; width: 6%;">Level</th><th style="border: 1px solid #000; width: 10%;">Learning</th><th style="border: 1px solid #000; width: 10%;">Sponsor</th><th style="border: 1px solid #000; white-space: nowrap;">Hrs</th>
            </tr></thead><tbody>';
            $current_table = $table_head; $count = 0; $row_counter = 0;

            foreach ($filtered_data as $row) {
                $count++; $row_counter++;
                $current_table .= '<tr style="text-align: center;">
                    <td style="border: 1px solid #000;">' . $count . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . ($row['title'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . ($row['name'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['so_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['moa_attached'] ?: 'None') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['type'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['venue'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['datetrain'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['lvl'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['learning'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000;">' . ($row['sponsor'] ?: 'N/A') . '</td>
                    <td style="border: 1px solid #000; white-space: nowrap;">' . ($row['hr'] ?: 'N/A') . '</td>
                </tr>';

                if ($row_counter >= 100) { $current_table .= '</tbody></table>'; $html_chunks[] = $current_table; $current_table = $table_head; $row_counter = 0; }
            }
            if ($row_counter > 0) { $html_chunks[] = $current_table . '</tbody></table>'; }
        }
    }
}

// Signatory block
$html_chunks[] = '
<div style="font-family: Arial, sans-serif; text-align: center; margin-top: 40px; font-size: 11px; page-break-inside: avoid;">
    <table style="width: 100%; border-spacing: 0; margin: 0 auto; border: none !important;">
        <tr>
            <td style="width: 33.33%; text-align: center; vertical-align: top; border: none !important;">
                <p style="margin: 20px 0; font-size: 14px;">Prepared By</p>
                <p style="border-top: 1px solid #000; width: 70%; margin: 40px auto;"></p>
            </td>
            <td style="width: 33.33%; text-align: center; vertical-align: top; border: none !important;">
                <p style="margin: 20px 0; font-size: 14px;">Signed By</p>
                <p style="border-top: 1px solid #000; width: 70%; margin: 40px auto;"></p>
            </td>
            <td style="width: 33.33%; text-align: center; vertical-align: top; border: none !important;">
                <p style="margin: 20px 0; font-size: 14px;">Approved By</p>
                <p style="border-top: 1px solid #000; width: 70%; margin: 40px auto; height: 50px;"></p>
                <br>
                <p style="font-size: 14px; font-weight: bold; margin-top: 10px;">Dr. Joel Fernando</p>
                <p style="font-size: 12px;">VPAA for Research</p>
            </td>
        </tr>
    </table>
</div>';
    
// Determine if we are downloading a PDF or a Word Document!
$doc_name = ($repp === 'all_modules') ? 'Comprehensive_Report_' : 'Module_Report_';

if ($format === 'word') {
    $raw_html = implode("\n", $html_chunks);
    $raw_html = preg_replace('/<img[^>]+>/i', '', $raw_html);
    
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment;filename=\"" . $doc_name . time() . ".doc\"");
    
    echo "<html xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns:m='http://schemas-microsoft.com/office/2004/12/omml' xmlns='http://www.w3.org/TR/REC-html40'>";
    echo "<head>";
    echo "<meta charset='utf-8'><title>Report</title>";
    echo "";
    echo "<style>";
    echo "@page { size: 11in 8.5in; mso-page-orientation: landscape; margin: 0.5in; }"; 
    echo "@page WordSection1 { size: 11in 8.5in; mso-page-orientation: landscape; margin: 0.5in; }";
    echo "div.WordSection1 { page: WordSection1; }";
    echo "table { border-collapse: collapse; table-layout: auto; width: 100%; }"; 
    echo "td, th { word-wrap: break-word; overflow-wrap: break-word; border: 1px solid #000; padding: 4px; }";
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