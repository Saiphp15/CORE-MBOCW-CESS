<?php
// require_once '../config/db.php';

function hasPermission($permission_name)
{
    if (!isset($_SESSION['user_permissions']) || !is_array($_SESSION['user_permissions'])) {
        return false;
    }

    return in_array($permission_name, $_SESSION['user_permissions']);
    // if (!isset($_SESSION['user_role'])) {
    //     return false;
    // }

    // $role_id = $_SESSION['user_role'];

    // $sql = '
    //     SELECT COUNT(*) 
    //     FROM role_permissions
    //     INNER JOIN permissions ON role_permissions.permission_id = permissions.id
    //     WHERE role_permissions.role_id = ? AND permissions.name = ?
    // ';

    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param('is', $role_id, $permission_name);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $count = $result->fetch_row()[0];
    // $stmt->close();

    // return $count > 0;
}


