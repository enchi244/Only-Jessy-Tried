<div id="researchconductedModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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

                    <div class="form-group mb-4">
                        <label for="title"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            class="form-control" 
                            placeholder="Enter the complete title of the research" 
                            required 
                        />
                    </div>

                    <div class="form-group mb-4">
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
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> The primary author or project leader.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="collaborators"><i class="fas fa-users mr-2 text-primary"></i>Co-Researchers / Collaborators</label>
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

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="research_agenda_cluster"><i class="fas fa-layer-group mr-2 text-primary"></i>Agenda Cluster</label>
                            <input 
                                type="text" 
                                name="research_agenda_cluster" 
                                id="research_agenda_cluster" 
                                class="form-control" 
                                placeholder="E.g. Climate Change" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
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

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="started_date"><i class="far fa-calendar-plus mr-2 text-primary"></i>Start Date</label>
                            <input 
                                type="date" 
                                name="started_date" 
                                id="started_date" 
                                class="form-control" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="completed_date"><i class="far fa-calendar-check mr-2 text-primary"></i>Completed Date</label>
                            <input 
                                type="date" 
                                name="completed_date" 
                                id="completed_date" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="funding_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                            <input 
                                type="text" 
                                name="funding_source" 
                                id="funding_source" 
                                class="form-control" 
                                placeholder="E.g. CHED, DOST" 
                                required 
                            />
                        </div>
                        
                        <div class="col-md-6 form-group mb-4">
                            <label for="approved_budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Approved Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span>
                                </div>
                                <input 
                                    type="number" 
                                    name="approved_budget" 
                                    id="approved_budget" 
                                    class="form-control border-left-0 pl-1" 
                                    step="0.01" 
                                    placeholder="0.00" 
                                    required 
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-6 form-group mb-0">
                            <label for="stat"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                            <select name="stat" id="stat" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="On Going">On Going</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-0">
                            <label for="has_files"><i class="fas fa-paperclip mr-2 text-primary"></i>File Attachments</label>
                            <select name="has_files" id="has_files" class="form-control" required>
                                <option value="None">None</option>
                                <option value="With">With Files</option>
                            </select>
                        </div>
                    </div>

                    <div id="dynamic_files_section" style="display: none; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="add_file_btn"><i class="fas fa-plus mr-1"></i> Add File</button>
                        </div>

                        <div id="existing_files_container" class="mb-3"></div>
                        <div id="new_files_container"></div>
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