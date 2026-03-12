<?php

include('rms.php');

$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_master_user())
{
    header("location:".$object->base_url."dashboard");
}

include('header.php');

?>

  <link rel="stylesheet" type="text/css" href="css/select2.min.css">
<style>
.red{
    background-color: #610d0d;
}
.red:hover{
    background-color: #610d0d;
}
.modal-xl {
    max-width: 90%; /* Adjust the percentage or use a fixed width (e.g., 1200px) */
}
#categoryModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    #categoryModal_t {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
#researchconductedModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }

    #publicationModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    #intellectualpropModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    
    
    #paperPresentationModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }

    #trainingsAttendedModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    #extensionProjectModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    #extModal {
        z-index: 1051 !important;  /* Ensure the second modal appears in front */
    }
    
    

    



    .custom-confirm-button {
    background-color: #ff0000 !important; /* Custom color (example: deep orange) */
    color: white !important;              /* Button text color */
    border: none !important;              /* Remove default border */
    border-radius: 5px !important;        /* Optional: Rounded corners */
  } 
  .swal2-confirm.btn-danger {
    background-color: red !important;
    border-color: red !important;
}

.swal2-cancel.btn-secondary {
    background-color: gray !important;
    border-color: gray !important;
}

</style>

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Researchers' Data</h1>
                    <link rel="stylesheet" type="text/css" href="css/select2.min.css">
<!-- DataTales Example -->
<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Researchers' List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_researcher" id="add_researcher" class="btn btn-danger pink  btn-sm"><i class="fas fa-plus"> Add Researcher</i></button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="researcher_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Researcher ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Program</th>
                        <th>User Created On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Researchers data will go here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

                <?php
                include('footer.php');
                ?>

<script src="js/app.js"></script>
<script src="js/select2.min.js"></script>
    
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


<div id="researchconductedModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <form method="post" id="researchconducted_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Research -->
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            class="form-control" 
                            placeholder="Enter the title of the research" 
                            required 
                        />
                    </div>

                    <!-- Research Agenda Cluster -->
                    <div class="form-group">
                        <label for="research_agenda_cluster">Research Agenda Cluster</label>
                        <input 
                            type="text" 
                            name="research_agenda_cluster" 
                            id="research_agenda_cluster" 
                            class="form-control" 
                            placeholder="Enter the research agenda cluster" 
                            required 
                        />
                    </div>

                    <!-- SDG Selection -->
                    <div class="form-group">
                        <label>Select SDG</label>
                        <select name="sdgs[]" id="sdgs" multiple required class="select form-control">
 <option value="" disabled selected>Select SDG</option>
    <?php
                            $object->query = "SELECT goal_name FROM tbl_sdgs ORDER BY goal_name ASC";
                            $sdg_result = $object->get_result();
                            foreach($sdg_result as $sdg) {
                                echo '<option value="'.$sdg["goal_name"].'">'.$sdg["goal_name"].'</option>';
                            }
                            ?>
         </select>
                    </div>

                   
                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="started_date">Start Date</label>
                        <input 
                            type="date" 
                            name="started_date" 
                            id="started_date" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Completed Date -->
                    <div class="form-group">
                        <label for="completed_date">Completed Date</label>
                        <input 
                            type="date" 
                            name="completed_date" 
                            id="completed_date" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="funding_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="funding_source" 
                            id="funding_source" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>
                    
                    <!-- Approved Budget -->
                    <div class="form-group">
                        <label for="approved_budget">Approved Budget</label>
                        <input 
                            type="number" 
                            name="approved_budget" 
                            id="approved_budget" 
                            class="form-control" 
                            step="0.01" 
                            placeholder="Enter the approved budget" 
                            required 
                        />
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="stat">Status</label>
                        <select 
                            name="stat" 
                            id="stat" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="On Going">On Going</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <!-- Terminal Report -->
                    <div class="form-group">
                        <label for="terminal_report">Terminal Report</label>
                        <select 
                            name="terminal_report" 
                            id="terminal_report" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="With">With</option>
                            <option value="None">None</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id_researchedconducted" id="hidden_id_researchedconducted" />
                    <input type="hidden" name="hiddeny" id="hiddeny" />
                    <input type="hidden" name="action_researchedconducted" id="action_researchedconducted" value="Add" />
                    <input type="submit" name="submit_button_researchedconducted" id="submit_button_researchedconducted" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>




<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="ext_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Extension Project -->
                    <div class="form-group">
                        <label for="title_ext">Title</label>
                        <input 
                            type="text" 
                            name="title_ext" 
                            id="title_ext" 
                            class="form-control" 
                            placeholder="Enter the title of the extension project" 
                            required 
                        />
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description_ext">Description</label>
                        <input 
                            name="description_ext" 
                            id="description_ext" 
                            class="form-control" 
                            placeholder="Enter a description of the project" 
                            required
                        ></input>
                    </div>

                    <!-- Project Leader -->
                    <div class="form-group">
                        <label for="proj_lead">Project Leader</label>
                        <input 
                            type="text" 
                            name="proj_lead" 
                            id="proj_lead" 
                            class="form-control" 
                            placeholder="Enter the project leader's name" 
                            required 
                        />
                    </div>

                    <!-- Assistant Coordinators -->
                    <div class="form-group">
                        <label for="assist_coordinators">Assistant Coordinators</label>
                        <input 
                            type="text" 
                            name="assist_coordinators" 
                            id="assist_coordinators" 
                            class="form-control" 
                            placeholder="Enter assistant coordinators' names" 
                            required 
                        />
                    </div>

                    <!-- Period of Implementation -->
                    <div class="form-group">
                        <label for="period_implement">Period of Implementation</label>
                        <input 
                            type="text" 
                            name="period_implement" 
                            id="period_implement" 
                            class="form-control" 
                            placeholder="Enter the implementation period" 
                            required 
                        />
                    </div>

                    <!-- Budget -->
                    <div class="form-group">
                        <label for="budget">Budget</label>
                        <input 
                            type="number" 
                            name="budget" 
                            id="budget" 
                            class="form-control" 
                            placeholder="Enter the project budget" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="fund_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="fund_source" 
                            id="fund_source" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>

                    <!-- Target Beneficiaries -->
                    <div class="form-group">
                        <label for="target_beneficiaries">Target Beneficiaries</label>
                        <input 
                            name="target_beneficiaries" 
                            id="target_beneficiaries" 
                            class="form-control" 
                            placeholder="Enter the target beneficiaries" 
                            required
                        ></input>
                    </div>

                    <!-- Partners -->
                    <div class="form-group">
                        <label for="partners">Partners</label>
                        <input 
                            name="partners" 
                            id="partners" 
                            class="form-control" 
                            placeholder="Enter the project partners" 
                            required
                        ></input>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="stat_ext">Status</label>
                        <select 
                            name="stat_ext" 
                            id="stat_ext" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                 
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>







