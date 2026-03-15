<?php

include('../../core/rms.php');

$object = new rms();

if(!$object->is_login())
{
    if (empty($object->base_url)) {
        // Get the current protocol (http or https)
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        
        // Get the host (domain or localhost)
        $host = $_SERVER['HTTP_HOST'];

        // Get the script's base directory (subfolder of the root, e.g., '/rms-b')
        $scriptName = dirname($_SERVER['PHP_SELF']);

        // Construct the base URL
        $object->base_url = $protocol . '://' . $host . $scriptName . '/';
    }

    // Ensure the base URL ends with a slash
    $baseUrl = rtrim($object->base_url, '/');

    // Redirect to the base URL (without "index" or any other parts in the path)
    header("Location: " . $baseUrl . "/");
    exit; // Always call exit after header redirect to
    
}

include('../../includes/header.php');


$totalDepartments = $object->Get_total_departments();

// Fetch the department program counts
$departments = $object->Get_department_program_count();






$Get_total_department_count = $object->Get_total_department_count();

// Fetch the department program counts
$Get_department_list = $object->Get_department_list();








?>

<style>
.border-left-pink{
border-left: 0.25rem solid #f23e5d !important;

}

.chart-type-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .chart-type-container label {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .chart-type-container input {
            margin-right: 10px;
            margin-bottom: 10px;}
</style>

    <!-- Page Heading -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Bootstrap CSS -->


    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

    <!-- Content Row -->
    <div class="row">
        <?php
        if($object->is_master_user())
        {
        ?>
              <form method="post" class="">

            
            
            
             <!-- Display total program count -->


    <br>



            
            
            
            
            
            
<!--             
            
<select id="select-region" onChange="loadRegion()" class="form-control"   style="margin-left: 15px;  margin-bottom: 15px;  width: 300px;">
</select>
<select id="select-cluster" onChange="loadChartStatus()" class="form-control"  style="margin-left: 15px;  margin-bottom: 15px;  width: 300px;">
</select>

</div> -->


</form>
                   <!-- Begin Page Content -->
<div class="container-fluid">

   
<!-- Content Row -->
<div class="row">

<div class="col-xl-12 ">

<div class="card shadow mb-4">


<a href="#collapsestatus" class="d-block card-header py-3" data-toggle="collapse"
                        role="button" aria-expanded="true" aria-controls="collapsestatus">
                        <h6 class="m-0 font-weight-bold text-primary" style="margin-bottom: 20px; font-weight: bold;">
                            Total Number of Departments: <?php echo $totalDepartments; ?>
                        </h6>
                    </a>

                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapsestatus">
                        <div class="card-body">
                        <div>
        <label for="chartType">Select Chart Type:</label><br>
                        <div class="chart-type-container">
                            <!-- Pie Chart -->
                            <div>
                                <input type="radio" id="pieChart" name="chartType" value="pie" checked> <label for="pieChart">Pie Chart</label>
                            </div>
                            <!-- Column Chart -->
                            <div>
                                <input type="radio" id="columnChart" name="chartType" value="column"> <label for="columnChart">Column Chart</label>
                            </div>
                            <!-- Line Chart -->
                            <div>
                                <input type="radio" id="lineChart" name="chartType" value="line"> <label for="lineChart">Line Chart</label>
                            </div>
                            <!-- Bar Chart -->
                            <div>
                                <input type="radio" id="barChart" name="chartType" value="bar"> <label for="barChart">Bar Chart</label>
                            </div>
                            <!-- Area Chart -->
                            <div>
                                <input type="radio" id="areaChart" name="chartType" value="area"> <label for="areaChart">Area Chart</label>
                            </div>

                            <!-- Doughnut Chart -->
                            <div>
                                <input type="radio" id="doughnutChart" name="chartType" value="doughnut"> <label for="doughnutChart">Doughnut Chart</label>
                            </div>
                            <!-- Spline Chart -->
                            <div>
                                <input type="radio" id="splineChart" name="chartType" value="spline"> <label for="splineChart">Spline Chart</label>
                            </div>
                            <!-- Stacked Column Chart -->
                            <div>
                                <input type="radio" id="stackedColumnChart" name="chartType" value="stackedColumn"> <label for="stackedColumnChart">Stacked Column Chart</label>
                            </div>
                            <!-- Stacked Bar Chart -->
                            <div>
                                <input type="radio" id="stackedBarChart" name="chartType" value="stackedBar"> <label for="stackedBarChart">Stacked Bar Chart</label>
                            </div>
                            <!-- Stacked Area Chart -->
                            <div>
                                <input type="radio" id="stackedAreaChart" name="chartType" value="stackedArea"> <label for="stackedAreaChart">Stacked Area Chart</label>
                            </div>

                            <!-- Bubble Chart -->
                            <div>
                                <input type="radio" id="bubbleChart" name="chartType" value="bubble"> <label for="bubbleChart">Bubble Chart</label>
                            </div>
                            <!-- Scatter Chart -->
                            <div>
                                <input type="radio" id="scatterChart" name="chartType" value="scatter"> <label for="scatterChart">Scatter Chart</label>
                            </div>
                        </div>
    </div>


                            <!-- Chart container -->
                            <div id="departmentChart" style="height: 300px; width: 98%;"></div>

                            <!-- Download button -->
                            <button id="downloadChart" class="btn btn-primary">Download Chart</button>

                            <script>
                                // PHP data passed to JavaScript
                                var departmentData = <?php echo json_encode($departments); ?>;

                                // Prepare chart data points
                                var chartData = departmentData.map(function(item) {
                                    return {
                                        label: item.department,
                                        y: parseInt(item.programcount)
                                    };
                                });

                                // Create chart with default type as "pie"
                                var chart = new CanvasJS.Chart("departmentChart", {
                                    animationEnabled: true,
                                    title: {
                                        text: "Department-by Researchers' Count"
                                    },
                                    data: [{
                                        type: "pie", // Default chart type
                                        dataPoints: chartData
                                    }]
                                });

                                // Render the chart
                                chart.render();

                                // Event listener for chart type selection
                                document.querySelectorAll('input[name="chartType"]').forEach(function(radio) {
                                    radio.addEventListener('change', function() {
                                        var selectedType = document.querySelector('input[name="chartType"]:checked').value;
                                        chart.options.data[0].type = selectedType; // Change chart type dynamically
                                        chart.render(); // Re-render the chart
                                    });
                                });

                                // Event listener for downloading the chart
                                document.getElementById("downloadChart").addEventListener("click", function() {
                                    chart.exportChart({ format: "png" }); // Export chart as PNG
                                });

                                // Handle click on pie chart slice (show modal with sub-department data)
                                chart.options.data[0].click = function(e) {
                                    // Get sub-department data from backend
                                    var departmentName = e.dataPoint.label;
                                  //  alert(departmentName);
                                    $.post("fetch_subdep.php", { 
    action: "fetch_sub_department_data",  // Action type
    department: departmentName            // The department for which you want the sub-department data
}, function(result) {
    console.log(result);  // Debug: see what the result looks like
    var subDepartments = JSON.parse(result);
                        var modalContent = '<table class="table table-bordered" width="100%" cellspacing="0"><thead><tr><th>' + departmentName + '</th></tr></thead><tbody>';
  
    subDepartments.forEach(function(subDepartment) {
        // Add each subDepartment name to the table
        modalContent += '<tr><td>' + subDepartment + '</td></tr>';
    });

    modalContent += '</tbody></table>';

    // Set the modal content and show the modal
    $('#modalBody').html(modalContent);  // Ensure you have the correct ID for the modal body
    $('#myModal').modal('show');  // Show the modal
});
                                };
                            </script>
                        </div>
                    </div>
</div>                </div>
            </div>
        </div>

        <div class="container-fluid">
        <div class="row">

<div class="col-xl-12 ">
        <div class="card shadow mb-4">

<a href="#collapsestatus2" class="d-block card-header py-3" data-toggle="collapse"
    role="button" aria-expanded="true" aria-controls="collapsestatus2">
    <h6 class="m-0 font-weight-bold text-primary" style="margin-bottom: 20px; font-weight: bold;">
        Total Number of Research Conducted: <?php echo $Get_total_department_count; ?>
    </h6>
</a>

<!-- Card Content - Collapse -->
<div class="collapse show" id="collapsestatus2">
    <div class="card-body">
    <div>
        <label for="chartType2">Select Chart Type:</label><br>
        <div class="chart-type-container">
            <!-- Pie Chart -->
            <div>
                <input type="radio" id="pieChart2" name="chartType2" value="pie" checked> <label for="pieChart2">Pie Chart</label>
            </div>
            <!-- Column Chart -->
            <div>
                <input type="radio" id="columnChart2" name="chartType2" value="column"> <label for="columnChart2">Column Chart</label>
            </div>
            <!-- Line Chart -->
            <div>
                <input type="radio" id="lineChart2" name="chartType2" value="line"> <label for="lineChart2">Line Chart</label>
            </div>
            <!-- Bar Chart -->
            <div>
                <input type="radio" id="barChart2" name="chartType2" value="bar"> <label for="barChart2">Bar Chart</label>
            </div>
            <!-- Area Chart -->
            <div>
                <input type="radio" id="areaChart2" name="chartType2" value="area"> <label for="areaChart2">Area Chart</label>
            </div>

            <!-- Doughnut Chart -->
            <div>
                <input type="radio" id="doughnutChart2" name="chartType2" value="doughnut"> <label for="doughnutChart2">Doughnut Chart</label>
            </div>
            <!-- Spline Chart -->
            <div>
                <input type="radio" id="splineChart2" name="chartType2" value="spline"> <label for="splineChart2">Spline Chart</label>
            </div>
            <!-- Stacked Column Chart -->
            <div>
                <input type="radio" id="stackedColumnChart2" name="chartType2" value="stackedColumn"> <label for="stackedColumnChart2">Stacked Column Chart</label>
            </div>
            <!-- Stacked Bar Chart -->
            <div>
                <input type="radio" id="stackedBarChart2" name="chartType2" value="stackedBar"> <label for="stackedBarChart2">Stacked Bar Chart</label>
            </div>
            <!-- Stacked Area Chart -->
            <div>
                <input type="radio" id="stackedAreaChart2" name="chartType2" value="stackedArea"> <label for="stackedAreaChart2">Stacked Area Chart</label>
            </div>

            <!-- Bubble Chart -->
            <div>
                <input type="radio" id="bubbleChart2" name="chartType2" value="bubble"> <label for="bubbleChart2">Bubble Chart</label>
            </div>
            <!-- Scatter Chart -->
            <div>
                <input type="radio" id="scatterChart2" name="chartType2" value="scatter"> <label for="scatterChart2">Scatter Chart</label>
            </div>
        </div>



    </div>

    <!-- Chart container -->
    <div id="departmentChart2" style="height: 300px; width: 98%;"></div>

    <!-- Download button -->
    <button id="downloadChart2" class="btn btn-primary">Download Chart</button>

    <script>
        // PHP data passed to JavaScript
        var departmentData2 = <?php echo json_encode($Get_department_list); ?>;

        // Prepare chart data points
        var chartData2 = departmentData2.map(function(item) {
            return {
                label: item.department,
                y: parseInt(item.countt)
            };
        });

        // Create chart with default type as "pie"
        var chart2 = new CanvasJS.Chart("departmentChart2", {
            animationEnabled: true,
            title: {
                text: "Research Conducted-by Department's Count"
            },
            data: [{
                type: "pie", // Default chart type
                dataPoints: chartData2
            }]
        });

        // Render the chart
        chart2.render();
        document.getElementById("downloadChart2").addEventListener("click", function() {
                                    chart.exportChart({ format: "png" }); // Export chart as PNG
                                });

        // Event listener for chart type selection
        document.querySelectorAll('input[name="chartType2"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var selectedType2 = document.querySelector('input[name="chartType2"]:checked').value;
                chart2.options.data[0].type = selectedType2; // Change chart type dynamically
                chart2.render(); // Re-render the chart
            });
        });

