<div id="extensionProjectModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extensionProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="extension_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Extension Project -->
                    <div class="form-group">
                        <label for="title_extp">Title</label>
                        <input 
                            type="text" 
                            name="title_extp" 
                            id="title_extp" 
                            class="form-control" 
                            placeholder="Enter project title" 
                            required 
                        />
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input 
                            type="date" 
                            name="start_date_extc" 
                            id="start_date_extc" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Completion Date -->
                    <div class="form-group">
                        <label for="completion_date">Completion Date</label>
                        <input 
                            type="date" 
                            name="completion_date_extc" 
                            id="completion_date_extc" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="funding_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="funding_source_exct" 
                            id="funding_source_exct" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>

                    <!-- Approved Budget -->
                    <div class="form-group">
                        <label for="approved_budget">Approved Budget</label>
                        <input 
                            type="number" 
                            name="approved_budget_exct" 
                            id="approved_budget_exct" 
                            class="form-control" 
                            placeholder="Enter the approved budget" 
                            required 
                        />
                    </div>

                    <!-- Target Beneficiaries/Communities -->
                    <div class="form-group">
                        <label for="target_beneficiaries_communities">Target Beneficiaries/Communities</label>
                        <input 
                            type="text" 
                            name="target_beneficiaries_communities" 
                            id="target_beneficiaries_communities" 
                            class="form-control" 
                            placeholder="Enter target beneficiaries/communities" 
                            required 
                        />
                    </div>

                    <!-- Partners -->
                    <div class="form-group">
                        <label for="partners">Partners</label>
                        <input 
                            type="text" 
                            name="partners" 
                            id="partners" 
                            class="form-control" 
                            placeholder="Enter project partners" 
                            required 
                        />
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Status</label>
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

                    <!-- Terminal Report -->
                    <div class="form-group">
                        <label for="terminal_report">Terminal Report</label>
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

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_extension" id="hidden_researcherID_extension" />
                    <input type="hidden" name="hidden_extensionID" id="hidden_extensionID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_extension" id="action_extension" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_extension" id="submit_button_extension" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>