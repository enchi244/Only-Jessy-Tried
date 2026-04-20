<?php
// modules/researchers/archive.php
include('../../core/rms.php');
$object = new rms();

if(!$object->is_login() || !$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
    exit;
}

include('../../includes/header.php');
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-trash-alt text-danger mr-2"></i> Universal Recycle Bin</h1>
        <a href="researcher.php" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to System</a>
    </div>

    <div class="card shadow mb-4 border-left-danger">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-danger">Archived</h6>
            
            <select id="module_filter" class="form-control form-control-sm border-danger text-danger" style="width: auto; font-weight:bold;">
                <option value="tbl_researchdata">Researcher Profiles</option>
                <option value="tbl_researchconducted">Research Conducted</option>
                <option value="tbl_publication">Publications</option>
                <option value="tbl_itelectualprop">Intellectual Property</option>
                <option value="tbl_paperpresentation">Paper Presentations</option>
                <option value="tbl_extension_project_conducted">Extension Projects</option>
                <option value="tbl_trainingsattended">Trainings Attended</option>
            </select>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="archive_table" width="100%" cellspacing="0">
                    <thead class="bg-gray-200 text-gray-800">
                        <tr>
                            <th width="20%">Record Type</th>
                            <th width="65%">Name / Title</th>
                            <th width="15%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    
    var dataTable = $('#archive_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/archive_action.php",
            type: "POST",
            data: function(d) {
                d.action = 'fetch_all';
                d.target_module = $('#module_filter').val(); // Send selected module to server
            }
        },
        "columnDefs": [ { "targets": [2], "orderable": false } ]
    });

    // Reload table when dropdown changes
    $('#module_filter').change(function(){
        dataTable.ajax.reload();
    });

    $(document).on('click', '.restore_btn', function() {
        var id = $(this).data('id');
        var target_module = $('#module_filter').val();

        Swal.fire({
            title: 'Restore Record?', text: 'This will be moved back to the Active system.', icon: 'info', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Yes, Restore'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "actions/archive_action.php", type: "POST", data: { id: id, target_module: target_module, action: 'restore' },
                    success: function(data) {
                        Swal.fire('Restored!', 'The record is active again.', 'success');
                        dataTable.ajax.reload();
                    }
                });
            }
        });
    });

    $(document).on('click', '.perma_delete_btn', function() {
        var id = $(this).data('id');
        var target_module = $('#module_filter').val();

        Swal.fire({
            title: 'Permanent Deletion!', text: 'This will destroy the record forever. This cannot be undone!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', confirmButtonText: 'Permanently Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "actions/archive_action.php", type: "POST", data: { id: id, target_module: target_module, action: 'perma_delete' },
                    success: function(data) {
                        Swal.fire('Deleted!', 'The record has been permanently destroyed.', 'success');
                        dataTable.ajax.reload();
                    }
                });
            }
        });
    });
});
</script>