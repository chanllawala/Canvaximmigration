<?php
// CANVEX Immigration Security Configuration
// This file contains all security settings and functions

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\'; connect-src \'self\'; frame-ancestors \'none\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

// Session Security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Error Reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Security Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it's a valid phone number (10-15 digits)
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function rate_limit_check($ip, $limit = 5, $window = 300) {
    $rate_file = 'security/rate_limits.json';
    $current_time = time();
    
    if (!file_exists($rate_file)) {
        file_put_contents($rate_file, json_encode([]));
        return true;
    }
    
    $limits = json_decode(file_get_contents($rate_file), true);
    
    // Clean old entries
    $limits = array_filter($limits, function($entry) use ($current_time, $window) {
        return ($current_time - $entry['timestamp']) < $window;
    });
    
    // Check current IP
    $ip_requests = array_filter($limits, function($entry) use ($ip) {
        return $entry['ip'] === $ip;
    });
    
    if (count($ip_requests) >= $limit) {
        return false;
    }
    
    // Add current request
    $limits[] = ['ip' => $ip, 'timestamp' => $current_time];
    file_put_contents($rate_file, json_encode($limits));
    
    return true;
}

function log_security_event($event_type, $details) {
    $log_file = 'security/security_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = "[$timestamp] IP: $ip | Event: $event_type | Details: $details | User-Agent: $user_agent\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

function check_malicious_patterns($input) {
    $malicious_patterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
        '/javascript:/i',
        '/vbscript:/i',
        '/onload\s*=/i',
        '/onerror\s*=/i',
        '/onclick\s*=/i',
        '/onmouseover\s*=/i',
        '/eval\s*\(/i',
        '/alert\s*\(/i',
        '/document\./i',
        '/window\./i',
        '/union\s+select/i',
        '/drop\s+table/i',
        '/insert\s+into/i',
        '/delete\s+from/i',
        '/update\s+set/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/shell_exec\s*\(/i'
    ];
    
    foreach ($malicious_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

function get_client_ip() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Create security directories if they don't exist
if (!file_exists('security')) {
    mkdir('security', 0755, true);
}

// Block suspicious IPs
function block_suspicious_ip($ip) {
    $blocked_file = 'security/blocked_ips.json';
    $blocked_ips = [];
    
    if (file_exists($blocked_file)) {
        $blocked_ips = json_decode(file_get_contents($blocked_file), true);
    }
    
    if (!in_array($ip, $blocked_ips)) {
        $blocked_ips[] = $ip;
        file_put_contents($blocked_file, json_encode($blocked_ips));
        log_security_event('IP_BLOCKED', "IP $ip blocked due to suspicious activity");
    }
}

function is_ip_blocked($ip) {
    $blocked_file = 'security/blocked_ips.json';
    
    if (!file_exists($blocked_file)) {
        return false;
    }
    
    $blocked_ips = json_decode(file_get_contents($blocked_file), true);
    return in_array($ip, $blocked_ips);
}
?>
