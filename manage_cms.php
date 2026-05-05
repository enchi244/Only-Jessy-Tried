<?php
// 1. Prevent headers already sent errors
ob_start(); 

// 2. Include core class
include('core/rms.php'); 

// 3. Instantiate the object exactly like dashboard.php
$object = new rms();
if(!$object->is_login()) {
    header("Location: " . $object->base_url . "/");
    exit; 
}

// 4. Create direct DB connection for CMS module
$conn = @new mysqli("localhost", "root", "", "rms");
if ($conn->connect_error) { die("Database connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

$msg = "";
// =========================================================
// FETCH EDIT NEWS DATA
// =========================================================
$edit_news = null;

if(isset($_GET['edit_news'])) {
    $id = (int)$_GET['edit_news'];
    $res = $conn->query("SELECT * FROM tbl_cms_news WHERE id=$id");
    if($res && $res->num_rows > 0) {
        $edit_news = $res->fetch_assoc();
    }
}
$upload_dir = 'uploads/cms/';
if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }


// =========================================================
// FORM ACTIONS
// =========================================================
// 1. Save Text
if (isset($_POST['save_text'])) {
    $about = $conn->real_escape_string($_POST['about_text']);
    $mission = $conn->real_escape_string($_POST['mission_text']);
    $vision = $conn->real_escape_string($_POST['vision_text']);
    $core = $conn->real_escape_string($_POST['core_responsibilities']);
    
    $conn->query("UPDATE tbl_cms_about SET about_text='$about', mission_text='$mission', vision_text='$vision', core_responsibilities='$core' WHERE id=1");
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Landing Page text updated!</div>";
}

// 2. Add Carousel Image
if (isset($_POST['add_carousel']) && !empty($_FILES['c_image']['name'])) {
    $filename = "car_" . time() . "_" . basename($_FILES["c_image"]["name"]);
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES["c_image"]["tmp_name"], $target)) {
        $alt = $conn->real_escape_string($_POST['alt_text']);
        $conn->query("INSERT INTO tbl_cms_carousel (image_path, alt_text) VALUES ('$target', '$alt')");
        $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Carousel image added!</div>";
    }
}

// 3. Add News
if (isset($_POST['add_news']) && !empty($_FILES['n_image']['name'])) {
    $filename = "news_" . time() . "_" . basename($_FILES["n_image"]["name"]);
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES["n_image"]["tmp_name"], $target)) {
        $title = $conn->real_escape_string($_POST['title']);
        $summary = $conn->real_escape_string($_POST['summary']);
        $content = $conn->real_escape_string($_POST['content']);
        $date = $conn->real_escape_string($_POST['date_published']);
        $conn->query("INSERT INTO tbl_cms_news (title, summary, content, image_path, date_published) VALUES ('$title', '$summary', '$content', '$target', '$date')");
        $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ News article published!</div>";
    }
}

// 3B. Update News
if (isset($_POST['update_news'])) {
    $id = (int)$_POST['news_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $summary = $conn->real_escape_string($_POST['summary']);
    $content = $conn->real_escape_string($_POST['content']);
    $date = $conn->real_escape_string($_POST['date_published']);

    if (!empty($_FILES['n_image']['name'])) {
        $filename = "news_" . time() . "_" . basename($_FILES["n_image"]["name"]);
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["n_image"]["tmp_name"], $target)) {
            $old = $conn->query("SELECT image_path FROM tbl_cms_news WHERE id=$id")->fetch_assoc();
            if($old && file_exists($old['image_path'])) { unlink($old['image_path']); }

            $conn->query("UPDATE tbl_cms_news 
                SET title='$title', summary='$summary', content='$content', image_path='$target', date_published='$date'
                WHERE id=$id");
        }
    } else {
        $conn->query("UPDATE tbl_cms_news 
            SET title='$title', summary='$summary', content='$content', date_published='$date'
            WHERE id=$id");
    }
    $msg = "<div class='alert alert-info alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✏️ News updated successfully!</div>";
}

// 4. Save Header Logos
if (isset($_POST['upload_logos'])) {
    $logos_to_process = ['logo_wmsu', 'logo_rdec', 'logo_third'];
    foreach ($logos_to_process as $logo_key) {
        if (isset($_FILES[$logo_key]) && $_FILES[$logo_key]['error'] == 0) {
            $filename = $logo_key . "_" . time() . "_" . basename($_FILES[$logo_key]["name"]);
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES[$logo_key]["tmp_name"], $target)) {
                $conn->query("UPDATE tbl_site_settings SET setting_value='$target' WHERE setting_key='$logo_key'");
            }
        }
    }
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Logos updated successfully!</div>";
}

