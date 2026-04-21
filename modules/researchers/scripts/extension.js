// modules/researchers/scripts/extension.js

// Function to Load Extension Data
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
            data: function(d) {
                d.project_id = projectID;
                d.action_ext = 'fetch_associated';
            }
        }
    });
    return extProjectsTable;
}

// Initialize Searchable Researcher Selects
function initResearcherSelects() {
    $('.select2-researcher').select2({
        dropdownParent: $('#extModal'),
        placeholder: "Search Researcher...",
        allowClear: true,
        ajax: {
            url: "actions/fetch_researchers.php",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term };
            },
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });
}

// Handle Auto-fill when Based Project is selected
$(document).on('change', '#linked_extension_project', function() {
    var projectID = $(this).val();
    var action = $('#action_ext').val();
    
    // Only auto-fill if we are in "Add" mode
    if (projectID != '' && action == 'Add') {
        $.ajax({
            url: "actions/extension_action.php",
            method: "POST",
            data: { action_ext: 'fetch_project_info', project_id: projectID },
            dataType: 'json',
            success: function(data) {
                // Populate Leader Select2
                if (data.proj_lead) {
                    var newOption = new Option(data.proj_lead, data.proj_lead, true, true);
                    $('#proj_lead').empty().append(newOption).trigger('change');
                }
                
                // Populate Coordinators Select2
                if (data.assist_coordinators) {
                    var coords = data.assist_coordinators.split(', ');
                    $('#assist_coordinators').empty();
                    coords.forEach(function(c) {
                        var opt = new Option(c, c, true, true);
                        $('#assist_coordinators').append(opt);
                    });
                    $('#assist_coordinators').trigger('change');
                }
                
                // Populating Partners (Using new unique ID) and other fields
                $('#partners_ext').val(data.partners); 
                $('#fund_source').val(data.fund_source);
                $('#budget').val(data.budget);
                $('#target_beneficiaries').val(data.target_beneficiaries);
            }
        });
    }
});

// Date Combiner Logic
$(document).on('change', '#period_start, #period_end', function() {
    const start = $('#period_start').val();
    const end = $('#period_end').val();
    if (start && end) {
        $('#period_implement').val(start + " to " + end);
    }
});

// Ensure Leader is not in Coordinator list
$(document).on('change', '#proj_lead', function() {
    var selectedLeader = $(this).val();
    var coordinatorSelect = $('#assist_coordinators');
    var currentCoordinators = coordinatorSelect.val() || [];
    var index = currentCoordinators.indexOf(selectedLeader);
    if (index > -1) {
        currentCoordinators.splice(index, 1);
        coordinatorSelect.val(currentCoordinators).trigger('change');
    }
});

$('#ext_project_form').on('submit', function (event) {
    event.preventDefault();
    var extensionForm = $('#ext_project_form').parsley();

    if (extensionForm.isValid()) {
        const start = $('#period_start').val();
        const end = $('#period_end').val();
        if (start && end) {
            $('#period_implement').val(start + " to " + end);
        }

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
                    Swal.fire({ title: 'Success!', text: 'Record saved successfully.', icon: 'success', timer: 1000, showConfirmButton: false });

                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        setTimeout(function(){ location.reload(); }, 1000);
                    }
                }
            }
        });
    }
});

$('#add_extension').click(function () {
    $('#ext_project_form')[0].reset();
    $('#period_start').val('');
    $('#period_end').val('');
    $('#period_implement').val('');
    $('#new_files_container_ext').html('');
    $('#existing_files_container_ext').html('');
    
    $('#linked_extension_project').val(null).trigger('change');
    $('#proj_lead').empty().trigger('change');
    $('#assist_coordinators').empty().trigger('change');
    $('#partners_ext').val(''); // Explicitly clear unique ID field
    
    initResearcherSelects();
    
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    var rid = $('#hidden_id_rd').val() || $('.edit_researcher').data('id');
    $('#hidden_researcherID_ext').val(rid);

    var parentProjectID = $('#viewExtensionsModal').data('project-id');
    $('#hidden_parent_project_id').val(parentProjectID); 

    if(parentProjectID) {
        $('#linked_extension_project').val(parentProjectID).trigger('change');
    }

    $('#submit_button_ext').val('Add');
    $('#extModal').modal('show');
});

