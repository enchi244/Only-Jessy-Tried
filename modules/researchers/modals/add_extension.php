<div id="extModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="extModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form method="post" id="ext_project_form" class="w-100" enctype="multipart/form-data">
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
                        <input type="text" name="title_ext" id="title_ext" class="form-control" placeholder="Enter the extension title" required />
                    </div>

                    <div class="form-group mb-4">
                        <label for="linked_extension_project"><i class="fas fa-project-diagram mr-2 text-primary"></i>Based on Extension Project (Optional)</label>
                        <select name="linked_extension_project" id="linked_extension_project" class="form-control select2-single" style="width: 100%;">
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
                        <textarea name="description_ext" id="description_ext" class="form-control" rows="2" placeholder="Briefly describe the extension activity" required></textarea>
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
                        <label><i class="far fa-calendar-alt mr-2 text-primary"></i>Period of Impl.</label>
                        <div class="input-group">
                            <input type="date" name="period_start" id="period_start" class="form-control" data-parsley-required="true" />
                            <div class="input-group-append input-group-prepend">
                                <span class="input-group-text">to</span>
                            </div>
                            <input type="date" name="period_end" id="period_end" class="form-control" data-parsley-required="true" />
                        </div>
                        <input type="hidden" name="period_implement" id="period_implement" />
                    </div>

                    <script>
                        // Update the hidden field whenever dates change
                        document.getElementById('period_start').addEventListener('change', updatePeriodString);
                        document.getElementById('period_end').addEventListener('change', updatePeriodString);

                        function updatePeriodString() {
                            const start = document.getElementById('period_start').value;
                            const end = document.getElementById('period_end').value;
                            if (start && end) {
                                document.getElementById('period_implement').value = start + " to " + end;
                            }
                        }
                    </script>
                        <div class="col-md-4 form-group mb-4">
                            <label for="fund_source"><i class="fas fa-hand-holding-usd mr-2 text-primary"></i>Funding Source</label>
                            <input type="text" name="fund_source" id="fund_source" class="form-control" placeholder="Enter funding source" required />
                        </div>
                        <div class="col-md-4 form-group mb-4">
                            <label for="budget"><i class="fas fa-money-bill-wave mr-2 text-primary"></i>Budget</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-light">₱</span></div>
                                <input type="number" step="0.01" name="budget" id="budget" class="form-control" placeholder="0.00" required />
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

                    <div class="row mt-3">
                        <div class="col-md-6 form-group">
                            <label for="a_link_ext"><i class="fas fa-link mr-2 text-primary"></i>External Link</label>
                            <input type="url" name="a_link_ext" id="a_link_ext" class="form-control" placeholder="https://example.com" />
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="attachments_ext"><i class="fas fa-paperclip mr-2 text-primary"></i>File Attachment</label>
                            <input type="file" name="attachments_ext" id="attachments_ext" class="form-control-file border p-1 rounded" />
                            <div id="existing_attachment_link" class="mt-2 small"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_ext" id="hidden_researcherID_ext" />
                    <input type="hidden" name="hidden_extID" id="hidden_extID" />
                    <input type="hidden" name="hidden_existing_attachment" id="hidden_existing_attachment" />
                    <input type="hidden" name="hidden_parent_project_id" id="hidden_parent_project_id" />
                    <input type="hidden" name="action_ext" id="action_ext" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_ext" id="submit_button_ext" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    // Auto-fill Project Leader and Assistant Coordinators
    $(document).off('change', '#linked_extension_project').on('change', '#linked_extension_project', function() {
        var projectID = $(this).val();
        if(projectID) {
            $.ajax({
                url: "actions/extension_action.php",
                method: "POST",
                data: { project_id: projectID, action_ext: 'fetch_project_info' },
                dataType: "json",
                success: function(data) {
                    if(data.proj_lead !== undefined) {
                        $('#proj_lead').val(data.proj_lead);
                        if ($('#proj_lead').parsley()) { $('#proj_lead').parsley().validate(); }
                    }
                    if(data.assist_coordinators !== undefined) {
                        $('#assist_coordinators').val(data.assist_coordinators);
                        if ($('#assist_coordinators').parsley()) { $('#assist_coordinators').parsley().validate(); }
                    }
                }
            });
        } else {
            $('#proj_lead').val('');
            $('#assist_coordinators').val('');
        }
    });
</script>