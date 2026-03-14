<div id="paperPresentationModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="paperPresentationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="paper_presentation_form" class="w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-microphone-alt"></i>
                        </div>
                        Add Paper Presentation
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="form-group mb-4">
                        <label for="title_pp"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title_pp" 
                            id="title_pp" 
                            class="form-control" 
                            placeholder="Enter the title of the paper presentation" 
                            required 
                        />
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="conference_title"><i class="fas fa-users mr-2 text-primary"></i>Conference Level</label>
                            <select 
                                name="conference_title" 
                                id="conference_title" 
                                class="form-control" 
                                required
                            >
                                <option value="">Select Conference Level</option>
                                <option value="Local">Local</option>
                                <option value="Regional">Regional</option>
                                <option value="National">National</option>
                                <option value="International">International</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="conference_venue"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Conference Venue</label>
                            <input 
                                type="text" 
                                name="conference_venue" 
                                id="conference_venue" 
                                class="form-control" 
                                placeholder="Enter the conference venue" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="conference_organizer"><i class="fas fa-building mr-2 text-primary"></i>Conference Organizer</label>
                            <input 
                                type="text" 
                                name="conference_organizer" 
                                id="conference_organizer" 
                                class="form-control" 
                                placeholder="Enter the conference organizer" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="date_paper"><i class="far fa-calendar-alt mr-2 text-primary"></i>Date of Presentation</label>
                            <input 
                                type="date" 
                                name="date_paper" 
                                id="date_paper" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="type_pp"><i class="fas fa-file-alt mr-2 text-primary"></i>Type of Paper</label>
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

                        <div class="col-md-6 form-group mb-3">
                            <label for="discipline"><i class="fas fa-book-reader mr-2 text-primary"></i>Discipline / Program</label>
                            <select name="discipline" id="discipline" class="form-control" required>
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

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_pp" id="hidden_researcherID_pp" />
                    <input type="hidden" name="hidden_paperPresentationID" id="hidden_paperPresentationID" />
                    
                    <input type="hidden" name="action_paper_presentation" id="action_paper_presentation" value="Add" />
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_paper_presentation" id="submit_button_paper_presentation" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>