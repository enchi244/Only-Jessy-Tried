<?php
// Start the session and include your core DB connection
include('core/rms.php');

// --- 1. RDE DATA COUNTS ---
$researchConductedCount = 0;
$rc_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_researchconducted");
if($rc_query && $row = mysqli_fetch_assoc($rc_query)) { $researchConductedCount = $row['total']; }

$ipCount = 0;
$ip_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_itelectualprop"); 
if($ip_query && $row = mysqli_fetch_assoc($ip_query)) { $ipCount = $row['total']; }

$ppCount = 0;
$pp_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_paperpresentation"); 
if($pp_query && $row = mysqli_fetch_assoc($pp_query)) { $ppCount = $row['total']; }

$epCount = 0;
$ep_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_extension_project_conducted"); 
if($ep_query && $row = mysqli_fetch_assoc($ep_query)) { $epCount = $row['total']; }

$publicationtotal = 0;
$pub_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_publication");
if($pub_query && $row = mysqli_fetch_assoc($pub_query)) { $publicationtotal = $row['total']; }

$researchertotal = 0;
$res_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_researchdata WHERE status = 1");
if($res_query && $row = mysqli_fetch_assoc($res_query)) { $researchertotal = $row['total']; }

// --- 2. CMS FETCH QUERIES ---

// Fetch About, Mission, Vision, and Core Responsibilities
$about_query = mysqli_query($conn, "SELECT * FROM tbl_cms_about LIMIT 1");
$cms_about = mysqli_fetch_assoc($about_query);

// Fetch Carousel Images
$carousel_images = [];
$car_query = mysqli_query($conn, "SELECT * FROM tbl_cms_carousel");
if($car_query){
    while($row = mysqli_fetch_assoc($car_query)){
        $carousel_images[] = $row;
    }
}

