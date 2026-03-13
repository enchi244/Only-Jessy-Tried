// Function to Load Research Conducted Tab Data using the Main Researcher ID
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
    // alert(rid); // you can place the alert for debugging if needed
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




// 3. Handle Tab Switching for Dynamic Content (e.g., Research Conducted)
$('#researchconductedTab').on('shown.bs.tab', function() {
    var id = $('#hidden_id_rd').val(); // Get the ID from the hidden field in the modal
    loadResearchConductedTab(id);  // Load the content dynamically when the tab is shown
});


// 4. Handle Form Submission for Adding or Updating Research Conducted Data
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
            success: function(data)    {
                $('#submit_button_researchedconducted').attr('disabled', false);
                if(data.error != '')
                {
                
                    $('#form_message').html(data.error);
                    $('#submit_button_researchedconducted').val('Add');
                }
                else
                {
                    $('#researchconductedModal').modal('hide');
                    $('#message').html(data.success);
                

                // S=document.getElementById("submit_button_researchedconducted").value
                    var Svalue = $('#action_researchedconducted').val();
                    if (Svalue == "Add") {
    Swal.fire({
        title: 'Added!',
        text: 'The record has been successfully added.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });
} else {
    Swal.fire({
        title: 'Updated!',
        text: 'The record has been successfully updated.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });
}

                // researcherconducteddataTable.ajax.reload(null, false);
                var researcherID = $('#researcherModala').data('id'); // Get the Researcher ID
                    var rcdataTable = loadResearchConductedTab(researcherID); // Reload the table data

                    

                    setTimeout(function(){

                        $('#message').html('');

                    }, 5000);
                }
            }
        });
    }
});

$('#researchconductedModal').on('hidden.bs.modal', function() {
        // Check if the first modal is still open
        if ($('.modal.show').length > 0) {
            // Reapply the `modal-open` class to allow body scrolling for the first modal
            $('body').addClass('modal-open');
        }
    
        // Optionally scroll the first modal to the top
        $('#researcherModala .modal-body').scrollTop(0);
        });


// 5. Handle Add Button for Research Conducted Tab
$('#add_researcherconducted').click(function() {
    // Reset form fields and validation for adding a new record
    $('#researchconducted_form')[0].reset();
    $('#researchconducted_form').parsley().reset();
    $('#sdgs').val([]);  // Clear the selected SDGs options

// If you're using Select2 or Bootstrap-Select, trigger their update
$('#sdgs').trigger('change');  // For Select2
$('#sdgs').selectpicker('refresh');  // For Bootstrap-Select
    $('#modal_title').text('Add Researcher Conducted');
    $('#action_researchedconducted').val('Add');
    $('#submit_button_researchedconducted').val('Add');
    var rid = $('#researcherModala').data('id');  // Get the Researcher ID
   // alert(rid);     
    $('#hiddeny').val(rid);  // Store Researcher ID in hidden field
    $('#researchconductedModal').modal('show');
    $('#form_message').html('');
});


















$(document).on('click', '.edit_button_researchconducted', function(){
           // var ridy = $('#researcherModala').data('id');
            var rcid = $(this).data('id');
            // alert(rcid+''+ridy);

            var editID = $(this).data('id'); // Get the selected ID from the clicked row



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

            const inputDatestarted =data.started_date; // MM-DD-YYYY format for started date
    const inputDatecompleted = data.completed_date; // MM-DD-YYYY format for completed date
    
    // Convert started date
    const [monthStarted, dayStarted, yearStarted] = inputDatestarted.split('-');
    const formattedDateStarted = `${yearStarted}-${monthStarted}-${dayStarted}`;

    // Convert completed date
    const [monthCompleted, dayCompleted, yearCompleted] = inputDatecompleted.split('-');
    const formattedDateCompleted = `${yearCompleted}-${monthCompleted}-${dayCompleted}`;

    // Set the formatted dates to both input fields
    $('#started_date').val(formattedDateStarted);
    $('#completed_date').val(formattedDateCompleted);

                        $('#title').val(data.title);
                        $('#research_agenda_cluster').val(data.research_agenda_cluster);
                       
                        var sdgsArray = data.sdgs.split(", ");  // Convert the comma-separated string into an array
    $('#sdgs').val(sdgsArray);  // Set the selected values in the #sdgs select field

    // If using Select2, trigger the change event to update the UI
    $('#sdgs').trigger('change');  // For Select2

    // If using Bootstrap-Select, refresh the selectpicker
    $('#sdgs').selectpicker('refresh');  // For Bootstrap-Select

                    
                        $('#funding_source').val(data.funding_source);
                        $('#approved_budget').val(data.approved_budget);
                        $('#stat').val(data.stat);
                        $('#terminal_report').val(data.terminal_report);








            $('#modal_title').text('Edit Data');

            $('#action_researchedconducted').val('Edit');

            $('#submit_button_researchedconducted').val('Edit');

                        $('#researchconductedModal').modal('show');

                        $('#hidden_id_researchedconducted').val(rcid);
                    

        }
        
    })

    });


    $(document).on('click', '.delete_button_researchconducted', function() {
        var xid = $(this).data('id');
        // Use SweetAlert instead of default confirm
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn-danger', // Custom class to make the confirm button red
                cancelButton: 'btn-secondary' // Optional: Customize cancel button style
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the delete action via AJAX
                $.ajax({
                    url: "actions/researchconducted_action.php",
                    method: "POST",
                    data: { xid: xid, action_researchedconducted: 'delete' },
                    success: function(data) {
                        // Show success message
                        Swal.fire({
                            title: 'Deleted!',
                        text: 'The record has been successfully deleted.',
                        icon: 'success',
                        timer: 600, // The message will disappear after 3 seconds
                        showConfirmButton: false, // Hide the confirm button
                                            });

                          // Reload the DataTable to reflect the deletion
                    var researcherID = $('#researcherModala').data('id');
                    loadResearchConductedTab(researcherID); // Reload the table after delete


                        // Optionally, you can clear any other messages or handle UI updates
                        setTimeout(function() {
                            $('#message').html('');
                        }, 5000);
                    },
                    error: function(xhr, status, error) {
                        // Handle error (e.g., database issues)
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong: ' + error,
                            icon: 'error',
                            confirmButtonText: 'Try Again',
                            customClass: {
                                confirmButton: 'btn-danger' // Red button for error
                            }
                        });
                    }
                });
            }
        });
    });


// Function to Load Research Conducted Tab Data using the Main Researcher ID
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
    // alert(rid); // you can place the alert for debugging if needed
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




// 3. Handle Tab Switching for Dynamic Content (e.g., Research Conducted)
$('#publicationModal').on('shown.bs.tab', function() {
    var id = $('#hidden_id_rd').val(); // Get the ID from the hidden field in the modal
    loadPublicationTab(id);  // Load the content dynamically when the tab is shown
});