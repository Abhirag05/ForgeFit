<?php
header('Content-Type: application/json');

// Your Gemini API key (keep this secret!)
$api_key = "AIzaSyBacWQCE7uXPClluh1pQQVf-Sl1Hg2lxZI";

// Get user message from POST (JSON)
$input = json_decode(file_get_contents("php://input"), true);
$userMsg = $input['message'] ?? '';

if (!$userMsg) {
    echo json_encode(['reply' => "No message provided."]);
    exit;
}

        // Prepare payload for Gemini
        $payload = [
            "contents" => [
                [
                    "role" => "model",
                    "parts" => [
                        ["text" => "You are Sensei, a professional fitness coach. Always provide accurate, concise, and encouraging fitness advice. Always reply in English."]
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

// Call Gemini API
$ch = curl_init('https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . $api_key);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);
$reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't understand that.";

echo json_encode(["reply" => $reply]);
?>