// 5. Save Category Names
if (isset($_POST['save_category_names'])) {
    $cats = ['cat_research', 'cat_publication', 'cat_ext', 'cat_ip', 'cat_pp', 'cat_train'];
    foreach($cats as $c) {
        if(isset($_POST[$c])) {
            $val = $conn->real_escape_string($_POST[$c]);
            $conn->query("UPDATE tbl_site_settings SET setting_value='$val' WHERE setting_key='$c'");
        }
    }
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Report Categories renamed successfully!</div>";
}

// 5B. Save RDE Database Settings
if (isset($_POST['save_rde_settings'])) {
    $display_full = isset($_POST['display_full_abstract']) ? '1' : '0';
    
    // Update the value. If the setting doesn't exist yet, insert it securely.
    $conn->query("UPDATE tbl_site_settings SET setting_value='$display_full' WHERE setting_key='display_full_abstract'");
    if ($conn->affected_rows == 0) {
        $conn->query("INSERT IGNORE INTO tbl_site_settings (setting_key, setting_value) VALUES ('display_full_abstract', '$display_full')");
    }
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ RDE Settings updated successfully!</div>";
}

// 5C. Save Footer Map Setting
if (isset($_POST['save_map'])) {
    $raw_input = $_POST['google_map_embed'];
    $map_embed = '';

    // Automatically extract the link if they pasted the entire <iframe ...> code
    if (preg_match('/src="([^"]+)"/', $raw_input, $matches)) {
        $map_embed = $matches[1]; // Grab only the URL inside src="..."
    } else {
        // If they just pasted the raw link, use it directly
        $map_embed = trim($raw_input);
    }

    $map_embed = $conn->real_escape_string($map_embed);
    
    $conn->query("UPDATE tbl_site_settings SET setting_value='$map_embed' WHERE setting_key='google_map_embed'");
    if ($conn->affected_rows == 0) {
        $conn->query("INSERT IGNORE INTO tbl_site_settings (setting_key, setting_value) VALUES ('google_map_embed', '$map_embed')");
    }
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Footer Map updated successfully!</div>";
}

// 5D. NEW: Save Footer Contacts
if (isset($_POST['save_footer_contacts'])) {
    $address = $conn->real_escape_string($_POST['footer_address']);
    $contact = $conn->real_escape_string($_POST['footer_contact']);
    $email = $conn->real_escape_string($_POST['footer_email']);
    
    $settings = [
        'footer_address' => $address,
        'footer_contact' => $contact,
        'footer_email' => $email
    ];
    
    foreach ($settings as $key => $val) {
        $conn->query("UPDATE tbl_site_settings SET setting_value='$val' WHERE setting_key='$key'");
        if ($conn->affected_rows == 0) {
            $conn->query("INSERT IGNORE INTO tbl_site_settings (setting_key, setting_value) VALUES ('$key', '$val')");
        }
    }
    $msg = "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✅ Footer Contacts updated successfully!</div>";
}

// 6. Deletions
if (isset($_GET['del_car'])) {
    $id = (int)$_GET['del_car'];
    $res = $conn->query("SELECT image_path FROM tbl_cms_carousel WHERE id=$id");
    if($row = $res->fetch_assoc()) { @unlink($row['image_path']); }
    $conn->query("DELETE FROM tbl_cms_carousel WHERE id=$id");
    header("Location: manage_cms.php?msg=deleted"); exit;
}
if (isset($_GET['del_news'])) {
    $id = (int)$_GET['del_news'];
    $res = $conn->query("SELECT image_path FROM tbl_cms_news WHERE id=$id");
    if($row = $res->fetch_assoc()) { @unlink($row['image_path']); }
    $conn->query("DELETE FROM tbl_cms_news WHERE id=$id");
    header("Location: manage_cms.php?msg=deleted"); exit;
}
if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>🗑️ Item successfully deleted.</div>";
}

// Fetch Text
$cms_text = ['about_text'=>'', 'mission_text'=>'', 'vision_text'=>'', 'core_responsibilities'=>''];
$res_txt = $conn->query("SELECT * FROM tbl_cms_about WHERE id=1");
if ($res_txt && $res_txt->num_rows > 0) { $cms_text = $res_txt->fetch_assoc(); }

