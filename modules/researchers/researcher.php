<?php
include('../../core/rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}
if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
}

include('../../includes/header.php');
?>

<link rel="stylesheet" type="text/css" href="<?php echo $object->base_url; ?>css/select2.min.css">
<style>
    /* Sleek Table Header */
    #researcher_table thead th {
        background-color: #f8f9fc;
        color: #5a5c69;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e3e6f0;
        border-top: none;
    }
    /* Soften Table Body */
    #researcher_table td {
        vertical-align: middle;
        color: #444;
        border-right: none;
        border-left: none;
    }
    
    /* Make table rows clickable */
    #researcher_table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    #researcher_table tbody tr:hover {
        background-color: #f1f3f9 !important;
    }

    /* FAB Container - Handles stacking automatically */
    .fab-container {
        position: fixed;
        bottom: 40px;
        right: 40px;
        z-index: 1000;
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        gap: 15px;
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
        position: relative;
        z-index: 3;
    }
    .fab-sub-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        pointer-events: none;
        z-index: 1;
    }
    
    /* Hover Effects for FAB */
    .fab-container:hover .fab-sub-btn {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .fab-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 25px rgba(242, 62, 93, 0.5);
    }
    
    /* Staggered animation for multiple sub-buttons */
    .fab-container:hover .fab-sub-btn:nth-child(2) { transition-delay: 0.05s; }
    .fab-container:hover .fab-sub-btn:nth-child(3) { transition-delay: 0.1s; }

    /* Master View Toggles */
    .master-toggles .btn {
        font-weight: 600;
        letter-spacing: 0.03em;
        margin-bottom: 5px;
    }
</style>

<div style="display: none;">
    <button type="button" name="add_researcher" id="add_researcher"></button>
    <button type="button" id="add_researcherconducted"></button>
</div>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">University Research Directory</h1>
        <p class="text-muted mb-0 mt-1">Manage, update, and track university research personnel and outputs.</p>
    </div>
</div>

<div class="mb-4">
    <div class="btn-group shadow-sm master-toggles flex-wrap" role="group" aria-label="Master Table Toggles">
        <button type="button" class="btn btn-danger pink active" id="btn_view_researchers"><i class="fas fa-users mr-1"></i> Researchers Profile</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_conducted"><i class="fas fa-flask mr-1"></i> Research Conducted</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_publications"><i class="fas fa-book mr-1"></i> Publications</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_ip"><i class="fas fa-lightbulb mr-1"></i> Intellectual Property</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_pp"><i class="fas fa-file-alt mr-1"></i> Paper Presentation</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_tra"><i class="fas fa-chalkboard-teacher mr-1"></i> Trainings</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_epc"><i class="fas fa-project-diagram mr-1"></i> Ext. Projects</button>
        <button type="button" class="btn btn-outline-danger" id="btn_view_ext"><i class="fas fa-hands-helping mr-1"></i> Extensions</button>
    </div>
</div>

<span id="message"></span>
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header py-3 bg-white border-bottom-0 pt-4 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-gray-700" id="master_table_title"><i class="fas fa-list-ul mr-2"></i>Personnel Roster</h6>
        <button class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm" id="toggleIDColumn">
            <i class="fas fa-eye-slash mr-1"></i> <span>Hidden ID</span>
        </button>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table w-100" id="researcher_table" cellspacing="0">
                <thead>
                    <tr>
                        <th width="15%">ID</th>
                        <th width="25%">Name</th>
                        <th width="20%">Department</th>
                        <th width="20%">Program</th>
                        <th width="10%">Joined</th>
                        <th width="10%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="fab-container" id="fab_container">
    <button class="btn btn-danger pink fab-btn text-white" id="fab_add_researcher" title="Add New Researcher Profile">
        <i class="fas fa-plus"></i>
    </button>
    
    <button class="btn btn-secondary fab-sub-btn text-white" id="fab_link_researcher" title="Manage Collaborators on Existing Project" data-toggle="modal" data-target="#linkResearcherModal">
        <i class="fas fa-link"></i>
    </button>

    <button class="btn btn-primary fab-sub-btn text-white" style="background-color: #4e73df; border-color: #4e73df;" id="fab_create_research_project" title="Add New Research Project">
        <i class="fas fa-flask"></i>
    </button>
