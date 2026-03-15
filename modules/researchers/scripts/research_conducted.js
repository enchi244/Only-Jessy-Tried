$(document).ready(function() {
    // Initialize Select2 for Collaborators inside the Modal
    // The dropdownParent is required so the search box is clickable inside a Bootstrap Modal
    if ($('#collaborators').length) {
        $('#collaborators').select2({
            placeholder: "Select Co-Researchers / Collaborators",
            allowClear: true,
            dropdownParent: $('#researchconductedModal')
        });
    }
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
    var id = $('#hidden_id_rd').val(); // Get the ID from the hidden field in the modal
    loadResearchConductedTab(id);  // Load the content dynamically when the tab is shown
});

// 3. Handle Form Submission for Adding or Updating Research Conducted Data
$('#researchconducted_form').on('submit', function(event) {
    event.preventDefault();
    if ($('#researchconducted_form').parsley().isValid()) {
        $.ajax({
            url: "actions/researchconducted_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#submit_button_researchedconducted').attr('disabled', 'disabled').val('Wait...');
            },
            success: function(data) {
                $('#submit_button_researchedconducted').attr('disabled', false);
                if(data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_researchedconducted').val('Add');
                } else {
                    $('#researchconductedModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_researchedconducted').val();
                    if (Svalue == "Add") {
                        Swal.fire({
                            title: 'Added!',
                            text: 'The record has been successfully added.',
                            icon: 'success',
                            timer: 600,  
                            showConfirmButton: false,  
                            customClass: { confirmButton: 'btn-success' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The record has been successfully updated.',
                            icon: 'success',
                            timer: 600,  
                            showConfirmButton: false,  
                            customClass: { confirmButton: 'btn-success' }
                        });
                    }

                    // For profile page reload logic
                    var researcherID = $('#researcherModala').data('id'); 
                    if(researcherID) {
                        // Reload the page using native JS to see the new timeline cards
                        window.location.href = "view_researcher.php?id=" + researcherID + "&tab=education";
                    } else {
                        // If it's a datatable (e.g., Master view), reload it
                        loadResearchConductedTab(researcherID); 
                    }

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
    $('#sdgs').val([]);  
    $('#sdgs').trigger('change');  
    if($.fn.selectpicker) { $('#sdgs').selectpicker('refresh'); }
    
    // Reset Collaborators Multi-Select
    if ($('#collaborators').length) {
        $('#collaborators').val(null).trigger('change');
    }

    $('#modal_title').text('Add Researcher Conducted');
    $('#action_researchedconducted').val('Add');
    $('#submit_button_researchedconducted').val('Add');
    
    var rid = $('#researcherModala').data('id');  
    $('#hiddeny').val(rid);  
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
            
            // Handle SDGs array
            if(data.sdgs) {
                var sdgsArray = data.sdgs.split(", ");  
                $('#sdgs').val(sdgsArray).trigger('change');  
                if($.fn.selectpicker) { $('#sdgs').selectpicker('refresh'); }
            }
            
            // Handle Collaborators Array via Select2
            if(data.collaborators && $('#collaborators').length) {
                $('#collaborators').val(data.collaborators).trigger('change');
            }
            
            $('#funding_source').val(data.funding_source);
            $('#approved_budget').val(data.approved_budget);
            $('#stat').val(data.stat);
            $('#terminal_report').val(data.terminal_report);

            $('#modal_title').text('Edit Project & Collaborators');
            $('#action_researchedconducted').val('Edit');
            $('#submit_button_researchedconducted').val('Edit');
            $('#researchconductedModal').modal('show');
            $('#hidden_id_researchedconducted').val(rcid);
        }
    });
});

// 7. Handle Delete Button
$(document).on('click', '.delete_button_researchconducted, .delete_buttonrc, .delete_master_researchconducted', function(e) {
    e.preventDefault();
    var xid = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the project and remove all associated collaborators!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn-danger', 
            cancelButton: 'btn-secondary' 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/researchconducted_action.php",
                method: "POST",
                data: { xid: xid, action_researchedconducted: 'delete' },
                success: function(data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The record has been successfully deleted.',
                        icon: 'success',
                        timer: 600, 
                        showConfirmButton: false, 
                    });

                    // Reload page to reflect timeline card deletion
                    var researcherID = $('#researcherModala').data('id');
                    if(researcherID) {
                        setTimeout(function() {
                            window.location.href = "view_researcher.php?id=" + researcherID + "&tab=education";
                        }, 600);
                    } else {
                        // Master view reload
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong: ' + error,
                        icon: 'error',
                        confirmButtonText: 'Try Again',
                        customClass: {
                            confirmButton: 'btn-danger' 
                        }
                    });
                }
            });
        }
    });
});

// 8. Function to Load Publication Tab Data (From your snippet)
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