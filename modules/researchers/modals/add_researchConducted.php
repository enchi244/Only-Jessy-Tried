<div id="researchconductedModal" class="modal fade" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1250px;">
        <form method="post" id="researchconducted_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header pb-2">
                    <h4 class="modal-title d-flex align-items-center" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-flask"></i>
                        </div>
                        Add Research Conducted
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body px-4" style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">
                    <span id="form_message"></span>

                    <div class="row">
                        <div class="col-lg-6 pr-lg-4">
                            <div class="form-group mb-4">
                                <label for="title"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="Enter the complete title of the research" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="lead_researcher_id"><i class="fas fa-user-tie mr-2 text-primary"></i>Lead Researcher</label>
                                <select name="lead_researcher_id" id="lead_researcher_id" class="form-control select2-single" required style="width: 100%;">
                                    <option value="">Select Lead Researcher</option>
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers = $object->get_result();
                                    foreach($researchers as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="collaborators"><i class="fas fa-users mr-2 text-primary"></i>Co-Researchers</label>
                                <select name="collaborators[]" id="collaborators" class="form-control select2-multi" multiple style="width: 100%;">
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers_collab = $object->get_result();
                                    foreach($researchers_collab as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="research_agenda_cluster"><i class="fas fa-layer-group mr-2 text-primary"></i>Agenda Cluster</label>
                                <select name="research_agenda_cluster" id="research_agenda_cluster" class="form-control select2-single" required style="width: 100%;">
                                    <option value="">Select Agenda Cluster...</option>
                                    <?php
                                    $object->query = "SELECT agenda FROM tbl_rde_agenda ORDER BY agenda ASC";
                                    $agenda_result = $object->get_result();
                                    // Removed the row_count check to prevent premature loop blocking
                                    foreach($agenda_result as $agenda) {
                                        echo '<option value="'.htmlspecialchars($agenda["agenda"]).'">'.htmlspecialchars($agenda["agenda"]).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label><i class="fas fa-globe mr-2 text-primary"></i>Select SDG</label>
                                <select name="sdgs[]" id="sdgs" class="form-control select2-multi" multiple required style="width: 100%;">
                                    <?php
                                    $object->query = "SELECT * FROM tbl_sdgs ORDER BY goal_name ASC";
                                    $sdg_result = $object->get_result();
                                    if($object->row_count() > 0) {
                                        foreach($sdg_result as $sdg) {
                                            echo '<option value="'.htmlspecialchars($sdg["goal_name"]).'">'.htmlspecialchars($sdg["goal_name"]).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6 border-left pl-lg-4">
                            <div class="row">
                                <div class="col-md-6 form-group mb-4">
                                    <label for="started_date"><i class="far fa-calendar-plus mr-2 text-primary"></i>Start Date</label>
                                    <input type="date" name="started_date" id="started_date" class="form-control" required />
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label for="completed_date"><i class="far fa-calendar-check mr-2 text-primary"></i>Completed Date</label>
                                    <input type="date" name="completed_date" id="completed_date" class="form-control" required />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-4">
                                    <label for="funding_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                                    <input type="text" name="funding_source" id="funding_source" class="form-control" placeholder="E.g. CHED, DOST" required />
                                </div>
                                
                                <div class="col-md-6 form-group mb-4">
                                    <label for="approved_budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Approved Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span>
                                        </div>
                                        <input type="number" name="approved_budget" id="approved_budget" class="form-control border-left-0 pl-1" step="0.01" placeholder="0.00" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="stat"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                                <select name="stat" id="stat" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="On Going">On Going</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <div class="dynamic-files-section mt-4" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px dashed #d1d3e2;">
                                <style>
                                    .dynamic-files-section .existing-files-container .d-flex > div:first-child,
                                    .dynamic-files-section .new-files-container .d-flex > div:first-child {
                                        min-width: 0;
                                        flex: 1;
                                        padding-right: 10px;
                                    }
                                    .dynamic-files-section a, .dynamic-files-section .text-gray-800 {
                                        word-break: break-word;
                                    }
                                </style>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                                    </div>
                                    
                                    <input type="file" class="hidden-multi-file" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" data-categories="SO, MOA, Terminal Report, PSE-PES, Financial Report, Other">
                                    <button type="button" class="btn btn-primary shadow-sm add-file-btn"><i class="fas fa-plus mr-1"></i> Browse Files</button>
                                </div>

                                <div class="existing-files-container mb-3"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-top-0 pt-3">
                    <input type="hidden" name="hidden_id_researchedconducted" id="hidden_id_researchedconducted" />
                    <input type="hidden" name="hiddeny" id="hiddeny" />
                    <input type="hidden" name="action_researchedconducted" id="action_researchedconducted" value="Add" />
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_researchedconducted" id="submit_button_researchedconducted" class="btn btn-danger pink px-4 font-weight-bold shadow-sm" value="Save Research" />
                </div>
            </div>
        </form>
    </div>
</div>