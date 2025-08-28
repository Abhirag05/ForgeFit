<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
   <link rel="icon" href="assets/images/logo.png" type="image/x-icon">
  <title>Forgot Password</title>
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
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .forgot-container {
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(1px);
      border: 1px solid rgba(108, 117, 125, 0.2);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(100, 0, 255, 0.2);
      width: 100%;
      max-width: 400px;
      animation: fadeIn 1s ease;
    }

    .forgot-container h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #ffffff;
    }

    .input-group {
      margin-bottom: 20px;
      position: relative;
    }

    .input-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #ccc;
    }

    .input-group input {
      width: 100%;
      padding: 12px;
      padding-right: 40px;
      background-color: rgba(255, 255, 255, 0.05);
      border: 1px solid #6a00ff;
      border-radius: 8px;
      color: #fff;
      transition: all 0.3s ease;
    }

    .input-group input:focus {
      border-color: #a64aff;
      outline: none;
      box-shadow: 0 0 8px #6a00ff;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-20%);
      background: none;
      border: none;
      color: #a64aff;
      cursor: pointer;
      padding: 5px;
      transition: color 0.3s ease, transform 0.2s ease;
    }

    .toggle-password:hover {
      color: #ffffff;
      transform: translateY(-20%) scale(1.1);
    }

    .toggle-password svg {
      width: 20px;
      height: 20px;
      fill: currentColor;
    }

    .eye-icon-hidden {
      display: none;
    }

    .show-password .eye-icon-visible {
      display: none;
    }

    .show-password .eye-icon-hidden {
      display: inline;
    }

    .reset-btn {
      width: 100%;
      background: linear-gradient(135deg, #6a00ff, #4ca1af);
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .reset-btn:hover {
      background: linear-gradient(135deg, #4ca1af, #6a00ff);
      transform: translateY(-2px);
    }

    .bottom-text {
      text-align: center;
      margin-top: 16px;
      font-size: 14px;
      color: #bbb;
    }

    .bottom-text a {
      color: #4ca1af;
      text-decoration: none;
      font-weight: bold;
    }

    .bottom-text a:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .top-nav {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 10;
    }

    .back-home-btn {
      padding: 10px 16px;
      color: #ffffff;
      background: rgba(106, 0, 255, 0.2);
      border: 1px solid #6a00ff;
      border-radius: 8px;
      font-size: 14px;
      text-decoration: none;
      backdrop-filter: blur(5px);
      transition: all 0.3s ease;
    }

    .back-home-btn:hover {
      background: #6a00ff;
      color: white;
      box-shadow: 0 0 10px #6a00ff;
    }

    .info-text {
      text-align: center;
      margin-bottom: 20px;
      color: #bbb;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>
  <div class="top-nav">
    <a href="signin.php" class="back-home-btn">← Back to Sign In</a>
  </div>

  <div class="forgot-container">
    <h2>Reset Password</h2>
    
    <form id="forgotForm">
      <div class="input-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required />
      </div>

      <div class="input-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required />
        <button type="button" class="toggle-password" aria-label="Show password">
          <svg class="eye-icon-visible" viewBox="0 0 24 24">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
          </svg>
          <svg class="eye-icon-hidden" viewBox="0 0 24 24">
            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-4.02.67l2.14 2.14C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22l1.27-1.27L3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
          </svg>
        </button>
      </div>

      <div class="input-group">
        <label for="confirmPassword">Confirm New Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required />
      </div>

      <div id="messageBox" style="margin: 10px 0; padding: 10px; border-radius: 5px; display: none; text-align: center; font-size: 14px;"></div>

      <button class="reset-btn" type="submit" id="submitBtn">Reset Password</button>
    </form>

    <div class="bottom-text">
      Remember your password? <a href="signin.php">Sign In</a>
    </div>
  </div>

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

      // Form elements
      const form = document.getElementById('forgotForm');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmPassword');
      const messageBox = document.getElementById('messageBox');
      const submitBtn = document.getElementById('submitBtn');

      // Password toggle functionality
      const toggleButton = document.querySelector('.toggle-password');

      if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', () => {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          toggleButton.parentElement.classList.toggle('show-password');
          toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });

        toggleButton.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            toggleButton.click();
          }
        });
      }

      // Show message function
      function showMessage(message, type) {
        messageBox.textContent = message;
        messageBox.style.display = 'block';
        messageBox.style.backgroundColor = type === 'success' ? 'rgba(102, 255, 153, 0.1)' : 'rgba(255, 102, 102, 0.1)';
        messageBox.style.color = type === 'success' ? '#66ff99' : '#ff6666';
        messageBox.style.border = `1px solid ${type === 'success' ? '#66ff99' : '#ff6666'}`;
      }

      // Form submission
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Basic validation
        if (!email || !password || !confirmPassword) {
          showMessage('All fields are required.', 'error');
          return;
        }

        if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
          showMessage('Invalid email format.', 'error');
          return;
        }

        if (password.length < 6) {
          showMessage('Password must be at least 6 characters long.', 'error');
          return;
        }

        if (password !== confirmPassword) {
          showMessage('Passwords do not match.', 'error');
          return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
        showMessage('Resetting password, please wait...', 'success');

        try {
          const response = await fetch('forgot_password_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              email: email,
              password: password,
              confirmPassword: confirmPassword
            })
          });

          const data = await response.json();
          
          if (data.status === 'success') {
            showMessage(data.message, 'success');
            setTimeout(() => {
              window.location.href = 'signin.php';
            }, 2000);
          } else {
            showMessage(data.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Reset Password';
          }
        } catch (err) {
          showMessage('Something went wrong. Please try again.', 'error');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Reset Password';
        }
      });
    });
  </script>
</body>
</html>
