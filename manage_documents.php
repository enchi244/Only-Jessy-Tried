<?php
// manage_documents.php
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
// BACKGROUND AJAX HANDLER FOR UPLOADING FILES
// ==============================================================================
if (isset($_POST['action']) && $_POST['action'] == 'upload_document') {
    header('Content-Type: application/json');
    
    $table_name = $_POST['table_name'];
    $record_id = intval($_POST['record_id']);
    $doc_type = $_POST['doc_type']; // 'so' or 'moa'
    
    // Security: Only allow specific tables
    $allowed_tables = ['tbl_researchdata', 'tbl_publication', 'tbl_researchconducted', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended', 'tbl_extension_project_conducted'];
    if (!in_array($table_name, $allowed_tables)) {
        echo json_encode(['error' => 'Invalid table reference.']);
        exit;
    }

    // Ensure the uploads directory exists
    $upload_dir = 'uploads/documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        
        if (!in_array($file_extension, $allowed_ext)) {
            echo json_encode(['error' => 'Invalid file format. Please upload PDF, Word, or Image files.']);
            exit;
        }

        // Generate a clean, unique file name
        $new_file_name = $doc_type . '_' . time() . '_' . $record_id . '.' . $file_extension;
        $target_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target_path)) {
            $conn = new mysqli("localhost", "root", "", "rms");
            $column_to_update = ($doc_type === 'so') ? 'so_file' : 'moa_file';
            
            $stmt = $conn->prepare("UPDATE {$table_name} SET {$column_to_update} = ? WHERE id = ?");
            $stmt->bind_param("si", $new_file_name, $record_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'file_url' => $target_path]);
            } else {
                echo json_encode(['error' => 'Database update failed.']);
            }
            $stmt->close();
            $conn->close();
        } else {
            echo json_encode(['error' => 'Failed to move uploaded file.']);
        }
    } else {
        echo json_encode(['error' => 'No valid file was uploaded.']);
    }
    exit;
}
// ==============================================================================

include('includes/header.php');

// Connect to DB to fetch data for our tables
$conn = new mysqli("localhost", "root", "", "rms");
$conn->set_charset("utf8mb4");

?>

