<?php
include('core/rms.php');
$object = new rms();

// 1. Capture URL Parameters for Filters, Tabs, and Pagination
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'hub'; // Defaults to the Hub directory
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$college = isset($_GET['college']) ? $_GET['college'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// --- DYNAMIC HEADER CONTENT ---
$header_content = [
    'research' => [
        'title' => 'Researches',
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
        'title' => 'Trainings',
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

$current_header = isset($header_content[$tab]) ? $header_content[$tab] : [
    'title' => 'Research & Evaluation Outputs',
    'desc'  => 'Explore thousands of academic papers, intellectual properties, policies, and publications.'
];

$DEFAULT_COVER = 'img/default_research_cover.png';
$limit = 12;
$offset = ($page - 1) * $limit;

function build_url($new_tab, $search, $college, $year, $page_num = 1) {
    return "?tab=$new_tab&search=" . urlencode($search) . "&college=" . urlencode($college) . "&year=" . urlencode($year) . "&page=$page_num";
}

// Helper function to generate the unique "Drawer" details for each card based on the tab
function getDrawerHtml($tab, $row, $object) {
    $html = '<div class="card-details-drawer"><div class="drawer-grid">';
    
    if ($tab == 'research') {
        $object->query = "SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = '".$row['id']."' AND col.researcher_id != '".$row['researcherID']."'";
        $object->execute();
        $co_res = $object->statement->fetch(PDO::FETCH_ASSOC);
        $co_authors = !empty($co_res['co_authors']) ? $co_res['co_authors'] : 'None';
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($co_authors).'</div>';
        $html .= '<div><i class="fas fa-bullseye text-success"></i> <strong>SDGs:</strong> '.htmlspecialchars($row['sdgs'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> '.htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A').'</div>';
    } 
    elseif ($tab == 'publication') {
        $object->query = "SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors FROM tbl_publication_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.publication_id = '".$row['id']."' AND col.researcher_id != '".$row['researcherID']."'";
        $object->execute();
        $co_res = $object->statement->fetch(PDO::FETCH_ASSOC);
        $co_authors = !empty($co_res['co_authors']) ? $co_res['co_authors'] : 'None';
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($co_authors).'</div>';
        $html .= '<div class="full-width"><i class="fas fa-book-open text-danger"></i> <strong>Journal:</strong> '.htmlspecialchars($row['journal'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-barcode text-secondary"></i> <strong>ISSN/ISBN:</strong> '.htmlspecialchars($row['issn_isbn'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-hashtag text-info"></i> <strong>Vol/Issue:</strong> '.htmlspecialchars($row['vol_num_issue_num'] ?? 'N/A').'</div>';
    } 
    elseif ($tab == 'ip') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($row['coauth'] ?? 'None').'</div>';
        $html .= '<div><i class="far fa-calendar-check text-success"></i> <strong>Granted:</strong> '.htmlspecialchars($row['date_granted'] ?? 'Pending').'</div>';
    } 
    elseif ($tab == 'pp') {
        $html .= '<div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Organizer:</strong> '.htmlspecialchars($row['conference_organizer'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> '.htmlspecialchars($row['conference_venue'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-globe text-info"></i> <strong>Level:</strong> '.htmlspecialchars($row['conference_title'] ?? 'N/A').'</div>';
    } 
    elseif ($tab == 'trainings') {
        $html .= '<div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Sponsor:</strong> '.htmlspecialchars($row['sponsor_org'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> '.htmlspecialchars($row['venue'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-clock text-warning"></i> <strong>Hours:</strong> '.htmlspecialchars($row['totnh'] ?? '0').'</div>';
    } 
    elseif ($tab == 'epc') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Beneficiaries:</strong> '.htmlspecialchars($row['target_beneficiaries_communities'] ?? 'N/A').'</div>';
        $html .= '<div class="full-width"><i class="fas fa-handshake text-info"></i> <strong>Partners:</strong> '.htmlspecialchars($row['partners'] ?? 'None').'</div>';
    } 
    elseif ($tab == 'ext') {
        $html .= '<div class="full-width"><i class="fas fa-info-circle text-primary"></i> <strong>Description:</strong> '.htmlspecialchars($row['description'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-user-tie text-info"></i> <strong>Lead:</strong> '.htmlspecialchars($row['proj_lead'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-users text-warning"></i> <strong>Assist:</strong> '.htmlspecialchars($row['assist_coordinators'] ?? 'None').'</div>';
    }
    
    $html .= '</div></div>';
    return $html;
}

// 2. Fetch Colleges for the Dropdown
$object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
$object->execute();
$colleges_list = $object->statement_result();

// 3. Determine Table and Columns based on Tab
$table = "";
$date_column = "";

if ($tab == 'research') {
    $table = "tbl_researchconducted";
    $date_column = "completed_date";
} elseif ($tab == 'publication') {
    $table = "tbl_publication";
    $date_column = "publication_date";
} elseif ($tab == 'ip') {
    $table = "tbl_itelectualprop";
    $date_column = "date_applied";
} elseif ($tab == 'trainings') {
    $table = "tbl_trainingsattended";
    $date_column = "date_train";
} elseif ($tab == 'pp') {
    $table = "tbl_paperpresentation";
    $date_column = "date_paper";
} elseif ($tab == 'epc') {
    $table = "tbl_extension_project_conducted";
    $date_column = "start_date";
} elseif ($tab == 'ext') {
    $table = "tbl_ext";
    $date_column = "period_implement";
}

// 4. Build the Database Query Filters
$where = " WHERE rd.status = 1 AND main.status = 1 "; 
$params = [];

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

// Initialize variables
$featured_item = null;
$trending_items = [];
$grid_items = [];
$current_page_count = 0;
$total_pages = 0;
$total_results = 0;

// 5. Fetch Data Logic (Applied natively to ALL tabs)
// 5. Fetch Data Logic (Applied natively to ALL tabs)
if ($tab !== 'hub' && $table !== '') {
    
    // 5a. Fetch Top Viewed (All-time)
    $top_query = "SELECT main.*, rd.firstName, rd.familyName, rd.department, 
                  (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = :tab_type1) as total_views
                  FROM $table main 
                  LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
                  $where 
                  ORDER BY total_views DESC LIMIT 1";
    
    $object->query = $top_query;
    $top_params = $params; // Clone the base filters (search, college, year)
    $top_params[':tab_type1'] = $tab;
    $object->execute($top_params);
    
    if ($object->row_count() > 0) {
        $featured_item = $object->statement->fetch(PDO::FETCH_ASSOC);
    }
    
    $exclude_ids = [];
    if ($featured_item) $exclude_ids[] = $featured_item['id'];

    // 5b. Fetch Trending (Last 7 Days)
    $exclude_str = !empty($exclude_ids) ? "AND main.id NOT IN (" . implode(',', $exclude_ids) . ")" : "";
    $trend_query = "SELECT main.*, rd.firstName, rd.familyName, rd.department, 
                    (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = :tab_type2 AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_views,
                    (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = :tab_type3) as total_views
                    FROM $table main 
                    LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
                    $where $exclude_str
                    ORDER BY recent_views DESC, total_views DESC LIMIT 3";
                    
    $object->query = $trend_query;
    $trend_params = $params; // Clone the base filters again
    $trend_params[':tab_type2'] = $tab;
    $trend_params[':tab_type3'] = $tab;
    $object->execute($trend_params);
    $trending_items = $object->statement_result();

    foreach($trending_items as $t) {
        $exclude_ids[] = $t['id'];
    }

    // 5c. Fetch the Main Grid (Excluding Top and Trending to prevent duplicates)
    $exclude_str_final = !empty($exclude_ids) ? "AND main.id NOT IN (" . implode(',', $exclude_ids) . ")" : "";
    
    $grid_where = $where . " " . $exclude_str_final;

    // Pagination for Grid
    $object->query = "SELECT COUNT(main.id) as total_rows FROM $table main LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id $grid_where";
    $object->execute($params); // Safe: $params only contains search, college, and year
    $count_result = $object->statement->fetch(PDO::FETCH_ASSOC);
    $total_results = $count_result['total_rows'];
    $total_pages = ceil($total_results / $limit);

    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $limit;
    }

    $grid_params = $params; // Clone one last time
    $grid_params[':tab_type4'] = $tab;
    
    $object->query = "SELECT main.*, rd.firstName, rd.familyName, rd.department,
                      (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = :tab_type4) as total_views
                      FROM $table main 
                      LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id 
                      $grid_where 
                      ORDER BY main.id DESC LIMIT $limit OFFSET $offset";
    $object->execute($grid_params);
    $grid_items = $object->statement_result();
    
    $current_page_count = count($grid_items) + ($page == 1 ? count($trending_items) + ($featured_item ? 1 : 0) : 0);
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
                    <h3>Researches</h3>
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
                    <h3>Trainings</h3>
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
                            <option value="research" <?php if($tab == 'research') echo 'selected'; ?>>Researches</option>
                            <option value="publication" <?php if($tab == 'publication') echo 'selected'; ?>>Academic Publications</option>
                            <option value="ip" <?php if($tab == 'ip') echo 'selected'; ?>>Intellectual Properties</option>
                            <option value="pp" <?php if($tab == 'pp') echo 'selected'; ?>>Paper Presentations</option>
                            <option value="trainings" <?php if($tab == 'trainings') echo 'selected'; ?>>Trainings</option>
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
                        
                        <!-- 1. TOP VIEWED LOGIC -->
                        <?php if ($page == 1 && ($featured_item || !empty($trending_items))): ?>
                        <div class="research-blog-top">
                            <?php if($featured_item): $row = $featured_item; ?>
                                <div class="featured-research-post data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $tab; ?>" <?php if($tab == 'publication' && !empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                    
                                    <?php 
                                        $db_cover = trim($row['cover_photo'] ?? '');
                                        $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
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
                                        
                                        <!-- Call the dynamic drawer function -->
                                        <?php echo getDrawerHtml($tab, $row, $object); ?>

                                        <div class="card-footer" style="margin-top: auto;">
                                            <span class="college-tag"><i class="fas fa-university mr-1 text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                            <div>
                                                <span class="date-tag mr-2"><i class="far fa-calendar-alt mr-1"></i> <?php echo !empty($row[$date_column]) ? htmlspecialchars($row[$date_column]) : 'N/A'; ?></span>
                                                <span class="view-tag" style="font-size: 0.85rem; color: #666; font-weight: 600;"><i class="fas fa-eye mr-1"></i> <?php echo $row['total_views'] ?? 0; ?> views</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- 2. TRENDING ITEMS LOGIC -->
                            <?php if(!empty($trending_items)): ?>
                                <div class="trending-research-list">
                                    <h4 class="trending-header">Also Trending <span style="font-size: 0.8rem; font-weight: normal; float:right; margin-top:5px;">(Last 7 Days)</span></h4>
                                    <?php foreach($trending_items as $row): ?>
                                        <div class="trending-card data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $tab; ?>" <?php if($tab == 'publication' && !empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                            
                                            <?php 
                                                $db_cover = trim($row['cover_photo'] ?? '');
                                                $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
                                            ?>
                                            <div class="trending-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;"></div>                                                
                                            <div class="trending-content">
                                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                                <p class="author-line" style="font-size: 0.85rem; margin-bottom: 10px;">
                                                    <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                                </p>
                                                
                                                <?php echo getDrawerHtml($tab, $row, $object); ?>

                                                <div class="card-footer" style="padding-top: 5px; margin-top: 0; align-items: center;">
                                                    <span class="college-tag" style="background: transparent; padding: 0;"><i class="fas fa-university text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                                    <span class="view-tag" style="font-size: 0.8rem; color: #666; font-weight: 600;"><i class="fas fa-eye mr-1"></i> <?php echo $row['total_views'] ?? 0; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 3. NEWEST (MAIN GRID) LOGIC -->
                        <?php if(!empty($grid_items)): ?>
                            <div class="db-results-grid">
                                <?php foreach($grid_items as $row): ?>
                                    <div class="hero-card data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $tab; ?>" <?php if($tab == 'publication' && !empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                        
                                        <?php 
                                            $db_cover = trim($row['cover_photo'] ?? '');
                                            $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
                                        ?>
                                        <div class="hero-card-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;"></div>                                            
                                        <div class="hero-card-content">
                                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                            <p class="author-line">
                                                <strong>Author:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                            </p>
                                            
                                            <?php echo getDrawerHtml($tab, $row, $object); ?>

                                            <div class="card-footer" style="align-items: center;">
                                                <span class="college-tag"><i class="fas fa-university text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                                <div style="display:flex; align-items: center; gap: 10px;">
                                                    <span class="date-tag"><i class="far fa-calendar-alt mr-1"></i> <?php echo !empty($row[$date_column]) ? htmlspecialchars($row[$date_column]) : 'N/A'; ?></span>
                                                    <span class="view-tag" style="font-size: 0.85rem; color: #666; font-weight: 600;"><i class="fas fa-eye mr-1"></i> <?php echo $row['total_views'] ?? 0; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="data-card">
                            <h3>No Results Found</h3>
                            <p>We couldn't find any records matching your search and filters. Please try adjusting them.</p>
                        </div>
                    <?php endif; ?>

                    <?php if($total_pages > 1): ?>
                        <div class="pagination-container">
                            
                            <?php if($page > 1): ?>
                                <a href="<?php echo build_url($tab, $search, $college, $year, $page - 1); ?>" class="page-btn page-btn-outline">&laquo; Prev</a>
                            <?php endif; ?>

                            <?php 
                            $max_links = 10;
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let hoverTimer;
        const cards = document.querySelectorAll('.rde-track-view');

        cards.forEach(card => {
            card.addEventListener('mouseenter', (e) => {
                if (card.dataset.viewed === 'true') return;

                hoverTimer = setTimeout(() => {
                    const itemId = card.dataset.id;
                    const itemType = card.dataset.type;
                    
                    if(itemId && itemType) {
                        logView(itemId, itemType);
                        card.dataset.viewed = 'true'; 
                    }
                }, 3000); 
            });

            card.addEventListener('mouseleave', () => {
                clearTimeout(hoverTimer);
            });
        });

        function logView(id, type) {
            // Strictly use 'actions/...' so it perfectly matches whatever folder rde-database.php is currently in.
            fetch('modules/researchers/actions/update_count.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${id}&item_type=${type}`
            })
            .then(async response => {
                // If the file STILL isn't found, this catches the HTML 404 page before it crashes the JSON parser
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`Server returned ${response.status}: ${text.substring(0, 50)}...`);
                }
                return response.json();
            })
            .then(data => console.log('View status:', data))
            .catch(err => console.error('Error logging view:', err.message));
        }
    });
    </script>
    <style>
.abstract-tooltip {
    position: fixed;
    background: rgba(25, 30, 36, 0.95);
    color: #e2e8f0;
    padding: 18px;
    border-radius: 8px;
    max-width: 400px;
    font-size: 0.95rem;
    pointer-events: none; /* Prevents cursor from interacting with tooltip itself */
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0.2s ease;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    line-height: 1.6;
    /* Centers horizontally and pushes tooltip 20px above cursor */
    transform: translate(-50%, calc(-100% - 20px));
}
.abstract-tooltip.visible {
    opacity: 1;
    visibility: visible;
}
.abstract-tooltip h6 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #4e73df;
    font-weight: 700;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 5px;
}
.abstract-tooltip .tooltip-content {
    display: -webkit-box;
    -webkit-line-clamp: 6; /* Limits to ~6 lines before truncating */
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. SETUP TOOLTIP ---
    const tooltip = document.createElement('div');
    tooltip.className = 'abstract-tooltip fade-in-up';
    
    // Notice the style="position: relative; z-index: 1;" wrapper.
    // This ensures the abstract text always sits ON TOP of the watermark.
    tooltip.innerHTML = `
        <div style="position: relative; z-index: 1;">
            <h6><i class="fas fa-quote-left mr-2"></i>Abstract Sneak Peek</h6>
            <div class="tooltip-content"></div>
        </div>
    `;
    document.body.appendChild(tooltip);
    const tooltipContent = tooltip.querySelector('.tooltip-content');
    
    // --- 2. SETUP WATERMARK INSIDE TOOLTIP ---
    const watermarkContainer = document.createElement('div');
    watermarkContainer.className = 'secure-watermark-overlay';
    const date = new Date().toLocaleDateString();
    const watermarkText = `WMSU SDMU Property - ${date}`;
    
    // Fill the tooltip with text (Only 15 needed for a small box, not 50)
    for (let i = 0; i < 15; i++) {
        const span = document.createElement('span');
        span.className = 'secure-watermark-text';
        span.innerText = watermarkText;
        watermarkContainer.appendChild(span);
    }
    
    // APPEND TO TOOLTIP (Not the body!)
    tooltip.appendChild(watermarkContainer);

    // --- 3. ATTACH HOVER EVENTS ---
    const abstractCards = document.querySelectorAll('.data-card[data-abstract]');
    
    abstractCards.forEach(card => {
        card.addEventListener('mouseenter', (e) => {
            const abstractText = card.getAttribute('data-abstract');
            if (abstractText && abstractText.trim() !== '') {
                // Show Tooltip (Watermark comes with it automatically!)
                tooltipContent.textContent = abstractText;
                tooltip.classList.add('visible');
            }
        });
        
        card.addEventListener('mousemove', (e) => {
            // Track cursor exactly
            tooltip.style.left = e.clientX + 'px';
            tooltip.style.top = e.clientY + 'px';
        });
        
        card.addEventListener('mouseleave', () => {
            // Hide tooltip
            tooltip.classList.remove('visible');
        });
    });
});
</script>
</body>
</html>