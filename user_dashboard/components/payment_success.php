<?php
session_start();
include '../../db.php';

// Get the user ID
$user_id = $_SESSION['user_id'] ?? null;
$success = false;
$error_message = '';

if ($user_id) {
    try {
        // Mark user as premium
        $stmt = $conn->prepare("UPDATE users SET premium = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['premium'] = true;
            $success = true;
        } else {
            $error_message = "Failed to activate premium status. Please contact support.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $error_message = "An error occurred while processing your payment. Please contact support.";
        error_log("Payment success error: " . $e->getMessage());
    }
} else {
    $error_message = "Session expired. Please log in again to continue.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Error'; ?> - Your App Name</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: <?php echo $success ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #ef4444, #dc2626)'; ?>;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 24px;
            color: <?php echo $success ? '#10b981' : '#ef4444'; ?>;
            animation: fadeInScale 0.6s ease-out;
        }

        .title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .subtitle {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
            animation: fadeInUp 0.6s ease-out 0.3s both;
        }

        .premium-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 32px;
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .premium-badge i {
            margin-right: 8px;
        }

        .features {
            background: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            animation: fadeInUp 0.6s ease-out 0.5s both;
        }

        .features h3 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .features ul {
            list-style: none;
            text-align: left;
        }

        .features li {
            color: #4b5563;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .features li i {
            color: #10b981;
            margin-right: 12px;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 16px;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }

        .btn {
            padding: 16px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            border-color: #d1d5db;
            background: #f9fafb;
        }

        .error-message {
            color: #dc2626;
            background: #fef2f2;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #ef4444;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .support-info {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.9rem;
            animation: fadeInUp 0.6s ease-out 0.7s both;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: 40px 24px;
            }
            
            .title {
                font-size: 1.6rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <i class="fas fa-check-circle icon"></i>
            <h1 class="title">Payment Successful!</h1>
            <p class="subtitle">Congratulations! Your payment has been processed successfully and your Premium account is now active.</p>
            
            <div class="premium-badge">
                <i class="fas fa-crown"></i>
                Premium Member
            </div>

            <div class="features">
                <h3>Your Premium Benefits</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Unlimited chatbot conversations</li>
                    <li><i class="fas fa-check"></i> Priority customer support</li>
                    <li><i class="fas fa-check"></i> Advanced AI features</li>
                    <li><i class="fas fa-check"></i> Ad-free experience</li>
                    <li><i class="fas fa-check"></i> Export conversation history</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="../chat_bot.php" class="btn btn-primary">
                    <i class="fas fa-comments"></i>
                    Start Chatting
                </a>
                <a href="../user_home.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i>
                    Go to Dashboard
                </a>
            </div>

        <?php else: ?>
            <i class="fas fa-exclamation-triangle icon"></i>
            <h1 class="title">Payment Error</h1>
            <p class="subtitle">We encountered an issue while processing your payment.</p>
            
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>

            <div class="action-buttons">
                <a href="../signin.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login Again
                </a>
                <a href="mailto:support@yourapp.com" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i>
                    Contact Support
                </a>
            </div>
        <?php endif; ?>

        <div class="support-info">
            Need help? Contact our support team at 
            <strong>support@yourapp.com</strong> or call 
            <strong>1-800-YOUR-APP</strong>
        </div>
    </div>
</body>
</html>