$(document).ready(function() {
    $('#extensionProjectModal').on('shown.bs.modal', function () {
        $('#linked_research_projects').select2({
            theme: "classic",
            placeholder: " Search and select research projects...",
            dropdownParent: $('#extensionProjectModal')
        });
    });

    // Dynamically display Lead and Co-Researchers when a project is selected
    $('#linked_research_projects').on('change', function() {
        var selected = $(this).find('option:selected');
        var html = '';
        if (selected.length > 0) {
            html += '<div class="p-3 mt-2 bg-light border rounded shadow-sm" style="font-size: 0.9rem;">';
            html += '<strong class="text-gray-800 d-block mb-2 border-bottom pb-1">Project Researchers:</strong>';
            selected.each(function() {
                var title = $(this).text();
                var lead = $(this).attr('data-lead');
                var co = $(this).attr('data-co');
                html += '<div class="mb-3">';
                html += '<div class="font-weight-bold text-dark mb-1" style="line-height: 1.2;"><i class="fas fa-caret-right mr-1 text-danger pink"></i> ' + title + '</div>';
                html += '<div class="ml-3"><span class="badge badge-primary px-2 py-1 mr-1">Lead</span> ' + lead + '</div>';
                html += '<div class="ml-3 mt-1 text-muted"><i class="fas fa-users mr-1"></i> ' + co + '</div>';
                html += '</div>';
            });
            html += '</div>';
        }
        $('#project_authors_display').html(html);
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
        "columnDefs": [{ "targets": [0], "orderable": false }],
    });
    return extensionProjectTable;
}

$('#extensionProjectModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_extension').val(); 
    loadExtensionProjectsTab(id);  
});

// Handle Form Submission using FormData
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
                if (data.error && data.error != '') {
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

                    var researcherID = $('#researcherModala').data('id');  
                    loadExtensionProjectsTab(researcherID);  

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            },
            error: function(xhr) {
                $('#submit_button_extension').attr('disabled', false).html('Save Data');
                Swal.fire('Error', 'An error occurred while saving the data. Check the console.', 'error');
                console.error(xhr.responseText);
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
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Extension Project
// Add New Extension Project
$('#add_extension_project').click(function () {
    var form = $('#extension_project_form');
    if (form.length > 0) { form[0].reset(); var p = form.parsley(); if (p) { p.reset(); } }
    
    $('#linked_research_projects').val(null).trigger('change'); 
    
    // UPDATED to use classes for the universal widget
    $('#extensionProjectModal .new-files-container').html('');
    $('#extensionProjectModal .existing-files-container').html('');

    $('#modal_title').text('Add Extension Project');  
    $('#action_extension').val('Add');
    
    // FIX: Safely grab the researcher ID from multiple potential sources
    // This guarantees the ID is found whether in the Master List or the Profile Page
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');  
    
    $('#hidden_researcherID_extension').val(rid);  
    
    $('#submit_button_extension').html('Save Data');
    $('#extensionProjectModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Extension Project
$(document).on('click', '.edit_button_extension_project', function () {
    var extensionID = $(this).data('id');  

    var form = $('#extension_project_form');
    if (form.length > 0) { form[0].reset(); var p = form.parsley(); if (p) { p.reset(); } }
    
    $('#linked_research_projects').val(null).trigger('change');
    $('#form_message').html('');
    
    // UPDATED to use classes for the universal widget
    $('#extensionProjectModal .new-files-container').html('');
    $('#extensionProjectModal .existing-files-container').html('');

    $.ajax({
        url: "actions/extension_project_action.php",
        method: "POST",
        data: { extensionID: extensionID, action_extension: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            function parseLegacyDate(val) {
                if (!val || val === 'null' || val === '0000-00-00') return '';
                let str = String(val).trim().replace(/\//g, '-');
                let parts = str.split('-');
                if (parts.length === 1 && parts[0].length === 4) return `${parts[0]}-01-01`;
                if (parts.length === 2) {
                    if (parts[1].length === 4) return `${parts[1]}-${parts[0].padStart(2, '0')}-01`;
                    if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2, '0')}-01`;
                }
                if (parts.length === 3) {
                    if (parts[2].length === 4) return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
                    if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                }
                return ''; 
            }
            
            $('#title_extp').val(data.title);
            $('#start_date_extc').val(parseLegacyDate(data.start_date));
            $('#completion_date_extc').val(parseLegacyDate(data.completed_date));
            $('#funding_source_exct').val(data.funding_source);
            $('#approved_budget_exct').val(data.approved_budget);
            $('#target_beneficiaries_communities').val(data.target_beneficiaries_communities);
            $('#partners').val(data.partners);
            $('#status_exct').val(data.status_exct);

            if(data.linked_projects && data.linked_projects.length > 0) {
                $('#linked_research_projects').val(data.linked_projects).trigger('change');
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
                $('#extensionProjectModal .existing-files-container').html(filesHtml);
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
$(document).on('click', '.delete_button_extension_project, .delete_master_extension_project', function (e) {
    e.preventDefault();
    var extensionID = $(this).data('id');  
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
                url: "actions/extension_project_action.php",
                method: "POST",
                data: { extensionID: extensionID, action_extension: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The extension project has been successfully deleted.',
                        icon: 'success',
                        timer: 800,
                        showConfirmButton: false,
                    });

                    var researcherID = $('#researcherModala').data('id');
                    if(researcherID) { loadExtensionProjectsTab(researcherID); } else { location.reload(); }
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
    
    // Check if we're inside the Extension Project modal
    if(btn.closest('#extensionProjectModal').length > 0) {
        Swal.fire({
            title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: "actions/extension_project_action.php",
                    method: "POST",
                    data: { action_extension: 'delete_file', file_id: fileId },
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

// --- VIEW ASSOCIATED EXTENSIONS MODAL TRIGGER ---
$(document).on('click', '.view_associated_extensions', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var projectID = $(this).data('id');
    
    // Store project ID in the modal so we know which project these extensions belong to
    $('#viewExtensionsModal').data('project-id', projectID);
    
    // Trigger the extension.js table reload using the projectID instead of Researcher ID
    if (typeof loadextprotab === "function") {
        loadextprotab(projectID); 
    }
    
    $('#viewExtensionsModal').modal('show');
});