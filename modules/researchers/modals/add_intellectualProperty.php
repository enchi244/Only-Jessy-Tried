<div id="intellectualpropModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="intellectualpropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <form method="post" id="intellectualprop_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        Add Intellectual Property
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="row">
                        <!-- Left Column: IP Identity & Team -->
                        <div class="col-lg-7 pr-lg-4">
                            <div class="form-group mb-3">
                                <label for="title_ip"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                                <input type="text" name="title_ip" id="title_ip" class="form-control" placeholder="Enter the title of the intellectual property" required />
                            </div>

                            <div class="form-group mb-3">
                                <label for="lead_researcher_id_ip"><i class="fas fa-user-tie mr-2 text-primary"></i>Lead Author / Owner</label>
                                <select name="lead_researcher_id_ip" id="lead_researcher_id_ip" class="form-control" required style="width: 100%;">
                                    <option value="">Select Lead Author</option>
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers = $object->get_result();
                                    foreach($researchers as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> The primary owner of this intellectual property.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="collaborators_ip"><i class="fas fa-users mr-2 text-primary"></i>Co-Authors / Co-Owners</label>
                                <select name="collaborators_ip[]" id="collaborators_ip" multiple class="select form-control" style="width: 100%;">
                                    <?php
                                    $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata ORDER BY familyName ASC";
                                    $researchers_collab = $object->get_result();
                                    foreach($researchers_collab as $res) {
                                        echo '<option value="'.$res["id"].'">'.htmlspecialchars($res["familyName"] . ', ' . $res["firstName"]).'</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Select additional co-authors here.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="type_ip"><i class="fas fa-certificate mr-2 text-primary"></i>Type of Intellectual Property</label>
                                <select name="type_ip" id="type_ip" class="form-control" required>
                                    <option value="">Select Type of IP</option>
                                    <option value="Patent">Patent</option>
                                    <option value="Invention">Invention</option>
                                    <option value="Copyright">Copyright</option>
                                    <option value="Trademark">Trademark</option>
                                    <option value="Industrial Design">Industrial Design</option>
                                    <option value="Basics">Basics</option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column: Timeline, Links & Files -->
                        <div class="col-lg-5 border-left pl-lg-4">
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="date_applied"><i class="far fa-calendar-plus mr-2 text-primary"></i>Date Applied</label>
                                    <input type="date" name="date_applied" id="date_applied" class="form-control" required />
                                </div>

                                <div class="col-md-6 form-group mb-3">
                                    <label for="date_granted"><i class="far fa-calendar-check mr-2 text-primary"></i>Date Granted</label>
                                    <input type="date" name="date_granted" id="date_granted" class="form-control" required />
                                </div>
                            </div>

                            <div class="form-group mb-3 border-bottom pb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="m-0" for="a_link_ip"><i class="fas fa-link mr-2 text-primary"></i>External Links (Optional)</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add_new_link_btn_ip"><i class="fas fa-plus"></i> Add Link</button>
                                </div>
                                <div id="dynamic_links_container_ip"></div>
                            </div>

                            <div class="dynamic-files-section" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                                <style>
                                    /* Fix for long file names causing overflow */
                                    .dynamic-files-section .existing-files-container .d-flex > div:first-child,
                                    .dynamic-files-section .new-files-container .d-flex > div:first-child {
                                        min-width: 0;
                                        flex: 1;
                                        padding-right: 10px;
                                    }
                                    .dynamic-files-section a, .dynamic-files-section .text-gray-800 {
                                        word-break: break-word;
                                    }
                                </style>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-folder-open mr-2"></i>Attached Files</h6>
                                    
                                    <input type="file" class="hidden-multi-file" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx" 
                                           data-categories="Certificate, Application Document, MOA, Other">
                                    
                                    <button type="button" class="btn btn-sm btn-primary add-file-btn"><i class="fas fa-plus mr-1"></i> Browse</button>
                                </div>

                                <div class="existing-files-container mb-3"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID_ip" id="hidden_researcherID_ip" />
                    <input type="hidden" name="hidden_intellectualPropID" id="hidden_intellectualPropID" />
                    <input type="hidden" name="action_intellectualprop" id="action_intellectualprop" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_intellectualprop" id="submit_button_intellectualprop" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>