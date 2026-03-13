<div id="researcherModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl"> <!-- Custom class for increased width -->
        <form method="post" id="researcher_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message_rm"></span>

                   
                        <!-- Researcher ID -->
    <div class="form-group">
        <label>Researcher ID</label>
        <input type="text" name="researcherID" id="researcherID" class="form-control"  placeholder="Enter ID" maxlength="50" />
    </div>

    <!-- Name Section (Family, First, Middle, Suffix) -->
    <div class="form-group row">
        <div class="col-md-3">
            <label>Family Name</label>
            <input type="text" name="familyName" id="familyName" class="form-control" required placeholder="Last Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>First Name</label>
            <input type="text" name="firstName" id="firstName" class="form-control" required placeholder="First Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>Middle Name</label>
            <input type="text" name="middleName" id="middleName" class="form-control" placeholder="Middle Name" maxlength="100" />
        </div>
        <div class="col-md-3">
            <label>Suffix</label>
            <input type="text" name="Suffix" id="Suffix" class="form-control" placeholder="Jr, Sr, III" maxlength="10" />
        </div>
    </div>

    <!-- Department and Major Discipline -->
    <div class="form-group row">
        <div class="col-md-6">
            <label>Select Department</label>
            <select name="department" id="department" class="form-control" data-parsley-trigger="change">
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
        <div class="col-md-6">
            <label>Select Major Discipline or Program</label>
            <select name="program" id="program" class="form-control" data-parsley-trigger="change">
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
    </div>

    <!-- Bachelor's Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Bachelor's Degree</label>
            <input type="text" name="bachelor_degree" id="bachelor_degree" class="form-control" placeholder="Bachelor's Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Bachelor's Institution</label>
            <input type="text" name="bachelor_institution" id="bachelor_institution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Bachelor's Year Graduated</label>
            <input type="text" name="bachelor_YearGraduated" id="bachelor_YearGraduated" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Master's Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Master's Degree</label>
            <input type="text" name="masterDegree" id="masterDegree" class="form-control" placeholder="Master's Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Master's Institution</label>
            <input type="text" name="masterInstitution" id="masterInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Master's Year Graduated</label>
            <input type="text" name="masterYearGraduated" id="masterYearGraduated" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Doctorate Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Doctorate Degree</label>
            <input type="text" name="doctorateDegree" id="doctorateDegree" class="form-control" placeholder="Doctorate Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Doctorate Institution</label>
            <input type="text" name="doctorateInstitution" id="doctorateInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Doctorate Year Graduated</label>
            <input type="text" name="doctorateYearGraduate" id="doctorateYearGraduate" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>
    </div>

    <!-- Post Degree Section -->
    <div class="form-group row">
        <div class="col-md-4">
            <label>Post Degree</label>
            <input type="text" name="postDegree" id="postDegree" class="form-control" placeholder="Post Degree" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Post Institution</label>
            <input type="text" name="postInstitution" id="postInstitution" class="form-control" placeholder="Institution" maxlength="100" />
        </div>
        <div class="col-md-4">
            <label>Post Year Graduated</label>
            <input type="text" name="postYearGraduate" id="postYearGraduate" class="form-control" placeholder="Year Graduated" maxlength="4" />
        </div>

        </div> <div class="modal-footer">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <input type="submit" name="submit_button" id="submit_button" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>

