<div id="researchconductedModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="researchconducted_form" class="w-100">
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
                            <select name="sdgs[]" id="sdgs" multiple required class="select form-control">
                                <option value="" disabled selected>Select SDG</option>
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

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="stat"><i class="fas fa-info-circle mr-2 text-primary"></i>Status</label>
                            <select 
                                name="stat" 
                                id="stat" 
                                class="form-control" 
                                required
                            >
                                <option value="">Select Status</option>
                                <option value="On Going">On Going</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="terminal_report"><i class="fas fa-file-alt mr-2 text-primary"></i>Terminal Report</label>
                            <select 
                                name="terminal_report" 
                                id="terminal_report" 
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