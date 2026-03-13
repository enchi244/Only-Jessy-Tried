<div id="paperPresentationModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="paperPresentationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="paper_presentation_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Add Paper Presentation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <!-- Title of the Paper Presentation -->
                    <div class="form-group">
                        <label for="title_pp">Title</label>
                        <input 
                            type="text" 
                            name="title_pp" 
                            id="title_pp" 
                            class="form-control" 
                            placeholder="Enter the title of the paper presentation" 
                            required 
                        />
                    </div>

                    <!-- Conference Title -->
                    <div class="form-group">
                              <label for="conference_title">Conference Title</label>
                        <select 
                            name="conference_title" 
                            id="conference_title" 
                            class="form-control" 
                            required
                        >

                        



                            <option value="">Select Conference Title</option>
                            <option value="Local">Local</option>
                            <option value="Regional">Regional</option>
                            <option value="National">National</option>
                            <option value="International">International</option>
                        </select>







                    </div>

                    <!-- Conference Venue -->
                    <div class="form-group">
                        <label for="conference_venue">Conference Venue</label>
                        <input 
                            type="text" 
                            name="conference_venue" 
                            id="conference_venue" 
                            class="form-control" 
                            placeholder="Enter the conference venue" 
                            required 
                        />
                    </div>

                    <!-- Conference Organizer -->
                    <div class="form-group">
                        <label for="conference_organizer">Conference Organizer</label>
                        <input 
                            type="text" 
                            name="conference_organizer" 
                            id="conference_organizer" 
                            class="form-control" 
                            placeholder="Enter the conference organizer" 
                            required 
                        />
                    </div>

                    <!-- Date of Paper Presentation -->
                    <div class="form-group">
                        <label for="date_paper">Date of Presentation</label>
                        <input 
                            type="date" 
                            name="date_paper" 
                            id="date_paper" 
                            class="form-control" 
                            required 
                        />
                    </div>

                    <!-- Type of Paper (Dropdown) -->
                    <div class="form-group">
                        <label for="type_pp">Type of Paper</label>
                        <select 
                            name="type_pp" 
                            id="type_pp" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select Type of Paper</option>
                            <option value="Oral">Oral</option>
                            <option value="Poster">Poster</option>
                        </select>
                    </div>

                    <!-- Discipline -->
                    <div class="form-group">
                        <label for="discipline">Discipline</label>
        

                        <select name="discipline" id="discipline" class="form-control" data-parsley-trigger="change">
                                                                                                                                                                            <option value="">Select Major Discipline or Program</option>
                                                                                                                                                                            <?php
                                                                                                                                                                            $object->query = "SELECT * FROM tbl_majordiscipline";
                                                                                                                                                                            $program_result = $object->get_result();
                                                                                                                                                                            foreach($program_result as $program) {
                                                                                                                                                                                echo '<option value="'.$program["major"].'">'.$program["major"].'</option>';
                                                                                                                                                                            }
                                                                                                                                                                            ?>
                                                                                                                                                                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- Hidden Fields for IDs -->
                    <input type="hidden" name="hidden_researcherID_pp" id="hidden_researcherID_pp" />
                    <input type="hidden" name="hidden_paperPresentationID" id="hidden_paperPresentationID" />
                    
                    <!-- Action to identify the operation (Add or Edit) -->
                    <input type="hidden" name="action_paper_presentation" id="action_paper_presentation" value="Add" />
                    
                    <!-- Submit Button -->
                    <input type="submit" name="submit_button_paper_presentation" id="submit_button_paper_presentation" class="btn btn-danger" value="Add" />
                    
                    <!-- Close Button -->
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>