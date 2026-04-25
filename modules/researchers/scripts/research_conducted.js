$(document).ready(function() {
    if ($('#collaborators').length) {
        $('#collaborators').select2({
            placeholder: "Select Co-Researchers / Collaborators",
            allowClear: true,
            dropdownParent: $('#researchconductedModal')
        });
    }

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

$('#researchconductedTab').on('shown.bs.tab', function() {
    var id = $('#hidden_id_rd').val(); 
    loadResearchConductedTab(id);  
});

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
                
                if(data.error && data.error != '') {
                    $('#form_message').html('<div class="alert alert-danger">' + data.error + '</div>');
                    $('#submit_button_researchedconducted').val($('#action_researchedconducted').val());
                } else if(data.success) {
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

                    // --- NEW AUTO-REFRESH LOGIC ---
                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ 
                            var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
                            window.location.href = window.location.pathname + '?id=' + rid + '&tab=education';
                        }, 800);
                    } else {
                        $('.dataTable').each(function() {
                            if ($.fn.dataTable.isDataTable(this)) {
                                $(this).DataTable().ajax.reload(null, false);
                            }
                        });
                    }
                    // ------------------------------

                    setTimeout(function(){
                        $('#message').html('');
                    }, 5000);
                } else {
                    Swal.fire('Error', 'Unexpected server response.', 'error');
                    $('#submit_button_researchedconducted').val($('#action_researchedconducted').val());
                }
            },
            error: function(xhr, status, error) {
                $('#submit_button_researchedconducted').attr('disabled', false).val($('#action_researchedconducted').val());
                Swal.fire({
                    title: 'Server Error (500)',
                    text: 'A fatal PHP error occurred. Check your network tab.',
                    icon: 'error'
                });
                console.error("Submission Error:", xhr.responseText);
            }
        });
    }
});

$('#researchconductedModal').on('hidden.bs.modal', function() {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }
    $('#researcherModala .modal-body').scrollTop(0);
});

$('#add_researcherconducted').click(function() {
    $('#researchconducted_form')[0].reset();
    $('#researchconducted_form').parsley().reset();
    
    // FIX: Properly reset Select2 UIs visually on Add
    $('#sdgs').val([]).trigger('change');  
    if($.fn.selectpicker) { 
        $('#sdgs').selectpicker('refresh'); 
    }
    
    if ($('#collaborators').length) {
        $('#collaborators').val(null).trigger('change');
    }

    $('#researchconductedModal .new-files-container').html('');
    $('#researchconductedModal .existing-files-container').html('');

    $('#modal_title').text('Add Research Conducted');
    $('#action_researchedconducted').val('Add');
    $('#submit_button_researchedconducted').val('Add');
    
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');  
    $('#hiddeny').val(rid);
    
    if(rid) { 
        $('#lead_researcher_id').val(rid).trigger('change'); 
    }

    $('#researchconductedModal').modal('show');
    $('#form_message').html('');
});

$(document).on('click', '.edit_button_researchconducted', function(e){
    e.preventDefault();
    var rcid = $(this).data('id');

    $('#researchconducted_form')[0].reset();
    $('#researchconducted_form').parsley().reset();
    $('#form_message').html('');
    
    $('#researchconductedModal .new-files-container').html('');
    $('#researchconductedModal .existing-files-container').html('');

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
            
            // FIX: Force the UI to update with .trigger('change')
            $('#research_agenda_cluster').val(data.research_agenda_cluster);

            $('#lead_researcher_id').val(data.lead_researcher_id).trigger('change');
            
            if(data.sdgs) {
                var sdgsArray = data.sdgs.split(", ");  
                $('#sdgs').val(sdgsArray).trigger('change');  
                if($.fn.selectpicker) { $('#sdgs').selectpicker('refresh'); }
            }
            
            if(data.collaborators && $('#collaborators').length) {
                var filteredCollabs = data.collaborators.filter(function(id) {
                    return id != data.lead_researcher_id;
                });
                $('#collaborators').val(filteredCollabs).trigger('change');
            }
            
            $('#lead_researcher_id').trigger('change');
            
            $('#funding_source').val(data.funding_source);
            $('#approved_budget').val(data.approved_budget);
            $('#stat').val(data.stat);
            
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
                $('#researchconductedModal .existing-files-container').html(filesHtml);
            }

            $('#modal_title').html('<div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-pencil-alt"></i></div>Edit Project & Collaborators');
            $('#action_researchedconducted').val('Edit');
            $('#submit_button_researchedconducted').val('Save Changes');
            $('#researchconductedModal').modal('show');
            $('#hidden_id_researchedconducted').val(rcid);
        }
    });
});

$(document).on('click', '.delete_button_researchconducted, .delete_buttonrc, .delete_master_researchconducted', function(e) {
    e.preventDefault();
    var btn = $(this);
    var xid = btn.data('id');
    var targetRow = btn.closest('tr');

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