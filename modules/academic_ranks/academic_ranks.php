<div class="card shadow mb-4 border-left-primary">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-award mr-2"></i>Academic Ranks List</h6>
        <button type="button" name="add_rank" id="add_rank" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"></i> Add Rank</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="rank_table" width="100%" cellspacing="0">
                <thead class="bg-gray-200 text-gray-800">
                    <tr>
                        <th>Academic Rank Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ACADEMIC RANK MODAL START -->
<div id="rankModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="rank_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title_rank">Add Academic Rank</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message_rank"></span>
                    <div class="form-group">
                        <label>Academic Rank Name <span class="text-danger">*</span></label>
                        <input type="text" name="rank_name" id="rank_name" class="form-control" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id_rank" id="hidden_id_rank" />
                    <input type="hidden" name="action" id="action_rank" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input type="submit" name="submit" id="submit_button_rank" class="btn btn-danger pink" value="Add" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- ACADEMIC RANK MODAL END -->