<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="ext_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Extension Project -->
                    <div class="form-group">
                        <label for="title_ext">Title</label>
                        <input 
                            type="text" 
                            name="title_ext" 
                            id="title_ext" 
                            class="form-control" 
                            placeholder="Enter the title of the extension project" 
                            required 
                        />
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description_ext">Description</label>
                        <input 
                            name="description_ext" 
                            id="description_ext" 
                            class="form-control" 
                           placeholder="Enter a description of the project" 
                            required
                        ></input>
                    </div>

                    <!-- Project Leader -->
                    <div class="form-group">
                        <label for="proj_lead">Project Leader</label>
                        <input 
                            type="text" 
                            name="proj_lead" 
                            id="proj_lead" 
                            class="form-control" 
                            placeholder="Enter the project leader's name" 
                            required 
                        />
                    </div>

                    <!-- Assistant Coordinators -->
                    <div class="form-group">
                        <label for="assist_coordinators">Assistant Coordinators</label>
                        <input 
                            type="text" 
                            name="assist_coordinators" 
                            id="assist_coordinators" 
                            class="form-control" 
                            placeholder="Enter assistant coordinators' names" 
                            required 
                        />
                    </div>

                    <!-- Period of Implementation -->
                    <div class="form-group">
                        <label for="period_implement">Period of Implementation</label>
                        <input 
                            type="text" 
                            name="period_implement" 
                            id="period_implement" 
                            class="form-control" 
                            placeholder="Enter the implementation period" 
                            required 
                        />
                    </div>

                    <!-- Budget -->
                    <div class="form-group">
                        <label for="budget">Budget</label>
                        <input 
                            type="number" 
                            name="budget" 
                            id="budget" 
                            class="form-control" 
                            placeholder="Enter the project budget" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="fund_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="fund_source" 
                            id="fund_source" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>

                    <!-- Target Beneficiaries -->
                    <div class="form-group">
                        <label for="target_beneficiaries">Target Beneficiaries</label>
                        <input 
                            name="target_beneficiaries" 
                            id="target_beneficiaries" 
                            class="form-control" 
                         
                            placeholder="Enter the target beneficiaries" 
                            required
                        ></input>
                    </div>

                    <!-- Partners -->
                    <div class="form-group">
                        <label for="partners">Partners</label>
                        <input 
                            name="partners" 
                            id="partners" 
                            class="form-control" 
                           
                            placeholder="Enter the project partners" 
                            required
                        ></input>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="stat_ext">Status</label>
                        <select 
                            name="stat_ext" 
                            id="stat_ext" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>










<div id="publicationModal" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-xl">
    <form method="post" id="publication_form">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title">Manage Publication</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <span id="form_message"></span>

          <!-- Hidden Fields for Publication and Researcher ID -->
          <input type="hidden" id="hidden_publicationID" name="hidden_publicationID" />
          <input type="hidden" id="hidden_researcherID" name="hidden_researcherID" />

          <!-- Title -->
          <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title_pub" id="title_pub" class="form-control" placeholder="Enter publication title" required />
          </div>

<!-- Include flatpickr CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Start Date -->
<div class="form-row">
  <!-- Start Date Format -->
  <div class="form-group col-md-6">
    <label for="start-format">Start Date Format</label>
    <select class="form-control" id="start-format" onchange="toggleStartDateFormat()">
      <option value="mm-yyyy">MM/YYYY</option>
      <option value="full-date">Full Date (DD-MM-YYYY)</option>
    </select>
  </div>

  <!-- Start Date -->
  <div class="form-group col-md-6">
    <label for="start">Start</label>
    <input type="text" name="start" id="start" class="form-control" required placeholder="MM-YYYY" />
  </div>

  <!-- End Date Format -->
  <div class="form-group col-md-6">
    <label for="end-format">End Date Format</label>
    <select class="form-control" id="end-format" onchange="toggleEndDateFormat()">
      <option value="mm-yyyy">MM/YYYY</option>
      <option value="full-date">Full Date (DD-MM-YYYY)</option>
    </select>
  </div>

  <!-- End Date -->
  <div class="form-group col-md-6">
    <label for="end">End</label>
    <input type="text" name="end" id="end" class="form-control" required placeholder="MM-YYYY" />
  </div>
</div>

