$(document).ready(function() {
    $('#extensionProjectModal').on('shown.bs.modal', function () {
        $('#linked_research_projects').select2({
            theme: "classic",
            placeholder: " Search and select research projects...",
            dropdownParent: $('#extensionProjectModal')
        });
    });

    // Toggle File Upload field based on Terminal Report selection
    $('#terminal_report_extc').on('change', function() {
        if ($(this).val() === 'With') {
            $('#terminal_report_file_container').slideDown();
        } else {
            $('#terminal_report_file_container').slideUp();
            $('#terminal_report_file').val(''); // Clear the file input
        }
    });
});

function loadExtensionProjectsTab(researcherID) {
    $('#extension_project_form').parsley();
    if ($.fn.dataTable.isDataTable('#extension_project_table')) {
        $('#extension_project_table').DataTable().clear().destroy();
    }

    var extensionProjectTable = $('#extension_project_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/extension_project_action.php",
            type: "POST",
            data: { rid: researcherID, action_extension: 'fetch' }
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return extensionProjectTable;
}

// Handle Tab Switching for Dynamic Content
$('#extensionProjectModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_extension').val(); 
    loadExtensionProjectsTab(id);  
});

// Handle Form Submission for Extension Project (Converted to FormData for files)
$('#extension_project_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#extension_project_form').parsley().isValid()) {
        
        var formData = new FormData(this);

        $.ajax({
            url: "actions/extension_project_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_extension').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function (data) {
                $('#submit_button_extension').attr('disabled', false).html('Save Data');
                if (data.error != '') {
                    $('#form_message').html(data.error);
                } else {
                    $('#extensionProjectModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_extension').val();
                    var titleText = (Svalue == "Add") ? 'Added!' : 'Updated!';
                    var msgText = (Svalue == "Add") ? 'The extension project has been successfully added.' : 'The extension project has been successfully updated.';

                    Swal.fire({
                        title: titleText,
                        text: msgText,
                        icon: 'success',
                        timer: 800,
                        showConfirmButton: false, 
                        customClass: { confirmButton: 'btn-success' }
                    });

                    // Reload the table data
                    var researcherID = $('#researcherModala').data('id');  
                    loadExtensionProjectsTab(researcherID);  

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#extensionProjectModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }
    $('#linked_research_projects').val(null).trigger('change'); 
    $('#terminal_report_file_container').hide();
    $('#existing_file_link').html('');
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Extension Project
$('#add_extension_project').click(function () {
    $('#extension_project_form')[0].reset();  
    $('#extension_project_form').parsley().reset();  
    $('#linked_research_projects').val(null).trigger('change'); 
    $('#terminal_report_file_container').hide();
    $('#existing_file_link').html('');
    $('#hidden_terminal_report_file').val('');

    $('#modal_title').text('Add Extension Project');  
    $('#action_extension').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_extension').val(rid);  
    $('#submit_button_extension').html('Save Data');
    $('#extensionProjectModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Extension Project
$(document).on('click', '.edit_button_extension_project', function () {
    var extensionID = $(this).data('id');  

    $('#extension_project_form')[0].reset();
    $('#extension_project_form').parsley().reset();
    $('#linked_research_projects').val(null).trigger('change');
    $('#form_message').html('');

    $.ajax({
        url: "actions/extension_project_action.php",
        method: "POST",
        data: { extensionID: extensionID, action_extension: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
           
            const inputDateApplied = data.start_date;
            const inputDateGranted = data.completed_date;

            const [monthApplied, dayApplied, yearApplied] = inputDateApplied.split('-');
            const formattedDateApplied = `${yearApplied}-${monthApplied}-${dayApplied}`;

            const [monthGranted, dayGranted, yearGranted] = inputDateGranted.split('-');
            const formattedDateGranted = `${yearGranted}-${monthGranted}-${dayGranted}`;
           
            $('#title_extp').val(data.title);
            $('#start_date_extc').val(formattedDateApplied);
            $('#completion_date_extc').val(formattedDateGranted);
            $('#funding_source_exct').val(data.funding_source);
            $('#approved_budget_exct').val(data.approved_budget);
            $('#target_beneficiaries_communities').val(data.target_beneficiaries_communities);
            $('#partners').val(data.partners);
            $('#status_exct').val(data.status_exct);
            $('#terminal_report_extc').val(data.terminal_report);

            // Handle the file upload UI
            if (data.terminal_report === 'With') {
                $('#terminal_report_file_container').show();
                if(data.terminal_report_file && data.terminal_report_file !== '') {
                    $('#existing_file_link').html('<a href="../../uploads/documents/' + data.terminal_report_file + '" target="_blank" class="text-primary"><i class="fas fa-external-link-alt mr-1"></i> View Currently Attached Report</a>');
                    $('#hidden_terminal_report_file').val(data.terminal_report_file);
                } else {
                    $('#existing_file_link').html('<span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Marked as "With", but no file is currently uploaded.</span>');
                    $('#hidden_terminal_report_file').val('');
                }
            } else {
                $('#terminal_report_file_container').hide();
                $('#existing_file_link').html('');
                $('#hidden_terminal_report_file').val('');
            }

            // Populate the Select2 linked projects array
            if(data.linked_projects && data.linked_projects.length > 0) {
                $('#linked_research_projects').val(data.linked_projects).trigger('change');
            }

            $('#modal_title').text('Edit Extension Project');
            $('#action_extension').val('Edit');
            $('#submit_button_extension').html('Save Changes');
            $('#extensionProjectModal').modal('show');
            $('#hidden_extensionID').val(extensionID);
        }
    });
});

// Handle Delete Button for Extension Project
$(document).on('click', '.delete_button_extension_project', function () {
    var extensionID = $(this).data('id');  
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this record!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn-danger',
            cancelButton: 'btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/extension_project_action.php",
                method: "POST",
                data: { extensionID: extensionID, action_extension: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The extension project has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    var researcherID = $('#researcherModala').data('id');
                    loadExtensionProjectsTab(researcherID);  
                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong: ' + error,
                        icon: 'error',
                        confirmButtonText: 'Try Again',
                        customClass: {
                            confirmButton: 'btn-danger'
                        }
                    });
                }
            });
        }
    });
});

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
        customClass: {
            confirmButton: 'btn-danger', 
            cancelButton: 'btn-secondary' 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/researcher_action.php",
                method: "POST",
                data: { id: id, action: 'delete' },
                success: function(data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The record has been successfully deleted.',
                        icon: 'success',
                        timer: 600, 
                        showConfirmButton: false, 
                        customClass: { confirmButton: 'btn-success' } 
                    });
                    dataTable.ajax.reload();
                    setTimeout(function() {
                        $('#message').html('');
                    }, 5000);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        timer: 3000, 
                        showConfirmButton: false
                    });
                }
            });
        }
    });
});