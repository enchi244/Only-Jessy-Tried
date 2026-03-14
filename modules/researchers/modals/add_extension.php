<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form method="post" id="ext_project_form" class="w-100">
            <div class="modal-content">
                <div class="modal-header">
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
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="form-group mb-4">
                        <label for="title_ext"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title_ext" 
                            id="title_ext" 
                            class="form-control" 
                            placeholder="Enter the extension title" 
                            required 
                        />
                    </div>

                    <div class="form-group mb-4">
                        <label for="description_ext"><i class="fas fa-align-left mr-2 text-primary"></i>Description</label>
                        <textarea 
                            name="description_ext" 
                            id="description_ext" 
                            class="form-control" 
                            rows="2" 
                            placeholder="Briefly describe the extension activity" 
                            required
                        ></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-4">
                            <label for="proj_lead"><i class="fas fa-user-tie mr-2 text-primary"></i>Project Leader</label>
                            <input type="text" name="proj_lead" id="proj_lead" class="form-control" placeholder="Leader name" required />
                        </div>

                        <div class="col-md-4 form-group mb-4">
                            <label for="assist_coordinators"><i class="fas fa-users-cog mr-2 text-primary"></i>Asst. Coordinators</label>
                            <input type="text" name="assist_coordinators" id="assist_coordinators" class="form-control" placeholder="Coordinators" required />
                        </div>

                        <div class="col-md-4 form-group mb-4">
                            <label for="target_beneficiaries"><i class="fas fa-users mr-2 text-primary"></i>Beneficiaries</label>
                            <input type="text" name="target_beneficiaries" id="target_beneficiaries" class="form-control" placeholder="E.g. Local Community" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-4">
                            <label for="period_implement"><i class="far fa-calendar-alt mr-2 text-primary"></i>Period of Impl.</label>
                            <input type="text" name="period_implement" id="period_implement" class="form-control" placeholder="E.g. Jan - Dec 2023" required />
                        </div>

                        <div class="col-md-4 form-group mb-4">
                            <label for="fund_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                            <input type="text" name="fund_source" id="fund_source" class="form-control" placeholder="Enter funding source" required />
                        </div>

                        <div class="col-md-4 form-group mb-4">
                            <label for="budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0 rounded-left" style="border-radius: 8px 0 0 8px;">₱</span>
                                </div>
                                <input type="number" name="budget" id="budget" class="form-control border-left-0 pl-1" placeholder="0.00" required />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="partners"><i class="fas fa-handshake mr-2 text-primary"></i>Partners</label>
                            <input type="text" name="partners" id="partners" class="form-control" placeholder="Enter partners involved" required />
                        </div>

                        <div class="col-md-6 form-group mb-3">
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
                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>