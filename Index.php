<?php
session_start();

$error_message = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USJ-R Login</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#E7E1B1] font-sans flex flex-col items-center justify-center min-h-screen">

    <div class="mb-6">
        <h1 class="text-7xl font-bold text-[#0D530E] 600 tracking-wide">Welcome to USJ-R </h1>
    </div>

    <div class="w-full max-w-xl bg-[#306D29] p-8 shadow-md rounded">
        
        <div class="bg-white rounded p-8 shadow-inner flex flex-col items-center">
            
            <div class="flex items-center gap-3 text-[#306D29] text-4xl font-bold tracking-tight mb-8">
                <i class="fa-solid fa-graduation-cap"></i>
                <h2>Students System</h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="w-full bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded text-xs font-medium mb-4 text-center">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="dashboard.php" method="POST" class="w-full space-y-4">
                
                <div class="relative">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Your Email" 
                           required 
                           class="w-full bg-[#ececec] text-gray-700 placeholder-gray-400 px-4 py-3.5 rounded text-sm outline-none border border-transparent focus:border-gray-300 transition">
                </div>

                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Your Password" 
                           required 
                           class="w-full bg-[#ececec] text-gray-700 placeholder-gray-400 px-4 py-3.5 rounded text-sm outline-none border border-transparent focus:border-gray-300 transition">
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-[#306D29] hover:bg-[#2e82b8] text-white font-bold py-3.5 rounded text-sm tracking-wide shadow cursor-pointer transition">
                        Login
                    </button>
                </div>

            </form>

        </div>
    </div>

</body>
</html>