// Fetch Current Settings (Logos & Categories)
$site_settings = [];
$set_res = $conn->query("SELECT setting_key, setting_value FROM tbl_site_settings");
if($set_res) {
    while($r = $set_res->fetch_assoc()) {
        $site_settings[$r['setting_key']] = $r['setting_value'];
    }
}
// Default fallbacks for categories if they are somehow empty
$cat_research = !empty($site_settings['cat_research']) ? $site_settings['cat_research'] : 'Research Conducted';
$cat_pub = !empty($site_settings['cat_publication']) ? $site_settings['cat_publication'] : 'Publications';
$cat_ext = !empty($site_settings['cat_ext']) ? $site_settings['cat_ext'] : 'Extension Projects';
$cat_ip = !empty($site_settings['cat_ip']) ? $site_settings['cat_ip'] : 'Intellectual Property';
$cat_pp = !empty($site_settings['cat_pp']) ? $site_settings['cat_pp'] : 'Paper Presentations';
$cat_train = !empty($site_settings['cat_train']) ? $site_settings['cat_train'] : 'Trainings Attended';


// =========================================================
// INCLUDE TEMPLATE HEADER
// =========================================================
include('includes/header.php'); 
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
    <h1 class="h3 mb-0 text-gray-800">Website Content Management</h1>
</div>

<?php echo $msg; ?>

