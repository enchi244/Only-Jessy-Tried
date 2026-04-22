// Function to Load Paper Presentation Data using the Researcher ID
function loadPaperPresentationTab(researcherID) {
    $('#paper_presentation_form').parsley();
    if ($.fn.dataTable.isDataTable('#paper_presentation_table')) {
        $('#paper_presentation_table').DataTable().clear().destroy();
    }

    var paperPresentationTable = $('#paper_presentation_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/paper_presentation_action.php",
            type: "POST",
            data: { rid: researcherID, action_paper_presentation: 'fetch' }
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return paperPresentationTable;
}

// Handle Tab Switching for Dynamic Content
$('#paperPresentationModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_pp').val(); 
    loadPaperPresentationTab(id);  
});

// Handle Form Submission using FormData for Files
$('#paper_presentation_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#paper_presentation_form').parsley().isValid()) {
        var formData = new FormData(this);
        
        $.ajax({
            url: "actions/paper_presentation_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_paper_presentation').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_paper_presentation').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_paper_presentation').val($('#action_paper_presentation').val());
                } else {
                    $('#paperPresentationModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_paper_presentation').val();
                    Swal.fire({
                        title: Svalue == "Add" ? 'Added!' : 'Updated!',
                        text: 'The paper presentation has been successfully saved.',
                        icon: 'success',
                        timer: 800,  
                        showConfirmButton: false,  
                        customClass: { confirmButton: 'btn-success' }
                    });

                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { loadPaperPresentationTab(researcherID); }
                    }

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#paperPresentationModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Paper Presentation
$('#add_paper_presentation').click(function () {
    $('#paper_presentation_form')[0].reset();  
    if ($('#paper_presentation_form').parsley) {
        $('#paper_presentation_form').parsley().reset();  
    }
    
    // Clear dynamic widgets
    $('#paperPresentationModal .new-files-container').html('');
    $('#paperPresentationModal .existing-files-container').html('');
    $('#dynamic_links_container').html('');

    $('#modal_title').html('<div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-microphone-alt"></i></div> Add Paper Presentation');  
    $('#action_paper_presentation').val('Add');
    
    // ==========================================
    // THE FIX: Bulletproof ID Fetcher
    // ==========================================
    // 1. Checks the dedicated hidden input on view_researcher.php
    // 2. Fallback: Checks the URL for ?id=123
    // 3. Fallback: Checks the Massive Edit Modal
    var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id') || $('#researcherModala').data('id');
    
    $('#hidden_researcherID_pp').val(rid);  
    // ==========================================

    $('#submit_button_paper_presentation').val('Save Data');
    $('#paperPresentationModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Paper Presentation
$(document).on('click', '.edit_button_paper_presentation', function () {
    var paperPresentationID = $(this).data('id');  

    $('#paper_presentation_form')[0].reset();
    $('#paper_presentation_form').parsley().reset();
    $('#form_message').html('');
    
    // UPDATED to use classes for universal widget
    $('#paperPresentationModal .new-files-container').html('');
    $('#paperPresentationModal .existing-files-container').html('');
    $('#dynamic_links_container').html('');

    $.ajax({
        url: "actions/paper_presentation_action.php",
        method: "POST",
        data: { paperPresentationID: paperPresentationID, action_paper_presentation: 'fetch_single' },
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

            $('#title_pp').val(data.title);
            $('#conference_title').val(data.conference_title);
            $('#conference_venue').val(data.conference_venue);
            $('#conference_organizer').val(data.conference_organizer);
            $('#date_paper').val(parseLegacyDate(data.date_paper));
            $('#type_pp').val(data.type);
            $('#discipline').val(data.discipline);

            if(data.a_link && data.a_link.trim() !== '') {
                var links = data.a_link.split("\n");
                links.forEach(function(link) {
                    if(link.trim() !== '') {
                        var linkRow = `
                            <div class="d-flex mb-2 link-row">
                                <input type="text" name="a_link[]" class="form-control mr-2" value="${link.trim()}" placeholder="Paste link here (e.g. https://...)" />
                                <button type="button" class="btn btn-danger remove-link-btn"><i class="fas fa-times"></i></button>
                            </div>
                        `;
                        $('#dynamic_links_container').append(linkRow);
                    }
                });
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
                $('#paperPresentationModal .existing-files-container').html(filesHtml);
            }

            $('#modal_title').text('Edit Paper Presentation');
            $('#action_paper_presentation').val('Edit');
            $('#submit_button_paper_presentation').val('Edit');
            $('#paperPresentationModal').modal('show');
            $('#hidden_paperPresentationID').val(paperPresentationID);
        }
    });
});

// Handle Delete Full Paper Presentation
$(document).on('click', '.delete_button_paper_presentation, .delete_master_paper_presentation', function (e) {
    e.preventDefault();
    var paperPresentationID = $(this).data('id');  
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
                url: "actions/paper_presentation_action.php",
                method: "POST",
                data: { paperPresentationID: paperPresentationID, action_paper_presentation: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The paper presentation has been successfully deleted.',
                        icon: 'success',
                        timer: 800,
                        showConfirmButton: false,
                    });

                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');
                        if(researcherID) { loadPaperPresentationTab(researcherID); } else { location.reload(); }
                    }
                }
            });
        }
    });
});

// --- DYNAMIC LINKS LOGIC ---
$(document).on('click', '#add_new_link_btn', function() {
    var linkRow = `
        <div class="d-flex mb-2 link-row">
            <input type="text" name="a_link[]" class="form-control mr-2" placeholder="Paste link here (e.g. https://...)" />
            <button type="button" class="btn btn-danger remove-link-btn"><i class="fas fa-times"></i></button>
        </div>
    `;
    $('#dynamic_links_container').append(linkRow);
});

$(document).on('click', '.remove-link-btn', function() {
    $(this).closest('.link-row').remove();
});

// Delete Existing Server File via AJAX
$(document).on('click', '.delete-existing-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#file_row_' + fileId);
    
    // Check if we're inside the PP modal to prevent interfering with other modules
    if(btn.closest('#paperPresentationModal').length > 0) {
        Swal.fire({
            title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: "actions/paper_presentation_action.php",
                    method: "POST",
                    data: { action_paper_presentation: 'delete_file', file_id: fileId },
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