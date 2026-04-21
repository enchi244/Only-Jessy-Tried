// Function to Load Extension Data using the Parent Extension Project ID
function loadextprotab(projectID) {
    $(document).ready(function() {
        $('#extModal').on('shown.bs.modal', function () {
            $('#linked_extension_project').select2({
                theme: "classic",
                dropdownParent: $('#extModal')
            });
        });

        // Auto-fill Project Leader when an Extension Project is selected
        $('#linked_extension_project').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var leadName = selectedOption.attr('data-lead');
            
            if (leadName && leadName.trim() !== '') {
                $('#proj_lead').val(leadName);
            }
        });
    });

    $('#ext_project_form').parsley(); // Initialize form validation
    if ($.fn.dataTable.isDataTable('#ext_project_table')) {
        $('#ext_project_table').DataTable().clear().destroy();
    }

    var extProjectsTable = $('#ext_project_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/extension_action.php",
            type: "POST",
            // Pass the parent project ID and trigger a specific fetch action
            data: { project_id: projectID, action_ext: 'fetch_associated' } 
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return extProjectsTable;
}

// Handle Form Submission for Extension
$('#ext_project_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#ext_project_form').parsley().isValid()) {
        $.ajax({
            url: "actions/extension_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#submit_button_ext').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_ext').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_ext').val('Add');
                } else {
                    $('#extModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_ext').val();
                    if (Svalue == "Add") {
                        Swal.fire({ title: 'Added!', text: 'The extension has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    } else {
                        Swal.fire({ title: 'Updated!', text: 'The extension has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    }

                    // Reload table using the active Project ID stored on the modal
                    var projectID = $('#viewExtensionsModal').data('project-id');
                    loadextprotab(projectID);
                    setTimeout(function () { $('#message').html(''); }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#extModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }
    // Prevent closing the parent modal
    $('#viewExtensionsModal').css('overflow-y', 'auto');
});

// Add New Extension
$('#add_extension').click(function () {
    $('#ext_project_form')[0].reset();
    $('#linked_extension_project').val(null).trigger('change');
    $('#ext_project_form').parsley().reset();
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    // Get Researcher ID from main profile
    var rid = $('#researcherModala').data('id');
    $('#hidden_researcherID_ext').val(rid);

    // Get Parent Extension Project ID from the modal container
    var parentProjectID = $('#viewExtensionsModal').data('project-id');
    $('#hidden_parent_project_id').val(parentProjectID); // Ensure we link it in the backend

    $('#submit_button_ext').val('Add');
    $('#extModal').modal('show');
    $('#form_message').html('');
});

// Edit Existing Extension
$(document).on('click', '.edit_button_ext', function () {
    var extID = $(this).data('id');

    $('#ext_project_form')[0].reset();
    $('#ext_project_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url: "actions/extension_action.php",
        method: "POST",
        data: { extID: extID, action_ext: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            const inputDateStarted_ext = data.period_implement;
            if(inputDateStarted_ext && inputDateStarted_ext.includes('-')) {
                const [monthStarted, dayStarted, yearStarted] = inputDateStarted_ext.split('-');
                const formattedDateStarted_ext = `${yearStarted}-${monthStarted}-${dayStarted}`;
                $('#period_implement').val(formattedDateStarted_ext);
            } else {
                $('#period_implement').val('');
            }

            $('#title_ext').val(data.title);
            $('#description_ext').val(data.description);
            $('#proj_lead').val(data.proj_lead);
            $('#assist_coordinators').val(data.assist_coordinators);
            $('#budget').val(data.budget);
            $('#fund_source').val(data.fund_source);
            $('#target_beneficiaries').val(data.target_beneficiaries);
            $('#partners').val(data.partners);
            $('#stat_ext').val(data.stat_ext);

            $('#modal_title').text('Edit Extension');
            $('#action_ext').val('Edit');
            $('#submit_button_ext').val('Edit');
            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
        }
    });
});

// Handle Delete Button for Extension
$(document).on('click', '.delete_button_ext', function () {
    var extID = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this record!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/extension_action.php",
                method: "POST",
                data: { extID: extID, action_ext: 'delete' },
                success: function (data) {
                    Swal.fire({ title: 'Deleted!', text: 'The extension has been successfully deleted.', icon: 'success', timer: 600, showConfirmButton: false });
                    
                    var projectID = $('#viewExtensionsModal').data('project-id');
                    loadextprotab(projectID);
                    
                    setTimeout(function () { $('#message').html(''); }, 5000);
                },
                error: function (xhr, status, error) {
                    Swal.fire({ title: 'Error!', text: 'Something went wrong.', icon: 'error', customClass: { confirmButton: 'btn-danger' } });
                }
            });
        }
    });
});