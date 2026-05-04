<div id="policyModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="policyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <form method="post" id="policy_form" class="w-100" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title_policy">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm pink" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        Add Research Policy
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info py-2 mb-0 border-left-info shadow-sm">
                                <i class="fas fa-info-circle mr-2"></i> Author is automatically set to the current profile.
                            </div>
                        </div>
                        
                        <!-- Left Column: Primary Details -->
                        <div class="col-lg-7 pr-lg-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark" for="policy_title"><i class="fas fa-heading mr-2 text-primary"></i>Policy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="policy_title" class="form-control" placeholder="Enter the policy title" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark" for="policy_date"><i class="far fa-calendar-check mr-2 text-primary"></i>Date Implemented <span class="text-danger">*</span></label>
                                    <input type="date" name="date_implemented" id="policy_date" class="form-control" required>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark" for="policy_research_conducted"><i class="fas fa-flask mr-2 text-primary"></i>Link to Research (Optional)</label>
                                    <select name="research_conducted_id" id="policy_research_conducted" class="form-control select2" style="width: 100%;">
                                        <option value="">Loading researches...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark" for="policy_abstract"><i class="fas fa-align-left mr-2 text-primary"></i>Abstract</label>
                                <textarea name="abstract" id="policy_abstract" class="form-control" rows="5" placeholder="Enter policy abstract..."></textarea>
                                <small class="text-muted mt-1 d-block text-right">
                                    <i class="fas fa-pen mr-1"></i> Word Count: <span id="policy_word_count" class="font-weight-bold text-primary">0</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Right Column: Description & Files -->
                        <div class="col-lg-5 border-left pl-lg-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark" for="policy_description"><i class="fas fa-align-justify mr-2 text-primary"></i>Description / Details <span class="text-danger">*</span></label>
                                <textarea name="description" id="policy_description" class="form-control" rows="5" placeholder="Provide detailed information about the policy..." required></textarea>
                            </div>

                            <div class="dynamic-files-section mt-4" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
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
                                            data-categories="Policy Document, Memorandum, Supporting Evidence, Presentation, Other">
                                    
                                    <button type="button" class="btn btn-sm btn-primary add-file-btn"><i class="fas fa-plus mr-1"></i> Browse</button>
                                </div>
                                <div class="existing-files-container mb-3"></div>
                                <div class="new-files-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-top-0 pt-0 mt-3">
                    <input type="hidden" name="hidden_id_policy" id="hidden_id_policy" />
                    <input type="hidden" name="researcher_id" id="policy_researcher_id" />
                    <input type="hidden" name="action_policy" id="action_policy" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submit_button_policy" id="submit_button_policy" class="btn btn-danger pink px-4" value="Save Data" />
                </div>
            </div>
        </form>
    </div>
</div>