<?php
// CANVEX Immigration CSRF Protection
// Prevents Cross-Site Request Forgery attacks

require_once 'security_config.php';

class CSRFProtection {
    private static $token_length = 32;
    private static $session_token_key = 'csrf_token';
    private static $session_time_key = 'csrf_token_time';
    private static $token_expiry = 3600; // 1 hour
    
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(self::$token_length / 2));
        $_SESSION[self::$session_token_key] = $token;
        $_SESSION[self::$session_time_key] = time();
        
        return $token;
    }
    
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$session_token_key]) {
            return false;
        }
        
        if (!isset($_SESSION[self::$session_time_key])) {
            return false;
        }
        
        // Check if token has expired
        if (time() - $_SESSION[self::$session_time_key] > self::$token_expiry) {
            self::clearToken();
            return false;
        }
        
        // Validate token
        if (!hash_equals($_SESSION[self::$session_token_key], $token)) {
            return false;
        }
        
        return true;
    }
    
    public static function clearToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::$session_token_key]);
        unset($_SESSION[self::$session_time_key]);
    }
    
    public static function getHiddenInput() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    public static function requireToken() {
        if (!self::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Error - CANVEX Immigration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .error-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
        .error-container h1 { color: #dc3545; margin-bottom: 1rem; }
        .error-container p { color: #666; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>🔒 Security Error</h1>
        <p>Invalid security token detected.</p>
        <p>This may be due to:</p>
        <ul style="text-align: left; display: inline-block;">
            <li>Session expired</li>
            <li>Browser cookies disabled</li>
            <li>Potential CSRF attack attempt</li>
        </ul>
        <p>Please refresh the page and try again.</p>
        <p><a href="javascript:history.back()">← Go Back</a></p>
    </div>
</body>
</html>';
            exit;
        }
    }
}

// Auto-generate token for forms
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION[CSRFProtection::$session_token_key])) {
    CSRFProtection::generateToken();
}
?>
