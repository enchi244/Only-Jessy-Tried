<div id="publicationModal" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-xl">
    <form method="post" id="publication_form">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title">Manage Publication</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <span id="form_message"></span>

          <!-- Hidden Fields for Publication and Researcher ID -->
          <input type="hidden" id="hidden_publicationID" name="hidden_publicationID" />
          <input type="hidden" id="hidden_researcherID" name="hidden_researcherID" />

          <!-- Title -->
          <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title_pub" id="title_pub" class="form-control" placeholder="Enter publication title" required />
</div>