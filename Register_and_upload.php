<?php
// 1. Start the session to carry the CSV data over to the dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Include your database connection for the primary account registration only
require_once 'db.php';

$message = '';

if (isset($_POST['submit_form'])) {
    $username = trim($_POST['username']);
    $plain_password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];

    if (!empty($username) && !empty($plain_password) && !empty($fullname)) {
        try {
            // A. Check if the primary username already exists in the database
            $check_sql = "SELECT COUNT(*) FROM users WHERE username = :username";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([':username' => $username]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $message = "<div style='color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: bold;'>Error: That username is already taken!</div>";
            } else {
                // B. Securely hash the primary user's password
                $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

                // C. Insert only the primary user into your SQL database
                $sql = "INSERT INTO users (username, password, fullname, role) VALUES (:username, :password, :fullname, :role)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':fullname' => $fullname,
                    ':role'     => $role
                ]);

                // D. Establish Session Logins
                $_SESSION['logged_in_user'] = $username;
                $_SESSION['user_fullname'] = $fullname;
                $_SESSION['user_role'] = $role;

                // E. NO SQL FOR CSV: Parse file directly into a temporary PHP session bucket
                if (isset($_FILES['user_csv']) && $_FILES['user_csv']['error'] == 0) {
                    $temporaryFile = $_FILES['user_csv']['tmp_name'];
                    
                    // Initialize an empty array list inside the session memory
                    $_SESSION['csv_dashboard_data'] = [];

                    if (($fileHandle = fopen($temporaryFile, "r")) !== FALSE) {
                        // Skip the first row (the headers)
                        fgetcsv($fileHandle, 1000, ",");
                        
                        // Loop through rows and store them directly in the session
                        while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                            if (!empty($row[0])) {
                                $_SESSION['csv_dashboard_data'][] = [
                                    'username' => htmlspecialchars($row[0]),
                                    'password' => htmlspecialchars($row[1]),
                                    'fullname' => htmlspecialchars($row[2]),
                                    'role'     => htmlspecialchars($row[3])
                                ];
                            }
                        }
                        fclose($fileHandle);
                    }
                }

                // Redirect straight to your dashboard page!
                header("Location: dashboard.php");
                exit();
            }

        } catch (PDOException $e) {
            $message = "<div style='color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 4px; margin-bottom: 20px;'>System SQL Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div style='color: #856404; background-color: #fff3cd; padding: 12px; border-radius: 4px; margin-bottom: 20px;'>Please complete all fields before submitting!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USJR Sign Up & Fast CSV Load</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 40px 20px; }
        .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); max-width: 500px; margin: 0 auto; }
        h2 { color: #222; margin-top: 0; text-align: center; font-size: 24px; }
        p.subtitle { color: #666; text-align: center; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 14px; }
        input[type="text"], input[type="password"], select, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        input[type="file"] { background: #fafafa; padding: 8px; cursor: pointer; }
        .divider { border: 0; border-top: 1px dashed #ddd; margin: 25px 0; }
        .csv-section { background: #fdfffe; border: 1px solid #d4edda; padding: 15px; border-radius: 6px; }
        .csv-section label { color: #155724; }
        button { background: #28a745; color: white; border: none; padding: 14px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 15px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Register & Send CSV to Dashboard</h2>
        <p class="subtitle">Creates your user profile and loads the CSV content straight into the session memory layout.</p>
        
        <?php echo $message; ?>

        <form action="register_and_upload.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Your Username:</label>
                <input type="text" name="username" placeholder="e.g. administrator_acc" required>
            </div>

            <div class="form-group">
                <label>Your Password:</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label>Your Full Name:</label>
                <input type="text" name="fullname" placeholder="e.g. Professor Smith" required>
            </div>

            <div class="form-group">
                <label>System Account Role:</label>
                <select name="role">
                    <option value="Administrator">Administrator</option>
                    <option value="Staff">Staff / System Encoder</option>
                    <option value="Student">Student Representative</option>
                </select>
            </div>

            <div class="divider"></div>

            <div class="csv-section">
                <label style="font-weight: bold;">Instant Dashboard CSV File:</label>
                <p style="margin: 4px 0 12px 0; font-size: 12px; color: #666;">Choose your CSV file to push data directly to the dashboard without SQL insertion.</p>
                <input type="file" name="user_csv" id="user_csv" accept=".csv" required>
            </div>

            <button type="submit" name="submit_form">
                Sign Up & Launch Dashboard
            </button>
        </form>
    </div>

</body>
</html>