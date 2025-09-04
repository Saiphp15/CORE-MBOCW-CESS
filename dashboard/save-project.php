<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic Project Information
    $project_name = $_POST['project_name'];
    $project_description = $_POST['project_description'] ?? null; // Added project_description, not required but good practice
    
    

    $construction_cost = $_POST['construction_cost'];
    $project_start_date = $_POST['project_start_date'];
    $project_end_date = $_POST['project_end_date'];
    $cess_amount = $_POST['cess_amount'];
    $state_id = $_POST['state_id'] ?? null; // Assuming these come from AJAX
    $district_id = $_POST['district_id'] ?? null;
    $taluka_id = $_POST['taluka_id'] ?? null;
    $village_id = $_POST['village_id'] ?? null;
    $pin_code = $_POST['pin_code'];
    $project_address = $_POST['project_address'];
    $status = 'Pending';
    $created_by = $_SESSION['user_id'];
    $updated_by = $_SESSION['user_id'];



    // Work Order Details (Arrays)
    $work_order_numbers = $_POST['work_order_number'];
    $work_order_date = $_POST['work_order_date'];
    $work_order_amounts = $_POST['work_order_amount'];
    // Assuming 'work_order_cess_amount' is not a form field but calculated, or it's a hidden field like in your form
    $work_order_cess_amounts = $_POST['work_order_cess_amount'];
    $work_order_approval_letters = $_FILES['work_order_approval_letter'];
    $work_order_manager_ids = $_POST['work_order_manager_id'];
    $work_order_engineer_ids = $_POST['work_order_engineer_id'];
    $work_order_employer_ids = $_POST['work_order_employer_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        $localAuthorityUsers = $conn->prepare("SELECT id,local_authority_id FROM local_authorities_users WHERE user_id = ? AND is_active = 1 LIMIT 1");
        $localAuthorityUsers->bind_param("i", $_SESSION['user_id']);
        $localAuthorityUsers->execute();
        $localAuthorityUsersResult = $localAuthorityUsers->get_result();
        if ($localAuthorityUsersResult->num_rows === 0) {
            throw new Exception("User is not associated with any Local Authority.");
        }
        $localAuthorityUsersRow = $localAuthorityUsersResult->fetch_assoc();
        $user_id = $localAuthorityUsersRow['id'];
        $local_authority_id = $localAuthorityUsersRow['local_authority_id'];

        // Fetch category and type IDs based on local authority
        $auth_stmt = $conn->prepare("SELECT type_id, authority_department_id, authority_subdepartment_id FROM local_authorities WHERE id = ?");
        $auth_stmt->bind_param("i", $local_authority_id);
        $auth_stmt->execute();
        $auth_result = $auth_stmt->get_result();
        if ($auth_result->num_rows === 0) {
            throw new Exception("Invalid Local Authority selected.");
        }
        $auth_row = $auth_result->fetch_assoc();
        $type_id = $auth_row['type_id'];
        $authority_department_id = $auth_row['authority_department_id'];
        $authority_subdepartment_id = $auth_row['authority_subdepartment_id'];

        // Validate required fields
        if (empty($project_name) || empty($local_authority_id) || empty($construction_cost) || empty($project_start_date) || empty($project_end_date) || empty($pin_code) || empty($project_address)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Validate work order details
        if (empty($work_order_numbers) || empty($work_order_date) || empty($work_order_amounts) || count($work_order_numbers) !== count($work_order_date) || count($work_order_numbers) !== count($work_order_amounts)) {
            throw new Exception("Please provide valid work order details.");
        }

        // Calculate total work order amount and validate against construction cost
        $total_work_order_amount = array_sum(array_map('floatval', $work_order_amounts));
        if ($total_work_order_amount > floatval($construction_cost)) {
            throw new Exception("Total work order amount cannot exceed construction cost.");
        }
    
        // Step 1: Insert into projects table
        $project_stmt = $conn->prepare("INSERT INTO projects (project_name, construction_cost, project_start_date, project_end_date, cess_amount, state_id, district_id, taluka_id, village_id, pin_code, project_address, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $project_stmt->bind_param("sdssdiiiiissii",
            $project_name,
            $construction_cost,
            $project_start_date,
            $project_end_date,
            $cess_amount,
            $state_id,
            $district_id,
            $taluka_id,
            $village_id,
            $pin_code,
            $project_address,
            $status,
            $created_by,
            $updated_by
        );
        $project_stmt->execute();

        if ($project_stmt->affected_rows === 0) {
            throw new Exception("Failed to insert project: " . $project_stmt->error);
        }

        $project_id = $project_stmt->insert_id;

        // Step 2: Fetch codes for category, type, and authority
        $cat_res = $conn->query("SELECT UPPER(LEFT(name,3)) AS code FROM authority_departments WHERE id = $authority_department_id");
        $type_res = $conn->query("SELECT UPPER(LEFT(name,3)) AS code FROM authority_subdepartments WHERE id = $authority_subdepartment_id");
        $auth_res = $conn->query("SELECT UPPER(LEFT(name,3)) AS code FROM local_authorities WHERE id = $local_authority_id");

        $cat_code = ($cat_res->num_rows > 0) ? $cat_res->fetch_assoc()['code'] : "CAT";
        $type_code = ($type_res->num_rows > 0) ? $type_res->fetch_assoc()['code'] : "TYP";
        $auth_code = ($auth_res->num_rows > 0) ? $auth_res->fetch_assoc()['code'] : "LOC";

        // Step 3: Generate Project ID
        $generated_project_id = $cat_code . $type_code . $auth_code . $project_id;

        // Step 4: Update project_id in projects table
        $update_stmt = $conn->prepare("UPDATE projects SET project_id = ? WHERE id = ?");
        $update_stmt->bind_param("si", $generated_project_id, $project_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows === 0) {
            throw new Exception("Failed to update project_id: " . $update_stmt->error);
        }

        // Step 2: Loop through and insert into project_work_orders table
        $total_work_orders = count($work_order_numbers);
        if ($total_work_orders > 0) {
            $target_dir = "../uploads/work_orders/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            for ($i = 0; $i < $total_work_orders; $i++) {
                $work_order_number = $work_order_numbers[$i];
                $work_order_date = null;
                if (!empty($work_order_dates[$i])) {
                    $work_order_date = date("Y-m-d", strtotime($work_order_dates[$i]));
                }
                $work_order_amount = $work_order_amounts[$i];
                $work_order_cess_amount = $work_order_amounts[$i] * 0.01; // Assuming 1% cess amount calculation
                $work_order_gst_cess_amount = $work_order_cess_amount; // This seems to be the Cess amount + GST on Cess (2.5%)
                $work_order_administrative_cost = $work_order_gst_cess_amount * 0.01;
                $work_order_effective_cess_amount = $work_order_gst_cess_amount - $work_order_administrative_cost;
                $work_order_employer_id = $work_order_employer_ids[$i];
                $work_order_manager_id = $work_order_manager_ids[$i];
                $work_order_engineer_id = $work_order_engineer_ids[$i];
                $work_order_status = 'Pending';
                
                // Handle file upload for each work order
                $work_order_approval_letter = '';
                if (isset($work_order_approval_letters['name'][$i]) && !empty($work_order_approval_letters['name'][$i])) {
                    $filename = time() . "_" . basename($work_order_approval_letters["name"][$i]);
                    $target_file = $target_dir . $filename;

                    if (move_uploaded_file($work_order_approval_letters["tmp_name"][$i], $target_file)) {
                        $work_order_approval_letter = $filename;
                    } else {
                        throw new Exception("Failed to upload file for work order: " . $work_order_approval_letters['name'][$i]);
                    }
                }

                $work_order_stmt = $conn->prepare("INSERT INTO project_work_orders (project_id, work_order_number, work_order_date, work_order_amount, work_order_cess_amount, work_order_gst_cess_amount, work_order_administrative_cost, work_order_effective_cess_amount, work_order_approval_letter, employer_id, manager_id, engineer_id, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $work_order_stmt->bind_param("issdddddsiiisii",
                    $project_id,
                    $work_order_number,
                    $work_order_date,
                    $work_order_amount,
                    $work_order_cess_amount,
                    $work_order_gst_cess_amount,
                    $work_order_administrative_cost,
                    $work_order_effective_cess_amount,
                    $work_order_approval_letter,
                    $work_order_employer_id,
                    $work_order_manager_id,
                    $work_order_engineer_id,
                    $work_order_status,
                    $created_by,
                    $updated_by
                );
                $work_order_stmt->execute();

                if ($work_order_stmt->affected_rows === 0) {
                    throw new Exception("Failed to insert work order: " . $work_order_stmt->error);
                }
            }
        }

        // Commit transaction if all inserts were successful
        $conn->commit();
        $_SESSION['success'] = "Project and its work orders saved successfully.";

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in save-project.php: " . $e->getMessage());
        $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
    }

    header("Location: add-project.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: add-project.php");
    exit;
}
?>