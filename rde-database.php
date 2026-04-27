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

// Helper to build URLs
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
$type_column = ""; 

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
} elseif ($tab == 'policy') {  // <--- NEW: Added Research Policy Support
    $table = "tbl_research_policy";
    $date_column = "date_implemented";
    $type_column = ""; 
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
$where = " WHERE rd.status = 1 "; 
$params = [];

$tables_with_status = ['tbl_researchconducted', 'tbl_publication', 'tbl_itelectualprop', 'tbl_research_policy', 'tbl_extension_project_conducted', 'tbl_ext'];
if (in_array($table, $tables_with_status)) {
    $where .= " AND main.status = 1 ";
}

if ($search != '') {
    $where .= " AND (main.title LIKE :search OR rd.familyName LIKE :search OR rd.firstName LIKE :search) ";
    $params[':search'] = "%$search%";
}

if ($college != 'all') {
    $where .= " AND rd.department = :college ";
    $params[':college'] = $college;
}

if ($year != 'all' && $date_column != "") {
    $where .= " AND main.$date_column LIKE :year ";
    $params[':year'] = "%$year%";
}

// 5. Pagination Logic
$object->query = "SELECT COUNT(main.id) as total_rows FROM $table main LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id $where";
$object->execute($params);
$count_result = $object->statement->fetch(PDO::FETCH_ASSOC);
$total_results = $count_result['total_rows'];
$total_pages = ceil($total_results / $limit);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// 6. Execute the Main Query
$object->query = "SELECT main.*, rd.firstName, rd.familyName, rd.department FROM $table main LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id $where ORDER BY main.id DESC LIMIT $limit OFFSET $offset";
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
</head>
<body class="database-body">

    <nav id="navbar" class="scrolled database-nav">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php" class="logo-text">SDMU <span class="highlight">SJCSI</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">&larr; Back to Home</a></li>
                <li><a href="login.php" class="btn-login"><i class="fas fa-user-shield"></i> Admin Login</a></li>
            </ul>
        </div>
    </nav>

    <header class="db-header">
        <div class="container text-center fade-in-up">
            <h1>Research & Evaluation Outputs</h1>
            <p>Explore thousands of academic papers, intellectual properties, policies, and publications.</p>
            
            <form action="rde-database.php" method="GET" class="search-wrapper">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <input type="hidden" name="college" value="<?php echo htmlspecialchars($college); ?>">
                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
                
                <div class="search-bar-container">
                    <i class="fas fa-search search-icon"></i>
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
                    <a href="<?php echo build_url('policy', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'policy') echo 'active'; ?>">Research Policy</a>
                    <a href="<?php echo build_url('pp', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'pp') echo 'active'; ?>">Paper Presentations</a>
                    <a href="<?php echo build_url('trainings', $search, $college, $year); ?>" class="db-tab <?php if($tab == 'trainings') echo 'active'; ?>">Trainings</a>
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
                            
                            <p class="author-line">
                                <strong>Author/Lead:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                            </p>
                            
                            <div class="card-details-drawer">
                                <?php if($tab == 'research'): ?>
                                    <?php
                                    // Fetch Co-Authors for Research (EXCLUDING THE LEAD AUTHOR)
                                    $object->query = "
                                        SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors 
                                        FROM tbl_research_collaborators col 
                                        JOIN tbl_researchdata d ON col.researcher_id = d.id 
                                        WHERE col.research_id = '".$row['id']."' 
                                        AND col.researcher_id != '".$row['researcherID']."'
                                    ";
                                    $object->execute();
                                    $co_res = $object->statement->fetch(PDO::FETCH_ASSOC);
                                    $co_authors = !empty($co_res['co_authors']) ? $co_res['co_authors'] : 'None';
                                    ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> <?php echo htmlspecialchars($co_authors); ?></div>
                                        <div><i class="fas fa-bullseye text-success"></i> <strong>SDGs:</strong> <?php echo htmlspecialchars($row['sdgs'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> <?php echo htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A'); ?></div>
                                    </div>

                                <?php elseif($tab == 'publication'): ?>
                                    <?php
                                    // Fetch Co-Authors for Publication (EXCLUDING THE LEAD AUTHOR)
                                    $object->query = "
                                        SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors 
                                        FROM tbl_publication_collaborators col 
                                        JOIN tbl_researchdata d ON col.researcher_id = d.id 
                                        WHERE col.publication_id = '".$row['id']."' 
                                        AND col.researcher_id != '".$row['researcherID']."'
                                    ";
                                    $object->execute();
                                    $co_res = $object->statement->fetch(PDO::FETCH_ASSOC);
                                    $co_authors = !empty($co_res['co_authors']) ? $co_res['co_authors'] : 'None';
                                    ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> <?php echo htmlspecialchars($co_authors); ?></div>
                                        <div class="full-width"><i class="fas fa-book-open text-danger"></i> <strong>Journal:</strong> <?php echo htmlspecialchars($row['journal'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-barcode text-secondary"></i> <strong>ISSN/ISBN:</strong> <?php echo htmlspecialchars($row['issn_isbn'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-hashtag text-info"></i> <strong>Vol/Issue:</strong> <?php echo htmlspecialchars($row['vol_num_issue_num'] ?? 'N/A'); ?></div>
                                    </div>

                                <?php elseif($tab == 'ip'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> <?php echo htmlspecialchars($row['coauth'] ?? 'None'); ?></div>
                                        <div><i class="far fa-calendar-check text-success"></i> <strong>Granted:</strong> <?php echo htmlspecialchars($row['date_granted'] ?? 'Pending'); ?></div>
                                    </div>

                                <?php elseif($tab == 'policy'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-align-left text-primary"></i> <strong>Abstract:</strong> <?php echo htmlspecialchars($row['abstract'] ?? 'N/A'); ?></div>
                                        <div class="full-width"><i class="fas fa-info-circle text-info"></i> <strong>Description:</strong> <?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></div>
                                    </div>

                                <?php elseif($tab == 'pp'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Organizer:</strong> <?php echo htmlspecialchars($row['conference_organizer'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> <?php echo htmlspecialchars($row['conference_venue'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-globe text-info"></i> <strong>Level:</strong> <?php echo htmlspecialchars($row['conference_title'] ?? 'N/A'); ?></div>
                                    </div>

                                <?php elseif($tab == 'trainings'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Sponsor:</strong> <?php echo htmlspecialchars($row['sponsor_org'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> <?php echo htmlspecialchars($row['venue'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-clock text-warning"></i> <strong>Hours:</strong> <?php echo htmlspecialchars($row['totnh'] ?? '0'); ?></div>
                                    </div>

                                <?php elseif($tab == 'epc'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Beneficiaries:</strong> <?php echo htmlspecialchars($row['target_beneficiaries_communities'] ?? 'N/A'); ?></div>
                                        <div class="full-width"><i class="fas fa-handshake text-info"></i> <strong>Partners:</strong> <?php echo htmlspecialchars($row['partners'] ?? 'None'); ?></div>
                                    </div>

                                <?php elseif($tab == 'ext'): ?>
                                    <div class="drawer-grid">
                                        <div class="full-width"><i class="fas fa-info-circle text-primary"></i> <strong>Description:</strong> <?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-user-tie text-info"></i> <strong>Lead:</strong> <?php echo htmlspecialchars($row['proj_lead'] ?? 'N/A'); ?></div>
                                        <div><i class="fas fa-users text-warning"></i> <strong>Assist:</strong> <?php echo htmlspecialchars($row['assist_coordinators'] ?? 'None'); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <span class="college-tag">
                                    <i class="fas fa-university mr-1 text-primary"></i>
                                    <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?>
                                </span>
                                <span class="date-tag">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    <?php 
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

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
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
            
            if (tabsContainer.scrollLeft <= 5) {
                leftBtn.style.opacity = '0';
                leftBtn.style.pointerEvents = 'none';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.pointerEvents = 'auto';
            }
            
            const maxScrollLeft = tabsContainer.scrollWidth - tabsContainer.clientWidth;
            if (tabsContainer.scrollLeft >= maxScrollLeft - 2) {
                rightBtn.style.opacity = '0';
                rightBtn.style.pointerEvents = 'none';
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.style.pointerEvents = 'auto';
            }
        }

        if(rightBtn) rightBtn.addEventListener('click', () => tabsContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
        if(leftBtn) leftBtn.addEventListener('click', () => tabsContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));

        if(tabsContainer) {
            tabsContainer.addEventListener('scroll', updateArrows);
            window.addEventListener('resize', updateArrows);
            updateArrows();

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