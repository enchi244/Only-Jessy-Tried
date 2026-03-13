$('#publication_form').on('submit', function(event) {
    event.preventDefault();
    if ($('#publication_form').parsley().isValid()) {
        $.ajax({
            url: "actions/publication_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#submit_button_publication').attr('disabled', 'disabled').val('Wait...');
            },
            success: function(data)    {
                $('#submit_button_publication').attr('disabled', false);
                if(data.error != '')
                {
                
                    $('#form_message').html(data.error);
                    $('#submit_button_publication').val('Add');
                }
                else
                {
                    $('#publicationModal').modal('hide');
                    $('#message').html(data.success);
                

                // S=document.getElementById("submit_button_researchedconducted").value
                    var Svalue6 = $('#action_publication').val();
                    if (Svalue6 == "Add") {
    Swal.fire({
        title: 'Added!',
        text: 'The publication has been successfully added.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });
} else {
    Swal.fire({
        title: 'Updated!',
        text: 'The publication has been successfully updated.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });
}

                // researcherconducteddataTable.ajax.reload(null, false);
                var publicationIDad = $('#researcherModala').data('id');  // Get the Publication ID
                var publicationTable = loadPublicationTab(publicationIDad); // Reload the table data

                    

                    setTimeout(function(){

                        $('#message').html('');

                    }, 5000);
                }
            }
        });
    }
});
$('#publicationModal').on('hidden.bs.modal', function() {
        // Check if the first modal is still open
        if ($('.modal.show').length > 0) {
            // Reapply the `modal-open` class to allow body scrolling for the first modal
            $('body').addClass('modal-open');
        }
    
        // Optionally scroll the first modal to the top
        $('#researcherModala .modal-body').scrollTop(0);
        });



$('#add_publication').click(function() {
    $('#publication_form')[0].reset();  // Reset form fields
    $('#publication_form').parsley().reset();  // Reset validation
    $('#modal_title').text('Add Publication');  // Set modal title
    $('#action_publication').val('Add');
    var ridp = $('#researcherModala').data('id');  // Get the Researcher ID
   // alert(rid);     
    $('#hidden_researcherID').val(ridp);  // Store Researcher ID in hidden field
    $('#submit_button_publication').val('Add');
    $('#publicationModal').modal('show');  // Show the modal
    $('#form_message').html('');
});




$(document).on('click', '.edit_button_publication', function(){
    // var ridy = $('#researcherModala').data('id');
    var publicationID = $(this).data('id');
     // alert(rcid+''+ridy);

    // var editID = $(this).data('id'); // Get the selected ID from the clicked row



$('#publication_form')[0].reset();
$('#publication_form').parsley().reset();
$('#form_message').html('');

$.ajax({

 url:"actions/publication_action.php",
 method:"POST",
 data:{publicationID: publicationID, action_publication: 'fetch_single'},
 dataType:'JSON',
 success:function(data)
 {
const inputDatecompleted = data.publication_date; // MM-DD-YYYY format for completed date

// Convert completed date
const [monthCompleted, dayCompleted, yearCompleted] = inputDatecompleted.split('-');
const formattedDateCompleted = `${yearCompleted}-${monthCompleted}-${dayCompleted}`;
$('#title_pub').val(data.title);
$('#start').val(data.start);
$('#end').val(data.end);
$('#journal').val(data.journal);
$('#vol_num_issue_num').val(data.vol_num_issue_num);
$('#issn_isbn').val(data.issn_isbn);
$('#indexing').val(data.indexing);
$('#publication_date').val(formattedDateCompleted);
     $('#modal_title').text('Edit Publication');
     $('#action_publication').val('Edit');
     $('#submit_button_publication').val('Edit');
     $('#publicationModal').modal('show');
     $('#hidden_publicationID').val(publicationID);
             

 }
 
})

});

























// 5. Handle Delete Button for Publication
$(document).on('click', '.delete_button_publication', function() {
    var publicationID = $(this).data('id');  // Get the publication ID to delete
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
                url: "actions/publication_action.php",
                method: "POST",
                data: {publicationID: publicationID, action_publication: 'delete'},
                success: function(data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The publication has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    // Reload the DataTable to reflect the deletion
                    var researcherIDaae = $('#researcherModala').data('id');
                    loadPublicationTab(researcherIDaae);  // Reload the table data after delete
                    setTimeout(function() {
                        $('#message').html('');
                    }, 5000);
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