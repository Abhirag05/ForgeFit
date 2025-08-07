<?php
session_start();
header('Content-Type: application/json');
include '../db.php'; // 🔁 Adjust this path based on your project

// 🔐 Gemini API Key
$api_key = "AIzaSyBacWQCE7uXPClluh1pQQVf-Sl1Hg2lxZI";

// 🧠 User must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['reply' => 'Not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 📥 Get user message
$input = json_decode(file_get_contents("php://input"), true);
$userMsg = trim($input['message'] ?? '');

if (!$userMsg) {
    echo json_encode(['reply' => "No message provided."]);
    exit;
}

// ✅ Rule-based website FAQ responses
$rules = [
    "how to update profile" => "Go to your profile page and click the edit button.",
    "how many levels" => "There are 10 levels in total. Keep leveling up!",
    "what is exp" => "EXP stands for Experience Points. Earn EXP by logging workouts and meals.",
    "how to log workout" => "Go to 'Log Workout' in the dashboard menu, select exercises, and submit.",
    "how to change goal" => "Visit your profile page and click on the goal dropdown to update it.",
    "how to become premium" => "We're working on premium features! Stay tuned for updates soon."
];

// 🔍 Check rule match
$response = "";
$matched = false;
foreach ($rules as $q => $r) {
    if (stripos($userMsg, $q) !== false) {
        $response = $r;
        $matched = true;
        break;
    }
}

// 🤖 Fallback to Gemini API if not rule-based
if (!$matched) {
    $payload = [
        "contents" => [
            [
                "role" => "model",
                "parts" => [
                    ["text" => "You are Sensei, a professional fitness coach. Always reply in English with helpful, concise, and motivating answers."]
                ]
            ],
            [
                "role" => "user",
                "parts" => [
                    ["text" => $userMsg]
                ]
            ]
        ]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=$api_key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    $response = $json['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't generate a response.";
}

// 💾 Save both messages to chat_history

// 1. User message
$stmt = $conn->prepare("INSERT INTO chat_history (user_id, message, response, sender) VALUES (?, ?, '', 'user')");
$stmt->bind_param("is", $user_id, $userMsg);
$stmt->execute();

// 2. Sensai response
$stmt = $conn->prepare("INSERT INTO chat_history (user_id, message, response, sender) VALUES (?, '', ?, 'sensai')");
$stmt->bind_param("is", $user_id, $response);
$stmt->execute();

echo json_encode(['reply' => $response]);
?>
