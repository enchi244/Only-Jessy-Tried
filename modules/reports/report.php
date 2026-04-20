<?php
// report.php
include('../../core/rms.php');

$object = new rms();

if (!$object->is_login()) {
    header("location:" . $object->base_url . "");
    exit;
}

if (!$object->is_master_user()) {
    header("location:" . $object->base_url . "dashboard.php");
    exit;
}

// ==============================================================================
// AJAX SECURE DYNAMIC DATA PREVIEW HANDLER (MULTI-SELECT SUPPORT)
// ==============================================================================
if (isset($_POST['action']) && $_POST['action'] == 'preview_report') {
    header('Content-Type: application/json');
    
    $allowed_tables = ['tbl_publication', 'tbl_researchconducted', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended', 'tbl_extension_project_conducted'];
    $repp = trim($_POST['repp'] ?? 'all');
    
    $tables_to_process = [];
    if ($repp === 'all' || $repp === 'all_modules') {
        $tables_to_process = $allowed_tables;
    } else {
        $requested_tables = explode(',', $repp);
        foreach ($requested_tables as $req) {
            if (in_array(trim($req), $allowed_tables)) {
                $tables_to_process[] = trim($req);
            }
        }
    }
    
    if (empty($tables_to_process)) {
        echo json_encode(['error' => 'Please select at least one valid module.']);
        exit;
    }
    
    $department = trim($_POST['department'] ?? 'all');
    $researcher_id = trim($_POST['researcher_id'] ?? '');
    $filter_status = isset($_POST['status']) ? strtolower($_POST['status']) : 'all';
    $filter_rank = trim($_POST['filter_rank'] ?? 'all');
    $filter_program = trim($_POST['filter_program'] ?? 'all');
    
    $is_all_time = empty($_POST['from_date']) && empty($_POST['to_date']);
    $from_date_ts = empty($_POST['from_date']) ? 0 : strtotime($_POST['from_date']);
    $to_date_ts = empty($_POST['to_date']) ? PHP_INT_MAX : strtotime($_POST['to_date']) + 86399;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli("localhost", "root", "", "rms");
        $conn->set_charset("utf8mb4");

        $final_data_output = [];
        $is_multi_module = count($tables_to_process) > 1;

        foreach ($tables_to_process as $current_table) {
            
            $co_author_subquery = "''";
            if ($current_table == 'tbl_researchconducted') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName, '|', IFNULL(d2.academic_rank, ''), '|', IFNULL(d2.program, '')) SEPARATOR '||') FROM tbl_research_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.research_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_publication') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName, '|', IFNULL(d2.academic_rank, ''), '|', IFNULL(d2.program, '')) SEPARATOR '||') FROM tbl_publication_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.publication_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_paperpresentation') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName, '|', IFNULL(d2.academic_rank, ''), '|', IFNULL(d2.program, '')) SEPARATOR '||') FROM tbl_paper_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.paper_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_itelectualprop') {
                $co_author_subquery = "r.coauth";
            }

            $query = "SELECT d.department AS `Department`, 
                             d.firstName, d.familyName, d.academic_rank, d.program,
                             {$co_author_subquery} AS `Co_Researchers_Raw`,
                             d.so_file AS `so_file`,
                             r.moa_file AS `moa_file`,
                             r.* FROM {$current_table} r 
                      JOIN tbl_researchdata d ON r.researcherID = d.id
                      WHERE r.status = 1"; 
                      
            $where_clauses = [];
            
            if ($department !== 'all' && !empty($department)) {
                $arr = explode(',', $department);
                $escaped = array_map(function($v) use ($conn) { return "'" . $conn->real_escape_string(trim($v)) . "'"; }, $arr);
                $where_clauses[] = "d.department IN (" . implode(',', $escaped) . ")";
            }
            if ($filter_rank !== 'all' && !empty($filter_rank)) {
                $arr = explode(',', $filter_rank);
                $escaped = array_map(function($v) use ($conn) { return "'" . $conn->real_escape_string(trim($v)) . "'"; }, $arr);
                $where_clauses[] = "d.academic_rank IN (" . implode(',', $escaped) . ")";
            }
            if ($filter_program !== 'all' && !empty($filter_program)) {
                $arr = explode(',', $filter_program);
                $escaped = array_map(function($v) use ($conn) { return "'" . $conn->real_escape_string(trim($v)) . "'"; }, $arr);
                $where_clauses[] = "d.program IN (" . implode(',', $escaped) . ")";
            }

            if (!empty($researcher_id)) {
                $r_id = $conn->real_escape_string($researcher_id);
                if (in_array($current_table, ['tbl_researchconducted', 'tbl_publication', 'tbl_paperpresentation'])) {
                    $col_table = ''; $col_fk = '';
                    if ($current_table == 'tbl_researchconducted') { $col_table = 'tbl_research_collaborators'; $col_fk = 'research_id'; }
                    if ($current_table == 'tbl_publication') { $col_table = 'tbl_publication_collaborators'; $col_fk = 'publication_id'; }
                    if ($current_table == 'tbl_paperpresentation') { $col_table = 'tbl_paper_collaborators'; $col_fk = 'paper_id'; }
                    
                    $where_clauses[] = "(r.researcherID = '$r_id' OR r.id IN (SELECT $col_fk FROM $col_table WHERE researcher_id = '$r_id'))";
                } else {
                    $where_clauses[] = "r.researcherID = '$r_id'";
                }
            }

            if (count($where_clauses) > 0) {
                $query .= " AND " . implode(" AND ", $where_clauses);
            }
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $grouped_data = array(); 
            $titles_in_range = array();
            
            while ($row = $result->fetch_assoc()) {
                
                $date_matched = false;
                if ($is_all_time) {
                    $date_matched = true;
                } else {
                    foreach ($row as $key => $val) {
                        $key_lower = strtolower($key);
                        if (strpos($key_lower, 'date') !== false || strpos($key_lower, 'start') !== false || strpos($key_lower, 'end') !== false) {
                            $val_clean = trim((string)$val);
                            if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) { $val_clean = "01-" . $val_clean; }
                            $ts = strtotime($val_clean);
                            if ($ts !== false && $ts >= $from_date_ts && $ts <= $to_date_ts) {
                                $date_matched = true;
                                break;
                            }
                        }
                    }
                }

                $has_status = in_array($current_table, ['tbl_researchconducted', 'tbl_extension_project_conducted']);
                $status_matched = true; 
                
                if ($has_status && $filter_status !== 'all') {
                    $row_stat_val = '';
                    if (isset($row['stat'])) $row_stat_val = strtolower(trim($row['stat']));
                    elseif (isset($row['status_exct'])) $row_stat_val = strtolower(trim($row['status_exct']));
                    
                    if ($filter_status === 'completed') {
                        if (strpos($row_stat_val, 'complet') === false && strpos($row_stat_val, 'finish') === false) { $status_matched = false; }
                    } elseif ($filter_status === 'ongoing') {
                        if (!preg_match('/ongoing|on-going|on going|progress|active|implement/i', $row_stat_val)) { $status_matched = false; }
                    }
                }
                
                $clean_row = array();
                $title_val = ''; 
                
                $clean_row['Department'] = htmlspecialchars($row['Department'] ?? '', ENT_QUOTES, 'UTF-8');
                
                $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-2 align-text-top" style="font-size:0.65rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
                $discipline_text = !empty($row["program"]) ? '<div class="small text-muted mt-1"><i class="fas fa-book-reader"></i> ' . htmlspecialchars($row["program"]) . '</div>' : '';
                $clean_row['Lead_Researcher'] = "<div class='font-weight-bold text-gray-800'>" . htmlspecialchars($row["firstName"] . " " . $row["familyName"]) . $rank_badge . "</div>" . $discipline_text;

                if ($current_table == 'tbl_itelectualprop') {
                    $clean_row['Co_Researchers'] = !empty($row['coauth']) ? htmlspecialchars($row['coauth'], ENT_QUOTES, 'UTF-8') : 'None';
                } else {
                    $co_html = "";
                    if (!empty($row['Co_Researchers_Raw'])) {
                        $co_list = explode('||', $row['Co_Researchers_Raw']);
                        foreach ($co_list as $co_data) {
                            $parts = explode('|', $co_data);
                            if (count($parts) >= 1) {
                                $c_name = $parts[0];
                                $c_rank = $parts[1] ?? '';
                                $c_prog = $parts[2] ?? '';
                                $c_badge = !empty($c_rank) ? '<span class="badge badge-light border px-2 py-1 ml-2 align-text-top" style="font-size:0.6rem; color:#5a5c69;">' . htmlspecialchars($c_rank) . '</span>' : '';
                                $c_discipline = !empty($c_prog) ? '<div class="text-xs text-muted" style="margin-left:5px;">— ' . htmlspecialchars($c_prog) . '</div>' : '';
                                $co_html .= "<div class='mb-2'><span class='text-gray-700 font-weight-bold' style='font-size:0.9rem;'>$c_name</span>$c_badge $c_discipline</div>";
                            }
                        }
                    }
                    $clean_row['Co_Researchers'] = !empty($co_html) ? $co_html : 'None';
                }

                $clean_row['SO_Attached'] = !empty($row['so_file']) ? 'Yes' : 'None';
                $clean_row['MOA_Attached'] = !empty($row['moa_file']) ? 'Yes' : 'None';
                
                foreach ($row as $k => $v) {
                    $kl = strtolower($k);
                    if ($kl === 'id' || $kl === 'researcherid' || $kl === 'lead_author_id' || $kl === 'lead_researcher_id' || $kl === 'status' || $kl === 'department' || $kl === 'firstname' || $kl === 'familyname' || $kl === 'academic_rank' || $kl === 'program' || $kl === 'co_researchers_raw' || $kl === 'lead_researcher' || $kl === 'co_researchers' || $kl === 'so_file' || $kl === 'moa_file' || $kl === 'has_files') continue;
                    
                    $clean_row[$k] = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
                    if ($kl === 'title') $title_val = $clean_row[$k];
                }
                
                if (!empty($title_val)) {
                    $row_hash = md5(strtolower(trim($title_val)));
                } else {
                    $row_hash = md5(implode('|', $clean_row)); 
                }
                
                if ($date_matched && $status_matched) {
                    $titles_in_range[$row_hash] = true;
                }
                
                if (!isset($grouped_data[$row_hash])) {
                    $grouped_data[$row_hash] = $clean_row;
                } else {
                    if (strpos($grouped_data[$row_hash]['Lead_Researcher'], $clean_row['Lead_Researcher']) === false) {
                        $grouped_data[$row_hash]['Lead_Researcher'] .= "<hr class='my-1'>" . $clean_row['Lead_Researcher'];
                    }
                }
            }
            
            foreach ($grouped_data as $hash => $group) {
                if (isset($titles_in_range[$hash])) {
                    if ($is_multi_module) {
                        $module_category = ucwords(str_replace(['tbl_', 'itelectualprop', '_project_conducted'], ['', 'intellectual_property', ''], $current_table));
                        $module_category = str_replace('_', ' ', $module_category);
                        
                        $extra_details = [];
                        $relevant_date = 'N/A';
                        
                        foreach ($group as $k => $v) {
                            $kl = strtolower($k);
                            if ($v !== '' && !in_array($kl, ['title', 'lead_researcher', 'co_researchers', 'department', 'so_attached', 'moa_attached'])) {
                                if (strpos($kl, 'date') !== false || strpos($kl, 'start') !== false || strpos($kl, 'end') !== false) {
                                    $relevant_date = $v;
                                } else {
                                    $extra_details[] = ucfirst(str_replace('_', ' ', $k)) . ": " . $v;
                                }
                            }
                        }

                        $final_data_output[] = [
                            'Module_Category' => $module_category,
                            'Research_Title' => $group['title'] ?? 'N/A',
                            'Department' => $group['Department'] ?? 'N/A',
                            'Lead_Researcher' => $group['Lead_Researcher'] ?? 'N/A',
                            'Co_Researchers' => $group['Co_Researchers'] ?? 'None',
                            'SO_Attached' => $group['SO_Attached'],
                            'MOA_Attached' => $group['MOA_Attached'],
                            'Relevant_Date' => $relevant_date,
                            'Specific_Details' => implode(" | ", $extra_details)
                        ];
                    } else {
                        $final_data_output[] = $group;
                    }
                }
            }
            $stmt->close();
        }
        
        $conn->close();
        echo json_encode(['success' => true, 'data' => $final_data_output]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'A database error occurred.']);
    }
    exit;
}
// ==============================================================================

