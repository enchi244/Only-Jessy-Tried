<div class="modal fade" id="policyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modal_title_policy"><i class="fas fa-file-contract mr-2"></i> Add Research Policy</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="policy_form" enctype="multipart/form-data">
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fas fa-info-circle mr-2"></i> Author is automatically set to the current profile.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Policy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="policy_title" class="form-control shadow-sm" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Date Implemented <span class="text-danger">*</span></label>
                                <input type="date" name="date_implemented" id="policy_date" class="form-control shadow-sm" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Link to Research (Optional)</label>
                                <select name="research_conducted_id" id="policy_research_conducted" class="form-control select2 shadow-sm" style="width: 100%;">
                                    <option value="">Loading researches...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="abstract"><i class="fas fa-align-left mr-2 text-primary"></i>Abstract</label>
                            <textarea name="abstract" id="policy_abstract" class="form-control" rows="4" placeholder="Enter policy abstract..."></textarea>
                            <small class="text-muted mt-1 d-block text-right">
                                <i class="fas fa-pen mr-1"></i> Word Count: <span id="policy_word_count" class="font-weight-bold text-primary">0</span>
                            </small>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Description / Details <span class="text-danger">*</span></label>
                                <textarea name="description" id="policy_description" class="form-control shadow-sm" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2 mb-2">
                            <div class="dynamic-files-section border p-3 rounded bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <label class="font-weight-bold text-dark m-0"><i class="fas fa-folder-open text-primary mr-2"></i>Attached Documents</label>
                                    <button type="button" class="btn btn-sm btn-primary add-file-btn"><i class="fas fa-plus"></i> Add File</button>
                                    <input type="file" class="d-none hidden-multi-file" multiple data-categories="Policy Document, Memorandum, Supporting Evidence, Presentation, Other">
                                </div>
                                <div class="existing-files-container mb-2"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_id_policy" id="hidden_id_policy" />
                    <input type="hidden" name="researcher_id" id="policy_researcher_id" />
                    <input type="hidden" name="action_policy" id="action_policy" value="Add" />
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Close</button>
                    <input type="submit" name="submit_button_policy" id="submit_button_policy" class="btn btn-danger pink shadow-sm" value="Add" />
                </div>
            </form>
        </div>
    </div>
</div>