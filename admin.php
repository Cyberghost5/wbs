<?php
require_once __DIR__ . '/config/database.php';

// Simple password protection
session_start();
$admin_password = 'wbs2026admin'; // Change this!

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$db = getDBConnection();
if (!$db) {
    die("Database connection failed");
}

// Get statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM registrations) as total_registrations,
    (SELECT COUNT(*) FROM registrations WHERE delegate_type='local') as local_count,
    (SELECT COUNT(*) FROM registrations WHERE delegate_type='foreign') as foreign_count,
    (SELECT COUNT(*) FROM contact_messages) as total_messages,
    (SELECT COUNT(*) FROM contact_messages WHERE status='new') as new_messages";
$statsResult = $db->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get recent registrations
$registrations = $db->query("SELECT * FROM registrations ORDER BY created_at DESC LIMIT 50");

// Get recent messages
$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50");

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>WBS 2026 Admin Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1a237e 0%, #283593 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 400px; width: 90%; }
            h1 { color: #1a237e; margin-bottom: 30px; text-align: center; }
            input[type="password"] { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; margin-bottom: 20px; }
            button { width: 100%; padding: 12px; background: #ff6b35; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
            button:hover { background: #e55a28; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>WBS 2026 Admin</h1>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter admin password" required autofocus>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WBS 2026 - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #1a237e 0%, #283593 100%); color: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; }
        .header .logout { float: right; background: #ff6b35; padding: 8px 20px; border-radius: 5px; text-decoration: none; color: white; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: #1a237e; }
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .section h2 { color: #1a237e; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ff6b35; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9f9f9; font-weight: bold; color: #333; }
        tr:hover { background: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-local { background: #4caf50; color: white; }
        .badge-foreign { background: #2196f3; color: white; }
        .badge-new { background: #ff9800; color: white; }
        .badge-read { background: #9e9e9e; color: white; }
        .email { color: #2196f3; }
        .date { color: #666; font-size: 12px; }
        .export-btn { background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 10px; }
        .export-btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-bullseye"></i> WBS 2026 Admin Dashboard</h1>
        <a href="?logout=1" class="logout">Logout</a>
        <div style="clear: both;"></div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total Registrations</h3>
                <div class="number"><?php echo $stats['total_registrations']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Local Delegates</h3>
                <div class="number"><?php echo $stats['local_count']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Foreign Delegates</h3>
                <div class="number"><?php echo $stats['foreign_count']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Contact Messages</h3>
                <div class="number"><?php echo $stats['total_messages']; ?></div>
            </div>
            <div class="stat-card">
                <h3>New Messages</h3>
                <div class="number"><?php echo $stats['new_messages']; ?></div>
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-clipboard-list"></i> Recent Registrations</h2>
            <button class="export-btn" onclick="exportToCSV('registrations')">Export Registrations</button>
            <?php if ($registrations->num_rows > 0): ?>
                <table id="registrations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Organization</th>
                            <th>Type</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $registrations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['country']); ?></td>
                                <td><?php echo htmlspecialchars($row['organization']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['delegate_type']; ?>">
                                        <?php echo strtoupper($row['delegate_type']); ?>
                                    </span>
                                </td>
                                <td class="date"><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; padding: 20px; text-align: center;">No registrations yet.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-comments"></i> Contact Messages</h2>
            <button class="export-btn" onclick="exportToCSV('messages')">Export Messages</button>
            <?php if ($messages->num_rows > 0): ?>
                <table id="messages-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $messages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <td class="date"><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; padding: 20px; text-align: center;">No messages yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function exportToCSV(type) {
            const table = document.getElementById(type + '-table');
            let csv = [];
            
            // Headers
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
            csv.push(headers.join(','));
            
            // Rows
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = Array.from(row.querySelectorAll('td')).map(td => {
                    let text = td.textContent.trim();
                    return '"' + text.replace(/"/g, '""') + '"';
                });
                csv.push(rowData.join(','));
            });
            
            // Download
            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'wbs2026_' + type + '_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
        }
    </script>
</body>
</html>
<?php $db->close(); ?>
