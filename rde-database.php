<?php
include('core/rms.php');
$object = new rms();

// 1. Capture URL Parameters for Filters, Tabs, and Pagination
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'research';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$college = isset($_GET['college']) ? $_GET['college'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Items per page
$limit = 10;
$offset = ($page - 1) * $limit;

// Helper to build URLs so we don't lose search/filter state when clicking tabs or pages
function build_url($new_tab, $search, $college, $year, $page_num = 1) {
    return "?tab=$new_tab&search=" . urlencode($search) . "&college=" . urlencode($college) . "&year=" . urlencode($year) . "&page=$page_num";
}

// 2. Fetch Colleges for the Dropdown
$object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
$object->execute();
$colleges_list = $object->statement_result();

// 3. Determine Table and Columns based on Tab
$table = "";
$date_column = "";
$type_column = ""; // For badges

if ($tab == 'research') {
    $table = "tbl_researchconducted";
    $date_column = "completed_date";
    $type_column = "stat";
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
} elseif ($tab == 'pp') {
    $table = "tbl_paperpresentation";
    $date_column = "date_paper";
    $type_column = "type_pp";
} elseif ($tab == 'epc') {
    $table = "tbl_extension_project_conducted";
    $date_column = "start_date";
    $type_column = "status_exct";
} elseif ($tab == 'ext') {
    $table = "tbl_ext";
    $date_column = "period_implement";
    $type_column = "stat";
}

// 4. Build the Database Query Filters
$where = " WHERE rd.status = 1 "; // Only active researchers
$params = [];

// Safely append item status check only for tables that have a status column in your DB
$tables_with_status = ['tbl_researchconducted', 'tbl_publication', 'tbl_itelectualprop', 'tbl_extension_project_conducted', 'tbl_ext'];
if (in_array($table, $tables_with_status)) {
    $where .= " AND main.status = 1 ";
}

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

// Apply Year Filter
if ($year != 'all' && $date_column != "") {
    $where .= " AND main.$date_column LIKE :year ";
    $params[':year'] = "%$year%";
}

// 5. Pagination Logic: Get Total Row Count
$object->query = "
    SELECT COUNT(main.id) as total_rows 
    FROM $table main 
    LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
    $where 
";
$object->execute($params);
$count_result = $object->statement->fetch(PDO::FETCH_ASSOC);
$total_results = $count_result['total_rows'];
$total_pages = ceil($total_results / $limit);

// Ensure current page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// 6. Execute the Main Query with LIMIT and OFFSET
$object->query = "
    SELECT main.*, rd.firstName, rd.familyName, rd.department 
    FROM $table main 
    LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
    $where 
    ORDER BY main.id DESC 
    LIMIT $limit OFFSET $offset
";
$object->execute($params);
$results = $object->statement_result();
$current_page_count = $object->row_count();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDE Database | SDMU</title>
    <link rel="stylesheet" href="css/public_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        /* Pagination Button Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .page-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            border: 2px solid var(--primary-color);
        }
        .page-btn-outline {
            background-color: transparent;
            color: var(--primary-color);
        }
        .page-btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        .page-btn-active {
            background-color: var(--primary-color);
            color: var(--white);
        }
    </style>
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
            
            <div class="tabs-wrapper">
                <button class="scroll-arrow left-arrow" id="scrollLeftBtn" type="button"><i class="fas fa-chevron-left"></i></button>
                
                <div class="category-tabs" id="categoryTabs">
                    <a href="<?php echo build_url('research', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'research') echo 'active'; ?>">Research Conducted</a>
                    <a href="<?php echo build_url('publication', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'publication') echo 'active'; ?>">Publication</a>
                    <a href="<?php echo build_url('ip', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'ip') echo 'active'; ?>">Intellectual Property</a>
                    <a href="<?php echo build_url('pp', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'pp') echo 'active'; ?>">Paper Presentations</a>
                    <a href="<?php echo build_url('trainings', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'trainings') echo 'active'; ?>">Trainings Attended</a>
                    <a href="<?php echo build_url('epc', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'epc') echo 'active'; ?>">Extension Projects</a>
                    <a href="<?php echo build_url('ext', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'ext') echo 'active'; ?>">Extension Activities</a>
                </div>

                <button class="scroll-arrow right-arrow" id="scrollRightBtn" type="button"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="db-results" id="resultsContainer">
                
                <div class="results-meta">
                    <span>Showing Page <strong><?php echo $page; ?></strong> of <strong><?php echo max(1, $total_pages); ?></strong> (Total: <?php echo $total_results; ?> records)</span>
                </div>

                <?php if($current_page_count > 0): ?>
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

                    <?php if($total_pages > 1): ?>
                        <div class="pagination-container">
                            <?php if($page > 1): ?>
                                <a href="<?php echo build_url($tab, $search, $college, $year, $page - 1); ?>" class="page-btn page-btn-outline">&laquo; Prev</a>
                            <?php endif; ?>

                            <?php 
                            // Show page numbers
                            for($i = 1; $i <= $total_pages; $i++): 
                            ?>
                                <a href="<?php echo build_url($tab, $search, $college, $year, $i); ?>" class="page-btn <?php echo ($i == $page) ? 'page-btn-active' : 'page-btn-outline'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if($page < $total_pages): ?>
                                <a href="<?php echo build_url($tab, $search, $college, $year, $page + 1); ?>" class="page-btn page-btn-outline">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const tabsContainer = document.getElementById('categoryTabs');
        const leftBtn = document.getElementById('scrollLeftBtn');
        const rightBtn = document.getElementById('scrollRightBtn');
        const scrollAmount = 300; 

        function updateArrows() {
            if(!tabsContainer || !leftBtn || !rightBtn) return;
            
            leftBtn.style.display = tabsContainer.scrollLeft <= 5 ? 'none' : 'flex';
            const maxScrollLeft = tabsContainer.scrollWidth - tabsContainer.clientWidth;
            rightBtn.style.display = tabsContainer.scrollLeft >= maxScrollLeft - 5 ? 'none' : 'flex';
        }

        if(rightBtn) {
            rightBtn.addEventListener('click', () => {
                tabsContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        if(leftBtn) {
            leftBtn.addEventListener('click', () => {
                tabsContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if(tabsContainer) {
            tabsContainer.addEventListener('scroll', updateArrows);
            window.addEventListener('resize', updateArrows);
            
            updateArrows();

            // Auto-scroll to active tab on load
            const activeTab = tabsContainer.querySelector('.db-tab.active');
            if (activeTab) {
                const containerRect = tabsContainer.getBoundingClientRect();
                const tabRect = activeTab.getBoundingClientRect();
                
                if (tabRect.left < containerRect.left || tabRect.right > containerRect.right) {
                    tabsContainer.scrollLeft = activeTab.offsetLeft - containerRect.left - 40; 
                    updateArrows(); 
                }
            }
        }
    });
    </script>
</body>
</html>