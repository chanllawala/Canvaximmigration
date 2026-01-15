<?php
// View all form submissions from database
session_start();

// Simple password protection (you should change this)
$password = 'canvex123';
$loggedIn = false;

if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $loggedIn = true;
    }
} elseif (isset($_SESSION['logged_in'])) {
    $loggedIn = true;
}

if (!$loggedIn) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CANVEX - Admin Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
            .login-box h2 { margin: 0 0 1rem 0; color: #333; text-align: center; }
            .login-box input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 4px; }
            .login-box button { width: 100%; padding: 0.75rem; background: #c4161c; color: white; border: none; border-radius: 4px; cursor: pointer; }
            .login-box button:hover { background: #a01219; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>CANVEX Admin</h2>
            <form method="post">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: view_submissions.php');
    exit;
}

try {
    $db = new SQLite3('database/canvex.db');
    
    // Get all submissions
    $contacts = $db->query('SELECT * FROM contacts ORDER BY created_at DESC');
    $consultations = $db->query('SELECT * FROM consultations ORDER BY created_at DESC');
    $assessments = $db->query('SELECT * FROM assessments ORDER BY created_at DESC');
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CANVEX - Form Submissions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: white; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; color: #333; }
        .logout-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; text-decoration: none; }
        .section { background: white; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section h2 { background: #c4161c; color: white; margin: 0; padding: 1rem; border-radius: 8px 8px 0 0; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table tr:hover { background: #f8f9fa; }
        .new { background: #d4edda; }
        .status { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .status.new { background: #28a745; color: white; }
        .status.read { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CANVEX Form Submissions</h1>
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>

    <div class="section">
        <h2>Contact Form Submissions (<?php echo count($contacts); ?>)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $contacts->fetchArray()): ?>
                <tr class="<?php echo $row['status'] === 'new' ? 'new' : ''; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['message'], 0, 100)); ?>...</td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Consultation Requests (<?php echo count($consultations); ?>)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Services</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $consultations->fetchArray()): ?>
                <tr class="<?php echo $row['status'] === 'new' ? 'new' : ''; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['services']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['message'], 0, 100)); ?>...</td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>CRS Assessments (<?php echo count($assessments); ?>)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Age</th>
                    <th>Education</th>
                    <th>Experience</th>
                    <th>Language</th>
                    <th>CRS Score</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assessments->fetchArray()): ?>
                <tr class="<?php echo $row['status'] === 'new' ? 'new' : ''; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo htmlspecialchars($row['education']); ?></td>
                    <td><?php echo htmlspecialchars($row['experience']); ?></td>
                    <td><?php echo htmlspecialchars($row['language']); ?></td>
                    <td><?php echo $row['crs_score']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
