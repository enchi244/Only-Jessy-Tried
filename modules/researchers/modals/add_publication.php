<div id="publicationModal" class="modal fade" data-backdrop="static" tabindex="-1" aria-labelledby="publicationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" id="publication_form">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modal_title">Add Publication</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <span id="form_message"></span>

          <div class="form-group">
            <label for="title_pub">Title</label>
            <input type="text" name="title_pub" id="title_pub" class="form-control" placeholder="Enter publication title" required />
          </div>

          <div class="form-group">
            <label for="journal">Journal</label>
            <input type="text" name="journal" id="journal" class="form-control" placeholder="Enter journal name" required />
          </div>

          <div class="form-group">
            <label for="vol_num_issue_num">Volume and Issue Number</label>
            <input type="text" name="vol_num_issue_num" id="vol_num_issue_num" class="form-control" placeholder="Enter volume and issue number" required />
          </div>

          <div class="form-group">
            <label for="issn_isbn">ISSN/ISBN</label>
            <input type="text" name="issn_isbn" id="issn_isbn" class="form-control" placeholder="Enter ISSN or ISBN number" required />
          </div>

          <div class="form-group">
            <label for="indexing">Indexing</label>
            <input type="text" name="indexing" id="indexing" class="form-control" placeholder="Enter indexing details (e.g., Scopus, Web of Science)" required />
          </div>

          <div class="form-group">
            <label for="publication_date">Publication Date</label>
            <input type="date" name="publication_date" id="publication_date" class="form-control" required />
          </div>
        </div>
        
        <div class="modal-footer">
          <input type="hidden" name="hidden_researcherID" id="hidden_researcherID" />
          <input type="hidden" name="hidden_publicationID" id="hidden_publicationID" />
          <input type="hidden" name="action_publication" id="action_publication" value="Add" />
          <input type="submit" name="submit_button_publication" id="submit_button_publication" class="btn btn-danger pink" value="Add" />
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>