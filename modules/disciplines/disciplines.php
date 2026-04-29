<style>
.red{ background-color: #610d0d; }
.red:hover{ background-color: #610d0d; }
</style>

<span id="discipline_message"></span>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">Disciplines List</h6>
            </div>
            <div class="col" align="right">
                <button type="button" name="add_category" id="add_discipline" class="btn btn-danger pink btn-sm"><i class="fas fa-plus"> Add Discipline</i></button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">	
            <table class="table table-bordered" id="discipline_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Discipline Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="disciplineModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="discipline_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="discipline_modal_title">Add Discipline</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="discipline_form_message"></span>
                    <div class="form-group">
                        <label>Discipline</label>
                        <input type="text" name="category_name" id="discipline_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id" id="discipline_hidden_id" />
                    <input type="hidden" name="action" id="discipline_action" value="Add" />
                    <input type="submit" name="submit" id="discipline_submit_button" class="btn btn-danger pink" value="Add" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>