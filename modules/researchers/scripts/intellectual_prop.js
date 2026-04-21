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

                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { loadIntellectualPropTab(researcherID); }
                    }
                }
            }
        });
    }
});

$('#intellectualpropModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

$('#add_intellectualprop').click(function () {
    $('#intellectualprop_form')[0].reset();  
    $('#intellectualprop_form').parsley().reset();  

    if ($('#collaborators_ip').length) { $('#collaborators_ip').val(null).trigger('change'); }
    $('#new_files_container_ip').html('');
    $('#existing_files_container_ip').html('');
    $('#dynamic_links_container_ip').html('');

    $('#modal_title').text('Add Intellectual Property');  
    $('#action_intellectualprop').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_ip').val(rid);  
    if(rid) { $('#lead_researcher_id_ip').val(rid).trigger('change'); }
    
    $('#submit_button_intellectualprop').val('Add');
    $('#intellectualpropModal').modal('show');  
    $('#form_message').html('');
});

$(document).on('click', '.edit_button_intellectualprop', function () {
    var intellectualPropID = $(this).data('id');  
    $('#intellectualprop_form')[0].reset();
    $('#intellectualprop_form').parsley().reset();
    $('#form_message').html('');
    $('#new_files_container_ip').html('');
    $('#existing_files_container_ip').html('');
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

            $('#has_files_ip').val(data.has_files).trigger('change');
            
            if(data.existing_files && data.existing_files.length > 0) {
                var filesHtml = '';
                data.existing_files.forEach(function(f) {
                    filesHtml += `
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 border rounded shadow-sm" id="ip_file_row_${f.id}">
                            <div>
                                <span class="badge badge-info mr-2">${f.category}</span>
                                <a href="${f.path}" target="_blank" class="text-gray-800 font-weight-bold">${f.name}</a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-existing-ip-file" data-file-id="${f.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                });
                $('#existing_files_container_ip').html(filesHtml);
            }

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

$(document).on('click', '#add_file_btn_ip', function() {
    var fileRow = `
        <div class="row align-items-center mb-2 new-file-row">
            <div class="col-md-4">
                <select name="ip_file_categories[]" class="form-control form-control-sm" required>
                    <option value="">Select Category</option>
                    <option value="Certificate">Certificate</option>
                    <option value="Application Document">Application Document</option>
                    <option value="MOA">MOA</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
<input type="file" name="ip_files[]" class="form-control-file border p-1 rounded bg-white" required accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" multiple>            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-sm btn-danger remove-new-ip-file"><i class="fas fa-times"></i></button>
            </div>
        </div>
    `;
    $('#new_files_container_ip').append(fileRow);
});

$(document).on('click', '.remove-new-ip-file', function() {
    $(this).closest('.new-file-row').remove();
});

$(document).on('click', '.delete-existing-ip-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#ip_file_row_' + fileId);
    
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
});