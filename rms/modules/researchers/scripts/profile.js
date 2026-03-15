$(document).ready(function() {
    $('.modal').appendTo('body');
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    // 1. Initialize Main Researcher Table
// 1. Initialize Main Researcher Table
    var dataTable = $('#researcher_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/researcher_action.php",
            type: "POST",
            data: { action: 'fetch' },
        },
        "columnDefs": [{ "targets": [0], "orderable": false }],
        "order": [[2, 'asc']],
        "stateSave": true,
        "drawCallback": function(settings) {
            var api = this.api();
            var rows = api.rows({ page: 'all' }).nodes();
            var departments = {};
            var totalResearchers = rows.length; // Count total researchers

            // Count researchers per department
            api.column(2, { page: 'all' }).data().each(function(department, i) {
                if (department) {
                    if (!departments[department]) { departments[department] = 0; }
                    departments[department]++;
                }
            });

            // Update the Modern KPI UI Widgets at the top of the page!
            $('#kpi_total_researchers').text(totalResearchers);
            $('#kpi_total_departments').text(Object.keys(departments).length);

            // Render the sleek new department headers
            var last = null;
            api.column(2, { page: 'all' }).data().each(function(department, i) {
                if (last !== department) {
                    $(rows).eq(i).before('<tr class="group"><td colspan="6"><i class="fas fa-folder-open mr-2 text-primary"></i><strong class="text-primary">' + department + '</strong> <span class="badge badge-light text-primary ml-2 rounded-pill shadow-sm" style="border: 1px solid #eaecf4;">' + (departments[department] || 0) + ' Members</span></td></tr>');
                }
                last = department;
            });
        }
    });

    // 2. Add New Researcher Form
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
                beforeSend: function() {
                    $('#submit_button').attr('disabled', 'disabled').val('wait...');
                },
                success: function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message_rm').html(data.error);
                        $('#submit_button').val('Add');
                    } else {
                        $('#researcherModal').modal('hide');
                        $('#message_rm').html(data.success);
                        dataTable.ajax.reload();
                        Swal.fire({ title: 'Added!', text: 'The record has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                        setTimeout(function(){ $('#message_rm').html(''); }, 5000);
                    }
                }
            });
        }
    });

    // 3. Open Massive Edit Modal
    $(document).on('click', '.edit_buttona', function(){
        var id = $(this).data('id');
        
        // Crash-Proof Parsley Reset
        var editForm = $('#researcherModala_form');
        if (editForm.length > 0) {
            editForm[0].reset();
            var parsleyInstance = editForm.parsley();
            if (parsleyInstance) { parsleyInstance.reset(); }
        }
        $('#form_message').html('');

        $.ajax({
            url: "actions/researcher_action.php",
            method: "POST",
            data: {id: id, action: 'fetch_single'},
            dataType: 'JSON',
            success: function(data) {
                $('#researcherIDu').val(data.researcherID);
                $('#familyNameu').val(data.familyName);
                $('#firstNameu').val(data.firstName);
                $('#middleNameu').val(data.middleName);
                $('#Suffixu').val(data.Suffix);
                $('#departmentu').val(data.department);
                $('#programu').val(data.program);
                $('#bachelor_degreeu').val(data.bachelor_degree);
                $('#bachelor_institutionu').val(data.bachelor_institution);
                $('#bachelor_YearGraduatedu').val(data.bachelor_YearGraduated);
                $('#masterDegreeu').val(data.masterDegree);
                $('#masterInstitutionu').val(data.masterInstitution);
                $('#masterYearGraduatedu').val(data.masterYearGraduated);
                $('#doctorateDegreeu').val(data.doctorateDegree);
                $('#doctorateInstitutionu').val(data.doctorateInstitution);
                $('#doctorateYearGraduateu').val(data.doctorateYearGraduate);
                $('#postDegreeu').val(data.postDegree);
                $('#postInstitutionu').val(data.postInstitution);
                $('#postYearGraduateu').val(data.postYearGraduate);

                $('#modal_title').text('Edit');
                $('#submit_button_rd').val('Edit');
                $('#researcherModala').data('id', id).modal('show');
                $('#hidden_id_rd').val(id);
                
                // Trigger Child Tabs Data Fetch
                loadResearchConductedTab(id);
                loadPublicationTab(id);
                loadIntellectualPropTab(id); 
                loadPaperPresentationTab(id); 
                loadTrainingsAttendedTab(id);
                loadExtensionProjectsTab(id);
                loadextprotab(id); 
            }
        });
    });

    // 4. Update Profile Data (Inside Edit Modal)
    $('#researcherModala_form').parsley();
    $('#submit_button_rd').on('click', function(event) {
        event.preventDefault(); 
        var dataPayload = {
            researcherIDu: $('#researcherIDu').val(),
            familyNameu: $('#familyNameu').val(),
            firstNameu: $('#firstNameu').val(),
            middleNameu: $('#middleNameu').val(),
            Suffixu: $('#Suffixu').val(),
            departmentu: $('#departmentu').val(),
            programu: $('#programu').val(),
            bachelor_degreeu: $('#bachelor_degreeu').val(),
            bachelor_institutionu: $('#bachelor_institutionu').val(),
            bachelor_YearGraduatedu: $('#bachelor_YearGraduatedu').val(),
            masterDegreeu: $('#masterDegreeu').val(),
            masterInstitutionu: $('#masterInstitutionu').val(),
            masterYearGraduatedu: $('#masterYearGraduatedu').val(),
            doctorateDegreeu: $('#doctorateDegreeu').val(),
            doctorateInstitutionu: $('#doctorateInstitutionu').val(),
            doctorateYearGraduateu: $('#doctorateYearGraduateu').val(),
            postDegreeu: $('#postDegreeu').val(),
            postInstitutionu: $('#postInstitutionu').val(),
            postYearGraduateu: $('#postYearGraduateu').val(),
            hidden_id_rd: $('#hidden_id_rd').val(),
            action_rd: 'update'
        };

        $.ajax({
            url: 'actions/update_researcher.php',
            method: 'POST',
            data: dataPayload,
            dataType: 'json',
            success: function(response) {
                Swal.fire({ title: 'Updated!', text: 'The record has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                dataTable.ajax.reload();
            }
        }); 
    });

    // 5. Delete Researcher
    $(document).on('click', '.delete_buttona', function() {
        var id = $(this).data('id'); 
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this record!",
            icon: 'warning',
            showCancelButton: true, 
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it',
            reverseButtons: true,
            customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "actions/researcher_action.php",
                    method: "POST",
                    data: { id: id, action: 'delete' },
                    success: function(data) {
                        Swal.fire({ title: 'Deleted!', text: 'The record has been successfully deleted.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                        dataTable.ajax.reload();
                    }
                });
            }
        });
    });

    // Global Modal Fix: Allow scrollbar to return if multiple modals are open
    $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
        $('#researcherModala .modal-body').scrollTop(0);
    });
});