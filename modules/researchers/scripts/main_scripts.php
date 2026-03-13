
<script>
    $(document).ready(function(){
    $('.modal').appendTo('body');
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
                
                // CRASH-PROOF FORM RESET
                var editForm = $('#researcherModala_form');
                if (editForm.length > 0) {
                    editForm[0].reset(); // Clear text fields natively
                    var parsleyInstance = editForm.parsley();
                    if (parsleyInstance) {
                        parsleyInstance.reset(); // Only reset Parsley if it actually exists!
                    }
                }

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
    loadextprotab(id);


    }

            });

        });

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
</script>