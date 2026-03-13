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

// --- FETCH ALL ASSOCIATED DATA ---

// 1. Fetch Research Conducted
$object->query = "SELECT * FROM tbl_researchconducted WHERE researcherID = '$researcher_id' ORDER BY started_date DESC";
$research_conducted = $object->get_result();

// 2. Fetch Publications
$object->query = "SELECT * FROM tbl_publication WHERE researcherID = '$researcher_id' ORDER BY publication_date DESC";
$publications = $object->get_result();

// 3. Fetch Intellectual Property
$object->query = "SELECT * FROM tbl_itelectualprop WHERE researcherID = '$researcher_id' ORDER BY date_applied DESC";
$intellectual_props = $object->get_result();

// 4. Fetch Paper Presentations
$object->query = "SELECT * FROM tbl_paperpresentation WHERE researcherID = '$researcher_id' ORDER BY date_paper DESC";
$presentations = $object->get_result();

// 5. Fetch Trainings Attended
$object->query = "SELECT * FROM tbl_trainingsattended WHERE researcherID = '$researcher_id' ORDER BY date_train DESC";
$trainings = $object->get_result();

// 6. Fetch Extension Projects Conducted
$object->query = "SELECT * FROM tbl_extension_project_conducted WHERE researcherID = '$researcher_id' ORDER BY start_date DESC";
$ext_projects = $object->get_result();

// 7. Fetch Extensions
$object->query = "SELECT * FROM tbl_ext WHERE researcherID = '$researcher_id' ORDER BY period_implement DESC";
$extensions = $object->get_result();
// ---------------------------------

