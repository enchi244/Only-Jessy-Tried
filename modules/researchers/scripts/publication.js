// Load Publication Table
function loadPublicationTab(researcherID) {
    $('#publication_form').parsley();
    if ($.fn.dataTable.isDataTable('#publication_table')) {
        $('#publication_table').DataTable().clear().destroy();
    }

    var publicationTable = $('#publication_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "actions/publication_action.php",
            type: "POST",
            data: { rid: researcherID, action_publication: 'fetch' }
        },
        "columnDefs": [{ "targets": [0], "orderable": false }],
    });
    return publicationTable;
}

// Handle Tab Switching
$('#degree-tab').on('shown.bs.tab', function () {
    var id = $('#hidden_id_rd').val(); 
    loadPublicationTab(id); 
});

// Handle Form Submit
$('#publication_form').on('submit', function(event) {
    event.preventDefault();
    if ($('#publication_form').parsley().isValid()) {
        $.ajax({
            url: "actions/publication_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#submit_button_publication').attr('disabled', 'disabled').val('Wait...');
            },
            success: function(data) {
                $('#submit_button_publication').attr('disabled', false);
                if(data.error != '') {
                    $('#form_message').html(data.error);
                    $('#submit_button_publication').val('Add');
                } else {
                    $('#publicationModal').modal('hide');
                    var Svalue = $('#action_publication').val();
                    if (Svalue == "Add") {
                        Swal.fire({ title: 'Added!', text: 'The publication has been successfully added.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    } else {
                        Swal.fire({ title: 'Updated!', text: 'The publication has been successfully updated.', icon: 'success', timer: 600, showConfirmButton: false, customClass: { confirmButton: 'btn-success' } });
                    }
                    var researcherID = $('#researcherModala').data('id');  
                    loadPublicationTab(researcherID);
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            }
        });
    }
});

$('#publicationModal').on('hidden.bs.modal', function() {
    if ($('.modal.show').length > 0) { $('body').addClass('modal-open'); }
    $('#researcherModala .modal-body').scrollTop(0);
});

// Add New Publication
$('#add_publication').click(function () {
    $('#publication_form')[0].reset();  
    $('#publication_form').parsley().reset();  
    $('#modal_title').text('Add Publication');  
    $('#action_publication').val('Add');
    var rid = $('#researcherModala').data('id');  
    $('#hidden_researcherID').val(rid); 
    $('#submit_button_publication').val('Add');
    $('#publicationModal').modal('show');  
    $('#form_message').html('');
});

// Edit Existing Publication
$(document).on('click', '.edit_button_publication', function(){
    var publicationID = $(this).data('id');
    $('#publication_form')[0].reset();
    $('#publication_form').parsley().reset();
    $('#form_message').html('');

    $.ajax({
        url:"actions/publication_action.php",
        method:"POST",
        data:{publicationID: publicationID, action_publication: 'fetch_single'},
        dataType:'JSON',
        success:function(data) {
// Ultimate Date Parser: Crash-proof, handles integers, nulls, slashes, and raw years
            function parseLegacyDate(val) {
                if (!val || val === 'null' || val === '0000-00-00') return '';
                
                // Safely cast to string to prevent crashes, clean spaces, and swap slashes to dashes
                let str = String(val).trim().replace(/\//g, '-');
                let parts = str.split('-');
                
                // Case 1: Just a Year (e.g. "2022" or 2022) -> HTML5 requires YYYY-MM-DD
                if (parts.length === 1 && parts[0].length === 4) {
                    return `${parts[0]}-01-01`;
                }
                
                // Case 2: 2 parts (MM-YYYY or YYYY-MM)
                if (parts.length === 2) {
                    if (parts[1].length === 4) return `${parts[1]}-${parts[0].padStart(2, '0')}-01`;
                    if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2, '0')}-01`;
                }
                
                // Case 3: 3 parts (MM-DD-YYYY or YYYY-MM-DD)
                if (parts.length === 3) {
                    if (parts[2].length === 4) return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
                    if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                }
                
                // Fallback: If it's completely unreadable, return empty so it doesn't break the input
                return ''; 
            }
            
            $('#title_pub').val(data.title);
            $('#start').val(parseLegacyDate(data.start));
            $('#end').val(parseLegacyDate(data.end));
            $('#journal').val(data.journal);
            $('#vol_num_issue_num').val(data.vol_num_issue_num);
            $('#issn_isbn').val(data.issn_isbn);
            $('#indexing').val(data.indexing);
            $('#publication_date').val(parseLegacyDate(data.publication_date));
            $('#modal_title').text('Edit Publication');
            $('#action_publication').val('Edit');
            $('#submit_button_publication').val('Edit');
            $('#publicationModal').modal('show');
            $('#hidden_publicationID').val(publicationID);
        }
    });
});

// Delete Publication
$(document).on('click', '.delete_button_publication', function() {
    var publicationID = $(this).data('id'); 
    Swal.fire({
        title: 'Are you sure?', text: 'You will not be able to recover this record!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, keep it', reverseButtons: true, customClass: { confirmButton: 'btn-danger', cancelButton: 'btn-secondary' }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "actions/publication_action.php",
                method: "POST",
                data: {publicationID: publicationID, action_publication: 'delete'},
                success: function(data) {
                    Swal.fire({ title: 'Deleted!', text: 'The publication has been successfully deleted.', icon: 'success', timer: 600, showConfirmButton: false });
                    var researcherID = $('#researcherModala').data('id');
                    loadPublicationTab(researcherID);  
                }
            });
        }
    });
});