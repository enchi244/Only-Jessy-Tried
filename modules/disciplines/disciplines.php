<style>
.red{ background-color: #610d0d; }
.red:hover{ background-color: #610d0d; }
</style>

<h1 class="h3 mb-4 text-gray-800">Disciplines' Management</h1>

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

<script>
$(document).ready(function(){

	var dataTable = $('#discipline_table').DataTable({
		"processing" : true,
		"serverSide" : true,
		"order" : [],
		"ajax" : {
			url:"modules/disciplines/discipline_action.php",
			type:"POST",
			data:{action:'fetch'}
		},
		"columnDefs":[ { "targets":[2], "orderable":false } ]
	});

	$('#add_discipline').click(function(){
		$('#discipline_form')[0].reset();
		$('#discipline_form').parsley().reset();
    	$('#discipline_modal_title').text('Add Data');
    	$('#discipline_action').val('Add');
    	$('#discipline_submit_button').val('Add');
    	$('#disciplineModal').modal('show');
    	$('#discipline_form_message').html('');
	});

	$('#discipline_form').parsley();

	$('#discipline_form').on('submit', function(event){
		event.preventDefault();
		if($('#discipline_form').parsley().isValid())
		{		
			$.ajax({
				url:"modules/disciplines/discipline_action.php",
				method:"POST",
				data:$(this).serialize(),
				dataType:'json',
				beforeSend:function()
				{
					$('#discipline_submit_button').attr('disabled', 'disabled').val('wait...');
				},
				success:function(data)
				{
					$('#discipline_submit_button').attr('disabled', false);
					if(data.error != '')
					{
						$('#discipline_form_message').html(data.error);
						$('#discipline_submit_button').val('Add');
					}
					else
					{
						$('#disciplineModal').modal('hide');
						$('#discipline_message').html(data.success);
						dataTable.ajax.reload();
						setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
					}
				}
			})
		}
	});

	$(document).on('click', '.edit_button', function(){
		var category_id = $(this).data('id');
		$('#discipline_form').parsley().reset();
		$('#discipline_form_message').html('');

		$.ajax({
	      	url:"modules/disciplines/discipline_action.php",
	      	method:"POST",
	      	data:{category_id:category_id, action:'fetch_single'},
	      	dataType:'JSON',
	      	success:function(data)
	      	{
	        	$('#discipline_name').val(data.category_name);
	        	$('#discipline_modal_title').text('Edit Data');
	        	$('#discipline_action').val('Edit');
	        	$('#discipline_submit_button').val('Edit');
	        	$('#disciplineModal').modal('show');
	        	$('#discipline_hidden_id').val(category_id);
	      	}
	    })
	});

	$(document).on('click', '.status_button', function(){
		var id = $(this).data('id');
    	var status = $(this).data('status');
		var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
		if(confirm("Are you sure you want to "+next_status+" it?"))
    	{
      		$.ajax({
        		url:"modules/disciplines/discipline_action.php",
        		method:"POST",
        		data:{id:id, action:'change_status', status:status, next_status:next_status},
        		success:function(data)
        		{
          			$('#discipline_message').html(data);
          			dataTable.ajax.reload();
          			setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
        		}
      		})
    	}
	});

	$(document).on('click', '.delete_button', function(){
    	var id = $(this).data('id');
    	if(confirm("Are you sure you want to remove it?"))
    	{
      		$.ajax({
        		url:"modules/disciplines/discipline_action.php",
        		method:"POST",
        		data:{id:id, action:'delete'},
        		success:function(data)
        		{
          			$('#discipline_message').html(data);
          			dataTable.ajax.reload();
          			setTimeout(function(){ $('#discipline_message').html(''); }, 5000);
        		}
      		})
    	}
  	});

});
</script>