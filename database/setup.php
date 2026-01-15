<?php
// Database Setup Script for CANVEX Immigration
// This creates the SQLite database and tables

try {
    // Create database directory if it doesn't exist
    if (!file_exists('database')) {
        mkdir('database', 0755, true);
    }
    
    // Create SQLite database
    $db = new SQLite3('database/canvex.db');
    
    // Enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON');
    
    // Create contacts table
    $db->exec('
        CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            message TEXT NOT NULL,
            form_type TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT "new"
        )
    ');
    
    // Create consultations table
    $db->exec('
        CREATE TABLE IF NOT EXISTS consultations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            services TEXT,
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT "new"
        )
    ');
    
    // Create assessments table
    $db->exec('
        CREATE TABLE IF NOT EXISTS assessments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            age INTEGER,
            education TEXT,
            experience TEXT,
            language TEXT,
            crs_score INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT "new"
        )
    ');
    
    echo "Database setup completed successfully!";
    
} catch (Exception $e) {
    echo "Database setup failed: " . $e->getMessage();
}
?>
