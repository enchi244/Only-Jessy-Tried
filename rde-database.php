<?php
include('core/rms.php');
$object = new rms();

// 1. Capture URL Parameters for Filters, Tabs, and Pagination
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'hub'; // Defaults to the Hub directory
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$college = isset($_GET['college']) ? $_GET['college'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// --- NEW: DYNAMIC HEADER CONTENT ---
$header_content = [
    'research' => [
        'title' => 'Researches Conducted',
        'desc'  => 'Explore the foundational research projects and studies undertaken by our university.'
    ],
    'publication' => [
        'title' => 'Academic Publications',
        'desc'  => 'Discover peer-reviewed journals, articles, and published papers authored by our faculty.'
    ],
    'ip' => [
        'title' => 'Intellectual Properties',
        'desc'  => 'Browse granted patents, utility models, and innovations registered under the university.'
    ],

    'pp' => [
        'title' => 'Paper Presentations',
        'desc'  => 'View academic papers presented by our researchers at local, national, and international conferences.'
    ],
    'trainings' => [
        'title' => 'Trainings Attended',
        'desc'  => 'Track the professional development, seminars, and capacity-building programs of our researchers.'
    ],
    'epc' => [
        'title' => 'Extension Projects',
        'desc'  => 'Explore community-driven extension projects aimed at societal development and technology transfer.'
    ],
    'ext' => [
        'title' => 'Extension Activities',
        'desc'  => 'Discover various community extension activities and engagements led by our faculty.'
    ]
];

// Fallback just in case an unknown tab is selected
$current_header = isset($header_content[$tab]) ? $header_content[$tab] : [
    'title' => 'Research & Evaluation Outputs',
    'desc'  => 'Explore thousands of academic papers, intellectual properties, policies, and publications.'
];

$DEFAULT_COVER = 'img/default_research_cover.png';

// Items per page
$limit = 12;
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
} elseif ($tab == 'policy') {  
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

// Initialize pagination variables
$results = [];
$current_page_count = 0;
$total_pages = 0;
$total_results = 0;

// ONLY run queries if we are NOT on the hub page
if ($tab !== 'hub') {
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
}
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
                <a href="index.php" class="logo-text">SDMU <span class="highlight">WMSU</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">&larr; Back to Home</a></li>
                <li><a href="login.php" class="btn-login"><i class="fas fa-user-shield"></i> Admin Login</a></li>
            </ul>
        </div>
    </nav>

    <?php if ($tab === 'hub'): ?>
        
        <header class="db-header" style="padding-bottom: 140px;">
            <div class="container text-center fade-in-up">
                <h1>Research & Evaluation Outputs</h1>
                <p>Explore thousands of academic papers, intellectual properties, policies, and publications.</p>
                
                <form action="rde-database.php" method="GET" class="search-wrapper">
                    <input type="hidden" name="tab" value="research">
                    <div class="search-bar-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search by title, author, or keyword...">
                        <button type="submit" class="btn btn-primary" id="searchBtn">Search</button>
                    </div>
                </form>
            </div>
        </header>

        <main class="rde-hub-section fade-in-up delay-1">
            <div class="container rde-hub-grid">
                
                <a href="?tab=research" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-microscope"></i></div>
                    <h3>Researches Conducted</h3>
                    <p>Explore the foundational research projects and studies undertaken by our university faculty and students.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=publication" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-book-open"></i></div>
                    <h3>Academic Publications</h3>
                    <p>Discover peer-reviewed journals, articles, and published papers authored by our dedicated faculty.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=ip" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-lightbulb"></i></div>
                    <h3>Intellectual Properties</h3>
                    <p>Browse granted patents, utility models, and innovative copyrights registered under the university.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=pp" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Paper Presentations</h3>
                    <p>View academic papers presented by our researchers at local, national, and international conferences.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=trainings" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-certificate"></i></div>
                    <h3>Trainings Attended</h3>
                    <p>Track the professional development, seminars, and capacity-building programs of our researchers.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=epc" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-project-diagram"></i></div>
                    <h3>Extension Projects</h3>
                    <p>Explore community-driven extension projects aimed at societal development and technology transfer.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

                <a href="?tab=ext" class="hub-category-card">
                    <div class="hub-icon-wrapper"><i class="fas fa-hands-helping"></i></div>
                    <h3>Extension Activities</h3>
                    <p>Discover various community extension activities, seminars, and engagements led by our faculty.</p>
                    <span class="hub-explore-link">Explore Category <i class="fas fa-arrow-right"></i></span>
                </a>

            </div>
        </main>

    <?php else: ?>

        <header class="db-header category-header">
            <div class="container text-center fade-in-up">
                <h1><?php echo htmlspecialchars($current_header['title']); ?></h1>
                <p><?php echo htmlspecialchars($current_header['desc']); ?></p>
            </div>
        </header>

        <main class="db-container container fade-in-up delay-1">
            
            <form action="rde-database.php" method="GET" class="top-filter-bar">
                
                <div class="filter-inputs-group">
                    
                    <div class="filter-group">
                        <select name="tab" class="custom-select" onchange="this.form.submit()" style="border-color: var(--primary-color); font-weight: 600; color: var(--primary-color);">
                            <option value="research" <?php if($tab == 'research') echo 'selected'; ?>>Research Conducted</option>
                            <option value="publication" <?php if($tab == 'publication') echo 'selected'; ?>>Academic Publications</option>
                            <option value="ip" <?php if($tab == 'ip') echo 'selected'; ?>>Intellectual Properties</option>
                            <option value="pp" <?php if($tab == 'pp') echo 'selected'; ?>>Paper Presentations</option>
                            <option value="trainings" <?php if($tab == 'trainings') echo 'selected'; ?>>Trainings Attended</option>
                            <option value="epc" <?php if($tab == 'epc') echo 'selected'; ?>>Extension Projects</option>
                            <option value="ext" <?php if($tab == 'ext') echo 'selected'; ?>>Extension Activities</option>
                        </select>
                    </div>

                    <div class="search-bar-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search by title, author, or keyword..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <select name="college" class="custom-select">
                            <option value="all">All Colleges / Units</option>
                            <?php foreach($colleges_list as $col): ?>
                                <option value="<?php echo htmlspecialchars($col['category_name']); ?>" <?php if($college == $col['category_name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($col['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <select name="year" class="custom-select">
                            <option value="all">All Years</option>
                            <?php 
                            $currentYear = date("Y");
                            for($i = $currentYear; $i >= 2010; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php if($year == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" id="applyFilters">Apply</button>
                    <a href="?tab=hub" class="btn btn-secondary" title="Back to Hub"><i class="fas fa-th-large"></i></a>
                </div>
            </form>

            <section class="db-content">

                <div class="db-results" id="resultsContainer">
                    
                    <div class="results-meta">
                        <span>Showing Page <strong><?php echo $page; ?></strong> of <strong><?php echo max(1, $total_pages); ?></strong> (Total: <?php echo $total_results; ?> records)</span>
                    </div>

                    <?php if($current_page_count > 0): ?>
                        
                        <?php if ($tab === 'research'): ?>
                            <?php
                                // Split the results for the blog layout
                                $featured_item = isset($results[0]) ? $results[0] : null;
                                $trending_items = array_slice($results, 1, 3); // Gets items 2, 3, and 4
                                $grid_items = array_slice($results, 4);        // Gets everything else
                            ?>

                            <div class="research-blog-top">
                                <?php if($featured_item): $row = $featured_item; ?>
                                    <div class="featured-research-post data-card">
                                        
                                        <?php 
                                            $db_cover = trim($row['cover_photo'] ?? '');
                                            $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : 'img/default_research_cover.png'; 
                                        ?>
                                        <div class="post-image-large" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;">
                                            <div class="card-badge"><i class="fas fa-fire"></i> Top Viewed</div>
                                        </div>
                                        
                                        <div class="post-content">
                                            <h2 style="font-size: 2rem; font-family: 'Playfair Display', serif; color: var(--dark-bg); margin-bottom: 15px; line-height: 1.2;">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </h2>
                                            
                                            <p class="author-line" style="font-size: 1.1rem; margin-bottom: 15px;">
                                                <i class="fas fa-user-circle text-primary"></i> <strong>Author/Lead:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                            </p>
                                            
                                            <div class="card-details-drawer">
                                                <?php
                                                $object->query = "SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = '".$row['id']."' AND col.researcher_id != '".$row['researcherID']."'";
                                                $object->execute();
                                                $co_res = $object->statement->fetch(PDO::FETCH_ASSOC);
                                                $co_authors = !empty($co_res['co_authors']) ? $co_res['co_authors'] : 'None';
                                                ?>
                                                <div class="drawer-grid">
                                                    <div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> <?php echo htmlspecialchars($co_authors); ?></div>
                                                    <div><i class="fas fa-bullseye text-success"></i> <strong>SDGs:</strong> <?php echo htmlspecialchars($row['sdgs'] ?? 'N/A'); ?></div>
                                                    <div><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> <?php echo htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A'); ?></div>
                                                </div>
                                            </div>

                                            <div class="card-footer" style="margin-top: auto;">
                                                <span class="college-tag"><i class="fas fa-university mr-1 text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                                <span class="date-tag"><i class="far fa-calendar-alt mr-1"></i> <?php echo !empty($row['completed_date']) ? date('Y', strtotime(str_replace('/', '-', $row['completed_date']))) : 'N/A'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($trending_items)): ?>
                                    <div class="trending-research-list">
                                        <h4 class="trending-header">Also Trending</h4>
                                        <?php foreach($trending_items as $row): ?>
                                            <div class="trending-card data-card">
                                                
                                                <?php 
                                                    $db_cover = trim($row['cover_photo'] ?? '');
                                                    $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : 'img/default_research_cover.png'; 
                                                ?>
                                                <div class="trending-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;"></div>                                                
                                                <div class="trending-content">
                                                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                                    <p class="author-line" style="font-size: 0.85rem; margin-bottom: 10px;">
                                                        <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                                    </p>
                                                    
                                                    <div class="card-details-drawer">
                                                        <div class="drawer-grid">
                                                            <div class="full-width"><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> <?php echo htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A'); ?></div>
                                                        </div>
                                                    </div>

                                                    <div class="card-footer" style="padding-top: 5px; margin-top: 0;">
                                                        <span class="college-tag" style="background: transparent; padding: 0;"><i class="fas fa-university text-primary"></i> <?php echo htmlspecialchars($row['department']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($grid_items)): ?>
                                <div class="db-results-grid">
                                    <?php foreach($grid_items as $row): ?>
                                        <div class="hero-card data-card">
                                            
                                            <?php 
                                                $db_cover = trim($row['cover_photo'] ?? '');
                                                $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : 'img/default_research_cover.png'; 
                                            ?>
                                            <div class="hero-card-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;"></div>                                            
                                            <div class="hero-card-content">
                                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                                <p class="author-line">
                                                    <strong>Author:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                                </p>
                                                
                                                <div class="card-details-drawer">
                                                    <div class="drawer-grid">
                                                        <div><i class="fas fa-bullseye text-success"></i> <strong>SDGs:</strong> <?php echo htmlspecialchars($row['sdgs'] ?? 'N/A'); ?></div>
                                                        <div class="full-width"><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> <?php echo htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A'); ?></div>
                                                    </div>
                                                </div>

                                                <div class="card-footer">
                                                    <span class="college-tag"><i class="fas fa-university text-primary"></i> <?php echo htmlspecialchars($row['department']); ?></span>
                                                    <span class="date-tag"><?php echo !empty($row['completed_date']) ? date('Y', strtotime(str_replace('/', '-', $row['completed_date']))) : 'N/A'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="db-results-grid">
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
                                            <?php if($tab == 'publication'): ?>
                                                <?php
                                                $object->query = "SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors FROM tbl_publication_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.publication_id = '".$row['id']."' AND col.researcher_id != '".$row['researcherID']."'";
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
                            </div> <?php endif; ?> 
                            
                        <?php if($total_pages > 1): ?>
                            <div class="pagination-container">
                                
                                <?php if($page > 1): ?>
                                    <a href="<?php echo build_url($tab, $search, $college, $year, $page - 1); ?>" class="page-btn page-btn-outline">&laquo; Prev</a>
                                <?php endif; ?>

                                <?php 
                                $max_links = 10; // Max number of page buttons to show
                                $start_page = max(1, $page - floor($max_links / 2));
                                $end_page = $start_page + $max_links - 1;

                                if ($end_page > $total_pages) {
                                    $end_page = $total_pages;
                                    $start_page = max(1, $end_page - $max_links + 1);
                                }
                                ?>

                                <?php if($start_page > 1): ?>
                                    <a href="<?php echo build_url($tab, $search, $college, $year, 1); ?>" class="page-btn page-btn-outline">1</a>
                                    <?php if($start_page > 2): ?>
                                        <span class="page-btn-dots">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                                    <a href="<?php echo build_url($tab, $search, $college, $year, $i); ?>" class="page-btn <?php echo ($i == $page) ? 'page-btn-active' : 'page-btn-outline'; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if($end_page < $total_pages): ?>
                                    <?php if($end_page < $total_pages - 1): ?>
                                        <span class="page-btn-dots">...</span>
                                    <?php endif; ?>
                                    <a href="<?php echo build_url($tab, $search, $college, $year, $total_pages); ?>" class="page-btn page-btn-outline"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

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

    <?php endif; ?>

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