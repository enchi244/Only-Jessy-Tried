<?php
include('../../core/rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}

$object->query = "
    SELECT * FROM user_table
    WHERE user_id = '".$_SESSION["user_id"]."'
    ";
$result = $object->get_result();

include('../../includes/header.php');
?>
<style>     
.pink{ background-color: #f23e5d; }
.pink:hover{ background-color: #e32747; }
</style>         

<form method="post" id="profile_form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            <span id="message"></span>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="row">
                        <div class="col">
                            <h6 class="m-0 font-weight-bold text-primary">User Profile</h6>
                        </div>
                        <div class="col" align="right">
                            <input type="hidden" name="action" value="profile" />
                            <button type="submit" name="edit_button" id="edit_button" class="btn btn-danger pink btn-sm"><i class="fas fa-edit"></i> &nbsp; Update</button>
                            &nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-maxlength="175" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required data-parsley-maxlength="12" data-parsley-type="integer" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="text" name="user_email" id="user_email" class="form-control" required data-parsley-maxlength="175" data-parsley-type="email" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="user_password" id="user_password" class="form-control" required data-parsley-maxlength="16" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Select Profile Image</label><br />
                        <input type="file" class="form-control" name="user_image" id="user_image" />
                        <span class="text-muted" style="font-style: italic;">Only .jpg, .png file allowed for upload</span><br><br>
                        <span id="uploaded_image"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include('../../includes/footer.php'); ?>

<script>
$(document).ready(function(){

    <?php foreach($result as $row) { ?>
        $('#user_name').val("<?php echo $row['user_name']; ?>");
        $('#user_contact_no').val("<?php echo $row['user_contact_no']; ?>");
        $('#user_email').val("<?php echo $row['user_email']; ?>");
        $('#user_password').val("<?php echo $row['user_password']; ?>");
        
        <?php if($row["user_profile"] != '') { ?>
            // Make sure the image previews correctly on load using base_url
            $('#uploaded_image').html('<img src="<?php echo $object->base_url . $row["user_profile"]; ?>" class="img-thumbnail" width="100" /><input type="hidden" name="hidden_user_profile" value="<?php echo $row["user_profile"]; ?>" />');
        <?php } ?>
    <?php } ?>

    $('#user_image').change(function(){
        var extension = $('#user_image').val().split('.').pop().toLowerCase();
        if(extension != '') {
            if(jQuery.inArray(extension, ['png','jpg']) == -1) {
                alert("Invalid Image File");
                $('#user_image').val('');
                return false;
            }
        }
    });

    $('#profile_form').parsley();

	$('#profile_form').on('submit', function(event){
		event.preventDefault();
		if($('#profile_form').parsley().isValid())
		{		
			$.ajax({
				url: "../users/user_action.php", // <-- FIXED: Points to the correct folder now!
				method: "POST",
				data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
				beforeSend: function() {
					$('#edit_button').attr('disabled', 'disabled');
					$('#edit_button').html('wait...');
				},
				success: function(data) {
					$('#edit_button').attr('disabled', false);
                    $('#edit_button').html('<i class="fas fa-edit"></i> &nbsp; Update');

                    $('#user_name').val(data.user_name);
                    $('#user_contact_no').val(data.user_contact_no);
                    $('#user_email').val(data.user_email);
                    $('#user_password').val(data.user_password);
                    $('#user_profile_name').text(data.user_name);
                    
                    if(data.user_profile != '') {
                        var full_image_url = '<?php echo $object->base_url; ?>' + data.user_profile;
                        $('#uploaded_image').html('<img src="'+full_image_url+'" class="img-thumbnail" width="100" /><input type="hidden" name="hidden_user_profile" value="'+data.user_profile+'" />');
                        $('#user_profile_image').attr('src', full_image_url);
                    }
						
                    $('#message').html(data.success);
					setTimeout(function(){ $('#message').html(''); }, 5000);
				},
                error: function(xhr) {
                    $('#edit_button').attr('disabled', false);
                    $('#edit_button').html('<i class="fas fa-edit"></i> &nbsp; Update');
                    alert("Error communicating with server. Check console for details.");
                    console.log(xhr.responseText);
                }
			});
		}
	});
});
</script>