<?php
session_start();
include 'db_connect.php';

// Function to get notifications for a customer
function getNotifications($customer_id) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE FK_CUSTOMER_ID = ? ORDER BY CREATED_AT DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to mark notifications as read
function markAsRead($notification_id) {
    global $conn;
    $sql = "UPDATE notifications SET STATUS = 'read' WHERE PK_NOTIFICATION_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

// Function to mark all notifications as read
function markAllAsRead($customer_id) {
    global $conn;
    $sql = "UPDATE notifications SET STATUS = 'read' WHERE FK_CUSTOMER_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    return $stmt->execute();
}

// Function to get unread notification count
function getUnreadCount($customer_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE FK_CUSTOMER_ID = ? AND STATUS = 'unread'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $response = ['success' => false];
    
    if (!isset($_SESSION['customer_id'])) {
        echo json_encode($response);
        exit;
    }
    
    $customer_id = $_SESSION['customer_id'];
    
    switch ($_GET['action']) {
        case 'get_notifications':
            $notifications = getNotifications($customer_id);
            $notifications_array = [];
            while ($notification = $notifications->fetch_assoc()) {
                $notifications_array[] = [
                    'id' => $notification['PK_NOTIFICATION_ID'],
                    'message' => $notification['MESSAGE'],
                    'type' => $notification['TYPE'],
                    'status' => $notification['STATUS'],
                    'created_at' => $notification['CREATED_AT']
                ];
            }
            $response = [
                'success' => true,
                'notifications' => $notifications_array,
                'unread_count' => getUnreadCount($customer_id)
            ];
            break;
            
        case 'mark_read':
            if (isset($_GET['id'])) {
                $response['success'] = markAsRead($_GET['id']);
            }
            break;
            
        case 'mark_all_read':
            $response['success'] = markAllAsRead($customer_id);
            break;
    }
    
    echo json_encode($response);
    exit;
}
?> 