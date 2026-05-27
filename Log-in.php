<?php
session_start();

// Simulated login check or error messaging
$error = isset($_GET['error']) ? "Invalid username or password." : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USJ-R School Management System v1.01</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-[#1e293b] text-white px-6 py-4 flex flex-col md:flex-row justify-between items-center shadow-md">
        <h1 class="text-2xl font-bold tracking-wide mb-4 md:mb-0">
            USJ-R School Management System v1.01
        </h1>
        
        <form action="login.php" method="POST" class="flex flex-wrap items-center gap-3 text-sm">
            <div class="flex items-center gap-1">
                <label for="username" class="font-semibold text-gray-300">Username:</label>
                <input type="text" id="username" name="username" required 
                       class="bg-white text-gray-900 px-2 py-1 rounded border border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 w-36">
            </div>
            <div class="flex items-center gap-1">
                <label for="password" class="font-semibold text-gray-300">Password:</label>
                <input type="password" id="password" name="password" required 
                       class="bg-white text-gray-900 px-2 py-1 rounded border border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 w-36">
            </div>
            <button type="submit" class="bg-[#22c55e] hover:bg-[#16a34a] text-white font-bold px-4 py-1 rounded transition duration-200">
                Login
            </button>
        </form>
    </header>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 text-center">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="bg-[#334155] text-white text-center py-12 px-4 shadow-inner">
        <h2 class="text-4xl font-bold mb-2">Welcome to USJ-R School Management System</h2>
        <p class="text-gray-300 text-lg">Manage your school's operations efficiently</p>
    </section>

    <main class="max-w-7xl mx-auto px-6 py-8">
        
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b-2 border-green-500 pb-2">Quick Access</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                <div class="text-amber-600 text-4xl mb-3"><i class="fa-solid fa-school"></i></div>
                <h4 class="text-xl font-bold text-gray-800 mb-1">Schools</h4>
                <p class="text-gray-500 text-sm">Manage school information and details</p>
            </div><?php
session_start();

// Dummy static credentials for demonstration purposes
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123'); // Replace with password_hash check in real apps

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        // Successful login
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        
        // Redirect to a secure dashboard panel
        header("Location: dashboard.php");
        exit;
    } else {
        // Failed login, redirect back with error flag
        header("Location: index.php?error=1");
        exit;
    }
} else {
    // If accessed directly without POST request, send back to index
    header("Location: index.php");
    exit;
}
?>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                <div class="text-blue-500 text-4xl mb-3"><i class="fa-solid fa-layer-group"></i></div>
                <h4 class="text-xl font-bold text-gray-800 mb-1">Departments</h4>
                <p class="text-gray-500 text-sm">Organize departments within schools</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                <div class="text-indigo-600 text-4xl mb-3"><i class="fa-solid fa-graduation-cap"></i></div>
                <h4 class="text-xl font-bold text-gray-800 mb-1">Programs</h4>
                <p class="text-gray-500 text-sm">Manage academic programs and courses</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                <div class="text-purple-600 text-4xl mb-3"><i class="fa-solid fa-users"></i></div>
                <h4 class="text-xl font-bold text-gray-800 mb-1">Students</h4>
                <p class="text-gray-500 text-sm">Manage student records and enrollment</p>
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6">
            <h3 class="text-xl font-bold text-emerald-900 mb-4">Getting Started</h3>
            <ol class="list-decimal list-inside space-y-2 text-emerald-800 font-medium">
                <li>Log in with your credentials</li>
                <li>Navigate to any section using the sidebar menu</li>
                <li>View, create, update, or delete records as needed</li>
                <li>Contact administrator for access requests</li>
            </ol>
        </div>

    </main>

</body>
</html>