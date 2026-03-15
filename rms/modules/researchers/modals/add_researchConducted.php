<div id="researchconductedModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <form method="post" id="researchconducted_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Research -->
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            class="form-control" 
                            placeholder="Enter the title of the research" 
                            required 
                        />
                    </div>

                    <!-- Research Agenda Cluster -->
                    <div class="form-group">
                        <label for="research_agenda_cluster">Research Agenda Cluster</label>
                        <input 
                            type="text" 
                            name="research_agenda_cluster" 
                            id="research_agenda_cluster" 
                            class="form-control" 
                            placeholder="Enter the research agenda cluster" 
                            required 
                        />
                    </div>

                    <!-- SDG Selection -->
                    <div class="form-group">
                        <label>Select SDG</label>
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

                   
                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="started_date">Start Date</label>
                        <input 
                            type="date" 
                            name="started_date" 
                            id="started_date" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Completed Date -->
                    <div class="form-group">
                        <label for="completed_date">Completed Date</label>
                        <input 
                            type="date" 
                            name="completed_date" 
                            id="completed_date" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Funding Source -->
                    <div class="form-group">
                        <label for="funding_source">Funding Source</label>
                        <input 
                            type="text" 
                            name="funding_source" 
                            id="funding_source" 
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
                            name="approved_budget" 
                            id="approved_budget" 
                            class="form-control" 
                            step="0.01" 
                            placeholder="Enter the approved budget" 
                            required 
                        />
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="stat">Status</label>
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

                    <!-- Terminal Report -->
                    <div class="form-group">
                        <label for="terminal_report">Terminal Report</label>
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
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id_researchedconducted" id="hidden_id_researchedconducted" />
                    <input type="hidden" name="hiddeny" id="hiddeny" />
                    <input type="hidden" name="action_researchedconducted" id="action_researchedconducted" value="Add" />
                    <input type="submit" name="submit_button_researchedconducted" id="submit_button_researchedconducted" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>