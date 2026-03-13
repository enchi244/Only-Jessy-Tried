
<script>
    $(document).ready(function(){
    var dataTable = $('#researcher_table').DataTable({
    "processing": true,
    "serverSide": true,
    "order": [],  // Disable default sorting
    "ajax": {
        url: "actions/researcher_action.php",  // Your API endpoint
        type: "POST",
        data: { action: 'fetch' },
    },
    "columnDefs": [
        {
            "targets": [0],  // Column 0 (Researcher ID) is not orderable
            "orderable": false,
        },
    ],
    "rowGroup": {
        "dataSrc": "department",  // Group rows by the "department" field
        "startRender": function(rows, group) {
            var departmentCount = rows.length;  // Get the number of rows in the current group
            // Group header with department name and researcher count
            return $('<tr class="group"><td colspan="6" style="font-weight: bold; background-color: #f1f1f1;">' + group + ' (' + departmentCount + ' Researchers)</td></tr>');
        }
    },
    "order": [[2, 'asc']],  // Ensure the rows are sorted by department (column index 2)
    "stateSave": true,  // Save table state (pagination, search, etc.)
    "drawCallback": function(settings) {
        // After rendering the table, we will calculate the department count
        var api = this.api();
        var rows = api.rows({ page: 'all' }).nodes();  // Access all rows, not just the current page
        var departments = {};

        // Loop through each row and count researchers per department
        api.column(2, { page: 'all' }).data().each(function(department, i) {
            if (department) {
                // If department exists, count it
                if (!departments[department]) {
                    departments[department] = 0;
                }
                departments[department]++;
            }
        });

        // Now, update the group headers with the correct counts
        var last = null;
        api.column(2, { page: 'all' }).data().each(function(department, i) {
            if (last !== department) {
                // Insert the new group header with the count
                $(rows).eq(i).before('<tr class="group"><td colspan="6" style="font-weight: bold; background-color: #f1f1f1;">' + department + ' (' + (departments[department] || 0) + ' Researchers)</td></tr>');
            }
            last = department;
        });
    }
});

    $(document).ready(function() {
        $('#researcherModala_form').parsley();
    $('#submit_button_rd').on('click', function(event) {
  
        event.preventDefault(); // Prevents the default form submission
    
    var researcherIDu = $('#researcherIDu').val();
    var hidden_id_rd = $('#hidden_id_rd').val();

  var familyNameu = $('#familyNameu').val();
    var firstNameu = $('#firstNameu').val();
    var middleNameu = $('#middleNameu').val();
    var Suffixu = $('#Suffixu').val();
    var departmentu = $('#departmentu').val();
    var programu = $('#programu').val();
    var bachelor_degreeu = $('#bachelor_degreeu').val();
    var bachelor_institutionu = $('#bachelor_institutionu').val();
    var bachelor_YearGraduatedu = $('#bachelor_YearGraduatedu').val();
    var masterDegreeu = $('#masterDegreeu').val();
    var masterInstitutionu = $('#masterInstitutionu').val();
    var masterYearGraduatedu = $('#masterYearGraduatedu').val();
    var doctorateDegreeu = $('#doctorateDegreeu').val();
    var doctorateInstitutionu = $('#doctorateInstitutionu').val();
    var doctorateYearGraduateu = $('#doctorateYearGraduateu').val();
    var postDegreeu = $('#postDegreeu').val();
    var postInstitutionu = $('#postInstitutionu').val();
    var postYearGraduateu = $('#postYearGraduateu').val();
    // AJAX request to send data to the backend
    $.ajax({
  url: 'actions/update_researcher.php', // Backend script URL
  method: 'POST',
  data: {
    researcherIDu: researcherIDu,
    familyNameu: familyNameu,
        firstNameu: firstNameu,
        middleNameu: middleNameu,
        Suffixu: Suffixu,
        departmentu: departmentu,
        programu: programu,
        bachelor_degreeu: bachelor_degreeu,
        bachelor_institutionu: bachelor_institutionu,
        bachelor_YearGraduatedu: bachelor_YearGraduatedu,
        masterDegreeu: masterDegreeu,
        masterInstitutionu: masterInstitutionu,
        masterYearGraduatedu: masterYearGraduatedu,
        doctorateDegreeu: doctorateDegreeu,
        doctorateInstitutionu: doctorateInstitutionu,
        doctorateYearGraduateu: doctorateYearGraduateu,
        postDegreeu: postDegreeu,
        postInstitutionu: postInstitutionu,
        postYearGraduateu: postYearGraduateu,
    hidden_id_rd: hidden_id_rd,
    action_rd: 'update'
  },
  dataType: 'json', // Expect JSON response
  success: function(response) {
                    

                // S=document.getElementById("submit_button_researchedconducted").value
                var Svalue = $('#action_researchedconducted').val();
        
    Swal.fire({
        title: 'Updated!',
        text: 'The record has been successfully updated.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });



    // Reload DataTable
    dataTable.ajax.reload();
   
  },

}); 
  });
});



        // Ensure proper behavior when the second modal closes
        $('#researcherModala').on('hidden.bs.modal', function() {
    // Check if the first modal is still open
    if ($('.modal.show').length > 0) {
        // Reapply the `modal-open` class to allow body scrolling for the first modal
        $('body').addClass('modal-open');
    }

    // Optionally scroll the first modal to the top
    $('#researcherModala .modal-body').scrollTop(0);
});























    
	

	$('#add_researcher').click(function(){
		
		$('#researcher_form')[0].reset();

		$('#researcher_form').parsley().reset();    

    	$('#modal_title').text('Add Data');

    	$('#action').val('Add');

    	$('#submit_button').val('Add');

    	$('#researcherModal').modal('show');

    	$('#form_message_rm').html('');

	});

	$('#researcher_form').parsley();

	$('#researcher_form').on('submit', function(event){
		event.preventDefault();
		if($('#researcher_form').parsley().isValid())
		{		
			$.ajax({
				url:"actions/researcher_action.php",
				method:"POST",
				data:$(this).serialize(),
				dataType:'json',
				beforeSend:function()
				{
					$('#submit_button').attr('disabled', 'disabled');
					$('#submit_button').val('wait...');
				},
				success:function(data)
				{
					//console.log(data);
					$('#submit_button').attr('disabled', false);
					if(data.error != '')
					{
                        
						$('#form_message_rm').html(data.error);
						$('#submit_button').val('Add');
					}
					else
					{
						$('#researcherModal').modal('hide');
						$('#message_rm').html(data.success);
						dataTable.ajax.reload();
                        Swal.fire({
        title: 'Added!',
        text: 'The record has been successfully added.',
        icon: 'success',
        timer: 600,  // Automatically closes after 2 seconds
        showConfirmButton: false,  // Hide the confirm button
        customClass: { confirmButton: 'btn-success' }
    });
						setTimeout(function(){

				            $('#message_rm').html('');

				        }, 5000);
					}
				}
			})
		}
	});

        $(document).on('click', '.edit_buttona', function(){
            var id = $(this).data('id');
            var rid = $(this).data('id');
        // research
    //
  //  alert("Researcher ID: " + rid);
            $('#researcherModala_form').parsley().reset();
            $('#form_message').html('');

            $.ajax({

                url:"actions/researcher_action.php",

                method:"POST",

                data:{id:id, action:'fetch_single'},

                dataType:'JSON',

                success:function(data)
                {
    $('#researcherIDu').val(data.researcherID);
    $('#familyNameu').val(data.familyName);
    $('#firstNameu').val(data.firstName);
    $('#middleNameu').val(data.middleName);
    $('#Suffixu').val(data.Suffix);
    $('#departmentu').val(data.department);
    $('#programu').val(data.program);
    $('#bachelor_degreeu').val(data.bachelor_degree);
    $('#bachelor_institutionu').val(data.bachelor_institution);
    $('#bachelor_YearGraduatedu').val(data.bachelor_YearGraduated);
    $('#masterDegreeu').val(data.masterDegree);
    $('#masterInstitutionu').val(data.masterInstitution);
    $('#masterYearGraduatedu').val(data.masterYearGraduated);
    $('#doctorateDegreeu').val(data.doctorateDegree);
    $('#doctorateInstitutionu').val(data.doctorateInstitution);
    $('#doctorateYearGraduateu').val(data.doctorateYearGraduate);
    $('#postDegreeu').val(data.postDegree);
    $('#postInstitutionu').val(data.postInstitution);
    $('#postYearGraduateu').val(data.postYearGraduate);

                    $('#modal_title').text('Edit');

            //  	$('#action_rd').val('Edit');

                    $('#submit_button_rd').val('Edit');
                   $('#researcherModala').data('id', id).modal('show');
                 //  $('#researcherModala').modal('show'); 
                   $('#hidden_id_rd').val(id);
                
                   // $('#hidden_id').val(id);
                   
                // $('#action_rd').val('Edit');
                  
                // Reload the DataTable if it's already initialized
                
            //   rcdataTable.ajax.reload();
            
            


    // Edit button click event
    loadResearchConductedTab(id);
    loadPublicationTab(id);
    loadIntellectualPropTab(id); 
    loadPaperPresentationTab(id); 
    loadTrainingsAttendedTab(id);
    loadExtensionProjectsTab(id);
   // loadextprotab(id);


    }

            });

        });
        

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


















$(document).on('click', '.edit_buttonrc', function(){
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


    $(document).on('click', '.delete_buttonrc', function() {
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



// Function to Load Intellectual Property Data using the Researcher ID
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

// Handle Tab Switching for Dynamic Content (e.g., Intellectual Property)
$('#intellectualpropModal').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); // Get the ID from the hidden field in the modal
    loadIntellectualPropTab(id);  // Load the content dynamically when the tab is shown
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
                        Swal.fire({
                            title: 'Added!',
                            text: 'The intellectual property has been successfully added.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'The intellectual property has been successfully updated.',
                            icon: 'success',
                            timer: 600,  // Automatically closes after 2 seconds
                            showConfirmButton: false,  // Hide the confirm button
                            customClass: { confirmButton: 'btn-success' }
                        });
                    }

                    // Reload the table data
                    var researcherID = $('#researcherModala').data('id');  // Get the Researcher ID
                    var intellectualPropTable = loadIntellectualPropTab(researcherID);

                    setTimeout(function () {
                        $('#message').html('');
                    }, 5000);
                }
            }
        });
    }
});

