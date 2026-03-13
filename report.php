<?php
// report.php
include('core/rms.php');

$object = new rms();

// Access Control
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
    
    $allowed_tables = ['tbl_publication', 'tbl_researchconducted', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended'];
    $repp = $_POST['repp'];
    
    if (!in_array($repp, $allowed_tables) && $repp !== 'all_modules') {
        echo json_encode(['error' => 'Invalid report type selected.']);
        exit;
    }
    
    $department = trim($_POST['department']);
    
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
            // INJECTED SO_FILE and MOA_FILE directly into the extraction query!
            $query = "SELECT d.department AS `Department`, 
                             CONCAT(d.firstName, ' ', d.familyName) AS `Researcher_Name`, 
                             d.so_file AS `so_file`,
                             r.moa_file AS `moa_file`,
                             r.* FROM {$current_table} r 
                      JOIN tbl_researchdata d ON r.researcherID = d.id";
                      
            if (!empty($department)) {
                $query .= " WHERE d.department = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $department);
            } else {
                $stmt = $conn->prepare($query);
            }
            
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
                            if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) {
                                $val_clean = "01-" . $val_clean;
                            }
                            $ts = strtotime($val_clean);
                            if ($ts !== false && $ts >= $from_date_ts && $ts <= $to_date_ts) {
                                $date_matched = true;
                                break;
                            }
                        }
                    }
                }
                
                $clean_row = array();
                $title_val = ''; 
                
                // Force strict column ordering so Excel looks beautiful
                $clean_row['Department'] = htmlspecialchars($row['Department'] ?? '', ENT_QUOTES, 'UTF-8');
                $clean_row['Researcher_Name'] = htmlspecialchars($row['Researcher_Name'] ?? '', ENT_QUOTES, 'UTF-8');
                $clean_row['SO_Attached'] = !empty($row['so_file']) ? 'Yes' : 'None';
                $clean_row['MOA_Attached'] = !empty($row['moa_file']) ? 'Yes' : 'None';
                
                foreach ($row as $k => $v) {
                    $kl = strtolower($k);
                    // Skip system keys and manually placed keys
                    if ($kl === 'id' || $kl === 'researcherid' || $kl === 'status' || $kl === 'department' || $kl === 'researcher_name' || $kl === 'so_file' || $kl === 'moa_file') continue;
                    
                    $clean_row[$k] = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
                    if ($kl === 'title') $title_val = $clean_row[$k];
                }
                
                if (!empty($title_val)) {
                    $row_hash = md5(strtolower(trim($title_val)));
                } else {
                    $row_hash = md5(implode('|', $clean_row)); 
                }
                
                if ($date_matched) {
                    $titles_in_range[$row_hash] = true;
                }
                
                if (!isset($grouped_data[$row_hash])) {
                    $grouped_data[$row_hash] = $clean_row;
                } else {
                    // Merge Names
                    $existing_names = explode(', ', $grouped_data[$row_hash]['Researcher_Name']);
                    if (!in_array($clean_row['Researcher_Name'], $existing_names)) {
                        $grouped_data[$row_hash]['Researcher_Name'] .= ', ' . $clean_row['Researcher_Name'];
                    }
                    // Merge Departments
                    $existing_depts = explode(', ', $grouped_data[$row_hash]['Department']);
                    if (!in_array($clean_row['Department'], $existing_depts)) {
                        $grouped_data[$row_hash]['Department'] .= ', ' . $clean_row['Department'];
                    }
                    // Sync File Indicators (If anyone has an SO/MOA, mark the project as Yes)
                    if ($clean_row['SO_Attached'] === 'Yes') $grouped_data[$row_hash]['SO_Attached'] = 'Yes';
                    if ($clean_row['MOA_Attached'] === 'Yes') $grouped_data[$row_hash]['MOA_Attached'] = 'Yes';
                }
            }
            
            // Map the grouped data
            foreach ($grouped_data as $hash => $group) {
                if (isset($titles_in_range[$hash])) {
                    if ($repp === 'all_modules') {
                        $module_category = ucwords(str_replace(['tbl_', 'itelectualprop'], ['', 'intellectual_property'], $current_table));
                        $module_category = str_replace('_', ' ', $module_category);
                        
                        $extra_details = [];
                        $relevant_date = 'N/A';
                        
                        foreach ($group as $k => $v) {
                            $kl = strtolower($k);
                            if ($v !== '' && !in_array($kl, ['title', 'researcher_name', 'department', 'so_attached', 'moa_attached'])) {
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
                            'Researchers' => $group['Researcher_Name'] ?? 'N/A',
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

include('includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

<style>
    body { background-color: #f4f7f6; }
    .enterprise-card {
        background: #ffffff; border: none; border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.04); margin-bottom: 2rem; overflow: hidden;
    }
    .enterprise-card-header {
        background-color: #ffffff; border-bottom: 1px solid #edf2f9; padding: 1.5rem 1.5rem 1rem 1.5rem;
    }
    .enterprise-card-header h6 {
        color: #2c3e50; font-weight: 700; font-size: 1.1rem; letter-spacing: 0.02rem; margin: 0;
    }
    .enterprise-card-body { padding: 1.5rem; }
    .form-label-custom {
        font-size: 0.8rem; font-weight: 600; color: #6e84a3; text-transform: uppercase; letter-spacing: 0.05rem; margin-bottom: 0.5rem; display: block;
    }
    .form-control-custom {
        background-color: #f9fbfd; border: 1px solid #d2ddec; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.95rem; color: #12263f; transition: all 0.2s; height: 42px;
    }
    .form-control-custom:focus {
        background-color: #ffffff; border-color: #2c7be5; box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.15); outline: none;
    }
    
    .btn-enterprise-search {
        background-color: #2c7be5; color: #fff; border: none; border-radius: 8px; height: 42px; width: 100%; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;
    }
    .btn-enterprise-search:hover {
        background-color: #1a68d1; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(44, 123, 229, 0.2); cursor: pointer;
    }
    
    .dt-buttons .btn-success { background-color: #00d27a !important; border-color: #00d27a !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; }
    .dt-buttons .btn-success:hover { background-color: #00b368 !important; box-shadow: 0 4px 6px rgba(0, 210, 122, 0.2); }
    .dt-buttons .btn-info { background-color: #39afd1 !important; border-color: #39afd1 !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    .dt-buttons .btn-info:hover { background-color: #2b9cbd !important; box-shadow: 0 4px 6px rgba(57, 175, 209, 0.2); }
    
    .dt-buttons .btn-danger { background-color: #e74a3b !important; border-color: #e74a3b !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    .dt-buttons .btn-danger:hover { background-color: #be2617 !important; box-shadow: 0 4px 6px rgba(231, 74, 59, 0.2); }
    
    .dt-buttons .btn-primary { background-color: #2c7be5 !important; border-color: #2c7be5 !important; color: #fff !important; border-radius: 6px; font-weight: 600; padding: 0.4rem 1rem; margin-left: 8px; }
    .dt-buttons .btn-primary:hover { background-color: #1a68d1 !important; box-shadow: 0 4px 6px rgba(44, 123, 229, 0.2); }
    
    .badge-soft-success { background-color: rgba(0, 210, 122, 0.15); color: #00d27a; font-weight: 700; padding: 0.5em 0.8em; border-radius: 6px; }

    .table-enterprise { border-collapse: separate; border-spacing: 0; width: 100%; }
    .table-enterprise thead th { background-color: #f9fbfd; color: #6e84a3; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #edf2f9; border-top: none; padding: 1rem; white-space: nowrap; }
    .table-enterprise tbody td { padding: 1rem; color: #12263f; font-size: 0.95rem; vertical-align: middle; border-bottom: 1px solid #edf2f9; }
    .table-enterprise tbody tr:hover td { background-color: #f9fbfd; }
    .dataTables_wrapper .dataTables_filter { display: flex; align-items: center; justify-content: flex-end; }
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #d2ddec; border-radius: 6px; padding: 0.4rem; margin-left: 0.5rem; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid mb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800" style="font-weight: 700;">Data Extraction & Reports</h1>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">Filter, analyze, and dynamically export research metrics. Leave dates blank for Full History.</p>
        </div>
    </div>

    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h6><i class="fas fa-filter text-primary mr-2"></i> Report Parameters</h6>
        </div>
        <div class="enterprise-card-body">
            <form id="filterForm">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label-custom">Target Module</label>
                        <select name="repp" id="repp" class="form-control-custom w-100">
                            <option value="all_modules" style="font-weight: bold;">All Modules (Comprehensive History)</option>
                            <option value="tbl_publication">Publication</option>
                            <option value="tbl_researchconducted">Research Conducted</option>
                            <option value="tbl_itelectualprop">Intellectual Property</option>
                            <option value="tbl_paperpresentation">Paper Presentation</option>
                            <option value="tbl_trainingsattended">Trainings Attended</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label-custom">Department Filter</label>
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
                        <label class="form-label-custom">Start Date (Optional)</label>
                        <input type="date" name="from_date" id="from_date" class="form-control-custom w-100">
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <label class="form-label-custom">End Date (Optional)</label>
                        <input type="date" name="to_date" id="to_date" class="form-control-custom w-100">
                    </div>

                    <div class="col-lg-2 col-md-4 mb-3">
                        <button type="button" id="previewBtn" class="btn-enterprise-search" title="Run Query">
                            <i class="fas fa-search"></i>
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

<?php include('includes/footer.php'); ?>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        $('#previewBtn').click();
    });

    $('#previewBtn').click(function() {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var department = $('#department').val();
        var repp = $('#repp').val();

        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            Swal.fire({ icon: 'error', title: 'Invalid Timeline', text: 'The Start Date cannot be chronologically after the End Date.', confirmButtonColor: '#2c7be5' });
            return;
        }

        Swal.fire({
            title: 'Executing Query...', text: 'Extracting data securely from the server.', allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: 'report.php',
            type: 'POST',
            data: { action: 'preview_report', from_date: fromDate, to_date: toDate, department: department, repp: repp },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                } else {
                    if ($.fn.DataTable.isDataTable('#previewTable')) {
                        $('#previewTable').DataTable().destroy();
                    }
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
                            var deptIndex = -1;
                            var modIndex = -1;
                            
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
                                { 
                                    extend: 'excelHtml5', 
                                    text: '<i class="fas fa-file-excel mr-1"></i> Excel', 
                                    className: 'btn btn-success btn-sm', 
                                    title: 'Data_Extraction_Report',
                                    customizeData: exportCustomize 
                                },
                                { 
                                    extend: 'csvHtml5', 
                                    text: '<i class="fas fa-file-csv mr-1"></i> CSV', 
                                    className: 'btn btn-info btn-sm', 
                                    title: 'Data_Extraction_Report',
                                    customizeData: exportCustomize 
                                },
                                {
                                    text: '<i class="fas fa-file-word mr-1"></i> Word',
                                    className: 'btn btn-primary btn-sm',
                                    action: function ( e, dt, node, config ) {
                                        var p_fromDate = $('#from_date').val();
                                        var p_toDate = $('#to_date').val();
                                        var p_department = encodeURIComponent($('#department').val());
                                        var p_repp = $('#repp').val();
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ 
                                            title: 'Compiling Document...', 
                                            text: 'Preparing standard Word format.', 
                                            icon: 'info', 
                                            showConfirmButton: false, 
                                            allowOutsideClick: false, 
                                            didOpen: () => { Swal.showLoading(); } 
                                        });

                                        setTimeout(function() {
                                            window.open('print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&repp=' + p_repp + '&format=word&_cb=' + cb, '_blank');
                                            swalLoading.close();
                                        }, 800);
                                    }
                                },
                                {
                                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                                    className: 'btn btn-danger btn-sm',
                                    action: function ( e, dt, node, config ) {
                                        var p_fromDate = $('#from_date').val();
                                        var p_toDate = $('#to_date').val();
                                        var p_department = encodeURIComponent($('#department').val());
                                        var p_repp = $('#repp').val();
                                        var cb = new Date().getTime(); 

                                        const swalLoading = Swal.fire({ 
                                            title: 'Compiling Document...', 
                                            text: 'Preparing standard PDF format.', 
                                            icon: 'info', 
                                            showConfirmButton: false, 
                                            allowOutsideClick: false, 
                                            didOpen: () => { Swal.showLoading(); } 
                                        });

                                        setTimeout(function() {
                                            window.open('print_journal.php?from_date=' + p_fromDate + '&to_date=' + p_toDate + '&department=' + p_department + '&repp=' + p_repp + '&format=pdf&_cb=' + cb, '_blank');
                                            swalLoading.close();
                                        }, 800);
                                    }
                                }
                            ],
                            initComplete: function () {
                                var api = this.api();
                                var selectedModule = $('#repp').val();
                                
                                if (selectedModule === 'all_modules') {
                                    var colIndex = -1;
                                    api.columns().every(function() {
                                        if (this.header().textContent.trim() === 'Module Category') {
                                            colIndex = this.index();
                                        }
                                    });

                                    if (colIndex !== -1) {
                                        var column = api.column(colIndex);
                                        
                                        var selectWrapper = $('<div class="mr-3"></div>');
                                        var select = $('<select class="form-control-custom" style="height: 34px; padding: 0.2rem 1rem; font-size: 0.85rem; width: auto; font-weight: bold;"><option value="">All Categories (View All)</option></select>')
                                            .appendTo(selectWrapper)
                                            .on('change', function () {
                                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                                            });

                                        selectWrapper.prependTo('.dataTables_filter');
                                        
                                        column.data().unique().sort().each(function (d, j) {
                                            if (d) { select.append('<option value="' + d + '">' + d + '</option>'); }
                                        });
                                    }
                                }
                            }
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