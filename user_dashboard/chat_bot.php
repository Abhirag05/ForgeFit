<?php
session_start();
include '../db.php';


// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Get user_id from session FIRST
$user_id = $_SESSION['user_id'];

//payment model integration
$premium =  $_SESSION['premium'];
if ($premium == 0) {
    include('components/payment_model.php');
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showPaymentModal();
        });
    </script>";
    exit();
}

// Fetch user details and profile image
$stmt = $conn->prepare("SELECT u.fullname, u.level, p.primary_goal, p.profile_image FROM users u LEFT JOIN user_fitness_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $name = $user_data['fullname'];
    $level = $user_data['level'];
    $goal = $user_data['primary_goal'] ?? 'General Fitness'; // Default value if null
    $profile_image = $user_data['profile_image'] ?? '';
} else {
    // Handle case where user not found
    $name = 'User';
    $level = 'Beginner';
    $goal = 'General Fitness';
    $profile_image = '';
}
$stmt->close();

// Determine user avatar path
if (!$profile_image) {
    $user_avatar = '../uploads/default-avatar.png';
} else if (strpos($profile_image, 'uploads/') === false) {
    $user_avatar = '../uploads/' . $profile_image;
} else {
    $user_avatar = $profile_image;
}

// Fetch chat history for this user
$chat_history = [];
$history_stmt = $conn->prepare("SELECT message, response, sender, created_at FROM chat_history WHERE user_id = ? ORDER BY created_at ASC");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

while ($row = $history_result->fetch_assoc()) {
    $chat_history[] = $row;
}

