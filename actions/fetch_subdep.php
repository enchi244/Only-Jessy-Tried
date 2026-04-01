<?php
include('../core/rms.php');
$object = new rms();

// Anti-Crash Helper Function
function getSafeRows($obj) {
    try {
        $result = $obj->get_result();
        if ($result && is_object($result) && method_exists($result, 'fetchAll')) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { }
    return [];
}

if(isset($_POST["action"])) {
    
    // ==============================================================
    // ACTION 1: FETCH THE LIST OF ITEMS FOR THE MODAL (WITH IDs)
    // ==============================================================
    if($_POST["action"] == "fetch_modal_details") {
        header('Content-Type: application/json'); // FORCES VALID JSON FORMAT
        $department = addslashes($_POST["department"]);
        $type = $_POST["type"];
        $data = array();

        $dept_query = ($department === 'Unknown') ? "(department = 'Unknown' OR department = '' OR department IS NULL)" : "department = '$department'";
        $d_dept_query = ($department === 'Unknown') ? "(d.department = 'Unknown' OR d.department = '' OR d.department IS NULL)" : "d.department = '$department'";

        if($type == 'researchers') {
            $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata WHERE $dept_query";
        }
        elseif($type == 'research_conducted') {
            $object->query = "SELECT r.id, r.title FROM tbl_researchconducted r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }
        elseif($type == 'publications') {
            $object->query = "SELECT r.id, r.title FROM tbl_publication r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }
        elseif($type == 'ip') {
            $object->query = "SELECT r.id, r.title FROM tbl_itelectualprop r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }
        elseif($type == 'paper_presentation') {
            $object->query = "SELECT r.id, r.title FROM tbl_paperpresentation r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }
        elseif($type == 'trainings') {
            $object->query = "SELECT r.id, r.title FROM tbl_trainingsattended r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }
        elseif($type == 'extension') {
            $object->query = "SELECT r.id, r.title FROM tbl_extension_project_conducted r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE $d_dept_query";
        }

        foreach(getSafeRows($object) as $row) {
            $text = isset($row["title"]) ? $row["title"] : $row["firstName"]." ".$row["familyName"];
            $data[] = array("id" => $row["id"], "text" => ($text ?: 'Untitled Record'));
        }
        echo json_encode($data);
        exit;
    }


    // ==============================================================
    // ACTION 2: FETCH FULL DETAILS OF A SPECIFIC RECORD
    // ==============================================================
    if($_POST["action"] == "fetch_item_details") {
        $id = intval($_POST["id"]);
        $type = $_POST["type"];
        $html = '<table class="table table-bordered table-striped" width="100%" cellspacing="0"><tbody>';

        $doc_path = $object->base_url . "uploads/documents/";

        // =========================================================================
        // RESEARCHER PORTFOLIO
        // =========================================================================
        if($type == 'researchers') {
            $object->query = "SELECT * FROM tbl_researchdata WHERE id = '$id'";
            foreach(getSafeRows($object) as $row) {
                
                $html .= "<tr><th width='30%' class='bg-white'>Full Name</th><td class='font-weight-bold text-primary bg-white' style='font-size: 1.1rem;'>{$row['firstName']} {$row['middleName']} {$row['familyName']} {$row['Suffix']}</td></tr>";
                $html .= "<tr><th class='bg-white'>Department</th><td class='bg-white'>{$row['department']}</td></tr>";
                $html .= "<tr><th class='bg-white'>Special Order (SO)</th><td class='bg-white'>" . (!empty($row['so_file']) ? "<a href='{$doc_path}{$row['so_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View SO File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                
                $html .= "<tr class='bg-light'><th colspan='2' class='text-center text-dark py-3' style='font-size: 1.05rem;'><i class='fas fa-folder-open text-primary mr-2'></i> Research & Project Portfolio</th></tr>";

                // 1. Research Conducted 
                $object->query = "
                    SELECT rc.title, rc.stat, rc.started_date, rc.moa_file,
                           (SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') 
                            FROM tbl_research_collaborators col 
                            JOIN tbl_researchdata d ON col.researcher_id = d.id 
                            WHERE col.research_id = rc.id AND col.researcher_id != '$id') AS co_authors
                    FROM tbl_researchconducted rc 
                    WHERE rc.id IN (SELECT research_id FROM tbl_research_collaborators WHERE researcher_id = '$id')
                       OR rc.researcherID = '$id'
                ";
                $res1 = getSafeRows($object);
                $html .= "<tr><th>Research Conducted</th><td>";
                if(count($res1) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res1 as $r1) { 
                        $moa_btn = !empty($r1['moa_file']) ? "<a href='{$doc_path}{$r1['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $co_authors = !empty($r1['co_authors']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r1['co_authors']}</small>" : "";
                        
                        $html .= "<li class='mb-2'><strong>" . ($r1['title'] ?: 'Untitled Project') . "</strong> <span class='badge badge-secondary ml-1'>{$r1['stat']}</span> {$moa_btn}{$co_authors}<br><small class='text-muted'>Started: {$r1['started_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No research projects found.</em></span>"; }
                $html .= "</td></tr>";

                // 2. Publications (FIXED: Now pulls Co-Authors from tbl_publication_collaborators)
                $object->query = "
                    SELECT pub.title, pub.journal, pub.publication_date, pub.moa_file,
                           (SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') 
                            FROM tbl_publication_collaborators col 
                            JOIN tbl_researchdata d ON col.researcher_id = d.id 
                            WHERE col.publication_id = pub.id AND col.researcher_id != '$id') AS co_authors
                    FROM tbl_publication pub 
                    WHERE pub.id IN (SELECT publication_id FROM tbl_publication_collaborators WHERE researcher_id = '$id')
                       OR pub.researcherID = '$id'
                ";
                $res2 = getSafeRows($object);
                $html .= "<tr><th>Publications</th><td>";
                if(count($res2) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res2 as $r2) { 
                        $moa_btn = !empty($r2['moa_file']) ? "<a href='{$doc_path}{$r2['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $co_authors = !empty($r2['co_authors']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r2['co_authors']}</small>" : "";

                        $html .= "<li class='mb-2'><strong>" . ($r2['title'] ?: 'Untitled Publication') . "</strong> {$moa_btn}{$co_authors}<br><small class='text-muted'>Journal: {$r2['journal']} | Published: {$r2['publication_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No publications found.</em></span>"; }
                $html .= "</td></tr>";

                // 3. Intellectual Property 
                $object->query = "SELECT title, type, date_granted, moa_file, coauth FROM tbl_itelectualprop WHERE researcherID = '$id'";
                $res3 = getSafeRows($object);
                $html .= "<tr><th>Intellectual Property</th><td>";
                if(count($res3) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res3 as $r3) { 
                        $moa_btn = !empty($r3['moa_file']) ? "<a href='{$doc_path}{$r3['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $co_authors = !empty($r3['coauth']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r3['coauth']}</small>" : "";

                        $html .= "<li class='mb-2'><strong>" . ($r3['title'] ?: 'Untitled IP') . "</strong> <span class='badge badge-info ml-1'>{$r3['type']}</span> {$moa_btn}{$co_authors}<br><small class='text-muted'>Granted: {$r3['date_granted']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No intellectual property found.</em></span>"; }
                $html .= "</td></tr>";

                // 4. Paper Presentation (Left as Lead Proponent Only as requested)
                $object->query = "SELECT title, conference_title, date_paper, moa_file FROM tbl_paperpresentation WHERE researcherID = '$id'";
                $res4 = getSafeRows($object);
                $html .= "<tr><th>Paper Presentations</th><td>";
                if(count($res4) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res4 as $r4) { 
                        $moa_btn = !empty($r4['moa_file']) ? "<a href='{$doc_path}{$r4['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r4['title'] ?: 'Untitled Presentation') . "</strong> {$moa_btn}<br><small class='text-muted'>Conference: {$r4['conference_title']} | Date: {$r4['date_paper']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No paper presentations found.</em></span>"; }
                $html .= "</td></tr>";

                // 5. Extension Projects
                $object->query = "SELECT title, status_exct, start_date, moa_file FROM tbl_extension_project_conducted WHERE researcherID = '$id'";
                $res5 = getSafeRows($object);
                $html .= "<tr><th>Extension Projects</th><td>";
                if(count($res5) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res5 as $r5) { 
                        $moa_btn = !empty($r5['moa_file']) ? "<a href='{$doc_path}{$r5['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r5['title'] ?: 'Untitled Project') . "</strong> <span class='badge badge-success ml-1'>{$r5['status_exct']}</span> {$moa_btn}<br><small class='text-muted'>Started: {$r5['start_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No extension projects found.</em></span>"; }
                $html .= "</td></tr>";

                // 6. Trainings Attended
                $object->query = "SELECT title, type, date_train, moa_file FROM tbl_trainingsattended WHERE researcherID = '$id'";
                $res6 = getSafeRows($object);
                $html .= "<tr><th>Trainings Attended</th><td>";
                if(count($res6) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res6 as $r6) { 
                        $moa_btn = !empty($r6['moa_file']) ? "<a href='{$doc_path}{$r6['moa_file']}' target='_blank' class='badge badge-info ml-1 p-1'><i class='fas fa-paperclip'></i> View MOA</a>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r6['title'] ?: 'Untitled Training') . "</strong> <span class='badge badge-warning ml-1'>{$r6['type']}</span> {$moa_btn}<br><small class='text-muted'>Date: {$r6['date_train']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No trainings found.</em></span>"; }
                $html .= "</td></tr>";

                break; 
            }
        }

        // =========================================================================
        // REGULAR MODULE DETAILS
        // =========================================================================
        elseif($type == 'research_conducted') {
            $object->query = "
                SELECT r.*, d.firstName, d.familyName,
                       (SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') 
                        FROM tbl_research_collaborators col 
                        JOIN tbl_researchdata d2 ON col.researcher_id = d2.id 
                        WHERE col.research_id = r.id AND col.researcher_id != r.researcherID) AS co_authors
                FROM tbl_researchconducted r 
                JOIN tbl_researchdata d ON r.researcherID = d.id 
                WHERE r.id = '$id'
            ";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";

                if (!empty($row['co_authors'])) {
                    $html .= "<tr><th>Co-Authors</th><td class='text-primary font-weight-bold'><i class='fas fa-users mr-1'></i>{$row['co_authors']}</td></tr>";
                }

                $html .= "<tr><th>Agenda Cluster</th><td>{$row['research_agenda_cluster']}</td></tr>";
                $html .= "<tr><th>SDGs</th><td>{$row['sdgs']}</td></tr>";
                $html .= "<tr><th>Date Started</th><td>{$row['started_date']}</td></tr>";
                $html .= "<tr><th>Date Completed</th><td>{$row['completed_date']}</td></tr>";
                $html .= "<tr><th>Funding Source</th><td>{$row['funding_source']}</td></tr>";
                $html .= "<tr><th>Approved Budget</th><td>{$row['approved_budget']}</td></tr>";
                $html .= "<tr><th>Status</th><td><span class='badge badge-secondary'>{$row['stat']}</span></td></tr>";
                $html .= "<tr><th>Terminal Report</th><td>{$row['terminal_report']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        elseif($type == 'publications') {
            // FIXED: Added subquery to pull Co-Authors for the Standalone Details view
            $object->query = "
                SELECT r.*, d.firstName, d.familyName,
                       (SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') 
                        FROM tbl_publication_collaborators col 
                        JOIN tbl_researchdata d2 ON col.researcher_id = d2.id 
                        WHERE col.publication_id = r.id AND col.researcher_id != r.researcherID) AS co_authors
                FROM tbl_publication r 
                JOIN tbl_researchdata d ON r.researcherID = d.id 
                WHERE r.id = '$id'
            ";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";

                if (!empty($row['co_authors'])) {
                    $html .= "<tr><th>Co-Authors</th><td class='text-primary font-weight-bold'><i class='fas fa-users mr-1'></i>{$row['co_authors']}</td></tr>";
                }

                $html .= "<tr><th>Journal</th><td>{$row['journal']}</td></tr>";
                $html .= "<tr><th>Vol / Issue No.</th><td>{$row['vol_num_issue_num']}</td></tr>";
                $html .= "<tr><th>ISSN / ISBN</th><td>{$row['issn_isbn']}</td></tr>";
                $html .= "<tr><th>Indexing</th><td>{$row['indexing']}</td></tr>";
                $html .= "<tr><th>Start Date</th><td>{$row['start']}</td></tr>";
                $html .= "<tr><th>End Date</th><td>{$row['end']}</td></tr>";
                $html .= "<tr><th>Publication Date</th><td>{$row['publication_date']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        elseif($type == 'ip') {
            $object->query = "SELECT r.*, d.firstName, d.familyName FROM tbl_itelectualprop r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Co-Authors</th><td>{$row['coauth']}</td></tr>";
                $html .= "<tr><th>Type</th><td>{$row['type']}</td></tr>";
                $html .= "<tr><th>Date Applied</th><td>{$row['date_applied']}</td></tr>";
                $html .= "<tr><th>Date Granted</th><td>{$row['date_granted']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        elseif($type == 'paper_presentation') {
            // LEFT AS IS: Paper Presentation only shows Lead Proponent for now!
            $object->query = "SELECT r.*, d.firstName, d.familyName FROM tbl_paperpresentation r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Conference Title</th><td>{$row['conference_title']}</td></tr>";
                $html .= "<tr><th>Conference Venue</th><td>{$row['conference_venue']}</td></tr>";
                $html .= "<tr><th>Conference Organizer</th><td>{$row['conference_organizer']}</td></tr>";
                $html .= "<tr><th>Date of Paper</th><td>{$row['date_paper']}</td></tr>";
                $html .= "<tr><th>Type</th><td>{$row['type']}</td></tr>";
                $html .= "<tr><th>Discipline</th><td>{$row['discipline']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        elseif($type == 'trainings') {
            $object->query = "SELECT r.*, d.firstName, d.familyName FROM tbl_trainingsattended r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Type</th><td>{$row['type']}</td></tr>";
                $html .= "<tr><th>Venue</th><td>{$row['venue']}</td></tr>";
                $html .= "<tr><th>Date of Training</th><td>{$row['date_train']}</td></tr>";
                $html .= "<tr><th>Level</th><td>{$row['lvl']}</td></tr>";
                $html .= "<tr><th>Type of Learning Dev</th><td>{$row['type_learning_dev']}</td></tr>";
                $html .= "<tr><th>Sponsor Org</th><td>{$row['sponsor_org']}</td></tr>";
                $html .= "<tr><th>Total Hours</th><td>{$row['totnh']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        elseif($type == 'extension') {
            $object->query = "SELECT r.*, d.firstName, d.familyName FROM tbl_extension_project_conducted r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Start Date</th><td>{$row['start_date']}</td></tr>";
                $html .= "<tr><th>Completed Date</th><td>{$row['completed_date']}</td></tr>";
                $html .= "<tr><th>Funding Source</th><td>{$row['funding_source']}</td></tr>";
                $html .= "<tr><th>Approved Budget</th><td>{$row['approved_budget']}</td></tr>";
                $html .= "<tr><th>Target Beneficiaries</th><td>{$row['target_beneficiaries_communities']}</td></tr>";
                $html .= "<tr><th>Partners</th><td>{$row['partners']}</td></tr>";
                $html .= "<tr><th>Status</th><td><span class='badge badge-secondary'>{$row['status_exct']}</span></td></tr>";
                $html .= "<tr><th>Terminal Report</th><td>{$row['terminal_report']}</td></tr>";
                
                $html .= "<tr><th>Attached MOA File</th><td>" . (!empty($row['moa_file']) ? "<a href='{$doc_path}{$row['moa_file']}' class='btn btn-sm btn-info' target='_blank'><i class='fas fa-file-pdf'></i> View MOA File</a>" : "<span class='text-muted'>None Attached</span>") . "</td></tr>";
                break;
            }
        }
        
        $html .= '</tbody></table>';
        echo $html;
        exit;
    }
}
?>