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
        "columnDefs": [
            {
                "targets": [0],
                "orderable": false,
            },
        ],
    });
    return intellectualPropTable;
}

// Handle Tab Switching for Dynamic Content
$('#intellectualpropModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); 
    loadIntellectualPropTab(id);  
});

// Handle Form Submission for Intellectual Property
$('#intellectualprop_form').on('submit', function (event) {
    event.preventDefault();
    if ($('#intellectualprop_form').parsley().isValid()) {
        $.ajax({
            url: "actions/intellectualprop_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('#submit_button_intellectualprop').attr('disabled', 'disabled').val('Wait...');
            },
            success: function (data) {
                $('#submit_button_intellectualprop').attr('disabled', false);
                if (data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_intellectualprop').val('Add');
                } else {
                    $('#intellectualpropModal').modal('hide');
                    $('#message').html(data.success);

                    var Svalue = $('#action_intellectualprop').val();
                    if (Svalue == "Add") {
                        Swal.fire({ title: 'Added!', text: 'The intellectual property has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' }});
                    } else {
                        Swal.fire({ title: 'Updated!', text: 'The intellectual property has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' }});
                    }

                    var researcherID = $('#researcherModala').data('id');  
                    loadIntellectualPropTab(researcherID);

                    setTimeout(function () { $('#message').html(''); }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#intellectualpropModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Intellectual Property
$('#add_intellectualprop').click(function () {
    $('#intellectualprop_form')[0].reset();  
    $('#intellectualprop_form').parsley().reset();  
    $('#modal_title').text('Add Intellectual Property');  
    $('#action_intellectualprop').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID_ip').val(rid);  
    $('#submit_button_intellectualprop').val('Add');
    $('#intellectualpropModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Intellectual Property
$(document).on('click', '.edit_button_intellectualprop', function () {
    var intellectualPropID = $(this).data('id');  

    $('#intellectualprop_form')[0].reset();
    $('#intellectualprop_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url: "actions/intellectualprop_action.php",
        method: "POST",
        data: { intellectualPropID: intellectualPropID, action_intellectualprop: 'fetch_single' },
        dataType: 'JSON',
        success: function (data) {
            const inputDateApplied = data.date_applied;
            const inputDateGranted = data.date_granted;

            const [monthApplied, dayApplied, yearApplied] = inputDateApplied.split('-');
            const formattedDateApplied = `${yearApplied}-${monthApplied}-${dayApplied}`;

            const [monthGranted, dayGranted, yearGranted] = inputDateGranted.split('-');
            const formattedDateGranted = `${yearGranted}-${monthGranted}-${dayGranted}`;

            $('#title_ip').val(data.title);
            $('#coauth').val(data.coauth);
            $('#type_ip').val(data.type);
            $('#date_applied').val(formattedDateApplied);
            $('#date_granted').val(formattedDateGranted);

            $('#modal_title').text('Edit Intellectual Property');
            $('#action_intellectualprop').val('Edit');
            $('#submit_button_intellectualprop').val('Edit');
            $('#intellectualpropModal').modal('show');
            $('#hidden_intellectualPropID').val(intellectualPropID);
        }
    });
});

// Handle Delete Button for Intellectual Property
$(document).on('click', '.delete_button_intellectualprop', function () {
    var intellectualPropID = $(this).data('id');  
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this record!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it',
        reverseButtons: true,
        customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/intellectualprop_action.php",
                method: "POST",
                data: { intellectualPropID: intellectualPropID, action_intellectualprop: 'delete' },
                success: function (data) {
                    Swal.fire({ title: 'Deleted!', text: 'The intellectual property has been successfully deleted.', icon: 'success', timer: 600, showConfirmButton: false });
                    var researcherID = $('#researcherModala').data('id');
                    loadIntellectualPropTab(researcherID);  
                    setTimeout(function () { $('#message').html(''); }, 5000);
                }
            });
        }
    });
});