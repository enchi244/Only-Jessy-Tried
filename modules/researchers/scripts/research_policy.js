$(document).ready(function() {
    
    // Initialize Select2
    if ($('#policy_research_conducted').length) {
        $('#policy_research_conducted').select2({
            dropdownParent: $('#policyModal'),
            placeholder: "Search and select a research...",
            allowClear: true
        });
    }

    function loadResearchDropdown(researcherID, selectedVal = '') {
        $.ajax({
            url: "actions/researchpolicy_action.php",
            method: "POST",
            data: { action_policy: 'fetch_researches', researcher_id: researcherID },
            success: function(data) {
                $('#policy_research_conducted').html(data);
                if(selectedVal !== '') {
                    $('#policy_research_conducted').val(selectedVal).trigger('change');
                }
            }
        });
    }

    // ADD Button triggered by FAB
    $('#add_policy').click(function() {
        $('#policy_form')[0].reset();
        $('#policy_form').parsley().reset();
        
        // Reset File Widgets
        $('#policyModal .existing-files-container').html('');
        $('#policyModal .new-files-container').html('');
        
        var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
        $('#policy_researcher_id').val(rid);
        $('#policy_research_conducted').val('').trigger('change');
        
        loadResearchDropdown(rid);

        $('#modal_title_policy').html('<i class="fas fa-file-contract mr-2"></i> Add Research Policy');
        $('#action_policy').val('Add');
        $('#submit_button_policy').val('Add');
        $('#policyModal').modal('show');
    });

    // EDIT Button (Card Click)
    $(document).on('click', '.edit_button_policy', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var rid = $('#hidden_id_rd').val();

        $('#policy_form')[0].reset();
        $('#policy_form').parsley().reset();
        $('#policyModal .new-files-container').html('');

        $.ajax({
            url: "actions/researchpolicy_action.php",
            method: "POST",
            data: { id: id, action_policy: 'fetch_single' },
            dataType: 'JSON',
            success: function(data) {
                $('#policy_title').val(data.title);
                $('#policy_abstract').val(data.abstract).trigger('input');
                $('#policy_description').val(data.description);
                $('#policy_date').val(data.date_implemented);
                $('#hidden_id_policy').val(id);
                $('#policy_researcher_id').val(rid);
                
                loadResearchDropdown(rid, data.research_conducted_id);

                // Render Existing Files
                if(data.existing_files && data.existing_files.length > 0) {
                    var filesHtml = '';
                    data.existing_files.forEach(function(f) {
                        filesHtml += `
                            <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 border rounded shadow-sm" id="file_row_${f.id}">
                                <div>
                                    <span class="badge badge-info mr-2">${f.category}</span>
                                    <a href="../../uploads/documents/${f.name}" target="_blank" class="text-gray-800 font-weight-bold">${f.name}</a>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-existing-file" data-file-id="${f.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    });
                    $('#policyModal .existing-files-container').html(filesHtml);
                } else {
                    $('#policyModal .existing-files-container').html('');
                }

                $('#modal_title_policy').html('<i class="fas fa-edit mr-2"></i> Edit Research Policy');
                $('#action_policy').val('Edit');
                $('#submit_button_policy').val('Save Changes');
                $('#policyModal').modal('show');
            }
        });
    });

    // SUBMIT Form
    $('#policy_form').on('submit', function(event) {
        event.preventDefault();
        if ($('#policy_form').parsley().isValid()) {
            $.ajax({
                url: "actions/researchpolicy_action.php",
                method: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_button_policy').attr('disabled', 'disabled').val('Wait...');
                },
                success: function(data) {
                    $('#submit_button_policy').attr('disabled', false);
                    if (data.error) {
                        Swal.fire('Form Error', data.error, 'error');
                        $('#submit_button_policy').val($('#action_policy').val());
                    } else {
                        $('#policyModal').modal('hide');
                        var Svalue = $('#action_policy').val();
                        Swal.fire({ title: Svalue == "Add" ? 'Added!' : 'Updated!', text: 'The research policy has been successfully saved.', icon: 'success', timer: 800, showConfirmButton: false});

                        setTimeout(function(){ 
                            var rid = $('#hidden_id_rd').val() || new URLSearchParams(window.location.search).get('id');
                            window.location.href = window.location.pathname + '?id=' + rid + '&tab=policy';
                        }, 800);
                    }
                },
                // --- NEW ERROR HANDLER (Prevents Infinite "Wait...") ---
                error: function(xhr, status, error) {
                    $('#submit_button_policy').attr('disabled', false).val($('#action_policy').val());
                    Swal.fire({
                        title: 'Database or Server Error',
                        text: 'An error occurred. Check the browser console (F12) for the exact PHP error.',
                        icon: 'error'
                    });
                    console.error("AJAX Error Details:", xhr.responseText);
                }
            });
        }
    });

    // DELETE Main Record
    $(document).on('click', '.delete_button_policy', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var targetRow = $(this).closest('.card');

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete this policy record.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#e74a3b'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "actions/researchpolicy_action.php",
                    method: "POST",
                    data: { id: id, action_policy: 'delete' },
                    success: function() {
                        Swal.fire({title: 'Deleted!', icon: 'success', timer: 800, showConfirmButton: false});
                        targetRow.fadeOut(400, function() { $(this).remove(); });
                    }
                });
            }
        });
    });

    // DELETE Specific Inner File
    $(document).on('click', '.delete-existing-file', function(e) {
        e.preventDefault();
        var btn = $(this);
        var fileId = btn.attr('data-file-id');
        var row = $('#file_row_' + fileId);
        
        // THE FIX: Scope this strictly to the Policy Modal
        if(btn.closest('#policyModal').length > 0) {
            Swal.fire({
                title: 'Delete this file?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                    $.ajax({
                        url: "actions/researchpolicy_action.php",
                        method: "POST",
                        data: { action_policy: 'delete_file', file_id: fileId },
                        dataType: "json",
                        success: function(data) {
                            if(data.status === 'success') {
                                row.fadeOut(300, function() { $(this).remove(); });
                            } 
                        }
                    });
                }
            });
        }
    });
});


// Live Word Counter for Policy Abstract
$(document).on('input', '#abstract', function() {
    let text = $(this).val().trim();
    let wordCount = text.length > 0 ? text.split(/\s+/).length : 0;
    $('#policy_word_count').text(wordCount);
});