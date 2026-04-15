<div id="trainingsAttendedModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="trainingsAttendedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="trainings_attended_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        Add Trainings Attended
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="form-group mb-4">
                        <label for="title_training"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input type="text" name="title_training" id="title_training" class="form-control" placeholder="Enter the title of the training" required />
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="type_training"><i class="fas fa-chalkboard mr-2 text-primary"></i>Type</label>
                            <select name="type_training" id="type_training" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="Training">Training</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Conference">Conference</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="venue_training"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Venue</label>
                            <input type="text" name="venue_training" id="venue_training" class="form-control" placeholder="Enter the venue" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="date_training"><i class="far fa-calendar-alt mr-2 text-primary"></i>Date of Training</label>
                            <input type="date" name="date_training" id="date_training" class="form-control" required />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="level_training"><i class="fas fa-layer-group mr-2 text-primary"></i>Level</label>
                            <select name="level_training" id="level_training" class="form-control" required>
                                <option value="">Select Level</option>
                                <option value="Local">Local</option>
                                <option value="Regional">Regional</option>
                                <option value="National">National</option>
                                <option value="International">International</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="type_learning_dev"><i class="fas fa-book-open mr-2 text-primary"></i>Type of Learning Dev</label>
                            <select name="type_learning_dev" id="type_learning_dev" class="form-control" required>
                                <option value="">Select Type of Learning Dev</option>
                                <option value="Managerial">Managerial</option>
                                <option value="Supervisory">Supervisory</option>
                                <option value="Technical">Technical</option>
                                <option value="Foundation">Foundation</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="sponsor_org"><i class="fas fa-building mr-2 text-primary"></i>Sponsor Organization</label>
                            <input type="text" name="sponsor_org" id="sponsor_org" class="form-control" placeholder="Enter the sponsor organization" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="total_hours_training"><i class="fas fa-clock mr-2 text-primary"></i>Total Number of Hours</label>
                            <input type="number" name="total_hours_training" id="total_hours_training" class="form-control" placeholder="E.g., 40" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="m-0" for="a_link_training"><i class="fas fa-link mr-2 text-primary"></i>External Links (Optional)</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_new_link_btn_training"><i class="fas fa-plus"></i> Add Link</button>
                            </div>
                            <div id="dynamic_links_container_training">
                                </div>
                        </div>
                    </div>

                    <div class="row border-bottom pb-3 mb-3">
                        <div class="col-md-12 form-group mb-0">
                            <label for="has_files_training"><i class="fas fa-paperclip mr-2 text-primary"></i>File Attachments</label>
                            <select name="has_files_training" id="has_files_training" class="form-control" required>
                                <option value="None">None</option>
                                <option value="With">With Files</option>
                            </select>
                        </div>
                    </div>

                    <div id="dynamic_files_section_training" style="display: none; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="add_file_btn_training"><i class="fas fa-plus mr-1"></i> Add File</button>
                        </div>

                        <div id="existing_files_container_training" class="mb-3"></div>
                        <div id="new_files_container_training"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_training" id="hidden_researcherID_training" />
                    <input type="hidden" name="hidden_trainingID" id="hidden_trainingID" />
                    <input type="hidden" name="action_training" id="action_training" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_training" id="submit_button_training" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>