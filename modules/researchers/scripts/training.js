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
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return trainingsAttendedTable;
}

// Handle Tab Switching for Dynamic Content (e.g., Trainings Attended)
$('#trainingsAttendedModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_training').val(); // Get the ID from the hidden field in the modal
    loadTrainingsAttendedTab(id);  // Load the content dynamically when the tab is shown
});

// Handle Form Submission for Trainings Attended
$('#trainings_attended_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#trainings_attended_form').parsley().isValid()) {
        $.ajax({
            url: "actions/trainings_attended_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#submit_button_training').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_training').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_training').val('Add');
                } else {
                    $('#trainingsAttendedModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_training').val();
                    if (Svalue == "Add") {
                        Swal.fire({
                            title: 'Added!',
                            text: 'The training has been successfully added.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The training has been successfully updated.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    }

                    // Reload the table data
                    var researcherID = $('#researcherModala').data('id');  // Get the Researcher ID
                    loadTrainingsAttendedTab(researcherID);  // Reload the table data

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#trainingsAttendedModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }

    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Training Attended
$('#add_training_attended').click(function () {
    $('#trainings_attended_form')[0].reset();  // Reset form fields
    $('#trainings_attended_form').parsley().reset();  // Reset validation
    $('#modal_title').text('Add Training Attended');  // Set modal title
    $('#action_training').val('Add');
    var rid = $('#researcherModala').data('id');  // Get the Researcher ID
    $('#hidden_researcherID_training').val(rid);  // Store Researcher ID in hidden field
    $('#submit_button_training').val('Add');
    $('#trainingsAttendedModal').modal('show');  // Show the modal
    $('#form_message').html('');
});

// Edit Existing Training Attended
$(document).on('click', '.edit_button_training', function () {
    var trainingID = $(this).data('id');  // Get the selected Training ID
//alert(trainingID);
    $('#trainings_attended_form')[0].reset();
    $('#trainings_attended_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url: "actions/trainings_attended_action.php",
        method: "POST",
        data: { trainingID: trainingID, action_training: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            const inputDatestarted_training = data.date_train; // MM-DD-YYYY format for started date
   
            // Convert started date
            const [monthStarted, dayStarted, yearStarted] = inputDatestarted_training.split('-');
            const formattedDateStarted_training = `${yearStarted}-${monthStarted}-${dayStarted}`;
            

            $('#title_training').val(data.title);
            $('#type_training').val(data.type);
            $('#venue_training').val(data.venue);
            $('#date_training').val(formattedDateStarted_training);
            $('#level_training').val(data.lvl);
            $('#type_learning_dev').val(data.type_learning_dev);
            $('#sponsor_org').val(data.sponsor_org);
            $('#total_hours_training').val(data.totnh);

            $('#modal_title').text('Edit Training Attended');
            $('#action_training').val('Edit');
            $('#submit_button_training').val('Edit');
            $('#trainingsAttendedModal').modal('show');
            $('#hidden_trainingID').val(trainingID);
        }
    });
});

// Handle Delete Button for Training Attended
$(document).on('click', '.delete_button_training', function () {
    var trainingID = $(this).data('id');  // Get the Training ID to delete
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
                url: "actions/trainings_attended_action.php",
                method: "POST",
                data: { trainingID: trainingID, action_training: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The training has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    // Reload the DataTable to reflect the deletion
                    var researcherID = $('#researcherModala').data('id');
                    loadTrainingsAttendedTab(researcherID);  // Reload the table data after delete
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