// Fetch Top 3 News Items
$news_items = [];
$news_query = mysqli_query($conn, "SELECT * FROM tbl_cms_news ORDER BY date_published DESC");
if($news_query){
    while($row = mysqli_fetch_assoc($news_query)){
        $news_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDMU | Western Mindanao State University</title>
    <link rel="stylesheet" href="css/public_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-text">SDMU <span class="highlight">WMSU</span></span>
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                
                <li class="nav-item-dropdown">
                    <a href="rde-database.php" class="btn-nav">
                        RDE Outputs 
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 5px; margin-bottom: -2px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="rde-database.php?tab=research">Research Conducted</a></li>
                        <li><a href="rde-database.php?tab=publication">Academic Publications</a></li>
                        <li><a href="rde-database.php?tab=ip">Intellectual Properties</a></li>
                        <li><a href="rde-database.php?tab=pp">Paper Presentations</a></li>
                        <li><a href="rde-database.php?tab=trainings">Trainings Attended</a></li>
                        <li><a href="rde-database.php?tab=epc">Extension Projects</a></li>
                        <li><a href="rde-database.php?tab=ext">Extension Activities</a></li>
                    </ul>
                </li>
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
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <header id="home" class="hero section">
        <div class="hero-content fade-in-up">
            <h1>Statistics and Data Management Unit</h1>
            <p>Empowering Western Mindanao State University through statistical expertise, data integrity, and actionable research insights.</p>
            <div class="hero-buttons">
                <a href="#about" class="btn btn-primary">Discover SDMU</a>
                <a href="rde-database.php" class="btn btn-secondary">Explore Researches</a>
            </div>
        </div>
        <div class="hero-image-container fade-in-up delay-1">
            <img src="img/research_center.jpg" alt="SDMU Hero Image" class="interactive-img">
        </div>
    </header>

    <section id="about" class="about section">
        <div class="container">
            <div class="section-title fade-in-up">
                <h2>About the Office</h2>
                <div class="line"></div>
            </div>
            <div class="about-grid">
                <div class="about-text scroll-reveal">
                    <p class="lead"><?php echo isset($cms_about['about_text']) ? nl2br(htmlspecialchars($cms_about['about_text'])) : ''; ?></p>
                    
<?php if(!empty($cms_about['core_responsibilities'])): ?>
                    <h3 style="margin-top: 30px;">Core Responsibilities:</h3>
                    <ul class="task-list" style="margin-left: 20px; font-size: 1.05rem; color: #4a5568; line-height: 1.8;">
                        <?php 
                        // The Auto-Formatter: Safely turns raw CMS text into HTML bullets and bold text!
                        $core_text = $cms_about['core_responsibilities'];
                        $lines = explode("\n", trim($core_text));
                        
                        foreach($lines as $line) {
                            $line = trim($line);
                            if($line != '') {
                                // If you type a colon (:), it makes the first part bold
                                $parts = explode(":", $line, 2);
                                if(count($parts) > 1) {
                                    echo "<li style='margin-bottom: 8px;'><strong>" . htmlspecialchars(trim($parts[0])) . ":</strong> " . htmlspecialchars(trim($parts[1])) . "</li>";
                                } else {
                                    echo "<li style='margin-bottom: 8px;'>" . htmlspecialchars($line) . "</li>";
                                }
                            }
                        }
                        ?>
                    </ul>
                    <?php endif; ?>

                </div>
                <div class="about-visuals scroll-reveal right">
                    <div class="vertical-carousel">
                        <div class="carousel-track">
                            <?php foreach($carousel_images as $img): ?>
                                <img src="<?php echo $img['image_path']; ?>" alt="<?php echo $img['alt_text']; ?>">
                            <?php endforeach; ?>
                            
                            <?php foreach($carousel_images as $img): ?>
                                <img src="<?php echo $img['image_path']; ?>" alt="<?php echo $img['alt_text']; ?>">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="mission-vision" class="mv-section section">
        <div class="container">
            <div class="mv-grid">
                <div class="mv-card scroll-reveal">
                    <h3>Our Mission</h3>
                    <p><?php echo isset($cms_about['mission_text']) ? nl2br(htmlspecialchars($cms_about['mission_text'])) : ''; ?></p>
                </div>
                <div class="mv-card scroll-reveal delay-1">
                    <h3>Our Vision</h3>
                    <p><?php echo isset($cms_about['vision_text']) ? nl2br(htmlspecialchars($cms_about['vision_text'])) : ''; ?></p>
                </div>
            </div>
        </div>
    </section>

    <section id="impact" class="impact-section section">
        <div class="container">
            <div class="impact-grid">
                <div class="impact-item scroll-reveal up">
                    <h3 class="counter" data-target="<?php echo $researchConductedCount; ?>">0</h3>
                    <p>Researches Conducted</p>
                </div>
                <div class="impact-item scroll-reveal up delay-1">
                    <h3 class="counter" data-target="<?php echo $publicationtotal; ?>">0</h3>
                    <p>Total Publications</p>
                </div>
                <div class="impact-item scroll-reveal up delay-2">
                    <h3 class="counter" data-target="<?php echo $ipCount; ?>">0</h3>
                    <p>Intellectual Properties</p>
                </div>
                <div class="impact-item scroll-reveal up delay-3">
                    <h3 class="counter" data-target="<?php echo $ppCount; ?>">0</h3>
                    <p>Paper Presentations</p>
                </div>
                <div class="impact-item scroll-reveal up delay-1">
                    <h3 class="counter" data-target="<?php echo $epCount; ?>">0</h3>
                    <p>Extension Projects</p>
                </div>
                <div class="impact-item scroll-reveal up delay-2">
                    <h3 class="counter" data-target="<?php echo $researchertotal; ?>">0</h3>
                    <p>Active Researchers</p>
                </div>
            </div>
        </div>
    </section>

    <section id="news" class="news-section section">
        <div class="container">
            <div class="section-title fade-in-up">
                <h2>Latest from SDMU</h2>
                <div class="line"></div>
            </div>
            
            <div class="news-grid" id="newsGridContainer">
                <?php 
                foreach($news_items as $news): 
                    $formattedDate = date("F j, Y", strtotime($news['date_published']));
                ?>
                <article class="news-card js-news-card fade-in-up" style="display: none;">
                    <div class="news-img-wrapper">
                        <img src="<?php echo $news['image_path']; ?>" alt="News Image">
                    </div>
                    <div class="news-content">
                        <span class="news-date"><?php echo $formattedDate; ?></span>
                        <h3><a href="#"><?php echo htmlspecialchars($news['title']); ?></a></h3>
                        <p><?php echo htmlspecialchars($news['summary']); ?></p>
                        
                        <div class="hidden-full-content" style="display:none;">
                            <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                        </div>

                        <a href="#" class="read-more">Read Article &rarr;</a>
                    </div>
                </article>
                <?php 
                endforeach; 
                ?>
            </div>

            <div id="newsPagination" style="display: flex; justify-content: center; align-items: center; margin-top: 30px; gap: 20px;">
                <button id="prevNewsBtn" class="btn btn-secondary" style="border-radius: 50%; width: 45px; height: 45px; padding: 0; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 1.2rem; transition: all 0.3s ease;">&lt;</button>
                <span id="newsPageIndicator" style="font-weight: 600; color: #555; font-family: 'Inter', sans-serif;">1 / 1</span>
                <button id="nextNewsBtn" class="btn btn-secondary" style="border-radius: 50%; width: 45px; height: 45px; padding: 0; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 1.2rem; transition: all 0.3s ease;">&gt;</button>
            </div>
        </div>
    </section>

    <section id="rde-outputs" class="rde-section section">
        <div class="container rde-content scroll-reveal up">
            <h2>Research Development & Evaluation Outputs</h2>
            <p>Dive into the expansive repository of WMSU's research data, academic publications, and statistical findings.</p>
            <a href="rde-database.php" class="btn btn-primary large-btn">
                Access Research Database
            </a>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <div class="footer-brand">
                <h3>SDMU <span class="highlight">WMSU</span></h3>
                <p>Statistics and Data Management Unit<br>Western Mindanao State University</p>
            </div>
            <div class="footer-links">
                <img src="img/footerMap.png" alt="Map of WMSU Location" style="max-width: 100%; height: auto; border-radius: 8px; max-height: 200px;">
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Western Mindanao State University - SDMU. All rights reserved.</p>
        </div>
    </footer>

    <div id="newsModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" aria-label="Close modal">&times;</button>
            <div class="modal-body">
                <img id="modalImage" src="" alt="News Feature Image">
                <div class="modal-text">
                    <span id="modalDate" class="news-date"></span>
                    <h2 id="modalTitle"></h2>
                    <div id="modalFullText" class="modal-article-body"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/public_app.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const cards = document.querySelectorAll('.js-news-card');
        const prevBtn = document.getElementById('prevNewsBtn');
        const nextBtn = document.getElementById('nextNewsBtn');
        const indicator = document.getElementById('newsPageIndicator');
        const paginationContainer = document.getElementById('newsPagination');

        const itemsPerPage = 3;
        let currentPage = 1;
        const totalPages = Math.ceil(cards.length / itemsPerPage) || 1; 

        if (cards.length <= itemsPerPage) {
            paginationContainer.style.display = 'none';
        }

        function updateGrid() {
            cards.forEach(card => card.style.display = 'none');

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            for (let i = start; i < end && i < cards.length; i++) {
                // THE FIX: We removed 'block' and changed it to an empty string.
                // This clears our hidden style and lets your gorgeous CSS take over entirely!
                cards[i].style.display = ''; 
            }

            indicator.textContent = `${currentPage} / ${totalPages}`;
            
            if (currentPage === 1) {
                prevBtn.style.opacity = '0.4';
                prevBtn.style.cursor = 'not-allowed';
            } else {
                prevBtn.style.opacity = '1';
                prevBtn.style.cursor = 'pointer';
            }

            if (currentPage === totalPages) {
                nextBtn.style.opacity = '0.4';
                nextBtn.style.cursor = 'not-allowed';
            } else {
                nextBtn.style.opacity = '1';
                nextBtn.style.cursor = 'pointer';
            }
        }

        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updateGrid();
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                updateGrid();
            }
        });

        if (cards.length > 0) {
            updateGrid();
        }
    });
    </script>
</body>
</html>