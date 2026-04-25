$(document).ready(function() {
    if ($('#collaborators_pub').length) {
        $('#collaborators_pub').select2({
            placeholder: "Select Co-Authors",
            allowClear: true,
            dropdownParent: $('#publicationModal')
        });
    }

    // Prevent Lead Author from being selected as a Co-Author
    $(document).on('change', '#lead_author_id', function() {
        var selectedLead = $(this).val();
        $('#collaborators_pub option').prop('disabled', false); 
        
        if (selectedLead) {
            $('#collaborators_pub option[value="' + selectedLead + '"]').prop('disabled', true);
            var currentCollabs = $('#collaborators_pub').val() || [];
            var newCollabs = currentCollabs.filter(function(val) { return val != selectedLead; });
            $('#collaborators_pub').val(newCollabs);
        }
        $('#collaborators_pub').trigger('change.select2'); 
    });
});

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
            data: { rid: researcherID, action_publication: 'fetch' }
        },
        "columnDefs": [{ "targets": [0], "orderable": false }],
    });
    return publicationTable;
}

$('#degree-tab').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); 
    loadPublicationTab(id); 
});

// Handle Form Submit (Using FormData for Files)
$('#publication_form').on('submit', function(event) {
    event.preventDefault();
    if ($('#publication_form').parsley().isValid()) {
        var formData = new FormData(this);
        $.ajax({
            url: "actions/publication_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#submit_button_publication').attr('disabled', 'disabled').val('Wait...');
            },
            success: function(data) {
                $('#submit_button_publication').attr('disabled', false);
                if(data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_publication').val($('#action_publication').val());
                } else {
                    $('#publicationModal').modal('hide');
                    var Svalue = $('#action_publication').val();
                    Swal.fire({ title: Svalue == "Add" ? 'Added!' : 'Updated!', text: 'The publication has been successfully saved.', icon: 'success', timer: 800, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    
                    // --- NEW AUTO-REFRESH LOGIC ---
                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ 
                            var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
                            window.location.href = window.location.pathname + '?id=' + rid + '&tab=degree';
                        }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { loadPublicationTab(researcherID); }
                    }
                    // ------------------------------

                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            }
        });
    }
});

$('#publicationModal').on('hidden.bs.modal', function() {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Publication
// Add New Publication
$('#add_publication').click(function () {
    $('#publication_form')[0].reset();  
    $('#publication_form').parsley().reset();  
    
    if ($('#collaborators_pub').length) { $('#collaborators_pub').val(null).trigger('change'); }
    
    // UPDATED to use classes for the universal widget
    $('#publicationModal .new-files-container').html('');
    $('#publicationModal .existing-files-container').html('');

    $('#modal_title').text('Add Publication');  
    $('#action_publication').val('Add');
    
    // FIX: Safely grab the researcher ID from multiple potential sources
    var rid = $('#researcherModala').data('id') || $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');  
    
    $('#hidden_researcherID').val(rid); 
    
    // Trigger the change to automatically select the lead author
    if(rid) { 
        $('#lead_author_id').val(rid).trigger('change'); 
    }

    $('#submit_button_publication').val('Add');
    $('#publicationModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Publication
$(document).on('click', '.edit_button_publication', function(){
    var publicationID = $(this).data('id');
    $('#publication_form')[0].reset();
    $('#publication_form').parsley().reset();
    $('#form_message').html('');
    
    // UPDATED to use classes for the universal widget
    $('#publicationModal .new-files-container').html('');
    $('#publicationModal .existing-files-container').html('');

    $.ajax({
        url:"actions/publication_action.php",
        method:"POST",
        data:{publicationID: publicationID, action_publication: 'fetch_single'},
        dataType:'JSON',
        success:function(data) {
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
            
            $('#title_pub').val(data.title);
            $('#start').val(parseLegacyDate(data.start));
            $('#end').val(parseLegacyDate(data.end));
            $('#journal').val(data.journal);
            $('#vol_num_issue_num').val(data.vol_num_issue_num);
            $('#issn_isbn').val(data.issn_isbn);
            $('#indexing').val(data.indexing);
            $('#publication_date').val(parseLegacyDate(data.publication_date));
            $('#lead_author_id').val(data.lead_author_id).trigger('change');

            if(data.collaborators && $('#collaborators_pub').length) {
                var filteredCollabs = data.collaborators.filter(function(id) { return id != data.lead_author_id; });
                $('#collaborators_pub').val(filteredCollabs).trigger('change');
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
                $('#publicationModal .existing-files-container').html(filesHtml);
            }

            $('#modal_title').text('Edit Publication');
            $('#action_publication').val('Edit');
            $('#submit_button_publication').val('Edit');
            $('#publicationModal').modal('show');
            $('#hidden_publicationID').val(publicationID);
        }
    });
});

// Delete Publication
$(document).on('click', '.delete_button_publication', function(e) {
    e.preventDefault();
    var publicationID = $(this).data('id'); 
    Swal.fire({
        title: 'Are you sure?', text: 'This will delete the publication, authors, and attached files!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, keep it', reverseButtons: true, customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/publication_action.php",
                method: "POST",
                data: {publicationID: publicationID, action_publication: 'delete'},
                success: function(data) {
                    Swal.fire({ title: 'Deleted!', text: 'The publication has been successfully deleted.', icon: 'success', timer: 800, showConfirmButton: false });
                    var researcherID = $('#researcherModala').data('id');
                    loadPublicationTab(researcherID);  
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
    
    // Check if we're inside the Publication modal
    if(btn.closest('#publicationModal').length > 0) {
        Swal.fire({
            title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: "actions/publication_action.php",
                    method: "POST",
                    data: { action_publication: 'delete_file', file_id: fileId },
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