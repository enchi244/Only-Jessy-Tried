<?php
include('core/rms.php');
$object = new rms();

// --- NEW: FETCH DYNAMIC CMS LOGOS ---
$site_logos = [];
try {
    $object->query = "SELECT setting_key, setting_value FROM tbl_site_settings";
    $object->execute();
    foreach($object->statement_result() as $row) {
        $site_logos[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Silently ignore if table doesn't exist yet
}

// 1. Capture URL Parameters for Filters, Tabs, and Pagination
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'hub'; 
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$college_filter = isset($_GET['college']) && trim($_GET['college']) !== '' ? trim($_GET['college']) : 'all';
$year_filter = isset($_GET['year']) && trim($_GET['year']) !== '' ? trim($_GET['year']) : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$is_filtering = ($search !== '' || $college_filter !== 'all' || $year_filter !== 'all' || ($tab !== 'hub' && $tab !== 'all' && strpos($tab, ',') !== false));

$module_map = [
    'research' => ['table' => 'tbl_researchconducted', 'date_col' => 'completed_date', 'title' => 'Researches'],
    'publication' => ['table' => 'tbl_publication', 'date_col' => 'publication_date', 'title' => 'Academic Publications'],
    'ip' => ['table' => 'tbl_itelectualprop', 'date_col' => 'date_applied', 'title' => 'Intellectual Properties'],
    'pp' => ['table' => 'tbl_paperpresentation', 'date_col' => 'date_paper', 'title' => 'Paper Presentations'],
    'trainings' => ['table' => 'tbl_trainingsattended', 'date_col' => 'date_train', 'title' => 'Trainings'],
    'epc' => ['table' => 'tbl_extension_project_conducted', 'date_col' => 'start_date', 'title' => 'Extension Projects'],
    'ext' => ['table' => 'tbl_ext', 'date_col' => 'period_implement', 'title' => 'Extension Activities']
];

// Determine Active Categories
$selected_categories = ($tab == 'all' || $tab == 'hub') ? array_keys($module_map) : explode(',', $tab);
$valid_modules = [];
$header_titles = [];
foreach($selected_categories as $m) {
    $m = trim($m);
    if(isset($module_map[$m])) {
        $valid_modules[] = $m;
        $header_titles[] = $module_map[$m]['title'];
    }
}
if(empty($valid_modules)) {
    $valid_modules = array_keys($module_map);
    $header_titles[] = "All Research & Evaluation Outputs";
}

// --- DYNAMIC HEADER CONTENT ---
if (count($valid_modules) == 1 && $tab !== 'hub' && $tab !== 'all' && $search === '' && $college_filter === 'all' && $year_filter === 'all') {
    // Single category with no other filters
    $header_title = $header_titles[0];
    $header_desc = "Explore our database records for " . $header_titles[0] . ".";
} elseif ($is_filtering && $tab !== 'hub') {
    $header_title = "Filtered RDE Outputs";
    
    $desc_parts = [];
    if ($search !== '') $desc_parts[] = "Keyword: '" . htmlspecialchars($search) . "'";
    
    // Explicitly grab specific Categories, Colleges, and Years
    $cat_text = (count($valid_modules) == count($module_map)) ? "All Categories" : htmlspecialchars(implode(', ', $header_titles));
    $col_text = ($college_filter === 'all') ? "All Colleges" : htmlspecialchars(str_replace(',', ', ', $college_filter));
    $yr_text = ($year_filter === 'all') ? "All Years" : htmlspecialchars(str_replace(',', ', ', $year_filter));
    
    $desc_parts[] = "Categories: " . $cat_text;
    $desc_parts[] = "College: " . $col_text;
    $desc_parts[] = "Year: " . $yr_text;
    
    $header_desc = implode(" | ", $desc_parts);
} else {
    $header_title = 'Research & Evaluation Outputs';
    $header_desc = 'Explore thousands of academic papers, intellectual properties, policies, and publications.';
}

$DEFAULT_COVER = 'img/default_research_cover.png';
$limit = 12;
$offset = ($page - 1) * $limit;

function build_url($new_tab, $search, $college, $year, $page_num = 1) {
    return "?tab=$new_tab&search=" . urlencode($search) . "&college=" . urlencode($college) . "&year=" . urlencode($year) . "&page=$page_num";
}

function getDrawerHtml($tab, $row) {
    $html = '<div class="card-details-drawer"><div class="drawer-grid">';
    if ($tab == 'research') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($row['co_authors'] ?? 'None').'</div>';
        $html .= '<div><i class="fas fa-bullseye text-success"></i> <strong>SDGs:</strong> '.htmlspecialchars($row['sdgs'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-layer-group text-info"></i> <strong>Cluster:</strong> '.htmlspecialchars($row['research_agenda_cluster'] ?? 'N/A').'</div>';
    } elseif ($tab == 'publication') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($row['co_authors'] ?? 'None').'</div>';
        $html .= '<div class="full-width"><i class="fas fa-book-open text-danger"></i> <strong>Journal:</strong> '.htmlspecialchars($row['journal'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-barcode text-secondary"></i> <strong>ISSN/ISBN:</strong> '.htmlspecialchars($row['issn_isbn'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-hashtag text-info"></i> <strong>Vol/Issue:</strong> '.htmlspecialchars($row['vol_num_issue_num'] ?? 'N/A').'</div>';
    } elseif ($tab == 'ip') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Co-Authors:</strong> '.htmlspecialchars($row['coauth'] ?? 'None').'</div>';
        $html .= '<div><i class="far fa-calendar-check text-success"></i> <strong>Granted:</strong> '.htmlspecialchars($row['date_granted'] ?? 'Pending').'</div>';
    } elseif ($tab == 'pp') {
        $html .= '<div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Organizer:</strong> '.htmlspecialchars($row['conference_organizer'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> '.htmlspecialchars($row['conference_venue'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-globe text-info"></i> <strong>Level:</strong> '.htmlspecialchars($row['conference_title'] ?? 'N/A').'</div>';
    } elseif ($tab == 'trainings') {
        $html .= '<div class="full-width"><i class="fas fa-building text-primary"></i> <strong>Sponsor:</strong> '.htmlspecialchars($row['sponsor_org'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-map-marker-alt text-danger"></i> <strong>Venue:</strong> '.htmlspecialchars($row['venue'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-clock text-warning"></i> <strong>Hours:</strong> '.htmlspecialchars($row['totnh'] ?? '0').'</div>';
    } elseif ($tab == 'epc') {
        $html .= '<div class="full-width"><i class="fas fa-users text-primary"></i> <strong>Beneficiaries:</strong> '.htmlspecialchars($row['target_beneficiaries_communities'] ?? 'N/A').'</div>';
        $html .= '<div class="full-width"><i class="fas fa-handshake text-info"></i> <strong>Partners:</strong> '.htmlspecialchars($row['partners'] ?? 'None').'</div>';
    } elseif ($tab == 'ext') {
        $html .= '<div class="full-width"><i class="fas fa-info-circle text-primary"></i> <strong>Description:</strong> '.htmlspecialchars($row['description'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-user-tie text-info"></i> <strong>Lead:</strong> '.htmlspecialchars($row['proj_lead'] ?? 'N/A').'</div>';
        $html .= '<div><i class="fas fa-users text-warning"></i> <strong>Assist:</strong> '.htmlspecialchars($row['assist_coordinators'] ?? 'None').'</div>';
    }
    $html .= '</div></div>';
    return $html;
}

// 2. Fetch Colleges for Dropdown
$object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
$object->execute();
$colleges_list = $object->statement_result();

// 3. Build Unified Background SQL Queries
$featured_item = null;
$trending_items = [];
$grid_items = [];
$current_page_count = 0;
$total_pages = 0;
$total_results = 0;

if ($tab !== 'hub') {
    
    $params = [];
    $search_condition = "";
    if ($search != '') {
        $search_condition = " AND (main.title LIKE :search OR rd.familyName LIKE :search OR rd.firstName LIKE :search) ";
        $params[':search'] = "%$search%";
    }

    $col_condition = "";
    if ($college_filter != 'all') {
        $selected_col_array = explode(',', $college_filter);
        $in_placeholders = [];
        foreach ($selected_col_array as $index => $col) {
            $param_name = ":col_$index";
            $in_placeholders[] = $param_name;
            $params[$param_name] = trim($col);
        }
        $col_condition = " AND rd.department IN (" . implode(',', $in_placeholders) . ") ";
    }

    $union_queries = [];
    $count_queries = [];

    foreach($valid_modules as $mod) {
        $table = $module_map[$mod]['table'];
        $date_col = $module_map[$mod]['date_col'];

        $yr_condition = "";
        if ($year_filter != 'all' && $date_col != "") {
            $selected_yr_array = explode(',', $year_filter);
            $yr_conditions = [];
            foreach ($selected_yr_array as $index => $yr) {
                $param_name = ":yr_{$mod}_$index";
                $yr_conditions[] = "main.$date_col LIKE $param_name";
                $params[$param_name] = "%" . trim($yr) . "%";
            }
            $yr_condition = " AND (" . implode(' OR ', $yr_conditions) . ") ";
        }

        $base_where = "WHERE main.status = 1 AND rd.status = 1 " . $search_condition . $col_condition . $yr_condition;

        $union_queries[] = "SELECT '$mod' AS module_type, main.id, main.title, rd.firstName, rd.familyName, rd.department, main.cover_photo, main.$date_col AS item_date,
                            (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = '$mod') AS total_views,
                            (SELECT COUNT(*) FROM tbl_rde_views v WHERE v.item_id = main.id AND v.item_type = '$mod' AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS recent_views
                            FROM $table main LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id $base_where";

        $count_queries[] = "SELECT COUNT(main.id) as cnt FROM $table main LEFT JOIN tbl_researchdata rd ON main.researcherID = rd.id $base_where";
    }

    $full_union_sql = implode(" UNION ALL ", $union_queries);
    $full_count_sql = "SELECT SUM(cnt) as total_rows FROM (" . implode(" UNION ALL ", $count_queries) . ") as combined_counts";

    $is_actively_searching = ($search !== '' || $college_filter !== 'all' || $year_filter !== 'all' || count($valid_modules) > 1);
    
    $exclude_ids_by_mod = [];
    $exclude_where_clauses = [];

    if (!$is_actively_searching && $page == 1) {
        $object->query = $full_union_sql . " ORDER BY total_views DESC LIMIT 1";
        $object->execute($params);
        if ($object->row_count() > 0) {
            $featured_item = $object->statement->fetch(PDO::FETCH_ASSOC);
            $exclude_ids_by_mod[$featured_item['module_type']][] = $featured_item['id'];
            $exclude_where_clauses[] = "NOT (module_type = '".$featured_item['module_type']."' AND id = ".$featured_item['id'].")";
        }

        $exclude_sql = !empty($exclude_where_clauses) ? " WHERE " . implode(" AND ", $exclude_where_clauses) : "";
        $object->query = "SELECT * FROM ($full_union_sql) AS t $exclude_sql ORDER BY recent_views DESC, total_views DESC LIMIT 3";
        $object->execute($params);
        $trending_items = $object->statement_result();
        foreach($trending_items as $t) {
            $exclude_where_clauses[] = "NOT (module_type = '".$t['module_type']."' AND id = ".$t['id'].")";
        }
    }

    $object->query = $full_count_sql;
    $object->execute($params);
    $total_results = $object->statement->fetch(PDO::FETCH_ASSOC)['total_rows'];
    $total_pages = ceil($total_results / $limit);

    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $limit;
    }

    $exclude_sql = !empty($exclude_where_clauses) ? " WHERE " . implode(" AND ", $exclude_where_clauses) : "";
    $object->query = "SELECT * FROM ($full_union_sql) AS t $exclude_sql ORDER BY item_date DESC, id DESC LIMIT $limit OFFSET $offset";
    $object->execute($params);
    $raw_grid_items = $object->statement_result();

    $hydrate_rows = function($items) use ($object, $module_map) {
        $hydrated = [];
        foreach($items as $row) {
            $mod = $row['module_type'];
            $table = $module_map[$mod]['table'];
            $object->query = "SELECT * FROM $table WHERE id = '".$row['id']."'";
            $object->execute();
            $full_data = $object->statement->fetch(PDO::FETCH_ASSOC);
            
            // Fallback if data fails to fetch
            if (!$full_data) $full_data = []; 
            
            $co_authors = '';
            if (in_array($mod, ['research', 'publication'])) {
                $col_table = ($mod == 'research') ? 'tbl_research_collaborators' : 'tbl_publication_collaborators';
                $col_fk = ($mod == 'research') ? 'research_id' : 'publication_id';
                $object->query = "SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') as co_authors FROM $col_table col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.$col_fk = '".$row['id']."' AND col.researcher_id != '".($full_data['researcherID'] ?? '')."'";
                $object->execute();
                $co_authors = $object->statement->fetch(PDO::FETCH_ASSOC)['co_authors'] ?? 'None';
            }
            $full_data['co_authors'] = $co_authors;
            
            $hydrated[] = array_merge($full_data, $row); 
        }
        return $hydrated;
    };

    if ($featured_item) {
        $hydrated_featured = $hydrate_rows([$featured_item]);
        $featured_item = $hydrated_featured[0];
    }
    $trending_items = $hydrate_rows($trending_items);
    $grid_items = $hydrate_rows($raw_grid_items);

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
    
    <style>
        /* BULLETPROOF UI CSS */
        .dropdown-menu-custom { box-shadow: 0 10px 25px rgba(0,0,0,0.15); padding: 0 !important; }
        
        .custom-dropdown-btn { 
            background: #fff; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            cursor: pointer; 
            user-select: none; 
            height: 45px; 
            border-radius: 8px; 
            padding: 0 15px; 
            border: 1px solid #d1d3e2;
            transition: all 0.2s;
        }
        .custom-dropdown-btn:hover { background-color: #f8f9fc; border-color: #4e73df; }
        
        .checkbox-row { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            border-bottom: 1px solid #f1f5f9; 
            transition: background 0.2s; 
            cursor: pointer;
        }
        .checkbox-row:hover { background-color: #f8f9fc; }
        .checkbox-row input[type="checkbox"] { 
            width: 18px; 
            height: 18px; 
            margin-right: 12px; 
            cursor: pointer; 
            accent-color: #4e73df; 
        }
        .checkbox-row span { font-size: 0.95rem; }
        
        .active-filter-banner { background: #fdfdfd; border: 1px solid #e3e6f0; border-left: 4px solid #4e73df; border-radius: 8px; }

        /* RESTORED: Abstract Tooltip & Watermark CSS */
        .abstract-tooltip {
            position: fixed;
            background: rgba(25, 30, 36, 0.95);
            color: #e2e8f0;
            padding: 18px;
            border-radius: 8px;
            max-width: 400px;
            font-size: 0.95rem;
            pointer-events: none; 
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            line-height: 1.6;
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
            -webkit-line-clamp: 6; 
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="database-body">

    <nav id="navbar" class="scrolled database-nav">
        <div class="nav-container">
<!-- UPDATED DYNAMIC LOGOS (CIRCULAR) -->
<div class="logo">
    <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
        <?php if(!empty($site_logos['logo_wmsu'])): ?>
            <img src="<?php echo htmlspecialchars($site_logos['logo_wmsu']); ?>" alt="WMSU Logo" style="height: 45px; width: 45px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php endif; ?>
        
        <?php if(!empty($site_logos['logo_rdec'])): ?>
            <img src="<?php echo htmlspecialchars($site_logos['logo_rdec']); ?>" alt="RDEC Logo" style="height: 45px; width: 45px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php endif; ?>
        
        <?php if(!empty($site_logos['logo_third'])): ?>
            <img src="<?php echo htmlspecialchars($site_logos['logo_third']); ?>" alt="3rd Logo" style="height: 45px; width: 45px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php endif; ?>
        
        <span class="logo-text" style="margin-left: 5px;">SDMU <span class="highlight">WMSU</span></span>
    </a>
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
                    <input type="hidden" name="tab" value="all">
                    <div class="search-bar-container" style="position: relative; z-index: 1000;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search by title, author, or keyword...">
                        <button type="submit" class="btn btn-primary">Search</button>
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
                <!-- DYNAMIC HEADER TITLES -->
                <h1><?php echo htmlspecialchars($header_title); ?></h1>
                <p><?php echo htmlspecialchars($header_desc); ?></p>
            </div>
        </header>

        <main class="db-container container fade-in-up delay-1">
            
            <form action="rde-database.php" method="GET" class="top-filter-bar" id="mainFilterForm" style="position: relative; z-index: 9999;">
                
                <input type="hidden" name="tab" id="hiddenCategories" value="<?php echo htmlspecialchars($tab); ?>">
                <input type="hidden" name="college" id="hiddenCollege" value="<?php echo htmlspecialchars($college_filter); ?>">
                <input type="hidden" name="year" id="hiddenYear" value="<?php echo htmlspecialchars($year_filter); ?>">

                <div class="filter-inputs-group" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; overflow: visible;">
                    
                    <div class="search-bar-container" style="flex: 1; min-width: 200px; margin: 0; background: #fff; border: 1px solid #d1d3e2; border-radius: 8px;">
                        <i class="fas fa-search search-icon" style="left: 15px;"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search title or author..." value="<?php echo htmlspecialchars($search); ?>" style="border: none; outline: none; background: transparent; padding: 10px 15px 10px 40px; width: 100%; height: 100%;">
                    </div>

                    <div class="vanilla-dropdown-container" id="dbCategoryDropdown" data-name="Categories" style="position: relative; flex: 1; min-width: 200px;">
                        <div class="custom-dropdown-btn border-primary">
                            <span class="btn-text font-weight-bold text-primary">All Categories</span>
                            <i class="fas fa-chevron-down text-primary"></i>
                        </div>
                        <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:110%; left:0; width: 300px; background:white; max-height:350px; overflow-y:auto; border-radius:8px; border: 1px solid #edf2f9;">
                            <label class="checkbox-row bg-light font-weight-bold text-primary">
                                <input type="checkbox" class="check-all" <?php if($tab == 'all') echo 'checked'; ?>>
                                <span>Select All Categories</span>
                            </label>
                            <?php foreach($module_map as $mod_key => $mod_data): 
                                $is_checked = ($tab == 'all' || in_array($mod_key, $selected_categories)) ? 'checked' : '';
                            ?>
                                <label class="checkbox-row">
                                    <input type="checkbox" class="check-item" value="<?php echo $mod_key; ?>" <?php echo $is_checked; ?>>
                                    <span class="font-weight-bold text-dark"><?php echo $mod_data['title']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="vanilla-dropdown-container" id="dbCollegeDropdown" data-name="Colleges" style="position: relative; flex: 1; min-width: 200px;">
                        <div class="custom-dropdown-btn">
                            <span class="btn-text text-dark">All Colleges</span>
                            <i class="fas fa-chevron-down text-muted"></i>
                        </div>
                        <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:110%; left:0; width: 320px; background:white; max-height:350px; overflow-y:auto; border-radius:8px; border: 1px solid #edf2f9;">
                            <?php $selected_colleges = ($college_filter == 'all') ? [] : explode(',', $college_filter); ?>
                            <label class="checkbox-row bg-light font-weight-bold text-primary">
                                <input type="checkbox" class="check-all" <?php if(empty($selected_colleges)) echo 'checked'; ?>>
                                <span>Select All Colleges</span>
                            </label>
                            <?php foreach($colleges_list as $index => $col): 
                                $col_name = htmlspecialchars($col['category_name']);
                                $is_checked = (empty($selected_colleges) || in_array($col['category_name'], $selected_colleges)) ? 'checked' : '';
                            ?>
                                <label class="checkbox-row">
                                    <input type="checkbox" class="check-item" value="<?php echo $col_name; ?>" <?php echo $is_checked; ?>>
                                    <span class="text-dark"><?php echo $col_name; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="vanilla-dropdown-container" id="dbYearDropdown" data-name="Years" style="position: relative; flex: 1; min-width: 150px;">
                        <div class="custom-dropdown-btn border-success">
                            <span class="btn-text text-dark">All Years</span>
                            <i class="fas fa-chevron-down text-success"></i>
                        </div>
                        <div class="dropdown-menu-custom shadow-lg" style="display:none; position:absolute; top:110%; right:0; width: 200px; background:white; max-height:350px; overflow-y:auto; border-radius:8px; border: 1px solid #edf2f9;">
                            <?php $selected_years = ($year_filter == 'all') ? [] : explode(',', $year_filter); ?>
                            <label class="checkbox-row bg-light font-weight-bold text-success">
                                <input type="checkbox" class="check-all" <?php if(empty($selected_years)) echo 'checked'; ?>>
                                <span>Select All Years</span>
                            </label>
                            <?php 
                            $currentYear = date("Y");
                            for($i = $currentYear; $i >= 2010; $i--): 
                                $is_checked = (empty($selected_years) || in_array($i, $selected_years)) ? 'checked' : '';
                            ?>
                                <label class="checkbox-row">
                                    <input type="checkbox" class="check-item" value="<?php echo $i; ?>" <?php echo $is_checked; ?>>
                                    <span class="text-dark font-weight-bold"><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="btn btn-primary" id="applyFiltersBtn" style="height: 45px; padding: 0 20px;">Apply Filter</button>
                        <a href="?tab=hub" class="btn btn-secondary" title="Back to Hub" style="height: 45px; display: flex; align-items: center; justify-content: center; padding: 0 15px;"><i class="fas fa-th-large"></i></a>
                    </div>
                </div>
            </form>

            <section class="db-content">
                <div class="db-results" id="resultsContainer">
                    
                    <div class="results-meta">
                        <span>Showing Page <strong><?php echo $page; ?></strong> of <strong><?php echo max(1, $total_pages); ?></strong> (Total: <?php echo $total_results; ?> records)</span>
                    </div>

                    <?php if($current_page_count > 0): ?>
                        
                        <?php if (!$is_actively_searching && $page == 1): ?>
                            <div class="research-blog-top">
                                <?php if($featured_item): $row = $featured_item; $c_tab = $row['module_type']; ?>
                                    <div class="featured-research-post data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $c_tab; ?>" <?php if(!empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                        
                                        <?php 
                                            $db_cover = trim($row['cover_photo'] ?? '');
                                            $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
                                        ?>
                                        <div class="post-image-large" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;">
                                            <div class="card-badge"><i class="fas fa-fire"></i> Top Viewed <?php echo htmlspecialchars($module_map[$c_tab]['title']); ?></div>
                                        </div>
                                        
                                        <div class="post-content">
                                            <h2 style="font-size: 2rem; font-family: 'Playfair Display', serif; color: var(--dark-bg); margin-bottom: 15px; line-height: 1.2;">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </h2>
                                            
                                            <p class="author-line" style="font-size: 1.1rem; margin-bottom: 15px;">
                                                <i class="fas fa-user-circle text-primary"></i> <strong>Author/Lead:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                            </p>
                                            
                                            <?php echo getDrawerHtml($c_tab, $row, $object); ?>

                                            <div class="card-footer" style="margin-top: auto;">
                                                <span class="college-tag"><i class="fas fa-university mr-1 text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                                <div>
                                                    <span class="date-tag mr-2"><i class="far fa-calendar-alt mr-1"></i> <?php echo !empty($row['item_date']) ? htmlspecialchars($row['item_date']) : 'N/A'; ?></span>
                                                    <span class="view-tag" style="font-size: 0.85rem; color: #666; font-weight: 600;"><i class="fas fa-eye mr-1"></i> <?php echo $row['total_views'] ?? 0; ?> views</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($trending_items)): ?>
                                    <div class="trending-research-list">
                                        <h4 class="trending-header">Also Trending <span style="font-size: 0.8rem; font-weight: normal; float:right; margin-top:5px;">(Last 7 Days)</span></h4>
                                        <?php foreach($trending_items as $row): $c_tab = $row['module_type']; ?>
                                            <div class="trending-card data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $c_tab; ?>" <?php if(!empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                                
                                                <?php 
                                                    $db_cover = trim($row['cover_photo'] ?? '');
                                                    $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
                                                ?>
                                                <div class="trending-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;"></div>                                                
                                                <div class="trending-content">
                                                    <span class="badge badge-light text-primary mb-1 border" style="font-size: 0.65rem;"><?php echo htmlspecialchars($module_map[$c_tab]['title']); ?></span>
                                                    <h3 style="margin-top: 0; padding-top: 0;"><?php echo htmlspecialchars($row['title']); ?></h3>
                                                    <p class="author-line" style="font-size: 0.85rem; margin-bottom: 10px;">
                                                        <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                                    </p>
                                                    
                                                    <?php echo getDrawerHtml($c_tab, $row, $object); ?>

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

                        <!-- 3. NEWEST (MAIN GRID) -->
                        <?php if(!empty($grid_items)): ?>
                            <div class="db-results-grid">
                                <?php foreach($grid_items as $row): $c_tab = $row['module_type']; ?>
                                    <div class="hero-card data-card rde-track-view" data-id="<?php echo $row['id']; ?>" data-type="<?php echo $c_tab; ?>" <?php if(!empty($row['abstract'])) echo 'data-abstract="'.htmlspecialchars($row['abstract']).'"'; ?>>
                                        
                                        <?php 
                                            $db_cover = trim($row['cover_photo'] ?? '');
                                            $cover_img = !empty($db_cover) ? htmlspecialchars($db_cover) : $DEFAULT_COVER; 
                                        ?>
                                        <div class="hero-card-image" style="background-image: url('<?php echo $cover_img; ?>'); background-size: cover; background-position: center;">
                                            <div class="card-badge" style="background: rgba(255,255,255,0.9); color: #4e73df;"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($module_map[$c_tab]['title']); ?></div>
                                        </div>                                            
                                        <div class="hero-card-content">
                                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                            <p class="author-line">
                                                <strong>Author:</strong> <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['familyName']); ?>
                                            </p>
                                            
                                            <?php echo getDrawerHtml($c_tab, $row, $object); ?>

                                            <div class="card-footer" style="align-items: center;">
                                                <span class="college-tag"><i class="fas fa-university text-primary"></i> <?php echo htmlspecialchars($row['department'] ?? 'Department Not Specified'); ?></span>
                                                <div style="display:flex; align-items: center; gap: 10px;">
                                                    <span class="date-tag"><i class="far fa-calendar-alt mr-1"></i> <?php echo !empty($row['item_date']) ? htmlspecialchars($row['item_date']) : 'N/A'; ?></span>
                                                    <span class="view-tag" style="font-size: 0.85rem; color: #666; font-weight: 600;"><i class="fas fa-eye mr-1"></i> <?php echo $row['total_views'] ?? 0; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="data-card text-center" style="padding: 40px;">
                            <h3>No Results Found</h3>
                            <p class="text-muted">We couldn't find any records matching your search and filters. Please try adjusting them.</p>
                            <a href="?tab=all" class="btn btn-outline-primary mt-3">Clear All Filters</a>
                        </div>
                    <?php endif; ?>

                    <?php if($total_pages > 1): ?>
                        <div class="pagination-container mt-4">
                            <?php if($page > 1): ?>
                                <a href="<?php echo build_url($tab, $search, $college_filter, $year_filter, $page - 1); ?>" class="page-btn page-btn-outline">&laquo; Prev</a>
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
                                <a href="<?php echo build_url($tab, $search, $college_filter, $year_filter, 1); ?>" class="page-btn page-btn-outline">1</a>
                                <?php if($start_page > 2): ?>
                                    <span class="page-btn-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="<?php echo build_url($tab, $search, $college_filter, $year_filter, $i); ?>" class="page-btn <?php echo ($i == $page) ? 'page-btn-active' : 'page-btn-outline'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if($end_page < $total_pages): ?>
                                <?php if($end_page < $total_pages - 1): ?>
                                    <span class="page-btn-dots">...</span>
                                <?php endif; ?>
                                <a href="<?php echo build_url($tab, $search, $college_filter, $year_filter, $total_pages); ?>" class="page-btn page-btn-outline"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if($page < $total_pages): ?>
                                <a href="<?php echo build_url($tab, $search, $college_filter, $year_filter, $page + 1); ?>" class="page-btn page-btn-outline">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </section>
        </main>

    <?php endif; ?>

    <script src="js/public_app.js"></script>

    <!-- VANILLA JS DROPDOWN LOGIC -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        const dropdownBtns = document.querySelectorAll('.custom-dropdown-btn');
        const allMenus = document.querySelectorAll('.dropdown-menu-custom');

        dropdownBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const menu = this.nextElementSibling;
                const isCurrentlyOpen = (menu.style.display === 'block');

                allMenus.forEach(m => { m.style.display = 'none'; });

                if (!isCurrentlyOpen) {
                    menu.style.display = 'block';
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.vanilla-dropdown-container')) {
                allMenus.forEach(m => { m.style.display = 'none'; });
            }
        });

        allMenus.forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        function updateDropdownText(container) {
            const btnText = container.querySelector('.btn-text');
            const checkAll = container.querySelector('.check-all');
            const items = container.querySelectorAll('.check-item');
            const typeName = container.getAttribute('data-name');
            
            let checkedCount = 0;
            items.forEach(item => { if(item.checked) checkedCount++; });
            const totalCount = items.length;

            if (checkedCount === totalCount) {
                if(checkAll) checkAll.checked = true;
                if(container.id === 'dbCategoryDropdown') {
                    btnText.innerText = "All Categories";
                    btnText.style.color = '#4e73df';
                } else {
                    btnText.innerText = "All " + typeName;
                    btnText.style.color = '#333';
                }
            } else if (checkedCount === 0) {
                if(checkAll) checkAll.checked = false;
                btnText.innerText = 'None Selected';
                btnText.style.color = '#e74a3b';
            } else {
                if(checkAll) checkAll.checked = false;
                btnText.innerText = checkedCount + ' ' + typeName + ' Selected';
                btnText.style.color = '#4e73df';
            }
        }

        const containers = document.querySelectorAll('.vanilla-dropdown-container');
        containers.forEach(container => {
            const checkAll = container.querySelector('.check-all');
            const checkItems = container.querySelectorAll('.check-item');

            if(checkAll) {
                checkAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    checkItems.forEach(item => { item.checked = isChecked; });
                    updateDropdownText(container);
                });
            }

            checkItems.forEach(item => {
                item.addEventListener('change', function() {
                    updateDropdownText(container);
                });
            });

            updateDropdownText(container);
        });

        function getDropdownValues(containerId) {
            const container = document.getElementById(containerId);
            if(!container) return '';
            
            const checkItems = container.querySelectorAll('.check-item');
            let selected = [];
            checkItems.forEach(item => {
                if(item.checked) selected.push(item.value);
            });
            
            if (selected.length === 0) return '';
            if (selected.length === checkItems.length) return 'all';
            return selected.join(',');
        }

        const applyBtn = document.getElementById('applyFiltersBtn');
        if(applyBtn) {
            applyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const selectedCategories = getDropdownValues('dbCategoryDropdown');
                const selectedColleges = getDropdownValues('dbCollegeDropdown');
                const selectedYears = getDropdownValues('dbYearDropdown');

                if(selectedCategories === '') {
                    alert('Please select at least one Category to search.');
                    return;
                }
                if(selectedColleges === '' || selectedYears === '') {
                    alert('Please select at least one College and Year to search.');
                    return;
                }

                document.getElementById('hiddenCategories').value = selectedCategories;
                document.getElementById('hiddenCollege').value = selectedColleges;
                document.getElementById('hiddenYear').value = selectedYears;
                
                document.getElementById('mainFilterForm').submit();
            });
        }
    });
    </script>

    <!-- Tracking and Tooltips -->
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
            fetch('modules/researchers/actions/update_count.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${id}&item_type=${type}`
            }).catch(err => console.error('Error logging view:', err.message));
        }
        
        // Tooltip logic
        const tooltip = document.createElement('div');
        tooltip.className = 'abstract-tooltip fade-in-up';
        tooltip.innerHTML = `
            <div style="position: relative; z-index: 1;">
                <h6><i class="fas fa-quote-left mr-2"></i>Abstract Sneak Peek</h6>
                <div class="tooltip-content"></div>
            </div>
        `;
        document.body.appendChild(tooltip);
        const tooltipContent = tooltip.querySelector('.tooltip-content');
        
        const watermarkContainer = document.createElement('div');
        watermarkContainer.className = 'secure-watermark-overlay';
        const date = new Date().toLocaleDateString();
        const watermarkText = `WMSU SDMU Property - ${date}`;
        
        for (let i = 0; i < 15; i++) {
            const span = document.createElement('span');
            span.className = 'secure-watermark-text';
            span.innerText = watermarkText;
            watermarkContainer.appendChild(span);
        }
        tooltip.appendChild(watermarkContainer);

        const abstractCards = document.querySelectorAll('.data-card[data-abstract]');
        abstractCards.forEach(card => {
            card.addEventListener('mouseenter', (e) => {
                const abstractText = card.getAttribute('data-abstract');
                if (abstractText && abstractText.trim() !== '') {
                    tooltipContent.textContent = abstractText;
                    tooltip.classList.add('visible');
                }
            });
            card.addEventListener('mousemove', (e) => {
                tooltip.style.left = e.clientX + 'px';
                tooltip.style.top = e.clientY + 'px';
            });
            card.addEventListener('mouseleave', () => { tooltip.classList.remove('visible'); });
        });
    });
    </script>
</body>
</html>