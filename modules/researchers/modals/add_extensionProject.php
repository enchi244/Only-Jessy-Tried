<div id="extensionProjectModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extensionProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="extension_project_form" class="w-100">
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

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="status_exct"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                            <select 
                                name="status_exct" 
                                id="status_exct" 
                                class="form-control" 
                                required
                            >
                                <option value="">Select Status</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="terminal_report_extc"><i class="fas fa-file-alt mr-2 text-primary"></i>Terminal Report</label>
                            <select 
                                name="terminal_report_extc" 
                                id="terminal_report_extc" 
                                class="form-control" 
                                required
                            >
                                <option value="">Select Status</option>
                                <option value="With">With</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_extension" id="hidden_researcherID_extension" />
                    <input type="hidden" name="hidden_extensionID" id="hidden_extensionID" />
                    
                    <input type="hidden" name="action_extension" id="action_extension" value="Add" />
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_extension" id="submit_button_extension" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>