<script>
  // Declare flatpickr instances to avoid re-initialization
  let startDatePicker, endDatePicker;

  // Toggle the format of the Start Date field
  function toggleStartDateFormat() {
    const startFormat = document.getElementById('start-format').value;
    const startInput = document.getElementById('start');

    // Destroy the current flatpickr instance if it exists
    if (startDatePicker) {
      startDatePicker.destroy();
    }

    if (startFormat === 'mm-yyyy') {
      // Initialize flatpickr for MM/YYYY format (month-year picker)
      startInput.placeholder = "MM-YYYY";
      startDatePicker = flatpickr(startInput, {
        dateFormat: "m-Y", // Set the format to MM-YYYY (Month-Year)
        mode: "single",
        monthSelectorType: "static",
        disableMobile: true,
        defaultDate: "01-1970", // Default the day to a dummy value to avoid showing it
        onChange: function(selectedDates, dateStr, instance) {
          // Ensure that only MM-YYYY is shown, not the day
          const monthYear = dateStr.split('-');
          instance.setDate(`${monthYear[0]}-${monthYear[1]}`, true); // Only set the month and year
        }
      });
    } else if (startFormat === 'full-date') {
      // Initialize flatpickr for Full Date (DD-MM-YYYY)
      startInput.placeholder = "DD-MM-YYYY";
      startDatePicker = flatpickr(startInput, {
        dateFormat: "d-m-Y", // Set the format to DD-MM-YYYY (Day-Month-Year)
        disableMobile: true
      });
    }
  }

  // Toggle the format of the End Date field
  function toggleEndDateFormat() {
    const endFormat = document.getElementById('end-format').value;
    const endInput = document.getElementById('end');

    // Destroy the current flatpickr instance if it exists
    if (endDatePicker) {
      endDatePicker.destroy();
    }

    if (endFormat === 'mm-yyyy') {
      // Initialize flatpickr for MM/YYYY format (month-year picker)
      endInput.placeholder = "MM-YYYY";
      endDatePicker = flatpickr(endInput, {
        dateFormat: "m-Y", // Set the format to MM-YYYY (Month-Year)
        mode: "single",
        monthSelectorType: "static",
        disableMobile: true,
        defaultDate: "01-1970", // Default the day to a dummy value to avoid showing it
        onChange: function(selectedDates, dateStr, instance) {
          // Ensure that only MM-YYYY is shown, not the day
          const monthYear = dateStr.split('-');
          instance.setDate(`${monthYear[0]}-${monthYear[1]}`, true); // Only set the month and year
        }
      });
    } else if (endFormat === 'full-date') {
      // Initialize flatpickr for Full Date (DD-MM-YYYY)
      endInput.placeholder = "DD-MM-YYYY";
      endDatePicker = flatpickr(endInput, {
        dateFormat: "d-m-Y", // Set the format to DD-MM-YYYY (Day-Month-Year)
        disableMobile: true
      });
    }
  }

  // Initialize formats on page load
  window.onload = function() {
    toggleStartDateFormat();
    toggleEndDateFormat();
  }
</script>




          <!-- Journal -->
          <div class="form-group">
            <label for="journal">Journal</label>
            <input type="text" name="journal" id="journal" class="form-control" placeholder="Enter journal name" required />
          </div>

          <!-- Volume and Issue Number -->
          <div class="form-group">
            <label for="vol_num_issue_num">Volume and Issue Number</label>
            <input type="text" name="vol_num_issue_num" id="vol_num_issue_num" class="form-control" placeholder="Enter volume and issue number" required />
          </div>

          <!-- ISSN/ISBN -->
          <div class="form-group">
            <label for="issn_isbn">ISSN/ISBN</label>
            <input type="text" name="issn_isbn" id="issn_isbn" class="form-control" placeholder="Enter ISSN or ISBN number" required />
          </div>

          <!-- Indexing -->
          <div class="form-group">
            <label for="indexing">Indexing</label>
            <input type="text" name="indexing" id="indexing" class="form-control" placeholder="Enter indexing details (e.g., Scopus, Web of Science)" required />
          </div>

          <!-- Publication Date -->
          <div class="form-group">
            <label for="publication_date">Publication Date</label>
            <input type="date" name="publication_date" id="publication_date" class="form-control" required />
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="action_publication" id="action_publication" value="Add" />
          <input type="submit" name="submit_button_publication" id="submit_button_publication" class="btn btn-danger pink" value="Add" />
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal for Adding/Editing Intellectual Property -->
<div id="intellectualpropModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="intellectualpropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="intellectualprop_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Intellectual Property</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Intellectual Property -->
                    <div class="form-group">
                        <label for="title_ip">Title</label>
                        <input 
                            type="text" 
                            name="title_ip" 
                            id="title_ip" 
                            class="form-control" 
                            placeholder="Enter the title of the intellectual property" 
                            required 
                        />
                    </div>

                    <!-- Co-authors -->
                    <div class="form-group">
                        <label for="coauth">Co-authors</label>
                        <input 
                            type="text" 
                            name="coauth" 
                            id="coauth" 
                            class="form-control" 
                            placeholder="Enter co-authors' names" 
                            required 
                        />
                    </div>

                    <!-- Type of Intellectual Property (Dropdown) -->
                    <div class="form-group">
                        <label for="type">Type of Intellectual Property</label>
                        <select 
                            name="type_ip" 
                            id="type_ip" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Intellectual Property</option>
                            <option value="Patent">Patent</option>
                            <option value="Invention">Invention</option>
                            <option value="Copyright">Copyright</option>
                            <option value="Trademark">Trademark</option>
                            <option value="Industrial Design">Industrial Design</option>
                            <option value="Basics">Basics</option>
                        </select>
                    </div>

                    <!-- Date Applied -->
                    <div class="form-group">
                        <label for="date_applied">Date Applied</label>
                        <input 
                            type="date" 
                            name="date_applied" 
                            id="date_applied" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Date Granted -->
                    <div class="form-group">
                        <label for="date_granted">Date Granted</label>
                        <input 
                            type="date" 
                            name="date_granted" 
                            id="date_granted" 
                            class="form-control" 
                            required 
                        />
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_ip" id="hidden_researcherID_ip" />
                    <input type="hidden" name="hidden_intellectualPropID" id="hidden_intellectualPropID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_intellectualprop" id="action_intellectualprop" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_intellectualprop" id="submit_button_intellectualprop" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>





