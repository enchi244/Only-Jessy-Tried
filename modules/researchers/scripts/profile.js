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
        if ($.fn.DataTable.isDataTable('#researcher_table')) { $('#researcher_table').DataTable().destroy(); }
        $('#master_table_title').html('<i class="fas fa-lightbulb mr-2"></i>All Intellectual Property');
        $('#researcher_table thead').html('<tr><th width="20%">Author</th><th width="30%">Title</th><th width="15%">Co-Authors</th><th width="15%">Type</th><th width="10%">Granted</th><th width="10%" class="text-center">Action</th></tr>');
        dataTable = $('#researcher_table').DataTable({ "processing": true, "serverSide": true, "order": [], "ajax": { url: "actions/intellectualprop_action.php", type: "POST", data: { action_intellectualprop: 'fetch_all' } }, "columnDefs": [{ "targets": [5], "orderable": false }] });
        $('#toggleIDColumn').hide(); $('#fab_container').hide();
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


    // Add New Researcher Form
    $('#add_researcher').click(function(){
        $('#researcher_form')[0].reset();
        $('#researcher_form').parsley().reset();    
        $('#modal_title').text('Add Data');
        $('#action').val('Add');
        $('#submit_button').val('Add');
        $('#researcherModal').modal('show');
        $('#form_message_rm').html('');
    });

    $('#researcher_form').parsley();
    $('#researcher_form').on('submit', function(event){
        event.preventDefault();
        if($('#researcher_form').parsley().isValid()) {		
            $.ajax({
                url: "actions/researcher_action.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() { $('#submit_button').attr('disabled', 'disabled').val('wait...'); },
                success: function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message_rm').html(data.error); $('#submit_button').val('Add');
                    } else {
                        $('#researcherModal').modal('hide'); $('#message_rm').html(data.success);
                        if (typeof dataTable !== 'undefined') { dataTable.ajax.reload(); }
                        Swal.fire({ title: 'Added!', text: 'The record has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                        setTimeout(function(){ $('#message_rm').html(''); }, 5000);
                    }
                }
            });
        }
    });

    // Open Massive Edit Modal
    $(document).on('click', '.edit_buttona', function(){
        var id = $(this).data('id');
        var editForm = $('#researcherModala_form');
        if (editForm.length > 0) { editForm[0].reset(); var parsleyInstance = editForm.parsley(); if (parsleyInstance) { parsleyInstance.reset(); } }
        $('#form_message').html('');

        $.ajax({
            url: "actions/researcher_action.php",
            method: "POST",
            data: {id: id, action: 'fetch_single'},
            dataType: 'JSON',
            success: function(data) {
                $('#researcherIDu').val(data.researcherID); $('#familyNameu').val(data.familyName); $('#firstNameu').val(data.firstName); $('#middleNameu').val(data.middleName); $('#Suffixu').val(data.Suffix); $('#departmentu').val(data.department); $('#programu').val(data.program); $('#academic_ranku').val(data.academic_rank); $('#bachelor_degreeu').val(data.bachelor_degree); $('#bachelor_institutionu').val(data.bachelor_institution); $('#bachelor_YearGraduatedu').val(data.bachelor_YearGraduated); $('#masterDegreeu').val(data.masterDegree); $('#masterInstitutionu').val(data.masterInstitution); $('#masterYearGraduatedu').val(data.masterYearGraduated); $('#doctorateDegreeu').val(data.doctorateDegree); $('#doctorateInstitutionu').val(data.doctorateInstitution); $('#doctorateYearGraduateu').val(data.doctorateYearGraduate); $('#postDegreeu').val(data.postDegree); $('#postInstitutionu').val(data.postInstitution); $('#postYearGraduateu').val(data.postYearGraduate);
                $('#submit_button_rd').val('Edit'); $('#researcherModala').data('id', id).modal('show'); $('#hidden_id_rd').val(id);
                
                // Only try to load the sub-tabs if the functions actually exist in the current window context
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

    // Update Profile Data (Inside Edit Modal)
    $('#researcherModala_form').parsley();
    $('#submit_button_rd').on('click', function(event) {
        event.preventDefault(); 
        var dataPayload = {
            researcherIDu: $('#researcherIDu').val(), familyNameu: $('#familyNameu').val(), firstNameu: $('#firstNameu').val(), middleNameu: $('#middleNameu').val(), Suffixu: $('#Suffixu').val(), departmentu: $('#departmentu').val(), programu: $('#programu').val(), academic_ranku: $('#academic_ranku').val(), bachelor_degreeu: $('#bachelor_degreeu').val(), bachelor_institutionu: $('#bachelor_institutionu').val(), bachelor_YearGraduatedu: $('#bachelor_YearGraduatedu').val(), masterDegreeu: $('#masterDegreeu').val(), masterInstitutionu: $('#masterInstitutionu').val(), masterYearGraduatedu: $('#masterYearGraduatedu').val(), doctorateDegreeu: $('#doctorateDegreeu').val(), doctorateInstitutionu: $('#doctorateInstitutionu').val(), doctorateYearGraduateu: $('#doctorateYearGraduateu').val(), postDegreeu: $('#postDegreeu').val(), postInstitutionu: $('#postInstitutionu').val(), postYearGraduateu: $('#postYearGraduateu').val(), hidden_id_rd: $('#hidden_id_rd').val(), action_rd: 'update'
        };

        $.ajax({
            url: 'actions/update_researcher.php', method: 'POST', data: dataPayload, dataType: 'json',
            success: function(response) {
                Swal.fire({ title: 'Updated!', text: 'The record has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                
                if (window.location.pathname.indexOf('view_researcher.php') !== -1) {
                    setTimeout(function() { location.reload(); }, 600);
                } else if (typeof dataTable !== 'undefined') {
                    $('#researcherModala').modal('hide');
                    dataTable.ajax.reload();
                }
            }
        }); 
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
        $('#researcherModala .modal-body').scrollTop(0);
    });
    // Trigger table refresh when dropdown filters change
    $('#filter_rank, #filter_program').on('change', function() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) {
            $('#researcher_table').DataTable().ajax.reload();
        }
    });
});