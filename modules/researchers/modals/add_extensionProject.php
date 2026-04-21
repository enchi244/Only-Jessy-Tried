<div id="extensionProjectModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extensionProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="extension_project_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        Add Extension Project
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="form-group mb-4">
                        <label for="title_extp"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title_extp" 
                            id="title_extp" 
                            class="form-control" 
                            placeholder="Enter project title" 
                            required 
                        />
                    </div>

                    <div class="form-group mb-4">
                        <label for="linked_research_projects"><i class="fas fa-flask mr-2 text-primary"></i>Based on Research Projects (Optional)</label>
                        <select name="linked_research_projects[]" id="linked_research_projects" class="form-control select2-multi" multiple="multiple" style="width: 100%;">
                            <?php
                            $object->query = "
                                SELECT rc.id, rc.title, 
                                       pd.familyName AS lead_familyName, pd.firstName AS lead_firstName,
                                       (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') 
                                        FROM tbl_research_collaborators col 
                                        JOIN tbl_researchdata d ON col.researcher_id = d.id 
                                        WHERE col.research_id = rc.id) AS co_authors
                                FROM tbl_researchconducted rc
                                LEFT JOIN tbl_researchdata pd ON (pd.id = rc.lead_researcher_id OR pd.id = rc.researcherID OR pd.researcherID = rc.researcherID)
                                ORDER BY rc.title ASC
                            ";
                            $research_projects = $object->get_result();
                            foreach($research_projects as $rp) { 
                                $lead = $rp['lead_familyName'] ? htmlspecialchars($rp['lead_familyName'] . ', ' . $rp['lead_firstName']) : 'Unknown Lead';
                                $co = $rp['co_authors'] ? htmlspecialchars($rp['co_authors']) : 'None';
                                echo '<option value="'.$rp["id"].'" data-lead="'.$lead.'" data-co="'.$co.'">'.htmlspecialchars($rp["title"]).'</option>'; 
                            }
                            ?>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Select any research projects that this extension is based upon.</small>
                        <div id="project_authors_display" class="mt-2"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="start_date_extc"><i class="far fa-calendar-plus mr-2 text-primary"></i>Start Date</label>
                            <input 
                                type="date" 
                                name="start_date_extc" 
                                id="start_date_extc" 
                                class="form-control" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="completion_date_extc"><i class="far fa-calendar-check mr-2 text-primary"></i>Completion Date</label>
                            <input 
                                type="date" 
                                name="completion_date_extc" 
                                id="completion_date_extc" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="funding_source_exct"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                            <input 
                                type="text" 
                                name="funding_source_exct" 
                                id="funding_source_exct" 
                                class="form-control" 
                                placeholder="E.g. CHED, DOST" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="approved_budget_exct"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Approved Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span>
                                </div>
                                <input 
                                    type="number" 
                                    name="approved_budget_exct" 
                                    id="approved_budget_exct" 
                                    class="form-control border-left-0 pl-1" 
                                    placeholder="0.00" 
                                    required 
                                />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="target_beneficiaries_communities"><i class="fas fa-users mr-2 text-primary"></i>Target Beneficiaries</label>
                            <input 
                                type="text" 
                                name="target_beneficiaries_communities" 
                                id="target_beneficiaries_communities" 
                                class="form-control" 
                                placeholder="E.g. Local Farmers, Students" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="partners"><i class="fas fa-handshake mr-2 text-primary"></i>Partners</label>
                            <input 
                                type="text" 
                                name="partners" 
                                id="partners" 
                                class="form-control" 
                                placeholder="Enter project partners" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-6 form-group mb-0">
                            <label for="status_exct"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                            <select name="status_exct" id="status_exct" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>

                        </div>

                    <div class="dynamic-files-section" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;">
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
                            
                            <input type="file" class="hidden-multi-file" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" 
                                   data-categories="Terminal Report, MOA, SO, Financial Report, Other">
                            
                            <button type="button" class="btn btn-sm btn-primary add-file-btn"><i class="fas fa-plus mr-1"></i> Browse Files</button>
                        </div>

                        <div class="existing-files-container mb-3"></div>
                        <div class="new-files-container"></div>
                    </div>

                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <input type="hidden" name="hidden_researcherID_extension" id="hidden_researcherID_extension" />
                    <input type="hidden" name="hidden_extensionID" id="hidden_extensionID" />
                    <input type="hidden" name="action_extension" id="action_extension" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="submit_button_extension" class="btn btn-danger pink px-4">Save Data</button>
                </div>
            </div>
        </form>
    </div>
</div>