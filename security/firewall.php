<?php
// CANVEX Immigration Web Application Firewall
// Protects against common attacks and suspicious activity

require_once 'security_config.php';

class SecurityFirewall {
    private $blocked_ips = [];
    private $suspicious_patterns = [];
    private $rate_limits = [];
    
    public function __construct() {
        $this->loadBlockedIPs();
        $this->loadSuspiciousPatterns();
        $this->checkCurrentRequest();
    }
    
    private function loadBlockedIPs() {
        $blocked_file = 'security/blocked_ips.json';
        if (file_exists($blocked_file)) {
            $this->blocked_ips = json_decode(file_get_contents($blocked_file), true);
        }
    }
    
    private function loadSuspiciousPatterns() {
        $this->suspicious_patterns = [
            // SQL Injection patterns
            '/union\s+select/i',
            '/select\s+.*\s+from/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+.*\s+set/i',
            '/drop\s+table/i',
            '/create\s+table/i',
            '/alter\s+table/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            
            // XSS patterns
            '/<script[^>]*>.*?<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/eval\s*\(/i',
            '/alert\s*\(/i',
            
            // Path traversal
            '/\.\.\//',
            '/\.\.\\/',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
            
            // Command injection
            '/;\s*rm\s+/i',
            '/;\s*cat\s+/i',
            '/;\s*ls\s+/i',
            '/;\s*wget\s+/i',
            '/;\s*curl\s+/i',
            
            // Bot patterns
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            
            // Suspicious user agents
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/metasploit/i'
        ];
    }
    
    public function checkCurrentRequest() {
        $ip = get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        
        // Check if IP is blocked
        if (is_ip_blocked($ip)) {
            $this->blockRequest('IP_BLOCKED', "IP $ip is blocked");
            return;
        }
        
        // Check for suspicious user agent
        foreach ($this->suspicious_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                $this->blockRequest('SUSPICIOUS_USER_AGENT', "Suspicious user agent detected: $user_agent");
                return;
            }
        }
        
        // Check for suspicious request URI
        foreach ($this->suspicious_patterns as $pattern) {
            if (preg_match($pattern, $request_uri)) {
                $this->blockRequest('SUSPICIOUS_REQUEST', "Suspicious request detected: $request_uri");
                return;
            }
        }
        
        // Check POST data for malicious content
        if ($method === 'POST') {
            $this->checkPostData();
        }
        
        // Rate limiting
        if (!rate_limit_check($ip, 10, 60)) { // 10 requests per minute
            $this->blockRequest('RATE_LIMIT', "Rate limit exceeded for IP: $ip");
            return;
        }
        
        // Log legitimate requests
        log_security_event('LEGITIMATE_REQUEST', "IP: $ip | URI: $request_uri | Method: $method");
    }
    
    private function checkPostData() {
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_value) {
                    if (check_malicious_patterns($sub_value)) {
                        $this->blockRequest('MALICIOUS_POST', "Malicious content detected in POST data: $key");
                        return;
                    }
                }
            } else {
                if (check_malicious_patterns($value)) {
                    $this->blockRequest('MALICIOUS_POST', "Malicious content detected in POST data: $key");
                    return;
                }
            }
        }
    }
    
    private function blockRequest($reason, $details) {
        $ip = get_client_ip();
        
        // Log the blocking event
        log_security_event($reason, $details);
        
        // Block the IP if it's a serious violation
        if (in_array($reason, ['IP_BLOCKED', 'MALICIOUS_POST', 'SUSPICIOUS_REQUEST'])) {
            block_suspicious_ip($ip);
        }
        
        // Send 403 Forbidden response
        http_response_code(403);
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - CANVEX Immigration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .error-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
        .error-container h1 { color: #dc3545; margin-bottom: 1rem; }
        .error-container p { color: #666; margin-bottom: 1.5rem; }
        .error-container code { background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>🛡️ Access Denied</h1>
        <p>Your request has been blocked for security reasons.</p>
        <p><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>
        <p><strong>Details:</strong> <code>' . htmlspecialchars($details) . '</code></p>
        <p><strong>IP Address:</strong> <code>' . htmlspecialchars($ip) . '</code></p>
        <p><strong>Time:</strong> <code>' . date('Y-m-d H:i:s') . '</code></p>
        <p>If you believe this is an error, please contact us.</p>
    </div>
</body>
</html>';
        exit;
    }
}

// Initialize firewall
$firewall = new SecurityFirewall();
?>
