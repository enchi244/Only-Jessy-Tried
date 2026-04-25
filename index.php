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
    <title>Research Data Management System</title>
    <link rel="stylesheet" href="css/public_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="img/roblox.png" alt="University Logo" class="logo">
            <div class="header-text">
                <h1>St. Joseph College of Sindangan Incorporated</h1>
                <p>Research Data Management System</p>
            </div>
        </div>
    </header>

    <nav class="navbar">
        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="rde-database.php">RDE Database</a></li>
            <li><a href="#">News & Updates</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="login.php" class="login-btn">LOGIN</a></li>
        </ul>
    </nav>

    <section class="hero-slider">
        <div class="slider-container">
            <div class="slide fade">
                <img src="img/sampol.jpg" alt="Research Activity 1">
                <div class="slide-caption">
                    <h2>Advancing Innovation and Excellence</h2>
                    <p>Empowering researchers through comprehensive data management.</p>
                </div>
            </div>
            <div class="slide fade">
                <img src="img/sampolb.jpg" alt="Research Activity 2">
                <div class="slide-caption">
                    <h2>Collaborative Research Opportunities</h2>
                    <p>Fostering partnerships across various disciplines.</p>
                </div>
            </div>
            <div class="slide fade">
                <img src="img/research_center.jpg" alt="Research Facility">
                <div class="slide-caption">
                    <h2>State-of-the-Art Facilities</h2>
                    <p>Providing the resources needed for groundbreaking discoveries.</p>
                </div>
            </div>

            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
        </div>
        <br>
        <div style="text-align:center">
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
        </div>
    </section>

    <section class="statistics">
        <div class="stat-box">
            <h3><?php echo number_format($centersCount); ?></h3>
            <p>RDE Centers / Colleges</p>
        </div>
        <div class="stat-box">
            <h3><?php echo number_format($researchConductedCount); ?></h3>
            <p>Researches Conducted</p>
        </div>
        <div class="stat-box">
            <h3><?php echo number_format($researchertotal); ?></h3>
            <p>Active Researchers</p>
        </div>
        <div class="stat-box">
            <h3><?php echo number_format($publicationtotal); ?></h3>
            <p>Total Publications</p>
        </div>
    </section>

    <section class="news-preview">
        <h2>Latest News & Updates</h2>
        <div class="news-grid">
            <div class="news-card">
                <img src="img/images.jpg" alt="News 1">
                <div class="news-content">
                    <h4>New Grant Awarded for Sustainable Agriculture</h4>
                    <p class="date">October 15, 2023</p>
                    <p>The university has received a major grant to explore sustainable farming practices in the region.</p>
                    <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="news-card">
                <img src="img/group-photo-poses-aperture-priority.webp" alt="News 2">
                <div class="news-content">
                    <h4>Annual Research Symposium 2023</h4>
                    <p class="date">September 28, 2023</p>
                    <p>Join us for our annual symposium showcasing the latest findings from our dedicated researchers.</p>
                    <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="news-card">
                <img src="img/sample.webp" alt="News 3">
                <div class="news-content">
                    <h4>Breakthrough in Renewable Energy Tech</h4>
                    <p class="date">September 10, 2023</p>
                    <p>Our engineering department has published groundbreaking research on efficient solar cell materials.</p>
                    <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="view-all-container">
            <a href="#" class="btn-primary">View All News</a>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About SDMU</h3>
                <p>The Research Data Management Unit is dedicated to organizing, preserving, and sharing the intellectual output of St. Joseph College of Sindangan Incorporated.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="rde-database.php">RDE Database</a></li>
                    <li><a href="login.php">Admin Login</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> National Highway, Sindangan, Zamboanga del Norte</p>
                <p><i class="fas fa-envelope"></i> research@sjc.edu.ph</p>
                <p><i class="fas fa-phone"></i> (065) 123-4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> St. Joseph College of Sindangan Incorporated. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="js/public_app.js"></script>
</body>
</html>