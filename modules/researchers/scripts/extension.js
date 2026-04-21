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
    if ($('#ext_project_form').parsley().isValid()) {
        var formData = new FormData(this); // Use FormData to support files

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
                    $('#submit_button_ext').val('Add');
                } else {
                    $('#extModal').modal('hide');
                    
                    Swal.fire({ 
                        title: 'Success!', 
                        text: 'Record saved successfully.', 
                        icon: 'success', 
                        timer: 1000, 
                        showConfirmButton: false 
                    });

                    // FIX: Reload table if in associated modal, otherwise reload page to update cards
                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        // We are on the main profile; reload to see new PHP-rendered cards
                        setTimeout(function(){ location.reload(); }, 1000);
                    }
                }
            }
        });
    }
});

// Add New Extension
$('#add_extension').click(function () {
    $('#ext_project_form')[0].reset();
    $('#linked_extension_project').val(null).trigger('change');
    $('#ext_project_form').parsley().reset();
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    // FIX: This now correctly picks up the ID because we changed the DIV to an INPUT
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val();
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
    $('#ext_project_form').parsley().reset();
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
            $('#stat_ext').val(data.stat); // Fixed key: was stat_ext

            $('#modal_title').text('Edit Extension');
            $('#action_ext').val('Edit');
            $('#submit_button_ext').val('Edit');
            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
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