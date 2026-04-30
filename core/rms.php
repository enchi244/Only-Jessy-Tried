<?php
// Hide minor PHP 8 warnings and notices so they don't break JSON responses
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);

//rms.php

class rms
{

    public $base_url = '';
    public $connect;
    public $query;
    public $statement;
    public $cur;

    function __construct()
    {
        try {
             $this->connect = new PDO("mysql:host=localhost;dbname=rms", "root", "");
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // ----- NEW: GLOBAL BASE URL CONFIGURATION -----
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $this->base_url = $protocol . '://' . $host . '/rms/rms/';
            // ----------------------------------------------

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    function execute($data = null)
    {
        try {
            $this->statement = $this->connect->prepare($this->query);
            if ($data) {
                $this->statement->execute($data);
            } else {
                $this->statement->execute();
            }
        } catch (PDOException $e) {
            die("Query execution failed: " . $e->getMessage());
        }
    }

    function row_count()
    {
        return $this->statement->rowCount();
    }

    function statement_result()
    {
        return $this->statement->fetchAll();
    }

    function get_result()
    {
        return $this->connect->query($this->query, PDO::FETCH_ASSOC);
    }

    function is_login()
    {
        if(isset($_SESSION['user_id']))
        {
            return true;
        }
        return false;
    }

    function is_master_user()
    {
        if(isset($_SESSION['user_type']))
        {
            if($_SESSION["user_type"] == 'Master')
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function clean_input($string)
    {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    function get_datetime()
    {
        return date("Y-m-d H:i:s",  STRTOTIME(date('h:i:sa')));
    }

    function make_avatar($character)
    {
        $path = "images/". time() . ".png";
        $image = imagecreate(200, 200);
        $red = rand(0, 255);
        $green = rand(0, 255);
        $blue = rand(0, 255);
        imagecolorallocate($image, 230, 230, 230);  
        $textcolor = imagecolorallocate($image, $red, $green, $blue);
        imagettftext($image, 100, 0, 55, 150, $textcolor, 'font/arial.ttf', $character);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }

    function Get_user_name($user_id)
    {
        $this->query = "
        SELECT * FROM user_table 
        WHERE user_id = '".$user_id."'
        ";
        $result = $this->get_result();
        foreach($result as $row)
        {
            return $row["user_name"];
        }
    }

    // =========================================================================
    // UNIVERSAL FILE UPLOAD & MANAGEMENT SYSTEM
    // =========================================================================

    public function handle_generic_files($files, $categories, $entity_id, $upload_dir, $db_path_prefix, $entity_type) {
        if(isset($files['name']) && is_array($files['name'])) {
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            foreach($files['name'] as $index => $original_name) {
                if(isset($files['error'][$index]) && $files['error'][$index] == 0 && !empty($original_name)) {
                    $category = isset($categories[$index]) ? addslashes($categories[$index]) : 'Other';
                    
                    $ext = strtolower(pathinfo(basename($original_name), PATHINFO_EXTENSION));
                    $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo(basename($original_name), PATHINFO_FILENAME));
                    $new_name = $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    
                    $target_file = $upload_dir . $new_name;
                    $db_path = addslashes($db_path_prefix . $new_name); 
                    
                    if(move_uploaded_file($files['tmp_name'][$index], $target_file)) {
                        $fname = addslashes(basename($original_name));
                        $eid = intval($entity_id);
                        
                        $this->query = "INSERT INTO tbl_rde_files (entity_type, entity_id, file_category, file_name, file_path) 
                                        VALUES ('$entity_type', '$eid', '$category', '$fname', '$db_path')";
                        $this->execute();
                    }
                }
            }
        }
    }

    public function delete_generic_file($file_id, $physical_path_prefix) {
        $fid = intval($file_id);
        $this->query = "SELECT file_path FROM tbl_rde_files WHERE id = '$fid'";
        $file_data = $this->get_result();
        
        $file_deleted = false;
        
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = $physical_path_prefix . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        
        if($file_deleted) {
            $this->query = "DELETE FROM tbl_rde_files WHERE id = '$fid'";
            $this->execute();
            return true;
        }
        return false;
    }

    public function uploadCoverPhoto($fileArray, $physical_upload_dir, $db_path_prefix) {
        if (!isset($fileArray['error']) || is_array($fileArray['error']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileArray['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return false; 
        }

        if (!file_exists($physical_upload_dir)) { 
            mkdir($physical_upload_dir, 0755, true); 
        }

        $extension = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('cover_', true) . '.' . $extension;
        $destination = rtrim($physical_upload_dir, '/') . '/' . $newFileName;

        if (move_uploaded_file($fileArray['tmp_name'], $destination)) {
            return rtrim($db_path_prefix, '/') . '/' . $newFileName; 
        }

        return false;
    }

    public function update_generic_has_files($record_id, $parent_table, $file_table, $fk_column) {
        // Deprecated: has_files column was removed in Phase 1.
        // Keeping this empty function here prevents fatal errors on unpatched modules.
        return true;
    }

    public function getRdeDisplayData($pdo, $tableName, $itemType, $dateColumn = 'id') {
        $excludeIds = [];
        $defaultImage = 'img/default_research_cover.png'; // Your default fallback image

        // 1. Get Top 1 and Next 3 Trending based on views
        $popularSql = "SELECT t.*, COUNT(v.id) as view_count 
                       FROM {$tableName} t 
                       LEFT JOIN tbl_rde_views v ON t.id = v.item_id AND v.item_type = :item_type 
                       WHERE t.status = 1
                       GROUP BY t.id 
                       ORDER BY view_count DESC, t.id DESC 
                       LIMIT 4";
                       
        $stmt = $pdo->prepare($popularSql);
        $stmt->execute(['item_type' => $itemType]);
        $popularItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $topItem = [];
        $trendingItems = [];

        if (!empty($popularItems)) {
            $topItem[] = array_shift($popularItems); // Grabs the #1 item
            $trendingItems = $popularItems;          // Grabs the #2, #3, #4 items
            
            // Save these IDs so we don't duplicate them in the "Newest" section
            $excludeIds[] = $topItem[0]['id'];
            foreach ($trendingItems as $trend) {
                $excludeIds[] = $trend['id'];
            }
        }

        // 2. Get the Newest items, excluding the ones we already grabbed above
        $newSql = "SELECT * FROM {$tableName} WHERE status = 1";
        $params = [];

        if (!empty($excludeIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $newSql .= " AND id NOT IN ($placeholders)";
            $params = $excludeIds;
        }

        $newSql .= " ORDER BY {$dateColumn} DESC";
        
        $stmtNew = $pdo->prepare($newSql);
        $stmtNew->execute($params);
        $newItems = $stmtNew->fetchAll(PDO::FETCH_ASSOC);

        // 3. APPLY DEFAULT IMAGES: Loop through all arrays and ensure cover_photo is never empty
        if (!empty($topItem[0]) && empty($topItem[0]['cover_photo'])) {
            $topItem[0]['cover_photo'] = $defaultImage;
        }
        
        foreach ($trendingItems as $key => $item) {
            if (empty($item['cover_photo'])) $trendingItems[$key]['cover_photo'] = $defaultImage;
        }
        
        foreach ($newItems as $key => $item) {
            if (empty($item['cover_photo'])) $newItems[$key]['cover_photo'] = $defaultImage;
        }

        // Return the perfectly formatted data
        return [
            'top' => $topItem,
            'trending' => $trendingItems,
            'new' => $newItems
        ];
    }

    // =========================================================================

    public function Get_total_departments()
    {
        $this->query = "
            SELECT COUNT(*) AS total_departments
            FROM `product_category_table`
            WHERE `category_status` = 'Enable'
        ";
    
        $result = $this->get_result(); 
    
        foreach ($result as $row)
        {
            if (!is_null($row["total_departments"]))
            {
                return number_format($row["total_departments"]);
            }
            else
            {
                return '0';
            }
        }
        return '0';
    }
    
    // NEW FUNCTION: Pulls true count directly from the master discipline table
    public function Get_total_disciplines()
    {
        $this->query = "
            SELECT COUNT(*) AS total_disciplines
            FROM `tbl_majordiscipline`
        ";
    
        $result = $this->get_result(); 
    
        foreach ($result as $row)
        {
            if (!is_null($row["total_disciplines"]))
            {
                return number_format($row["total_disciplines"]);
            }
        }
        return '0';
    }

    public function Get_department_program_count()
    {
        $this->query = "
            SELECT department, COUNT(department) AS programcount
            FROM `tbl_researchdata`
            WHERE `status` = 1
            GROUP BY department
            ORDER BY department
        ";
    
        $result = $this->get_result();
    
        $departments = [];
    
        foreach ($result as $row)
        {
            if (!is_null($row["programcount"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'programcount' => number_format($row['programcount'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => $row['department'],
                    'programcount' => '0'
                ];
            }
        }
    
        return $departments;
    }

    public function Get_total_department_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM `tbl_researchconducted`
            JOIN tbl_researchdata ON tbl_researchconducted.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result(); 
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }

    public function Get_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt 
             FROM `tbl_researchconducted`
             JOIN tbl_researchdata ON tbl_researchconducted.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result(); 
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

    public function Get_total_chart3_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM tbl_publication
            JOIN tbl_researchdata ON tbl_publication.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result(); 
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }


    public function Get_chart3_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt 
             FROM tbl_publication
             JOIN tbl_researchdata ON tbl_publication.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result();
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

    public function Get_total_chart4_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM tbl_itelectualprop
            JOIN tbl_researchdata ON tbl_itelectualprop.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result(); 
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }


    public function Get_chart4_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, 
            COUNT(tbl_researchdata.department) AS countt 
             FROM tbl_itelectualprop
             JOIN tbl_researchdata ON tbl_itelectualprop.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result();
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

    public function Get_total_chart5_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM tbl_paperpresentation
            JOIN tbl_researchdata ON tbl_paperpresentation.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result();
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }

    public function Get_chart5_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt 
             FROM tbl_paperpresentation
             JOIN tbl_researchdata ON tbl_paperpresentation.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result(); 
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

    public function Get_total_chart6_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM tbl_trainingsattended
            JOIN tbl_researchdata ON tbl_trainingsattended.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result();
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }

    public function Get_chart6_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt 
             FROM tbl_trainingsattended
             JOIN tbl_researchdata ON tbl_trainingsattended.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result();
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

    public function Get_total_chart7_count()
    {
        $this->query = "
            SELECT COUNT(tbl_researchdata.department) AS countt
            FROM tbl_extension_project_conducted
            JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id
             WHERE tbl_researchdata.status = 1
        ";
        
        $result = $this->get_result(); 
        
        foreach ($result as $row)
        {
            if (!is_null($row["countt"]))
            {
                return number_format($row["countt"]);
            }
            else
            {
                return '0';
            }
        }
    }

    public function Get_chart7_department_list()
    {
        $this->query = "
            SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt 
             FROM tbl_extension_project_conducted
             JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id 
             WHERE tbl_researchdata.status = 1
             GROUP BY tbl_researchdata.department;
        ";
        
        $result = $this->get_result(); 
        
        $departments = [];
        
        foreach ($result as $row)
        {
            if (!is_null($row["department"]) && !is_null($row["countt"]))
            {
                $departments[] = [
                    'department' => $row['department'],
                    'countt' => number_format($row['countt'])
                ];
            }
            else
            {
                $departments[] = [
                    'department' => 'Unknown',
                    'countt' => '0'
                ];
            }
        }
        
        return $departments;
    }

}

$conn = mysqli_connect("localhost", "root", "", "rms");

//Users Count ---------------------------------------------------------------
$userscount = "SELECT COUNT(user_id) FROM user_table";
$resultusers   = mysqli_query($conn, $userscount);

if (mysqli_num_rows($resultusers) > 0) {
    while($row = mysqli_fetch_assoc($resultusers)) {
        $userstotal=$row["COUNT(user_id)"];
    }
} else {
    $userstotal = 0;
}

//publi Count ---------------------------------------------------------------
$publicationcount = "SELECT COUNT(researcherID) FROM tbl_publication where researcherID!=''";
$resultpublication   = mysqli_query($conn, $publicationcount);

if (mysqli_num_rows($resultpublication) > 0) {
    while($row = mysqli_fetch_assoc($resultpublication)) {
        $publicationtotal=$row["COUNT(researcherID)"];
    }
} else {
    $publicationtotal = 0;
}

//reser Count ---------------------------------------------------------------
$researchercount = "SELECT COUNT(DISTINCT familyName) FROM tbl_researchdata WHERE status = 1;";
$resultresearcher   = mysqli_query($conn, $researchercount);

if (mysqli_num_rows($resultresearcher) > 0) {
    while($row = mysqli_fetch_assoc($resultresearcher)) {
        $researchertotal=$row["COUNT(DISTINCT familyName)"];
    }
} else {
    $researchertotal = 0;
}

//train Count ---------------------------------------------------------------
$trainingsattendedcount = "SELECT COUNT(researcherID) FROM tbl_trainingsattended where researcherID!=''";
$resulttrainingsattended   = mysqli_query($conn, $trainingsattendedcount);

if (mysqli_num_rows($resulttrainingsattended) > 0) {
    while($row = mysqli_fetch_assoc($resulttrainingsattended)) {
        $trainingsattendedtotal=$row["COUNT(researcherID)"];
    }
} else {
    $trainingsattendedtotal = 0;
}

//inte Count ---------------------------------------------------------------
$itelectualpropcount = "SELECT COUNT(researcherID) FROM tbl_itelectualprop where researcherID!=''";
$resultitelectualprop   = mysqli_query($conn, $itelectualpropcount);

if (mysqli_num_rows($resultitelectualprop) > 0) {
    while($row = mysqli_fetch_assoc($resultitelectualprop)) {
        $itelectualproptotal=$row["COUNT(researcherID)"];
    }
} else {
    $itelectualproptotal = 0;
}

//papr Count ---------------------------------------------------------------
$papercount = "SELECT COUNT(researcherID) FROM tbl_paperpresentation where researcherID!=''";
$resultpaper   = mysqli_query($conn, $papercount);

if (mysqli_num_rows($resultpaper) > 0) {
    while($row = mysqli_fetch_assoc($resultpaper)) {
        $papertotal=$row["COUNT(researcherID)"];
    }
} else {
    $papertotal = 0;
}
?>