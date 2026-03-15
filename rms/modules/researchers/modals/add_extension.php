<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="ext_project_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Extension</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    <div class="form-group"><label>Title</label><input type="text" name="title_ext" id="title_ext" class="form-control" required /></div>
                    <div class="form-group"><label>Description</label><textarea name="description_ext" id="description_ext" class="form-control" required></textarea></div>
                    <div class="form-group"><label>Project Leader</label><input type="text" name="proj_lead" id="proj_lead" class="form-control" required /></div>
                    <div class="form-group"><label>Assistant Coordinators</label><input type="text" name="assist_coordinators" id="assist_coordinators" class="form-control" required /></div>
                    <div class="form-group"><label>Period of Implementation</label><input type="text" name="period_implement" id="period_implement" class="form-control" required /></div>
                    <div class="form-group"><label>Budget</label><input type="number" name="budget" id="budget" class="form-control" required /></div>
                    <div class="form-group"><label>Funding Source</label><input type="text" name="fund_source" id="fund_source" class="form-control" required /></div>
                    <div class="form-group"><label>Target Beneficiaries</label><input type="text" name="target_beneficiaries" id="target_beneficiaries" class="form-control" required /></div>
                    <div class="form-group"><label>Partners</label><input type="text" name="partners" id="partners" class="form-control" required /></div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="stat_ext" id="stat_ext" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>