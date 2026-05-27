<?php
session_start();

// 1. Connect to the global database configurations layer
require_once 'db.php';

// 2. Backup admin credentials for local processing safety
if (!isset($_SESSION['admin_user'])) {
    $_SESSION['admin_user'] = 'admin';
    $_SESSION['admin_pass'] = 'admin123';
}

// 3. Process primary inbound submission parameters from Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['action'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $_SESSION['admin_user'] && $password === $_SESSION['admin_pass']) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
}

// 4. Session Protection Guard: If unauthorized, redirect to login
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: login.php");
    exit;
}

// 5. Detect and assign the current active routing view state context
$current_view = $_GET['view'] ?? 'home';
$db_error = null;
$success_message = null;

// Control state indicators for system structural edits
$edit_mode = false;
$edit_data = [];

// Helper Function: Read users from the local CSV storage file safely (No SQL used!)
function read_users_from_csv() {
    $csv_file = 'users.csv';
    $users = [];
    if (file_exists($csv_file)) {
        if (($handle = fopen($csv_file, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== FALSE) {
                if (empty($data) || !isset($data[0])) {
                    continue;
                }
                $first_col = strtolower(trim($data[0]));
                if ($first_col === 'username' || $first_col === 'user name' || empty($first_col)) {
                    continue;
                }
                if (count($data) >= 4) {
                    $users[] = [
                        'username' => trim($data[0]),
                        'password' => trim($data[1]),
                        'fullname' => trim($data[2]),
                        'role'     => trim($data[3])
                    ];
                }
            }
            fclose($handle);
        }
    }
    return $users;
}

// 6. ACTION INTERCEPT ROUTER: Dispatches actions to their matching sub-views
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if (strpos($action, '_user') !== false) {
            include_once 'views/users.php';
        } elseif (strpos($action, '_college') !== false) {
            include_once 'views/schools.php';
        } elseif (strpos($action, '_department') !== false) {
            include_once 'views/departments.php';
        } elseif (strpos($action, '_program') !== false) {
            include_once 'views/programs.php';
        } elseif (strpos($action, '_student') !== false) {
            include_once 'views/students.php';
        }
    }

    if (isset($_GET['edit_id'])) {
        $edit_mode = true;
        $target_id = $_GET['edit_id'];
        
        if ($current_view === 'users') {
            $all_csv_users = read_users_from_csv();
            foreach ($all_csv_users as $u) {
                if ($u['username'] === $target_id) {
                    $edit_data = $u;
                    break;
                }
            }
        } elseif ($current_view === 'courses') {
            $stmt = $pdo->prepare("SELECT * FROM colleges WHERE collid = ?");
            $stmt->execute([$target_id]);
            $edit_data = $stmt->fetch() ?: [];
        } elseif ($current_view === 'departments') {
            $stmt = $pdo->prepare("SELECT * FROM departments WHERE deptid = ?");
            $stmt->execute([$target_id]);
            $edit_data = $stmt->fetch() ?: [];
        } elseif ($current_view === 'enrollments') {
            $stmt = $pdo->prepare("SELECT * FROM programs WHERE progid = ?");
            $stmt->execute([$target_id]);
            $edit_data = $stmt->fetch() ?: [];
        } elseif ($current_view === 'students') {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE studid = ?");
            $stmt->execute([$target_id]);
            $edit_data = $stmt->fetch() ?: [];
        }
    }

} catch (PDOException $e) {
    $db_error = "Database Routing Action Failed: " . $e->getMessage();
}

// 7. DATA MATRIX INITIALIZATION: Pre-hydrating layouts and metric blocks
$total_students = $total_schools = $total_departments = $total_programs = $total_users = 0;
$users_list = $colleges_list = $departments_list = $programs_list = $students_list = [];

