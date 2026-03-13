<?php
session_start();
include("connect.php");
include("functions.php");

$message = "";
$message_type = ""; // 'error', 'success', or 'warning'
$username = "";

// Check for logout message from force_logout.php
if (isset($_SESSION['logout_message'])) {
    $message = $_SESSION['logout_message'];
    $message_type = $_SESSION['logout_success'] ? 'success' : 'error';
    unset($_SESSION['logout_message']);
    unset($_SESSION['logout_success']);
}

// Initialize login attempts tracking
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
    $_SESSION['locked_until'] = 0;
}

// Check if account is locked
$current_time = time();
if ($_SESSION['locked_until'] > $current_time) {
    $remaining_time = $_SESSION['locked_until'] - $current_time;
    $minutes = floor($remaining_time / 60);
    $seconds = $remaining_time % 60;
    $message = "Too many failed login attempts. Please wait " . $minutes . " minute(s) and " . $seconds . " second(s) before trying again.";
    $message_type = "error";
} else {
    // Reset lock if time has passed
    if ($_SESSION['locked_until'] > 0 && $_SESSION['locked_until'] <= $current_time) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['locked_until'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    // Check if account is locked
    if ($_SESSION['locked_until'] > $current_time) {
        $remaining_time = $_SESSION['locked_until'] - $current_time;
        $minutes = floor($remaining_time / 60);
        $seconds = $remaining_time % 60;
        $message = "Too many failed login attempts. Please wait " . $minutes . " minute(s) and " . $seconds . " second(s) before trying again.";
        $message_type = "error";
    } elseif (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Check if user is already logged in
            if ($row['status'] === 'logged_in') {
                $message = "This account is already logged in from another browser/device.";
                $message_type = "warning";
            } elseif (password_verify($password, $row['password']) || $password === $row['password']) {

                // Reset login attempts on successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['locked_until'] = 0;

                // Assign session variables
                if ($row['role'] === 'admin') {
                    $_SESSION['admin_id'] = $username;
                } elseif ($row['role'] === 'teacher') {
                    $_SESSION['teacher_id'] = $username;
                } else {
                    $_SESSION['student_id'] = $username;
                }
                $_SESSION['role'] = $row['role'];

                // Update status to logged_in
                $update_status = $conn->prepare("UPDATE users SET status='logged_in' WHERE username=?");
                $update_status->bind_param("s", $username);
                $update_status->execute();
                $update_status->close();

                addLog($conn, $username, $row['role'], 'Logged in successfully');

                // Hash legacy password if needed
                if (!password_verify($password, $row['password'])) {
                    $new_hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
                    $update_stmt->bind_param("ss", $new_hashed, $username);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("Location: admin.php");
                } elseif ($row['role'] === 'teacher') {
                    header("Location: teacher_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                // Increment failed login attempts
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();

                $remaining_attempts = 5 - $_SESSION['login_attempts'];

                if ($_SESSION['login_attempts'] >= 5) {
                    // Lock account for 3 minutes (180 seconds)
                    $_SESSION['locked_until'] = time() + 180;
                    $message = "Too many failed login attempts. Your account has been locked for 3 minutes.";
                    $message_type = "error";
                    addLog($conn, $username, 'unknown', 'Account locked due to multiple failed login attempts');
                } else {
                    $message = "Incorrect password. You have " . $remaining_attempts . " attempt(s) remaining.";
                    $message_type = "error";
                    addLog($conn, $username, 'unknown', 'Failed login attempt: incorrect password (Attempt ' . $_SESSION['login_attempts'] . '/5)');
                }
            }
        } else {
            // Increment attempts for non-existent username too
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();

            $remaining_attempts = 5 - $_SESSION['login_attempts'];

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['locked_until'] = time() + 180;
                $message = "Too many failed login attempts. Please wait 3 minutes before trying again.";
                $message_type = "error";
            } else {
                $message = "Username not found. You have " . $remaining_attempts . " attempt(s) remaining.";
                $message_type = "error";
            }
            addLog($conn, $username, 'unknown', 'Failed login attempt: ID not found (Attempt ' . $_SESSION['login_attempts'] . '/5)');
        }
    } else {
        $message = "Please fill in all fields.";
        $message_type = "error";
    }
}

// Calculate if locked and remaining time for display
$is_locked = $_SESSION['locked_until'] > time();
$remaining_seconds = $is_locked ? ($_SESSION['locked_until'] - time()) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RSASHS E-PORTAL LOGIN</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <style>
        /* ===== ERROR MESSAGE STYLING ===== */
        .message-container {
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
            animation: slideDown 0.3s ease-out;
        }

        .message-container.show {
            display: block;
        }

        .message-error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .message-warning {
            background-color: #fff3e0;
            color: #e65100;
            border-left: 4px solid #e65100;
        }

        .message-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #f1f8e9;
            color: #2e3a2f;
            padding: 25px 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .modal-buttons .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #2e7d32;
            color: #fff;
            font-weight: 600;
        }

        .modal-buttons .btn-secondary {
            background: #c62828;
        }

        .modal-buttons .btn:hover {
            opacity: 0.9;
        }

        /* Input field error state */
        .input-error {
            border-color: #c62828 !important;
            background-color: #ffebee !important;
        }

        /* Disabled form styling */
        .form-disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Countdown timer */
        .countdown-timer {
            font-weight: bold;
            color: #c62828;
            font-size: 16px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="./img/logo.jpg" alt="Logo">
        <h2>RSASHS E-PORTAL LOGIN</h2>

        <!-- Error Message Display -->
        <?php if (!empty($message) && strpos($message, 'already logged in') === false): ?>
            <div class="message-container message-<?php echo $message_type; ?> show">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($is_locked): ?>
                    <div class="countdown-timer" id="countdown"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" <?php echo $is_locked ? 'class="form-disabled"' : ''; ?>>
            <label for="student_id">Username/ID</label>
            <input type="text" id="student_id" name="student_id"
                value="<?php echo htmlspecialchars($username); ?>"
                class="<?php echo (!empty($message) && $message_type === 'error') ? 'input-error' : ''; ?>"
                <?php echo $is_locked ? 'disabled' : 'required'; ?>>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                class="<?php echo (!empty($message) && $message_type === 'error') ? 'input-error' : ''; ?>"
                <?php echo $is_locked ? 'disabled' : 'required'; ?>>

            <button type="submit" <?php echo $is_locked ? 'disabled' : ''; ?>>
                <?php echo $is_locked ? 'Login Disabled' : 'Login'; ?>
            </button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>

    <!-- Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <h3>Account Already Logged In</h3>
            <p id="modalMessage"><?php echo htmlspecialchars($message); ?></p>
            <div class="modal-buttons">
                <form method="POST" action="force_logout.php">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <button type="submit" class="btn">Logout Other Session</button>
                </form>
                <button id="modalClose" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("loginModal");
            const closeBtn = document.getElementById("modalClose");

            <?php if (!empty($message) && strpos($message, 'already logged in') !== false): ?>
                modal.style.display = "flex";
            <?php endif; ?>

            closeBtn.addEventListener("click", () => modal.style.display = "none");

            window.addEventListener("click", (e) => {
                if (e.target === modal) modal.style.display = "none";
            });

            // Auto-hide error messages after 5 seconds (except for locked messages)
            const messageContainer = document.querySelector('.message-container');
            <?php if (!$is_locked): ?>
                if (messageContainer && messageContainer.classList.contains('show')) {
                    setTimeout(() => {
                        messageContainer.style.opacity = '0';
                        messageContainer.style.transition = 'opacity 0.5s';
                        setTimeout(() => {
                            messageContainer.style.display = 'none';
                        }, 500);
                    }, 5000);
                }
            <?php endif; ?>

            // Countdown timer for locked account
            <?php if ($is_locked): ?>
                let remainingSeconds = <?php echo $remaining_seconds; ?>;
                const countdownElement = document.getElementById('countdown');

                function updateCountdown() {
                    if (remainingSeconds <= 0) {
                        location.reload();
                        return;
                    }

                    const minutes = Math.floor(remainingSeconds / 60);
                    const seconds = remainingSeconds % 60;
                    countdownElement.textContent = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;

                    remainingSeconds--;
                    setTimeout(updateCountdown, 1000);
                }

                updateCountdown();
            <?php endif; ?>
        });
    </script>
</body>

</html>