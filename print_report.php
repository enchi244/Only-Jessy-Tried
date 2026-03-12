<?php
// product.php
include('rms.php');

$object = new rms();

if (!$object->is_login()) {
    header("location:" . $object->base_url . "");
}

if (!$object->is_master_user()) {
    header("location:" . $object->base_url . "dashboard.php");
}

include('header.php');
?>
<style>


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
<link href="vendors/bootstrap.min.css" rel="stylesheet">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mt-5">
                <div class="card-header">
                    <h4>Publication Report</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form id="filterForm">
                        <div class="row">
                            <!-- Select Report -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Select Report</label>
                                    <select name="repp" id="repp" class="form-control" data-parsley-trigger="change">
                                        <option value="">Select All</option>
                                        <option value="tbl_researchconducted">Research Conducted</option>
                                        <option value="tbl_publication">Publication</option>
                                        <option value="tbl_itelectualprop">Intelectual Property</option>
                                        <option value="tbl_trainingsattended">Trainings Attended</option>
                                        <option value="tbl_extension_project_conducted">Extension Project Conducted</option>

                                    </select>
                                </div>
                            </div>

                            <!-- Select Department -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Select Department</label>
                                    <select name="department" id="department" class="form-control" data-parsley-trigger="change">
                                        <option value="">Select All</option>
                                        <?php
                                        $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                                        $category_result = $object->get_result();
                                        foreach($category_result as $category) {
                                            echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- From Date (Reduced size) -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control date-input" required>
                                </div>
                            </div>

                            <!-- To Date (Reduced size) -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control date-input" required>
                                </div>
                            </div>

                            <!-- Filter Button -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Click to Filter</label><br>
                                    <button type="submit" class="btn btn-danger" id="filterButton">Download PDF</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS to reduce the size of date fields -->
<style>
    .date-input {
        padding: 5px;
        font-size: 14px;
    }
</style>
<?php
                include('footer.php');
                ?>
				
<script src="vendors/jquery-3.5.1.min.js"></script>
<script src="vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendors/bootstrap.bundle.min.js"></script>

<script>$('#filterForm').submit(function(event) {
    event.preventDefault(); 

    var fromDate = $('#from_date').val();
    var toDate = $('#to_date').val();
    var department = $('#department').val();

    // Check if both dates are selected
    if (fromDate && toDate) {
        // Validate the date range
        if (new Date(fromDate) > new Date(toDate)) {
            Swal.fire({
                title: 'Error!',
                text: 'From Date must be earlier than To Date',
                icon: 'error',
                confirmButtonText: 'Try Again',
                customClass: {
                    confirmButton: 'btn-danger' // Red button for error
                }
            });
        } else {
            // Show loading spinner while waiting for the response
            const swalLoading = Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while we prepare the document.',
                icon: 'info',
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading(); // Show loading spinner
                }
            });

            // Add a delay to allow the spinner to appear before opening the new tab
            setTimeout(function() {
                // Open the PDF directly in a new tab with the query string
                window.open('print_journal.php?from_date=' + fromDate + '&to_date=' + toDate + '&department=' + department, '_blank');
                
                // Close the loading spinner
                swalLoading.close();
            }, 500); // Delay in milliseconds (adjust to your preference)
        }
    } else {
        // Handle missing date selection
        Swal.fire({
            icon: 'warning',
            title: 'Missing Dates',
            text: 'Please select both From Date and To Date.',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn-danger' // Red button for missing date warning
            }
        });
    }
});

</script>
