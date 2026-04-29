<?php
// actions/fetch_subdep.php
include('../core/rms.php');
$object = new rms();

function getSafeRows($obj) {
    try {
        $result = $obj->get_result();
        if ($result && is_object($result) && method_exists($result, 'fetchAll')) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { }
    return [];
}

// Helper Function: Formats the GROUP_CONCAT file categories into clickable teleport badges
function format_file_badges($concat_categories, $researcher_id, $tab_id, $base_url) {
    if(empty($concat_categories) || trim($concat_categories) == '') return "<span class='text-muted'><i class='fas fa-times-circle'></i> None Attached</span>";
    
    $cats = array_filter(array_unique(explode('||', $concat_categories)));
    if(empty($cats)) return "<span class='text-muted'><i class='fas fa-times-circle'></i> None Attached</span>";
    
    $html = '';
    foreach($cats as $cat) {
        $safe_cat = htmlspecialchars(strtoupper(trim($cat))); 
        $html .= "<a href='{$base_url}modules/researchers/view_researcher.php?id={$researcher_id}&tab={$tab_id}' class='badge badge-info mr-1 p-2 shadow-sm text-white' style='text-decoration:none;' target='_blank'><i class='fas fa-folder-open mr-1'></i> {$safe_cat}</a> ";
    }
    return $html;
}

if(isset($_POST["action"])) {
    
    // ==============================================================
    // ACTION 1: FETCH THE LIST OF ITEMS FOR THE MODAL (WITH IDs)
    // ==============================================================
    if($_POST["action"] == "fetch_modal_details") {
        header('Content-Type: application/json'); 
        $department = addslashes($_POST["department"]);
        $type = $_POST["type"];
        $from_year = isset($_POST['from_year']) ? $_POST['from_year'] : 'all';
        $to_year = isset($_POST['to_year']) ? $_POST['to_year'] : 'all';
        
        $data = array();

        $dept_query = ($department === 'Unknown') ? "(department = 'Unknown' OR department = '' OR department IS NULL)" : "department = '$department'";
        
        function isYearMatch($row, $from_year, $to_year) {
            if ($from_year === 'all' || $to_year === 'all') return true;
            $date_cols = ['user_created_on', 'date_paper', 'started_date', 'completed_date', 'start_date', 'publication_date', 'date_applied', 'date_granted', 'date_train'];
            foreach ($date_cols as $dc) {
                if (isset($row[$dc]) && !empty(trim((string)$row[$dc]))) {
                    $val_clean = trim((string)$row[$dc]);
                    if (preg_match('/^\d{2}-\d{4}$/', $val_clean)) { $val_clean = "01-" . $val_clean; }
                    $ts = strtotime($val_clean);
                    if ($ts !== false) {
                        $record_year = date('Y', $ts);
                        if ($record_year >= $from_year && $record_year <= $to_year) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        if($type == 'researchers') {
            $object->query = "SELECT id, firstName, familyName FROM tbl_researchdata WHERE $dept_query AND status = 1";
            $dept_researchers = getSafeRows($object);

            if ($from_year === 'all' || $to_year === 'all') {
                foreach($dept_researchers as $row) {
                    $data[] = array("id" => $row["id"], "text" => $row["firstName"]." ".$row["familyName"]);
                }
            } else {
                $active_ids = [];
                $tables = ['tbl_researchconducted', 'tbl_publication', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_trainingsattended', 'tbl_extension_project_conducted'];
                
                foreach ($tables as $tbl) {
                    if (in_array($tbl, ['tbl_researchconducted', 'tbl_publication', 'tbl_paperpresentation'])) {
                        $col_table = ''; $col_fk = '';
                        if ($tbl == 'tbl_researchconducted') { $col_table = 'tbl_research_collaborators'; $col_fk = 'research_id'; }
                        if ($tbl == 'tbl_publication') { $col_table = 'tbl_publication_collaborators'; $col_fk = 'publication_id'; }
                        if ($tbl == 'tbl_paperpresentation') { $col_table = 'tbl_paper_collaborators'; $col_fk = 'paper_id'; }
                        
                        $object->query = "SELECT r.*, col.researcher_id as col_res_id FROM {$tbl} r LEFT JOIN {$col_table} col ON r.id = col.{$col_fk} WHERE r.status = 1";
                    } else {
                        $object->query = "SELECT r.* FROM {$tbl} r WHERE r.status = 1";
                    }
                    
                    foreach (getSafeRows($object) as $r) {
                        if (isYearMatch($r, $from_year, $to_year)) {
                            if (!empty($r['researcherID'])) $active_ids[$r['researcherID']] = true;
                            if (!empty($r['col_res_id'])) $active_ids[$r['col_res_id']] = true;
                        }
                    }
                }
                
                foreach ($dept_researchers as $row) {
                    if (isset($active_ids[$row['id']])) {
                        $data[] = array("id" => $row["id"], "text" => $row["firstName"]." ".$row["familyName"]);
                    }
                }
            }
        }
        else {
            $tbl = '';
            if($type == 'research_conducted') $tbl = 'tbl_researchconducted';
            if($type == 'publications') $tbl = 'tbl_publication';
            if($type == 'ip') $tbl = 'tbl_itelectualprop';
            if($type == 'paper_presentation') $tbl = 'tbl_paperpresentation';
            if($type == 'trainings') $tbl = 'tbl_trainingsattended';
            if($type == 'extension') $tbl = 'tbl_extension_project_conducted';
            
            if ($tbl !== '') {
                if (in_array($tbl, ['tbl_researchconducted', 'tbl_publication', 'tbl_paperpresentation'])) {
                    $col_table = ''; $col_fk = '';
                    if ($tbl == 'tbl_researchconducted') { $col_table = 'tbl_research_collaborators'; $col_fk = 'research_id'; }
                    if ($tbl == 'tbl_publication') { $col_table = 'tbl_publication_collaborators'; $col_fk = 'publication_id'; }
                    if ($tbl == 'tbl_paperpresentation') { $col_table = 'tbl_paper_collaborators'; $col_fk = 'paper_id'; }
                    
                    $object->query = "SELECT r.*, COALESCE(d.department, d_main.department) as calc_dept 
                                      FROM {$tbl} r 
                                      LEFT JOIN {$col_table} col ON r.id = col.{$col_fk} 
                                      LEFT JOIN tbl_researchdata d ON col.researcher_id = d.id
                                      LEFT JOIN tbl_researchdata d_main ON r.researcherID = d_main.id
                                      WHERE r.status = 1"; 
                } else {
                    $object->query = "SELECT r.*, d.department as calc_dept FROM {$tbl} r LEFT JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.status = 1"; 
                }
                
                $seen_ids = [];
                foreach(getSafeRows($object) as $row) {
                    $item_dept = !empty($row['calc_dept']) ? $row['calc_dept'] : 'Unknown';
                    
                    if ($item_dept === $department || ($department === 'Unknown' && ($item_dept === 'Unknown' || empty($item_dept)))) {
                        if (isYearMatch($row, $from_year, $to_year)) {
                            if (!isset($seen_ids[$row['id']])) {
                                $seen_ids[$row['id']] = true;
                                $text = isset($row["title"]) ? $row["title"] : 'Untitled Record';
                                $data[] = array("id" => $row["id"], "text" => $text);
                            }
                        }
                    }
                }
            }
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

        if($type == 'researchers') {
            $object->query = "SELECT * FROM tbl_researchdata WHERE id = '$id'";
            foreach(getSafeRows($object) as $row) {
                
                $html .= "<tr><th width='30%' class='bg-white'>Full Name</th><td class='font-weight-bold text-primary bg-white' style='font-size: 1.1rem;'>{$row['firstName']} {$row['middleName']} {$row['familyName']} {$row['Suffix']}</td></tr>";
                $html .= "<tr><th class='bg-white'>Department</th><td class='bg-white'>{$row['department']}</td></tr>";
                
                $so_cat = !empty($row['so_file']) ? 'Special Order' : '';
                $html .= "<tr><th class='bg-white'>Attached Files</th><td class='bg-white'>" . format_file_badges($so_cat, $id, 'personal-info', $object->base_url) . "</td></tr>";
                
                $html .= "<tr class='bg-light'><th colspan='2' class='text-center text-dark py-3' style='font-size: 1.05rem;'><i class='fas fa-folder-open text-primary mr-2'></i> Research & Project Portfolio</th></tr>";

                // 1. Research Conducted 
                $object->query = "
                    SELECT rc.title, rc.stat, rc.started_date, rc.moa_file,
                           (SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') 
                            FROM tbl_research_collaborators col 
                            JOIN tbl_researchdata d ON col.researcher_id = d.id 
                            WHERE col.research_id = rc.id AND col.researcher_id != '$id') AS co_authors,
                           (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_research_files WHERE research_id = rc.id) AS file_cats
                    FROM tbl_researchconducted rc 
                    WHERE (rc.id IN (SELECT research_id FROM tbl_research_collaborators WHERE researcher_id = '$id')
                       OR rc.researcherID = '$id') AND rc.status = 1
                ";
                $res1 = getSafeRows($object);
                $html .= "<tr><th>Research Conducted</th><td>";
                if(count($res1) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res1 as $r1) { 
                        $cats = [];
                        if(!empty($r1['file_cats'])) $cats[] = $r1['file_cats'];
                        if(!empty($r1['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'education', $object->base_url);
                        
                        $co_authors = !empty($r1['co_authors']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r1['co_authors']}</small>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r1['title'] ?: 'Untitled Project') . "</strong> <span class='badge badge-secondary ml-1 mb-1'>{$r1['stat']}</span> <br>{$moa_btn}{$co_authors}<br><small class='text-muted'>Started: {$r1['started_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No research projects found.</em></span>"; }
                $html .= "</td></tr>";

                // 2. Publications
                $object->query = "
                    SELECT pub.title, pub.journal, pub.publication_date, pub.moa_file,
                           (SELECT GROUP_CONCAT(CONCAT(d.firstName, ' ', d.familyName) SEPARATOR ', ') 
                            FROM tbl_publication_collaborators col 
                            JOIN tbl_researchdata d ON col.researcher_id = d.id 
                            WHERE col.publication_id = pub.id AND col.researcher_id != '$id') AS co_authors,
                           (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_publication_files WHERE publication_id = pub.id) AS file_cats
                    FROM tbl_publication pub 
                    WHERE (pub.id IN (SELECT publication_id FROM tbl_publication_collaborators WHERE researcher_id = '$id')
                       OR pub.researcherID = '$id') AND pub.status = 1
                ";
                $res2 = getSafeRows($object);
                $html .= "<tr><th>Publications</th><td>";
                if(count($res2) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res2 as $r2) { 
                        $cats = [];
                        if(!empty($r2['file_cats'])) $cats[] = $r2['file_cats'];
                        if(!empty($r2['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'degree', $object->base_url);
                        
                        $co_authors = !empty($r2['co_authors']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r2['co_authors']}</small>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r2['title'] ?: 'Untitled Publication') . "</strong> <br>{$moa_btn}{$co_authors}<br><small class='text-muted'>Journal: {$r2['journal']} | Published: {$r2['publication_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No publications found.</em></span>"; }
                $html .= "</td></tr>";

                // 3. Intellectual Property 
                $object->query = "SELECT title, type, date_granted, coauth, moa_file, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_ip_files WHERE ip_id = tbl_itelectualprop.id) AS file_cats FROM tbl_itelectualprop WHERE researcherID = '$id' AND status = 1";
                $res3 = getSafeRows($object);
                $html .= "<tr><th>Intellectual Property</th><td>";
                if(count($res3) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res3 as $r3) { 
                        $cats = [];
                        if(!empty($r3['file_cats'])) $cats[] = $r3['file_cats'];
                        if(!empty($r3['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'ip', $object->base_url);
                        
                        $co_authors = !empty($r3['coauth']) ? "<br><small class='text-primary'><i class='fas fa-users mr-1'></i><strong>Co-Authors:</strong> {$r3['coauth']}</small>" : "";
                        $html .= "<li class='mb-2'><strong>" . ($r3['title'] ?: 'Untitled IP') . "</strong> <span class='badge badge-info ml-1 mb-1'>{$r3['type']}</span> <br>{$moa_btn}{$co_authors}<br><small class='text-muted'>Granted: {$r3['date_granted']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No intellectual property found.</em></span>"; }
                $html .= "</td></tr>";

                // 4. Paper Presentation
                $object->query = "SELECT title, conference_title, date_paper, moa_file, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_paper_files WHERE paper_id = tbl_paperpresentation.id) AS file_cats FROM tbl_paperpresentation WHERE researcherID = '$id' AND status = 1";
                $res4 = getSafeRows($object);
                $html .= "<tr><th>Paper Presentations</th><td>";
                if(count($res4) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res4 as $r4) { 
                        $cats = [];
                        if(!empty($r4['file_cats'])) $cats[] = $r4['file_cats'];
                        if(!empty($r4['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'pp', $object->base_url);
                        
                        $html .= "<li class='mb-2'><strong>" . ($r4['title'] ?: 'Untitled Presentation') . "</strong> <br>{$moa_btn}<br><small class='text-muted'>Conference: {$r4['conference_title']} | Date: {$r4['date_paper']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No paper presentations found.</em></span>"; }
                $html .= "</td></tr>";

                // 5. Extension Projects
                $object->query = "SELECT title, status_exct, start_date, moa_file, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_extension_files WHERE extension_id = tbl_extension_project_conducted.id) AS file_cats FROM tbl_extension_project_conducted WHERE researcherID = '$id' AND status = 1";
                $res5 = getSafeRows($object);
                $html .= "<tr><th>Extension Projects</th><td>";
                if(count($res5) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res5 as $r5) { 
                        $cats = [];
                        if(!empty($r5['file_cats'])) $cats[] = $r5['file_cats'];
                        if(!empty($r5['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'epc', $object->base_url);
                        
                        $html .= "<li class='mb-2'><strong>" . ($r5['title'] ?: 'Untitled Project') . "</strong> <span class='badge badge-success ml-1 mb-1'>{$r5['status_exct']}</span> <br>{$moa_btn}<br><small class='text-muted'>Started: {$r5['start_date']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No extension projects found.</em></span>"; }
                $html .= "</td></tr>";

                // 6. Trainings Attended
                $object->query = "SELECT title, type, date_train, moa_file, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_training_files WHERE training_id = tbl_trainingsattended.id) AS file_cats FROM tbl_trainingsattended WHERE researcherID = '$id' AND status = 1";
                $res6 = getSafeRows($object);
                $html .= "<tr><th>Trainings Attended</th><td>";
                if(count($res6) > 0) {
                    $html .= "<ul class='pl-3 mb-0' style='font-size: 0.9rem;'>";
                    foreach($res6 as $r6) { 
                        $cats = [];
                        if(!empty($r6['file_cats'])) $cats[] = $r6['file_cats'];
                        if(!empty($r6['moa_file'])) $cats[] = 'Legacy MOA';
                        $moa_btn = format_file_badges(implode('||', $cats), $id, 'tra', $object->base_url);
                        
                        $html .= "<li class='mb-2'><strong>" . ($r6['title'] ?: 'Untitled Training') . "</strong> <span class='badge badge-warning ml-1 mb-1'>{$r6['type']}</span> <br>{$moa_btn}<br><small class='text-muted'>Date: {$r6['date_train']}</small></li>"; 
                    }
                    $html .= "</ul>";
                } else { $html .= "<span class='text-muted'><em>No trainings found.</em></span>"; }
                $html .= "</td></tr>";

                break; 
            }
        }
        elseif($type == 'research_conducted') {
            $object->query = "
                SELECT r.*, d.firstName, d.familyName,
                       (SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') 
                        FROM tbl_research_collaborators col 
                        JOIN tbl_researchdata d2 ON col.researcher_id = d2.id 
                        WHERE col.research_id = r.id AND col.researcher_id != r.researcherID) AS co_authors,
                       (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_research_files WHERE research_id = r.id) AS file_cats
                FROM tbl_researchconducted r 
                JOIN tbl_researchdata d ON r.researcherID = d.id 
                WHERE r.id = '$id'
            ";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                if (!empty($row['co_authors'])) { $html .= "<tr><th>Co-Authors</th><td class='text-primary font-weight-bold'><i class='fas fa-users mr-1'></i>{$row['co_authors']}</td></tr>"; }
                $html .= "<tr><th>Agenda Cluster</th><td>{$row['research_agenda_cluster']}</td></tr>";
                $html .= "<tr><th>SDGs</th><td>{$row['sdgs']}</td></tr>";
                $html .= "<tr><th>Date Started</th><td>{$row['started_date']}</td></tr>";
                $html .= "<tr><th>Date Completed</th><td>{$row['completed_date']}</td></tr>";
                $html .= "<tr><th>Funding Source</th><td>{$row['funding_source']}</td></tr>";
                $html .= "<tr><th>Approved Budget</th><td>{$row['approved_budget']}</td></tr>";
                $html .= "<tr><th>Status</th><td><span class='badge badge-secondary'>{$row['stat']}</span></td></tr>";
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'education', $object->base_url) . "</td></tr>";
                break;
            }
        }
        elseif($type == 'publications') {
            $object->query = "
                SELECT r.*, d.firstName, d.familyName,
                       (SELECT GROUP_CONCAT(CONCAT(d2.firstName, ' ', d2.familyName) SEPARATOR ', ') 
                        FROM tbl_publication_collaborators col 
                        JOIN tbl_researchdata d2 ON col.researcher_id = d2.id 
                        WHERE col.publication_id = r.id AND col.researcher_id != r.researcherID) AS co_authors,
                       (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_publication_files WHERE publication_id = r.id) AS file_cats
                FROM tbl_publication r 
                JOIN tbl_researchdata d ON r.researcherID = d.id 
                WHERE r.id = '$id'
            ";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                if (!empty($row['co_authors'])) { $html .= "<tr><th>Co-Authors</th><td class='text-primary font-weight-bold'><i class='fas fa-users mr-1'></i>{$row['co_authors']}</td></tr>"; }
                $html .= "<tr><th>Journal</th><td>{$row['journal']}</td></tr>";
                $html .= "<tr><th>Vol / Issue No.</th><td>{$row['vol_num_issue_num']}</td></tr>";
                $html .= "<tr><th>ISSN / ISBN</th><td>{$row['issn_isbn']}</td></tr>";
                $html .= "<tr><th>Indexing</th><td>{$row['indexing']}</td></tr>";
                $html .= "<tr><th>Start Date</th><td>{$row['start']}</td></tr>";
                $html .= "<tr><th>End Date</th><td>{$row['end']}</td></tr>";
                $html .= "<tr><th>Publication Date</th><td>{$row['publication_date']}</td></tr>";
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'degree', $object->base_url) . "</td></tr>";
                break;
            }
        }
        elseif($type == 'ip') {
            $object->query = "SELECT r.*, d.firstName, d.familyName, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_ip_files WHERE ip_id = r.id) AS file_cats FROM tbl_itelectualprop r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Co-Authors</th><td>{$row['coauth']}</td></tr>";
                $html .= "<tr><th>Type</th><td>{$row['type']}</td></tr>";
                $html .= "<tr><th>Date Applied</th><td>{$row['date_applied']}</td></tr>";
                $html .= "<tr><th>Date Granted</th><td>{$row['date_granted']}</td></tr>";
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'ip', $object->base_url) . "</td></tr>";
                break;
            }
        }
        elseif($type == 'paper_presentation') {
            $object->query = "SELECT r.*, d.firstName, d.familyName, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_paper_files WHERE paper_id = r.id) AS file_cats FROM tbl_paperpresentation r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
            foreach(getSafeRows($object) as $row) {
                $html .= "<tr><th width='35%'>Title</th><td class='font-weight-bold'>{$row['title']}</td></tr>";
                $html .= "<tr><th>Lead Proponent</th><td>{$row['firstName']} {$row['familyName']}</td></tr>";
                $html .= "<tr><th>Conference Title</th><td>{$row['conference_title']}</td></tr>";
                $html .= "<tr><th>Conference Venue</th><td>{$row['conference_venue']}</td></tr>";
                $html .= "<tr><th>Conference Organizer</th><td>{$row['conference_organizer']}</td></tr>";
                $html .= "<tr><th>Date of Paper</th><td>{$row['date_paper']}</td></tr>";
                $html .= "<tr><th>Type</th><td>{$row['type']}</td></tr>";
                $html .= "<tr><th>Discipline</th><td>{$row['discipline']}</td></tr>";
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'pp', $object->base_url) . "</td></tr>";
                break;
            }
        }
        elseif($type == 'trainings') {
            $object->query = "SELECT r.*, d.firstName, d.familyName, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_training_files WHERE training_id = r.id) AS file_cats FROM tbl_trainingsattended r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
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
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'tra', $object->base_url) . "</td></tr>";
                break;
            }
        }
        elseif($type == 'extension') {
            $object->query = "SELECT r.*, d.firstName, d.familyName, (SELECT GROUP_CONCAT(file_category SEPARATOR '||') FROM tbl_extension_files WHERE extension_id = r.id) AS file_cats FROM tbl_extension_project_conducted r JOIN tbl_researchdata d ON r.researcherID = d.id WHERE r.id = '$id'";
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
                
                $cats = [];
                if(!empty($row['file_cats'])) $cats[] = $row['file_cats'];
                if(!empty($row['moa_file'])) $cats[] = 'Legacy MOA';
                $html .= "<tr><th>Attached Files</th><td>" . format_file_badges(implode('||', $cats), $row['researcherID'], 'epc', $object->base_url) . "</td></tr>";
                break;
            }
        }
        
        $html .= '</tbody></table>';
        echo $html;
        exit;
    }
}
?>