//                                 // Event listener for downloading the chart
//                                 document.getElementById("downloadChart").addEventListener("click", function() {
//                                     chart.exportChart({ format: "png" }); // Export chart as PNG
//                                 });

//                                 // Handle click on pie chart slice (show modal with sub-department data)
//                                 chart.options.data[0].click = function(e) {
//                                     // Get sub-department data from backend
//                                     var departmentName = e.dataPoint.label;
//                                   //  alert(departmentName);
//                                     $.post("fetch_subdep.php", { 
//     action: "fetch_sub_department_data",  // Action type
//     department: departmentName            // The department for which you want the sub-department data
// }, function(result) {
//     console.log(result);  // Debug: see what the result looks like
//     var subDepartments = JSON.parse(result);
//                         var modalContent = '<table class="table table-bordered" width="100%" cellspacing="0"><thead><tr><th>' + departmentName + '</th></tr></thead><tbody>';
  
//     subDepartments.forEach(function(subDepartment) {
//         // Add each subDepartment name to the table
//         modalContent += '<tr><td>' + subDepartment + '</td></tr>';
//     });

//     modalContent += '</tbody></table>';

//     // Set the modal content and show the modal
//     $('#modalBody').html(modalContent);  // Ensure you have the correct ID for the modal body
//     $('#myModal').modal('show');  // Show the modal
// });
//                                 };
                            </script>
                        </div>
                    </div>
                </div>
                </div>
                </div>
                </div>







       
       <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Researchers Count</div>
                                 
                            <!-- <div class="h5 mb-0 font-weight-bold text-gray-800">   </div> -->
                            
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($researchertotal);?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Trainings Attended Total</div>
                         

                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($trainingsattendedtotal); ?></div> 
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Intellectual property Total
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                
                                   
                                   <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo number_format($itelectualproptotal); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Paper Presentation Count</div>
                                


                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($papertotal);?></div> 
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>   






         <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">
                                Total Number of Colleges</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format(16);?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">
                                Total discipline</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format(22);?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-table fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">Total Publications
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo number_format($publicationtotal);?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-th-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">    
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">
                                Total Projects Implemented</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format(13);?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
        
        <!-- Modal for displaying sub-department data -->
        <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="myModalLabel">Sub-Departments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div id="modalBody" class="modal-body">
                <!-- The table content will be inserted here -->
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <?php
    }
    ?>


<?php
include('../../includes/footer.php');
?>
<script src="<?php echo $object->base_url; ?>vendor/jquery/jquery.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo $object->base_url; ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    // jQuery to trigger modal
    $('#showModalBtn').click(function() {
        $('#myModal').modal('show');
    });
</script>