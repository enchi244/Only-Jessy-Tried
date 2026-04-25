<?php
// Start the session and include your core DB connection
include('core/rms.php');

// $conn is created at the bottom of core/rms.php, we will use it for quick counts

// 1. Count Researches Conducted
$researchConductedCount = 0;
$rc_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_researchconducted");
if($rc_query && $row = mysqli_fetch_assoc($rc_query)) {
    $researchConductedCount = $row['total'];
}

// 2. Count Departments/Centers
$centersCount = 0;
$centers_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_category_table WHERE category_status = 'Enable'");
if($centers_query && $row = mysqli_fetch_assoc($centers_query)) {
    $centersCount = $row['total'];
}

// Note: $researchertotal and $publicationtotal are already calculated 
// dynamically at the bottom of your core/rms.php file!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDMU | St. Joseph College of Sindangan</title>
    <link rel="stylesheet" href="css/public_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-text">SDMU <span class="highlight">SJCSI</span></span>
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="rde-database.php" class="btn-nav">RDE Outputs</a></li>
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
            <p>Empowering St. Joseph College of Sindangan Incorporated through statistical expertise, data integrity, and actionable research insights.</p>
            <div class="hero-buttons">
                <a href="#about" class="btn btn-primary">Discover SDMU</a>
                <a href="#rde-outputs" class="btn btn-secondary">Explore Researches</a>
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
                    <p class="lead">Under the Research Development and Evaluation Center (RDEC), the SDMU collects, manages, and analyzes data within the University.</p>
                    <h3>Core Responsibilities:</h3>
                    <ul class="task-list">
                        <li><strong>Demographic Profiling:</strong> Collects pertinent data including the demographic profile of teaching and non-teaching personnel.</li>
                        <li><strong>Data Summarization:</strong> Presents data summaries with conclusions and recommendations.</li>
                        <li><strong>Information Handling:</strong> Handles data processing, storage, retrieval, and the production of hard-copy information.</li>
                        <li><strong>Database Management:</strong> Manages databases, encompassing consistent updating and data screening.</li>
                        <li><strong>Statistical Analysis:</strong> Performs advanced statistical computations and analyses for R&D projects.</li>
                    </ul>
                </div>
                <div class="about-visuals scroll-reveal right">
                    <div class="vertical-carousel">
                        <div class="carousel-track">
                            <img src="img/sampol.jpg" alt="SDMU Image 1">
                            <img src="img/sampolb.jpg" alt="SDMU Image 2">
                            <img src="img/roblox.png" alt="SDMU Image 3">
                            
                            <img src="img/sampol.jpg" alt="SDMU Image 1">
                            <img src="img/sampolb.jpg" alt="SDMU Image 2">
                            <img src="img/roblox.png" alt="SDMU Image 3">
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
                    <p>SDMU is committed to providing appropriate statistical information and analyses while ensuring that the R&D data of the University are properly stored and managed.</p>
                </div>
                <div class="mv-card scroll-reveal delay-1">
                    <h3>Our Vision</h3>
                    <p>SDMU aspires to become the leading hub of statistical expertise and data integrity within St. Joseph College of Sindangan Incorporated.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="impact" class="impact-section section">
        <div class="container">
            <div class="impact-grid">
                <div class="impact-item scroll-reveal up">
                    <h3 class="counter" data-target="<?php echo $centersCount; ?>">0</h3>
                    <p>RDE Centers / Colleges</p>
                </div>
                <div class="impact-item scroll-reveal up delay-1">
                    <h3 class="counter" data-target="<?php echo $researchConductedCount; ?>">0</h3>
                    <p>Researches Conducted</p>
                </div>
                <div class="impact-item scroll-reveal up delay-2">
                    <h3 class="counter" data-target="<?php echo $researchertotal; ?>">0</h3>
                    <p>Active Researchers</p>
                </div>
                <div class="impact-item scroll-reveal up delay-3">
                    <h3 class="counter" data-target="<?php echo $publicationtotal; ?>">0</h3>
                    <p>Total Publications</p>
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
            
            <div class="news-grid">
                <article class="news-card scroll-reveal up">
                    <div class="news-img-wrapper">
                        <img src="img/research_center.jpg" alt="News Image">
                    </div>
                    <div class="news-content">
                        <span class="news-date">October 12, 2026</span>
                        <h3><a href="#">SDMU Launches New Demographic Dashboard</a></h3>
                        <p>The office has officially rolled out the new interactive dashboard for teaching and non-teaching personnel data...</p>
                        <a href="#" class="read-more">Read Article &rarr;</a>
                    </div>
                </article>

                <article class="news-card scroll-reveal up delay-1">
                    <div class="news-img-wrapper">
                        <img src="img/research_center.jpg" alt="News Image">
                    </div>
                    <div class="news-content">
                        <span class="news-date">September 28, 2026</span>
                        <h3><a href="#">SJCSI Researchers Benefit from Advanced Statistical Tools</a></h3>
                        <p>A recent seminar hosted by SDMU showcased advanced statistical computation methods for ongoing R&D projects...</p>
                        <a href="#" class="read-more">Read Article &rarr;</a>
                    </div>
                </article>

                <article class="news-card scroll-reveal up delay-2">
                    <div class="news-img-wrapper">
                        <img src="img/research_center.jpg" alt="News Image">
                    </div>
                    <div class="news-content">
                        <span class="news-date">September 10, 2026</span>
                        <h3><a href="#">Annual Data Integrity Audit Completed</a></h3>
                        <p>Ensuring the highest standards, the SDMU has successfully concluded the annual screening of the university's database...</p>
                        <a href="#" class="read-more">Read Article &rarr;</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="rde-outputs" class="rde-section section">
        <div class="container rde-content scroll-reveal up">
            <h2>Research Development & Evaluation Outputs</h2>
            <p>Dive into the expansive repository of SJCSI's research data, academic publications, and statistical findings.</p>
            <a href="rde-database.php" class="btn btn-primary large-btn">
                Access Research Database
            </a>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <div class="footer-brand">
                <h3>SDMU <span class="highlight">SJCSI</span></h3>
                <p>Statistics and Data Management Unit<br>St. Joseph College of Sindangan Incorporated</p>
            </div>
            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="rde-database.php">RDE Outputs</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> St. Joseph College of Sindangan Incorporated - SDMU. All rights reserved.</p>
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
                    <div id="modalFullText" class="modal-article-body">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/public_app.js"></script>
</body>
</html>