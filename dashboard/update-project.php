<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Error log helper
function logError($message) {
    $logFile = __DIR__ . '/../logs/error_log.txt';
    $date = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

// Validate project ID
if (!isset($_POST['project_name']) || !isset($_POST['construction_cost'])) {
    $_SESSION['error'] = "Invalid form submission.";
    header("Location: projects.php");
    exit;
}

$project_id          = intval($_POST['id'] ?? 0);
$project_name        = trim($_POST['project_name']);
$local_authority_id  = intval($_POST['local_authority_id']);
$construction_cost   = floatval($_POST['construction_cost']);
$project_start_date  = $_POST['project_start_date'];
$project_end_date    = $_POST['project_end_date'];
$cess_amount         = floatval($_POST['cess_amount']);
$district_id         = intval($_POST['district_id']);
$taluka_id           = intval($_POST['taluka_id']);
$village_id          = intval($_POST['village_id']);
$pin_code            = $_POST['pin_code'];
$project_address     = trim($_POST['project_address']);

// Work orders
$work_order_ids      = $_POST['work_order_id'] ?? [];
$work_order_numbers  = $_POST['work_order_number'] ?? [];
$work_order_dates    = $_POST['work_order_date'] ?? [];
$work_order_amounts  = $_POST['work_order_amount'] ?? [];
$work_order_manager  = $_POST['work_order_manager_id'] ?? [];
$work_order_engineer = $_POST['work_order_engineer_id'] ?? [];
$work_order_employer = $_POST['work_order_employer_id'] ?? [];

// Validate work order total
$totalWorkOrderAmount = array_sum(array_map('floatval', $work_order_amounts));
if ($totalWorkOrderAmount > $construction_cost) {
    $_SESSION['error'] = "Total work order amount cannot exceed construction cost.";
    header("Location: edit-project.php?id=" . $project_id);
    exit;
}

try {
    $conn->begin_transaction();

    // Update project
    $stmt = $conn->prepare("UPDATE projects SET 
        project_name=?, local_authority_id=?, construction_cost=?, project_start_date=?, project_end_date=?, cess_amount=?, 
        district_id=?, taluka_id=?, village_id=?, pin_code=?, project_address=?, updated_at=NOW() 
        WHERE id=?");
    $stmt->bind_param("sidssdiisssi", 
        $project_name, $local_authority_id, $construction_cost, $project_start_date, $project_end_date, $cess_amount,
        $district_id, $taluka_id, $village_id, $pin_code, $project_address, $project_id
    );
    if (!$stmt->execute()) {
        throw new Exception("Failed to update project: " . $stmt->error);
    }
    $stmt->close();

    // Handle work orders
    $uploadDir = "../uploads/work_orders/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    foreach ($work_order_numbers as $i => $number) {
        $wo_id = intval($work_order_ids[$i] ?? 0);
        $wo_date = null;
        if (!empty($work_order_dates[$i])) {
            $wo_date = date("Y-m-d", strtotime($work_order_dates[$i]));
        }
        $wo_amount = floatval($work_order_amounts[$i] ?? 0);
        $wo_manager = intval($work_order_manager[$i] ?? 0);
        $wo_engineer = intval($work_order_engineer[$i] ?? 0);
        $wo_employer = intval($work_order_employer[$i] ?? 0);

        // Handle file upload
        $approvalLetter = null;
        if (isset($_FILES['work_order_approval_letter']['name'][$i]) && $_FILES['work_order_approval_letter']['error'][$i] === UPLOAD_ERR_OK) {
            $filename = time() . "_" . basename($_FILES['work_order_approval_letter']['name'][$i]);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['work_order_approval_letter']['tmp_name'][$i], $targetPath)) {
                $approvalLetter = $filename;
            }
        }

        if ($wo_id > 0) {
            // Update existing work order
            $sql = "UPDATE project_work_orders SET work_order_number=?, work_order_date=?, work_order_amount=?, 
                        manager_id=?, engineer_id=?, employer_id=?";
            if ($approvalLetter) {
                $sql .= ", work_order_approval_letter=?";
            }
            $sql .= " WHERE id=? AND project_id=?";
            if ($approvalLetter) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiisiii", $number, $wo_date, $wo_amount, $wo_manager, $wo_engineer, $wo_employer, $approvalLetter, $wo_id, $project_id);
            } else {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiisii", $number, $wo_date, $wo_amount, $wo_manager, $wo_engineer, $wo_employer, $wo_id, $project_id);
            }
            if (!$stmt->execute()) {
                throw new Exception("Failed to update work order: " . $stmt->error);
            }
            $stmt->close();
        } else {
            // Insert new work order
            $stmt = $conn->prepare("INSERT INTO project_work_orders 
                (project_id, work_order_number, work_order_date, work_order_amount, manager_id, engineer_id, employer_id, work_order_approval_letter, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issdiiss", $project_id, $number, $wo_date, $wo_amount, $wo_manager, $wo_engineer, $wo_employer, $approvalLetter);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert work order: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    $conn->commit();
    $_SESSION['success'] = "Project updated successfully.";
    header("Location: edit-project.php?id=" . $project_id);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    logError("Project Update Failed: " . $e->getMessage());
    $_SESSION['error'] = "Project update failed. Please check logs.";
    header("Location: edit-project.php?id=" . $project_id);
    exit;
}
