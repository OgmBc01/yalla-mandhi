
<?php

// Include database configuration and connection
require_once __DIR__ . '/database.php';

// includes/functions.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    $conn = getDBConnection();
    if ($conn) {
        return $conn->real_escape_string(trim($input));
    }
    return trim(htmlspecialchars($input));
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * User registration function
 */
function registerUser($userData) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Sanitize inputs
    $username = sanitizeInput($userData['username']);
    $email = sanitizeInput($userData['email']);
    $password = $userData['password'];
    $full_name = isset($userData['full_name']) ? sanitizeInput($userData['full_name']) : '';
    $phone = isset($userData['phone']) ? sanitizeInput($userData['phone']) : '';
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (!validatePassword($password)) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, and number'];
    }
    
    // Check if username or email already exists
    $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    $stmt->close();
    
    // Hash password
    $password_hash = hashPassword($password);
    
    // Insert user
    $insertQuery = "INSERT INTO users (username, email, password_hash, full_name, phone) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssss", $username, $email, $password_hash, $full_name, $phone);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        
        // Log the user in automatically after registration
        return loginUser($username, $password);
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed: ' . $error];
    }
}

/**
 * User login function
 */
function loginUser($username_email, $password) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Sanitize input
    $username_email = sanitizeInput($username_email);
    
    // Find user by username or email
    $query = "SELECT id, username, email, password_hash, full_name, role, is_active 
              FROM users 
              WHERE (username = ? OR email = ?) AND is_active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (verifyPassword($password, $user['password_hash'])) {
            // Update last login
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Create session record (optional)
            createSessionRecord($user['id']);
            
            $stmt->close();
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        }
    }
    
    $stmt->close();
    return ['success' => false, 'message' => 'Invalid username/email or password'];
}

/**
 * Create session record for tracking
 */
function createSessionRecord($user_id) {
    $conn = getDBConnection();
    if (!$conn) return;
    
    $session_id = session_id();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $query = "INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent) 
              VALUES (?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              last_activity = NOW(), ip_address = ?, user_agent = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sissss", $session_id, $user_id, $ip_address, $user_agent, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}

/**
 * Logout user
 */
function logoutUser() {
    // Remove session record
    $conn = getDBConnection();
    if ($conn && isset($_SESSION['user_id'])) {
        $session_id = session_id();
        $query = "DELETE FROM user_sessions WHERE session_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    return ['success' => true, 'message' => 'Logged out successfully'];
}

/**
 * Forgot password - generate reset token
 */
function forgotPassword($email) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    $email = sanitizeInput($email);
    
    // Check if email exists
    $query = "SELECT id FROM users WHERE email = ? AND is_active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update user with reset token
        $updateQuery = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssi", $reset_token, $reset_token_expiry, $user_id);
        
        if ($updateStmt->execute()) {
            // Log the reset request
            logPasswordResetRequest($user_id);
            
            $updateStmt->close();
            $stmt->close();
            
            // In a real application, you would send an email here
            // For now, we'll return the token (in production, send via email)
            return [
                'success' => true, 
                'message' => 'Password reset instructions sent to your email',
                'reset_token' => $reset_token // Remove this in production
            ];
        }
        
        $updateStmt->close();
    }
    
    $stmt->close();
    return ['success' => false, 'message' => 'Email not found or account inactive'];
}

/**
 * Log password reset request
 */
function logPasswordResetRequest($user_id) {
    $conn = getDBConnection();
    if (!$conn) return;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $query = "INSERT INTO password_reset_logs (user_id, ip_address) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $ip_address);
    $stmt->execute();
    $stmt->close();
}

/**
 * Reset password with token
 */
function resetPassword($token, $new_password) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Validate password
    if (!validatePassword($new_password)) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, and number'];
    }
    
    // Check if token is valid and not expired
    $query = "SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Hash new password
        $password_hash = hashPassword($new_password);
        
        // Update password and clear reset token
        $updateQuery = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $password_hash, $user_id);
        
        if ($updateStmt->execute()) {
            // Log the reset completion
            logPasswordResetCompletion($user_id);
            
            $updateStmt->close();
            $stmt->close();
            
            return ['success' => true, 'message' => 'Password reset successfully'];
        }
        
        $updateStmt->close();
    }
    
    $stmt->close();
    return ['success' => false, 'message' => 'Invalid or expired reset token'];
}

/**
 * Log password reset completion
 */
function logPasswordResetCompletion($user_id) {
    $conn = getDBConnection();
    if (!$conn) return;
    
    $query = "UPDATE password_reset_logs SET reset_completed_at = NOW() 
              WHERE user_id = ? AND reset_completed_at IS NULL 
              ORDER BY reset_requested_at DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Update user profile
 */
function updateProfile($user_id, $data) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    $full_name = isset($data['full_name']) ? sanitizeInput($data['full_name']) : null;
    $phone = isset($data['phone']) ? sanitizeInput($data['phone']) : null;
    $email = isset($data['email']) ? sanitizeInput($data['email']) : null;
    
    // Build dynamic query
    $fields = [];
    $params = [];
    $types = "";
    
    if ($full_name !== null) {
        $fields[] = "full_name = ?";
        $params[] = $full_name;
        $types .= "s";
    }
    
    if ($phone !== null) {
        $fields[] = "phone = ?";
        $params[] = $phone;
        $types .= "s";
    }
    
    if ($email !== null) {
        // Check if email already exists (excluding current user)
        $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $email, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            return ['success' => false, 'message' => 'Email already exists'];
        }
        $checkStmt->close();
        
        $fields[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }
    
    if (empty($fields)) {
        return ['success' => false, 'message' => 'No fields to update'];
    }
    
    // Add user_id to params
    $params[] = $user_id;
    $types .= "i";
    
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Update session if email or full_name changed
        if ($email !== null) {
            $_SESSION['email'] = $email;
        }
        if ($full_name !== null) {
            $_SESSION['full_name'] = $full_name;
        }
        
        $stmt->close();
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Update failed: ' . $error];
    }
}

/**
 * Change password
 */
function changePassword($user_id, $current_password, $new_password) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Get current password hash
    $query = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (verifyPassword($current_password, $user['password_hash'])) {
            // Validate new password
            if (!validatePassword($new_password)) {
                $stmt->close();
                return ['success' => false, 'message' => 'New password must be at least 8 characters with uppercase, lowercase, and number'];
            }
            
            // Hash new password
            $new_password_hash = hashPassword($new_password);
            
            // Update password
            $updateQuery = "UPDATE users SET password_hash = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $new_password_hash, $user_id);
            
            if ($updateStmt->execute()) {
                $updateStmt->close();
                $stmt->close();
                return ['success' => true, 'message' => 'Password changed successfully'];
            }
            
            $updateStmt->close();
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
    }
    
    $stmt->close();
    return ['success' => false, 'message' => 'User not found'];
}

// Close database connection on script end
register_shutdown_function(function() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->close();
    }
});
?>