</div>

<?php include('modals/add_researchConducted.php'); ?>
<?php include('modals/add_publication.php'); ?>
<?php include('modals/add_intellectualProperty.php'); ?>
<?php include('modals/add_paperPresentation.php'); ?>
<?php include('modals/add_trainingsAttended.php'); ?>
<?php include('modals/add_extensionProject.php'); ?>
<?php include('modals/add_extension.php'); ?>
<?php include('modals/add_researcher.php'); ?>

<div id="linkResearcherModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="link_researcher_form" class="w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title d-flex align-items-center" id="link_modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-link"></i>
                        </div>
                        Manage Collaborators
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="link_form_message"></div>
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold" for="existing_research_id"><i class="fas fa-flask mr-2 text-primary"></i>Select Existing Research Project <span class="text-danger">*</span></label>
                        <select name="existing_research_id" id="existing_research_id" class="form-control select2-single" required style="width: 100%;">
                            <option value="">Select a Project...</option>
                            <?php
                            $object->query = "SELECT id, title FROM tbl_researchconducted GROUP BY title ORDER BY title ASC";
                            $projects = $object->get_result();
                            foreach($projects as $p) { echo '<option value="'.$p["id"].'">'.htmlspecialchars($p["title"]).'</option>'; }
                            ?>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Selecting a project will auto-fill its current researchers.</small>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold" for="linked_researchers"><i class="fas fa-users mr-2 text-primary"></i>Select Researchers to Link <span class="text-danger">*</span></label>
                        <select name="linked_researchers[]" id="linked_researchers" class="form-control select2-multi" multiple="multiple" required style="width: 100%;">
                            <?php
                            $object->query = "SELECT id, researcherID, firstName, familyName FROM tbl_researchdata WHERE status = 1 ORDER BY familyName ASC";
                            $researchers = $object->get_result();
                            foreach($researchers as $r) { echo '<option value="'.$r["id"].'">'.htmlspecialchars($r["familyName"].', '.$r["firstName"].' ('.$r["researcherID"].')').'</option>'; }
                            ?>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> You can add or remove researchers from the project here.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <input type="hidden" name="action_link" id="action_link" value="link_multiple" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="submit_link_button" class="btn btn-danger pink px-4">Save Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="researcherModala" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl"> 
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" name="modal_title" id="modal_title">Update Data</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <span id="form_message"></span>
            
            <ul class="nav nav-tabs" id="researcherTab" role="tablist">
                <li class="nav-item" id="navID"><a class="nav-link active" id="personal-info-tab" data-toggle="tab" href="#personal-info" role="tab" aria-controls="personal-info" aria-selected="true">Researchers' Profile</a></li>
                <li class="nav-item"><a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">Research Conducted</a></li>
                <li class="nav-item"><a class="nav-link" id="degree-tab" data-toggle="tab" href="#degree" role="tab" aria-controls="degree" aria-selected="false">Publication</a></li>
                <li class="nav-item"><a class="nav-link" id="ip-tab" data-toggle="tab" href="#ip" role="tab" aria-controls="ip" aria-selected="false">Intelectual Property</a></li>
                <li class="nav-item"><a class="nav-link" id="pp-tab" data-toggle="tab" href="#pp" role="tab" aria-controls="pp" aria-selected="false">Paper Presentation</a></li>
                <li class="nav-item"><a class="nav-link" id="tra-tab" data-toggle="tab" href="#tra" role="tab" aria-controls="tra" aria-selected="false">Trainings Attended</a></li>
                <li class="nav-item"><a class="nav-link" id="epc-tab" data-toggle="tab" href="#epc" role="tab" aria-controls="epc" aria-selected="false">Extension Project Conducted</a></li>
                <li class="nav-item"><a class="nav-link" id="ext-tab" data-toggle="tab" href="#ext" role="tab" aria-controls="ext" aria-selected="false">Extension</a></li>
            </ul>

            <div class="tab-content" id="researcherTabContent">
                
                <div class="tab-pane fade show active" id="personal-info" role="tabpanel" aria-labelledby="personal-info-tab">
                    <form method="post" id="researcherModala_form">     
                        <div class="form-group"><label>Researcher ID</label><input type="text" name="researcherIDu" id="researcherIDu" class="form-control" required maxlength="50" /></div>
                        <div class="form-group row">
                            <div class="col-md-3"><label>Family Name</label><input type="text" name="familyNameu" id="familyNameu" class="form-control" required maxlength="100" /></div>
                            <div class="col-md-3"><label>First Name</label><input type="text" name="firstNameu" id="firstNameu" class="form-control" required maxlength="100" /></div>
                            <div class="col-md-3"><label>Middle Name</label><input type="text" name="middleNameu" id="middleNameu" class="form-control" maxlength="100" /></div>
                            <div class="col-md-3"><label>Suffix</label><input type="text" name="Suffixu" id="Suffixu" class="form-control" maxlength="10" /></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label>Select Major Discipline or Program</label>
                                <select name="programu" id="programu" class="form-control" data-parsley-trigger="change">
                                    <option value="">Select Major Discipline or Program</option>
                                    <?php
                                    $object->query = "SELECT * FROM tbl_majordiscipline";
                                    $program_result = $object->get_result();
                                    foreach($program_result as $program) { echo '<option value="'.$program["major"].'">'.$program["major"].'</option>'; }
                                    ?>
                                </select>
                            </div>
                        </div>
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
                        <div class="modal-footer">
                            <input type="hidden" name="hidden_id_rd" id="hidden_id_rd" />
                            <button type="button" id="submit_button_rd" class="btn btn-danger pink">Update</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>                                
                </div>

                <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                    <h1 class="h3 mb-4 text-gray-800">Research Conducted Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Research Conducted List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_researcherconducted" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Research Conducted</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="researcherconducted_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Research Agenda Cluster</th><th>SDG</th><th>Start Date</th><th>Completed Date</th><th>Funding Source</th><th>Approved Budget</th><th>Status</th><th>Terminal Report</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="degree" role="tabpanel" aria-labelledby="degree-tab">
                    <h1 class="h3 mb-4 text-gray-800">Publications Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Publications List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_publication" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Publication</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="publication_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Start</th><th>End</th><th>Journal</th><th>Volume/Issue</th><th>ISSN/ISBN</th><th>Indexing</th><th>Publication Date</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ip" role="tabpanel" aria-labelledby="ip-tab">
                    <h1 class="h3 mb-4 text-gray-800">Intellectual Property Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Intellectual Property List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_intellectualprop" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Intellectual Property</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="intellectualprop_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Co-authors</th><th>Type of IP</th><th>Date Applied</th><th>Date Granted</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pp" role="tabpanel" aria-labelledby="pp-tab">
                    <h1 class="h3 mb-4 text-gray-800">Paper Presentation Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Paper Presentation List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_paper_presentation" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Paper Presentation</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="paper_presentation_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Conference Title</th><th>Conference Venue</th><th>Conference Organizer</th><th>Date of Presentation</th><th>Type of Paper</th><th>Discipline</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>                

                <div class="tab-pane fade" id="tra" role="tabpanel" aria-labelledby="tra-tab">
                    <h1 class="h3 mb-4 text-gray-800">Trainings Attended Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Trainings Attended List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_training_attended" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Training Attended</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="trainings_attended_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Type</th><th>Venue</th><th>Date of Training</th><th>Level</th><th>Type of Learning Development</th><th>Sponsor/Organizer</th><th>Total Number of Hours</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>  

                <div class="tab-pane fade" id="epc" role="tabpanel" aria-labelledby="epc-tab">
                    <h1 class="h3 mb-4 text-gray-800">Extension Project Conducted</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Extension Project Conducted List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_extension_project" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Extension Project Conducted</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="extension_project_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Start Date</th><th>Completion Date</th><th>Funding Source</th><th>Approved Budget</th><th>Target Beneficiaries/Communities</th><th>Partners</th><th>Status</th><th>Terminal Report</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ext" role="tabpanel" aria-labelledby="ext-tab">
                    <h1 class="h3 mb-4 text-gray-800">Extension Management</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col"><h6 class="m-0 font-weight-bold text-primary">Extension List</h6></div>
                                <div class="col" align="right"><button type="button" id="add_extension" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Extension</i></button></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="ext_project_table" width="100%" cellspacing="0">
                                    <thead><tr><th>Title</th><th>Project Leader</th><th>Assistant Coordinators</th><th>Period of Implementation</th><th>Budget</th><th>Funding Source</th><th>Target Beneficiaries</th><th>Partners</th><th>Status</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

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
    // Initialize Select2 for the link modal
    $('.select2-single').select2({ theme: "classic", dropdownParent: $('#linkResearcherModal') });
    $('.select2-multi').select2({ theme: "classic", placeholder: " Search and select researchers...", dropdownParent: $('#linkResearcherModal') });
    
    // 1. Connect Main FAB to Add Researcher
    document.getElementById('fab_add_researcher').addEventListener('click', function() {
        document.getElementById('add_researcher').click();
    });

    // 2. Connect NEW FAB Sub-Button to Add Research Project Modal
    document.getElementById('fab_create_research_project').addEventListener('click', function() {
        // Clear any leftover profile ID so it doesn't auto-assign to the wrong person
        $('#researcherModala').data('id', ''); 
        // Trigger the shared script button to open the Add Research Conducted modal
        document.getElementById('add_researcherconducted').click();
    });

    // 1.5. Collaborators Modal Logic
    $(document).on('click', '.view_collaborators', function(e) {
        e.stopPropagation(); // Prevent triggering the row click
        var id = $(this).attr('data-id'); 
        
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "actions/researchconducted_action.php",
            method: "POST",
            data: { action_researchedconducted: 'fetch_collaborators', id: id },
            dataType: "json",
            success: function(data) {
                var html = '<div class="row mt-3">';
                if(data.length > 0) {
                    data.forEach(function(collab) {
                        var dept = collab.department ? collab.department : 'N/A';
                        html += `
                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm border-left-pink h-100" style="cursor:pointer; transition: 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'" onclick="window.location.href='view_researcher.php?id=${collab.id}'">
                                <div class="card-body p-3 text-left">
                                    <div class="font-weight-bold text-gray-800" style="font-size: 1rem;">${collab.familyName}, ${collab.firstName}</div>
                                    <div class="text-xs text-muted mt-1"><i class="fas fa-building mr-1"></i> ${dept}</div>
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    html += '<div class="col-12 text-muted">No collaborators found.</div>';
                }
                html += '</div>';

                Swal.fire({
                    title: '<i class="fas fa-users text-primary mr-2"></i> Collaborators',
                    html: html,
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '600px'
                });
            },
            error: function() {
                Swal.fire('Error', 'Could not fetch collaborators', 'error');
            }
        });
    });

    // 2. Delegate Row Click to open Profile/Edit
    $(document).on('click', '#researcher_table tbody tr', function(e) {
        if ($(e.target).closest('button').length || $(e.target).closest('a').length) {
            return; 
        }
        let viewBtn = $(this).find('.view_button'); 
        let editBtn = $(this).find('.edit_button'); 
        let editBtna = $(this).find('.edit_buttona'); 
        let linkBtn = $(this).find('a.btn'); 

        if (viewBtn.length) { viewBtn[0].click(); } 
        else if (editBtn.length) { editBtn[0].click(); } 
        else if (editBtna.length) { editBtna[0].click(); } 
        else if (linkBtn.length) { linkBtn[0].click(); }
    });

    // 3. DataTable ID Column Toggle Logic
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('#researcher_table')) {
            var toggleBtn = $('#toggleIDColumn');
            var icon = toggleBtn.find('i');
            var text = toggleBtn.find('span');

            // Toggle logic
            toggleBtn.on('click', function(e) {
                e.preventDefault();
                var table = $('#researcher_table').DataTable();
                var idColumn = table.column(0);
                idColumn.visible(!idColumn.visible());
                
                if (idColumn.visible()) {
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $(this).removeClass('btn-outline-secondary').addClass('btn-secondary text-white');
                    text.text("Showing ID");
                } else {
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $(this).removeClass('btn-secondary text-white').addClass('btn-outline-secondary');
                    text.text("Hidden ID");
                }
            });
        }
    }, 500); 
});
</script>

<script src="scripts/profile.js"></script>
<script src="scripts/research_conducted.js"></script>
<script src="scripts/publication.js"></script>
<script src="scripts/intellectual_prop.js"></script>
<script src="scripts/paper_presentation.js"></script>
<script src="scripts/training.js"></script>
<script src="scripts/extension_project.js"></script>
<script src="scripts/extension.js"></script>

<script>
$(document).ready(function() {
    
$('#existing_research_id').on('change', function() {
        var research_id = $(this).val();
        
        console.log("1. Selected Project ID:", research_id); // DEBUG LOG
        
        if (research_id) {
            $.ajax({
                url: "actions/researchconducted_action.php",
                method: "POST",
                data: { action_researchedconducted: 'fetch_collaborators', id: research_id },
                dataType: "json",
                success: function(data) {
                    console.log("2. Data from Server:", data); // DEBUG LOG
                    
                    if (data && data.length > 0) {
                        var assigned_ids = data.map(function(collab) { return collab.id.toString(); });
                        console.log("3. Array given to Dropdown:", assigned_ids); // DEBUG LOG
                        
                        $('#linked_researchers').val(assigned_ids).trigger('change');
                    } else {
                        console.log("3. Array given to Dropdown: NONE"); // DEBUG LOG
                        $('#linked_researchers').val(null).trigger('change');
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                }
            });
        } else {
            $('#linked_researchers').val(null).trigger('change');
        }
    });

    // Reset fields when the modal opens/closes to clear leftover ghost data
    $('#linkResearcherModal').on('hidden.bs.modal', function () {
        $('#link_researcher_form')[0].reset();
        $('.select2-single, .select2-multi').val(null).trigger('change');
    });

    // AJAX form submission for Linking/Managing Researchers
    $('#link_researcher_form').on('submit', function(event){
        event.preventDefault();
        
        $('#submit_link_button').attr('disabled', 'disabled');
        $('#submit_link_button').html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: "actions/researcher_action.php",
            method: "POST",
            data: $(this).serialize(),
            success:function(data)
            {
                // Reset UI
                $('#submit_link_button').attr('disabled', false);
                $('#submit_link_button').html('Save Data');
                $('#linkResearcherModal').modal('hide');
                $('#link_researcher_form')[0].reset();
                $('.select2-single, .select2-multi').val(null).trigger('change');

                Swal.fire({
                    icon: 'success',
                    title: 'Collaborators Updated',
                    text: 'The research project has been successfully synced with the selected profiles.',
                    confirmButtonColor: '#f23e5d'
                });

                if (typeof dataTable !== 'undefined') {
                    dataTable.ajax.reload();
                } else {
                    setTimeout(function(){ location.reload(); }, 1500);
                }
            },
            error: function() {
                Swal.fire('Error', 'A server error occurred.', 'error');
                $('#submit_link_button').attr('disabled', false);
                $('#submit_link_button').html('Save Data');
            }
        });
    });
});
</script>