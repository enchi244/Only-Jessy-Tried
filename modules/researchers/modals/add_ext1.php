<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="ext_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Extension Project -->
                    <div class="form-group">
                        <label for="title_ext">Title</label>
                        <input 
                            type="text" 
                            name="title_ext" 
                            id="title_ext" 
                            class="form-control" 
                            placeholder="Enter the title of the extension project" 
                            required 
                        />
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description_ext">Description</label>
                        <input 
                            name="description_ext" 
                            id="description_ext" 
                            class="form-control" 
                            placeholder="Enter a description of the project" 
                            required
                        ></input>
                    </div>

                    <!-- Project Leader -->
                    <div class="form-group">
                        <label for="proj_lead">Project Leader</label>
                        <input 
                            type="text" 
                            name="proj_lead" 
                            id="proj_lead" 
                            class="form-control" 
                            placeholder="Enter the project leader's name" 
                            required 
                        />
                    </div>

                    <!-- Assistant Coordinators -->
                    <div class="form-group">
                        <label for="assist_coordinators">Assistant Coordinators</label>
                        <input 
                            type="text" 
                            name="assist_coordinators" 
                            id="assist_coordinators" 
                            class="form-control" 
                            placeholder="Enter assistant coordinators' names" 
                            required 
                        />
                    </div>

                    <!-- Period of Implementation -->
                    <div class="form-group">
                        <label for="period_implement">Period of Implementation</label>
                        <input 
                            type="text" 
                            name="period_implement" 
                            id="period_implement" 
                            class="form-control" 
                            placeholder="Enter the implementation period" 
                            required 
                        />
                    </div>

                    <!-- Budget -->
                    <div class="form-group">
                        <label for="budget">Budget</label>
                        <input 
                            type="number" 
                            name="budget" 
                            id="budget" 
                            class="form-control" 
                            placeholder="Enter the project budget" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="fund_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="fund_source" 
                            id="fund_source" 
                            class="form-control" 
                            placeholder="Enter the funding source" 
                            required 
                        />
                    </div>

                    <!-- Target Beneficiaries -->
                    <div class="form-group">
                        <label for="target_beneficiaries">Target Beneficiaries</label>
                        <input 
                            name="target_beneficiaries" 
                            id="target_beneficiaries" 
                            class="form-control" 
                            placeholder="Enter the target beneficiaries" 
                            required
                        ></input>
                    </div>

                    <!-- Partners -->
                    <div class="form-group">
                        <label for="partners">Partners</label>
                        <input 
                            name="partners" 
                            id="partners" 
                            class="form-control" 
                            placeholder="Enter the project partners" 
                            required
                        ></input>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="stat_ext">Status</label>
                        <select 
                            name="stat_ext" 
                            id="stat_ext" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                 
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>