$history_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        const USER_AVATAR = "<?= htmlspecialchars($user_avatar) ?>";
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Assistant | Sensei</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Base and Typography --- */
        :root {
            --primary-color: #8e44ad;
            --secondary-color: #9b59b6;
            --user-message-bg: linear-gradient(135deg, #8e44ad, #9b59b6);
            --bot-message-bg: rgba(44, 48, 72, 0.7);
            --container-bg: rgba(17, 17, 27, 0.6);
            --text-color: #ecf0f1;
            --text-muted-color: #bdc3c7;
            --border-color: rgba(142, 68, 173, 0.2);
            --font-family: 'Poppins', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #0a0a12;
            color: var(--text-color);
            font-family: var(--font-family);
            min-height: 100vh;
            height: 100vh;
            /* Removed overflow: hidden and flex centering to allow navbar to align at top */
            margin: 0;
            padding: 0;
        }

        /* --- Background Particles --- */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
        }

        /* --- Chat Centering Wrapper --- */
        .chat-center-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px; /* Add space above, below, and sides */
        }

        /* --- Chat Container --- */
        .chat-container {
            width: 100%;
            max-width: 850px;
            height: 85vh;
            max-height: 900px;
            display: flex;
            flex-direction: column;
            background: var(--container-bg);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            overflow: hidden;
            position: relative;
            z-index: 2;
            padding: 24px; /* Add padding on all sides */
        }
        
        /* --- Chat Header --- */
        .chat-header {
            padding: 20px 25px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .chat-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
        }

        .chat-header h2 .fa-hand-peace {
            margin-left: 10px;
            color: #f1c40f;
            transform-origin: bottom left;
            animation: wave 2.5s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(12deg); }
            50% { transform: rotate(0deg); }
        }

        .chat-header p {
            font-size: 0.9rem;
            color: var(--text-muted-color);
            margin-top: 4px;
            font-weight: 300;
        }
        
        /* --- Messages Area --- */
        .messages {
            flex-grow: 1;
            padding: 20px 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* Custom Scrollbar */
        .messages::-webkit-scrollbar {
            width: 8px;
        }

        .messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .messages::-webkit-scrollbar-thumb:hover {
            background-color: var(--secondary-color);
        }

        /* --- Individual Messages --- */
        .message {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            max-width: 85%;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-content {
            padding: 12px 18px;
            border-radius: 18px;
            line-height: 1.5;
            font-weight: 400;
            color: var(--text-color);
            white-space: pre-wrap; /* Renders newlines */
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Bot Messages */
        .bot-message {
            align-self: flex-start;
        }

        .bot-message .avatar {
            background: var(--primary-color);
        }

        .bot-message .message-content {
            background: var(--bot-message-bg);
            border-bottom-left-radius: 5px;
        }
        
        /* User Messages */
        .user-message {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .user-message .avatar {
            background: #2ecc71;
        }

        .user-message .message-content {
            background: var(--user-message-bg);
            border-bottom-right-radius: 5px;
        }

        .timestamp {
            font-size: 0.75rem;
            color: var(--text-muted-color);
            margin-top: 8px;
            padding: 0 5px;
        }

        .bot-message .timestamp { text-align: left; }
        .user-message .timestamp { text-align: right; }

        /* --- Typing Indicator --- */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .typing-indicator .typing-dots span {
            height: 8px;
            width: 8px;
            margin: 0 2px;
            background-color: var(--text-muted-color);
            border-radius: 50%;
            display: inline-block;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        /* --- Input Area --- */
        .input-area {
            display: flex;
            padding: 15px 25px;
            background: rgba(0, 0, 0, 0.3);
            border-top: 1px solid var(--border-color);
            flex-shrink: 0;
            gap: 15px;
            align-items: center;
        }

        #userInput {
            flex-grow: 1;
            padding: 12px 20px;
            border: 1px solid transparent;
            border-radius: 25px;
            background: var(--bot-message-bg);
            color: white;
            font-size: 1rem;
            font-family: var(--font-family);
            outline: none;
            transition: all 0.3s ease;
        }

        #userInput:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(142, 68, 173, 0.3);
        }

        #userInput::placeholder {
            color: var(--text-muted-color);
            font-weight: 300;
        }

        .send-button {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .send-button:hover {
            background: var(--secondary-color);
            transform: scale(1.1) rotate(15deg);
        }
        
        .send-button:active {
            transform: scale(1);
        }
        
        .send-button:disabled {
            background: #555;
            cursor: not-allowed;
            transform: none;
        }

        /* --- Error Message --- */
        .error-message {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border-left: 4px solid #ff6b6b;
            padding: 12px;
            border-radius: 4px;
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            body {
                overflow: auto; /* Allow scrolling on small devices */
            }
            .chat-center-wrapper {
                padding: 16px 6px;
            }
            .chat-container {
                height: 100vh;
                width: 100vw;
                border-radius: 0;
                border: none;
                max-height: none;
                padding: 12px 4px;
            }
            .messages {
                padding: 15px;
            }
            .message {
                max-width: 90%;
            }
            .input-area {
                padding: 10px 15px;
            }
        }

            /* --- Responsive: 480px and below --- */
            @media (max-width: 480px) {
                .chat-center-wrapper {
                    padding: 8px 2px;
                }
                .chat-container {
                    max-width: 100vw;
                    min-width: 0;
                    margin-top:10px;
                    height: 90vh;
                    border-radius: 0;
                    box-shadow: none;
                    padding: 8px 2px;
                }
                .chat-header {
                    padding: 14px 10px 10px 10px;
                }
                .chat-header h2 {
                    font-size: 1.1rem;
                }
                .chat-header p {
                    font-size: 0.8rem;
                }
                .messages {
                    padding: 10px 6px;
                    gap: 10px;
                }
                .message {
                    max-width: 98%;
                    gap: 7px;
                }
                .message-content {
                    padding: 9px 12px;
                    font-size: 0.97rem;
                    border-radius: 13px;
                }
                .avatar {
                    width: 32px;
                    height: 32px;
                    font-size: 1rem;
                }
                .timestamp {
                    font-size: 0.7rem;
                    margin-top: 4px;
                }
                .input-area {
                    padding: 7px 6px;
                    gap: 7px;
                }
                #userInput {
                    font-size: 0.97rem;
                    padding: 9px 12px;
                }
                .send-button {
                    width: 38px;
                    height: 38px;
                    font-size: 1rem;
                }
            }

            /* --- Responsive: 400px and below --- */
            @media (max-width: 400px) {
                .chat-center-wrapper {
                    padding: 4px 1px;
                }
                .chat-container {
                    padding: 4px 1px;
                }
                .chat-header {
                    padding: 10px 4px 7px 4px;
                }
                .chat-header h2 {
                    font-size: 0.98rem;
                }
                .chat-header p {
                    font-size: 0.7rem;
                }
                .messages {
                    padding: 6px 2px;
                    gap: 7px;
                }
                .message-content {
                    padding: 7px 8px;
                    font-size: 0.92rem;
                    border-radius: 10px;
                }
                .avatar {
                    width: 26px;
                    height: 26px;
                    font-size: 0.9rem;
                }
                .input-area {
                    padding: 5px 2px;
                    gap: 4px;
                }
                #userInput {
                    font-size: 0.92rem;
                    padding: 7px 8px;
                }
                .send-button {
                    width: 30px;
                    height: 30px;
                    font-size: 0.9rem;
                }
            }

            /* --- Responsive: 360px and below --- */
            @media (max-width: 360px) {
                .chat-center-wrapper {
                    padding: 2px 0;
                }
                .chat-container {
                    padding: 2px 0;
                }
                .chat-header {
                    padding: 7px 2px 5px 2px;
                }
                .chat-header h2 {
                    font-size: 0.85rem;
                }
                .chat-header p {
                    font-size: 0.62rem;
                }
                .messages {
                    padding: 3px 1px;
                    gap: 5px;
                }
                .message-content {
                    padding: 5px 5px;
                    font-size: 0.85rem;
                    border-radius: 7px;
                }
                .avatar {
                    width: 20px;
                    height: 20px;
                    font-size: 0.7rem;
                }
                .input-area {
                    padding: 3px 1px;
                    gap: 2px;
                }
                #userInput {
                    font-size: 0.85rem;
                    padding: 5px 5px;
                }
                .send-button {
                    width: 22px;
                    height: 22px;
                    font-size: 0.7rem;
                }
            }

            /* --- Responsive: 320px and below --- */
            @media (max-width: 320px) {
                .chat-center-wrapper {
                    padding: 1px 0;
                }
                .chat-container {
                    padding: 1px 0;
                }
                .chat-header {
                    padding: 4px 1px 3px 1px;
                }
                .chat-header h2 {
                    font-size: 0.7rem;
                }
                .chat-header p {
                    font-size: 0.5rem;
                }
                .messages {
                    padding: 1px 0;
                    gap: 3px;
                }
                .message-content {
                    padding: 3px 3px;
                    font-size: 0.7rem;
                    border-radius: 5px;
                }
                .avatar {
                    width: 14px;
                    height: 14px;
                    font-size: 0.5rem;
                }
                .input-area {
                    padding: 1px 0;
                    gap: 1px;
                }
                #userInput {
                    font-size: 0.7rem;
                    padding: 3px 3px;
                }
                .send-button {
                    width: 14px;
                    height: 14px;
                    font-size: 0.5rem;
                }
            }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <!-- The navbar include can be placed here if needed, but for a dedicated chat UI, it might be better on other pages. -->
    <?php include('components/user_navbar.php'); ?>
    
    <div class="chat-center-wrapper">
      <div class="chat-container">
        <div class="chat-header">
            <h2>Hello, <?= htmlspecialchars($name) ?> <i class="fas fa-hand-peace"></i></h2>
            <p>I'm Sensei ,Your AI Fitness Coach for <?= htmlspecialchars($goal) ?></p>
        </div>
        
        <div class="messages" id="messages">
            <!-- Initial Welcome Message or Chat History -->
            <?php if (empty($chat_history)): ?>
                <div class="message bot-message">
                    <div class="avatar"><i class='fas fa-robot'></i></div>
                    <div>
                        <div class="message-content">
                            Welcome! I'm Sensei, your AI fitness partner. Ask me for workout plans, nutrition tips, or anything to help you on your fitness journey!
                        </div>
                        <div class="timestamp"><?= date("h:i A") ?></div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($chat_history as $chat): ?>
                    <?php if ($chat['sender'] === 'user'): ?>
                        <div class="message user-message">
                            <div class="avatar"><img src="<?= htmlspecialchars($user_avatar) ?>" alt="User Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover;"></div>
                            <div>
                                <div class="message-content"><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
                                <div class="timestamp"><?= date("h:i A", strtotime($chat['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="message bot-message">
                            <div class="avatar"><i class='fas fa-robot'></i></div>
                            <div>
                                <div class="message-content"><?= nl2br(htmlspecialchars($chat['response'])) ?></div>
                                <div class="timestamp"><?= date("h:i A", strtotime($chat['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="input-area">
            <input type="text" id="userInput" placeholder="Ask Sensei anything..." autocomplete="off">
            <button class="send-button" onclick="sendMessage()" title="Send Message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
      </div>
    </div>

    <script>
    // --- UTILITY FUNCTIONS ---
    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom() {
        const messagesDiv = document.getElementById("messages");
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // --- DOM MANIPULATION ---
    function addMessage(content, isUser = false) {
        const messagesDiv = document.getElementById("messages");
        const now = new Date();
        
        const messageTypeClass = isUser ? 'user-message' : 'bot-message';
        const avatarIcon = isUser
            ? `<img src="${USER_AVATAR}" alt="User Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">`
            : "<i class='fas fa-robot'></i>";
        
        // Sanitize content to prevent HTML injection
        const sanitizedContent = content.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        const messageHTML = `
            <div class="message ${messageTypeClass}">
                <div class="avatar">${avatarIcon}</div>
                <div>
                    <div class="message-content">${sanitizedContent}</div>
                    <div class="timestamp">${formatTime(now)}</div>
                </div>
            </div>
        `;
        
        messagesDiv.insertAdjacentHTML('beforeend', messageHTML);
        scrollToBottom();
    }

    function showTypingIndicator() {
        if (document.getElementById("typingIndicator")) return; // Already exists

        const messagesDiv = document.getElementById("messages");
        const typingHTML = `
            <div class="message bot-message" id="typingIndicator">
                <div class="avatar"><i class='fas fa-robot'></i></div>
                <div class="message-content typing-indicator">
                    <span class="typing-text">Sensei is typing</span>
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        messagesDiv.insertAdjacentHTML('beforeend', typingHTML);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const typingIndicator = document.getElementById("typingIndicator");
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    function addErrorMessage(error) {
        const messagesDiv = document.getElementById("messages");
        const errorHTML = `
            <div class="message bot-message">
                <div class="avatar" style="background: #e74c3c;"><i class='fas fa-exclamation-triangle'></i></div>
                <div>
                    <div class="message-content error-message">
                        I'm having trouble connecting right now. Please check your internet and try again.
                        <br><small>Error: ${error.message}</small>
                    </div>
                </div>
            </div>`;
        messagesDiv.insertAdjacentHTML('beforeend', errorHTML);
        scrollToBottom();
    }

    // --- CORE LOGIC ---
    async function sendMessage() {
        const input = document.getElementById("userInput");
        const sendButton = document.querySelector('.send-button');
        const msg = input.value.trim();
        
        if (!msg) return;
        
        // Disable form
        input.disabled = true;
        sendButton.disabled = true;
        
        addMessage(msg, true);
        input.value = "";
        showTypingIndicator();
        
        try {
            const response = await fetch("chat_bot_api.php", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ message: msg })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            removeTypingIndicator();
            
            // Determine the reply from various possible response structures
            let reply = data.reply || data.response || (data.choices && data.choices[0]?.message?.content) || data.message;
            
            if (reply) {
                // Replace newlines for proper HTML rendering
                addMessage(reply.replace(/\\n/g, '\n'));
            } else {
                throw new Error("Invalid response format from API.");
            }
            
        } catch (error) {
            console.error("Error:", error);
            removeTypingIndicator();
            addErrorMessage(error);
        } finally {
            // Re-enable form
            input.disabled = false;
            sendButton.disabled = false;
            input.focus();
        }
    }

    // --- EVENT LISTENERS ---
    document.getElementById("userInput").addEventListener("keypress", function(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        scrollToBottom();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize particles.js
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#8e44ad" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.3, "random": true, "anim": { "enable": true, "speed": 1, "opacity_min": 0.1, "sync": false } },
                "size": { "value": 3, "random": true, "anim": { "enable": false } },
                "line_linked": { "enable": true, "distance": 150, "color": "#8e44ad", "opacity": 0.2, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": true, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "repulse" },
                    "onclick": { "enable": true, "mode": "push" },
                    "resize": true
                },
                "modes": {
                    "repulse": { "distance": 100, "duration": 0.4 },
                    "push": { "particles_nb": 4 }
                }
            },
            "retina_detect": true
        });
    });
    </script>
</body>
</html>
