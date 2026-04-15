<div id="researcherModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="researcherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form method="post" id="researcher_form" class="w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        Add Researcher
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message_rm"></span>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="researcherID" class="m-0"><i class="fas fa-id-badge mr-2 text-primary"></i>Researcher ID</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="toggleCustomID">
                                <label class="custom-control-label text-muted font-weight-bold" style="font-size: 0.85rem; cursor: pointer;" for="toggleCustomID">Custom ID</label>
                            </div>
                        </div>
                        <input type="text" name="researcherID" id="researcherID" class="form-control bg-light" placeholder="Auto-generated ID" maxlength="50" readonly required />
                    </div>

                    <h6 class="font-weight-bold text-gray-700 mb-3 mt-4 border-bottom pb-2"><i class="fas fa-user mr-2 text-secondary"></i>Personal Information</h6>
                    <div class="form-group row mb-4">
                        <div class="col-md-3">
                            <label for="familyName">Family Name</label>
                            <input type="text" name="familyName" id="familyName" class="form-control" required placeholder="Last Name" maxlength="100" />
                        </div>
                        <div class="col-md-3">
                            <label for="firstName">First Name</label>
                            <input type="text" name="firstName" id="firstName" class="form-control" required placeholder="First Name" maxlength="100" />
                        </div>
                        <div class="col-md-3">
                            <label for="middleName">Middle Name</label>
                            <input type="text" name="middleName" id="middleName" class="form-control" placeholder="Middle Name" maxlength="100" />
                        </div>
                        <div class="col-md-3">
                            <label for="Suffix">Suffix</label>
                            <input type="text" name="Suffix" id="Suffix" class="form-control" placeholder="E.g. Jr, Sr, III" maxlength="10" />
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-gray-700 mb-3 mt-4 border-bottom pb-2"><i class="fas fa-building mr-2 text-secondary"></i>Academic Assignment</h6>
                    <div class="form-group row mb-4">
                        <div class="col-md-4">
                            <label for="department">Select Department</label>
                            <select name="department" id="department" class="form-control" required data-parsley-trigger="change">
                                <option value="">Select Department</option>
                                <?php
                                $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                                $category_result = $object->get_result();
                                foreach($category_result as $category) {
                                    echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="program">Major Discipline/Program</label>
                            <select name="program" id="program" class="form-control" required data-parsley-trigger="change">
                                <option value="">Select Major Discipline or Program</option>
                                <?php
                                $object->query = "SELECT * FROM tbl_majordiscipline";
                                $program_result = $object->get_result();
                                foreach($program_result as $program) {
                                    echo '<option value="'.$program["major"].'">'.$program["major"].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="academic_rank">Academic Rank</label>
                            <select name="academic_rank" id="academic_rank" class="form-control" required data-parsley-trigger="change">
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
                    
                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label>Bachelor's Degree</label>
                            <input type="text" name="bachelor_degree" id="bachelor_degree" class="form-control" placeholder="Degree Name" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Institution</label>
                            <input type="text" name="bachelor_institution" id="bachelor_institution" class="form-control" placeholder="University/College" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Year Graduated</label>
                            <input type="text" name="bachelor_YearGraduated" id="bachelor_YearGraduated" class="form-control" placeholder="YYYY" maxlength="4" />
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label>Master's Degree</label>
                            <input type="text" name="masterDegree" id="masterDegree" class="form-control" placeholder="Degree Name" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Institution</label>
                            <input type="text" name="masterInstitution" id="masterInstitution" class="form-control" placeholder="University/College" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Year Graduated</label>
                            <input type="text" name="masterYearGraduated" id="masterYearGraduated" class="form-control" placeholder="YYYY" maxlength="4" />
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label>Doctorate Degree</label>
                            <input type="text" name="doctorateDegree" id="doctorateDegree" class="form-control" placeholder="Degree Name" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Institution</label>
                            <input type="text" name="doctorateInstitution" id="doctorateInstitution" class="form-control" placeholder="University/College" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Year Graduated</label>
                            <input type="text" name="doctorateYearGraduate" id="doctorateYearGraduate" class="form-control" placeholder="YYYY" maxlength="4" />
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label>Post-Doctorate</label>
                            <input type="text" name="postDegree" id="postDegree" class="form-control" placeholder="Degree Name" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Institution</label>
                            <input type="text" name="postInstitution" id="postInstitution" class="form-control" placeholder="University/College" maxlength="100" />
                        </div>
                        <div class="col-md-4">
                            <label>Year Graduated</label>
                            <input type="text" name="postYearGraduate" id="postYearGraduate" class="form-control" placeholder="YYYY" maxlength="4" />
                        </div>
                    </div>

                </div> 
                
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button" id="submit_button" class="btn btn-danger pink px-4" value="Save Researcher" />
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const idInput = document.getElementById('researcherID');
    const toggleSwitch = document.getElementById('toggleCustomID');

    // Handle Custom ID Toggle
    toggleSwitch.addEventListener('change', function() {
        if (this.checked) {
            idInput.readOnly = false;
            idInput.classList.remove('bg-light');
            idInput.focus();
        } else {
            idInput.readOnly = true;
            idInput.classList.add('bg-light');
        }
    });

    // Auto-generate ID when modal opens (only if it's an "Add" action and Custom ID is OFF)
    $('#researcherModal').on('show.bs.modal', function () {
        if (!toggleSwitch.checked && $('#action').val() === 'Add') {
            const year = new Date().getFullYear();
            const randomNum = Math.floor(10000 + Math.random() * 90000); // 5 digit random number
            idInput.value = 'RES-' + year + '-' + randomNum;
        }
    });
});
</script>