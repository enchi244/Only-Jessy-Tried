$(document).ready(function() {
    if ($('#collaborators_ip').length) {
        $('#collaborators_ip').select2({
            placeholder: "Select Co-Authors",
            allowClear: true,
            dropdownParent: $('#intellectualpropModal')
        });
    }

    $(document).on('change', '#lead_researcher_id_ip', function() {
        var selectedLead = $(this).val();
        $('#collaborators_ip option').prop('disabled', false); 
        
        if (selectedLead) {
            $('#collaborators_ip option[value="' + selectedLead + '"]').prop('disabled', true);
            var currentCollabs = $('#collaborators_ip').val() || [];
            var newCollabs = currentCollabs.filter(function(val) { return val != selectedLead; });
            $('#collaborators_ip').val(newCollabs);
        }
        $('#collaborators_ip').trigger('change.select2'); 
    });
});

function loadIntellectualPropTab(researcherID) {
    $('#intellectualprop_form').parsley();
    if ($.fn.dataTable.isDataTable('#intellectualprop_table')) {
        $('#intellectualprop_table').DataTable().clear().destroy();
    }

    var intellectualPropTable = $('#intellectualprop_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/intellectualprop_action.php",
            type: "POST",
            data: { rid: researcherID, action_intellectualprop: 'fetch' }
        },
        "columnDefs": [{ "targets": [0], "orderable": false }],
    });
    return intellectualPropTable;
}

$('#intellectualpropModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); 
    loadIntellectualPropTab(id);  
});

$('#intellectualprop_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#intellectualprop_form').parsley().isValid()) {
        var formData = new FormData(this);
        $.ajax({
            url: "actions/intellectualprop_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_intellectualprop').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_intellectualprop').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_intellectualprop').val($('#action_intellectualprop').val());
                } else {
                    $('#intellectualpropModal').modal('hide');
                    var Svalue = $('#action_intellectualprop').val();
                    Swal.fire({ title: Svalue == "Add" ? 'Added!' : 'Updated!', text: 'The intellectual property has been successfully saved.', icon: 'success', timer: 800, showConfirmButton: false, customClass: { confirmButton: 'btn-success' }});

                    // --- UPGRADED AUTO-REFRESH LOGIC ---
                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ 
                            var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
                            window.location.href = window.location.pathname + '?id=' + rid + '&tab=ip';
                        }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { loadIntellectualPropTab(researcherID); }
                    }
                    // -----------------------------------
                }
            }
        });
    }
});

