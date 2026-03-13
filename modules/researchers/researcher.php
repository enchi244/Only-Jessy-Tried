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
.red{ background-color: #610d0d; }
.red:hover{ background-color: #610d0d; }
.modal-xl { max-width: 90%; }
.modal { z-index: 1051 !important; }
.custom-confirm-button { background-color: #ff0000 !important; color: white !important; border: none !important; border-radius: 5px !important; } 
.swal2-confirm.btn-danger { background-color: red !important; border-color: red !important; }
.swal2-cancel.btn-secondary { background-color: gray !important; border-color: gray !important; }
</style>

<h1 class="h3 mb-4 text-gray-800">Researchers' Data</h1>

<span id="message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Researchers' List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_researcher" id="add_researcher" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Researcher</i></button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="researcher_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Researcher ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Program</th>
                        <th>User Created On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('modals/add_researchConducted.php'); ?>
<?php include('modals/add_publication.php'); ?>
<?php include('modals/add_intellectualProperty.php'); ?>
<?php include('modals/add_paperPresentation.php'); ?>
<?php include('modals/add_extensionProject.php'); ?>
<?php include('modals/add_extension.php'); ?>
<?php include('modals/add_researcher.php'); ?>

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

<script src="<?php echo $object->base_url; ?>js/app.js"></script>
<script src="<?php echo $object->base_url; ?>js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="scripts/profile.js"></script>
<script src="scripts/research_conducted.js"></script>
<script src="scripts/publications.js"></script>
<script src="scripts/intellectual_prop.js"></script>
<script src="scripts/paper_presentation.js"></script>
<script src="scripts/trainings.js"></script>
<script src="scripts/extension_project.js"></script>
<script src="scripts/extension.js"></script>