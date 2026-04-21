<?php
include('../../core/rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}
if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
}

// 1. SECURE THE PAGE & FETCH RESEARCHER DATA
if(!isset($_GET['id'])) {
    header("location: researcher.php");
    exit();
}

$researcher_id = $_GET['id'];
$object->query = "SELECT * FROM tbl_researchdata WHERE id = :id";
$object->execute([':id' => $researcher_id]);
if($object->row_count() == 0) {
    header("location: researcher.php");
    exit();
}
$researcher_data = $object->statement->fetch(PDO::FETCH_ASSOC);

// Format the name nicely
$full_name = trim($researcher_data['firstName'] . ' ' . $researcher_data['middleName'] . ' ' . $researcher_data['familyName'] . ' ' . $researcher_data['Suffix']);

$object->query = "SELECT rc.* FROM tbl_researchconducted rc JOIN tbl_research_collaborators col ON rc.id = col.research_id WHERE col.researcher_id = '$researcher_id' ORDER BY rc.started_date DESC";
$object->execute();
$research_conducted = $object->statement_result();

// Use join tables to ensure co-authors/collaborators see records on their profiles
$object->query = "SELECT p.* FROM tbl_publication p JOIN tbl_publication_collaborators col ON p.id = col.publication_id WHERE col.researcher_id = '$researcher_id' AND p.status = 1 ORDER BY p.publication_date DESC";
$object->execute();
$publications = $object->statement_result();

$object->query = "SELECT ip.* FROM tbl_itelectualprop ip JOIN tbl_ip_collaborators col ON ip.id = col.ip_id WHERE col.researcher_id = '$researcher_id' AND ip.status = 1 ORDER BY ip.date_applied DESC";
$object->execute();
$intellectual_props = $object->statement_result();

$object->query = "SELECT * FROM tbl_paperpresentation WHERE researcherID = '$researcher_id' ORDER BY date_paper DESC";
$object->execute();
$presentations = $object->statement_result();

$object->query = "SELECT * FROM tbl_trainingsattended WHERE researcherID = '$researcher_id' ORDER BY date_train DESC";
$object->execute();
$trainings = $object->statement_result();

$object->query = "SELECT *, (SELECT COUNT(l.id) FROM tbl_extension_activity_links l JOIN tbl_ext e ON l.extension_activity_id = e.id WHERE l.extension_project_id = tbl_extension_project_conducted.id AND (e.status = 1 OR e.status IS NULL)) AS ext_count FROM tbl_extension_project_conducted WHERE researcherID = '$researcher_id' AND status = 1 ORDER BY start_date DESC";
$object->execute();
$ext_projects = $object->statement_result();

$object->query = "SELECT * FROM tbl_ext WHERE researcherID = '$researcher_id' ORDER BY period_implement DESC";
$object->execute();
$extensions = $object->statement_result();

