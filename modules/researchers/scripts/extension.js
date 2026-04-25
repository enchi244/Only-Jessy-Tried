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
                
                // Populating Partners and other fields
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
                if (data.error && data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_ext').val($('#action_ext').val());
                } else {
                    $('#extModal').modal('hide');
                    var Svalue = $('#action_ext').val();
                    Swal.fire({ title: Svalue == "Add" ? 'Added!' : 'Updated!', text: 'The extension activity has been successfully saved.', icon: 'success', timer: 800, showConfirmButton: false, customClass: { confirmButton: 'btn-success' }});

                    // --- UPGRADED AUTO-REFRESH LOGIC ---
                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ 
                            var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
                            // Routes to the inner tab automatically
                            window.location.href = window.location.pathname + '?id=' + rid + '&tab=ext';
                        }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { 
                            if(typeof loadextprotab === "function") loadextprotab(researcherID); 
                        } else {
                            $('.dataTable').each(function() { if ($.fn.dataTable.isDataTable(this)) { $(this).DataTable().ajax.reload(null, false); } });
                        }
                    }
                    // -----------------------------------
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
    
    // UPDATED to use classes for the universal widget
    $('#extModal .new-files-container').html('');
    $('#extModal .existing-files-container').html('');
    
    $('#linked_extension_project').val(null).trigger('change');
    $('#proj_lead').empty().trigger('change');
    $('#assist_coordinators').empty().trigger('change');
    $('#partners_ext').val(''); 
    
    initResearcherSelects();
    
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    // FIX: Safely grab the researcher ID from multiple potential sources
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');  
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
    
    // UPDATED to use classes for the universal widget
    $('#extModal .new-files-container').html('');
    $('#extModal .existing-files-container').html('');
    
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
            $('#partners_ext').val(data.partners); 
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
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 border rounded shadow-sm" id="file_row_${f.id}">
                            <div>
                                <span class="badge badge-info mr-2">${f.category}</span>
                                <a href="${f.path}" target="_blank" class="text-gray-800 font-weight-bold">${f.name}</a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-existing-file" data-file-id="${f.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                });
                $('#extModal .existing-files-container').html(filesHtml);
            }

            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
        }
    });
});

$(document).on('click', '.delete_button_ext', function (e) {
    e.preventDefault();
    var extID = $(this).data('id');  
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the record and all attached files!',
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
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The extension activity has been successfully deleted.',
                        icon: 'success',
                        timer: 800,
                        showConfirmButton: false,
                    });

                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        setTimeout(function(){ location.reload(); }, 800);
                    }
                }
            });
        }
    });
});

// Delete Existing Server File via AJAX
$(document).on('click', '.delete-existing-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#file_row_' + fileId);
    
    // Check if we're inside the Extension Activity modal
    if(btn.closest('#extModal').length > 0) {
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
                        Swal.fire('Server Error', 'Failed to delete. Please check the console log.', 'error');
                    }
                });
            }
        });
    }
});