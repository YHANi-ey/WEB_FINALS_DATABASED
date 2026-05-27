<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-white font-sans flex flex-col items-center justify-center min-h-screen p-4">

    <div class="text-center space-y-6 max-w-xl w-full">
        
        <div class="space-y-1">
            <h1 class="text-xl font-bold text-black">User Dashboard</h1>
            <p class="text-base text-black font-normal">
                Welcome to the User Dashboard. Here you can manage user accounts and settings.
            </p>
        </div>

        <div class="flex flex-col items-center justify-center gap-4 pt-2">
            
            <a href="dashboard.php?view=users" 
               class="inline-block w-44 bg-[#cccccc] text-white font-medium text-center py-2 px-4 rounded shadow-xs hover:bg-[#b8b8b8] transition duration-200 cursor-pointer">
                Manage Users
            </a>

            <a href="dashboard.php?view=users" 
               class="inline-block w-44 bg-[#4caf50] text-white font-medium text-center py-2 px-4 rounded shadow-xs hover:bg-[#43a047] transition duration-200 cursor-pointer">
                Add Users
            </a>
            
        </div>
    </div>

</body>
</html>