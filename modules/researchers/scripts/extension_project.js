$(document).ready(function() {
    $('#extensionProjectModal').on('shown.bs.modal', function () {
        $('#linked_research_projects').select2({
            theme: "classic",
            placeholder: " Search and select research projects...",
            dropdownParent: $('#extensionProjectModal')
        });
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
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Extension Project
$('#add_extension_project').click(function () {
    var form = $('#extension_project_form');
    if (form.length > 0) { form[0].reset(); var p = form.parsley(); if (p) { p.reset(); } }
    
    $('#linked_research_projects').val(null).trigger('change'); 
    $('#has_files_extp').val('None').trigger('change');
    $('#new_files_container_extp').html('');
    $('#existing_files_container_extp').html('');

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

    var form = $('#extension_project_form');
    if (form.length > 0) { form[0].reset(); var p = form.parsley(); if (p) { p.reset(); } }
    
    $('#linked_research_projects').val(null).trigger('change');
    $('#form_message').html('');
    $('#new_files_container_extp').html('');
    $('#existing_files_container_extp').html('');

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

            // Handle Files
            $('#has_files_extp').val(data.has_files).trigger('change');
            
            if(data.existing_files && data.existing_files.length > 0) {
                var filesHtml = '';
                data.existing_files.forEach(function(f) {
                    filesHtml += `
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 border rounded shadow-sm" id="extp_file_row_${f.id}">
                            <div>
                                <span class="badge badge-info mr-2">${f.category}</span>
                                <a href="${f.path}" target="_blank" class="text-gray-800 font-weight-bold">${f.name}</a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-existing-extp-file" data-file-id="${f.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                });
                $('#existing_files_container_extp').html(filesHtml);
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

// --- DYNAMIC FILE UPLOAD LOGIC ---
$(document).on('change', '#has_files_extp', function() {
    if($(this).val() === 'With') {
        $('#dynamic_files_section_extp').slideDown(200);
    } else {
        $('#dynamic_files_section_extp').slideUp(200);
        $('#new_files_container_extp').empty();
    }
});

$(document).on('click', '#add_file_btn_extp', function() {
    var fileRow = `
        <div class="row align-items-center mb-2 new-file-row">
            <div class="col-md-4">
                <select name="extp_file_categories[]" class="form-control form-control-sm" required>
                    <option value="">Select Category</option>
                    <option value="Terminal Report">Terminal Report</option>
                    <option value="MOA">MOA</option>
                    <option value="SO">SO</option>
                    <option value="Financial Report">Financial Report</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="file" name="extp_files[]" class="form-control-file border p-1 rounded bg-white" required accept=".pdf,.doc,.docx,.jpg,.png,.xlsx">
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-sm btn-danger remove-new-extp-file"><i class="fas fa-times"></i></button>
            </div>
        </div>
    `;
    $('#new_files_container_extp').append(fileRow);
});

$(document).on('click', '.remove-new-extp-file', function() {
    $(this).closest('.new-file-row').remove();
});

$(document).on('click', '.delete-existing-extp-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#extp_file_row_' + fileId);
    
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
                    Swal.fire('Server Error', 'Failed to delete.', 'error');
                }
            });
        }
    });
});