$('#intellectualpropModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

$('#add_intellectualprop').click(function(){
    $('#intellectualprop_form')[0].reset();
    
    // (If you use Parsley validation, reset it here)
    if ($('#intellectualprop_form').parsley) {
        $('#intellectualprop_form').parsley().reset();
    }
    
    $('#modal_title_ip').html('<div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-lightbulb"></i></div> Add Intellectual Property');
    $('#action_intellectualprop').val('Add');
    $('#intellectualpropModal .cover-photo-input').val('');
    $('#intellectualpropModal .preview-img').attr('src', '../../img/default_research_cover.png');
    $('#intellectualpropModal .cover-photo-preview').show();
    $('#submit_button_intellectualprop').val('Save Data');
    $('#form_message_ip').html('');

    // ==========================================
    // AUTO-FILL CURRENT RESEARCHER
    // ==========================================
    var urlParams = new URLSearchParams(window.location.search);
    var currentResearcherId = urlParams.get('id'); 
    
    if (currentResearcherId) {
        // Corrected ID to match your specific PHP modal!
        $('#lead_researcher_id_ip').val(currentResearcherId).trigger('change');
    }
    // ==========================================

    $('#intellectualpropModal').modal('show');
});

$(document).on('click', '.edit_button_intellectualprop', function () {
    var intellectualPropID = $(this).data('id');  
    $('#intellectualprop_form')[0].reset();
    $('#intellectualprop_form').parsley().reset();
    $('#form_message').html('');
    
    // UPDATED to use classes for the universal widget
    $('#intellectualpropModal .new-files-container').html('');
    $('#intellectualpropModal .existing-files-container').html('');
    $('#dynamic_links_container_ip').html('');

    $.ajax({
        url: "actions/intellectualprop_action.php",
        method: "POST",
        data: { intellectualPropID: intellectualPropID, action_intellectualprop: 'fetch_single' },
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

            $('#title_ip').val(data.title);
            $('#type_ip').val(data.type);
            $('#date_applied').val(parseLegacyDate(data.date_applied));
            $('#date_granted').val(parseLegacyDate(data.date_granted));
            
            $('#lead_researcher_id_ip').val(data.lead_researcher_id).trigger('change');

            if(data.collaborators && $('#collaborators_ip').length) {
                var filteredCollabs = data.collaborators.filter(function(id) { return id != data.lead_researcher_id; });
                $('#collaborators_ip').val(filteredCollabs).trigger('change');
            }

            if(data.a_link && data.a_link.trim() !== '') {
                var links = data.a_link.split("\n");
                links.forEach(function(link) {
                    if(link.trim() !== '') {
                        var linkRow = `
                            <div class="d-flex mb-2 link-row-ip">
                                <input type="text" name="a_link_ip[]" class="form-control mr-2" value="${link.trim()}" placeholder="Paste link here (e.g. https://...)" />
                                <button type="button" class="btn btn-danger remove-link-btn-ip"><i class="fas fa-times"></i></button>
                            </div>
                        `;
                        $('#dynamic_links_container_ip').append(linkRow);
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
                // UPDATED to use class for the universal widget
                $('#intellectualpropModal .existing-files-container').html(filesHtml);
            }

            // Load existing cover photo
            $('#intellectualpropModal .preview-img').attr('src', '../../' + data.cover_photo);
            $('#intellectualpropModal .cover-photo-preview').show();
            $('#modal_title').text('Edit Intellectual Property');
            $('#action_intellectualprop').val('Edit');
            $('#submit_button_intellectualprop').val('Edit');
            $('#intellectualpropModal').modal('show');
            $('#hidden_intellectualPropID').val(intellectualPropID);
        }
    });
});

$(document).on('click', '.delete_button_intellectualprop', function (e) {
    e.preventDefault();
    var intellectualPropID = $(this).data('id');  
    Swal.fire({
        title: 'Are you sure?', text: 'This will delete the IP, authors, and attached files!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, keep it', reverseButtons: true, customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/intellectualprop_action.php",
                method: "POST",
                data: { intellectualPropID: intellectualPropID, action_intellectualprop: 'delete' },
                success: function (data) {
                    Swal.fire({ title: 'Deleted!', text: 'The intellectual property has been successfully deleted.', icon: 'success', timer: 800, showConfirmButton: false });
                    
                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');
                        if(researcherID) { loadIntellectualPropTab(researcherID); } else { location.reload(); }
                    }
                }
            });
        }
    });
});

// --- DYNAMIC LINKS LOGIC ---
$(document).on('click', '#add_new_link_btn_ip', function() {
    var linkRow = `
        <div class="d-flex mb-2 link-row-ip">
            <input type="text" name="a_link_ip[]" class="form-control mr-2" placeholder="Paste link here (e.g. https://...)" />
            <button type="button" class="btn btn-danger remove-link-btn-ip"><i class="fas fa-times"></i></button>
        </div>
    `;
    $('#dynamic_links_container_ip').append(linkRow);
});

$(document).on('click', '.remove-link-btn-ip', function() {
    $(this).closest('.link-row-ip').remove();
});

// Delete Existing Server File via AJAX
$(document).on('click', '.delete-existing-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#file_row_' + fileId);
    
    // Check if we're inside the IP modal to prevent interfering with other modules
    if(btn.closest('#intellectualpropModal').length > 0) {
        Swal.fire({
            title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: "actions/intellectualprop_action.php",
                    method: "POST",
                    data: { action_intellectualprop: 'delete_file', file_id: fileId },
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