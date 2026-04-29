<?php
include('core/rms.php');

// Define your categories based on your RDE tabs
$categories = ['research', 'publication', 'ip', 'pp', 'trainings', 'epc', 'ext'];

// We will generate 5000 fake views
$total_fake_views = 5000;

for ($i = 0; $i < $total_fake_views; $i++) {
    // 1. Pick a random category
    $type = $categories[array_rand($categories)];
    
    // 2. Pick a random item ID (Assuming IDs 1 through 20 exist for testing)
    $item_id = rand(1, 20); 
    
    // 3. Generate a random timestamp within the last 30 days
    $days_ago = rand(0, 30);
    $hours_ago = rand(0, 23);
    $minutes_ago = rand(0, 59);
    $random_date = date('Y-m-d H:i:s', strtotime("-$days_ago days -$hours_ago hours -$minutes_ago minutes"));
    
    // 4. Insert into database
    $sql = "INSERT INTO tbl_rde_views (item_id, item_type, viewed_at) VALUES ('$item_id', '$type', '$random_date')";
    mysqli_query($conn, $sql);
}

echo "Successfully injected $total_fake_views mock views!";
?>