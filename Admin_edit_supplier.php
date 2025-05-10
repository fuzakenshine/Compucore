<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log the incoming data
        error_log("POST data received: " . print_r($_POST, true));
        
        $supplier_id = filter_var($_POST['supplier_id'], FILTER_SANITIZE_NUMBER_INT);
        $fname = htmlspecialchars(trim($_POST['fname']), ENT_QUOTES, 'UTF-8');
        $lname = htmlspecialchars(trim($_POST['lname']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $company = htmlspecialchars(trim($_POST['company']), ENT_QUOTES, 'UTF-8');

        // Debug: Log the processed data
        error_log("Processed data - ID: $supplier_id, Fname: $fname, Lname: $lname, Email: $email, Company: $company");

        // Start transaction
        $conn->begin_transaction();

        // First verify the supplier exists
        $check_stmt = $conn->prepare("SELECT PK_SUPPLIER_ID FROM supplier WHERE PK_SUPPLIER_ID = ?");
        if (!$check_stmt) {
            throw new Exception("Prepare check failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("i", $supplier_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Supplier not found with ID: " . $supplier_id);
        }

        // Update basic info
        $update_stmt = $conn->prepare("UPDATE supplier SET 
            S_FNAME = ?, 
            S_LNAME = ?, 
            EMAIL = ?, 
            COMPANY_NAME = ?,
            UPDATE_AT = CURRENT_TIMESTAMP 
            WHERE PK_SUPPLIER_ID = ?");
            
        if (!$update_stmt) {
            throw new Exception("Prepare update failed: " . $conn->error);
        }

        $update_stmt->bind_param("ssssi", 
            $fname, 
            $lname, 
            $email, 
            $company, 
            $supplier_id
        );
        
        if (!$update_stmt->execute()) {
            throw new Exception("Execute update failed: " . $update_stmt->error);
        }

        // If no rows were updated, continue without throwing an error (data may be unchanged)

        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($filetype, $allowed)) {
                $newname = 'supplier_' . uniqid() . '.' . $filetype;
                $upload_path = 'uploads/' . $newname;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Get old image filename
                    $img_stmt = $conn->prepare("SELECT SUPPLIER_IMAGE FROM supplier WHERE PK_SUPPLIER_ID = ?");
                    $img_stmt->bind_param("i", $supplier_id);
                    $img_stmt->execute();
                    $old_image = $img_stmt->get_result()->fetch_assoc()['SUPPLIER_IMAGE'];

                    // Update database with new image
                    $img_update = $conn->prepare("UPDATE supplier SET SUPPLIER_IMAGE = ? WHERE PK_SUPPLIER_ID = ?");
                    $img_update->bind_param("si", $newname, $supplier_id);
                    $img_update->execute();

                    // Delete old image if exists
                    if ($old_image && file_exists('uploads/' . $old_image)) {
                        unlink('uploads/' . $old_image);
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Verify the update was successful
        $verify_stmt = $conn->prepare("SELECT S_FNAME, S_LNAME, EMAIL, COMPANY_NAME FROM supplier WHERE PK_SUPPLIER_ID = ?");
        $verify_stmt->bind_param("i", $supplier_id);
        $verify_stmt->execute();
        $updated_data = $verify_stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'updated_data' => $updated_data
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in supplier update: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

$conn->close();

// If we get here, it's an invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();