<!-- Paper Presentation Modal -->
<div id="paperPresentationModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="paperPresentationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="paper_presentation_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Paper Presentation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Paper Presentation -->
                    <div class="form-group">
                        <label for="title_pp">Title</label>
                        <input 
                            type="text" 
                            name="title_pp" 
                            id="title_pp" 
                            class="form-control" 
                            placeholder="Enter the title of the paper presentation" 
                            required 
                        />
                    </div>

                    <!-- Conference Title -->
                    <div class="form-group">
                              <label for="conference_title">Conference Title</label>
                        <select 
                            name="conference_title" 
                            id="conference_title" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Conference Title</option>
                            <option value="Local">Local</option>
                            <option value="Regional">Regional</option>
                            <option value="National">National</option>
                            <option value="International">International</option>
                        </select>







                    </div>

                    <!-- Conference Venue -->
                    <div class="form-group">
                        <label for="conference_venue">Conference Venue</label>
                        <input 
                            type="text" 
                            name="conference_venue" 
                            id="conference_venue" 
                            class="form-control" 
                            placeholder="Enter the conference venue" 
                            required 
                        />
                    </div>

                    <!-- Conference Organizer -->
                    <div class="form-group">
                        <label for="conference_organizer">Conference Organizer</label>
                        <input 
                            type="text" 
                            name="conference_organizer" 
                            id="conference_organizer" 
                            class="form-control" 
                            placeholder="Enter the conference organizer" 
                            required 
                        />
                    </div>

                    <!-- Date of Paper Presentation -->
                    <div class="form-group">
                        <label for="date_paper">Date of Presentation</label>
                        <input 
                            type="date" 
                            name="date_paper" 
                            id="date_paper" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Type of Paper (Dropdown) -->
                    <div class="form-group">
                        <label for="type_pp">Type of Paper</label>
                        <select 
                            name="type_pp" 
                            id="type_pp" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Paper</option>
                            <option value="Oral">Oral</option>
                            <option value="Poster">Poster</option>
                        </select>
                    </div>

                    <!-- Discipline -->
                    <div class="form-group">
                        <label for="discipline">Discipline</label>
        

                        <select name="discipline" id="discipline" class="form-control" data-parsley-trigger="change">
                                                                                                                                                                            <option value="">Select Major Discipline or Program</option>
                                                                                                                                                                            <?php
                                                                                                                                                                            $object->query = "SELECT * FROM tbl_majordiscipline";
                                                                                                                                                                            $program_result = $object->get_result();
                                                                                                                                                                            foreach($program_result as $program) {
                                                                                                                                                                                echo '<option value="'.$program["major"].'">'.$program["major"].'</option>';
                                                                                                                                                                            }
                                                                                                                                                                            ?>
                                                                                                                                                                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_pp" id="hidden_researcherID_pp" />
                    <input type="hidden" name="hidden_paperPresentationID" id="hidden_paperPresentationID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_paper_presentation" id="action_paper_presentation" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_paper_presentation" id="submit_button_paper_presentation" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>




<!-- Trainings Attended Modal -->
<div id="trainingsAttendedModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="trainingsAttendedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="trainings_attended_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Trainings Attended</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Training -->
                    <div class="form-group">
                        <label for="title_training">Title</label>
                        <input 
                            type="text" 
                            name="title_training" 
                            id="title_training" 
                            class="form-control" 
                            placeholder="Enter the title of the training" 
                            required 
                        />
                    </div>

                    <!-- Type of the Training -->
                    <div class="form-group">
                        <label for="type_training">Type</label>

                       
                        <select 
                            name="type_training" 
                            id="type_training" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Training</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Conference">Conference</option>
                            <option value="Training">Training</option>
                        </select>

                    </div>

                    <!-- Venue -->
                    <div class="form-group">
                        <label for="venue_training">Venue</label>
                        <input 
                            type="text" 
                            name="venue_training" 
                            id="venue_training" 
                            class="form-control" 
                            placeholder="Enter the training venue" 
                            required 
                        />
                    </div>

                    <!-- Date of Training -->
                    <div class="form-group">
                        <label for="date_training">Date</label>
                        <input 
                            type="date" 
                            name="date_training" 
                            id="date_training" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Level -->
                    <div class="form-group">
                        <label for="level_training">Level</label>
                          <select 
                            name="level_training" 
                            id="level_training" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Level</option>
                            <option value="Local">Local</option>
                            <option value="Regional">Regional</option>
                            <option value="National">National</option>
                            <option value="International">International</option>
                        </select>


                    </div>

                    <!-- Type of Learning Development -->
                    <div class="form-group">
                        <label for="type_learning_dev">Type of Learning Development</label>
                      
                      
                        
                      
                        <select 
                            name="type_learning_dev" 
                            id="type_learning_dev" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Type of Learing Development</option>
                            <option value="Clerical">Clerical</option>
                            <option value="Supervisory">Supervisory</option>
                            <option value="Technical">Technical</option>
                            <option value="Managerial">Managerial</option>
                        </select>

                      
                      
                        </div>
                      
                      
                      
                      
                      
                      
                    

                    <!-- Sponsor/Organizer -->
                    <div class="form-group">
                        <label for="sponsor_org">Sponsor/Organizer</label>
                        <input 
                            type="text" 
                            name="sponsor_org" 
                            id="sponsor_org" 
                            class="form-control" 
                            placeholder="Enter the sponsor or organizer" 
                            required 
                        />
                    </div>

                    <!-- Total Number of Hours -->
                    <div class="form-group">
                        <label for="total_hours_training">Total Number of Hours</label>
                        <input 
                            type="number" 
                            name="total_hours_training" 
                            id="total_hours_training" 
                            class="form-control" 
                            placeholder="Enter the total number of hours" 
                            required 
                        />
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_training" id="hidden_researcherID_training" />
                    <input type="hidden" name="hidden_trainingID" id="hidden_trainingID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_training" id="action_training" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_training" id="submit_button_training" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Extension Project Conducted Modal -->
