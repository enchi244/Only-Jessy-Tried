<div id="publicationModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="publicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" id="publication_form" class="w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        Add Publication
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>

                    <div class="form-group mb-4">
                        <label for="title_pub"><i class="fas fa-heading mr-2 text-primary"></i>Title</label>
                        <input 
                            type="text" 
                            name="title_pub" 
                            id="title_pub" 
                            class="form-control" 
                            placeholder="Enter the complete publication title" 
                            required 
                        />
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="start"><i class="far fa-calendar-plus mr-2 text-primary"></i>Start Date</label>
                            <input 
                                type="date" 
                                name="start" 
                                id="start" 
                                class="form-control" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="end"><i class="far fa-calendar-check mr-2 text-primary"></i>End Date</label>
                            <input 
                                type="date" 
                                name="end" 
                                id="end" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label for="journal"><i class="fas fa-newspaper mr-2 text-primary"></i>Journal</label>
                            <input 
                                type="text" 
                                name="journal" 
                                id="journal" 
                                class="form-control" 
                                placeholder="Enter journal name" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label for="vol_num_issue_num"><i class="fas fa-layer-group mr-2 text-primary"></i>Volume & Issue Number</label>
                            <input 
                                type="text" 
                                name="vol_num_issue_num" 
                                id="vol_num_issue_num" 
                                class="form-control" 
                                placeholder="E.g. Vol. 5, Issue 2" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="issn_isbn"><i class="fas fa-barcode mr-2 text-primary"></i>ISSN/ISBN</label>
                            <input 
                                type="text" 
                                name="issn_isbn" 
                                id="issn_isbn" 
                                class="form-control" 
                                placeholder="Enter ISSN or ISBN number" 
                                required 
                            />
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="indexing"><i class="fas fa-list-ol mr-2 text-primary"></i>Indexing</label>
                            <input 
                                type="text" 
                                name="indexing" 
                                id="indexing" 
                                class="form-control" 
                                placeholder="E.g. Scopus, Web of Science" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="publication_date"><i class="fas fa-calendar-alt mr-2 text-primary"></i>Publication Date</label>
                            <input 
                                type="date" 
                                name="publication_date" 
                                id="publication_date" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer">
                    <input type="hidden" name="hidden_researcherID" id="hidden_researcherID" />
                    <input type="hidden" name="hidden_publicationID" id="hidden_publicationID" />
                    <input type="hidden" name="action_publication" id="action_publication" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_publication" id="submit_button_publication" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>