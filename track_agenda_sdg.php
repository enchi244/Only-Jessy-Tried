<?php
// track_agenda_sdg.php
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

include('includes/header.php');

// Connect to DB securely
$conn = new mysqli("localhost", "root", "", "rms");
$conn->set_charset("utf8mb4");

// Fetch all data once to populate both tables efficiently
$query = "
    SELECT r.id, r.title, r.research_agenda_cluster, r.sdgs, r.stat, 
           d.firstName, d.familyName, d.department 
    FROM tbl_researchconducted r 
    JOIN tbl_researchdata d ON r.researcherID = d.id 
    ORDER BY r.id DESC
";
$result = $conn->query($query);
$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
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
    .enterprise-card-body { padding: 1.5rem; }
    
    .nav-tabs .nav-link { font-weight: 600; color: #5a5c69; border: none; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; background: transparent; }
    .table-container { background: #fff; padding: 1.5rem; border-radius: 0 0.35rem 0.35rem 0.35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    
    .filter-wrapper { background-color: #f8f9fc; padding: 1rem 1.5rem; border-radius: 0.35rem; border: 1px solid #e3e6f0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px; }
    .filter-wrapper label { margin: 0; font-weight: 700; color: #4e73df; white-space: nowrap; }
    .filter-wrapper select { flex-grow: 1; max-width: 400px; border-radius: 8px; border: 1px solid #d1d3e2; padding: 0.4rem 1rem; color: #5a5c69; outline: none; }
    .filter-wrapper select:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    
    .form-label-custom { font-size: 0.8rem; font-weight: 700; color: #4e73df; text-transform: uppercase; letter-spacing: 0.05rem; margin-bottom: 0.5rem; display: block; }
    .form-control-custom { background-color: #ffffff; border: 1px solid #d1d3e2; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.95rem; color: #5a5c69; width: 100%; outline: none; transition: border-color 0.2s; max-width: 300px; }
    .form-control-custom:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    
    .table-enterprise { border-collapse: separate; border-spacing: 0; width: 100%; }
    .table-enterprise thead th { background-color: #f9fbfd; color: #6e84a3; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #edf2f9; border-top: none; padding: 1rem; white-space: nowrap; }
    .table-enterprise tbody td { padding: 1rem; color: #12263f; font-size: 0.95rem; vertical-align: middle; border-bottom: 1px solid #edf2f9; }
    .badge-agenda { background-color: rgba(54, 185, 204, 0.15); color: #36b9cc; font-weight: 700; padding: 0.4em 0.8em; border-radius: 6px; }
    .badge-sdg { background-color: rgba(28, 200, 138, 0.15); color: #1cc88a; font-weight: 700; padding: 0.4em 0.8em; border-radius: 6px; }
</style>

<div class="container-fluid mb-5">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800" style="font-weight: 700;">Agenda & SDG Tracker</h1>
            <p class="mb-0 text-muted">Isolate and extract research metrics by College, Agenda Cluster, and SDG.</p>
        </div>
    </div>

    <div class="filter-wrapper shadow-sm">
        <label for="globalCollegeFilter"><i class="fas fa-filter mr-2"></i>Filter by College:</label>
        <select id="globalCollegeFilter">
            <option value="">All Colleges & Departments (View All)</option>
            <?php
            $dept_query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
            $dept_res = $conn->query($dept_query);
            while($d = $dept_res->fetch_assoc()){
                echo '<option value="'.htmlspecialchars($d['category_name']).'">'.htmlspecialchars($d['category_name']).'</option>';
            }
            ?>
        </select>
    </div>

    <ul class="nav nav-tabs" id="docTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="agenda-tab" data-toggle="tab" href="#agenda-content" role="tab"><i class="fas fa-layer-group mr-2"></i>By Agenda Cluster</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="sdg-tab" data-toggle="tab" href="#sdg-content" role="tab"><i class="fas fa-leaf mr-2"></i>By Sustainable Development Goal (SDG)</a>
        </li>
    </ul>

    <div class="tab-content table-container">
        
        <div class="tab-pane fade show active" id="agenda-content" role="tabpanel">
            <div class="mb-3 d-flex align-items-center">
                <label class="form-label-custom mr-3 mb-0">Isolate Agenda:</label>
                <select id="agendaFilter" class="form-control-custom">
                    <option value="">All Agenda Clusters (View All)</option>
                    </select>
            </div>
            
            <div class="table-responsive">
                <table class="table-enterprise table-hover" id="agendaTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Project Title</th>
                            <th width="20%">Lead Proponent</th>
                            <th width="20%">Department</th>
                            <th width="15%">Agenda Cluster</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): 
                            $title = htmlspecialchars($row['title'] ?: 'Untitled');
                            $name = htmlspecialchars($row['firstName'] . ' ' . $row['familyName']);
                            $dept = htmlspecialchars($row['department'] ?: 'N/A');
                            $agenda = htmlspecialchars(trim($row['research_agenda_cluster'] ?: 'Unassigned'));
                            $status = htmlspecialchars($row['stat'] ?: 'Unknown');
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class='font-weight-bold'><?= $title ?></td>
                                <td><?= $name ?></td>
                                <td><?= $dept ?></td>
                                <td><span class='badge-agenda'><?= $agenda ?></span></td>
                                <td><span class='badge badge-secondary'><?= $status ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="sdg-content" role="tabpanel">
            <div class="mb-3 d-flex align-items-center">
                <label class="form-label-custom mr-3 mb-0 text-success">Isolate SDG:</label>
                <select id="sdgFilter" class="form-control-custom" style="border-color: #1cc88a;">
                    <option value="">All SDGs (View All)</option>
                    </select>
            </div>
            
            <div class="table-responsive">
                <table class="table-enterprise table-hover" id="sdgTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Project Title</th>
                            <th width="20%">Lead Proponent</th>
                            <th width="20%">Department</th>
                            <th width="15%">SDG Target</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): 
                            $title = htmlspecialchars($row['title'] ?: 'Untitled');
                            $name = htmlspecialchars($row['firstName'] . ' ' . $row['familyName']);
                            $dept = htmlspecialchars($row['department'] ?: 'N/A');
                            $sdg = htmlspecialchars(trim($row['sdgs'] ?: 'Unassigned'));
                            $status = htmlspecialchars($row['stat'] ?: 'Unknown');
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class='font-weight-bold'><?= $title ?></td>
                                <td><?= $name ?></td>
                                <td><?= $dept ?></td>
                                <td><span class='badge-sdg'><?= $sdg ?></span></td>
                                <td><span class='badge badge-secondary'><?= $status ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<?php 
$conn->close();
include('includes/footer.php'); 
?>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    
    // Initialize DataTables
    var dtConfig = {
        pageLength: 25,
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    };

    // Table 1: Agenda 
    var tableAgenda = $('#agendaTable').DataTable($.extend(true, {}, dtConfig, {
        buttons: [{ extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Export Agenda Data', className: 'btn btn-info btn-sm mb-3', title: 'Research_Agenda_Report' }],
        initComplete: function () {
            this.api().column(4).data().unique().sort().each(function (d, j) {
                var cleanText = $('<div>' + d + '</div>').text().trim();
                if(cleanText && cleanText !== 'Unassigned') {
                    $('#agendaFilter').append('<option value="' + cleanText + '">' + cleanText + '</option>');
                }
            });
        }
    }));

    // Table 2: SDG
    var tableSdg = $('#sdgTable').DataTable($.extend(true, {}, dtConfig, {
        buttons: [{ extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Export SDG Data', className: 'btn btn-success btn-sm mb-3', title: 'SDG_Report' }],
        initComplete: function () {
            this.api().column(4).data().unique().sort().each(function (d, j) {
                var cleanText = $('<div>' + d + '</div>').text().trim();
                if(cleanText && cleanText !== 'Unassigned') {
                    $('#sdgFilter').append('<option value="' + cleanText + '">' + cleanText + '</option>');
                }
            });
        }
    }));

    // ==========================================
    // MAGICAL COLLEGE/DEPARTMENT FILTER LOGIC
    // ==========================================
    $('#globalCollegeFilter').on('change', function() {
        var searchVal = $(this).val();
        var regex = searchVal ? '^' + $.fn.dataTable.util.escapeRegex(searchVal) + '$' : '';
        
        // Target Column 3 (Department) in BOTH tables simultaneously
        tableAgenda.column(3).search(regex, true, false).draw();
        tableSdg.column(3).search(regex, true, false).draw();
    });

    // Handle Agenda Specific Selection (Column 4)
    $('#agendaFilter').on('change', function() {
        var searchVal = $(this).val();
        tableAgenda.column(4).search(searchVal ? searchVal : '', true, false).draw();
    });

    // Handle SDG Specific Selection (Column 4)
    $('#sdgFilter').on('change', function() {
        var searchVal = $(this).val();
        tableSdg.column(4).search(searchVal ? searchVal : '', true, false).draw();
    });

});
</script>