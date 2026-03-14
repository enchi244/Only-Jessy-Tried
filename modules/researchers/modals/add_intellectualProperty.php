<div id="intellectualpropModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="intellectualpropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="intellectualprop_form" class="w-100">
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

                    <div class="form-group mb-4">
                        <label for="title_ip"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title_ip" 
                            id="title_ip" 
                            class="form-control" 
                            placeholder="Enter the title of the intellectual property" 
                            required 
                        />
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="coauth"><i class="fas fa-user-friends mr-2 text-primary"></i>Co-authors</label>
                            <input 
                                type="text" 
                                name="coauth" 
                                id="coauth" 
                                class="form-control" 
                                placeholder="Enter co-authors' names" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="type_ip"><i class="fas fa-certificate mr-2 text-primary"></i>Type of Intellectual Property</label>
                            <select 
                                name="type_ip" 
                                id="type_ip" 
                                class="form-control" 
                                required
                            >
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

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="date_applied"><i class="far fa-calendar-plus mr-2 text-primary"></i>Date Applied</label>
                            <input 
                                type="date" 
                                name="date_applied" 
                                id="date_applied" 
                                class="form-control" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="date_granted"><i class="far fa-calendar-check mr-2 text-primary"></i>Date Granted</label>
                            <input 
                                type="date" 
                                name="date_granted" 
                                id="date_granted" 
                                class="form-control" 
                                required 
                            />
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