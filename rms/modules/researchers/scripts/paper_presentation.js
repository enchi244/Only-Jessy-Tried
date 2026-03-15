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

// Handle Tab Switching for Dynamic Content (e.g., Paper Presentation)
$('#paperPresentationModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_pp').val(); // Get the ID from the hidden field in the modal
    loadPaperPresentationTab(id);  // Load the content dynamically when the tab is shown
});

// Handle Form Submission for Paper Presentation
$('#paper_presentation_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#paper_presentation_form').parsley().isValid()) {
        $.ajax({
            url: "actions/paper_presentation_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#submit_button_paper_presentation').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_paper_presentation').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_paper_presentation').val('Add');
                } else {
                    $('#paperPresentationModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_paper_presentation').val();
                    if (Svalue == "Add") {
                        Swal.fire({
                            title: 'Added!',
                            text: 'The paper presentation has been successfully added.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The paper presentation has been successfully updated.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    }

                    // Reload the table data
                    var researcherID = $('#researcherModala').data('id');  // Get the Researcher ID
                    loadPaperPresentationTab(researcherID);  // Reload the table data

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
    $('#paper_presentation_form')[0].reset();  // Reset form fields
    $('#paper_presentation_form').parsley().reset();  // Reset validation
    $('#modal_title').text('Add Paper Presentation');  // Set modal title
    $('#action_paper_presentation').val('Add');
    var rid = $('#researcherModala').data('id');  // Get the Researcher ID
    $('#hidden_researcherID_pp').val(rid);  // Store Researcher ID in hidden field
    $('#submit_button_paper_presentation').val('Add');
    $('#paperPresentationModal').modal('show');  // Show the modal
    $('#form_message').html('');
});

// Edit Existing Paper Presentation
$(document).on('click', '.edit_button_paper_presentation', function () {
    var paperPresentationID = $(this).data('id');  // Get the selected Paper Presentation ID

    $('#paper_presentation_form')[0].reset();
    $('#paper_presentation_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url: "actions/paper_presentation_action.php",
        method: "POST",
        data: { paperPresentationID: paperPresentationID, action_paper_presentation: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            const inputDatestarted_pp =data.date_paper; // MM-DD-YYYY format for started date
   
    // Convert started date
    const [monthStarted, dayStarted, yearStarted] = inputDatestarted_pp.split('-');
    const formattedDateStarted_pp = `${yearStarted}-${monthStarted}-${dayStarted}`;

            $('#title_pp').val(data.title);
            $('#conference_title').val(data.conference_title);
            $('#conference_venue').val(data.conference_venue);
            $('#conference_organizer').val(data.conference_organizer);
            $('#date_paper').val(formattedDateStarted_pp);
            $('#type_pp').val(data.type);
            $('#discipline').val(data.discipline);

            $('#modal_title').text('Edit Paper Presentation');
            $('#action_paper_presentation').val('Edit');
            $('#submit_button_paper_presentation').val('Edit');
            $('#paperPresentationModal').modal('show');
            $('#hidden_paperPresentationID').val(paperPresentationID);
        }
    });
});

// Handle Delete Button for Paper Presentation
$(document).on('click', '.delete_button_paper_presentation', function () {
    var paperPresentationID = $(this).data('id');  // Get the Paper Presentation ID to delete
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
                url: "actions/paper_presentation_action.php",
                method: "POST",
                data: { paperPresentationID: paperPresentationID, action_paper_presentation: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The paper presentation has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    // Reload the DataTable to reflect the deletion
                    var researcherID = $('#researcherModala').data('id');
                    loadPaperPresentationTab(researcherID);  // Reload the table data after delete
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