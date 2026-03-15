<div id="intellectualpropModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="intellectualpropModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="intellectualprop_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Intellectual Property</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Intellectual Property -->
                    <div class="form-group">
                        <label for="title_ip">Title</label>
                        <input 
                            type="text" 
                            name="title_ip" 
                            id="title_ip" 
                            class="form-control" 
                            placeholder="Enter the title of the intellectual property" 
                            required 
                        />
                    </div>

                    <!-- Co-authors -->
                    <div class="form-group">
                        <label for="coauth">Co-authors</label>
                        <input 
                            type="text" 
                            name="coauth" 
                            id="coauth" 
                            class="form-control" 
                            placeholder="Enter co-authors' names" 
                            required 
                        />
                    </div>

                    <!-- Type of Intellectual Property (Dropdown) -->
                    <div class="form-group">
                        <label for="type">Type of Intellectual Property</label>
                        <select 
                            name="type_ip" 
                            id="type_ip" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Intellectual Property</option>
                            <option value="Patent">Patent</option>
                            <option value="Invention">Invention</option>
                            <option value="Copyright">Copyright</option>
                            <option value="Trademark">Trademark</option>
                            <option value="Industrial Design">Industrial Design</option>
                            <option value="Basics">Basics</option>
                        </select>
                    </div>

                    <!-- Date Applied -->
                    <div class="form-group">
                        <label for="date_applied">Date Applied</label>
                        <input 
                            type="date" 
                            name="date_applied" 
                            id="date_applied" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Date Granted -->
                    <div class="form-group">
                        <label for="date_granted">Date Granted</label>
                        <input 
                            type="date" 
                            name="date_granted" 
                            id="date_granted" 
                            class="form-control" 
                            required 
                        />
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_ip" id="hidden_researcherID_ip" />
                    <input type="hidden" name="hidden_intellectualPropID" id="hidden_intellectualPropID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_intellectualprop" id="action_intellectualprop" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_intellectualprop" id="submit_button_intellectualprop" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>