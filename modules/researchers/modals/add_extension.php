<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <form method="post" id="ext_project_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header pb-2">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        Add Extension
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="bg-light px-4 pt-3 border-bottom">
                    <ul class="nav nav-pills nav-justified pb-3" id="extensionTabs" role="tablist">
                        <li class="nav-item mr-2">
                            <a class="nav-link active font-weight-bold shadow-sm" id="general-tab" data-toggle="pill" href="#general" role="tab" style="border-radius: 8px;">
                                <i class="fas fa-info-circle mr-2"></i>1. General Info
                            </a>
                        </li>
                        <li class="nav-item mr-2">
                            <a class="nav-link font-weight-bold shadow-sm" id="logistics-tab" data-toggle="pill" href="#logistics" role="tab" style="border-radius: 8px;">
                                <i class="fas fa-users-cog mr-2"></i>2. Team & Logistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold shadow-sm" id="files-tab" data-toggle="pill" href="#files" role="tab" style="border-radius: 8px;">
                                <i class="fas fa-folder-open mr-2"></i>3. Files & Links
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="modal-body p-4" style="max-height: 55vh; overflow-y: auto; overflow-x: hidden;">
                    <span id="form_message"></span>

                    <div class="tab-content" id="extensionTabsContent">
                        
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="form-group mb-4">
                                <label for="title_ext"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                                <input type="text" name="title_ext" id="title_ext" class="form-control" placeholder="Enter the extension title" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="linked_extension_project"><i class="fas fa-project-diagram mr-2 text-primary"></i>Based on Extension Project <span class="text-danger">*</span></label>
                                <select name="linked_extension_project" id="linked_extension_project" class="form-control select2-single" style="width: 100%;" required>
                                    <option value="">Select an Extension Project...</option>
                                    <?php
                                    $object->query = "SELECT id, title FROM tbl_extension_project_conducted WHERE status = 1 ORDER BY title ASC";
                                    $all_ext_projects = $object->get_result();
                                    foreach($all_ext_projects as $ep) { 
                                        echo '<option value="'.$ep["id"].'">'.htmlspecialchars($ep["title"]).'</option>'; 
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="description_ext"><i class="fas fa-align-left mr-2 text-primary"></i>Description</label>
                                <textarea name="description_ext" id="description_ext" class="form-control" rows="3" placeholder="Briefly describe the extension activity" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-4">
                                    <label for="target_beneficiaries"><i class="fas fa-users mr-2 text-primary"></i>Target Beneficiaries</label>
                                    <input type="text" name="target_beneficiaries" id="target_beneficiaries" class="form-control" placeholder="E.g. Local Community" required />
                                </div>
                                <div class="col-md-6 form-group mb-4">
                                    <label for="stat_ext"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                                    <select name="stat_ext" id="stat_ext" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="Ongoing">Ongoing</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="logistics" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group mb-4">
                                    <label for="proj_lead"><i class="fas fa-user-tie mr-2 text-primary"></i>Project Leader</label>
                                    <select name="proj_lead" id="proj_lead" class="form-control select2-researcher" style="width: 100%;" required>
                                        <option value="">Search Researcher...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-4">
                                    <label for="assist_coordinators"><i class="fas fa-users-cog mr-2 text-primary"></i>Asst. Coordinators</label>
                                    <select name="assist_coordinators[]" id="assist_coordinators" class="form-control select2-researcher" style="width: 100%;" multiple="multiple">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="partners_ext"><i class="fas fa-handshake mr-2 text-primary"></i>Partners</label>
                                <textarea name="partners_ext" id="partners_ext" class="form-control" rows="2" placeholder="Enter partners involved" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group mb-4">
                                    <label><i class="far fa-calendar-alt mr-2 text-primary"></i>Period of Impl.</label>
                                    <div class="input-group">
                                        <input type="date" name="period_start" id="period_start" class="form-control" data-parsley-required="true" />
                                        <div class="input-group-append input-group-prepend">
                                            <span class="input-group-text border-left-0 border-right-0">to</span>
                                        </div>
                                        <input type="date" name="period_end" id="period_end" class="form-control" data-parsley-required="true" />
                                    </div>
                                    <input type="hidden" name="period_implement" id="period_implement" />
                                </div>

                                <div class="col-md-4 form-group mb-4">
                                    <label for="fund_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                                    <input type="text" name="fund_source" id="fund_source" class="form-control" placeholder="Enter funding source" required />
                                </div>
                                
                                <div class="col-md-4 form-group mb-4">
                                    <label for="budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span></div>
                                        <input type="number" step="0.01" name="budget" id="budget" class="form-control border-left-0 pl-1" placeholder="0.00" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="files" role="tabpanel">
                            <div class="form-group mb-4">
                                <label for="a_link_ext"><i class="fas fa-link mr-2 text-primary"></i>External Link</label>
                                <input type="url" name="a_link_ext" id="a_link_ext" class="form-control" placeholder="https://example.com" />
                            </div>

                            <div class="dynamic-files-section" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px dashed #d1d3e2;">
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
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                                        <small class="text-muted">Upload activity reports, attendance, and photos.</small>
                                    </div>
                                    
                                    <input type="file" class="hidden-multi-file" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" 
                                           data-categories="Activity Report, Attendance, Photos, Other">
                                    
                                    <button type="button" class="btn btn-primary shadow-sm add-file-btn"><i class="fas fa-plus mr-1"></i> Browse Files</button>
                                </div>

                                <div class="existing-files-container mb-3"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="modal-footer bg-light border-top-0">
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    <input type="hidden" name="hidden_existing_attachment" id="hidden_existing_attachment" />
                    <input type="hidden" name="hidden_parent_project_id" id="hidden_parent_project_id" />
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger pink px-4 font-weight-bold shadow-sm" value="Save Extension" />
                </div>
            </div>
        </form>
    </div>
</div>