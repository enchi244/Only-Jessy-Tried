// Function to Load Extension Data
function loadextprotab(projectID) {
    if ($.fn.dataTable.isDataTable('#ext_project_table')) {
        $('#ext_project_table').DataTable().clear().destroy();
    }

    var extProjectsTable = $('#ext_project_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/extension_action.php",
            type: "POST",
            data: { project_id: projectID, action_ext: 'fetch_associated' } 
        }
    });
    return extProjectsTable;
}

// Initialize Searchable Researcher Selects
function initResearcherSelects() {
    $('.select2-researcher').select2({
        dropdownParent: $('#extModal'),
        placeholder: "Search Researcher...",
        allowClear: true,
        ajax: {
            url: "actions/fetch_researchers.php", // You will need to create this simple action file
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}

// Ensure Leader is not in Coordinator list
$(document).on('change', '#proj_lead', function() {
    var selectedLeader = $(this).val();
    var coordinatorSelect = $('#assist_coordinators');
    
    // Get current coordinator values
    var currentCoordinators = coordinatorSelect.val() || [];
    
    // If leader was in coordinators, remove them
    var index = currentCoordinators.indexOf(selectedLeader);
    if (index > -1) {
        currentCoordinators.splice(index, 1);
        coordinatorSelect.val(currentCoordinators).trigger('change');
    }
});

$('#ext_project_form').on('submit', function (event) {
    event.preventDefault();
    var extensionForm = $('#ext_project_form').parsley();

    if (extensionForm.isValid()) {
        var formData = new FormData(this);

        $.ajax({
            url: "actions/extension_action.php",
            method: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_button_ext').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_ext').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_ext').val($('#action_ext').val());
                } else {
                    $('#extModal').modal('hide');
                    Swal.fire({ title: 'Success!', text: 'Record saved successfully.', icon: 'success', timer: 1000, showConfirmButton: false });

                    var projectID = $('#viewExtensionsModal').data('project-id');
                    if(projectID) {
                        loadextprotab(projectID);
                    } else {
                        setTimeout(function(){ location.reload(); }, 1000);
                    }
                }
            }
        });
    }
});

$('#add_extension').click(function () {
    $('#ext_project_form')[0].reset();
    $('#period_start').val('');
    $('#period_end').val('');
    $('#period_implement').val('');
    
    // Reset Select2 fields
    $('#linked_extension_project').val(null).trigger('change');
    $('#proj_lead').val(null).trigger('change');
    $('#assist_coordinators').val(null).trigger('change');
    
    initResearcherSelects();
    
    $('#modal_title').text('Add Extension');
    $('#action_ext').val('Add');
    
    var rid = $('#hidden_id_rd').val() || $('.edit_researcher').data('id');
    $('#hidden_researcherID_ext').val(rid);

    var parentProjectID = $('#viewExtensionsModal').data('project-id');
    $('#hidden_parent_project_id').val(parentProjectID); 

    $('#submit_button_ext').val('Add');
    $('#extModal').modal('show');
});

$(document).on('click', '.edit_button_ext', function () {
    var extID = $(this).data('id');
    $('#ext_project_form')[0].reset();
    initResearcherSelects();

    $.ajax({
        url: "actions/extension_action.php",
        method: "POST",
        data: { extID: extID, action_ext: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            $('#title_ext').val(data.title);
            $('#description_ext').val(data.description);
            
            // Set Select2 values for Leader and Coordinators
            if(data.proj_lead) {
                var newOption = new Option(data.proj_lead, data.proj_lead, true, true);
                $('#proj_lead').append(newOption).trigger('change');
            }
            
            if(data.assist_coordinators) {
                var coords = data.assist_coordinators.split(', ');
                coords.forEach(function(c) {
                    var opt = new Option(c, c, true, true);
                    $('#assist_coordinators').append(opt).trigger('change');
                });
            }

            $('#budget').val(data.budget);
            $('#fund_source').val(data.fund_source);
            $('#target_beneficiaries').val(data.target_beneficiaries);
            $('#partners').val(data.partners);
            $('#stat_ext').val(data.stat);

            if (data.period_implement && data.period_implement.includes(" to ")) {
                var dates = data.period_implement.split(" to ");
                $('#period_start').val(dates[0]);
                $('#period_end').val(dates[1]);
            }

            $('#modal_title').text('Edit Extension');
            $('#action_ext').val('Edit');
            $('#submit_button_ext').val('Edit');
            $('#extModal').modal('show');
            $('#hidden_extID').val(extID);
        }
    });
});