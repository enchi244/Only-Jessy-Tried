<?php

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
            $project_dir = dirname($_SERVER['SCRIPT_NAME']);
            $project_dir = str_replace('\\', '/', $project_dir); // Fix for Windows XAMPP
            
            // This guarantees the system always knows your exact root URL
            $this->base_url = rtrim($protocol . '://' . $host . $project_dir, '/') . '/';
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


	public function Get_total_departments()
	{
		$this->query = "
			SELECT COUNT(DISTINCT department) AS total_departments
			FROM `tbl_researchdata`
			WHERE `status` = 1
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