include('../../includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

<style>
    body { background-color: #f4f7f6; }
    /* THE CSS FIX: Changed overflow to visible so dropdowns aren't cut off! */
    .enterprise-card { background: #ffffff; border: none; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.04); margin-bottom: 2rem; overflow: visible; }
    .enterprise-card-header { background-color: #ffffff; border-bottom: 1px solid #edf2f9; padding: 1.5rem 1.5rem 1rem 1.5rem; border-radius: 12px 12px 0 0; }
    .enterprise-card-header h6 { color: #2c3e50; font-weight: 700; font-size: 1.1rem; letter-spacing: 0.02rem; margin: 0; }
    .enterprise-card-body { padding: 1.5rem; }
    .form-label-custom { font-size: 0.8rem; font-weight: 600; color: #6e84a3; text-transform: uppercase; letter-spacing: 0.05rem; margin-bottom: 0.5rem; display: block; }
    
    .form-control-custom { background-color: #f9fbfd; border: 1px solid #d2ddec; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.95rem; color: #12263f; transition: all 0.2s; height: 42px; }
    .form-control-custom:focus { background-color: #ffffff; border-color: #2c7be5; box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.15); outline: none; }
    .form-control-custom:disabled { background-color: #eaecf4; color: #b7b9cc; cursor: not-allowed; border-color: #eaecf4; }
    
    .btn-enterprise-search { background-color: #2c7be5; color: #fff; border: none; border-radius: 8px; height: 42px; width: 100%; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
    .btn-enterprise-search:hover { background-color: #1a68d1; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(44, 123, 229, 0.2); cursor: pointer; }
    
    .dt-buttons .btn-success { background-color: #00d27a !important; border-color: #00d27a !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; }
    .dt-buttons .btn-info { background-color: #39afd1 !important; border-color: #39afd1 !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    .dt-buttons .btn-danger { background-color: #e74a3b !important; border-color: #e74a3b !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    .dt-buttons .btn-primary { background-color: #2c7be5 !important; border-color: #2c7be5 !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    
    .badge-soft-success { background-color: rgba(0, 210, 122, 0.15); color: #00d27a; font-weight: 700; padding: 0.5em 0.8em; border-radius: 6px; }
    .table-enterprise { border-collapse: separate; border-spacing: 0; width: 100%; }
    .table-enterprise thead th { background-color: #f9fbfd; color: #6e84a3; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #edf2f9; border-top: none; padding: 1rem; white-space: nowrap; }
    .table-enterprise tbody td { padding: 1rem; color: #12263f; font-size: 0.95rem; vertical-align: middle; border-bottom: 1px solid #edf2f9; }
    .table-enterprise tbody tr:hover td { background-color: #f9fbfd; }

    /* Custom Vanilla JS Dropdown Styles (Conflict Free) */
    .dropdown-menu-custom { box-shadow: 0 10px 25px rgba(0,0,0,0.2); border-radius: 10px; z-index: 9999 !important; }
    .custom-control-label { cursor: pointer; color: #4e73df; font-weight: 600; font-size: 0.9rem; }
    .check-item + label { color: #5a5c69; font-weight: 500; font-size: 0.85rem;}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800" style="font-weight: 700;">Data Extraction & Reports</h1>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">Filter, analyze, and export multiple modules at once.</p>
        </div>
    </div>

    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h6><i class="fas fa-filter text-primary mr-2"></i> Report Parameters</h6>
        </div>
        <div class="enterprise-card-body">
            <form id="filterForm">
                <div class="row align-items-end mb-3">
                    
                    <div class="col-lg-3 mb-2">
                        <label class="form-label-custom">Target Modules</label>
                        <div class="custom-multi-select position-relative w-100" id="reppDropdownContainer" data-name="Modules">
                            <div class="form-control-custom w-100 text-left d-flex justify-content-between align-items-center bg-white shadow-sm dropdown-btn" style="cursor: pointer; border-color: #b7b9cc;">
                                <span class="btn-text text-truncate font-weight-bold text-primary">All Modules</span>
                                <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:105%; left:0; right:0; background:white; max-height:280px; overflow-y:auto; border:1px solid #edf2f9; padding:15px;">
                                <div class="custom-control custom-checkbox mb-3 pb-3 border-bottom">
                                    <input type="checkbox" class="custom-control-input check-all" id="repp_all" checked>
                                    <label class="custom-control-label" for="repp_all">Select All Modules</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_pub" value="tbl_publication" checked>
                                    <label class="custom-control-label" for="repp_pub">Publications</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_rc" value="tbl_researchconducted" checked>
                                    <label class="custom-control-label" for="repp_rc">Research Conducted</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_ext" value="tbl_extension_project_conducted" checked>
                                    <label class="custom-control-label" for="repp_ext">Extension Projects</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_ip" value="tbl_itelectualprop" checked>
                                    <label class="custom-control-label" for="repp_ip">Intellectual Property</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_pp" value="tbl_paperpresentation" checked>
                                    <label class="custom-control-label" for="repp_pp">Paper Presentations</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-0">
                                    <input type="checkbox" class="custom-control-input check-item" id="repp_train" value="tbl_trainingsattended" checked>
                                    <label class="custom-control-label" for="repp_train">Trainings Attended</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-2">
                        <label class="form-label-custom text-primary"><i class="fas fa-user-tie mr-1"></i> Specific Researcher</label>
                        <select name="researcher_id" id="researcher_id" class="form-control-custom w-100 border-primary">
                            <option value="">All Personnel</option>
                            <?php
                            $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata WHERE status = 1 ORDER BY firstName ASC";
                            $res_researchers = $object->get_result();
                            foreach($res_researchers as $r) {
                                echo '<option value="'.$r["id"].'">'.htmlspecialchars($r["firstName"].' '.$r["familyName"]).'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-3 mb-2">
                        <label class="form-label-custom">Department</label>
                        <div class="custom-multi-select position-relative w-100" id="deptDropdownContainer" data-name="Departments">
                            <div class="form-control-custom w-100 text-left d-flex justify-content-between align-items-center bg-white shadow-sm dropdown-btn" style="cursor: pointer; border-color: #b7b9cc;">
                                <span class="btn-text text-truncate font-weight-bold text-primary">All Departments</span>
                                <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:105%; left:0; right:0; background:white; max-height:280px; overflow-y:auto; border:1px solid #edf2f9; border-radius:10px; padding:15px;">
                                <div class="custom-control custom-checkbox mb-3 pb-3 border-bottom">
                                    <input type="checkbox" class="custom-control-input check-all" id="dept_all" checked>
                                    <label class="custom-control-label" for="dept_all">All Departments</label>
                                </div>
                                <?php
                                $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                                $category_result = $object->get_result();
                                $i = 0;
                                foreach($category_result as $category) {
                                    $cat = htmlspecialchars($category["category_name"]);
                                    echo '<div class="custom-control custom-checkbox mb-2">';
                                    echo '<input type="checkbox" class="custom-control-input check-item" id="dept_'.$i.'" value="'.$cat.'" checked>';
                                    echo '<label class="custom-control-label font-weight-normal text-dark" for="dept_'.$i.'">'.$cat.'</label>';
                                    echo '</div>';
                                    $i++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-2" id="statusFilterContainer">
                        <label class="form-label-custom text-info" title="Only applies to Research & Extension"><i class="fas fa-tasks mr-1"></i> Status</label>
                        <select name="status" id="status" class="form-control-custom w-100 border-info">
                            <option value="all">All</option>
                            <option value="ongoing">On-going</option>
                            <option value="completed">Done</option>
                        </select>
                    </div>
                </div>

                <div class="row align-items-end">
                    
                    <div class="col-lg-3 mb-2">
                        <label class="form-label-custom text-danger">Academic Rank</label>
                        <div class="custom-multi-select position-relative w-100" id="rankDropdownContainer" data-name="Ranks">
                            <div class="form-control-custom w-100 text-left d-flex justify-content-between align-items-center bg-white shadow-sm dropdown-btn border-danger" style="cursor: pointer;">
                                <span class="btn-text text-truncate font-weight-bold text-primary">All Ranks</span>
                                <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:105%; left:0; right:0; background:white; max-height:280px; overflow-y:auto; border:1px solid #edf2f9; border-radius:10px; padding:15px;">
                                <div class="custom-control custom-checkbox mb-3 pb-3 border-bottom">
                                    <input type="checkbox" class="custom-control-input check-all" id="rank_all" checked>
                                    <label class="custom-control-label" for="rank_all">All Ranks</label>
                                </div>
                                <?php
                                $ranks = ["Instructor I", "Instructor II", "Instructor III", "Assistant Professor I", "Assistant Professor II", "Assistant Professor III", "Assistant Professor IV", "Associate Professor I", "Associate Professor II", "Associate Professor III", "Associate Professor IV", "Associate Professor V", "Professor I", "Professor II", "Professor III", "Professor IV", "Professor V", "Professor VI", "College Professor", "University Professor"];
                                foreach($ranks as $idx => $r) {
                                    echo '<div class="custom-control custom-checkbox mb-2">';
                                    echo '<input type="checkbox" class="custom-control-input check-item" id="rank_'.$idx.'" value="'.$r.'" checked>';
                                    echo '<label class="custom-control-label font-weight-normal text-dark" for="rank_'.$idx.'">'.$r.'</label>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 mb-2">
                        <label class="form-label-custom text-success">Discipline / Program</label>
                        <div class="custom-multi-select position-relative w-100" id="progDropdownContainer" data-name="Programs">
                            <div class="form-control-custom w-100 text-left d-flex justify-content-between align-items-center bg-white shadow-sm dropdown-btn border-success" style="cursor: pointer;">
                                <span class="btn-text text-truncate font-weight-bold text-primary">All Disciplines</span>
                                <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:105%; left:0; right:0; background:white; max-height:280px; overflow-y:auto; border:1px solid #edf2f9; border-radius:10px; padding:15px;">
                                <div class="custom-control custom-checkbox mb-3 pb-3 border-bottom">
                                    <input type="checkbox" class="custom-control-input check-all" id="prog_all" checked>
                                    <label class="custom-control-label" for="prog_all">All Disciplines</label>
                                </div>
                                <?php
                                $object->query = "SELECT * FROM tbl_majordiscipline ORDER BY major ASC";
                                $result_prog = $object->get_result();
                                $j = 0;
                                foreach($result_prog as $row) {
                                    $prog = htmlspecialchars($row["major"]);
                                    echo '<div class="custom-control custom-checkbox mb-2">';
                                    echo '<input type="checkbox" class="custom-control-input check-item" id="prog_'.$j.'" value="'.$prog.'" checked>';
                                    echo '<label class="custom-control-label font-weight-normal text-dark" for="prog_'.$j.'">'.$prog.'</label>';
                                    echo '</div>';
                                    $j++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="form-label-custom">Start Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control-custom w-100">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <label class="form-label-custom">End Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control-custom w-100">
                    </div>

                    <div class="col-lg-2 mb-2">
                        <button type="button" id="previewBtn" class="btn-enterprise-search" title="Run Query">
                            <i class="fas fa-search mr-2"></i> Extract
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="enterprise-card" id="previewContainer" style="display: none; overflow: hidden;">
        <div class="enterprise-card-header d-flex flex-row align-items-center justify-content-between">
            <h6><i class="fas fa-database text-success mr-2"></i> Query Results Matrix</h6>
            <span class="badge-soft-success" id="recordCount">0 Records Found</span>
        </div>
        <div class="enterprise-card-body">
            <div class="table-responsive">
                <table class="table-enterprise" id="previewTable"></table>
            </div>
        </div>
    </div>

</div> 

<?php include('../../includes/footer.php'); ?>

<script src="<?php echo $object->base_url; ?>vendor/jquery/jquery.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    
    // --- VANILLA JS DROPDOWN LOGIC (100% Conflict Free) ---
    $('.dropdown-btn').click(function(e) {
        e.stopPropagation();
        var $menu = $(this).siblings('.dropdown-menu-custom');
        $('.dropdown-menu-custom').not($menu).slideUp('fast'); 
        $menu.slideToggle('fast');
    });
    
    $('.dropdown-menu-custom').on('click', function(e) {
        e.stopPropagation(); 
    });

    $(document).click(function() {
        $('.dropdown-menu-custom').slideUp('fast'); 
    });
    // -----------------------------------------------------

    function updateDropdownText($container) {
        var $btnText = $container.find('.btn-text');
        var $checkAll = $container.find('.check-all');
        var $items = $container.find('.check-item');
        var checkedCount = $items.filter(':checked').length;
        var totalCount = $items.length;
        var typeName = $container.data('name'); 

        if (checkedCount === totalCount) {
            $checkAll.prop('checked', true);
            $btnText.text('All ' + typeName).removeClass('text-danger text-warning').addClass('text-primary');
        } else if (checkedCount === 0) {
            $checkAll.prop('checked', false);
            $btnText.text('None Selected').removeClass('text-primary text-warning').addClass('text-danger');
        } else if (checkedCount === 1) {
            $checkAll.prop('checked', false);
            $btnText.text($items.filter(':checked').next('label').text()).removeClass('text-danger text-primary').addClass('text-warning text-dark');
        } else {
            $checkAll.prop('checked', false);
            $btnText.text(checkedCount + ' ' + typeName + ' Selected').removeClass('text-danger text-primary').addClass('text-warning text-dark');
        }
        
        if ($container.attr('id') === 'reppDropdownContainer') {
            var statusAllowedModules = ['tbl_researchconducted', 'tbl_extension_project_conducted'];
            var allowStatus = false;
            $items.filter(':checked').each(function() {
                if (statusAllowedModules.includes($(this).val())) { allowStatus = true; }
            });
            if (!allowStatus) {
                $('#status').val('all').prop('disabled', true);
                $('#statusFilterContainer').css('opacity', '0.5'); 
            } else {
                $('#status').prop('disabled', false);
                $('#statusFilterContainer').css('opacity', '1'); 
            }
        }
    }

    $('.custom-multi-select .check-all').change(function() {
        var $container = $(this).closest('.custom-multi-select');
        $container.find('.check-item').prop('checked', $(this).is(':checked'));
        updateDropdownText($container);
    });

    $('.custom-multi-select .check-item').change(function() {
        var $container = $(this).closest('.custom-multi-select');
        updateDropdownText($container);
    });

    $('.custom-multi-select').each(function() {
        updateDropdownText($(this));
    });

    function getDropdownValues(containerId) {
        var $container = $('#' + containerId);
        var $items = $container.find('.check-item');
        if ($items.filter(':checked').length === 0) { return ''; }
        if ($items.filter(':checked').length === $items.length) { return 'all'; }
        
        var selected = [];
        $items.filter(':checked').each(function() {
            selected.push($(this).val());
        });
        return selected.join(',');
    }
    // ---------------------------------

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        $('#previewBtn').click();
    });

    $('#previewBtn').click(function() {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var researcherId = $('#researcher_id').val();
        var statusFilter = $('#status').val(); 
        
        var repp = getDropdownValues('reppDropdownContainer');
        var department = getDropdownValues('deptDropdownContainer');
        var filterRank = getDropdownValues('rankDropdownContainer');
        var filterProgram = getDropdownValues('progDropdownContainer');

        if (repp === '') {
            Swal.fire({ icon: 'warning', title: 'Action Required', text: 'Please select at least one module from the dropdown.', confirmButtonColor: '#2c7be5' });
            return;
        }

        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            Swal.fire({ icon: 'error', title: 'Invalid Timeline', text: 'The Start Date cannot be chronologically after the End Date.', confirmButtonColor: '#2c7be5' });
            return;
        }

        Swal.fire({ title: 'Executing Query...', text: 'Extracting data securely from the server.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        $.ajax({
            url: 'report.php',
            type: 'POST',
            data: { 
                action: 'preview_report', 
                from_date: fromDate, 
                to_date: toDate, 
                department: department, 
                researcher_id: researcherId, 
                repp: repp, 
                status: statusFilter,
                filter_rank: filterRank,
                filter_program: filterProgram
            },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                } else {
                    if ($.fn.DataTable.isDataTable('#previewTable')) { $('#previewTable').DataTable().destroy(); }
                    $('#previewTable').empty();
                    
                    if (response.data && response.data.length > 0) {
                        $('#recordCount').text(response.data.length + " Records Confirmed");
                        
                        var columnsMap = [];
                        var theadHtml = '<thead><tr>';
                        var defSortIndex = 0;
                        
                        $.each(response.data[0], function(key, value) {
                            var cleanHeader = key.replace(/_/g, ' ');
                            theadHtml += '<th>' + cleanHeader + '</th>';
                            columnsMap.push({ data: key });
                            if(key.toLowerCase() === 'department') defSortIndex = columnsMap.length - 1;
                        });
                        theadHtml += '</tr></thead>';
                        $('#previewTable').html(theadHtml + '<tbody></tbody>');
                        
                        var exportCustomize = function ( data ) {
                            var deptIndex = -1; var modIndex = -1;
                            for (var i=0; i<data.header.length; i++) {
                                var headerText = data.header[i].toLowerCase().trim();
                                if (headerText === 'department') deptIndex = i;
                                if (headerText === 'module category' || headerText === 'module_category') modIndex = i;
                            }
                            if (deptIndex !== -1) {
                                data.body.sort(function(a, b) {
                                    var deptA = (a[deptIndex] || '').toString();
                                    var deptB = (b[deptIndex] || '').toString();
                                    if (deptA === deptB && modIndex !== -1) {
                                        var modA = (a[modIndex] || '').toString();
                                        var modB = (b[modIndex] || '').toString();
                                        return modA.localeCompare(modB);
                                    }
                                    return deptA.localeCompare(deptB);
                                });
                                var newBody = [];
                                var currentDept = null;
                                for (var i = 0; i < data.body.length; i++) {
                                    var dept = data.body[i][deptIndex];
                                    if (dept !== currentDept) {
                                        if (currentDept !== null) { newBody.push(new Array(data.header.length).fill('')); }
                                        var divider = new Array(data.header.length).fill('');
                                        divider[deptIndex] = '==== ' + (dept || 'UNSPECIFIED DEPARTMENT').toUpperCase() + ' ====';
                                        newBody.push(divider);
                                        currentDept = dept;
                                    }
                                    newBody.push(data.body[i]);
                                }
                                data.body = newBody;
                            }
                        };

                        $('#previewTable').DataTable({
                            data: response.data,
                            columns: columnsMap,
                            pageLength: 10,
                            scrollX: true,
                            order: [[defSortIndex, 'asc']], 
                            dom: "<'row'<'col-sm-12 col-md-7'B><'col-sm-12 col-md-5 d-flex justify-content-end'f>>" +
                                 "<'row'<'col-sm-12'tr>>" +
                                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                            buttons: [
                                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: 'btn btn-success btn-sm', title: 'Data_Extraction_Report', customizeData: exportCustomize },
                                { extend: 'csvHtml5', text: '<i class="fas fa-file-csv mr-1"></i> CSV', className: 'btn btn-info btn-sm', title: 'Data_Extraction_Report', customizeData: exportCustomize },
                                {
                                    text: '<i class="fas fa-file-word mr-1"></i> Word Doc',
                                    className: 'btn btn-primary btn-sm',
                                    action: function ( e, dt, node, config ) {
                                        var p_fromDate = $('#from_date').val();
                                        var p_toDate = $('#to_date').val();
                                        var p_researcher = encodeURIComponent($('#researcher_id').val());
                                        var p_status = $('#status').val(); 
                                        
                                        var p_repp = encodeURIComponent(getDropdownValues('reppDropdownContainer'));
                                        var p_department = encodeURIComponent(getDropdownValues('deptDropdownContainer'));
                                        var p_rank = encodeURIComponent(getDropdownValues('rankDropdownContainer'));
                                        var p_prog = encodeURIComponent(getDropdownValues('progDropdownContainer'));
                                        
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ title: 'Compiling Document...', text: 'Formatting Word layout.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                                        setTimeout(function() {
                                            window.location.href = 'print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&researcher_id=' + p_researcher + '&repp=' + p_repp + '&status=' + p_status + '&academic_rank=' + p_rank + '&program=' + p_prog + '&format=word&_cb=' + cb;
                                            swalLoading.close();
                                        }, 800);
                                    }
                                },
                                {
                                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF File',
                                    className: 'btn btn-danger btn-sm',
                                    action: function ( e, dt, node, config ) {
                                        var p_fromDate = $('#from_date').val();
                                        var p_toDate = $('#to_date').val();
                                        var p_researcher = encodeURIComponent($('#researcher_id').val());
                                        var p_status = $('#status').val(); 
                                        
                                        var p_repp = encodeURIComponent(getDropdownValues('reppDropdownContainer'));
                                        var p_department = encodeURIComponent(getDropdownValues('deptDropdownContainer'));
                                        var p_rank = encodeURIComponent(getDropdownValues('rankDropdownContainer'));
                                        var p_prog = encodeURIComponent(getDropdownValues('progDropdownContainer'));
                                        
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ title: 'Compiling Document...', text: 'Formatting PDF layout.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                                        setTimeout(function() {
                                            window.location.href = 'print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&researcher_id=' + p_researcher + '&repp=' + p_repp + '&status=' + p_status + '&academic_rank=' + p_rank + '&program=' + p_prog + '&format=pdf&_cb=' + cb;
                                            swalLoading.close();
                                        }, 800);
                                    }
                                }
                            ]
                        });
                        
                    } else {
                        $('#recordCount').text("0 Records Confirmed");
                        $('#previewTable').html('<tbody><tr><td class="text-center p-4 text-muted">No records align with your selected parameters.</td></tr></tbody>');
                    }
                    
                    $('#previewContainer').fadeIn(600);
                    $('html, body').animate({ scrollTop: $("#previewContainer").offset().top - 20 }, 500);
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('Connection Error', 'Failed to communicate with the database server.', 'error');
            }
        });
    });
});
</script>