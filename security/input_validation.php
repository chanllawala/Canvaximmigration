<?php
// CANVEX Immigration Input Validation
// Comprehensive validation and sanitization for all user inputs

require_once 'security_config.php';

class InputValidator {
    private static $errors = [];
    
    public static function validateName($name) {
        $name = sanitize_input($name);
        
        if (empty($name)) {
            self::$errors[] = 'Name is required';
            return false;
        }
        
        if (strlen($name) < 2) {
            self::$errors[] = 'Name must be at least 2 characters long';
            return false;
        }
        
        if (strlen($name) > 100) {
            self::$errors[] = 'Name must be less than 100 characters';
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
            self::$errors[] = 'Name can only contain letters, spaces, hyphens, apostrophes, and periods';
            return false;
        }
        
        if (check_malicious_patterns($name)) {
            self::$errors[] = 'Name contains invalid characters';
            return false;
        }
        
        return $name;
    }
    
    public static function validateEmail($email) {
        $email = sanitize_input($email);
        
        if (empty($email)) {
            self::$errors[] = 'Email is required';
            return false;
        }
        
        if (!validate_email($email)) {
            self::$errors[] = 'Please enter a valid email address';
            return false;
        }
        
        if (strlen($email) > 255) {
            self::$errors[] = 'Email must be less than 255 characters';
            return false;
        }
        
        // Block suspicious email domains
        $blocked_domains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
        $domain = substr(strrchr($email, '@'), 1);
        
        if (in_array($domain, $blocked_domains)) {
            self::$errors[] = 'Please use a real email address';
            return false;
        }
        
        return $email;
    }
    
    public static function validatePhone($phone) {
        $phone = sanitize_input($phone);
        
        if (empty($phone)) {
            return ''; // Phone is optional
        }
        
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (!validate_phone($phone)) {
            self::$errors[] = 'Please enter a valid phone number (10-15 digits)';
            return false;
        }
        
        return $phone;
    }
    
    public static function validateMessage($message) {
        $message = sanitize_input($message);
        
        if (empty($message)) {
            self::$errors[] = 'Message is required';
            return false;
        }
        
        if (strlen($message) < 10) {
            self::$errors[] = 'Message must be at least 10 characters long';
            return false;
        }
        
        if (strlen($message) > 2000) {
            self::$errors[] = 'Message must be less than 2000 characters';
            return false;
        }
        
        if (check_malicious_patterns($message)) {
            self::$errors[] = 'Message contains invalid content';
            return false;
        }
        
        return $message;
    }
    
    public static function validateAge($age) {
        $age = (int)$age;
        
        if ($age < 18) {
            self::$errors[] = 'You must be at least 18 years old';
            return false;
        }
        
        if ($age > 45) {
            self::$errors[] = 'Age must be 45 or less for Express Entry';
            return false;
        }
        
        return $age;
    }
    
    public static function validateEducation($education) {
        $valid_education = ['highschool', 'bachelors', 'masters', 'phd'];
        
        if (!in_array($education, $valid_education)) {
            self::$errors[] = 'Please select a valid education level';
            return false;
        }
        
        return $education;
    }
    
    public static function validateExperience($experience) {
        $valid_experience = ['less1', '1-2', '3-4', '5plus'];
        
        if (!in_array($experience, $valid_experience)) {
            self::$errors[] = 'Please select a valid experience level';
            return false;
        }
        
        return $experience;
    }
    
    public static function validateLanguage($language) {
        $valid_languages = ['basic', 'moderate', 'good', 'fluent'];
        
        if (!in_array($language, $valid_languages)) {
            self::$errors[] = 'Please select a valid language level';
            return false;
        }
        
        return $language;
    }
    
    public static function validateCRSScore($score) {
        $score = (int)$score;
        
        if ($score < 0 || $score > 1200) {
            self::$errors[] = 'CRS score must be between 0 and 1200';
            return false;
        }
        
        return $score;
    }
    
    public static function validateServices($services) {
        if (empty($services)) {
            self::$errors[] = 'Please select at least one service';
            return false;
        }
        
        $services = sanitize_input($services);
        
        if (check_malicious_patterns($services)) {
            self::$errors[] = 'Services selection contains invalid content';
            return false;
        }
        
        return $services;
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    public static function hasErrors() {
        return !empty(self::$errors);
    }
    
    public static function clearErrors() {
        self::$errors = [];
    }
    
    public static function validateFile($file, $allowed_types = ['pdf', 'doc', 'docx'], $max_size = 5242880) { // 5MB
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                self::$errors[] = 'File size exceeds server limit';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    self::$errors[] = 'File size exceeds form limit';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    self::$errors[] = 'File was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    self::$errors[] = 'No file was uploaded';
                    break;
                default:
                    self::$errors[] = 'Unknown upload error';
                    break;
            }
            return false;
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            self::$errors[] = 'File size must be less than 5MB';
            return false;
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            self::$errors[] = 'Only PDF, DOC, and DOCX files are allowed';
            return false;
        }
        
        // Check for malicious file content
        $file_content = file_get_contents($file['tmp_name']);
        if (check_malicious_patterns($file_content)) {
            self::$errors[] = 'File contains malicious content';
            return false;
        }
        
        return true;
    }
}
?>
