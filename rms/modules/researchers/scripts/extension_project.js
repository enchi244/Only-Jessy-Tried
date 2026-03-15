function loadExtensionProjectsTab(researcherID) {
    $('#extension_project_form').parsley();
    if ($.fn.dataTable.isDataTable('#extension_project_table')) {
        $('#extension_project_table').DataTable().clear().destroy();
    }

    var extensionProjectTable = $('#extension_project_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/extension_project_action.php",
            type: "POST",
            data: { rid: researcherID, action_extension: 'fetch' }
        },
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return extensionProjectTable;
}

// Handle Tab Switching for Dynamic Content (e.g., Extension Projects)
$('#extensionProjectModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_extension').val(); // Get the ID from the hidden field in the modal
    loadExtensionProjectsTab(id);  // Load the content dynamically when the tab is shown
});

// Handle Form Submission for Extension Project
$('#extension_project_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#extension_project_form').parsley().isValid()) {
        $.ajax({
            url: "actions/extension_project_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#submit_button_extension').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_extension').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_extension').val('Add');
                } else {
                    $('#extensionProjectModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_extension').val();
                    if (Svalue == "Add") {
                        Swal.fire({
                            title: 'Added!',
                            text: 'The extension project has been successfully added.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The extension project has been successfully updated.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    }

                    // Reload the table data
                    var researcherID = $('#researcherModala').data('id');  // Get the Researcher ID
                    loadExtensionProjectsTab(researcherID);  // Reload the table data

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior for Extension Project
$('#extensionProjectModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }

    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Extension Project
$('#add_extension_project').click(function () {
    $('#extension_project_form')[0].reset();  // Reset form fields
    $('#extension_project_form').parsley().reset();  // Reset validation
    $('#modal_title').text('Add Extension Project');  // Set modal title
    $('#action_extension').val('Add');
    var rid = $('#researcherModala').data('id');  // Get the Researcher ID
    $('#hidden_researcherID_extension').val(rid);  // Store Researcher ID in hidden field
    $('#submit_button_extension').val('Add');
    $('#extensionProjectModal').modal('show');  // Show the modal
    $('#form_message').html('');
});

// Edit Existing Extension Project
$(document).on('click', '.edit_button_extension_project', function () {
    var extensionID = $(this).data('id');  // Get the selected Extension Project ID

    $('#extension_project_form')[0].reset();
    $('#extension_project_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url: "actions/extension_project_action.php",
        method: "POST",
        data: { extensionID: extensionID, action_extension: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
           
            const inputDateApplied = data.start_date;
            const inputDateGranted = data.completed_date;

            const [monthApplied, dayApplied, yearApplied] = inputDateApplied.split('-');
            const formattedDateApplied = `${yearApplied}-${monthApplied}-${dayApplied}`;

            const [monthGranted, dayGranted, yearGranted] = inputDateGranted.split('-');
            const formattedDateGranted = `${yearGranted}-${monthGranted}-${dayGranted}`;
           
           
           
           
           
           
           
           
            $('#title_extp').val(data.title);
            $('#start_date_extc').val(formattedDateApplied);
            $('#completion_date_extc').val(formattedDateGranted);
            $('#funding_source_exct').val(data.funding_source);
            $('#approved_budget_exct').val(data.approved_budget);
            $('#target_beneficiaries_communities').val(data.target_beneficiaries_communities);
            $('#partners').val(data.partners);
            $('#status_exct').val(data.status_exct);
            $('#terminal_report_extc').val(data.terminal_report);

            $('#modal_title').text('Edit Extension Project');
            $('#action_extension').val('Edit');
            $('#submit_button_extension').val('Edit');
            $('#extensionProjectModal').modal('show');
            $('#hidden_extensionID').val(extensionID);






            
        }
    });
});

// Handle Delete Button for Extension Project
$(document).on('click', '.delete_button_extension_project', function () {
    var extensionID = $(this).data('id');  // Get the Extension Project ID to delete
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this record!',
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
                url: "actions/extension_project_action.php",
                method: "POST",
                data: { extensionID: extensionID, action_extension: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The extension project has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    // Reload the DataTable to reflect the deletion
                    var researcherID = $('#researcherModala').data('id');
                    loadExtensionProjectsTab(researcherID);  // Reload the table data after delete
                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                },
                error: function (xhr, status, error) {
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

























    $(document).on('click', '.delete_buttona', function() {
    var id = $(this).data('id'); // Get the ID of the record to delete

    // SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to recover this record!",
        icon: 'warning',
        showCancelButton: true,  // Show Cancel button
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true, // Reverse buttons (Yes is on the left)
        customClass: {
            confirmButton: 'btn-danger', // Custom class for confirm button (red for delete)
            cancelButton: 'btn-secondary' // Custom class for cancel button (gray for cancel)
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, proceed with the delete action
            $.ajax({
                url: "actions/researcher_action.php",
                method: "POST",
                data: { id: id, action: 'delete' },
                success: function(data) {
                    // Show success message with Swal
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The record has been successfully deleted.',
                        icon: 'success',
                        timer: 600, // Automatically closes after 3 seconds
                        showConfirmButton: false, // No confirm button, it closes automatically
                        customClass: { confirmButton: 'btn-success' } // Custom class for confirm button (if visible)
                    });

                    // Reload the DataTable
                    dataTable.ajax.reload();

                    // Optionally clear any message displayed
                    setTimeout(function() {
                        $('#message').html('');
                    }, 5000);
                },
                error: function(xhr, status, error) {
                    // In case of an error during deletion, show an error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        timer: 3000, // Auto close after 3 seconds
                        showConfirmButton: false
                    });
                }
            });
        }
    });
});