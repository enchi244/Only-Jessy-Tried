<div class="card shadow mb-4 border-left-danger">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-danger">Archived</h6>
        
        <select id="module_filter" class="form-control form-control-sm border-danger text-danger" style="width: auto; font-weight:bold;">
            <option value="tbl_researchdata">Researcher Profiles</option>
            <option value="tbl_researchconducted">Research Conducted</option>
            <option value="tbl_publication">Publications</option>
            <option value="tbl_itelectualprop">Intellectual Property</option>
            <option value="tbl_paperpresentation">Paper Presentations</option>
            <option value="tbl_extension_project_conducted">Extension Projects</option>
            <option value="tbl_trainingsattended">Trainings Attended</option>
        </select>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="archive_table" width="100%" cellspacing="0">
                <thead class="bg-gray-200 text-gray-800">
                    <tr>
                        <th width="20%">Record Type</th>
                        <th width="65%">Name / Title</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>