<div id="extensionProjectModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extensionProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="extension_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Extension Project -->
                    <div class="form-group">
                        <label for="title_extp">Title</label>
                        <input 
                            type="text" 
                            name="title_extp" 
                            id="title_extp" 
                            class="form-control" 
                            placeholder="Enter project title" 
                            required 
                        />
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input 
                            type="date" 
                            name="start_date_extc" 
                            id="start_date_extc" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Completion Date -->
                    <div class="form-group">
                        <label for="completion_date">Completion Date</label>
                        <input 
                            type="date" 
                            name="completion_date_extc" 
                            id="completion_date_extc" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="funding_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="funding_source_exct" 
                            id="funding_source_exct" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>

                    <!-- Approved Budget -->
                    <div class="form-group">
                        <label for="approved_budget">Approved Budget</label>
                        <input 
                            type="number" 
                            name="approved_budget_exct" 
                            id="approved_budget_exct" 
                            class="form-control" 
                            placeholder="Enter the approved budget" 
                            required 
                        />
                    </div>

                    <!-- Target Beneficiaries/Communities -->
                    <div class="form-group">
                        <label for="target_beneficiaries_communities">Target Beneficiaries/Communities</label>
                        <input 
                            type="text" 
                            name="target_beneficiaries_communities" 
                            id="target_beneficiaries_communities" 
                            class="form-control" 
                            placeholder="Enter target beneficiaries/communities" 
                            required 
                        />
                    </div>

                    <!-- Partners -->
                    <div class="form-group">
                        <label for="partners">Partners</label>
                        <input 
                            type="text" 
                            name="partners" 
                            id="partners" 
                            class="form-control" 
                            placeholder="Enter project partners" 
                            required 
                        />
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select 
                            name="status_exct" 
                            id="status_exct" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <!-- Terminal Report -->
                    <div class="form-group">
                        <label for="terminal_report">Terminal Report</label>
                        <select 
                            name="terminal_report_extc" 
                            id="terminal_report_extc" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="With">With</option>
                            <option value="None">None</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_extension" id="hidden_researcherID_extension" />
                    <input type="hidden" name="hidden_extensionID" id="hidden_extensionID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_extension" id="action_extension" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_extension" id="submit_button_extension" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>


						


