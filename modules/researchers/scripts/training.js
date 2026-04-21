function loadTrainingsAttendedTab(researcherID) {
    $('#trainings_attended_form').parsley();
    if ($.fn.dataTable.isDataTable('#trainings_attended_table')) {
        $('#trainings_attended_table').DataTable().clear().destroy();
    }

    var trainingsAttendedTable = $('#trainings_attended_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/trainings_attended_action.php",
            type: "POST",
            data: { rid: researcherID, action_training: 'fetch' }
        },
        "columnDefs": [{ "targets": [0], "orderable": false }],
    });
    return trainingsAttendedTable;
}

$('#tra-tab').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); 
    loadTrainingsAttendedTab(id);  
});

// Submit using FormData
$('#trainings_attended_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#trainings_attended_form').parsley().isValid()) {
        var formData = new FormData(this);
        $.ajax({
            url: "actions/trainings_attended_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_training').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_training').attr('disabled', false);
                if (data.error && data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_training').val($('#action_training').val());
                } else {
                    $('#trainingsAttendedModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_training').val();
                    Swal.fire({
                        title: Svalue == "Add" ? 'Added!' : 'Updated!',
                        text: 'The training has been successfully saved.',
                        icon: 'success',
                        timer: 800,  
                        showConfirmButton: false,  
                        customClass: { confirmButton: 'btn-success' }
                    });

                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');  
                        if(researcherID) { loadTrainingsAttendedTab(researcherID); }
                    }

                    setTimeout(function () { $('#message').html(''); }, 5000);
                }
            },
            error: function(xhr, status, error) {
                $('#submit_button_training').attr('disabled', false).val($('#action_training').val());
                Swal.fire({
                    title: 'Server Error',
                    text: 'An error occurred while saving. Check the console.',
                    icon: 'error'
                });
                console.error(xhr.responseText);
            }
        });
    }
});

$('#trainingsAttendedModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

$('#add_training_attended').click(function () {
    $('#trainings_attended_form')[0].reset();  
    $('#trainings_attended_form').parsley().reset();  

    // UPDATED to use classes for the universal widget
    $('#trainingsAttendedModal .new-files-container').html('');
    $('#trainingsAttendedModal .existing-files-container').html('');
    $('#dynamic_links_container_training').html('');

    $('#modal_title').text('Add Trainings Attended');  
    $('#action_training').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_training').val(rid);  
    $('#submit_button_training').val('Add');
    $('#trainingsAttendedModal').modal('show');  
    $('#form_message').html('');
});

$(document).on('click', '.edit_button_training', function () {
    var trainingID = $(this).data('id');  
    $('#trainings_attended_form')[0].reset();
    $('#trainings_attended_form').parsley().reset();
    $('#form_message').html('');
    
    // UPDATED to use classes for the universal widget
    $('#trainingsAttendedModal .new-files-container').html('');
    $('#trainingsAttendedModal .existing-files-container').html('');
    $('#dynamic_links_container_training').html('');

    $.ajax({
        url: "actions/trainings_attended_action.php",
        method: "POST",
        data: { trainingID: trainingID, action_training: 'fetch_single' },
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

            $('#title_training').val(data.title);
            $('#type_training').val(data.type);
            $('#venue_training').val(data.venue);
            $('#date_training').val(parseLegacyDate(data.date_train));
            $('#level_training').val(data.lvl);
            $('#type_learning_dev').val(data.type_learning_dev);
            $('#sponsor_org').val(data.sponsor_org);
            $('#total_hours_training').val(data.totnh);

            if(data.a_link && data.a_link.trim() !== '') {
                var links = data.a_link.split("\n");
                links.forEach(function(link) {
                    if(link.trim() !== '') {
                        var linkRow = `
                            <div class="d-flex mb-2 link-row-training">
                                <input type="text" name="a_link_training[]" class="form-control mr-2" value="${link.trim()}" placeholder="Paste link here (e.g. https://...)" />
                                <button type="button" class="btn btn-danger remove-link-btn-training"><i class="fas fa-times"></i></button>
                            </div>
                        `;
                        $('#dynamic_links_container_training').append(linkRow);
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
                $('#trainingsAttendedModal .existing-files-container').html(filesHtml);
            }

            $('#modal_title').text('Edit Trainings Attended');
            $('#action_training').val('Edit');
            $('#submit_button_training').val('Edit');
            $('#trainingsAttendedModal').modal('show');
            $('#hidden_trainingID').val(trainingID);
        }
    });
});

// Handle Delete Full Training
$(document).on('click', '.delete_button_training, .delete_master_training', function (e) {
    e.preventDefault();
    var trainingID = $(this).data('id');  
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
                url: "actions/trainings_attended_action.php",
                method: "POST",
                data: { trainingID: trainingID, action_training: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The training has been successfully deleted.',
                        icon: 'success',
                        timer: 800,
                        showConfirmButton: false,
                    });

                    if (window.location.href.indexOf("view_researcher.php") > -1) {
                        setTimeout(function(){ location.reload(); }, 800);
                    } else {
                        var researcherID = $('#researcherModala').data('id');
                        if(researcherID) { loadTrainingsAttendedTab(researcherID); } else { location.reload(); }
                    }
                }
            });
        }
    });
});

// --- DYNAMIC LINKS LOGIC ---
$(document).on('click', '#add_new_link_btn_training', function() {
    var linkRow = `
        <div class="d-flex mb-2 link-row-training">
            <input type="text" name="a_link_training[]" class="form-control mr-2" placeholder="Paste link here (e.g. https://...)" />
            <button type="button" class="btn btn-danger remove-link-btn-training"><i class="fas fa-times"></i></button>
        </div>
    `;
    $('#dynamic_links_container_training').append(linkRow);
});

$(document).on('click', '.remove-link-btn-training', function() {
    $(this).closest('.link-row-training').remove();
});

// Delete Existing Server File via AJAX
$(document).on('click', '.delete-existing-file', function(e) {
    e.preventDefault();
    var btn = $(this);
    var fileId = btn.attr('data-file-id');
    var row = $('#file_row_' + fileId);
    
    // Scoped specifically to the Trainings Modal to avoid crossover
    if(btn.closest('#trainingsAttendedModal').length > 0) {
        Swal.fire({
            title: 'Delete this file?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: "actions/trainings_attended_action.php",
                    method: "POST",
                    data: { action_training: 'delete_file', file_id: fileId },
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