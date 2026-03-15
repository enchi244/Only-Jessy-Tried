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
                    var Svalue = $('#action_training').val();
                    if (Svalue == "Add") {
                        Swal.fire({ title: 'Added!', text: 'The training has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    } else {
                        Swal.fire({ title: 'Updated!', text: 'The training has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    }
                    var researcherID = $('#researcherModala').data('id');  
                    loadTrainingsAttendedTab(researcherID);
                    setTimeout(function () { $('#message').html(''); }, 5000);
                }
            }
        });
    }
});

$('#trainingsAttendedModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Training Attended
$('#add_training_attended').click(function () {
    $('#trainings_attended_form')[0].reset();  
    $('#trainings_attended_form').parsley().reset();  
    $('#modal_title').text('Add Training Attended');  
    $('#action_training').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_training').val(rid);  
    $('#submit_button_training').val('Add');
    $('#trainingsAttendedModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Training Attended
// Add New Training Attended
$('#add_training_attended').click(function () {
    // CRASH-PROOF RESET
    var form = $('#trainings_attended_form');
    if (form.length > 0) {
        form[0].reset();  
        var p = form.parsley();
        if (p) { p.reset(); }
    }

    $('#modal_title').text('Add Training Attended');  
    $('#action_training').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_training').val(rid);  
    $('#submit_button_training').val('Add');
    $('#trainingsAttendedModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Training Attended
$(document).on('click', '.edit_button_training', function () {
    var trainingID = $(this).data('id');  
    
    // CRASH-PROOF RESET
    var form = $('#trainings_attended_form');
    if (form.length > 0) {
        form[0].reset();
        var p = form.parsley();
        if (p) { p.reset(); }
    }
    $('#form_message').html('');

    $.ajax({
        url: "actions/trainings_attended_action.php",
        method: "POST",
        data: { trainingID: trainingID, action_training: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            const inputDatestarted_training = data.date_train; 
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

// Delete Training Attended
$(document).on('click', '.delete_button_training', function () {
    var trainingID = $(this).data('id');  
    Swal.fire({
        title: 'Are you sure?', text: 'You will not be able to recover this record!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, keep it', reverseButtons: true, customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/trainings_attended_action.php",
                method: "POST",
                data: { trainingID: trainingID, action_training: 'delete' },
                success: function (data) {
                    Swal.fire({ title: 'Deleted!', text: 'The training has been successfully deleted.', icon: 'success', timer: 600, showConfirmButton: false });
                    var researcherID = $('#researcherModala').data('id');
                    loadTrainingsAttendedTab(researcherID);  
                }
            });
        }
    });
});