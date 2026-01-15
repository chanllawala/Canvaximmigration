# CANVEX Immigration Database Setup

## Overview
This database system stores all form submissions locally until you set up your email and domain. No email configuration required!

## Files Created
- `setup.php` - Database initialization script
- `save_submission.php` - Saves form data to database
- `view_submissions.php` - Admin panel to view submissions
- `canvex.db` - SQLite database file (created automatically)

## Quick Setup (5 minutes)

### Step 1: Initialize Database
1. Upload the entire `database` folder to your web server
2. Visit: `https://your-website.com/database/setup.php`
3. You should see: "Database setup completed successfully!"

### Step 2: Test Forms
1. Go to your website and submit a test contact form
2. Submit a test consultation request
3. Submit a test assessment
4. All should save successfully

### Step 3: View Submissions
1. Visit: `https://your-website.com/database/view_submissions.php`
2. Login with password: `canvex123`
3. You'll see all submitted forms organized by type

## Database Tables

### Contacts Table
- Stores contact form submissions
- Fields: name, email, phone, message, created_at, status

### Consultations Table  
- Stores consultation requests
- Fields: name, email, phone, services, message, created_at, status

### Assessments Table
- Stores CRS assessment results
- Fields: name, email, phone, age, education, experience, language, crs_score, created_at, status

## Security Notes

### Change Admin Password
Edit this line in `view_submissions.php`:
```php
$password = 'canvex123'; // Change this to your secure password
```

### File Permissions
- Ensure `database` folder is writable (755 permissions)
- Database file will be created automatically

## Features

### Automatic Data Organization
- All forms are automatically categorized
- Timestamps for every submission
- Status tracking (new/read)

### Admin Panel Features
- View all submissions in organized tables
- Sort by date (newest first)
- Color-coded status indicators
- Mobile-friendly interface

### No Email Required
- Forms work immediately after setup
- Data stored locally and securely
- Easy export when you get email ready

## Next Steps

### When You Get Email Ready
1. Update forms to send emails instead of saving to database
2. Keep database as backup
3. Export existing data for follow-up

### When You Get Domain Ready
1. Update all email addresses to your domain
2. Set up professional email addresses
3. Configure SSL certificate

## Troubleshooting

### Database Not Creating
- Check folder permissions (755)
- Ensure PHP has write access
- Check PHP error logs

### Can't View Submissions
- Verify database file exists
- Check admin password
- Clear browser cache

### Forms Not Saving
- Check JavaScript console for errors
- Verify `save_submission.php` is accessible
- Check network tab in browser dev tools

## Support
This database system is designed to work immediately without any external dependencies. All form submissions are stored locally and can be accessed through the admin panel.
