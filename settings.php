<?php
require_once 'core/config.php';
include('core/rms.php'); $object = new rms();
include 'includes/header.php';
?>

<style>
    /* Makes the card content scrollable without scrolling the whole page */
    .scrollable-card-body {
        max-height: 70vh; /* 70vh means 70% of the screen height */
        overflow-y: auto; /* Adds a vertical scrollbar if needed */
        overflow-x: hidden;
    }
    
    /* Optional: Makes the scrollbar look a bit thinner and cleaner */
    .scrollable-card-body::-webkit-scrollbar { width: 6px; }
    .scrollable-card-body::-webkit-scrollbar-track { background: #f1f1f1; }
    .scrollable-card-body::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    .scrollable-card-body::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="colleges-tab" data-toggle="tab" href="#colleges" role="tab" aria-controls="colleges" aria-selected="true">
                        <i class="fas fa-university fa-sm fa-fw mr-2 text-gray-400"></i>Colleges
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="disciplines-tab" data-toggle="tab" href="#disciplines" role="tab" aria-controls="disciplines" aria-selected="false">
                        <i class="fas fa-book fa-sm fa-fw mr-2 text-gray-400"></i>Disciplines
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sdg-tab" data-toggle="tab" href="#sdg" role="tab" aria-controls="sdg" aria-selected="false">
                        <i class="fas fa-globe fa-sm fa-fw mr-2 text-gray-400"></i>Agenda SDG Tracker
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="archive-tab" data-toggle="tab" href="#archive" role="tab" aria-controls="archive" aria-selected="false">
                        <i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i>Recycle Bin
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body scrollable-card-body">
            <div class="tab-content" id="settingsTabsContent">
                
                <div class="tab-pane fade show active" id="colleges" role="tabpanel" aria-labelledby="colleges-tab">
                    <?php include 'modules/colleges/colleges.php'; ?>
                </div>

                <div class="tab-pane fade" id="disciplines" role="tabpanel" aria-labelledby="disciplines-tab">
                    <?php include 'modules/disciplines/disciplines.php'; ?>
                </div>

                <div class="tab-pane fade" id="sdg" role="tabpanel" aria-labelledby="sdg-tab">
                    <?php include 'modules/trackers/track_agenda_sdg.php'; ?>
                </div>

                <div class="tab-pane fade" id="archive" role="tabpanel" aria-labelledby="archive-tab">
                    <?php include 'modules/researchers/archive.php'; ?>
                </div>

            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function(){

    // ==========================================
    // 1. COLLEGES SCRIPT
    // ==========================================
    var collegeTable = $('#category_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : {
            url:"modules/colleges/college_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[2], "orderable":false } ],
    });

    $('#add_category').click(function(){
        $('#category_form')[0].reset();
        $('#category_form').parsley().reset();
        $('#modal_title').text('Add Data');
        $('#action').val('Add');
        $('#submit_button').val('Add');
        $('#categoryModal').modal('show');
        $('#form_message').html('');
    });

    $('#category_form').parsley();

    $('#category_form').on('submit', function(event){
        event.preventDefault();
        if($('#category_form').parsley().isValid()) {       
            $.ajax({
                url:"modules/colleges/college_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                beforeSend:function() {
                    $('#submit_button').attr('disabled', 'disabled');
                    $('#submit_button').val('wait...');
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message').html(data.error);
                        $('#submit_button').val('Add');
                    } else {
                        $('#categoryModal').modal('hide');
                        $('#message').html(data.success);
                        collegeTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    $('#category_table tbody').on('click', '.edit_button', function(){
        var category_id = $(this).data('id');
        $('#category_form').parsley().reset();
        $('#form_message').html('');
        $.ajax({
            url:"modules/colleges/college_action.php",
            method:"POST",
            data:{category_id:category_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#category_name').val(data.category_name);
                $('#modal_title').text('Edit Data');
                $('#action').val('Edit');
                $('#submit_button').val('Edit');
                $('#categoryModal').modal('show');
                $('#hidden_id').val(category_id);
            }
        })
    });

    $('#category_table tbody').on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = 'Enable';
        if(status == 'Enable') { next_status = 'Disable'; }
        if(confirm("Are you sure you want to "+next_status+" it?")) {
            $.ajax({
                url:"modules/colleges/college_action.php",
                method:"POST",
                data:{id:id, action:'change_status', status:status, next_status:next_status},
                success:function(data) {
                    $('#message').html(data);
                    collegeTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            })
        }
    });

    $('#category_table tbody').on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Are you sure you want to remove it?")) {
            $.ajax({
                url:"modules/colleges/college_action.php",
                method:"POST",
                data:{id:id, action:'delete'},
                success:function(data) {
                    $('#message').html(data);
                    collegeTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            })
        }
    });


    // ==========================================
    // 2. DISCIPLINES SCRIPT
    // ==========================================
    var disciplineTable = $('#discipline_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : {
            url:"modules/disciplines/discipline_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[2], "orderable":false } ]
    });

    $('#add_discipline').click(function(){
        $('#discipline_form')[0].reset();
        $('#discipline_form').parsley().reset();
        $('#discipline_modal_title').text('Add Data');
        $('#discipline_action').val('Add');
        $('#discipline_submit_button').val('Add');
        $('#disciplineModal').modal('show');
        $('#discipline_form_message').html('');
    });

    $('#discipline_form').parsley();

    $('#discipline_form').on('submit', function(event){
        event.preventDefault();
        if($('#discipline_form').parsley().isValid()) {       
            $.ajax({
                url:"modules/disciplines/discipline_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                beforeSend:function() {
                    $('#discipline_submit_button').attr('disabled', 'disabled').val('wait...');
                },
                success:function(data) {
                    $('#discipline_submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#discipline_form_message').html(data.error);
                        $('#discipline_submit_button').val('Add');
                    } else {
                        $('#disciplineModal').modal('hide');
                        $('#discipline_message').html(data.success);
                        disciplineTable.ajax.reload();
                        setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    $('#discipline_table tbody').on('click', '.edit_button', function(){
        var category_id = $(this).data('id');
        $('#discipline_form').parsley().reset();
        $('#discipline_form_message').html('');
        $.ajax({
            url:"modules/disciplines/discipline_action.php",
            method:"POST",
            data:{category_id:category_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#discipline_name').val(data.category_name);
                $('#discipline_modal_title').text('Edit Data');
                $('#discipline_action').val('Edit');
                $('#discipline_submit_button').val('Edit');
                $('#disciplineModal').modal('show');
                $('#discipline_hidden_id').val(category_id);
            }
        })
    });

    $('#discipline_table tbody').on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Are you sure you want to "+next_status+" it?")) {
            $.ajax({
                url:"modules/disciplines/discipline_action.php",
                method:"POST",
                data:{id:id, action:'change_status', status:status, next_status:next_status},
                success:function(data) {
                    $('#discipline_message').html(data);
                    disciplineTable.ajax.reload();
                    setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
                }
            })
        }
    });

    $('#discipline_table tbody').on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Are you sure you want to remove it?")) {
            $.ajax({
                url:"modules/disciplines/discipline_action.php",
                method:"POST",
                data:{id:id, action:'delete'},
                success:function(data) {
                    $('#discipline_message').html(data);
                    disciplineTable.ajax.reload();
                    setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
                }
            })
        }
    });


    // ==========================================
    // 3. ARCHIVE SCRIPT
    // ==========================================
    var archiveTable = $('#archive_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "modules/researchers/actions/archive_action.php",
            type: "POST",
            data: function(d) {
                d.action = 'fetch_all';
                d.target_module = $('#module_filter').val();
            }
        },
        "columnDefs": [ { "targets": [2], "orderable": false } ]
    });

    $('#module_filter').change(function(){
        archiveTable.ajax.reload();
    });

    $('#archive_table tbody').on('click', '.restore_btn', function() {
        var id = $(this).data('id');
        var target_module = $('#module_filter').val();
        Swal.fire({
            title: 'Restore Record?', text: 'This will be moved back to the Active system.', icon: 'info', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Yes, Restore'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "modules/researchers/actions/archive_action.php",
                    type: "POST", 
                    data: { id: id, target_module: target_module, action: 'restore' },
                    success: function(data) {
                        Swal.fire('Restored!', 'The record is active again.', 'success');
                        archiveTable.ajax.reload();
                    }
                });
            }
        });
    });

    $('#archive_table tbody').on('click', '.perma_delete_btn', function() {
        var id = $(this).data('id');
        var target_module = $('#module_filter').val();
        Swal.fire({
            title: 'Permanent Deletion!', text: 'This will destroy the record forever. This cannot be undone!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', confirmButtonText: 'Permanently Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "modules/researchers/actions/archive_action.php",
                    type: "POST", 
                    data: { id: id, target_module: target_module, action: 'perma_delete' },
                    success: function(data) {
                        Swal.fire('Deleted!', 'The record has been permanently destroyed.', 'success');
                        archiveTable.ajax.reload();
                    }
                });
            }
        });
    });

    // ==========================================
    // 4. AGENDA & SDG TRACKER SCRIPT
    // ==========================================
    var dtConfig = {
        pageLength: 25,
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    };

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

    $('#globalCollegeFilter').on('change', function() {
        var searchVal = $(this).val();
        var regex = searchVal ? '^' + $.fn.dataTable.util.escapeRegex(searchVal) + '$' : '';
        tableAgenda.column(3).search(regex, true, false).draw();
        tableSdg.column(3).search(regex, true, false).draw();
    });

    $('#agendaFilter').on('change', function() {
        var searchVal = $(this).val();
        tableAgenda.column(4).search(searchVal ? searchVal : '', true, false).draw();
    });

    $('#sdgFilter').on('change', function() {
        var searchVal = $(this).val();
        tableSdg.column(4).search(searchVal ? searchVal : '', true, false).draw();
    });


    // ==========================================
    // 5. TAB RESIZING FIX
    // ==========================================
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

});
</script>