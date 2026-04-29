<div id="researchconductedModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <form method="post" id="researchconducted_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        Add Research Conducted
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="row">
                        <!-- Left Column: Project Identity & Team -->
                        <div class="col-lg-7 pr-lg-4">
                            <div class="form-group mb-3">
                                <label for="title"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="Enter the complete title of the research" required />
                            </div>
                            <div class="form-group mb-3">
                                <label for="cover_photo"><i class="fas fa-image mr-2 text-primary"></i>Cover Photo (Optional)</label>
                                <input type="file" name="cover_photo" id="cover_photo" class="form-control-file" accept="image/jpeg, image/png, image/jpg, image/webp" />
                                <div id="cover_photo_preview" class="mt-2 text-center" style="display:none; background: #f8f9fa; padding: 10px; border-radius: 8px;">
                                    <img src="" id="preview_img" class="img-fluid rounded shadow-sm" style="max-height: 180px; object-fit: cover;" />
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="lead_researcher_id"><i class="fas fa-user-tie mr-2 text-primary"></i>Lead Researcher</label>
                                <select name="lead_researcher_id" id="lead_researcher_id" class="form-control" required style="width: 100%;">
                                    <option value="">Select Lead Researcher</option>
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers = $object->get_result();
                                    foreach($researchers as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Primary author or project leader.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="collaborators"><i class="fas fa-users mr-2 text-primary"></i>Co-Researchers</label>
                                <select name="collaborators[]" id="collaborators" multiple class="select form-control" style="width: 100%;">
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers_collab = $object->get_result();
                                    foreach($researchers_collab as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Select additional authors here.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="research_agenda_cluster"><i class="fas fa-layer-group mr-2 text-primary"></i>Agenda Cluster</label>
                                <input type="text" name="research_agenda_cluster" id="research_agenda_cluster" class="form-control" placeholder="Enter Agenda Cluster" required />
                            </div>

                            <div class="form-group mb-3">
                                <label><i class="fas fa-globe mr-2 text-primary"></i>Select SDG</label>
                                <select name="sdgs[]" id="sdgs" multiple required class="select form-control" style="width: 100%;">
                                    <option value="" disabled>Select SDG</option>
                                    <?php
                                    $object->query = "SELECT goal_name FROM tbl_sdgs ORDER BY goal_name ASC";
                                    $sdg_result = $object->get_result();
                                    foreach($sdg_result as $sdg) {
                                        echo '<option value="'.$sdg["goal_name"].'">'.$sdg["goal_name"].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column: Logistics, Status & Files -->
                        <div class="col-lg-5 border-left pl-lg-4">
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="started_date"><i class="far fa-calendar-plus mr-2 text-primary"></i>Start Date</label>
                                    <input type="date" name="started_date" id="started_date" class="form-control" required />
                                </div>

                                <div class="col-md-6 form-group mb-3">
                                    <label for="completed_date"><i class="far fa-calendar-check mr-2 text-primary"></i>Completed Date</label>
                                    <input type="date" name="completed_date" id="completed_date" class="form-control" required />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="funding_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                                    <input type="text" name="funding_source" id="funding_source" class="form-control" placeholder="E.g. CHED, DOST" required />
                                </div>
                                
                                <div class="col-md-6 form-group mb-3">
                                    <label for="approved_budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Approved Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span>
                                        </div>
                                        <input type="number" name="approved_budget" id="approved_budget" class="form-control border-left-0 pl-1" step="0.01" placeholder="0.00" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3 border-bottom pb-3">
                                <label for="stat"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                                <select name="stat" id="stat" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="On Going">On Going</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <div class="dynamic-files-section" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                                <style>
                                    /* Fix for long file names causing overflow */
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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                                    <input type="file" class="hidden-multi-file" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" data-categories="SO, MOA, Terminal Report, PSE-PES, Financial Report, Other">
                                    <button type="button" class="btn btn-sm btn-primary add-file-btn"><i class="fas fa-plus mr-1"></i> Browse</button>
                                </div>

                                <div class="existing-files-container mb-3"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id_researchedconducted" id="hidden_id_researchedconducted" />
                    <input type="hidden" name="hiddeny" id="hiddeny" />
                    <input type="hidden" name="action_researchedconducted" id="action_researchedconducted" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_researchedconducted" id="submit_button_researchedconducted" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>