<?php
 $street_id = (int)$_POST['street_id'];
    $numbers = trim($_POST['numbers']);
    $stmt = $pdo->prepare("INSERT INTO location_street_house_numbers (street_id, numbers) VALUES (?, ?)");
    $stmt->execute([$street_id, $numbers]);
    echo json_encode(['status' => 'success']);
    exit;
	
?>