// Handle the Modal Close Behavior
$('#intellectualpropModal').on('hidden.bs.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    }

    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Intellectual Property
$('#add_intellectualprop').click(function () {
    $('#intellectualprop_form')[0].reset();  // Reset form fields
    $('#intellectualprop_form').parsley().reset();  // Reset validation
    $('#modal_title').text('Add Intellectual Property');  // Set modal title
    $('#action_intellectualprop').val('Add');
    var rid = $('#researcherModala').data('id');  // Get the Researcher ID
    $('#hidden_researcherID_ip').val(rid);  // Store Researcher ID in hidden field
    $('#submit_button_intellectualprop').val('Add');
    $('#intellectualpropModal').modal('show');  // Show the modal
    $('#form_message').html('');
});

// Edit Existing Intellectual Property
$(document).on('click', '.edit_button_intellectualprop', function () {
    var intellectualPropID = $(this).data('id');  // Get the selected Intellectual Property ID

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
    var intellectualPropID = $(this).data('id');  // Get the Intellectual Property ID to delete
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
                url: "actions/intellectualprop_action.php",
                method: "POST",
                data: { intellectualPropID: intellectualPropID, action_intellectualprop: 'delete' },
                success: function (data) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The intellectual property has been successfully deleted.',
                        icon: 'success',
                        timer: 600,
                        showConfirmButton: false,
                    });

                    // Reload the DataTable to reflect the deletion
                    var researcherID = $('#researcherModala').data('id');
                    loadIntellectualPropTab(researcherID);  // Reload the table data after delete
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

// Function to Load Trainings Attended Data using the Researcher ID
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





// // Function to Load Extension Projects Data using the Researcher ID
// function loadextprotab(researcherID) {
//     $('#ext_project_form').parsley(); // Initialize form validation
//     if ($.fn.dataTable.isDataTable('#ext_project_table')) {
//         $('#ext_project_table').DataTable().clear().destroy();
//     }

//     var extProjectsTable = $('#ext_project_table').DataTable({
//         "processing": true,
//         "serverSide": true,
//         "order": [],
//         "ajax": {
//             url: "actions/extension_projects_action.php",
//             type: "POST",
//             data: { rid: researcherID, action_ext: 'fetch' }
//         },
//         "columnDefs": [
//             {
//                 "targets": [0],
//                 "orderable": false,
//             },
//         ],
//     });
//     return extProjectsTable;
// }

// // Handle Tab Switching for Dynamic Content (e.g., Extension Projects)
// $('#extModal').on('shown.bs.tab', function () {
//     var id = $('#hidden_id_ext').val(); // Get the ID from the hidden field in the modal
//     loadextprotab(id);  // Load the content dynamically when the tab is shown
// });

// // Handle Form Submission for Extension Projects
// $('#ext_project_form').on('submit', function (event) {
//     event.preventDefault();
//     if ($('#ext_project_form').parsley().isValid()) {
//         $.ajax({
//             url: "actions/extension_projects_action.php",
//             method: "POST",
//             data: $(this).serialize(),
//             dataType: 'json',
//             beforeSend: function () {
//                 $('#submit_button_ext').attr('disabled', 'disabled').val('Wait...');
//             },
//             success: function (data) {
//                 $('#submit_button_ext').attr('disabled', false);
//                 if (data.error != '') {
//                     $('#form_message').html(data.error);
//                     $('#submit_button_ext').val('Add');
//                 } else {
//                     $('#extModal').modal('hide');
//                     $('#message').html(data.success);

//                     var Svalue = $('#action_ext').val();
//                     if (Svalue == "Add") {
//                         Swal.fire({
//                             title: 'Added!',
//                             text: 'The extension project has been successfully added.',
//                             icon: 'success',
//                             timer: 600,  // Automatically closes after 2 seconds
//                             showConfirmButton: false,  // Hide the confirm button
//                             customClass: { confirmButton: 'btn-success' }
//                         });
//                     } else {
//                         Swal.fire({
//                             title: 'Updated!',
//                             text: 'The extension project has been successfully updated.',
//                             icon: 'success',
//                             timer: 600,  // Automatically closes after 2 seconds
//                             showConfirmButton: false,  // Hide the confirm button
//                             customClass: { confirmButton: 'btn-success' }
//                         });
//                     }

//                     // Reload the table data
//                     var researcherID = $('#researcherModala').data('id');  // Get the Researcher ID
//                     loadextprotab(researcherID);  // Reload the table data

//                     setTimeout(function () {
//                         $('#message').html('');
//                     }, 5000);
//                 }
//             }
//         });
//     }
// });

// // Handle the Modal Close Behavior
// $('#extModal').on('hidden.bs.modal', function () {
//     if ($('.modal.show').length > 0) {
//         $('body').addClass('modal-open');
//     }

//     $('#researcherModala .modal-body').scrollTop(0);
// });

// // Add New Extension Project
// $('#add_extension').click(function () {
//     $('#ext_project_form')[0].reset();  // Reset form fields
//     $('#ext_project_form').parsley().reset();  // Reset validation
//     $('#modal_title').text('Add Extension Project');  // Set modal title
//     $('#action_ext').val('Add');
//     var rid = $('#researcherModala').data('id');  // Get the Researcher ID
//     $('#hidden_researcherID_ext').val(rid);  // Store Researcher ID in hidden field
//     $('#submit_button_ext').val('Add');
//     $('#extModal').modal('show');  // Show the modal
//     $('#form_message').html('');
// });

// // Edit Existing Extension Project
// $(document).on('click', '.edit_button_ext', function () {
//     var extID = $(this).data('id');  // Get the selected Extension Project ID

//     $('#ext_project_form')[0].reset();
//     $('#ext_project_form').parsley().reset();
//     $('#form_message').html('');

//     $.ajax({
//         url: "actions/extension_projects_action.php",
//         method: "POST",
//         data: { extID: extID, action_ext: 'fetch_single' },
//         dataType: 'JSON',
//         success: function (data) {
//             const inputDateStarted_ext = data.period_implement; // MM-DD-YYYY format for started date

//             // Convert started date
//             const [monthStarted, dayStarted, yearStarted] = inputDateStarted_ext.split('-');
//             const formattedDateStarted_ext = `${yearStarted}-${monthStarted}-${dayStarted}`;

//             // Populate the form fields with data
//             $('#title_ext').val(data.title);
//             $('#description_ext').val(data.description);
//             $('#proj_lead').val(data.proj_lead);
//             $('#assist_coordinators').val(data.assist_coordinators);
//             $('#period_implement').val(formattedDateStarted_ext);
//             $('#budget').val(data.budget);
//             $('#fund_source').val(data.fund_source);
//             $('#target_beneficiaries').val(data.target_beneficiaries);
//             $('#partners').val(data.partners);
//             $('#stat_ext').val(data.stat_ext);

//             $('#modal_title').text('Edit Extension Project');
//             $('#action_ext').val('Edit');
//             $('#submit_button_ext').val('Edit');
//             $('#extModal').modal('show');
//             $('#hidden_extID').val(extID);
//         }
//     });
// });

// // Handle Delete Button for Extension Projects
// $(document).on('click', '.delete_button_ext', function () {
//     var extID = $(this).data('id');  // Get the Extension Project ID to delete
//     Swal.fire({
//         title: 'Are you sure?',
//         text: 'You will not be able to recover this record!',
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Yes, delete it!',
//         cancelButtonText: 'No, keep it',
//         reverseButtons: true,
//         customClass: {
//             confirmButton: 'btn-danger',
//             cancelButton: 'btn-secondary'
//         }
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.ajax({
//                 url: "actions/extension_projects_action.php",
//                 method: "POST",
//                 data: { extID: extID, action_ext: 'delete' },
//                 success: function (data) {
//                     Swal.fire({
//                         title: 'Deleted!',
//                         text: 'The extension project has been successfully deleted.',
//                         icon: 'success',
//                         timer: 600,
//                         showConfirmButton: false,
//                     });

//                     // Reload the DataTable to reflect the deletion
//                     var researcherID = $('#researcherModala').data('id');
//                     loadextprotab(researcherID);  // Reload the table data after delete
//                     setTimeout(function () {
//                         $('#message').html('');
//                     }, 5000);
//                 },
//                 error: function (xhr, status, error) {
//                     Swal.fire({
//                         title: 'Error!',
//                         text: 'Something went wrong: ' + error,
//                         icon: 'error',
//                         confirmButtonText: 'Try Again',
//                         customClass: {
//                             confirmButton: 'btn-danger'
//                         }
//                     });
//                 }
//             });
//         }
//     });
// });







// Function to Load Extension Projects Data using the Researcher ID
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
$(document).on('click', '.edit_button_extension', function () {
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
$(document).on('click', '.delete_button_extension', function () {
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

});

</script>