<div class="row">

    <!-- ============================================== -->
    <!-- LEFT COLUMN -->
    <!-- ============================================== -->
    <div class="col-lg-6 mb-4">
        
        <!-- CARD: LANDING PAGE TEXT -->
        <div class="card border-left-primary shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-align-left mr-1"></i> 1. Landing Page Text</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-group">
                        <label class="font-weight-bold">About the Office</label>
                        <textarea class="form-control bg-light" name="about_text" rows="4" required><?php echo htmlspecialchars($cms_text['about_text']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Our Mission</label>
                        <textarea class="form-control bg-light" name="mission_text" rows="3" required><?php echo htmlspecialchars($cms_text['mission_text']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Our Vision</label>
                        <textarea class="form-control bg-light" name="vision_text" rows="3" required><?php echo htmlspecialchars($cms_text['vision_text']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Core Responsibilities</label>
                        <textarea class="form-control bg-light" name="core_responsibilities" rows="5" placeholder="List responsibilities, one per line." required><?php echo htmlspecialchars($cms_text['core_responsibilities'] ?? ''); ?></textarea>
                        <small class="text-muted">Use numbers or bullet points for a clean list format.</small>
                    </div>
                    <button type="submit" name="save_text" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Save Text Updates</button>
                </form>
            </div>
        </div>

        <!-- CARD: CAROUSEL IMAGES -->
        <div class="card border-left-success shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-images mr-1"></i> 2. Carousel Images</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php" enctype="multipart/form-data" class="mb-4">
                    <div class="form-row align-items-center">
                        <div class="col-sm-5 my-1">
                            <input type="file" class="form-control-file border p-1 rounded" name="c_image" accept="image/*" required>
                        </div>
                        <div class="col-sm-5 my-1">
                            <input type="text" class="form-control form-control-sm" name="alt_text" placeholder="Image Description" required>
                        </div>
                        <div class="col-sm-2 my-1">
                            <button type="submit" name="add_carousel" class="btn btn-success btn-sm w-100"><i class="fas fa-upload"></i></button>
                        </div>
                    </div>
                </form>

                <h6 class="font-weight-bold small text-muted text-uppercase mb-2">Active Images</h6>
                <div class="row">
                    <?php 
                    $cars = $conn->query("SELECT * FROM tbl_cms_carousel ORDER BY id DESC");
                    if($cars && $cars->num_rows > 0) {
                        while($img = $cars->fetch_assoc()) {
                            echo '<div class="col-4 text-center mb-3">
                                <img src="'.$img['image_path'].'" class="img-fluid rounded border shadow-sm mb-1" style="height:90px; object-fit:cover; width:100%;">
                                <a href="manage_cms.php?del_car='.$img['id'].'" class="btn btn-danger btn-sm w-100 py-0" onclick="return confirm(\'Delete this image?\')"><i class="fas fa-trash"></i></a>
                            </div>';
                        }
                    } else { echo "<div class='col-12'><p class='text-muted small'>No images uploaded yet.</p></div>"; }
                    ?>
                </div>
            </div>
        </div>

        <!-- CARD: NEWS & UPDATES -->
        <div class="card border-left-info shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-newspaper mr-1"></i> 3. News & Updates</h6>
                <button class="btn btn-sm btn-info" data-toggle="collapse" data-target="#newsFormCollapse">Add Article</button>
            </div>
            <div class="card-body">
                
                <div class="collapse mb-4 <?php echo $edit_news ? 'show' : ''; ?>" id="newsFormCollapse">
                    <form method="POST" action="manage_cms.php" enctype="multipart/form-data" class="bg-light p-3 rounded border">

                            <input type="hidden" name="news_id" value="<?php echo $edit_news['id'] ?? ''; ?>">

                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label class="small font-weight-bold">Article Title</label>
                                    <input type="text" class="form-control form-control-sm" name="title"
                                    value="<?php echo $edit_news['title'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold">Date Published</label>
                                    <input type="date" class="form-control form-control-sm" name="date_published"
                                    value="<?php echo $edit_news['date_published'] ?? date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Feature Image</label>
                                <input type="file" class="form-control-file border p-1 rounded bg-white" name="n_image" accept="image/*">
                                <?php if($edit_news && !empty($edit_news['image_path'])): ?>
                                    <small class="text-muted d-block mt-1">Current Image:</small>
                                    <img src="<?php echo $edit_news['image_path']; ?>" style="width:80px; height:80px; object-fit:cover;">
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Short Summary</label>
                                <textarea class="form-control form-control-sm" name="summary" rows="2" required><?php echo $edit_news['summary'] ?? ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Full Content</label>
                                <textarea class="form-control form-control-sm" name="content" rows="4" required><?php echo $edit_news['content'] ?? ''; ?></textarea>
                            </div>

                            <button type="submit"
                                name="<?php echo $edit_news ? 'update_news' : 'add_news'; ?>"
                                class="btn btn-info btn-sm btn-block">

                                <i class="fas fa-paper-plane"></i>
                                <?php echo $edit_news ? 'Update Article' : 'Publish Article'; ?>
                            </button>
                            <?php if($edit_news): ?>
                            <a href="manage_cms.php" class="btn btn-secondary btn-sm mt-2 w-100">
                                Cancel Editing
                            </a>
                            <?php endif; ?>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover text-sm">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%">Date</th>
                                <th width="60%">Article</th>
                                <th width="20%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $news_q = $conn->query("SELECT id, title, image_path, date_published FROM tbl_cms_news ORDER BY date_published DESC");
                            if($news_q && $news_q->num_rows > 0) {
                                while($n = $news_q->fetch_assoc()) {
                                    echo '<tr>
                                        <td class="align-middle">'.date("M d, Y", strtotime($n['date_published'])).'</td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <img src="'.$n['image_path'].'" class="img-thumbnail mr-3 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                                <span class="font-weight-bold">'.htmlspecialchars($n['title']).'</span>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="manage_cms.php?edit_news='.$n['id'].'" class="btn btn-primary btn-sm mb-1"><i class="fas fa-edit"></i></a>
                                            <a href="manage_cms.php?del_news='.$n['id'].'" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this article?\')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>';
                                }
                            } else { echo "<tr><td colspan='3' class='text-center text-muted'>No news published yet.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>


    <!-- ============================================== -->
    <!-- RIGHT COLUMN -->
    <!-- ============================================== -->
    <div class="col-lg-6 mb-4">
        
        <!-- CARD: MANAGE CATEGORIES -->
        <div class="card border-left-secondary shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-tags mr-1"></i> Rename Report Categories</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Research</label>
                            <input type="text" class="form-control form-control-sm" name="cat_research" value="<?php echo htmlspecialchars($cat_research); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Publications</label>
                            <input type="text" class="form-control form-control-sm" name="cat_publication" value="<?php echo htmlspecialchars($cat_pub); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Extension</label>
                            <input type="text" class="form-control form-control-sm" name="cat_ext" value="<?php echo htmlspecialchars($cat_ext); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Intellectual Property</label>
                            <input type="text" class="form-control form-control-sm" name="cat_ip" value="<?php echo htmlspecialchars($cat_ip); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Paper Presentation</label>
                            <input type="text" class="form-control form-control-sm" name="cat_pp" value="<?php echo htmlspecialchars($cat_pp); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">Module: Trainings</label>
                            <input type="text" class="form-control form-control-sm" name="cat_train" value="<?php echo htmlspecialchars($cat_train); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="save_category_names" class="btn btn-secondary btn-sm btn-block mt-2"><i class="fas fa-save"></i> Save Category Names</button>
                </form>
            </div>
        </div>

        <!-- CARD: RDE DATABASE SETTINGS -->
        <div class="card border-left-info shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-database mr-1"></i> RDE Database Settings</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="display_full_abstract" name="display_full_abstract" value="1" <?php echo (isset($site_settings['display_full_abstract']) && $site_settings['display_full_abstract'] == '1') ? 'checked' : ''; ?>>
                            <label class="custom-control-label font-weight-bold text-dark" for="display_full_abstract">Display Full Abstract on Hover</label>
                        </div>
                        <small class="text-muted d-block mt-2">If enabled, the sneak peek tooltip in the public database will expand to show the entire abstract instead of truncating it to 6 lines.</small>
                    </div>
                    <button type="submit" name="save_rde_settings" class="btn btn-info btn-sm btn-block font-weight-bold"><i class="fas fa-save"></i> Save RDE Settings</button>
                </form>
            </div>
        </div>

        <!-- CARD: MANAGE LOGOS -->
        <div class="card border-left-warning shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-images mr-1"></i> Header Logos</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <label class="small font-weight-bold">WMSU Logo</label>
                            <div class="mb-2">
                                <img src="<?php echo !empty($site_settings['logo_wmsu']) ? $site_settings['logo_wmsu'] : '../img/placeholder.png'; ?>" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: contain;">
                            </div>
                            <input type="file" name="logo_wmsu" class="form-control-file small" accept="image/*">
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <label class="small font-weight-bold">RDEC Logo</label>
                            <div class="mb-2">
                                <img src="<?php echo !empty($site_settings['logo_rdec']) ? $site_settings['logo_rdec'] : '../img/placeholder.png'; ?>" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: contain;">
                            </div>
                            <input type="file" name="logo_rdec" class="form-control-file small" accept="image/*">
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <label class="small font-weight-bold">3rd Logo</label>
                            <div class="mb-2">
                                <img src="<?php echo !empty($site_settings['logo_third']) ? $site_settings['logo_third'] : '../img/placeholder.png'; ?>" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: contain;">
                            </div>
                            <input type="file" name="logo_third" class="form-control-file small" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" name="upload_logos" class="btn btn-warning btn-sm btn-block text-dark font-weight-bold"><i class="fas fa-save"></i> Save Logos</button>
                </form>
            </div>
        </div>

        <!-- CARD: FOOTER MAP SETTINGS -->
        <div class="card border-left-danger shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-map-marked-alt mr-1"></i> Footer Map Location</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Google Maps Embed URL or Code</label>
                        <textarea class="form-control bg-light" name="google_map_embed" rows="3" placeholder="Paste the Google Maps Embed code or URL here..."><?php echo htmlspecialchars($site_settings['google_map_embed'] ?? ''); ?></textarea>
                        <small class="text-muted d-block mt-2">
                            <b>How to get the link:</b> Go to Google Maps > Search for your location > Click "Share" > Click "Embed a map" > Click "Copy HTML". You can paste the <b>entire iframe code</b> here, and the system will automatically extract what it needs!
                        </small>
                    </div>
                    <button type="submit" name="save_map" class="btn btn-danger btn-sm btn-block font-weight-bold"><i class="fas fa-save"></i> Save Map Location</button>
                </form>
            </div>
        </div>

        <!-- CARD: FOOTER CONTACT SETTINGS -->
        <div class="card border-left-danger shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-address-card mr-1"></i> Footer Contact Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Office Address</label>
                        <textarea class="form-control bg-light" name="footer_address" rows="2" placeholder="e.g. Statistics and Data Management Unit&#10;Western Mindanao State University" required><?php echo htmlspecialchars($site_settings['footer_address'] ?? "Statistics and Data Management Unit\nWestern Mindanao State University"); ?></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Contact Numbers</label>
                        <input type="text" class="form-control bg-light" name="footer_contact" placeholder="e.g. (062) 123-4567, 0912-345-6789" value="<?php echo htmlspecialchars($site_settings['footer_contact'] ?? "(contact number here), (telephone number here)"); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Email Address</label>
                        <input type="text" class="form-control bg-light" name="footer_email" placeholder="e.g. sdmu@wmsu.edu.ph" value="<?php echo htmlspecialchars($site_settings['footer_email'] ?? "(email here)"); ?>" required>
                    </div>
                    <button type="submit" name="save_footer_contacts" class="btn btn-danger btn-sm btn-block font-weight-bold"><i class="fas fa-save"></i> Save Contact Info</button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php 
// Only leave the closing tags for the elements opened INSIDE manage_cms.php
include('includes/footer.php'); 
ob_end_flush();
?>