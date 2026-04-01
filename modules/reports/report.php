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
// AJAX SECURE DYNAMIC DATA PREVIEW HANDLER
// ==============================================================================
if (isset($_POST['action']) && $_POST['action'] == 'preview_report') {
    header('Content-Type: application/json');
    
    $allowed_tables = ['tbl_publication', 'tbl_researchconducted', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended', 'tbl_extension_project_conducted'];
    $repp = $_POST['repp'];
    
    if (!in_array($repp, $allowed_tables) && $repp !== 'all_modules') {
        echo json_encode(['error' => 'Invalid report type selected.']);
        exit;
    }
    
    $department = trim($_POST['department']);
    $researcher_id = trim($_POST['researcher_id'] ?? '');
    $filter_status = isset($_POST['status']) ? strtolower($_POST['status']) : 'all';
    
    $is_all_time = empty($_POST['from_date']) && empty($_POST['to_date']);
    $from_date_ts = empty($_POST['from_date']) ? 0 : strtotime($_POST['from_date']);
    $to_date_ts = empty($_POST['to_date']) ? PHP_INT_MAX : strtotime($_POST['to_date']) + 86399;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli("localhost", "root", "", "rms");
        $conn->set_charset("utf8mb4");

        $tables_to_process = ($repp === 'all_modules') ? $allowed_tables : [$repp];
        $final_data_output = [];

        foreach ($tables_to_process as $current_table) {
            
            // THE FIX: DYNAMICALLY FETCH CO-AUTHORS FOR THE PREVIEW TABLE
            $co_author_subquery = "''";
            if ($current_table == 'tbl_researchconducted') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') FROM tbl_research_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.research_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_publication') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') FROM tbl_publication_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.publication_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_paperpresentation') {
                $co_author_subquery = "(SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') FROM tbl_paper_collaborators col JOIN tbl_researchdata d2 ON col.researcher_id = d2.id WHERE col.paper_id = r.id AND col.researcher_id != r.researcherID)";
            } elseif ($current_table == 'tbl_itelectualprop') {
                $co_author_subquery = "r.coauth";
            }

            $query = "SELECT d.department AS `Department`, 
                             CONCAT(d.firstName, ' ', d.familyName) AS `Lead_Researcher`, 
                             {$co_author_subquery} AS `Co_Researchers`,
                             d.so_file AS `so_file`,
                             r.moa_file AS `moa_file`,
                             r.* FROM {$current_table} r 
                      JOIN tbl_researchdata d ON r.researcherID = d.id";
                      
            $where_clauses = [];
            if (!empty($department)) {
                $where_clauses[] = "d.department = '" . $conn->real_escape_string($department) . "'";
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
                $query .= " WHERE " . implode(" AND ", $where_clauses);
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

                $status_matched = true;
                if ($filter_status !== 'all') {
                    $row_stat_val = '';
                    if (isset($row['stat'])) $row_stat_val = strtolower(trim($row['stat']));
                    elseif (isset($row['status_exct'])) $row_stat_val = strtolower(trim($row['status_exct']));
                    elseif (isset($row['status'])) $row_stat_val = strtolower(trim($row['status']));
                    
                    if ($row_stat_val !== '') {
                        if ($filter_status === 'completed') {
                            if (strpos($row_stat_val, 'complet') === false && strpos($row_stat_val, 'finish') === false) { $status_matched = false; }
                        } elseif ($filter_status === 'ongoing') {
                            if (!preg_match('/ongoing|on-going|on going|progress|active|implement/i', $row_stat_val)) { $status_matched = false; }
                        }
                    } else {
                        $status_matched = false;
                    }
                }
                
                $clean_row = array();
                $title_val = ''; 
                
                $clean_row['Department'] = htmlspecialchars($row['Department'] ?? '', ENT_QUOTES, 'UTF-8');
                $clean_row['Lead_Researcher'] = htmlspecialchars($row['Lead_Researcher'] ?? '', ENT_QUOTES, 'UTF-8');
                $clean_row['Co_Researchers'] = !empty($row['Co_Researchers']) ? htmlspecialchars($row['Co_Researchers'], ENT_QUOTES, 'UTF-8') : 'None';
                $clean_row['SO_Attached'] = !empty($row['so_file']) ? 'Yes' : 'None';
                $clean_row['MOA_Attached'] = !empty($row['moa_file']) ? 'Yes' : 'None';
                
                foreach ($row as $k => $v) {
                    $kl = strtolower($k);
                    if ($kl === 'id' || $kl === 'researcherid' || $kl === 'status' || $kl === 'department' || $kl === 'lead_researcher' || $kl === 'co_researchers' || $kl === 'so_file' || $kl === 'moa_file') continue;
                    
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
                    $existing_names = explode(', ', $grouped_data[$row_hash]['Lead_Researcher']);
                    if (!in_array($clean_row['Lead_Researcher'], $existing_names)) {
                        $grouped_data[$row_hash]['Lead_Researcher'] .= ', ' . $clean_row['Lead_Researcher'];
                    }
                    $existing_depts = explode(', ', $grouped_data[$row_hash]['Department']);
                    if (!in_array($clean_row['Department'], $existing_depts)) {
                        $grouped_data[$row_hash]['Department'] .= ', ' . $clean_row['Department'];
                    }
                    if ($clean_row['SO_Attached'] === 'Yes') $grouped_data[$row_hash]['SO_Attached'] = 'Yes';
                    if ($clean_row['MOA_Attached'] === 'Yes') $grouped_data[$row_hash]['MOA_Attached'] = 'Yes';
                }
            }
            
            foreach ($grouped_data as $hash => $group) {
                if (isset($titles_in_range[$hash])) {
                    if ($repp === 'all_modules') {
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
        error_log("Report Dynamic Fetch Error: " . $e->getMessage());
        echo json_encode(['error' => 'A database error occurred. Please try again later.']);
    }
    exit;
}
// ==============================================================================

include('../../includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

<style>
    body { background-color: #f4f7f6; }
    .enterprise-card { background: #ffffff; border: none; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.04); margin-bottom: 2rem; overflow: hidden; }
    .enterprise-card-header { background-color: #ffffff; border-bottom: 1px solid #edf2f9; padding: 1.5rem 1.5rem 1rem 1.5rem; }
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
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800" style="font-weight: 700;">Data Extraction & Reports</h1>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">Filter, analyze, and dynamically export research metrics.</p>
        </div>
    </div>

    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h6><i class="fas fa-filter text-primary mr-2"></i> Report Parameters</h6>
        </div>
        <div class="enterprise-card-body">
            <form id="filterForm">
                <div class="row align-items-end">
                    
                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label-custom">Target Module</label>
                        <select name="repp" id="repp" class="form-control-custom w-100">
                            <option value="all_modules" style="font-weight: bold;">All Modules</option>
                            <option value="tbl_publication">Publication</option>
                            <option value="tbl_researchconducted">Research Conducted</option>
                            <option value="tbl_extension_project_conducted">Extension Projects</option>
                            <option value="tbl_itelectualprop">Intellectual Property</option>
                            <option value="tbl_paperpresentation">Paper Presentation</option>
                            <option value="tbl_trainingsattended">Trainings Attended</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label-custom">Department</label>
                        <select name="department" id="department" class="form-control-custom w-100">
                            <option value="">All Departments</option>
                            <?php
                            $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                            $category_result = $object->get_result();
                            foreach($category_result as $category) {
                                echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label-custom text-primary"><i class="fas fa-user-tie mr-1"></i> Researcher</label>
                        <select name="researcher_id" id="researcher_id" class="form-control-custom w-100 border-primary">
                            <option value="">All Personnel</option>
                            <?php
                            $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY firstName ASC";
                            $res_researchers = $object->get_result();
                            foreach($res_researchers as $r) {
                                echo '<option value="'.$r["id"].'">'.htmlspecialchars($r["firstName"].' '.$r["familyName"]).'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3" id="statusFilterContainer">
                        <label class="form-label-custom text-info"><i class="fas fa-tasks mr-1"></i> Project Status</label>
                        <select name="status" id="status" class="form-control-custom w-100 border-info">
                            <option value="all">All Statuses</option>
                            <option value="ongoing">On-going</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-4 mb-3">
                        <label class="form-label-custom">Start Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control-custom w-100" style="padding: 0.2rem;">
                    </div>

                    <div class="col-lg-1 col-md-4 mb-3">
                        <label class="form-label-custom">End Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control-custom w-100" style="padding: 0.2rem;">
                    </div>

                    <div class="col-lg-2 col-md-12 mb-3">
                        <button type="button" id="previewBtn" class="btn-enterprise-search" title="Run Query">
                            <i class="fas fa-search mr-2"></i> Extract
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="enterprise-card" id="previewContainer" style="display: none;">
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
    
    $('#repp').on('change', function() {
        var selectedModule = $(this).val();
        var statusAllowedModules = ['tbl_researchconducted', 'tbl_extension_project_conducted'];
        
        if (!statusAllowedModules.includes(selectedModule)) {
            $('#status').val('all').prop('disabled', true);
            $('#statusFilterContainer').css('opacity', '0.5'); 
        } else {
            $('#status').prop('disabled', false);
            $('#statusFilterContainer').css('opacity', '1'); 
        }
    });
    $('#repp').trigger('change');

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        $('#previewBtn').click();
    });

    $('#previewBtn').click(function() {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var department = $('#department').val();
        var researcherId = $('#researcher_id').val();
        var repp = $('#repp').val();
        var statusFilter = $('#status').val(); 

        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            Swal.fire({ icon: 'error', title: 'Invalid Timeline', text: 'The Start Date cannot be chronologically after the End Date.', confirmButtonColor: '#2c7be5' });
            return;
        }

        Swal.fire({ title: 'Executing Query...', text: 'Extracting data securely from the server.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        $.ajax({
            url: 'report.php',
            type: 'POST',
            data: { action: 'preview_report', from_date: fromDate, to_date: toDate, department: department, researcher_id: researcherId, repp: repp, status: statusFilter },
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
                                        var p_department = encodeURIComponent($('#department').val());
                                        var p_researcher = encodeURIComponent($('#researcher_id').val());
                                        var p_repp = $('#repp').val();
                                        var p_status = $('#status').val(); 
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ title: 'Compiling Document...', text: 'Formatting beautiful Word layout.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                                        setTimeout(function() {
                                            window.location.href = 'print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&researcher_id=' + p_researcher + '&repp=' + p_repp + '&status=' + p_status + '&format=word&_cb=' + cb;
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
                                        var p_department = encodeURIComponent($('#department').val());
                                        var p_researcher = encodeURIComponent($('#researcher_id').val());
                                        var p_repp = $('#repp').val();
                                        var p_status = $('#status').val(); 
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ title: 'Compiling Document...', text: 'Formatting beautiful PDF layout.', icon: 'info', showConfirmButton: false, allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                                        setTimeout(function() {
                                            window.location.href = 'print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&researcher_id=' + p_researcher + '&repp=' + p_repp + '&status=' + p_status + '&format=pdf&_cb=' + cb;
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