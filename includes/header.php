<?php 
date_default_timezone_set('Asia/Manila');
$date = date('m/d/Y', time());
$time = date('h:i:s a', time());
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Research Archiving System Dashboard">
    <meta name="author" content="">

    <title>RAS | Dashboard</title>

    <link href="<?php echo $object->base_url; ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo $object->base_url; ?>vendor/font.css" rel="stylesheet">

    <link href="<?php echo $object->base_url; ?>css/sb-admin-2.min.css" rel="stylesheet">
    <link href="<?php echo $object->base_url; ?>vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $object->base_url; ?>vendor/parsley/parsley.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $object->base_url; ?>vendor/bootstrap-select/bootstrap-select.min.css"/>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $object->base_url; ?>img/hs.ico"/>

    <script src="<?php echo $object->base_url; ?>js/sweetalert2.js"></script>
    <script src="<?php echo $object->base_url; ?>js/canvasjs.min.js"></script>

    <style type="text/css">
        /* Professional Academic Gradient for Sidebar */
        .bg-gradient-primary-custom {
            background-color: #800000;
            background-image: linear-gradient(180deg, #800000 10%, #4a0000 100%);
            background-size: cover;
        }

        /* Topbar Typography */
        .topbar-title {
            color: #4a0000;
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        /* Sidebar Item Adjustments */
        .nav-item .nav-link span {
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

/* --- FIXED SIDEBAR CSS --- */
#accordionSidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh; /* Forces the sidebar to span the exact height of the screen */
    z-index: 1030; /* Keeps it layered above your content */
    overflow-y: auto; /* Adds a scrollbar INSIDE the sidebar if you add too many links */
}

/* Push the main content over so it doesn't hide behind the sidebar */
#content-wrapper {
    margin-left: 14rem; /* Matches the default expanded width of the sidebar */
    min-height: 100vh;
    width: calc(100% - 14rem);
    transition: margin-left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Adjust the content margin smoothly when the sidebar is collapsed (hover out) */
body.sidebar-toggled #content-wrapper {
    margin-left: 6.5rem; /* Matches the collapsed width */
    width: calc(100% - 6.5rem);
}

/* Mobile screen fixes (We don't want it permanently fixed on tiny phone screens) */
@media (max-width: 768px) {
    #content-wrapper, 
    body.sidebar-toggled #content-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
    }
    #accordionSidebar {
        position: relative; 
        height: 100%;
    }
}
/* --- END FIXED SIDEBAR CSS --- */


        /* Smooth Sidebar Transition */
#accordionSidebar {
    transition: width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}

#accordionSidebar .nav-item .nav-link span {
    transition: opacity 0.3s ease, margin 0.3s ease !important;
}
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav bg-gradient-primary-custom sidebar sidebar-dark accordion shadow" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center py-4" href="<?php echo $object->base_url; ?>dashboard.php" title="Dashboard">
                <div>
                    <img src="<?php echo $object->base_url; ?>img/wmsu_logo.png" alt="RAS Logo" width="40" height="40">
                </div>
                <div class="sidebar-brand-text mx-3">RAS Admin</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="<?php echo $object->base_url; ?>dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <?php if($object->is_master_user()) { ?>
            <div class="sidebar-heading">
                System Management
            </div>

            <li class="nav-item">
                <a class="nav-link pb-2" href="<?php echo $object->base_url; ?>modules/researchers/researcher.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Researchers' Data</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link pb-2" href="<?php echo $object->base_url; ?>modules/colleges/colleges.php">
                    <i class="fas fa-fw fa-university"></i>
                    <span>Colleges</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link pb-2" href="<?php echo $object->base_url; ?>modules/disciplines/disciplines.php">
                    <i class="fas fa-fw fa-book-open"></i>
                    <span>Discipline</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo $object->base_url; ?>modules/reports/report.php">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $object->base_url; ?>modules/documents/manage_documents.php">
                    <i class="fas fa-fw fa-file-signature"></i>
                    <span>Manage SO & MOA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $object->base_url; ?>modules/trackers/track_agenda_sdg.php">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Agenda & SDG Tracker</span>
                </a>
            </li>
            <?php } ?>

            <hr class="sidebar-divider d-none d-md-block mt-3">

            <div class="text-center d-none d-md-inline">
               
            </div>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars text-dark"></i>
                    </button>

                    <div class="d-none d-sm-block ml-3">
                        <h1 class="topbar-title">Research Archiving System</h1>
                    </div>

                    <ul class="navbar-nav ml-auto">

                        <?php
                        // Securely fetch user data
                        $user_id = $_SESSION['user_id'] ?? null;
                        $user_name = 'Administrator';
                        $user_profile_image = 'img/undraw_profile.svg';

                        if ($user_id) {
                            $object->query = "SELECT user_name, user_profile FROM user_table WHERE user_id = '$user_id'";
                            $user_result = $object->get_result();

                            foreach($user_result as $row) {
                                if(!empty($row['user_name'])) {
                                    $user_name = $row['user_name'];
                                }
                                if(!empty($row['user_profile'])) {
                                    $user_profile_image = $row['user_profile'];
                                }
                            }
                        }
                        ?>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-700 font-weight-bold">
                                    <?php echo htmlspecialchars($user_name); ?>
                                </span>
                                <img class="img-profile rounded-circle border" src="<?php echo $object->base_url; ?><?php echo htmlspecialchars($user_profile_image); ?>" alt="Profile">
                            </a>
                            
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo $object->base_url; ?>modules/profile/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <div class="container-fluid">