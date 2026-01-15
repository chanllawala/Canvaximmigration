<?php
// Save form submissions to database with security
require_once '../security/security_config.php';
require_once '../security/firewall.php';
require_once '../security/csrf_protection.php';
require_once '../security/input_validation.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Validate CSRF token
CSRFProtection::requireToken();

try {
    // Create database directory if it doesn't exist
    if (!file_exists('database')) {
        mkdir('database', 0755, true);
    }
    
    // Connect to SQLite database
    $db = new SQLite3('database/canvex.db');
    
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }
    
    // Validate all inputs
    InputValidator::clearErrors();
    
    $formType = $data['form_type'] ?? '';
    
    switch ($formType) {
        case 'contact':
            $validatedData = validateContactData($data);
            break;
        case 'consultation':
            $validatedData = validateConsultationData($data);
            break;
        case 'assessment':
            $validatedData = validateAssessmentData($data);
            break;
        default:
            throw new Exception('Unknown form type');
    }
    
    if (InputValidator::hasErrors()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Validation failed',
            'errors' => InputValidator::getErrors()
        ]);
        exit;
    }
    
    // Save validated data
    saveValidatedData($db, $formType, $validatedData);
    
    $db->close();
    
} catch (Exception $e) {
    log_security_event('DATABASE_ERROR', $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving submission: ' . $e->getMessage()
    ]);
}

function validateContactData($data) {
    return [
        'name' => InputValidator::validateName($data['name'] ?? ''),
        'email' => InputValidator::validateEmail($data['email'] ?? ''),
        'phone' => InputValidator::validatePhone($data['phone'] ?? ''),
        'message' => InputValidator::validateMessage($data['message'] ?? ''),
        'form_type' => 'contact'
    ];
}

function validateConsultationData($data) {
    return [
        'name' => InputValidator::validateName($data['Name'] ?? ''),
        'email' => InputValidator::validateEmail($data['Email'] ?? ''),
        'phone' => InputValidator::validatePhone($data['Phone Number'] ?? ''),
        'services' => InputValidator::validateServices($data['Services Interested In:'] ?? ''),
        'message' => InputValidator::validateMessage($data['Message'] ?? ''),
        'form_type' => 'consultation'
    ];
}

function validateAssessmentData($data) {
    return [
        'name' => InputValidator::validateName($data['name'] ?? ''),
        'email' => InputValidator::validateEmail($data['email'] ?? ''),
        'phone' => InputValidator::validatePhone($data['phone'] ?? ''),
        'age' => InputValidator::validateAge($data['age'] ?? 0),
        'education' => InputValidator::validateEducation($data['education'] ?? ''),
        'experience' => InputValidator::validateExperience($data['experience'] ?? ''),
        'language' => InputValidator::validateLanguage($data['language'] ?? ''),
        'crs_score' => InputValidator::validateCRSScore($data['crs_score'] ?? 0),
        'form_type' => 'assessment'
    ];
}

function saveValidatedData($db, $formType, $validatedData) {
    switch ($formType) {
        case 'contact':
            saveContact($db, $validatedData);
            break;
        case 'consultation':
            saveConsultation($db, $validatedData);
            break;
        case 'assessment':
            saveAssessment($db, $validatedData);
            break;
    }
}

function saveContact($db, $data) {
    $stmt = $db->prepare('
        INSERT INTO contacts (name, email, phone, message, form_type) 
        VALUES (:name, :email, :phone, :message, :form_type)
    ');
    
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':phone', $data['phone']);
    $stmt->bindValue(':message', $data['message']);
    $stmt->bindValue(':form_type', $data['form_type']);
    
    if ($stmt->execute()) {
        log_security_event('CONTACT_SUBMITTED', "Contact form submitted from IP: " . get_client_ip());
        echo json_encode([
            'success' => true, 
            'message' => 'Contact form saved successfully!',
            'id' => $db->lastInsertRowID()
        ]);
    } else {
        throw new Exception('Failed to save contact form');
    }
}

function saveConsultation($db, $data) {
    $stmt = $db->prepare('
        INSERT INTO consultations (name, email, phone, services, message) 
        VALUES (:name, :email, :phone, :services, :message)
    ');
    
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':phone', $data['phone']);
    $stmt->bindValue(':services', $data['services']);
    $stmt->bindValue(':message', $data['message']);
    
    if ($stmt->execute()) {
        log_security_event('CONSULTATION_SUBMITTED', "Consultation request submitted from IP: " . get_client_ip());
        echo json_encode([
            'success' => true, 
            'message' => 'Consultation request saved successfully!',
            'id' => $db->lastInsertRowID()
        ]);
    } else {
        throw new Exception('Failed to save consultation request');
    }
}

function saveAssessment($db, $data) {
    $stmt = $db->prepare('
        INSERT INTO assessments (name, email, phone, age, education, experience, language, crs_score) 
        VALUES (:name, :email, :phone, :age, :education, :experience, :language, :crs_score)
    ');
    
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':phone', $data['phone']);
    $stmt->bindValue(':age', $data['age']);
    $stmt->bindValue(':education', $data['education']);
    $stmt->bindValue(':experience', $data['experience']);
    $stmt->bindValue(':language', $data['language']);
    $stmt->bindValue(':crs_score', $data['crs_score']);
    
    if ($stmt->execute()) {
        log_security_event('ASSESSMENT_SUBMITTED', "CRS assessment submitted from IP: " . get_client_ip());
        echo json_encode([
            'success' => true, 
            'message' => 'Assessment saved successfully!',
            'id' => $db->lastInsertRowID()
        ]);
    } else {
        throw new Exception('Failed to save assessment');
    }
}
?>
