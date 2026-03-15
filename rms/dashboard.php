<?php
// dashboard.php
include('core/rms.php');

// ==============================================================================
// DYNAMIC DASHBOARD AJAX HANDLER (Perfectly mapped to your actual schema!)
// ==============================================================================
if (isset($_POST['action']) && $_POST['action'] == 'filter_dashboard') {
    header('Content-Type: application/json');
    $year = $_POST['year'];
    
    $conn = @new mysqli("localhost", "root", "", "rms");
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    $conn->set_charset("utf8mb4");

    // Smart Engine: Groups by department, filters by EXACT date columns, and counts UNIQUE titles
    function getFilteredData($conn, $table, $year, $is_researcher = false, $distinct_col = null, $title_col = 'title') {
        $total_rows = 0; 
        $dept_counts = [];
        $distinct_vals = [];
        $unique_titles = [];

        if ($is_researcher) {
            $query = "SELECT department, user_created_on FROM {$table}";
        } else {
            $query = "SELECT d.department, r.* FROM {$table} r JOIN tbl_researchdata d ON r.researcherID = d.id";
        }

        $res = @$conn->query($query);
        if ($res) {
            while($row = $res->fetch_assoc()) {
                $match = ($year === 'all');
                
                if (!$match) {
                    // Looking at your EXACT schema date columns
                    $date_cols = ['user_created_on', 'date_paper', 'started_date', 'completed_date', 'start_date', 'publication_date', 'date_applied', 'date_granted', 'date_train'];
                    foreach ($date_cols as $dc) {
                        if (isset($row[$dc]) && !empty(trim((string)$row[$dc]))) {
                            $val_clean = trim((string)$row[$dc]);
                            if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) { $val_clean = "01-" . $val_clean; }
                            $ts = strtotime($val_clean);
                            if ($ts !== false && date('Y', $ts) == $year) {
                                $match = true;
                                break;
                            }
                        }
                    }
                }

                if ($match) {
                    $total_rows++;
                    $dept = !empty($row['department']) ? $row['department'] : 'Unknown';
                    if(!isset($dept_counts[$dept])) $dept_counts[$dept] = 0;
                    $dept_counts[$dept]++;
                    
                    // Counts unique disciplines perfectly
                    if ($distinct_col && isset($row[$distinct_col]) && !empty(trim($row[$distinct_col]))) {
                        $distinct_vals[trim($row[$distinct_col])] = true;
                    }
                    
                    // Prevents duplicate counting (e.g. 3 researchers on 1 project = 1 Unique Project)
                    if ($title_col && isset($row[$title_col]) && !empty(trim($row[$title_col]))) {
                        $unique_titles[strtolower(trim($row[$title_col]))] = true;
                    }
                }
            }
        }

        $chart_data = [];
        foreach($dept_counts as $dept => $cnt) {
            $chart_data[] = ['label' => $dept, 'y' => $cnt];
        }
        
        return [
            'total' => $total_rows, 
            'chart' => $chart_data, 
            'distinct_count' => count($distinct_vals),
            'unique_titles_count' => count($unique_titles)
        ];
    }

    // Mapping exactly to the tables in your screenshot
    $response = [
        'chart1' => getFilteredData($conn, 'tbl_researchdata', $year, true, null, null),
        'chart2' => getFilteredData($conn, 'tbl_researchconducted', $year, false, null, 'title'),
        'chart3' => getFilteredData($conn, 'tbl_publication', $year, false, null, 'title'),
        'chart4' => getFilteredData($conn, 'tbl_itelectualprop', $year, false, null, 'title'),
        'chart5' => getFilteredData($conn, 'tbl_paperpresentation', $year, false, 'discipline', 'title'),
        'chart6' => getFilteredData($conn, 'tbl_trainingsattended', $year, false, null, 'title'),
        'chart7' => getFilteredData($conn, 'tbl_extension_project_conducted', $year, false, null, 'title') 
    ];
    
    echo json_encode($response);
    exit;
}
// ==============================================================================


$object = new rms();

