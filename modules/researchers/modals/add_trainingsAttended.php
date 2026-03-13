<div id="trainingsAttendedModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="trainingsAttendedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="trainings_attended_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Trainings Attended</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Training -->
                    <div class="form-group">
                        <label for="title_training">Title</label>
                        <input 
                            type="text" 
                            name="title_training" 
                            id="title_training" 
                            class="form-control" 
                            placeholder="Enter the title of the training" 
                            required 
                        />
                    </div>

                    <!-- Type of the Training -->
                    <div class="form-group">
                        <label for="type_training">Type</label>

                       
                        <select 
                            name="type_training" 
                            id="type_training" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Training</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Conference">Conference</option>
                            <option value="Training">Training</option>
                        </select>

                    </div>

                    <!-- Venue -->
                    <div class="form-group">
                        <label for="venue_training">Venue</label>
                        <input 
                            type="text" 
                            name="venue_training" 
                            id="venue_training" 
                            class="form-control" 
                            placeholder="Enter the training venue" 
                            required 
                        />
                    </div>

                    <!-- Date of Training -->
                    <div class="form-group">
                        <label for="date_training">Date</label>
                        <input 
                            type="date" 
                            name="date_training" 
                            id="date_training" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Level -->
                    <div class="form-group">
                        <label for="level_training">Level</label>
                          <select 
                            name="level_training" 
                            id="level_training" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Level</option>
                            <option value="Local">Local</option>
                            <option value="Regional">Regional</option>
                            <option value="National">National</option>
                            <option value="International">International</option>
                        </select>


                    </div>

                    <!-- Type of Learning Development -->
                    <div class="form-group">
                        <label for="type_learning_dev">Type of Learning Development</label>
                      
                      
                        
                      
                        <select 
                            name="type_learning_dev" 
                            id="type_learning_dev" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Type of Learing Development</option>
                            <option value="Clerical">Clerical</option>
                            <option value="Supervisory">Supervisory</option>
                            <option value="Technical">Technical</option>
                            <option value="Managerial">Managerial</option>
                        </select>

                      
                      
                        </div>
                      
                      
                      
                      
                      
                      
                    

                    <!-- Sponsor/Organizer -->
                    <div class="form-group">
                        <label for="sponsor_org">Sponsor/Organizer</label>
                        <input 
                            type="text" 
                            name="sponsor_org" 
                            id="sponsor_org" 
                            class="form-control" 
                            placeholder="Enter the sponsor or organizer" 
                            required 
                        />
                    </div>

                    <!-- Total Number of Hours -->
                    <div class="form-group">
                        <label for="total_hours_training">Total Number of Hours</label>
                        <input 
                            type="number" 
                            name="total_hours_training" 
                            id="total_hours_training" 
                            class="form-control" 
                            placeholder="Enter the total number of hours" 
                            required 
                        />
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_training" id="hidden_researcherID_training" />
                    <input type="hidden" name="hidden_trainingID" id="hidden_trainingID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_training" id="action_training" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_training" id="submit_button_training" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>