include('../../includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="<?php echo $object->base_url; ?>css/select2.min.css">
<style>
    .pink { background-color: #f23e5d; color: white; }
    .pink:hover { background-color: #e32747; color: white; }

    /* Custom Vertical Timeline */
    .timeline {
        border-left: 3px solid #eaecf4;
        padding-left: 25px;
        margin-left: 15px;
        position: relative;
    }
    .timeline-item {
        margin-bottom: 30px;
        position: relative;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -33px;
        top: 5px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: #f23e5d;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #eaecf4;
    }
    .timeline-date {
        font-size: 0.85rem;
        font-weight: bold;
        color: #4e73df;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 5px;
    }
    
    /* Hover effects for Minimalist Lists */
    .list-group-item-action:hover {
        background-color: #f8f9fc;
        transform: translateX(5px);
        transition: transform 0.2s ease-in-out;
    }
    
    /* Modern Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #800000 0%, #4a0000 100%);
        border-radius: 10px;
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #800000;
        font-weight: bold;
    }
    
    /* Sleek Vertical Nav */
    .nav-pills .nav-link {
        color: #5a5c69;
        font-weight: 600;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: all 0.2s;
    }
    .nav-pills .nav-link:hover {
        background-color: #eaecf4;
    }
    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
        background-color: #f23e5d;
        color: white;
        box-shadow: 0 4px 6px rgba(242, 62, 93, 0.2);
    }
</style>

<a href="researcher.php" class="btn btn-light btn-sm mb-3 shadow-sm font-weight-bold text-gray-700">
    <i class="fas fa-arrow-left mr-2"></i>Back to Directory
</a>

<div class="profile-header d-flex align-items-center">
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
                    <a class="nav-link active" id="tab-personal-info" data-toggle="pill" href="#personal-info" role="tab"><i class="fas fa-user mr-2"></i> Profile Overview</a>
                    <a class="nav-link" id="tab-education" data-toggle="pill" href="#education" role="tab"><i class="fas fa-microscope mr-2"></i> Research Conducted</a>
                    <a class="nav-link" id="tab-degree" data-toggle="pill" href="#degree" role="tab"><i class="fas fa-book mr-2"></i> Publications</a>
                    <a class="nav-link" id="tab-ip" data-toggle="pill" href="#ip" role="tab"><i class="fas fa-lightbulb mr-2"></i> Intellectual Property</a>
                    <a class="nav-link" id="tab-pp" data-toggle="pill" href="#pp" role="tab"><i class="fas fa-file-alt mr-2"></i> Paper Presentation</a>
                    <a class="nav-link" id="tab-tra" data-toggle="pill" href="#tra" role="tab"><i class="fas fa-chalkboard-teacher mr-2"></i> Trainings</a>
                    <a class="nav-link" id="tab-epc" data-toggle="pill" href="#epc" role="tab"><i class="fas fa-project-diagram mr-2"></i> Extension Projects</a>
                    <a class="nav-link" id="tab-ext" data-toggle="pill" href="#ext" role="tab"><i class="fas fa-hands-helping mr-2"></i> Extension</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span id="message"></span>
                <div class="tab-content" id="v-pills-tabContent">

                    <div class="tab-pane fade show active" id="personal-info" role="tabpanel">
                        <h4 class="font-weight-bold text-gray-800 mb-4 border-bottom pb-2">Academic Background</h4>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Bachelor's Degree</div>
                            <div class="col-md-8 font-weight-bold"><?php echo htmlspecialchars($researcher_data['bachelor_degree'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($researcher_data['bachelor_YearGraduated'] ?? ''); ?>)</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Master's Degree</div>
                            <div class="col-md-8 font-weight-bold"><?php echo htmlspecialchars($researcher_data['masterDegree'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($researcher_data['masterYearGraduated'] ?? ''); ?>)</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Doctorate Degree</div>
                            <div class="col-md-8 font-weight-bold"><?php echo htmlspecialchars($researcher_data['doctorateDegree'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($researcher_data['doctorateYearGraduate'] ?? ''); ?>)</div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="education" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Research Timeline</h4>
                            <button type="button" id="add_researcherconducted" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Record</button>
                        </div>
                        
                        <?php if(count($research_conducted) > 0): ?>
                            <div class="timeline mt-4">
                                <?php foreach($research_conducted as $rc): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date"><?php echo htmlspecialchars($rc['started_date']); ?> to <?php echo htmlspecialchars($rc['completed_date']); ?></div>
                                        <div class="card shadow-sm border-0 bg-light">
                                            <div class="card-body py-3">
                                                <div class="float-right">
                                                    <button class="btn btn-sm btn-link text-primary edit_button_researchconducted" data-id="<?php echo $rc['id']; ?>"><i class="fas fa-edit"></i></button>
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

                    <div class="tab-pane fade" id="degree" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Publications</h4>
                            <button type="button" id="add_publication" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Publication</button>
                        </div>

                        <?php if(count($publications) > 0): ?>
                            <?php foreach($publications as $pub): ?>
                                <div class="card shadow-sm mb-3 border-left-primary position-relative">
                                    <div class="card-body py-3">
                                        <div class="position-absolute" style="top: 15px; right: 15px;">
                                            <button class="btn btn-sm btn-light text-primary shadow-sm mr-1 edit_button_publication" data-id="<?php echo $pub['id']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-light text-danger shadow-sm delete_button_publication" data-id="<?php echo $pub['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <h5 class="font-weight-bold text-gray-800 mb-1 pr-5"><?php echo htmlspecialchars($pub['title']); ?></h5>
                                        <p class="text-muted mb-2 font-size-sm">
                                            <i class="fas fa-book-open mr-2"></i> <?php echo htmlspecialchars($pub['journal']); ?> | Vol/Issue: <?php echo htmlspecialchars($pub['vol_num_issue_num']); ?>
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge badge-success px-2 py-1 mr-2"><i class="fas fa-check-circle mr-1"></i> <?php echo htmlspecialchars($pub['indexing']); ?></span>
                                            <span class="badge badge-light text-dark px-2 py-1 mr-2"><i class="far fa-calendar-alt mr-1"></i> Published: <?php echo htmlspecialchars($pub['publication_date']); ?></span>
                                            <span class="badge badge-light text-dark px-2 py-1"><i class="fas fa-barcode mr-1"></i> ISSN: <?php echo htmlspecialchars($pub['issn_isbn']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No publications recorded yet.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="ip" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Intellectual Property</h4>
                            <button type="button" id="add_intellectualprop" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add IP</button>
                        </div>

                        <?php if(count($intellectual_props) > 0): ?>
                            <div class="row">
                                <?php foreach($intellectual_props as $ip): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card shadow-sm border-left-warning h-100">
                                            <div class="card-body py-3 position-relative">
                                                <div class="position-absolute" style="top: 10px; right: 10px;">
                                                    <button class="btn btn-sm btn-light text-primary edit_button_intellectualprop" data-id="<?php echo $ip['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger delete_button_intellectualprop" data-id="<?php echo $ip['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 pr-5"><?php echo htmlspecialchars($ip['title']); ?></h6>
                                                <p class="text-muted small mb-2"><i class="fas fa-users mr-1"></i> Co-authors: <?php echo htmlspecialchars($ip['coauth']); ?></p>
                                                <div class="mt-3">
                                                    <span class="badge badge-warning text-dark px-2 py-1 mb-1"><i class="fas fa-certificate mr-1"></i> <?php echo htmlspecialchars($ip['type']); ?></span><br>
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

                    <div class="tab-pane fade" id="pp" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Paper Presentations</h4>
                            <button type="button" id="add_paper_presentation" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Presentation</button>
                        </div>

                        <div class="card shadow-sm border-0">
                            <?php if(count($presentations) > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach($presentations as $paper): ?>
                                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                            <div>
                                                <h6 class="font-weight-bold text-gray-800 mb-1"><i class="fas fa-microphone-alt text-primary mr-2"></i> <?php echo htmlspecialchars($paper['title']); ?></h6>
                                                <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($paper['conference_title']); ?> (<?php echo htmlspecialchars($paper['conference_venue']); ?>) | <?php echo htmlspecialchars($paper['date_paper']); ?></small>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-circle btn-light text-primary edit_button_paper_presentation" data-id="<?php echo $paper['id']; ?>"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-circle btn-light text-danger delete_button_paper_presentation" data-id="<?php echo $paper['id']; ?>"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="card-body text-center py-4 text-muted">No paper presentations recorded yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tra" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Trainings & Development</h4>
                            <button type="button" id="add_training_attended" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Training</button>
                        </div>
                        
                        <?php if(count($trainings) > 0): ?>
                            <div class="timeline mt-4">
                                <?php foreach($trainings as $train): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date"><?php echo htmlspecialchars($train['date_train']); ?> (<?php echo htmlspecialchars($train['totnh']); ?> Hours)</div>
                                        <div class="card shadow-sm border-0 bg-light">
                                            <div class="card-body py-3">
                                                <div class="float-right">
                                                    <button class="btn btn-sm btn-link text-primary edit_button_training" data-id="<?php echo $train['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-link text-danger delete_button_training" data-id="<?php echo $train['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($train['title']); ?></h6>
                                                <p class="text-muted small mb-2"><i class="fas fa-building mr-1"></i> <?php echo htmlspecialchars($train['sponsor_org']); ?> | Venue: <?php echo htmlspecialchars($train['venue']); ?></p>
                                                <span class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($train['lvl']); ?> Level</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No training records found.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="epc" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Extension Projects Conducted</h4>
                            <button type="button" id="add_extension_project" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Ext. Project</button>
                        </div>

                        <?php if(count($ext_projects) > 0): ?>
                            <div class="timeline mt-4">
                                <?php foreach($ext_projects as $ep): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date"><?php echo htmlspecialchars($ep['start_date']); ?> to <?php echo htmlspecialchars($ep['completed_date']); ?></div>
                                        <div class="card shadow-sm border-0 bg-light">
                                            <div class="card-body py-3">
                                                <div class="float-right">
                                                    <button class="btn btn-sm btn-link text-primary edit_button_extension_project" data-id="<?php echo $ep['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-link text-danger delete_button_extension_project" data-id="<?php echo $ep['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <h6 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($ep['title']); ?></h6>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-handshake mr-1"></i> Partners: <?php echo htmlspecialchars($ep['partners']); ?> | 
                                                    <i class="fas fa-wallet ml-2 mr-1"></i> <?php echo htmlspecialchars($ep['funding_source']); ?> (₱<?php echo number_format((float)$ep['approved_budget'], 2); ?>)
                                                </p>
                                                <p class="text-muted small mb-2"><i class="fas fa-users mr-1"></i> Beneficiaries: <?php echo htmlspecialchars($ep['target_beneficiaries_communities']); ?></p>
                                                <span class="badge badge-success px-2 py-1"><?php echo htmlspecialchars($ep['status_exct']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center py-4 border">No extension projects found.</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="ext" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-bold text-gray-800 m-0">Extension Activities</h4>
                            <button type="button" id="add_extension" class="btn btn-danger pink btn-sm shadow-sm"><i class="fas fa-plus mr-1"></i> Add Extension</button>
                        </div>

                        <?php if(count($extensions) > 0): ?>
                            <?php foreach($extensions as $ext): ?>
                                <div class="card shadow-sm mb-3 border-left-success position-relative">
                                    <div class="card-body py-3">
                                        <div class="position-absolute" style="top: 15px; right: 15px;">
                                            <button class="btn btn-sm btn-light text-primary shadow-sm mr-1 edit_button_ext" data-id="<?php echo $ext['id']; ?>"><i class="fas fa-edit"></i></button>
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

                </div> </div> </div> </div> </div> <div id="hidden_id_rd" value="<?php echo htmlspecialchars($researcher_id); ?>"></div>
<div id="researcherModala" data-id="<?php echo htmlspecialchars($researcher_id); ?>"></div>

<?php include('../../includes/footer.php'); ?>

<script src="<?php echo $object->base_url; ?>js/app.js"></script>
<script src="<?php echo $object->base_url; ?>js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    // 1. MAGIC OVERLAPPING MODAL FIX
    $('.modal').appendTo('body');
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    // 2. BULLETPROOF TAB SWITCHER (Bypasses Bootstrap completely!)
    $('.nav-pills .nav-link').on('click', function(e) {
        e.preventDefault(); // Stop page jump
        
        // Remove 'active' color from all links, add to the one clicked
        $('.nav-pills .nav-link').removeClass('active');
        $(this).addClass('active');
        
        // Hide all tab panes, then force the target pane to show
        var targetPane = $(this).attr('href');
        $('.tab-pane').removeClass('show active');
        $(targetPane).addClass('show active');
    });
});
</script>

<script src="scripts/research_conducted.js"></script>
<script src="scripts/publication.js"></script>
<script src="scripts/intellectual_prop.js"></script>
<script src="scripts/paper_presentation.js"></script>
<script src="scripts/training.js"></script>
<script src="scripts/extension_project.js"></script>
<script src="scripts/extension.js"></script>

<script>
    window.loadResearchConductedTab = function() { location.reload(); };
    window.loadPublicationTab = function() { location.reload(); };
    window.loadIntellectualPropTab = function() { location.reload(); };
    window.loadPaperPresentationTab = function() { location.reload(); };
    window.loadTrainingsAttendedTab = function() { location.reload(); };
    window.loadExtensionProjectsTab = function() { location.reload(); };
    window.loadextprotab = function() { location.reload(); };
</script>