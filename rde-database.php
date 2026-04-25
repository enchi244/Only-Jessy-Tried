<?php
include('core/rms.php');
$object = new rms();

// 1. Capture URL Parameters for Filters and Tabs
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'research';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$college = isset($_GET['college']) ? $_GET['college'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';

// Helper to build URLs so we don't lose search/filter state when clicking tabs
function build_url($new_tab, $search, $college, $year) {
    return "?tab=$new_tab&search=" . urlencode($search) . "&college=" . urlencode($college) . "&year=" . urlencode($year);
}

// 2. Fetch Colleges for the Dropdown
$object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
$object->execute();
$colleges_list = $object->statement_result();

// 3. Build the Database Query based on the Active Tab
$where = " WHERE rd.status = 1 "; // Only active researchers
$params = [];

// Apply Search Filter
if ($search != '') {
    $where .= " AND (main.title LIKE :search OR rd.familyName LIKE :search OR rd.firstName LIKE :search) ";
    $params[':search'] = "%$search%";
}

// Apply College Filter
if ($college != 'all') {
    $where .= " AND rd.department = :college ";
    $params[':college'] = $college;
}

// Determine Table and Date Column based on Tab
$table = "";
$date_column = "";
$type_column = ""; // For badges

if ($tab == 'research') {
    $table = "tbl_researchconducted";
    $date_column = "completed_date";
    $type_column = "stat"; // Status as badge
} elseif ($tab == 'publication') {
    $table = "tbl_publication";
    $date_column = "publication_date";
    $type_column = "indexing"; 
} elseif ($tab == 'ip') {
    $table = "tbl_itelectualprop";
    $date_column = "date_applied";
    $type_column = "type";
} elseif ($tab == 'trainings') {
    $table = "tbl_trainingsattended";
    $date_column = "date_train";
    $type_column = "lvl";
}

// Apply Year Filter
if ($year != 'all' && $date_column != "") {
    // Extracts the year from the date column (assuming standard date formats)
    $where .= " AND main.$date_column LIKE :year ";
    $params[':year'] = "%$year%";
}

// Execute the Query
$object->query = "
    SELECT main.*, rd.firstName, rd.familyName, rd.department 
    FROM $table main 
    LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
    $where 
    ORDER BY main.id DESC 
    LIMIT 100
";
$object->execute($params);
$results = $object->statement_result();
$result_count = $object->row_count();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDE Database | SDMU WMSU</title>
    <link rel="stylesheet" href="css/public_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="database-body">

    <nav id="navbar" class="scrolled database-nav">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php" class="logo-text">SDMU <span class="highlight">SJCSI</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">&larr; Back to Home</a></li>
                <li>
                    <a href="login.php" class="btn-login">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-bottom: -2px;">
                            <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1z"/>
                            <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117zM11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5zM4 1.934V15h6V1.077l-6 .857z"/>
                        </svg>
                        Admin Login
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <header class="db-header">
        <div class="container text-center fade-in-up">
            <h1>Research & Evaluation Outputs</h1>
            <p>Explore thousands of academic papers, intellectual properties, and publications.</p>
            
            <form action="rde-database.php" method="GET" class="search-wrapper">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <input type="hidden" name="college" value="<?php echo htmlspecialchars($college); ?>">
                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
                
                <div class="search-bar-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" id="searchInput" placeholder="Search by title, author, or keyword..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary" id="searchBtn">Search</button>
                </div>
            </form>
        </div>
    </header>

    <main class="db-container container fade-in-up delay-1">
        
        <aside class="db-filters">
            <form action="rde-database.php" method="GET" class="filter-card">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                
                <h3>Advanced Filters</h3>
                
                <div class="filter-group">
                    <label for="collegeFilter">College / Unit</label>
                    <select name="college" id="collegeFilter" class="custom-select">
                        <option value="all">All Colleges & Units</option>
                        <?php foreach($colleges_list as $col): ?>
                            <option value="<?php echo htmlspecialchars($col['category_name']); ?>" <?php if($college == $col['category_name']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($col['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="yearFilter">Year</label>
                    <select name="year" id="yearFilter" class="custom-select">
                        <option value="all">All Years</option>
                        <?php 
                        $currentYear = date("Y");
                        for($i = $currentYear; $i >= 2010; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php if($year == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-secondary w-100" id="applyFilters">Apply Filters</button>
            </form>
        </aside>

        <section class="db-content">
            
            <div class="category-tabs">
                <a href="<?php echo build_url('research', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'research') echo 'active'; ?>" style="text-decoration:none;">Research Conducted</a>
                <a href="<?php echo build_url('publication', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'publication') echo 'active'; ?>" style="text-decoration:none;">Publication</a>
                <a href="<?php echo build_url('ip', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'ip') echo 'active'; ?>" style="text-decoration:none;">Intellectual Property</a>
                <a href="<?php echo build_url('trainings', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'trainings') echo 'active'; ?>" style="text-decoration:none;">Trainings Attended</a>
            </div>

            <div class="db-results" id="resultsContainer">
                
                <div class="results-meta">
                    <span>Showing <strong><?php echo $result_count; ?></strong> results for your query</span>
                </div>

                <?php if($result_count > 0): ?>
                    <?php foreach($results as $row): ?>
                        <div class="data-card">
                            <?php if(!empty($row[$type_column])): ?>
                                <div class="card-badge"><?php echo htmlspecialchars($row[$type_column]); ?></div>
                            <?php endif; ?>
                            
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            
                            <p style="color:#555; margin-bottom: 10px;">
                                <strong>Author/Lead:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                            </p>
                            
                            <div class="card-footer" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; display:flex; justify-content:space-between; align-items:center;">
                                <span class="college-tag" style="background:#e9ecef; padding:4px 8px; border-radius:4px; font-size:12px;">
                                    <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?>
                                </span>
                                <span class="date-tag" style="font-weight:bold; color:#f23e5d;">
                                    <?php 
                                    // Extract just the year if possible
                                    $date_val = $row[$date_column] ?? '';
                                    echo !empty($date_val) ? date('Y', strtotime(str_replace('/', '-', $date_val))) : 'N/A'; 
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="data-card">
                        <h3>No Results Found</h3>
                        <p>We couldn't find any records matching your search and filters. Please try adjusting them.</p>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <script src="js/public_app.js"></script>
</body>
</html>