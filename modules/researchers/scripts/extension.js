// Function to Load Extension Data using the Parent Extension Project ID
function loadextprotab(projectID) {
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
            data: { project_id: projectID, action_ext: 'fetch_associated' } 
        }
    });
    return extProjectsTable;
}

// Handle Form Submission for Extension
$('#ext_project_form').on('submit', function (event) {
    event.preventDefault();

    // Re-initialize Parsley to ensure it includes the dynamically added date and hidden fields
    var extensionForm = $('#ext_project_form').parsley({
        excluded: 'input[type=button], input[type=submit], input[type=reset]'
    });

    console.log("Validation status:", extensionForm.isValid());

    if (extensionForm.isValid()) {
        var formData = new FormData(this);

        $.ajax({
            url: "actions/extension_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_ext').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_ext').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_ext').val($('#action_ext').val());
                } else {
                    $('#extModal').modal('hide');
                    
                    Swal.fire({ 
                        title: 'Success!', 
                        text: 'Record saved successfully.', 
                        icon: 'success', 
                        timer: 1000, 
                        showConfirmButton: false 
                    });

                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        setTimeout(function(){ location.reload(); }, 1000);
                    }
                }
            },
            error: function() {
                $('#submit_button_ext').attr('disabled', false).val('Save Data');
                $('#form_message').html('<div class="alert alert-danger">Server Error: Could not save data.</div>');
            }
        });
    }
});

// Add New Extension
$('#add_extension').click(function () {
    // Auto-fill Project Leader when a Base Extension Project is selected
$(document).on('change', '#linked_extension_project', function() {
    var projectID = $(this).val();
    if(projectID) {
        $.ajax({
            url: "actions/extension_action.php",
            method: "POST",
            data: { project_id: projectID, action_ext: 'fetch_project_info' },
            dataType: "json",
            success: function(data) {
                if(data.proj_lead) {
                    $('#proj_lead').val(data.proj_lead);
                    // Tell Parsley to re-validate the field since we injected text
                    if ($('#proj_lead').parsley()) { $('#proj_lead').parsley().validate(); }
                }
            }
        });
    } else {
        // Clear if they deselect the project
        $('#proj_lead').val('');
    }
});
    $('#ext_project_form')[0].reset();
    
    // Clear custom date fields
    $('#period_start').val('');
    $('#period_end').val('');
    $('#period_implement').val('');
    
    $('#linked_extension_project').val(null).trigger('change');
    
    // Reset and Rebind Parsley
    if ($('#ext_project_form').parsley()) {
        $('#ext_project_form').parsley().destroy();
    }
    $('#ext_project_form').parsley();
    
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    // FIX: Retrieve Researcher ID from common context sources
    var rid = $('#hidden_id_rd').val() || $('.edit_researcher').data('id') || $('#researcherModala').data('id');
    $('#hidden_researcherID_ext').val(rid);

    var parentProjectID = $('#viewExtensionsModal').data('project-id');
    $('#hidden_parent_project_id').val(parentProjectID); 

    $('#submit_button_ext').val('Add');
    $('#extModal').modal('show');
    $('#form_message').html('');
});

// Edit Existing Extension
$(document).on('click', '.edit_button_ext', function () {
    var extID = $(this).data('id');
    $('#ext_project_form')[0].reset();
    
    if ($('#ext_project_form').parsley()) {
        $('#ext_project_form').parsley().reset();
    }
    
    $('#form_message').html('');

    $.ajax({
        url: "actions/extension_action.php",
        method: "POST",
        data: { extID: extID, action_ext: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            $('#title_ext').val(data.title);
            $('#description_ext').val(data.description);
            $('#proj_lead').val(data.proj_lead);
            $('#assist_coordinators').val(data.assist_coordinators);
            $('#budget').val(data.budget);
            $('#fund_source').val(data.fund_source);
            $('#target_beneficiaries').val(data.target_beneficiaries);
            $('#partners').val(data.partners);
            $('#stat_ext').val(data.stat);

            // Handle splitting the period implement string back into date inputs
            if (data.period_implement && data.period_implement.includes(" to ")) {
                var dates = data.period_implement.split(" to ");
                $('#period_start').val(dates[0]);
                $('#period_end').val(dates[1]);
                $('#period_implement').val(data.period_implement);
            } else {
                $('#period_start').val('');
                $('#period_end').val('');
                $('#period_implement').val(data.period_implement);
            }

            $('#modal_title').text('Edit Extension');
            $('#action_ext').val('Edit');
            $('#submit_button_ext').val('Edit');
            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
            $('#hidden_existing_attachment').val(data.attachments);
        }
    });
});

// Delete Extension
$(document).on('click', '.delete_button_ext, .delete_master_ext', function () {
    var extID = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: 'This record will be moved to the recycle bin.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/extension_action.php",
                method: "POST",
                data: { extID: extID, action_ext: 'delete' },
                success: function (data) {
                    Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1000, showConfirmButton: false });
                    
                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        setTimeout(function(){ location.reload(); }, 1000);
                    }
                }
            });
        }
    });
});