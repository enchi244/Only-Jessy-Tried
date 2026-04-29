$(document).ready(function() {
    $('.modal').appendTo('body');
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    // --- MASTER TABLE TOGGLE LOGIC ---
    var dataTable;

    $('.master-toggles .btn').click(function() {
        $('.master-toggles .btn').removeClass('active btn-danger pink').addClass('btn-outline-danger');
        $(this).removeClass('btn-outline-danger').addClass('active btn-danger pink');
    });

    $('#btn_view_researchers').click(function() { loadResearcherTable(); });
    $('#btn_view_conducted').click(function() { loadResearchConductedTable(); });
    $('#btn_view_publications').click(function() { loadPublicationTable(); });
    $('#btn_view_ip').click(function() { loadIPTable(); });
    $('#btn_view_pp').click(function() { loadPPTable(); });
    $('#btn_view_tra').click(function() { loadTraTable(); });
    $('#btn_view_epc').click(function() { loadEPCTable(); });
    $('#btn_view_ext').click(function() { loadExtTable(); });

    // 1. Load Researchers Master Table
    function loadResearcherTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-users mr-2"></i>Personnel Roster');
        $('#researcher_table thead').html('<tr><th width="15%">ID</th><th width="25%">Name</th><th width="20%">Department</th><th width="20%">Program</th><th width="10%">Joined</th><th width="10%" class="text-center">Actions</th></tr>');
        dataTable = $('#researcher_table').DataTable({
            "processing": true, "serverSide": true, "order": [],
            "ajax": { 
                url: "actions/researcher_action.php", 
                type: "POST", 
                data: function(d) {
                    d.action = 'fetch';
                    d.filter_rank = $('#filter_rank').val();
                    d.filter_program = $('#filter_program').val();
                }
            },
            "columnDefs": [{ "targets": [0], "orderable": false }],
            "order": [[2, 'asc']], "stateSave": true,
            "drawCallback": function(settings) {
                var api = this.api(); var rows = api.rows({ page: 'all' }).nodes(); var departments = {};
                api.column(2, { page: 'all' }).data().each(function(department, i) {
                    if (department) { if (!departments[department]) { departments[department] = 0; } departments[department]++; }
                });
                var last = null;
                api.column(2, { page: 'all' }).data().each(function(department, i) {
                    if (last !== department) {
                        $(rows).eq(i).before('<tr class="group"><td colspan="6"><i class="fas fa-folder-open mr-2 text-primary"></i><strong class="text-primary">' + department + '</strong> <span class="badge badge-light text-primary ml-2 rounded-pill shadow-sm" style="border: 1px solid #eaecf4;">' + (departments[department] || 0) + ' Members</span></td></tr>');
                    }
                    last = department;
                });
            }
        });
        $('#toggleIDColumn').show(); $('#fab_container').show(); dataTable.column(0).visible(false);
    }

    // 2. Load Research Conducted Master Table
    function loadResearchConductedTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-flask mr-2"></i>All Research Conducted');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="30%">Title</th><th width="15%">Cluster</th><th width="15%">SDG</th><th width="10%">Status</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/researchconducted_action.php", type: "POST", data: { action_researchedconducted: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // 3. Load Publications Master Table
    function loadPublicationTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-book mr-2"></i>All Published Works');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="30%">Title</th><th width="20%">Journal</th><th width="10%">Publication Date</th><th width="10%">ISSN/ISBN</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/publication_action.php", type: "POST", data: { action_publication: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // 4. Load Intellectual Property Master Table
    function loadIPTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { 
            $('#researcher_table').DataTable().clear().destroy(); 
        }
        
        // FIX: Completely wipe the old HTML table so leftover 6-column rows don't confuse DataTables
        $('#researcher_table').empty(); 

        $('#master_table_title').html('<i class="fas fa-lightbulb mr-2"></i>All Intellectual Property');
        
        // FIX: Rebuild BOTH the <thead> and an empty <tbody> 
        $('#researcher_table').html('<thead><tr><th width="25%">Authors</th><th width="35%">Title</th><th width="20%">Type of IP</th><th width="10%">Granted</th><th width="10%" class="text-center">Action</th></tr></thead><tbody></tbody>');
        
        dataTable = $('#researcher_table').DataTable({ 
            "processing": true, 
            "serverSide": true, 
            "order": [], 
            "ajax": { 
                url: "actions/intellectualprop_action.php", 
                type: "POST", 
                data: { action_intellectualprop: 'fetch_all' } 
            }, 
            "columnDefs": [{ "targets": [4], "orderable": false }] 
        });
        
        $('#toggleIDColumn').hide(); 
        $('#fab_container').hide();
    }

    // 5. Load Paper Presentation Master Table
    function loadPPTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-file-alt mr-2"></i>All Paper Presentations');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="30%">Title</th><th width="15%">Conference</th><th width="15%">Venue</th><th width="10%">Date</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/paper_presentation_action.php", type: "POST", data: { action_paper_presentation: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // 6. Load Trainings Master Table
    function loadTraTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-chalkboard-teacher mr-2"></i>All Trainings');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="30%">Title</th><th width="15%">Venue</th><th width="10%">Date</th><th width="15%">Level</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/trainings_attended_action.php", type: "POST", data: { action_training: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // 7. Load Extension Projects Master Table
    function loadEPCTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-project-diagram mr-2"></i>All Extension Projects');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="25%">Title</th><th width="15%">Funding Source</th><th width="20%">Beneficiaries</th><th width="10%">Status</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/extension_project_action.php", type: "POST", data: { action_extension: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // 8. Load Extensions Master Table
    function loadExtTable() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-hands-helping mr-2"></i>All Extensions');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="25%">Title</th><th width="15%">Project Lead</th><th width="15%">Period</th><th width="15%">Budget</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/extension_action.php", type: "POST", data: { action_ext: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
    }

    // Initialize Default view on page load
    if ($('#researcher_table').length > 0) {
        loadResearcherTable();
    }


    // Open Unified Modal for ADDING
    $('#add_researcher').click(function(){
        $('#researcher_form')[0].reset();
        $('#researcher_form').parsley().reset();    
        // No need to set UI here, the modal 'show' listener inside add_researcher.php handles it!
        $('#action').val('Add'); 
        $('#form_message_rm').html('');
        $('#researcherModal').modal('show');
    });

    // Open Unified Modal for EDITING
    $(document).on('click', '.edit_buttona', function(){
        var id = $(this).data('id');
        var editForm = $('#researcher_form');
        
        if (editForm.length > 0) { 
            editForm[0].reset(); 
            var parsleyInstance = editForm.parsley(); 
            if (parsleyInstance) { parsleyInstance.reset(); } 
        }
        $('#form_message_rm').html('');

        $.ajax({
            url: "actions/researcher_action.php",
            method: "POST",
            data: {id: id, action: 'fetch_single'},
            dataType: 'JSON',
            success: function(data) {
                // Populate the unified modal fields (removed 'u' suffix)
                $('#researcherID').val(data.researcherID); 
                $('#familyName').val(data.familyName); 
                $('#firstName').val(data.firstName); 
                $('#middleName').val(data.middleName); 
                $('#Suffix').val(data.Suffix); 
                $('#department').val(data.department); 
                $('#program').val(data.program); 
                $('#academic_rank').val(data.academic_rank); 
                $('#bachelor_degree').val(data.bachelor_degree); 
                $('#bachelor_institution').val(data.bachelor_institution); 
                $('#bachelor_YearGraduated').val(data.bachelor_YearGraduated); 
                $('#masterDegree').val(data.masterDegree); 
                $('#masterInstitution').val(data.masterInstitution); 
                $('#masterYearGraduated').val(data.masterYearGraduated); 
                $('#doctorateDegree').val(data.doctorateDegree); 
                $('#doctorateInstitution').val(data.doctorateInstitution); 
                $('#doctorateYearGraduate').val(data.doctorateYearGraduate); 
                $('#postDegree').val(data.postDegree); 
                $('#postInstitution').val(data.postInstitution); 
                $('#postYearGraduate').val(data.postYearGraduate);
                
                // Set action to Edit so the modal styles itself automatically
                $('#action').val('Edit');
                $('#hidden_id').val(id);
                
                // Show unified modal
                $('#researcherModal').modal('show');
                
                // Load sub-tabs if present in the view
                if (typeof loadResearchConductedTab === "function") { loadResearchConductedTab(id); }
                if (typeof loadPublicationTab === "function") { loadPublicationTab(id); }
                if (typeof loadIntellectualPropTab === "function") { loadIntellectualPropTab(id); }
                if (typeof loadPaperPresentationTab === "function") { loadPaperPresentationTab(id); }
                if (typeof loadTrainingsAttendedTab === "function") { loadTrainingsAttendedTab(id); }
                if (typeof loadExtensionProjectsTab === "function") { loadExtensionProjectsTab(id); }
                if (typeof loadextprotab === "function") { loadextprotab(id); }
            }
        });
    });

    // UNIFIED Form Submit Logic (Handles both Add & Edit)
    $('#researcher_form').parsley();
    $('#researcher_form').on('submit', function(event){
        event.preventDefault();
        
        if($('#researcher_form').parsley().isValid()) {     
            
            var currentAction = $('#action').val();
            
            // Route Add vs Edit to proper files
            var targetUrl = (currentAction === 'Edit') ? 'actions/update_researcher.php' : 'actions/researcher_action.php';
            
            // Generate standard payload from form
            var payload = $(this).serialize();
            
            // If editing, append legacy keys that update_researcher.php expects
            if(currentAction === 'Edit') {
                payload += '&action_rd=update&hidden_id_rd=' + $('#hidden_id').val();
            }

            $.ajax({
                url: targetUrl,
                method: "POST",
                data: payload,
                dataType: 'json',
                beforeSend: function() { 
                    $('#submit_button').attr('disabled', 'disabled').val('wait...'); 
                },
                success: function(data) {
                    $('#submit_button').attr('disabled', false);
                    
                    if(data && data.error && data.error != '') {
                        // Backend returned an error
                        $('#form_message_rm').html(data.error); 
                        $('#submit_button').val(currentAction === 'Edit' ? 'Update Researcher' : 'Save Researcher');
                    } else {
                        // Success block
                        $('#researcherModal').modal('hide'); 
                        $('#message_rm').html(data ? data.success : '');
                        
                        var successMsg = (currentAction === 'Edit') ? 'The record has been successfully updated.' : 'The record has been successfully added.';
                        Swal.fire({ title: (currentAction === 'Edit' ? 'Updated!' : 'Added!'), text: successMsg, icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                        
                        setTimeout(function(){ $('#message_rm').html(''); }, 5000);

                        // Refresh UI based on where we are
                        if (currentAction === 'Edit' && window.location.pathname.indexOf('view_researcher.php') !== -1) {
                            setTimeout(function() { location.reload(); }, 600);
                        } else if (typeof dataTable !== 'undefined') { 
                            dataTable.ajax.reload(); 
                        }
                    }
                },
                error: function() {
                    // Fallback block if backend returns empty/non-JSON on update
                    $('#submit_button').attr('disabled', false);
                    $('#researcherModal').modal('hide');
                    Swal.fire({ title: 'Success', text: 'Action completed.', icon: 'success', timer: 600, showConfirmButton: false });
                    
                    if (currentAction === 'Edit' && window.location.pathname.indexOf('view_researcher.php') !== -1) {
                        setTimeout(function() { location.reload(); }, 600);
                    } else if (typeof dataTable !== 'undefined') { dataTable.ajax.reload(); }
                }
            });
        }
    });

    // --- MASTER TABLE DELETE HANDLERS ---
    function triggerMasterDelete(url, dataPayload) {
        Swal.fire({
            title: 'Are you sure?', text: 'You will not be able to recover this record!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, keep it', reverseButtons: true, customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, method: "POST", data: dataPayload,
                    success: function(data) {
                        Swal.fire({ title: 'Deleted!', text: 'Record deleted successfully.', icon: 'success', timer: 600, showConfirmButton: false });
                        if (typeof dataTable !== 'undefined') { dataTable.ajax.reload(); } else { setTimeout(function() { location.reload(); }, 600); }
                    }
                });
            }
        });
    }

    $(document).on('click', '.delete_buttona', function() { triggerMasterDelete("actions/researcher_action.php", { id: $(this).data('id'), action: 'delete' }); });
    $(document).on('click', '.delete_master_publication', function() { triggerMasterDelete("actions/publication_action.php", { publicationID: $(this).data('id'), action_publication: 'delete' }); });
    $(document).on('click', '.delete_master_researchconducted', function() { triggerMasterDelete("actions/researchconducted_action.php", { xid: $(this).data('id'), action_researchedconducted: 'delete' }); });
    $(document).on('click', '.delete_master_intellectualprop', function() { triggerMasterDelete("actions/intellectualprop_action.php", { intellectualPropID: $(this).data('id'), action_intellectualprop: 'delete' }); });
    $(document).on('click', '.delete_master_paper_presentation', function() { triggerMasterDelete("actions/paper_presentation_action.php", { paperPresentationID: $(this).data('id'), action_paper_presentation: 'delete' }); });
    $(document).on('click', '.delete_master_training', function() { triggerMasterDelete("actions/trainings_attended_action.php", { trainingID: $(this).data('id'), action_training: 'delete' }); });
    $(document).on('click', '.delete_master_extension_project', function() { triggerMasterDelete("actions/extension_project_action.php", { extensionID: $(this).data('id'), action_extension: 'delete' }); });
    $(document).on('click', '.delete_master_ext', function() { triggerMasterDelete("actions/extension_action.php", { extID: $(this).data('id'), action_ext: 'delete' }); });

    // Global Modal Fix
    $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
        $('#researcherModal .modal-body').scrollTop(0); // Updated ID
    });
    
    // Trigger table refresh when dropdown filters change
    $('#filter_rank, #filter_program').on('change', function() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) {
            $('#researcher_table').DataTable().ajax.reload();
        }
    });
});