<div id="researcherModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl"> <!-- Custom class for increased width -->
        <form method="post" id="researcher_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message_rm"></span>

                   
                        <!-- Researcher ID -->
    <div class="form-group">
        <label>Researcher ID</label>
        <input type="text" name="researcherID" id="researcherID" class="form-control"  placeholder="Enter ID" maxlength="50" />
    </div>

    <!-- Name Section (Family, First, Middle, Suffix) -->
    <div class="form-group row">
        <div class="col-md-3">
            <label>Family Name</label>
            <input type="text" name="familyName" id="familyName" class="form-control" required placeholder="Last Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>First Name</label>
            <input type="text" name="firstName" id="firstName" class="form-control" required placeholder="First Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>Middle Name</label>
            <input type="text" name="middleName" id="middleName" class="form-control" placeholder="Middle Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>Suffix</label>
            <input type="text" name="Suffix" id="Suffix" class="form-control" placeholder="Jr, Sr, III" maxlength="10" />
        </div>
    </div>

    <!-- Department and Major Discipline -->
    <div class="form-group row">
        <div class="col-md-6">
            <label>Select Department</label>
            <select name="department" id="department" class="form-control" data-parsley-trigger="change">
                <option value="">Select Department</option>
                <?php
                $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                $category_result = $object->get_result();
                foreach($category_result as $category) {
                    echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label>Select Major Discipline or Program</label>
            <select name="program" id="program" class="form-control" data-parsley-trigger="change">
                <option value="">Select Major Discipline or Program</option>
                <?php
                $object->query = "SELECT * FROM tbl_majordiscipline";
                $program_result = $object->get_result();
                foreach($program_result as $program) {
                    echo '<option value="'.$program["major"].'">'.$program["major"].'</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Bachelor's Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Bachelor's Degree</label>
            <input type="text" name="bachelor_degree" id="bachelor_degree" class="form-control" placeholder="Bachelor's Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Bachelor's Institution</label>
            <input type="text" name="bachelor_institution" id="bachelor_institution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Bachelor's Year Graduated</label>
            <input type="text" name="bachelor_YearGraduated" id="bachelor_YearGraduated" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Master's Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Master's Degree</label>
            <input type="text" name="masterDegree" id="masterDegree" class="form-control" placeholder="Master's Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Master's Institution</label>
            <input type="text" name="masterInstitution" id="masterInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Master's Year Graduated</label>
            <input type="text" name="masterYearGraduated" id="masterYearGraduated" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Doctorate Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Doctorate Degree</label>
            <input type="text" name="doctorateDegree" id="doctorateDegree" class="form-control" placeholder="Doctorate Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Doctorate Institution</label>
            <input type="text" name="doctorateInstitution" id="doctorateInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Doctorate Year Graduated</label>
            <input type="text" name="doctorateYearGraduate" id="doctorateYearGraduate" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Post Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Post Degree</label>
            <input type="text" name="postDegree" id="postDegree" class="form-control" placeholder="Post Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Post Institution</label>
            <input type="text" name="postInstitution" id="postInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Post Year Graduated</label>
            <input type="text" name="postYearGraduate" id="postYearGraduate" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>
</div>

                
                     
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <input type="submit" name="submit_button" id="submit_button" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>




































<div id="researcherModala" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl"> <!-- Custom class for increased width -->
       
            <div class="modal-content">
               
                                    <div class="modal-header">
                                        <h4 class="modal-title" name="modal_title" id="modal_title">Update Data</h4>
                                      
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Tab Navigation -->
                     
                                            <ul class="nav nav-tabs" id="researcherTab" role="tablist">
                                               
                                            <li class="nav-item" id="navID">
                                                    <a class="nav-link active" id="personal-info-tab" data-toggle="tab" href="#personal-info" role="tab" aria-controls="personal-info" aria-selected="true">Researchers' Profile</a>
                                                </li>
                                               
                                                <li class="nav-item">
                                                    <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">Research Conducted</a>
                                                </li>
                                              
                                                <li class="nav-item">
                                                    <a class="nav-link" id="degree-tab" data-toggle="tab" href="#degree" role="tab" aria-controls="degree" aria-selected="false">Publication</a>
                                               
                                                </li>
                                            
                                                <li class="nav-item">
                                                    <a class="nav-link" id="ip-tab" data-toggle="tab" href="#ip" role="tab" aria-controls="ip" aria-selected="false">Intelectual Property</a>
                                                </li>
                                                
                                              
                                              
                                              
                                                <li class="nav-item">
                                                    <a class="nav-link" id="pp-tab" data-toggle="tab" href="#pp" role="tab" aria-controls="pp" aria-selected="false">Paper Presentation</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tra-tab" data-toggle="tab" href="#tra" role="tab" aria-controls="tra" aria-selected="false">Trainings Attended</a>
                                                </li>
                                                
                                                
                                                
                                                <li class="nav-item">
                                                    <a class="nav-link" id="epc-tab" data-toggle="tab" href="#epc" role="tab" aria-controls="epc" aria-selected="false">Extension Project Conducted</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="ext-tab" data-toggle="tab" href="#ext" role="tab" aria-controls="ext" aria-selected="false">Extension</a>
                                                </li>
                                                
                                                
                                                
                                                
                                                
                                                
                                                                                           
                                            </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="researcherTabContent">
                                                                               <!-- Personal Information Tab -->
                                                                                                                                                    <div class="tab-pane fade show active" id="personal-info" role="tabpanel" aria-labelledby="personal-info-tab">
                                                                                                                                                    <form method="post" id="researcherModala_form">     
                                                                                                                                                    <!-- Researcher ID -->
                                                                                                                                                                    <div class="form-group">
                                                                                                                                                                        <label>Researcher ID</label>
                                                                                                                                                                        <input type="text" name="researcherIDu" id="researcherIDu" class="form-control" required placeholder="Enter ID" maxlength="50" />
                                                                                                                                                                    </div>

                                                                                                                                                                    <!-- Name Section (Family, First, Middle, Suffix) -->
                                                                                                                                                            <div class="form-group row">
                                                                                                                                                                    <div class="col-md-3">
                                                                                                                                                                        <label>Family Name</label>
                                                                                                                                                                        <input type="text" name="familyNameu" id="familyNameu" class="form-control" required placeholder="Last Name" maxlength="100" />
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-3">
                                                                                                                                                                        <label>First Name</label>
                                                                                                                                                                        <input type="text" name="firstNameu" id="firstNameu" class="form-control" required placeholder="First Name" maxlength="100" />
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-3">
                                                                                                                                                                        <label>Middle Name</label>
                                                                                                                                                                        <input type="text" name="middleNameu" id="middleNameu" class="form-control" placeholder="Middle Name" maxlength="100" />
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-3">
                                                                                                                                                                        <label>Suffix</label>
                                                                                                                                                                        <input type="text" name="Suffixu" id="Suffixu" class="form-control" placeholder="Jr, Sr, III" maxlength="10" />
                                                                                                                                                                    </div>
                                                                                                                                                            </div>

                                                                                                                                                                    <!-- Department and Major Discipline -->
                                                                                                                                                                <div class="form-group row">
                                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                                        <label>Select Department</label>
                                                                                                                                                                            <select name="departmentu" id="departmentu" class="form-control" data-parsley-trigger="change">
                                                                                                                                                                                <option value="">Select Department</option>
                                                                                                                                                                                <?php
                                                                                                                                                                                $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                                                                                                                                                                                $category_result = $object->get_result();
                                                                                                                                                                                foreach($category_result as $category) {
                                                                                                                                                                                    echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                                                                                                                                                                                }
                                                                                                                                                                                ?>
                                                                                                                                                                            </select>
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                                        <label>Select Major Discipline or Program</label>
                                                                                                                                                                        <select name="programu" id="programu" class="form-control" data-parsley-trigger="change">
                                                                                                                                                                            <option value="">Select Major Discipline or Program</option>
                                                                                                                                                                            <?php
                                                                                                                                                                            $object->query = "SELECT * FROM tbl_majordiscipline";
                                                                                                                                                                            $program_result = $object->get_result();
                                                                                                                                                                            foreach($program_result as $program) {
                                                                                                                                                                                echo '<option value="'.$program["major"].'">'.$program["major"].'</option>';
                                                                                                                                                                            }
                                                                                                                                                                            ?>
                                                                                                                                                                        </select>
                                                                                                                                                                    </div>
                                                                                                                                                                </div>

                                                                                                                                                                    <!-- Bachelor's Degree Section -->
                                                                                                                                                            <div class="form-group row">
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Bachelor's Degree</label>
                                                                                                                                                                <input type="text" name="bachelor_degreeu" id="bachelor_degreeu" class="form-control" placeholder="Bachelor's Degree" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Bachelor's Institution</label>
                                                                                                                                                                <input type="text" name="bachelor_institutionu" id="bachelor_institutionu" class="form-control" placeholder="Institution" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Bachelor's Year Graduated</label>
                                                                                                                                                                <input type="text" name="bachelor_YearGraduatedu" id="bachelor_YearGraduatedu" class="form-control" placeholder="Year Graduated" maxlength="4" />
                                                                                                                                                            </div>
                                                                                                                                                            </div>

                                                                                                                                                                <!-- Master's Degree Section -->
                                                                                                                                                        <div class="form-group row">
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Master's Degree</label>
                                                                                                                                                                <input type="text" name="masterDegreeu" id="masterDegreeu" class="form-control" placeholder="Master's Degree" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Master's Institution</label>
                                                                                                                                                                <input type="text" name="masterInstitutionu" id="masterInstitutionu" class="form-control" placeholder="Institution" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Master's Year Graduated</label>
                                                                                                                                                                <input type="text" name="masterYearGraduatedu" id="masterYearGraduatedu" class="form-control" placeholder="Year Graduated" maxlength="4" />
                                                                                                                                                            </div>
                                                                                                                                                        </div>

                                                                                                                                                                        <!-- Doctorate Degree Section -->
                                                                                                                                                            <div class="form-group row">
                                                                                                                                                                <div class="col-md-4">
                                                                                                                                                                    <label>Doctorate Degree</label>
                                                                                                                                                                    <input type="text" name="doctorateDegreeu" id="doctorateDegreeu" class="form-control" placeholder="Doctorate Degree" maxlength="100" />
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-4">
                                                                                                                                                                    <label>Doctorate Institution</label>
                                                                                                                                                                    <input type="text" name="doctorateInstitutionu" id="doctorateInstitutionu" class="form-control" placeholder="Institution" maxlength="100" />
                                                                                                                                                                </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Doctorate Year Graduated</label>
                                                                                                                                                                <input type="text" name="doctorateYearGraduateu" id="doctorateYearGraduateu" class="form-control" placeholder="Year Graduated" maxlength="4" />
                                                                                                                                                            </div>
                                                                                                                                                            </div>

                                                                                                                                                            <!-- Post Degree Section -->
                                                                                                                                                            <div class="form-group row">
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Post Degree</label>
                                                                                                                                                                <input type="text" name="postDegreeu" id="postDegreeu" class="form-control" placeholder="Post Degree" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Post Institution</label>
                                                                                                                                                                <input type="text" name="postInstitutionu" id="postInstitutionu" class="form-control" placeholder="Institution" maxlength="100" />
                                                                                                                                                            </div>
                                                                                                                                                            <div class="col-md-4">
                                                                                                                                                                <label>Post Year Graduated</label>
                                                                                                                                                                <input type="text" name="postYearGraduateu" id="postYearGraduateu" class="form-control" placeholder="Year Graduated" maxlength="4" />
                                                                                                                                                            </div>
                                                                                                                                                            </div>
                                                                                                                        
                                                                                                                                                            <div class="modal-footer">
                                                                                                                                                            <input type="hidden" name="hidden_id_rd" id="hidden_id_rd" />
                                                                                                                                                                  <!-- <input type="hidden" name="action_rd" id="action_rd" value="Edit" />
                                                                                                                                                 -->
                                                                                                                                                 <button type="button" id="submit_button_rd" class="btn btn-danger pink">Update</button>

                                                                                                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                                                                                            </div>
                                                                                                                                                     </form>                                
                                                                                                                                                    </div>
                                

                                                                                   <!-- Education Tab -->
                                                                                                                                                    <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                                                                                                                                        <h1 class="h3 mb-4 text-gray-800">Colleges' Management</h1>

                                                                                                                                                                        <!-- DataTales Example -->
                                                                                                                                                            <span id="message"></span>
                                                                                                                                                            <div class="card shadow mb-4">
                                                                                                                                                                        <div class="card-header py-3">
                                                                                                                                                                            <div class="row">
                                                                                                                                                                                <div class="col">
                                                                                                                                                                                    <h6 class="m-0 font-weight-bold text-primary">Colleges' List</h6>
                                                                                                                                                                                </div>
                                                                                                                                                                                <div class="col" align="right">
                                                                                                                                                                                    <button type="button" name="add_researcherconducted" id="add_researcherconducted" class="btn btn-danger pink  btn-sm"><i class="fas fa-plus"> Add Researcher Conducted</i></button>
                                                                                                                                                                                </div>
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                            <div class="card-body">
                                                                                    
                                                                                    
                                                                                                                                                                <div class="table-responsive">
                                                                                                                                                                    <table class="table table-bordered" id="researcherconducted_table" width="100%" cellspacing="0">
                                                                                                                                                                        <thead>
                                                                                                                                                                            <tr>
                                                                                                                                                                            <th>Title</th>
                                                                                                                                                                            <th>Research Agenda Cluster</th>
                                                                                                                                                                            <th>SDG</th>
                                                                                                                                                                            <th>Start Date</th>
                                                                                                                                                                            <th>Completed Date</th>
                                                                                                                                                                            <th>Funding Source</th>
                                                                                                                                                                            <th>Approved Budget</th>
                                                                                                                                                                            <th>Status</th>
                                                                                                                                                                            <th>Terminal Report</th>
                                                                                                                                                                            <th>Action</th>
                                                                                                                                                                            </tr>
                                                                                                                                                                        </thead>
                                                                                                                                                                        <tbody>
                                                                                                                                                                            
                                                                                                                                                                        </tbody>
                                                                                                                                                                    </table>
                                                                                                                                                                </div>


                                                                                                                                                    </div>



           </div>
                        </div>





                                                                                                                                                                      <!-- Degree Tab -->
                                                                                                                                                        <div class="tab-pane fade" id="degree" role="tabpanel" aria-labelledby="degree-tab">
                                                                                            
                                                                                                                                                        <h1 class="h3 mb-4 text-gray-800">Publications' Management</h1>

<!-- DataTales Example -->
                                            <span id="message"></span>
                                        <div class="card shadow mb-4">
                                            <div class="card-header py-3">
                                                     <div class="row">
                                                        <div class="col">
                                                            <h6 class="m-0 font-weight-bold text-primary">Publications' List</h6>
                                                        </div>
                                                            <div class="col" align="right">
                                                                <button type="button" name="add_publication" id="add_publication" class="btn btn-danger pink btn-sm">
                                                                    <i class="fas fa-plus"> Add Publication</i>
                                                                </button>
                                                            </div>
                                                    </div>
                                            </div>
                                                         <div class="card-body">
                                                 <div class="table-responsive">
                                                <table class="table table-bordered" id="publication_table" width="100%" cellspacing="0">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Title</th>
                                                                                        <th>Start</th>
                                                                                        <th>End</th>
                                                                                        <th>Journal</th>
                                                                                        <th>Volume/Issue</th>
                                                                                        <th>ISSN/ISBN</th>
                                                                                        <th>Indexing</th>
                                                                                        <th>Publication Date</th>
                                                                                        <th>Action</th>
                                                                                    </tr>
                                                                                </thead>
                                                    <tbody>
                                             <!-- Data will be loaded here -->
                                                    </tbody>
                                                  </table>
                                                  </div>
                                                   </div>
                                            </div>


                                                                                                                                                        </div>

                                                                                                                                                        <div class="tab-pane fade" id="ip" role="tabpanel" aria-labelledby="ip-tab">
                                                                                                                                                                     
                                                                                                                                                        <h1 class="h3 mb-4 text-gray-800">Intellectual Property Management</h1>

<!-- DataTales Example -->
<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Intellectual Property List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_intellectualprop" id="add_intellectualprop" class="btn btn-danger pink btn-sm">
                    <i class="fas fa-plus"> Add Intellectual Property</i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="intellectualprop_table" width="100%" cellspacing="0">
                             
     
    <thead>
        <tr>
            <th>Title</th>
            <th>Co-authors</th>
            <th>Type of IP</th>
            <th>Date Applied</th>
            <th>Date Granted</th>
            <th>Action</th>
        </tr>
    </thead>
                <tbody>
                    <!-- Data will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
                                                                                                                                                    </div>                                              
                                                                                                                                                  
                                                                                                                                                    <div class="tab-pane fade" id="pp" role="tabpanel" aria-labelledby="pp-tab">
                                                                                                                                                                     
                                                                                                                                                    <h1 class="h3 mb-4 text-gray-800">Paper Presentation Management</h1>

<!-- DataTales Example -->
<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Paper Presentation List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_paper_presentation" id="add_paper_presentation" class="btn btn-danger pink btn-sm">
                    <i class="fas fa-plus"> Add Paper Presentation</i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="paper_presentation_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Conference Title</th>
                        <th>Conference Venue</th>
                        <th>Conference Organizer</th>
                        <th>Date of Presentation</th>
                        <th>Type of Paper</th>
                        <th>Discipline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

                                                                                                                                                                     </div>                 
                                                                                                                                                                     <div class="tab-pane fade" id="tra" role="tabpanel" aria-labelledby="tra-tab">
                                                                                                                                                                     
                                                                                                                                                                     <h1 class="h3 mb-4 text-gray-800">Trainings Attended Management</h1>

<!-- DataTales Example -->
<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Trainings Attended List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_training_attended" id="add_training_attended" class="btn btn-danger pink btn-sm">
                    <i class="fas fa-plus"> Add Training Attended</i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="trainings_attended_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Venue</th>
                        <th>Date of Training</th>
                        <th>Level</th>
                        <th>Type of Learning Development</th>
                        <th>Sponsor/Organizer</th>
                        <th>Total Number of Hours</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
                                                                                                                                                                     </div>  
                                                                                                                                                                     <div class="tab-pane fade" id="epc" role="tabpanel" aria-labelledby="epc-tab">
                                                                                                                                                                     
                                                                                                                                                                    <!-- Extension Project Conducted Section -->
<h1 class="h3 mb-4 text-gray-800">Extension Project Conducted</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Extension Project Conducted List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_extension_project" id="add_extension_project" class="btn btn-danger pink btn-sm">
                    <i class="fas fa-plus"> Add Extension Project Conducted</i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="extension_project_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Start Date</th>
                        <th>Completion Date</th>
                        <th>Funding Source</th>
                        <th>Approved Budget</th>
                        <th>Target Beneficiaries/Communities</th>
                        <th>Partners</th>
                        <th>Status</th>
                        <th>Terminal Report</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
                                                                                                                                                                     </div>
                                                                                                                                                                     
                                                                                                                                                                     <div class="tab-pane fade" id="ext" role="tabpanel" aria-labelledby="ext-tab">
                                                                                                                                                                     
                                                                                                                                                                <!-- Extension Project Section -->
                                                                                                                                                                <h1 class="h3 mb-4 text-gray-800">Extension Projects Management</h1>

<!-- DataTales Example -->
<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Extension Projects List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_extension" id="add_extension" class="btn btn-danger pink btn-sm">
                    <i class="fas fa-plus"> Add Extension Project</i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="ext_project_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                    <th>Title</th>
            <th>Project Leader</th>
            <th>Assistant Coordinators</th>
            <th>Period of Implementation</th>
            <th>Budget</th>
            <th>Funding Source</th>
            <th>Target Beneficiaries</th>
            <th>Partners</th>
            <th>Status</th>
            <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

                                                                                                                                                                     </div>                


                                                                                                                  <!-- Degree Tab -->
                                                                                                                 
               
            </div>
       
    </div>
</div>








<script>
$(document).ready(function(){
    var dataTable = $('#researcher_table').DataTable({
    "processing": true,
    "serverSide": true,
    "order": [],  // Disable default sorting
    "ajax": {
        url: "researcher_action.php",  // Your API endpoint
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
  url: 'update_researcher.php', // Backend script URL
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
				url:"researcher_action.php",
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

                url:"researcher_action.php",

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
    url: "researchconducted_action.php",
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
            url: "researchconducted_action.php",
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

        url:"researchconducted_action.php",

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
                    url: "researchconducted_action.php",
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
    url: "publication_action.php",
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
            url: "publication_action.php",
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

 url:"publication_action.php",
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
                url: "publication_action.php",
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
            url: "intellectualprop_action.php",
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
            url: "intellectualprop_action.php",
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
        url: "intellectualprop_action.php",
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
                url: "intellectualprop_action.php",
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
            url: "paper_presentation_action.php",
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
            url: "paper_presentation_action.php",
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
        url: "paper_presentation_action.php",
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
                url: "paper_presentation_action.php",
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
            url: "trainings_attended_action.php",
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
            url: "trainings_attended_action.php",
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
        url: "trainings_attended_action.php",
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
                url: "trainings_attended_action.php",
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
//             url: "extension_projects_action.php",
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
//             url: "extension_projects_action.php",
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
//         url: "extension_projects_action.php",
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
//                 url: "extension_projects_action.php",
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
            url: "extension_project_action.php",
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
            url: "extension_project_action.php",
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
        url: "extension_project_action.php",
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
                url: "extension_project_action.php",
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
                url: "researcher_action.php",
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















