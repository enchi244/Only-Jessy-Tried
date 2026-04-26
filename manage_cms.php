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
// SAFE AUTO-INSTALLER FOR DATABASE TABLES
// =========================================================
$conn->query("CREATE TABLE IF NOT EXISTS `tbl_cms_about` (
  `id` int(11) NOT NULL AUTO_INCREMENT, `about_text` text NOT NULL, `mission_text` text NOT NULL, `vision_text` text NOT NULL, PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$chk = $conn->query("SELECT id FROM tbl_cms_about WHERE id=1");
if($chk && $chk->num_rows == 0) {
    $conn->query("INSERT INTO `tbl_cms_about` (`id`, `about_text`, `mission_text`, `vision_text`) VALUES (1, 'Under the Research Development and Evaluation Center (RDEC), the SDMU collects, manages, and analyzes data within the University.', 'SDMU is committed to providing appropriate statistical information and analyses while ensuring that the R&D data of the University are properly stored and managed.', 'SDMU aspires to become the leading hub of statistical expertise and data integrity within Western Mindanao State University.')");
}

$conn->query("CREATE TABLE IF NOT EXISTS `tbl_cms_carousel` (
  `id` int(11) NOT NULL AUTO_INCREMENT, `image_path` varchar(255) NOT NULL, `alt_text` varchar(255) NOT NULL, PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS `tbl_cms_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `summary` text NOT NULL, `content` longtext NOT NULL, `image_path` varchar(255) NOT NULL, `date_published` date NOT NULL, PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// =========================================================
// FORM ACTIONS
// =========================================================
// 1. Save Text
if (isset($_POST['save_text'])) {
    $about = $conn->real_escape_string($_POST['about_text']);
    $mission = $conn->real_escape_string($_POST['mission_text']);
    $vision = $conn->real_escape_string($_POST['vision_text']);
    $conn->query("UPDATE tbl_cms_about SET about_text='$about', mission_text='$mission', vision_text='$vision' WHERE id=1");
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

    // If new image uploaded
    if (!empty($_FILES['n_image']['name'])) {

        $filename = "news_" . time() . "_" . basename($_FILES["n_image"]["name"]);
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["n_image"]["tmp_name"], $target)) {

            // delete old image
            $old = $conn->query("SELECT image_path FROM tbl_cms_news WHERE id=$id")->fetch_assoc();
            if($old && file_exists($old['image_path'])) {
                unlink($old['image_path']);
            }

            $conn->query("UPDATE tbl_cms_news 
                SET title='$title', summary='$summary', content='$content', image_path='$target', date_published='$date'
                WHERE id=$id");
        }

    } else {
        // No image change
        $conn->query("UPDATE tbl_cms_news 
            SET title='$title', summary='$summary', content='$content', date_published='$date'
            WHERE id=$id");
    }

    $msg = "<div class='alert alert-info alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>✏️ News updated successfully!</div>";
}

// 4. Deletions
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
$cms_text = ['about_text'=>'', 'mission_text'=>'', 'vision_text'=>''];
$res_txt = $conn->query("SELECT * FROM tbl_cms_about WHERE id=1");
if ($res_txt && $res_txt->num_rows > 0) { $cms_text = $res_txt->fetch_assoc(); }

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
    <div class="col-lg-6 mb-4">
        
        <div class="card border-left-primary shadow h-100 mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-align-left mr-1"></i> 1. Landing Page Text</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_cms.php">
                    <div class="form-group">
                        <label class="font-weight-bold">About the Office</label>
                        <textarea class="form-control bg-light" name="about_text" rows="5" required><?php echo htmlspecialchars($cms_text['about_text']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Our Mission</label>
                        <textarea class="form-control bg-light" name="mission_text" rows="3" required><?php echo htmlspecialchars($cms_text['mission_text']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Our Vision</label>
                        <textarea class="form-control bg-light" name="vision_text" rows="3" required><?php echo htmlspecialchars($cms_text['vision_text']); ?></textarea>
                    </div>
                    <button type="submit" name="save_text" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Save Text Updates</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        
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

        <div class="card border-left-info shadow">
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
                            // THE FIX: image_path is now pulled from the database!
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
</div>

<?php 
// Only leave the closing tags for the elements opened INSIDE manage_cms.php
include('includes/footer.php'); 
ob_end_flush();
?>