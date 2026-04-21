$(document).ready(function() {
    // Initialize Select2 for Collaborators inside the Modal
    if ($('#collaborators').length) {
        $('#collaborators').select2({
            placeholder: "Select Co-Researchers / Collaborators",
            allowClear: true,
            dropdownParent: $('#researchconductedModal')
        });
    }
// Prevent Lead Researcher from being selected as a Co-Researcher
    $(document).on('change', '#lead_researcher_id', function() {
        var selectedLead = $(this).val();
        
        $('#collaborators option').prop('disabled', false); 
        
        if (selectedLead) {
            $('#collaborators option[value="' + selectedLead + '"]').prop('disabled', true);
            
            var currentCollabs = $('#collaborators').val() || [];
            var newCollabs = currentCollabs.filter(function(val) {
                return val != selectedLead;
            });
            $('#collaborators').val(newCollabs);
        }
        $('#collaborators').trigger('change.select2'); 
    });
});

// 1. Function to Load Research Conducted Tab Data using the Main Researcher ID
function loadResearchConductedTab(researcherID) {
    $('#researchconducted_form').parsley();
    if ($.fn.dataTable.isDataTable('#researcherconducted_table')) {
        $('#researcherconducted_table').DataTable().clear().destroy();
    }

    var rcdataTable = $('#researcherconducted_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/researchconducted_action.php",
            type: "POST",
            data: {rid: researcherID, action_researchedconducted: 'fetch'}
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return rcdataTable;
}

// 2. Handle Tab Switching for Dynamic Content
$('#researchconductedTab').on('shown.bs.tab', function() {
    var id = $('#hidden_id_rd').val(); 
    loadResearchConductedTab(id);  
});

// 3. Handle Form Submission (Using FormData for Files)
$('#researchconducted_form').on('submit', function(event) {
    event.preventDefault();
    if ($('#researchconducted_form').parsley().isValid()) {
        
        var formData = new FormData(this);
        
        $.ajax({
            url: "actions/researchconducted_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#submit_button_researchedconducted').attr('disabled', 'disabled').val('Wait...');
            },
            success: function(data) {
                $('#submit_button_researchedconducted').attr('disabled', false);
                if(data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_researchedconducted').val($('#action_researchedconducted').val());
                } else {
                    $('#researchconductedModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_researchedconducted').val();
                    Swal.fire({
                        title: Svalue == "Add" ? 'Added!' : 'Updated!',
                        text: 'The record has been successfully saved.',
                        icon: 'success',
                        timer: 800,  
                        showConfirmButton: false,  
                        customClass: { confirmButton: 'btn-success' }
                    });

                    // Background Refresh for saves/edits
                    $('.dataTable').each(function() {
                        if ($.fn.dataTable.isDataTable(this)) {
                            $(this).DataTable().ajax.reload(null, false);
                        }
                    });

                    setTimeout(function(){
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// 4. Global Modal Fix
$('#researchconductedModal').on('hidden.bs.modal', function() {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }
    $('#researcherModala .modal-body').scrollTop(0);
});

// 5. Handle Add Button for Research Conducted Tab
$('#add_researcherconducted').click(function() {
    $('#researchconducted_form')[0].reset();
    $('#researchconducted_form').parsley().reset();
    
    // Reset SDGs
    $('#sdgs').val([]).trigger('change');  
    if($.fn.selectpicker) { $('#sdgs').selectpicker('refresh'); }
    
    // Reset Collaborators
    if ($('#collaborators').length) {
        $('#collaborators').val(null).trigger('change');
    }

    // Reset Files UI
    $('#has_files').val('None').trigger('change');
    $('#new_files_container').html('');
    $('#existing_files_container').html('');

    $('#modal_title').text('Add Research Conducted');
    $('#action_researchedconducted').val('Add');
    $('#submit_button_researchedconducted').val('Add');
    
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');  
    $('#hiddeny').val(rid);
    
    // Auto-select lead researcher if adding from a profile
    if(rid) { $('#lead_researcher_id').val(rid); }

    $('#researchconductedModal').modal('show');
    $('#form_message').html('');
});

// 6. Handle Edit Button (Populating the Modal)
$(document).on('click', '.edit_button_researchconducted', function(e){
    e.preventDefault();
    var rcid = $(this).data('id');

    $('#researchconducted_form')[0].reset();
    $('#researchconducted_form').parsley().reset();
    $('#form_message').html('');
    $('#new_files_container').html('');
    $('#existing_files_container').html('');

    $.ajax({
        url:"actions/researchconducted_action.php",
        method:"POST",
        data:{rcid:rcid,action_researchedconducted:'fetch_single'},
        dataType:'JSON',
        success:function(data)
        {
            $('#started_date').val(data.started_date);
            $('#completed_date').val(data.completed_date);
            $('#title').val(data.title);
            $('#research_agenda_cluster').val(data.research_agenda_cluster);
            $('#lead_researcher_id').val(data.lead_researcher_id).trigger('change');
            // Handle SDGs array
            if(data.sdgs) {
                var sdgsArray = data.sdgs.split(", ");  
                $('#sdgs').val(sdgsArray).trigger('change');  
                if($.fn.selectpicker) { $('#sdgs').selectpicker('refresh'); }
            }
            
            // Handle Collaborators Array via Select2
            if(data.collaborators && $('#collaborators').length) {
                var filteredCollabs = data.collaborators.filter(function(id) {
                    return id != data.lead_researcher_id;
                });
                $('#collaborators').val(filteredCollabs).trigger('change');
            }
            
            // Trigger change to ensure the Lead is disabled visually
            $('#lead_researcher_id').trigger('change');
            
            $('#funding_source').val(data.funding_source);
            $('#approved_budget').val(data.approved_budget);
            $('#stat').val(data.stat);
            
            // Handle Files
            $('#has_files').val(data.has_files).trigger('change');
            
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
                $('#existing_files_container').html(filesHtml);
            }

            $('#modal_title').text('Edit Project & Collaborators');
            $('#action_researchedconducted').val('Edit');
            $('#submit_button_researchedconducted').val('Edit');
            $('#researchconductedModal').modal('show');
            $('#hidden_id_researchedconducted').val(rcid);
        }
    });
});

// 7. Handle Delete Button (INSTANT UI REMOVAL)
$(document).on('click', '.delete_button_researchconducted, .delete_buttonrc, .delete_master_researchconducted', function(e) {
    e.preventDefault();
    var btn = $(this);
    var xid = btn.data('id');
    var targetRow = btn.closest('tr'); // Capture the exact row to hide

    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the project, its collaborators, and all attached files!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/researchconducted_action.php",
                method: "POST",
                data: { xid: xid, action_researchedconducted: 'delete' },
                dataType: "json", 
                success: function(data) {
                    if (data && data.status === 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The record has been successfully deleted.',
                            icon: 'success',
                            timer: 800, 
                            showConfirmButton: false, 
                        });

                        // SILENTLY HIDE THE ROW INSTANTLY
                        targetRow.fadeOut(400, function() {
                            $(this).remove();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Database Error!',
                        text: xhr.responseText,
                        icon: 'error'
                    });
                }
            });
        }
    });
});

// --- NEW DYNAMIC FILE UPLOAD LOGIC ---

// Toggle File Section visibility
$(document).on('change', '#has_files', function() {
    if($(this).val() === 'With') {
        $('#dynamic_files_section').slideDown(200);
    } else {
        $('#dynamic_files_section').slideUp(200);
        // Clear un-uploaded new files if user switches back to "None"
        $('#new_files_container').empty();
    }
});

// Add New File Row
$(document).on('click', '#add_file_btn', function() {
    var fileRow = `
        <div class="row align-items-center mb-2 new-file-row">
            <div class="col-md-4">
                <select name="file_categories[]" class="form-control form-control-sm" required>
                    <option value="">Select Category</option>
                    <option value="SO">SO</option>
                    <option value="MOA">MOA</option>
                    <option value="Terminal Report">Terminal Report</option>
                    <option value="PSE-PES">PSE-PES</option>
                    <option value="Financial Report">Financial Report</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="file" name="research_files[]" class="form-control-file border p-1 rounded bg-white" required accept=".pdf,.doc,.docx,.jpg,.png,.xlsx">
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-sm btn-danger remove-new-file"><i class="fas fa-times"></i></button>
            </div>
        </div>
    `;
    $('#new_files_container').append(fileRow);
});

// Remove un-uploaded file row
$(document).on('click', '.remove-new-file', function() {
    $(this).closest('.new-file-row').remove();
});

// Delete Existing Server File via AJAX
$(document).on('click', '.delete-existing-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#file_row_' + fileId);
    
    Swal.fire({
        title: 'Delete this file?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            $.ajax({
                url: "actions/researchconducted_action.php",
                method: "POST",
                data: { action_researchedconducted: 'delete_file', file_id: fileId },
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
});

// 8. Function to Load Publication Tab Data
function loadPublicationTab(researcherID) {
    $('#publication_form').parsley();
    if ($.fn.dataTable.isDataTable('#publication_table')) {
        $('#publication_table').DataTable().clear().destroy();
    }

    var publicationTable = $('#publication_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/publication_action.php",
            type: "POST",
            data: {rid: researcherID, action_publication: 'fetch'}
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return publicationTable;
}

$('#publicationModal').on('shown.bs.tab', function() {
    var id = $('#hidden_id_rd').val(); 
    loadPublicationTab(id); 
});