$(document).on('click', '.edit_button_ext', function () {
    var extID = $(this).data('id');
    $('#ext_project_form')[0].reset();
    $('#new_files_container_ext').html('');
    $('#existing_files_container_ext').html('');
    initResearcherSelects();

    $.ajax({
        url: "actions/extension_action.php",
        method: "POST",
        data: { extID: extID, action_ext: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            $('#modal_title').text('Edit Extension');
            $('#action_ext').val('Edit');
            $('#submit_button_ext').val('Edit');
            
            $('#title_ext').val(data.title);
            $('#description_ext').val(data.description);
            
            // Setting the project ID first to avoid triggering Add auto-fill
            $('#linked_extension_project').val(data.extension_project_id).trigger('change');
            
            if(data.proj_lead) {
                var newOption = new Option(data.proj_lead, data.proj_lead, true, true);
                $('#proj_lead').empty().append(newOption).trigger('change');
            }
            
            if(data.assist_coordinators) {
                var coords = data.assist_coordinators.split(', ');
                $('#assist_coordinators').empty();
                coords.forEach(function(c) {
                    var opt = new Option(c, c, true, true);
                    $('#assist_coordinators').append(opt);
                });
                $('#assist_coordinators').trigger('change');
            }

            $('#budget').val(data.budget);
            $('#fund_source').val(data.fund_source);
            $('#target_beneficiaries').val(data.target_beneficiaries);
            $('#partners_ext').val(data.partners); // Using new unique ID
            $('#stat_ext').val(data.stat);
            $('#a_link_ext').val(data.a_link);

            if (data.period_implement && data.period_implement.includes(" to ")) {
                var dates = data.period_implement.split(" to ");
                $('#period_start').val(dates[0]);
                $('#period_end').val(dates[1]);
                $('#period_implement').val(data.period_implement);
            }

            if(data.existing_files && data.existing_files.length > 0) {
                var filesHtml = '';
                data.existing_files.forEach(function(f) {
                    filesHtml += `
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 border rounded shadow-sm" id="ext_file_row_${f.id}">
                            <div>
                                <span class="badge badge-info mr-2">${f.category}</span>
                                <a href="${f.path}" target="_blank" class="text-gray-800 font-weight-bold">${f.name}</a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-existing-ext-activity-file" data-file-id="${f.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                });
                $('#existing_files_container_ext').html(filesHtml);
            }

            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
        }
    });
});

// --- DYNAMIC FILE UPLOAD LOGIC ---
$(document).on('click', '#add_file_btn_ext', function() {
    var fileRow = `
        <div class="row align-items-center mb-2 new-file-row">
            <div class="col-md-4">
                <select name="ext_file_categories[]" class="form-control form-control-sm" required>
                    <option value="">Select Category</option>
                    <option value="Activity Report">Activity Report</option>
                    <option value="Attendance">Attendance</option>
                    <option value="Photos">Photos</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="file" name="ext_files[]" class="form-control-file border p-1 rounded bg-white" required accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" multiple>
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-sm btn-danger remove-new-ext-file"><i class="fas fa-times"></i></button>
            </div>
        </div>
    `;
    $('#new_files_container_ext').append(fileRow);
});

$(document).on('click', '.remove-new-ext-file', function() {
    $(this).closest('.new-file-row').remove();
});

$(document).on('click', '.delete-existing-ext-activity-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#ext_file_row_' + fileId);
    
    Swal.fire({
        title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            $.ajax({
                url: "actions/extension_action.php",
                method: "POST",
                data: { action_ext: 'delete_file', file_id: fileId },
                dataType: "json",
                success: function(data) {
                    if(data.status === 'success') {
                        Swal.fire({title: 'Deleted!', text: 'The file has been deleted.', icon: 'success', timer: 1000, showConfirmButton: false});
                        row.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        btn.html('<i class="fas fa-trash"></i>').prop('disabled', false);
                        Swal.fire('Error', data.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.html('<i class="fas fa-trash"></i>').prop('disabled', false);
                    console.error("Delete Error:", xhr.responseText);
                    Swal.fire('Server Error', 'Failed to delete.', 'error');
                }
            });
        }
    });
});