if(!$object->is_login())
{
    if (empty($object->base_url)) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['PHP_SELF']);
        $object->base_url = $protocol . '://' . $host . $scriptName . '/';
    }
    $baseUrl = rtrim($object->base_url, '/');
    header("Location: " . $baseUrl . "/");
    exit; 
}

include('includes/header.php');
$totalDepartments = $object->Get_total_departments();
?>

<style>
    .border-left-pink {
        border-left: 0.25rem solid #f23e5d !important;
    }
    
    .chart-type-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .chart-type-container div {
        display: inline-block;
    }
    .chart-type-container input[type="radio"] {
        display: none; 
    }
    .chart-type-container label {
        margin: 0;
        padding: 4px 12px;
        background-color: #f8f9fc;
        border: 1px solid #d1d3e2;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #5a5c69;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .chart-type-container label:hover {
        background-color: #eaecf4;
    }
    .chart-type-container input[type="radio"]:checked + label {
        background-color: #4e73df;
        color: #fff;
        border-color: #4e73df;
        box-shadow: 0 2px 4px rgba(78, 115, 223, 0.2);
    }
    
    .capture-zone {
        background-color: #ffffff;
        padding: 15px;
        border-radius: 0.35rem;
        position: relative;
    }
    
    .custom-legend-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 15px;
        padding: 15px;
        font-size: 11px;
        color: #5a5c69;
        border-top: 1px solid #eaecf4;
        background-color: #f8f9fc;
        border-radius: 0 0 0.35rem 0.35rem;
    }
    .legend-item {
        display: flex;
        align-items: flex-start;
        line-height: 1.3;
    }
    .legend-color-box {
        flex-shrink: 0; 
        display: inline-block;
        width: 12px;
        height: 12px;
        margin-right: 8px;
        border-radius: 2px;
        margin-top: 1px;
    }
    
    .dashboard-filter-wrap {
        display: flex;
        align-items: center;
        background: #fff;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #eaecf4;
    }
    .dashboard-filter-wrap select {
        border: none;
        background: transparent;
        font-weight: 700;
        color: #4e73df;
        cursor: pointer;
        outline: none;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var chartColors = [
        "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", 
        "#858796", "#5a5c69", "#f23e5d", "#e83e8c", "#fd7e14", 
        "#20c997", "#0dcaf0", "#6610f2", "#d63384", "#ffc107", 
        "#198754", "#dc3545", "#0d6efd", "#6c757d", "#212529", 
        "#ff9800", "#9c27b0", "#00bcd4", "#8bc34a"
    ];

    var departmentColorMap = {};
    var currentColorIndex = 0;

    function getConsistentColor(departmentName) {
        var cleanName = departmentName.trim();
        if (!departmentColorMap[cleanName]) {
            departmentColorMap[cleanName] = chartColors[currentColorIndex % chartColors.length];
            currentColorIndex++;
        }
        return departmentColorMap[cleanName];
    }

    function generateCustomLegend(data, containerId) {
        var html = '';
        data.forEach(function(dp) {
            html += '<div class="legend-item"><span class="legend-color-box" style="background-color:' + dp.color + ';"></span><span>' + dp.label + '</span></div>';
        });
        document.getElementById(containerId).innerHTML = html;
    }

    function openDetailsModal(departmentName, type, title) {
        $.post("fetch_subdep.php", { action: "fetch_modal_details", department: departmentName, type: type }, function(result) {
            try {
                var data = JSON.parse(result);
                var modalContent = '<table class="table table-bordered table-striped" id="subDepTable" width="100%" cellspacing="0"><thead><tr><th>' + departmentName + '</th></tr></thead><tbody>';
                if (data.length > 0) {
                    data.forEach(function(item) {
                        modalContent += '<tr><td>' + item + '</td></tr>';
                    });
                } else {
                    modalContent += '<tr><td class="text-center text-muted">No records found.</td></tr>';
                }
                modalContent += '</tbody></table>';
                
                $('#myModalLabel').text(title);
                $('#modalTableContainer').html(modalContent); 
                $('#modalSearch').val(''); 
                $('#myModal').modal('show'); 
            } catch(e) {
                console.error("Error parsing JSON response: ", e);
            }
        });
    }
</script>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
        
        <div class="dashboard-filter-wrap">
            <i class="fas fa-calendar-alt text-primary mr-2" style="font-size: 1.2rem;"></i>
            <select id="dashboardYearFilter">
                <option value="all">All Time (Whole History)</option>
                <?php 
                    $curr_year = date("Y");
                    for($y = $curr_year; $y >= 2010; $y--) {
                        echo "<option value=\"$y\">$y</option>";
                    }
                ?>
            </select>
        </div>
    </div>

    <?php if($object->is_master_user()) { ?>
        
    <form method="post" class=""></form> <div class="row">
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Researchers Count</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-researchers"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Trainings Attended Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-trainings"><i class="fas fa-spinner fa-spin"></i></div> 
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Intellectual property Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-ip"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-pink shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Paper Presentation Count</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-paper"><i class="fas fa-spinner fa-spin"></i></div> 
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
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">Total Number of Colleges</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-colleges"><?php echo number_format($totalDepartments);?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">Total discipline</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-discipline"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-table fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">Total Publications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-pub"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-th-list fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-default text-uppercase mb-1">Total Extension Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-projects"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>

    <div class="row">

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart1">Total Number of Departments: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart" name="chartType" value="pie"> <label for="pieChart">Pie</label></div>
                                <div><input type="radio" id="columnChart" name="chartType" value="column" checked> <label for="columnChart">Column</label></div>
                                <div><input type="radio" id="lineChart" name="chartType" value="line"> <label for="lineChart">Line</label></div>
                                <div><input type="radio" id="barChart" name="chartType" value="bar"> <label for="barChart">Bar</label></div>
                                <div><input type="radio" id="areaChart" name="chartType" value="area"> <label for="areaChart">Area</label></div>
                            </div>
                            <button id="downloadChart" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-1" class="capture-zone">
                            <div id="departmentChart" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart = new CanvasJS.Chart("departmentChart", {
                                animationEnabled: true,
                                title: { text: "Department-by Researchers' Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "column", dataPoints: [] }]
                            });
                            
                            chart.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'researchers', "Researchers List");
                            };

                            document.getElementById("downloadChart").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-1");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Department_Count_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart.exportChart({ format: "png" }); });
                                } else { chart.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart.options.data[0].type = document.querySelector('input[name="chartType"]:checked').value;
                                    chart.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus2" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus2">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart2">Total Number of Research Conducted: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus2">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart2" name="chartType2" value="pie"> <label for="pieChart2">Pie</label></div>
                                <div><input type="radio" id="columnChart2" name="chartType2" value="column"> <label for="columnChart2">Column</label></div>
                                <div><input type="radio" id="lineChart2" name="chartType2" value="line"> <label for="lineChart2">Line</label></div>
                                <div><input type="radio" id="barChart2" name="chartType2" value="bar" checked> <label for="barChart2">Bar</label></div>
                                <div><input type="radio" id="areaChart2" name="chartType2" value="area"> <label for="areaChart2">Area</label></div>
                            </div>
                            <button id="downloadChart2" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-2" class="capture-zone">
                            <div id="departmentChart2" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend2" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart2 = new CanvasJS.Chart("departmentChart2", {
                                animationEnabled: true,
                                title: { text: "Research Conducted-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "bar", dataPoints: [] }]
                            });
                            
                            chart2.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'research_conducted', 'Research Conducted');
                            };

                            document.getElementById("downloadChart2").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-2");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Research_Conducted_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart2.exportChart({ format: "png" }); });
                                } else { chart2.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType2"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart2.options.data[0].type = document.querySelector('input[name="chartType2"]:checked').value;
                                    chart2.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus3" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus3">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart3">Total Number of Publications: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus3">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart3" name="chartType3" value="pie"> <label for="pieChart3">Pie</label></div>
                                <div><input type="radio" id="columnChart3" name="chartType3" value="column" checked> <label for="columnChart3">Column</label></div>
                                <div><input type="radio" id="lineChart3" name="chartType3" value="line"> <label for="lineChart3">Line</label></div>
                                <div><input type="radio" id="barChart3" name="chartType3" value="bar"> <label for="barChart3">Bar</label></div>
                                <div><input type="radio" id="areaChart3" name="chartType3" value="area"> <label for="areaChart3">Area</label></div>
                            </div>
                            <button id="downloadChart3" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-3" class="capture-zone">
                            <div id="departmentChart3" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend3" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart3 = new CanvasJS.Chart("departmentChart3", {
                                animationEnabled: true,
                                title: { text: "Publications-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "column", dataPoints: [] }]
                            });
                            
                            chart3.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'publications', 'Publications');
                            };

                            document.getElementById("downloadChart3").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-3");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Publications_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart3.exportChart({ format: "png" }); });
                                } else { chart3.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType3"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart3.options.data[0].type = document.querySelector('input[name="chartType3"]:checked').value;
                                    chart3.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus4" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus4">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart4">Total Number of Intellectual Property: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus4">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart4" name="chartType4" value="pie"> <label for="pieChart4">Pie</label></div>
                                <div><input type="radio" id="columnChart4" name="chartType4" value="column"> <label for="columnChart4">Column</label></div>
                                <div><input type="radio" id="lineChart4" name="chartType4" value="line"> <label for="lineChart4">Line</label></div>
                                <div><input type="radio" id="barChart4" name="chartType4" value="bar" checked> <label for="barChart4">Bar</label></div>
                                <div><input type="radio" id="areaChart4" name="chartType4" value="area"> <label for="areaChart4">Area</label></div>
                            </div>
                            <button id="downloadChart4" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-4" class="capture-zone">
                            <div id="departmentChart4" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend4" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart4 = new CanvasJS.Chart("departmentChart4", {
                                animationEnabled: true,
                                title: { text: "Intellectual Property-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "bar", dataPoints: [] }]
                            });
                            
                            chart4.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'ip', 'Intellectual Property');
                            };

                            document.getElementById("downloadChart4").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-4");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Intellectual_Property_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart4.exportChart({ format: "png" }); });
                                } else { chart4.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType4"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart4.options.data[0].type = document.querySelector('input[name="chartType4"]:checked').value;
                                    chart4.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus5" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus5">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart5">Total Number of Paper Presentation: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus5">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart5" name="chartType5" value="pie"> <label for="pieChart5">Pie</label></div>
                                <div><input type="radio" id="columnChart5" name="chartType5" value="column" checked> <label for="columnChart5">Column</label></div>
                                <div><input type="radio" id="lineChart5" name="chartType5" value="line"> <label for="lineChart5">Line</label></div>
                                <div><input type="radio" id="barChart5" name="chartType5" value="bar"> <label for="barChart5">Bar</label></div>
                                <div><input type="radio" id="areaChart5" name="chartType5" value="area"> <label for="areaChart5">Area</label></div>
                            </div>
                            <button id="downloadChart5" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-5" class="capture-zone">
                            <div id="departmentChart5" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend5" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart5 = new CanvasJS.Chart("departmentChart5", {
                                animationEnabled: true,
                                title: { text: "Paper Presentation-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "column", dataPoints: [] }]
                            });
                            
                            chart5.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'paper_presentation', 'Paper Presentations');
                            };

                            document.getElementById("downloadChart5").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-5");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Paper_Presentation_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart5.exportChart({ format: "png" }); });
                                } else { chart5.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType5"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart5.options.data[0].type = document.querySelector('input[name="chartType5"]:checked').value;
                                    chart5.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus6" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus6">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart6">Total Number of Trainings Attended: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus6">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart6" name="chartType6" value="pie"> <label for="pieChart6">Pie</label></div>
                                <div><input type="radio" id="columnChart6" name="chartType6" value="column"> <label for="columnChart6">Column</label></div>
                                <div><input type="radio" id="lineChart6" name="chartType6" value="line"> <label for="lineChart6">Line</label></div>
                                <div><input type="radio" id="barChart6" name="chartType6" value="bar" checked> <label for="barChart6">Bar</label></div>
                                <div><input type="radio" id="areaChart6" name="chartType6" value="area"> <label for="areaChart6">Area</label></div>
                            </div>
                            <button id="downloadChart6" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-6" class="capture-zone">
                            <div id="departmentChart6" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend6" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart6 = new CanvasJS.Chart("departmentChart6", {
                                animationEnabled: true,
                                title: { text: "Trainings Attended-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "bar", dataPoints: [] }]
                            });
                            
                            chart6.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'trainings', 'Trainings Attended');
                            };

                            document.getElementById("downloadChart6").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-6");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Trainings_Attended_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart6.exportChart({ format: "png" }); });
                                } else { chart6.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType6"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart6.options.data[0].type = document.querySelector('input[name="chartType6"]:checked').value;
                                    chart6.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 mb-4">
            <div class="card shadow h-100">
                <a href="#collapsestatus7" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsestatus7">
                    <h6 class="m-0 font-weight-bold text-primary" id="title-chart7">Total Number of Extension Project Conducted: <i class="fas fa-spinner fa-spin"></i></h6>
                </a>
                <div class="collapse show" id="collapsestatus7">
                    <div class="card-body" style="padding-bottom: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <div class="chart-type-container">
                                <div><input type="radio" id="pieChart7" name="chartType7" value="pie" checked> <label for="pieChart7">Pie</label></div>
                                <div><input type="radio" id="columnChart7" name="chartType7" value="column"> <label for="columnChart7">Column</label></div>
                                <div><input type="radio" id="lineChart7" name="chartType7" value="line"> <label for="lineChart7">Line</label></div>
                                <div><input type="radio" id="barChart7" name="chartType7" value="bar"> <label for="barChart7">Bar</label></div>
                                <div><input type="radio" id="areaChart7" name="chartType7" value="area"> <label for="areaChart7">Area</label></div>
                            </div>
                            <button id="downloadChart7" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Download Chart</button>
                        </div>
                        
                        <div id="capture-zone-7" class="capture-zone">
                            <div id="departmentChart7" style="height: 350px; width: 100%;"></div>
                            <div id="departmentChartLegend7" class="custom-legend-container"></div>
                        </div>

                        <script>
                            var chart7 = new CanvasJS.Chart("departmentChart7", {
                                animationEnabled: true,
                                title: { text: "Extension Project Conducted-by Department's Count", fontSize: 16 },
                                axisX: { labelFormatter: function() { return ""; }, tickLength: 0, lineThickness: 1 },
                                data: [{ type: "pie", dataPoints: [] }]
                            });
                            
                            chart7.options.data[0].click = function(e) {
                                openDetailsModal(e.dataPoint.label, 'extension', 'Extension Projects Conducted');
                            };

                            document.getElementById("downloadChart7").addEventListener("click", function() {
                                var target = document.getElementById("capture-zone-7");
                                if(typeof html2canvas === 'function') {
                                    html2canvas(target, { scale: 2 }).then(function(canvas) {
                                        var link = document.createElement('a');
                                        link.download = 'Extension_Projects_Chart.png';
                                        link.href = canvas.toDataURL("image/png");
                                        link.click();
                                    }).catch(function() { chart7.exportChart({ format: "png" }); });
                                } else { chart7.exportChart({ format: "png" }); }
                            });

                            document.querySelectorAll('input[name="chartType7"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    chart7.options.data[0].type = document.querySelector('input[name="chartType7"]:checked').value;
                                    chart7.render();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
        
    </div> 
    
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="myModalLabel">Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="text" id="modalSearch" class="form-control mb-3" placeholder="Search records...">
                    <div id="modalTableContainer" style="max-height: 400px; overflow-y: auto;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php } // End if is_master_user ?>

</div> <?php include('includes/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#showModalBtn').click(function() {
            $('#myModal').modal('show');
        });

        $("#modalSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#subDepTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // ==============================================================================
        // DYNAMIC DASHBOARD AJAX FILTER LISTENER 
        // ==============================================================================
        $('#dashboardYearFilter').change(function() {
            var selectedYear = $(this).val();
            
            Swal.fire({
                title: 'Filtering Dashboard...', 
                text: 'Loading data for ' + (selectedYear === 'all' ? 'All Time' : selectedYear), 
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'dashboard.php',
                type: 'POST',
                data: { action: 'filter_dashboard', year: selectedYear },
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if(res.error) {
                        console.error(res.error);
                        Swal.fire('Error', 'Failed to retrieve data.', 'error');
                        return;
                    }

                    // 1. UPDATE ALL TOP CARDS WITH PERFECT SCHEMA MAPPING
                    if ($('#card-researchers').length) $('#card-researchers').text(res.chart1.total.toLocaleString());
                    if ($('#card-trainings').length) $('#card-trainings').text(res.chart6.unique_titles_count.toLocaleString());
                    if ($('#card-ip').length) $('#card-ip').text(res.chart4.unique_titles_count.toLocaleString());
                    if ($('#card-paper').length) $('#card-paper').text(res.chart5.unique_titles_count.toLocaleString());
                    if ($('#card-pub').length) $('#card-pub').text(res.chart3.unique_titles_count.toLocaleString());
                    
                    // Maps directly to tbl_paperpresentation -> discipline
                    if ($('#card-discipline').length) $('#card-discipline').text(res.chart5.distinct_count.toLocaleString()); 
                    
                    // Maps directly to tbl_extension_project_conducted -> unique titles
                    if ($('#card-projects').length) $('#card-projects').text(res.chart7.unique_titles_count.toLocaleString()); 

                    // 2. UPDATE CHART TITLES WITH REAL UNIQUE COUNTS
                    if ($('#title-chart1').length) $('#title-chart1').text('Total Number of Departments: ' + Object.keys(res.chart1.chart).length);
                    if ($('#title-chart2').length) $('#title-chart2').text('Total Number of Research Conducted: ' + res.chart2.unique_titles_count);
                    if ($('#title-chart3').length) $('#title-chart3').text('Total Number of Publications: ' + res.chart3.unique_titles_count);
                    if ($('#title-chart4').length) $('#title-chart4').text('Total Number of Intellectual Property: ' + res.chart4.unique_titles_count);
                    if ($('#title-chart5').length) $('#title-chart5').text('Total Number of Paper Presentation: ' + res.chart5.unique_titles_count);
                    if ($('#title-chart6').length) $('#title-chart6').text('Total Number of Trainings Attended: ' + res.chart6.unique_titles_count);
                    if ($('#title-chart7').length) $('#title-chart7').text('Total Number of Extension Project Conducted: ' + res.chart7.unique_titles_count);

                    // 3. HELPER FUNCTION TO UPDATE CHARTS & CUSTOM LEGENDS
                    function updateChart(chartObj, legendId, data) {
                        if (typeof chartObj !== 'undefined') {
                            var formattedData = data.map(function(item) {
                                return { label: item.label, y: parseInt(item.y), color: getConsistentColor(item.label) };
                            });
                            
                            generateCustomLegend(formattedData, legendId);
                            chartObj.options.data[0].dataPoints = formattedData;
                            chartObj.render();
                        }
                    }

                    // 4. INJECT NEW DATA INTO EXISTING CHARTS
                    updateChart(chart, "departmentChartLegend", res.chart1.chart);
                    updateChart(chart2, "departmentChartLegend2", res.chart2.chart);
                    updateChart(chart3, "departmentChartLegend3", res.chart3.chart);
                    updateChart(chart4, "departmentChartLegend4", res.chart4.chart);
                    updateChart(chart5, "departmentChartLegend5", res.chart5.chart);
                    updateChart(chart6, "departmentChartLegend6", res.chart6.chart);
                    updateChart(chart7, "departmentChartLegend7", res.chart7.chart);
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Connection Error', 'Could not connect to the database.', 'error');
                }
            });
        });
        
        // Auto-Trigger the AJAX function immediately on page load!
        // This erases the old static/hardcoded numbers and pulls the perfect math from your DB
        setTimeout(function() {
            $('#dashboardYearFilter').trigger('change');
        }, 50);
    });
</script>