<style>
    .doc-badge { font-size: 0.85rem; padding: 0.4em 0.6em; border-radius: 6px; }
    .nav-tabs .nav-link { font-weight: 600; color: #5a5c69; border: none; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; background: transparent; }
    .table-container { background: #fff; padding: 1.5rem; border-radius: 0 0.35rem 0.35rem 0.35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    
    .filter-wrapper { background-color: #f8f9fc; padding: 1rem 1.5rem; border-radius: 0.35rem; border: 1px solid #e3e6f0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px; }
    .filter-wrapper label { margin: 0; font-weight: 700; color: #4e73df; white-space: nowrap; }
    .filter-wrapper select { flex-grow: 1; max-width: 400px; border-radius: 8px; border: 1px solid #d1d3e2; padding: 0.4rem 1rem; color: #5a5c69; outline: none; }
    .filter-wrapper select:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
</style>

<div class="container-fluid mb-5">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800" style="font-weight: 700;">Document Management</h1>
            <p class="mb-0 text-muted">Track and upload Special Orders (SO) and Memorandums of Agreement (MOA).</p>
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
            <a class="nav-link active" id="so-tab" data-toggle="tab" href="#so-content" role="tab"><i class="fas fa-user-tie mr-2"></i>Researchers (SO Files)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="moa-tab" data-toggle="tab" href="#moa-content" role="tab"><i class="fas fa-project-diagram mr-2"></i>Research Projects (MOA Files)</a>
        </li>
    </ul>

    <div class="tab-content table-container">
        
        <div class="tab-pane fade show active" id="so-content" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="soTable">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Researcher Name</th>
                            <th width="30%">Department</th>
                            <th width="15%" class="text-center">SO Status</th>
                            <th width="20%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res_query = "SELECT id, firstName, familyName, department, so_file FROM tbl_researchdata ORDER BY id DESC";
                        $res_result = $conn->query($res_query);
                        while ($row = $res_result->fetch_assoc()) {
                            $name = htmlspecialchars($row['firstName'] . ' ' . $row['familyName']);
                            $dept = htmlspecialchars($row['department']);
                            $has_so = !empty($row['so_file']);
                            
                            $status_html = $has_so 
                                ? '<span class="badge badge-success doc-badge"><i class="fas fa-check-circle mr-1"></i>Attached</span>' 
                                : '<span class="badge badge-danger doc-badge"><i class="fas fa-times-circle mr-1"></i>Missing</span>';
                            
                            $action_html = $has_so
                                ? '<a href="uploads/documents/' . htmlspecialchars($row['so_file']) . '" target="_blank" class="btn btn-sm btn-info shadow-sm mr-1"><i class="fas fa-eye"></i> View</a>' .
                                  '<button class="btn btn-sm btn-warning shadow-sm upload-btn" data-type="so" data-table="tbl_researchdata" data-id="'.$row['id'].'" data-name="'.$name.'"><i class="fas fa-upload"></i> Replace</button>'
                                : '<button class="btn btn-sm btn-primary shadow-sm upload-btn" data-type="so" data-table="tbl_researchdata" data-id="'.$row['id'].'" data-name="'.$name.'"><i class="fas fa-upload"></i> Upload SO</button>';

                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td class='font-weight-bold text-gray-800'>{$name}</td>
                                    <td>{$dept}</td>
                                    <td class='text-center'>{$status_html}</td>
                                    <td class='text-center'>{$action_html}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="moa-content" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="moaTable">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th width="15%">Module Type</th>
                            <th width="30%">Project Title</th>
                            <th width="15%">Lead Proponent</th>
                            <th width="20%">Department</th>
                            <th width="10%" class="text-center">MOA Status</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Massive UNION query to pull all projects into one beautiful master view (NOW INCLUDING DEPARTMENT)
                        $moa_query = "
                            SELECT 'Publication' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_publication' as tbl_name FROM tbl_publication r JOIN tbl_researchdata d ON r.researcherID = d.id UNION ALL
                            SELECT 'Research Conducted' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_researchconducted' as tbl_name FROM tbl_researchconducted r JOIN tbl_researchdata d ON r.researcherID = d.id UNION ALL
                            SELECT 'Intellectual Property' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_itelectualprop' as tbl_name FROM tbl_itelectualprop r JOIN tbl_researchdata d ON r.researcherID = d.id UNION ALL
                            SELECT 'Paper Presentation' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_paperpresentation' as tbl_name FROM tbl_paperpresentation r JOIN tbl_researchdata d ON r.researcherID = d.id UNION ALL
                            SELECT 'Training Attended' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_trainingsattended' as tbl_name FROM tbl_trainingsattended r JOIN tbl_researchdata d ON r.researcherID = d.id UNION ALL
                            SELECT 'Extension Project' as module, r.id, r.title, r.moa_file, d.firstName, d.familyName, d.department, 'tbl_extension_project_conducted' as tbl_name FROM tbl_extension_project_conducted r JOIN tbl_researchdata d ON r.researcherID = d.id
                        ";
                        
                        $moa_result = $conn->query($moa_query);
                        while ($row = $moa_result->fetch_assoc()) {
                            $title = htmlspecialchars($row['title']);
                            if(empty($title)) $title = "<i>Untitled Record</i>";
                            $name = htmlspecialchars($row['firstName'] . ' ' . $row['familyName']);
                            $dept = htmlspecialchars($row['department']);
                            $has_moa = !empty($row['moa_file']);
                            
                            $status_html = $has_moa 
                                ? '<span class="badge badge-success doc-badge"><i class="fas fa-check-circle mr-1"></i>Attached</span>' 
                                : '<span class="badge badge-danger doc-badge"><i class="fas fa-times-circle mr-1"></i>Missing</span>';
                            
                            $action_html = $has_moa
                                ? '<a href="uploads/documents/' . htmlspecialchars($row['moa_file']) . '" target="_blank" class="btn btn-sm btn-info shadow-sm mr-1"><i class="fas fa-eye"></i></a>' .
                                  '<button class="btn btn-sm btn-warning shadow-sm upload-btn" data-type="moa" data-table="'.$row['tbl_name'].'" data-id="'.$row['id'].'" data-name="Project: '.$title.'"><i class="fas fa-upload"></i></button>'
                                : '<button class="btn btn-sm btn-primary shadow-sm upload-btn" data-type="moa" data-table="'.$row['tbl_name'].'" data-id="'.$row['id'].'" data-name="Project: '.$title.'"><i class="fas fa-upload"></i> Upload</button>';

                            echo "<tr>
                                    <td><span class='badge badge-secondary'>{$row['module']}</span></td>
                                    <td class='font-weight-bold text-gray-800'>{$title}</td>
                                    <td>{$name}</td>
                                    <td>{$dept}</td>
                                    <td class='text-center'>{$status_html}</td>
                                    <td class='text-center'>{$action_html}</td>
                                  </tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadModalLabel">Upload Document</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <p id="uploadTargetName" class="font-weight-bold text-dark mb-4 border-bottom pb-2"></p>
                    
                    <input type="hidden" name="action" value="upload_document">
                    <input type="hidden" name="table_name" id="modalTable" value="">
                    <input type="hidden" name="record_id" id="modalId" value="">
                    <input type="hidden" name="doc_type" id="modalDocType" value="">
                    
                    <div class="form-group">
                        <label>Select File (PDF, DOCX, JPG, PNG)</label>
                        <input type="file" name="document_file" class="form-control p-1" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-upload-alt mr-1"></i> Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables for clean sorting and searching
    var tableSO = $('#soTable').DataTable({ pageLength: 25 });
    var tableMOA = $('#moaTable').DataTable({ pageLength: 25 });

    // ==========================================
    // MAGICAL COLLEGE/DEPARTMENT FILTER LOGIC
    // ==========================================
    $('#globalCollegeFilter').on('change', function() {
        var selectedCollege = $(this).val();
        
        // Exact match regex so "College of Science" doesn't accidentally trigger "College of Science and Mathematics"
        var regex = selectedCollege ? '^' + $.fn.dataTable.util.escapeRegex(selectedCollege) + '$' : '';
        
        // Apply filter to Column 2 (Department) of SO Table
        tableSO.column(2).search(regex, true, false).draw();
        
        // Apply filter to Column 3 (Department) of MOA Table
        tableMOA.column(3).search(regex, true, false).draw();
    });

    // Open Upload Modal
    $(document).on('click', '.upload-btn', function() {
        var docType = $(this).data('type');
        var tableName = $(this).data('table');
        var recordId = $(this).data('id');
        var targetName = $(this).data('name');
        
        var modalTitle = (docType === 'so') ? 'Upload Special Order (SO)' : 'Upload Memorandum of Agreement (MOA)';
        
        $('#uploadModalLabel').text(modalTitle);
        $('#uploadTargetName').html('<i class="fas fa-info-circle text-primary mr-2"></i> Target: <span class="text-primary">' + targetName + '</span>');
        
        $('#modalDocType').val(docType);
        $('#modalTable').val(tableName);
        $('#modalId').val(recordId);
        
        $('#uploadForm')[0].reset();
        $('#uploadModal').modal('show');
    });

    // Handle AJAX Form Submission safely
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while the document is securely saved.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: 'manage_documents.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#uploadModal').modal('hide');
                    Swal.fire('Success!', 'Document successfully attached. Reports will automatically update.', 'success').then(() => {
                        location.reload(); // Reload to refresh table statuses
                    });
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Connection Error', 'Failed to communicate with the server.', 'error');
            }
        });
    });
});
</script>