try {
    $total_students = $pdo->query("SELECT COUNT(*) FROM `students`")->fetchColumn();
    $total_schools = $pdo->query("SELECT COUNT(*) FROM `colleges`")->fetchColumn();
    $total_departments = $pdo->query("SELECT COUNT(*) FROM `departments`")->fetchColumn();
    $total_programs = $pdo->query("SELECT COUNT(*) FROM `programs`")->fetchColumn();
    
    $users_list = read_users_from_csv();
    $total_users = count($users_list);

    $colleges_list = $pdo->query("SELECT * FROM colleges ORDER BY collfullname ASC")->fetchAll();
    $departments_list = $pdo->query("SELECT d.*, c.collshortname FROM departments d JOIN colleges c ON d.deptcollid = c.collid ORDER BY d.deptfullname ASC")->fetchAll();
    $programs_list = $pdo->query("SELECT p.*, c.collshortname, d.deptfullname FROM programs p JOIN colleges c ON p.progcollid = c.collid JOIN departments d ON p.progcolldeptid = d.deptid ORDER BY p.progfullname ASC")->fetchAll();
    
    try {
        $students_list = $pdo->query("SELECT s.*, p.progshortname, c.collshortname FROM students s JOIN programs p ON s.studprogid = p.progid JOIN colleges c ON s.studcollid = c.collid ORDER BY s.studlastname ASC")->fetchAll();
    } catch (PDOException $ex) {
        $students_list = [];
    }
} catch (PDOException $e) {
    $db_error = "Operational Core Sync Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USJ-R Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#d5d5d5] font-sans text-gray-800 m-0 p-0 overflow-hidden h-screen w-screen flex flex-col">

    <header class="w-full text-center py-4 bg-[#d5d5d5] flex-shrink-0">
        <h1 class="text-3xl font-bold text-gray-600 tracking-wide">USJ-R School Management System</h1>
    </header>

    <div class="flex flex-1 max-w-7xl w-full mx-auto bg-white border border-gray-300 rounded shadow-md overflow-hidden mb-6">
        
        <aside class="w-60 bg-[#344557] text-white flex flex-col justify-between p-4 h-full flex-shrink-0">
            <div>
                <div class="text-center py-3 border-b border-gray-600">
                    <h2 class="text-xl font-bold text-orange-400">Dashboard</h2>
                </div>
                <nav class="mt-4 space-y-1.5">
                    <a href="dashboard.php?view=home" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'home' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-house text-xs"></i> Home</a>
                    <a href="dashboard.php?view=students" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'students' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-user-graduate text-xs"></i> Students</a>
                    <a href="dashboard.php?view=courses" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'courses' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-graduation-cap text-xs"></i> Schools</a>
                    <a href="dashboard.php?view=departments" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'departments' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-sitemap text-xs"></i> Departments</a>
                    <a href="dashboard.php?view=enrollments" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'enrollments' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-book text-xs"></i> Programs</a>
                    <a href="dashboard.php?view=users" class="flex items-center gap-3 px-3 py-2 rounded text-sm w-full <?php echo $current_view === 'users' ? 'bg-[#3a9ad9] font-bold' : 'hover:bg-slate-600'; ?>"><i class="fa-solid fa-users-gear text-xs"></i> Users</a>
                </nav>
            </div>
            <div class="text-center pt-4 border-t border-gray-600">
                <p class="text-xs text-gray-300 mb-2">Logged in: <b><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></b></p>
                <a href="logout.php" class="inline-block w-full bg-[#a24bb1] hover:bg-[#8d3fa0] text-white text-xs font-bold py-2 rounded text-center">Logout</a>
            </div>
        </aside>

        <main class="flex-1 bg-[#ececec] p-6 overflow-y-auto h-full">
            
            <?php if ($db_error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs font-mono mb-4"><strong>Warning/Error:</strong> <?php echo $db_error; ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded text-xs font-semibold mb-4"><i class="fa-solid fa-circle-check mr-1"></i> <?php echo $success_message; ?></div><?php endif; ?>

            <?php if ($current_view === 'home'): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl mx-auto pt-4">
                    <a href="dashboard.php?view=students" class="bg-white hover:shadow-md border p-8 rounded text-center flex flex-col items-center justify-center transform hover:-translate-y-0.5 transition-all group">
                        <div class="text-[#3a9ad9] text-4xl mb-2 group-hover:scale-105 transition-transform"><i class="fa-solid fa-user-graduate"></i></div>
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wide">Students</h4>
                        <span class="text-3xl font-extrabold text-gray-700 mt-2"><?php echo $total_students; ?></span>
                    </a>
                    
                    <a href="dashboard.php?view=courses" class="bg-white hover:shadow-md border p-8 rounded text-center flex flex-col items-center justify-center transform hover:-translate-y-0.5 transition-all group">
                        <div class="text-[#3a9ad9] text-4xl mb-2 group-hover:scale-105 transition-transform"><i class="fa-solid fa-graduation-cap"></i></div>
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wide">Schools</h4>
                        <span class="text-3xl font-extrabold text-gray-700 mt-2"><?php echo $total_schools; ?></span>
                    </a>
                    
                    <a href="dashboard.php?view=departments" class="bg-white hover:shadow-md border p-8 rounded text-center flex flex-col items-center justify-center transform hover:-translate-y-0.5 transition-all group">
                        <div class="text-[#3a9ad9] text-4xl mb-2 group-hover:scale-105 transition-transform"><i class="fa-solid fa-sitemap"></i></div>
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wide">Departments</h4>
                        <span class="text-3xl font-extrabold text-gray-700 mt-2"><?php echo $total_departments; ?></span>
                    </a>
                    
                    <a href="dashboard.php?view=enrollments" class="bg-white hover:shadow-md border p-8 rounded text-center flex flex-col items-center justify-center transform hover:-translate-y-0.5 transition-all group">
                        <div class="text-[#3a9ad9] text-4xl mb-2 group-hover:scale-105 transition-transform"><i class="fa-solid fa-book-open"></i></div>
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wide">Programs</h4>
                        <span class="text-3xl font-extrabold text-gray-700 mt-2"><?php echo $total_programs; ?></span>
                    </a>

                    <div class="bg-white border p-5 rounded text-center flex flex-col sm:flex-row items-center justify-between px-10 md:col-span-2 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="text-[#3a9ad9] text-2xl"><i class="fa-solid fa-users-gear"></i></div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Registered Accounts (CSV File Source Container)</h4>
                        </div>
                        <span class="text-xl font-extrabold text-gray-700"><?php echo $total_users; ?> Active System Users</span>
                    </div>
                </div>

            <?php elseif ($current_view === 'students'): ?>
                <?php include_once 'views/students.php'; ?>

            <?php elseif ($current_view === 'users'): ?>
                <?php include_once 'views/users.php'; ?>

            <?php elseif ($current_view === 'courses'): ?>
                <?php include_once 'views/schools.php'; ?>

            <?php elseif ($current_view === 'departments'): ?>
                <?php include_once 'views/departments.php'; ?>

            <?php elseif ($current_view === 'enrollments'): ?>
                <?php include_once 'views/programs.php'; ?>
                
            <?php endif; ?>
        </main>
    </div>

</body>
</html>