<?php
function addExperience($userId, $expToAdd, $conn) {
    $userQuery = "SELECT experience, level FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $newExp = $user['experience'] + $expToAdd;
    $newLevel = $user['level'];

    $levelQuery = "SELECT level, required_exp FROM levels ORDER BY level ASC";
    $levelResult = $conn->query($levelQuery);
    while ($row = $levelResult->fetch_assoc()) {
        if ($newExp >= $row['required_exp']) {
            $newLevel = $row['level'];
        }
    }

    $update = "UPDATE users SET experience = ?, level = ? WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("iii", $newExp, $newLevel, $userId);
    $stmt->execute();
}
?>