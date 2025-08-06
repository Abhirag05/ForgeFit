<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.fullname, u.level, p.primary_goal FROM users u LEFT JOIN user_fitness_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $level, $goal);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Assistant | Sensei</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
    }

    body {
      background-color: #0a0a12;
      color: #f0f0f0;
      min-height: 100vh;
      height: 100vh;
      overflow: auto;
    }

    .chat-container {
      max-width: 800px;
      margin: 80px auto 20px;
      min-height: 500px;
      height: 70vh;
      display: flex;
      flex-direction: column;
      background: rgba(20, 20, 40, 0.8);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      overflow: hidden;
      position: relative;
      z-index: 2;
    }

    .chat-header {
      padding: 20px;
      background: rgba(106, 0, 255, 0.2);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .chat-header h2 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #fff;
    }

    .chat-header p {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 5px;
    }

    .messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #6a00ff rgba(255, 255, 255, 0.1);
    }

    .messages::-webkit-scrollbar {
      width: 6px;
    }

    .messages::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }

    .messages::-webkit-scrollbar-thumb {
      background-color: #6a00ff;
      border-radius: 3px;
    }

    .message {
      margin-bottom: 15px;
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.4;
      position: relative;
      animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .user-message {
      background: #6a00ff;
      color: white;
      margin-left: auto;
      border-bottom-right-radius: 4px;
    }

    .bot-message {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
      margin-right: auto;
      border-bottom-left-radius: 4px;
    }

    .message-sender {
      font-weight: 600;
      font-size: 0.8rem;
      margin-bottom: 5px;
      display: block;
    }

    .input-area {
      display: flex;
      padding: 15px;
      background: rgba(20, 20, 40, 0.9);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    #userInput {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 25px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 1rem;
      outline: none;
      transition: all 0.3s;
    }

    #userInput:focus {
      background: rgba(255, 255, 255, 0.15);
      box-shadow: 0 0 0 2px rgba(106, 0, 255, 0.5);
    }

    #userInput::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }

    .send-button {
      background: #6a00ff;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 0 25px;
      margin-left: 10px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .send-button:hover {
      background: #7a1aff;
      transform: translateY(-2px);
    }

    .send-button:active {
      transform: translateY(0);
    }

    .typing-indicator {
      display: flex;
      align-items: center;
      background: rgba(106, 0, 255, 0.12);
      border-radius: 18px;
      margin: 12px 0 10px 0;
      padding: 12px 20px;
      width: fit-content;
      color: #fff;
      font-size: 1rem;
      box-shadow: 0 2px 8px rgba(106,0,255,0.10);
      border-left: 4px solid #6a00ff;
      gap: 14px;
      animation: fadeIn 0.3s;
    }

    .typing-dots-pro {
      display: inline-flex;
      align-items: center;
      margin-right: 8px;
    }
    .typing-dots-pro span {
      height: 10px;
      width: 10px;
      margin: 0 2px;
      background-color: #6a00ff;
      border-radius: 50%;
      display: inline-block;
      animation: typingAnimation 1.4s infinite ease-in-out;
    }
    .typing-dots-pro span:nth-child(2) {
      animation-delay: 0.2s;
    }
    .typing-dots-pro span:nth-child(3) {
      animation-delay: 0.4s;
    }
    @keyframes typingAnimation {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-7px); }
    }
    .typing-text {
      font-style: italic;
      opacity: 0.7;
      letter-spacing: 1px;
    }

    .timestamp {
      font-size: 0.7rem;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 5px;
      text-align: right;
    }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?php include('./components/user_navbar.php'); ?>
    
    <div class="chat-container">
      <div class="chat-header">
        <h2>Hello <?= htmlspecialchars($name) ?> <i class="fas fa-hand-peace"></i></h2>
        <p>I'm Sensei, your Fitness Coach specialized in <?= $goal ?></p>
      </div>
      
      <div class="messages" id="messages">
  <!-- Messages will be dynamically inserted here -->
</div>
      
      <div class="input-area">
        <input type="text" id="userInput" placeholder="Ask me anything about fitness..." autocomplete="off">
        <button class="send-button" onclick="sendMessage()">
          Send <i class="fas fa-paper-plane" style="margin-left: 8px;"></i>
        </button>
      </div>
    </div>

    <script>
    function formatTime(date) {
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    async function sendMessage() {
      const input = document.getElementById("userInput");
      const msg = input.value.trim();
      
      if (!msg) return;
      
      input.value = "";
      
      // Add user message
      const now = new Date();
      const messagesDiv = document.getElementById("messages");
      messagesDiv.innerHTML += `
        <div class="message user-message">
          <span class="message-sender">You</span>
          ${msg}
          <div class="timestamp">${formatTime(now)}</div>
        </div>
      `;
      
      // Add professional typing indicator below last user message
      const typingHTML = `
        <div class="message bot-message typing-indicator" id="typingIndicator">
          <span class="message-sender"><i class='fas fa-robot'></i> Sensei</span>
          <span class="typing-dots-pro">
            <span></span><span></span><span></span>
          </span>
          <span class="typing-text">Sensei is typing...</span>
        </div>
      `;
      messagesDiv.innerHTML += typingHTML;
      
      // Scroll to bottom
      messagesDiv.scrollTop = messagesDiv.scrollHeight;
      
      try {
        const response = await fetch("chat_bot_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: msg })
      });
      const data = await response.json();
        
      let reply = data.reply || "I couldn't process your request. Please try again.";
        
        if (data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.content) {
          reply = data.choices[0].message.content;
        }
        
        // Remove typing indicator
        const typingIndicator = document.getElementById("typingIndicator");
        if (typingIndicator) typingIndicator.remove();
        
        // Add bot response
        const responseTime = new Date();
        messagesDiv.innerHTML += `
          <div class="message bot-message">
            <span class="message-sender"><i class='fas fa-robot'></i> Sensei</span>
            ${reply}
            <div class="timestamp">${formatTime(responseTime)}</div>
          </div>
        `;
        
        // Scroll to bottom again
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
      } catch (error) {
        console.error("Error:", error);
        typingIndicator.style.display = "none";
        
        const errorTime = new Date();
        messagesDiv.innerHTML += `
          <div class="message bot-message">
            <span class="message-sender">Sensei</span>
            I'm having trouble connecting right now. Please try again later.
            <div class="timestamp">${formatTime(errorTime)}</div>
          </div>
        `;
      }
    }

    // Allow sending message with Enter key
    document.getElementById("userInput").addEventListener("keypress", function(e) {
      if (e.key === "Enter") {
        sendMessage();
      }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Initialize particles.js
      particlesJS("particles-js", {
        "particles": {
          "number": { "value": 100, "density": { "enable": true, "value_area": 800 } },
          "color": { "value": "#a64aff" },
          "shape": { "type": "circle" },
          "opacity": { "value": 0.4, "random": true },
          "size": { "value": 3, "random": true },
          "line_linked": { "enable": true, "distance": 120, "color": "#6a00ff", "opacity": 0.3, "width": 1 },
          "move": { "enable": true, "speed": 2, "direction": "none", "random": true, "out_mode": "out" }
        },
        "interactivity": {
          "detect_on": "canvas",
          "events": {
            "onhover": { "enable": true, "mode": "repulse" },
            "onclick": { "enable": true, "mode": "push" }
          }
        }
      });
    });
    </script>
</body>
</html>