include('../../includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="<?php echo $object->base_url; ?>css/select2.min.css">
<style>
    .pink { background-color: #f23e5d; color: white; }
    .pink:hover { background-color: #e32747; color: white; }

    /* Custom Vertical Timeline */
    .timeline { border-left: 3px solid #eaecf4; padding-left: 25px; margin-left: 15px; position: relative; }
    .timeline-item { margin-bottom: 30px; position: relative; }
    .timeline-item::before { content: ''; position: absolute; left: -33px; top: 5px; width: 14px; height: 14px; border-radius: 50%; background-color: #f23e5d; border: 3px solid white; box-shadow: 0 0 0 2px #eaecf4; }
    .timeline-date { font-size: 0.85rem; font-weight: bold; color: #4e73df; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px; }
    
    /* Modern Profile Header */
    .profile-header { background: linear-gradient(135deg, #800000 0%, #4a0000 100%); border-radius: 10px; color: white; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .profile-avatar { width: 100px; height: 100px; background-color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #800000; font-weight: bold; }
    
    /* Sleek Vertical Nav */
    .nav-pills .nav-link { color: #5a5c69; font-weight: 600; padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; transition: all 0.2s; cursor: pointer; }
    .nav-pills .nav-link:hover { background-color: #eaecf4; }
    .nav-pills .nav-link.active, .nav-pills .show>.nav-link { background-color: #f23e5d; color: white; box-shadow: 0 4px 6px rgba(242, 62, 93, 0.2); }

    /* Clean Inner Nav Tabs styling */
    #epcInnerTabs .nav-link {
        color: #5a5c69;
        border: none;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    #epcInnerTabs .nav-link:hover {
        color: #f23e5d;
    }
    #epcInnerTabs .nav-link.active {
        color: #f23e5d !important;
        background-color: transparent;
        border-bottom: 3px solid #f23e5d;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    /* --- NEW CSS FOR CLICKABLE CARDS & FAB --- */
    .clickable-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid transparent;
    }
    .clickable-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
        border-color: #eaecf4;
    }
    
    /* Prevents clicking 'Delete' from triggering the card's edit action */
    .isolate-click {
        position: relative;
        z-index: 10;
    }

    /* Floating Action Button */
    .fab-container {
        position: fixed;
        bottom: 40px;
        right: 40px;
        z-index: 1000;
        display: none; /* Hidden by default until a non-profile tab is selected */
    }
    .fab-btn {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(242, 62, 93, 0.4);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .fab-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 25px rgba(242, 62, 93, 0.5);
    }
</style>

<a href="researcher.php" id="back_to_directory" class="btn btn-light btn-sm mb-3 shadow-sm font-weight-bold text-gray-700">
    <i class="fas fa-arrow-left mr-2"></i>Back to Directory
</a>

<button type="button" id="hidden_profile_edit" class="edit_buttona d-none" data-id="<?php echo htmlspecialchars($researcher_id); ?>"></button>

<div class="profile-header d-flex align-items-center clickable-card position-relative" title="Click to Edit Profile" onclick="document.getElementById('hidden_profile_edit').click();">
    
    <div class="position-absolute text-white-50" style="top: 20px; right: 25px; font-size: 1.5rem; opacity: 0.7;">
        <i class="fas fa-user-edit"></i>
    </div>
    
    <div class="profile-avatar mr-4 shadow">
        <?php echo substr($researcher_data['firstName'], 0, 1) . substr($researcher_data['familyName'], 0, 1); ?>
    </div>
    <div>
        <h2 class="mb-1 font-weight-bold"><?php echo htmlspecialchars($full_name); ?></h2>
        <div class="mb-2">
            <span class="badge badge-light text-danger px-3 py-2 rounded-pill mr-2 shadow-sm">
                <i class="fas fa-building mr-1"></i> <?php echo htmlspecialchars($researcher_data['department']); ?>
            </span>
            <span class="badge badge-light text-primary px-3 py-2 rounded-pill shadow-sm">
                <i class="fas fa-graduation-cap mr-1"></i> <?php echo htmlspecialchars($researcher_data['program']); ?>
            </span>
            <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm ml-2">
                <i class="fas fa-award mr-1"></i> <?php echo !empty($researcher_data['academic_rank']) ? htmlspecialchars($researcher_data['academic_rank']) : 'Rank Not Set'; ?>
            </span>
        </div>
        <p class="mb-0 text-white-50"><i class="fas fa-id-badge mr-2"></i>System ID: <?php echo htmlspecialchars($researcher_id); ?> | Researcher ID: <?php echo htmlspecialchars($researcher_data['researcherID']); ?></p>
    </div>
</div>

<?php include('modals/add_researchConducted.php'); ?>
<?php include('modals/add_publication.php'); ?>
<?php include('modals/add_intellectualProperty.php'); ?>
<?php include('modals/add_paperPresentation.php'); ?>
<?php include('modals/add_trainingsAttended.php'); ?>
<?php include('modals/add_extensionProject.php'); ?> 
<?php include('modals/add_extension.php'); ?>

<div class="row">
    <div class="col-xl-3 col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active custom-tab-btn" id="tab-personal-info" href="#personal-info" role="tab"><i class="fas fa-user mr-2"></i> Profile Overview</a>
                    <a class="nav-link custom-tab-btn" id="tab-education" href="#education" role="tab"><i class="fas fa-microscope mr-2"></i> Research Conducted</a>
                    <a class="nav-link custom-tab-btn" id="tab-degree" href="#degree" role="tab"><i class="fas fa-book mr-2"></i> Publications</a>
                    <a class="nav-link custom-tab-btn" id="tab-ip" href="#ip" role="tab"><i class="fas fa-lightbulb mr-2"></i> Intellectual Property</a>
                    <a class="nav-link custom-tab-btn" id="tab-pp" href="#pp" role="tab"><i class="fas fa-file-alt mr-2"></i> Paper Presentation</a>
                    <a class="nav-link custom-tab-btn" id="tab-tra" href="#tra" role="tab"><i class="fas fa-chalkboard-teacher mr-2"></i> Trainings</a>
                    <a class="nav-link custom-tab-btn" id="tab-epc" href="#epc" role="tab"><i class="fas fa-project-diagram mr-2"></i> Extension Projects</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span id="message"></span>
                <div class="tab-content" id="v-pills-tabContent">

                    <div class="tab-pane custom-tab-pane active" id="personal-info" role="tabpanel" style="display: block;">
                        <h4 class="font-weight-bold text-gray-800 mb-4 border-bottom pb-2"><i class="fas fa-graduation-cap text-primary mr-2"></i>Academic Background</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-left-primary h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Bachelor's Degree</div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800"><?php echo !empty($researcher_data['bachelor_degree']) ? htmlspecialchars($researcher_data['bachelor_degree']) : '<span class="text-muted font-italic">Not Specified</span>'; ?></div>
                                        <div class="text-sm text-muted mb-1"><i class="fas fa-university mr-2"></i><?php echo !empty($researcher_data['bachelor_institution']) ? htmlspecialchars($researcher_data['bachelor_institution']) : '<span class="text-muted font-italic">No Institution Provided</span>'; ?></div>
                                        <div class="text-sm text-muted"><i class="fas fa-calendar-alt mr-2"></i>Class of <?php echo !empty($researcher_data['bachelor_YearGraduated']) ? htmlspecialchars($researcher_data['bachelor_YearGraduated']) : '<span class="text-muted font-italic">N/A</span>'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-left-success h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Master's Degree</div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800"><?php echo !empty($researcher_data['masterDegree']) ? htmlspecialchars($researcher_data['masterDegree']) : '<span class="text-muted font-italic">Not Specified</span>'; ?></div>
                                        <div class="text-sm text-muted mb-1"><i class="fas fa-university mr-2"></i><?php echo !empty($researcher_data['masterInstitution']) ? htmlspecialchars($researcher_data['masterInstitution']) : '<span class="text-muted font-italic">No Institution Provided</span>'; ?></div>
                                        <div class="text-sm text-muted"><i class="fas fa-calendar-alt mr-2"></i>Class of <?php echo !empty($researcher_data['masterYearGraduated']) ? htmlspecialchars($researcher_data['masterYearGraduated']) : '<span class="text-muted font-italic">N/A</span>'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-left-info h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Doctorate Degree</div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800"><?php echo !empty($researcher_data['doctorateDegree']) ? htmlspecialchars($researcher_data['doctorateDegree']) : '<span class="text-muted font-italic">Not Specified</span>'; ?></div>
                                        <div class="text-sm text-muted mb-1"><i class="fas fa-university mr-2"></i><?php echo !empty($researcher_data['doctorateInstitution']) ? htmlspecialchars($researcher_data['doctorateInstitution']) : '<span class="text-muted font-italic">No Institution Provided</span>'; ?></div>
                                        <div class="text-sm text-muted"><i class="fas fa-calendar-alt mr-2"></i>Class of <?php echo !empty($researcher_data['doctorateYearGraduate']) ? htmlspecialchars($researcher_data['doctorateYearGraduate']) : '<span class="text-muted font-italic">N/A</span>'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-left-warning h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Post-Doctorate</div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800"><?php echo !empty($researcher_data['postDegree']) ? htmlspecialchars($researcher_data['postDegree']) : '<span class="text-muted font-italic">Not Specified</span>'; ?></div>
                                        <div class="text-sm text-muted mb-1"><i class="fas fa-university mr-2"></i><?php echo !empty($researcher_data['postInstitution']) ? htmlspecialchars($researcher_data['postInstitution']) : '<span class="text-muted font-italic">No Institution Provided</span>'; ?></div>
                                        <div class="text-sm text-muted"><i class="fas fa-calendar-alt mr-2"></i>Class of <?php echo !empty($researcher_data['postYearGraduate']) ? htmlspecialchars($researcher_data['postYearGraduate']) : '<span class="text-muted font-italic">N/A</span>'; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="education" role="tabpanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Research Timeline</h4>
                        </div>
                        
                        <?php if(count($research_conducted) > 0): ?>
                            <div class="timeline mt-4">
                                <?php foreach($research_conducted as $rc): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date"><?php echo htmlspecialchars($rc['started_date']); ?> to <?php echo htmlspecialchars($rc['completed_date']); ?></div>
                                        <div class="card shadow-sm border-0 bg-light clickable-card edit_button_researchconducted" data-id="<?php echo $rc['id']; ?>">
                                            <div class="card-body py-3">
                                                <div class="float-right isolate-click">
                                                    <button class="btn btn-sm btn-link text-danger delete_button_researchconducted" data-id="<?php echo $rc['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($rc['title']); ?></h6>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-bullseye mr-1"></i> SDG: <?php echo htmlspecialchars($rc['sdgs']); ?> | 
                                                    <i class="fas fa-wallet ml-2 mr-1"></i> <?php echo htmlspecialchars($rc['funding_source']); ?> (₱<?php echo number_format((float)$rc['approved_budget'], 2); ?>)
                                                </p>
                                                <span class="badge badge-primary px-2 py-1"><?php echo htmlspecialchars($rc['stat']); ?></span>
                                                <span class="badge badge-light px-2 py-1 ml-1 border">Cluster: <?php echo htmlspecialchars($rc['research_agenda_cluster']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No research conducted records found.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="degree" role="tabpanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Publications</h4>
                        </div>

                        <?php if(count($publications) > 0): ?>
                            <?php foreach($publications as $pub): ?>
                                <div class="card shadow-sm mb-3 border-left-primary position-relative clickable-card edit_button_publication" data-id="<?php echo $pub['id']; ?>">
                                    <div class="card-body py-3">
                                        <div class="position-absolute isolate-click" style="top: 15px; right: 15px;">
                                            <button class="btn btn-sm btn-light text-danger shadow-sm delete_button_publication" data-id="<?php echo $pub['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <h5 class="font-weight-bold text-gray-800 mb-1 pr-5"><?php echo htmlspecialchars($pub['title']); ?></h5>
                                        <p class="text-muted mb-2 font-size-sm">
                                            <i class="fas fa-book-open mr-2"></i> <?php echo htmlspecialchars($pub['journal']); ?> | Vol/Issue: <?php echo htmlspecialchars($pub['vol_num_issue_num']); ?>
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge badge-success px-2 py-1 mr-2"><i class="fas fa-check-circle mr-1"></i> <?php echo htmlspecialchars($pub['indexing']); ?></span>
                                            <span class="badge badge-light text-dark px-2 py-1 mr-2"><i class="far fa-calendar-alt mr-1"></i> Published: <?php echo htmlspecialchars($pub['publication_date']); ?></span>
                                            <span class="badge badge-light border border-primary text-primary px-2 py-1 mr-2"><i class="fas fa-hourglass-start mr-1"></i> Start: <?php echo !empty($pub['start']) ? htmlspecialchars($pub['start']) : 'N/A'; ?></span>
                                            <span class="badge badge-light border border-danger text-danger px-2 py-1 mr-2"><i class="fas fa-flag-checkered mr-1"></i> End: <?php echo !empty($pub['end']) ? htmlspecialchars($pub['end']) : 'N/A'; ?></span>
                                            <span class="badge badge-light text-dark px-2 py-1 mt-1 d-inline-block"><i class="fas fa-barcode mr-1"></i> ISSN: <?php echo htmlspecialchars($pub['issn_isbn']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No publications recorded yet.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="ip" role="tabpanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Intellectual Property</h4>
                        </div>

                        <?php if(count($intellectual_props) > 0): ?>
                            <div class="row">
                                <?php foreach($intellectual_props as $ip): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card shadow-sm border-left-warning h-100 clickable-card edit_button_intellectualprop" data-id="<?php echo $ip['id']; ?>">
                                            <div class="card-body py-3 position-relative">
                                                <div class="position-absolute isolate-click" style="top: 10px; right: 10px;">
                                                    <button class="btn btn-sm btn-light text-danger delete_button_intellectualprop" data-id="<?php echo $ip['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 pr-5"><?php echo htmlspecialchars($ip['title']); ?></h6>
                                                <p class="text-muted small mb-2"><i class="fas fa-users mr-1"></i> Co-authors: <?php echo htmlspecialchars($ip['coauth']); ?></p>
                                                <div class="mt-3">
                                                    <span class="badge badge-warning text-dark px-2 py-1 mb-1"><i class="fas fa-certificate mr-1"></i> <?php echo htmlspecialchars($ip['type']); ?></span><br>
                                                   <?php if(!empty($ip['a_link'])): ?>
                                                        <a href="<?php echo htmlspecialchars($ip['a_link']); ?>" target="_blank" class="badge badge-primary px-2 py-1 mb-1 isolate-click shadow-sm" style="text-decoration: none;"><i class="fas fa-external-link-alt mr-1"></i> View External Link</a><br>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><b>Applied:</b> <?php echo htmlspecialchars($ip['date_applied']); ?> | <b>Granted:</b> <?php echo htmlspecialchars($ip['date_granted']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No intellectual property records found.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="pp" role="tabpanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Paper Presentations</h4>
                        </div>

                            <?php if(count($presentations) > 0): ?>
                            <?php foreach($presentations as $paper): ?>
                                <div class="card shadow-sm mb-3 border-left-info position-relative clickable-card edit_button_paper_presentation" data-id="<?php echo $paper['id']; ?>">
                                    <div class="card-body py-3">
                                        <div class="position-absolute isolate-click" style="top: 15px; right: 15px;">
                                            <button class="btn btn-sm btn-light text-danger shadow-sm delete_button_paper_presentation" data-id="<?php echo $paper['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <h5 class="font-weight-bold text-gray-800 mb-1 pr-5"><?php echo htmlspecialchars($paper['title']); ?></h5>
                                        <p class="text-muted mb-2 font-size-sm">
                                            <i class="fas fa-building mr-1 text-primary"></i> <?php echo !empty($paper['conference_organizer']) ? htmlspecialchars($paper['conference_organizer']) : '<i class="text-muted font-italic">Organizer not specified</i>'; ?> &nbsp;|&nbsp; 
                                            <i class="fas fa-map-marker-alt mx-1 text-danger"></i> <?php echo htmlspecialchars($paper['conference_venue']); ?> &nbsp;|&nbsp; 
                                            <i class="far fa-calendar-alt mx-1 text-success"></i> <?php echo htmlspecialchars($paper['date_paper']); ?>
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge badge-info px-2 py-1 mr-2"><i class="fas fa-microphone mr-1"></i><?php echo !empty($paper['type_pp']) ? htmlspecialchars($paper['type_pp']) : 'Presentation'; ?></span>
                                            <?php if(!empty($paper['a_link'])): ?>
                                                <?php 
                                                $links = explode("\n", trim($paper['a_link']));
                                                foreach($links as $index => $link): 
                                                    $link = trim($link);
                                                    if(!empty($link)):
                                                ?>
                                                    <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" class="badge badge-primary px-2 py-1 mr-2 isolate-click shadow-sm" style="text-decoration: none;"><i class="fas fa-external-link-alt mr-1"></i> Link <?php echo $index + 1; ?></a>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            <?php endif; ?>
                                            <span class="badge badge-secondary px-2 py-1 mr-2"><?php echo htmlspecialchars($paper['conference_title']); ?> Level</span>
                                            <span class="badge badge-light border px-2 py-1"><i class="fas fa-book-reader mr-1 text-muted"></i><?php echo !empty($paper['discipline']) ? htmlspecialchars($paper['discipline']) : 'Discipline N/A'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No paper presentations recorded yet.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="tra" role="tabpanel" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Trainings & Development</h4>
                        </div>
                        
                        <?php if(count($trainings) > 0): ?>
                            <div class="timeline mt-4">
                                <?php foreach($trainings as $train): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date"><?php echo htmlspecialchars($train['date_train']); ?> (<?php echo htmlspecialchars($train['totnh']); ?> Hours)</div>
                                        <div class="card shadow-sm border-0 bg-light clickable-card edit_button_training" data-id="<?php echo $train['id']; ?>">
                                            <div class="card-body py-3">
                                                <div class="float-right isolate-click">
                                                    <button class="btn btn-sm btn-link text-danger delete_button_training" data-id="<?php echo $train['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($train['title']); ?></h6>
                                                    <p class="text-muted small mb-2"><i class="fas fa-building mr-1 text-primary"></i> <?php echo htmlspecialchars($train['sponsor_org']); ?> &nbsp;|&nbsp; <i class="fas fa-map-marker-alt mx-1 text-danger"></i> <?php echo htmlspecialchars($train['venue']); ?></p>
                                                <div class="mt-2">
                                                    <span class="badge badge-info px-2 py-1 mr-1"><?php echo htmlspecialchars($train['lvl']); ?> Level</span>
                                                    <?php if(!empty($train['a_link'])): ?>
                                                        <?php 
                                                        $links = explode("\n", trim($train['a_link']));
                                                        foreach($links as $index => $link): 
                                                            $link = trim($link);
                                                            if(!empty($link)):
                                                        ?>
                                                            <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" class="badge badge-primary px-2 py-1 mr-1 isolate-click shadow-sm" style="text-decoration: none;"><i class="fas fa-external-link-alt mr-1"></i> Link <?php echo $index + 1; ?></a>
                                                        <?php 
                                                            endif;
                                                        endforeach; 
                                                        ?>
                                                    <?php endif; ?>
                                                    <span class="badge badge-secondary px-2 py-1 mr-1"><?php echo !empty($train['type_training']) ? htmlspecialchars($train['type_training']) : 'Training'; ?></span>
                                                    <span class="badge badge-light border px-2 py-1"><i class="fas fa-book-reader mr-1 text-muted"></i><?php echo !empty($train['type_learning_dev']) ? htmlspecialchars($train['type_learning_dev']) : 'N/A'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No training records found.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane custom-tab-pane" id="epc" role="tabpanel" style="display: none;">
                        
                        <ul class="nav nav-tabs mb-4 border-bottom-danger" id="epcInnerTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="inner-epc-tab" data-toggle="tab" href="#inner-epc" role="tab"><i class="fas fa-project-diagram mr-2"></i>Extension Projects Conducted</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="inner-ext-tab" data-toggle="tab" href="#inner-ext" role="tab"><i class="fas fa-hands-helping mr-2"></i>Extension Activities</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="epcInnerTabsContent">
                            
                            <div class="tab-pane fade show active" id="inner-epc" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="font-weight-bold text-gray-800 m-0">Extension Projects Conducted</h4>
                                </div>

                                <?php if(count($ext_projects) > 0): ?>
                                    <div class="timeline mt-4">
                                        <?php foreach($ext_projects as $ep): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-date"><?php echo htmlspecialchars($ep['start_date']); ?> to <?php echo htmlspecialchars($ep['completed_date']); ?></div>
                                                <div class="card shadow-sm border-0 bg-light clickable-card edit_button_extension_project" data-id="<?php echo $ep['id']; ?>">
                                                    <div class="card-body py-3">
                                                        <div class="float-right isolate-click">
                                                            <button class="btn btn-sm btn-link text-danger delete_button_extension_project" data-id="<?php echo $ep['id']; ?>"><i class="fas fa-trash"></i></button>
                                                        </div>
                                                        <h6 class="font-weight-bold text-gray-800 mb-3 pr-4"><?php echo htmlspecialchars($ep['title']); ?></h6>
                                                        
                                                        <div class="row text-muted small mb-2">
                                                            <div class="col-md-6 mb-2">
                                                                <div class="mb-1"><i class="fas fa-users mr-2 text-primary"></i> <b>Beneficiaries:</b> <br><span class="ml-4"><?php echo htmlspecialchars($ep['target_beneficiaries_communities']); ?></span></div>
                                                                <div class="mt-2"><i class="fas fa-handshake mr-2 text-primary"></i> <b>Partners:</b> <br><span class="ml-4"><?php echo htmlspecialchars($ep['partners']); ?></span></div>
                                                            </div>
                                                            <div class="col-md-6 mb-2 border-left">
                                                                <div class="mb-1"><i class="fas fa-wallet mr-2 text-success"></i> <b>Funding Source:</b> <br><span class="ml-4"><?php echo htmlspecialchars($ep['funding_source']); ?></span></div>
                                                                <div class="mt-2"><i class="fas fa-money-bill-wave mr-2 text-success"></i> <b>Approved Budget:</b> <br><span class="ml-4 font-weight-bold">₱<?php echo number_format((float)$ep['approved_budget'], 2); ?></span></div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-2">
                                                            <span class="badge badge-success px-2 py-1 mr-2"><i class="fas fa-info-circle mr-1"></i><?php echo htmlspecialchars($ep['status_exct']); ?></span>
                                                            <button type="button" onclick="$('#inner-ext-tab').tab('show');" class="badge <?php echo ($ep['ext_count'] > 0) ? 'badge-info' : 'badge-secondary'; ?> px-2 py-1 mr-2 border-0 isolate-click" <?php echo ($ep['ext_count'] == 0) ? 'disabled title="No extensions linked"' : 'title="View Activities"'; ?>>
                                                                <i class="fas fa-hands-helping mr-1"></i> Extension Activities (<?php echo $ep['ext_count']; ?>)
                                                            </button>
                                                            <?php if(!empty($ep['terminal_report_file'])): ?>
                                                                <a href="../../uploads/documents/<?php echo htmlspecialchars($ep['terminal_report_file']); ?>" target="_blank" class="badge badge-primary px-2 py-1 mr-2 isolate-click shadow-sm" style="text-decoration: none;"><i class="fas fa-file-download mr-1"></i> Download Report</a>
                                                            <?php else: ?>
                                                                <span class="badge badge-light border px-2 py-1 mr-2"><i class="fas fa-file-alt mr-1 text-muted"></i>Report: <?php echo !empty($ep['terminal_report']) ? htmlspecialchars($ep['terminal_report']) : 'N/A'; ?></span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php
                                                        // Fetch linked research projects for this extension
                                                        $object->query = "
                                                            SELECT r.title, 
                                                                   pd.familyName AS lead_familyName, pd.firstName AS lead_firstName,
                                                                   (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') 
                                                                    FROM tbl_research_collaborators col 
                                                                    JOIN tbl_researchdata d ON col.researcher_id = d.id 
                                                                    WHERE col.research_id = r.id) AS co_authors
                                                            FROM tbl_extension_research_links l 
                                                            JOIN tbl_researchconducted r ON l.research_id = r.id 
                                                            LEFT JOIN tbl_researchdata pd ON (pd.id = r.lead_researcher_id OR pd.id = r.researcherID OR pd.researcherID = r.researcherID)
                                                            WHERE l.extension_id = '".$ep['id']."'
                                                        ";
                                                        $object->execute();
                                                        $linked_res = $object->statement_result();
                                                        if(count($linked_res) > 0):
                                                        ?>
                                                            <div class="mt-3 pt-2 border-top">
                                                                <small class="text-muted d-block mb-2"><i class="fas fa-link mr-1 text-primary"></i> <b>Based on Research:</b></small>
                                                                <?php foreach($linked_res as $lr): 
                                                                    $lead = $lr['lead_familyName'] ? htmlspecialchars($lr['lead_familyName'] . ', ' . $lr['lead_firstName']) : 'Unknown Lead';
                                                                    $co = $lr['co_authors'] ? htmlspecialchars($lr['co_authors']) : 'None';
                                                                ?>
                                                                    <div class="mb-2 p-2 bg-white border rounded shadow-sm">
                                                                        <div class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;"><i class="fas fa-flask mr-1 text-danger pink"></i> <?php echo htmlspecialchars($lr['title']); ?></div>
                                                                        <div class="ml-3 small"><span class="badge badge-primary px-2 py-0 mr-1">Lead</span> <?php echo $lead; ?></div>
                                                                        <div class="ml-3 mt-1 small text-muted"><i class="fas fa-users mr-1"></i> <?php echo $co; ?></div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-light text-center py-4 border">No extension projects found.</div>
                                <?php endif; ?>
                            </div>

                            <div class="tab-pane fade" id="inner-ext" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="font-weight-bold text-gray-800 m-0">Extension Activities</h4>
                                </div>

                                <?php if(count($extensions) > 0): ?>
                                    <?php foreach($extensions as $ext): ?>
                                        <div class="card shadow-sm mb-3 border-left-success position-relative clickable-card edit_button_ext" data-id="<?php echo $ext['id']; ?>">
                                            <div class="card-body py-3">
                                                <div class="position-absolute isolate-click" style="top: 15px; right: 15px;">
                                                    <button class="btn btn-sm btn-light text-danger shadow-sm delete_button_ext" data-id="<?php echo $ext['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h5 class="font-weight-bold text-gray-800 mb-1 pr-5"><?php echo htmlspecialchars($ext['title']); ?></h5>
                                                <p class="text-muted mb-2 font-size-sm">
                                                    <i class="fas fa-user-tie mr-1"></i> Lead: <?php echo htmlspecialchars($ext['proj_lead']); ?> | Assist: <?php echo htmlspecialchars($ext['assist_coordinators']); ?>
                                                </p>
                                                <p class="mb-2 text-dark small"><?php echo htmlspecialchars($ext['description']); ?></p>
                                                
                                                <div class="mt-2 bg-light p-2 rounded small">
                                                    <b><i class="far fa-calendar-alt mr-1"></i> Period:</b> <?php echo htmlspecialchars($ext['period_implement']); ?> | 
                                                    <b><i class="fas fa-users ml-2 mr-1"></i> Beneficiaries:</b> <?php echo htmlspecialchars($ext['target_beneficiaries']); ?> |
                                                    <b><i class="fas fa-money-bill-wave ml-2 mr-1"></i> Budget:</b> ₱<?php echo number_format((float)$ext['budget'], 2); ?>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-info-circle mr-1"></i> <?php echo htmlspecialchars($ext['stat']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-light text-center py-4 border">No extension activities found.</div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <button type="button" id="add_researcherconducted"></button>
    <button type="button" id="add_publication"></button>
    <button type="button" id="add_intellectualprop"></button>
    <button type="button" id="add_paper_presentation"></button>
    <button type="button" id="add_training_attended"></button>
    <button type="button" id="add_extension_project"></button>
    <button type="button" id="add_extension"></button>
</div>

<div class="fab-container" id="fab-container">
    <button class="btn btn-danger pink fab-btn text-white" id="dynamic-fab-btn" title="Add New Record">
        <i class="fas fa-plus"></i>
    </button>
</div>

<input type="hidden" id="hidden_id_rd" value="<?php echo htmlspecialchars($researcher_id); ?>">

<div id="researcherModala" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title d-flex align-items-center" name="modal_title" id="modal_title">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fas fa-user-edit"></i>
                </div>
                Update Profile Data
            </h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <span id="form_message"></span>

            <form method="post" id="researcherModala_form">
                <div class="form-group"><label>Researcher ID</label><input type="text" name="researcherIDu" id="researcherIDu" class="form-control bg-light" required maxlength="50" readonly /></div>
                
                <h6 class="font-weight-bold text-gray-700 mb-3 mt-4 border-bottom pb-2"><i class="fas fa-user mr-2 text-secondary"></i>Personal Information</h6>
                <div class="form-group row">
                    <div class="col-md-3"><label>Family Name</label><input type="text" name="familyNameu" id="familyNameu" class="form-control" required maxlength="100" /></div>
                    <div class="col-md-3"><label>First Name</label><input type="text" name="firstNameu" id="firstNameu" class="form-control" required maxlength="100" /></div>
                    <div class="col-md-3"><label>Middle Name</label><input type="text" name="middleNameu" id="middleNameu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-3"><label>Suffix</label><input type="text" name="Suffixu" id="Suffixu" class="form-control" maxlength="10" /></div>
                </div>
                
                <h6 class="font-weight-bold text-gray-700 mb-3 mt-4 border-bottom pb-2"><i class="fas fa-building mr-2 text-secondary"></i>Academic Assignment</h6>
                <div class="form-group row">
                    <div class="col-md-4">
                        <label>Select Department</label>
                        <select name="departmentu" id="departmentu" class="form-control" data-parsley-trigger="change">
                            <option value="">Select Department</option>
                            <?php
                            $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                            $category_result = $object->get_result();
                            foreach($category_result as $category) { echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>'; }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Major Discipline/Program</label>
                        <select name="programu" id="programu" class="form-control" data-parsley-trigger="change">
                            <option value="">Select Major Discipline or Program</option>
                            <?php
                            $object->query = "SELECT * FROM tbl_majordiscipline";
                            $program_result = $object->get_result();
                            foreach($program_result as $program) { echo '<option value="'.$program["major"].'">'.$program["major"].'</option>'; }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Academic Rank</label>
                        <select name="academic_ranku" id="academic_ranku" class="form-control" data-parsley-trigger="change">
                            <option value="">Select Academic Rank</option>
                            <option value="Instructor I">Instructor I</option>
                            <option value="Instructor II">Instructor II</option>
                            <option value="Instructor III">Instructor III</option>
                            <option value="Assistant Professor I">Assistant Professor I</option>
                            <option value="Assistant Professor II">Assistant Professor II</option>
                            <option value="Assistant Professor III">Assistant Professor III</option>
                            <option value="Assistant Professor IV">Assistant Professor IV</option>
                            <option value="Associate Professor I">Associate Professor I</option>
                            <option value="Associate Professor II">Associate Professor II</option>
                            <option value="Associate Professor III">Associate Professor III</option>
                            <option value="Associate Professor IV">Associate Professor IV</option>
                            <option value="Associate Professor V">Associate Professor V</option>
                            <option value="Professor I">Professor I</option>
                            <option value="Professor II">Professor II</option>
                            <option value="Professor III">Professor III</option>
                            <option value="Professor IV">Professor IV</option>
                            <option value="Professor V">Professor V</option>
                            <option value="Professor VI">Professor VI</option>
                            <option value="College Professor">College Professor</option>
                            <option value="University Professor">University Professor</option>
                        </select>
                    </div>
                </div>
                
                <h6 class="font-weight-bold text-gray-700 mb-3 mt-4 border-bottom pb-2"><i class="fas fa-graduation-cap mr-2 text-secondary"></i>Educational Background</h6>
                <div class="form-group row">
                    <div class="col-md-4"><label>Bachelor's Degree</label><input type="text" name="bachelor_degreeu" id="bachelor_degreeu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Bachelor's Institution</label><input type="text" name="bachelor_institutionu" id="bachelor_institutionu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Bachelor's Year Graduated</label><input type="text" name="bachelor_YearGraduatedu" id="bachelor_YearGraduatedu" class="form-control" maxlength="4" /></div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4"><label>Master's Degree</label><input type="text" name="masterDegreeu" id="masterDegreeu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Master's Institution</label><input type="text" name="masterInstitutionu" id="masterInstitutionu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Master's Year Graduated</label><input type="text" name="masterYearGraduatedu" id="masterYearGraduatedu" class="form-control" maxlength="4" /></div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4"><label>Doctorate Degree</label><input type="text" name="doctorateDegreeu" id="doctorateDegreeu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Doctorate Institution</label><input type="text" name="doctorateInstitutionu" id="doctorateInstitutionu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Doctorate Year Graduated</label><input type="text" name="doctorateYearGraduateu" id="doctorateYearGraduateu" class="form-control" maxlength="4" /></div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4"><label>Post Degree</label><input type="text" name="postDegreeu" id="postDegreeu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Post Institution</label><input type="text" name="postInstitutionu" id="postInstitutionu" class="form-control" maxlength="100" /></div>
                    <div class="col-md-4"><label>Post Year Graduated</label><input type="text" name="postYearGraduateu" id="postYearGraduateu" class="form-control" maxlength="4" /></div>
                </div>
                
                <div class="modal-footer mt-4">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" id="submit_button_rd" class="btn btn-danger pink px-4">Update Profile</button>
                </div>
            </form>

            <div class="d-none">
                <table id="researcherconducted_table"></table>
                <table id="publication_table"></table>
                <table id="intellectualprop_table"></table>
                <table id="paper_presentation_table"></table>
                <table id="trainings_attended_table"></table>
                <table id="extension_project_table"></table>
                <table id="ext_project_table"></table>
            </div>

        </div>
    </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

<script src="<?php echo $object->base_url; ?>js/app.js"></script>
<script src="<?php echo $object->base_url; ?>js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. MODAL OVERLAP FIX
    if (typeof $ !== 'undefined') {
        $('.modal').appendTo('body');
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1040 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function() {
                $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });
        
        // Listen to changes on the newly added Inner Tabs to swap out the FAB action
        $('#epcInnerTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href"); // newly activated inner tab
            if (target === '#inner-epc') {
                document.getElementById('dynamic-fab-btn').setAttribute('data-target-id', 'add_extension_project');
            } else if (target === '#inner-ext') {
                document.getElementById('dynamic-fab-btn').setAttribute('data-target-id', 'add_extension');
            }
        });
    }

    // 2. ISOLATED VANILLA JS TAB & FAB CONTROLLER
    const tabBtns = document.querySelectorAll('.custom-tab-btn');
    const tabPanes = document.querySelectorAll('.custom-tab-pane');
    const fabContainer = document.getElementById('fab-container');
    const backBtn = document.getElementById('back_to_directory');
    const dynamicFabBtn = document.getElementById('dynamic-fab-btn');

    // Mapping view_researcher tabs to researcher.php master button IDs
    const backMapping = {
        'tab-personal-info': 'btn_view_researchers',
        'tab-education': 'btn_view_conducted',
        'tab-degree': 'btn_view_publications',
        'tab-ip': 'btn_view_ip',
        'tab-pp': 'btn_view_pp',
        'tab-tra': 'btn_view_tra',
        'tab-epc': 'btn_view_epc' // Extension is now covered under EPC
    };

    // Mapping tab IDs to the "Add" button IDs expected by your jQuery scripts
    const fabMapping = {
        'tab-education': 'add_researcherconducted',
        'tab-degree': 'add_publication',
        'tab-ip': 'add_intellectualprop',
        'tab-pp': 'add_paper_presentation',
        'tab-tra': 'add_training_attended'
        // epc is handled dynamically
    };

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            // Update Back Link
            const tabId = this.getAttribute('id');
            if (backBtn && backMapping[tabId]) {
                backBtn.href = "researcher.php?tab=" + backMapping[tabId];
            }

            // Remove active states from all buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            // Add active state to clicked button
            this.classList.add('active');

            // Hide all tab panes
            tabPanes.forEach(pane => {
                pane.style.display = 'none';
                pane.classList.remove('active');
            });

            // Show target tab pane
            const targetId = this.getAttribute('href');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.style.display = 'block';
                targetPane.classList.add('active');
            }

            // --- Context-Aware FAB Logic ---
            if (tabId === 'tab-personal-info') {
                fabContainer.style.display = 'none';
            } else if (tabId === 'tab-epc') {
                fabContainer.style.display = 'block';
                // Find out which inner tab is currently visible
                const activeInnerTab = document.querySelector('#epcInnerTabs .nav-link.active');
                if(activeInnerTab && activeInnerTab.getAttribute('href') === '#inner-ext') {
                    dynamicFabBtn.setAttribute('data-target-id', 'add_extension');
                } else {
                    dynamicFabBtn.setAttribute('data-target-id', 'add_extension_project');
                }
            } else {
                fabContainer.style.display = 'block';
                dynamicFabBtn.setAttribute('data-target-id', fabMapping[tabId]);
            }
        });
    });

    // --- Proxy Click Event for the FAB ---
    dynamicFabBtn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target-id');
        if (targetId) {
            const hiddenProxyButton = document.getElementById(targetId);
            if (hiddenProxyButton) {
                hiddenProxyButton.click(); 
            }
        }
    });

    // 3. Prevent Delete Button Clicks from Opening Edit Modals
    $('.isolate-click').on('click', function(e) {
        e.stopPropagation(); 
        
        var btn = $(this).find('button');
        if (btn.length > 0) {
            var simulatedEvent = $.Event('click');
            simulatedEvent.target = btn[0];
            $(document).trigger(simulatedEvent);
        }
    });
});
</script>

<script src="scripts/research_conducted.js"></script>
<script src="scripts/profile.js"></script>
<script src="scripts/publication.js"></script>
<script src="scripts/intellectual_prop.js"></script>
<script src="scripts/paper_presentation.js"></script>
<script src="scripts/training.js"></script>
<script src="scripts/extension_project.js"></script>
<script src="scripts/extension.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Check the URL for a 'tab' parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        if (activeTab === 'ext') {
            // If URL requested the old 'ext' tab, redirect the click to the new parent EPC tab
            let mainLink = document.querySelector('.custom-tab-btn[href="#epc"]');
            if (mainLink) mainLink.click();
            
            // Then manually select the inner Extension tab via Bootstrap
            setTimeout(function() {
                if(typeof $ !== 'undefined') {
                    $('#inner-ext-tab').tab('show');
                }
                mainLink.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);
        } else {
            let tabLink = document.querySelector('.custom-tab-btn[href="#' + activeTab + '"]');
            if (tabLink) {
                tabLink.click();
                setTimeout(function() {
                    tabLink.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
        }
    }
});
</script>