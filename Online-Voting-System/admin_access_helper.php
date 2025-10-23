<?php
include "connection.php";

echo "<h2>🔐 Admin Access Helper</h2>";

// Check current admin user in database
$result = mysqli_query($con, "SELECT * FROM loginusers WHERE rank = 'admin'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<h3>Current Admin Users in Database:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p><strong>Username:</strong> " . htmlspecialchars($row['username']) . "</p>";
        echo "<p><strong>Password Hash:</strong> " . htmlspecialchars($row['password']) . "</p>";
        echo "<p><strong>Rank:</strong> " . htmlspecialchars($row['rank']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
        echo "<hr>";
    }
} else {
    echo "<p style='color: red;'>No admin users found in database!</p>";
}

// Let's check what password hash cd2104300d75dc8a15336c14cb98cdd5 corresponds to
echo "<h3>Password Hash Analysis:</h3>";
$hash = "cd2104300d75dc8a15336c14cb98cdd5";
echo "<p>Hash: $hash</p>";

// Common passwords to test
$common_passwords = ['admin', 'password', '123456', 'admin123', 'himanshu123', 'himanshu', 'kumar'];

foreach ($common_passwords as $password) {
    $md5_hash = md5($password);
    if ($md5_hash === $hash) {
        echo "<p style='color: green; font-weight: bold;'>✅ FOUND! Password is: <strong>$password</strong></p>";
        break;
    }
}

echo "<h3>🔧 Fix Admin Access</h3>";
echo "<p>If you want to reset the admin password to 'himanshu123' (to match the admin_login.php file), click the button below:</p>";

if (isset($_POST['reset_admin_password'])) {
    $new_password = 'himanshu123';
    $new_hash = md5($new_password);
    
    $update_stmt = mysqli_prepare($con, "UPDATE loginusers SET password = ? WHERE username = 'admin'");
    mysqli_stmt_bind_param($update_stmt, "s", $new_hash);
    
    if (mysqli_stmt_execute($update_stmt)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0;'>";
        echo "<h4>✅ Admin Password Reset Successfully!</h4>";
        echo "<p>You can now login with:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> himanshu123</li>";
        echo "</ul>";
        echo "<p><a href='admin_login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Go to Admin Login</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update admin password!</p>";
    }
    mysqli_stmt_close($update_stmt);
}

if (!isset($_POST['reset_admin_password'])) {
    echo "<form method='post'>";
    echo "<button type='submit' name='reset_admin_password' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🔄 Reset Admin Password to 'himanshu123'</button>";
    echo "</form>";
}

echo "<br><div style='text-align: center;'>";
echo "<a href='admin_login.php' style='margin: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>🔐 Admin Login</a>";
echo "<a href='index.php' style='margin: 10px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>🏠 Home</a>";
echo "</div>";
?>