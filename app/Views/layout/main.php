<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unyun</title>
    <!-- Imports Tailwind CSS securely from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<!-- 
    GLOBAL APPLICATION WRAPPER
    The body uses 'h-screen overflow-hidden' so that only the main content area scrolls, 
    keeping the physical Navigation Bar stuck permanently on mobile/desktop screens.
-->
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col md:flex-row h-screen overflow-hidden">

<?php 
/**
 * 1. CURRENT USER LOOKUP
 * The layout is shared by every authenticated page, so it performs one small lookup
 * to paint the active user's avatar inside the Profile navigation item.
 */
$userId = session()->get('user_id'); 
$db = \Config\Database::connect();
$currentUser = $db->table('users')->where('id', $userId)->get()->getRowArray();
$currentUserPic = $currentUser['profile_pic'] ?? null;
?>

<!-- 
    RESPONSIVE NAVIGATION BAR
    Mobile Mode: Snaps to the 'fixed bottom-0' of the screen and stretches horizontally width-full.
    Desktop Mode ('md:' prefix): Transforms to a fixed 'w-64' sidebar that stretches vertically across the screen.
-->
<nav class="bg-white border-t md:border-r md:border-t-0 border-gray-200 z-50 order-last md:order-first 
            fixed bottom-0 md:relative w-full md:w-64 md:h-screen md:flex md:flex-col md:px-4 py-2 md:py-8 shrink-0">
    
    <!-- Desktop Application Branding Logo -->
    <div class="hidden md:block mb-10 px-4">
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500" style="font-family: inherit;">Unyun</h1>
    </div>

    <!-- 
        3. PRIMARY NAVIGATION LINKS
        Shared across desktop sidebar and mobile bottom nav through responsive Tailwind classes.
    -->
    <ul class="flex md:flex-col justify-around md:justify-start h-full md:gap-4">
        <li>
            <a href="/feed" class="flex items-center md:gap-4 p-3 md:px-4 md:py-3 md:hover:bg-gray-100 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                <span class="hidden md:inline font-medium">Home</span>
            </a>
        </li>
        <li>
            <a href="/quickie" class="flex items-center md:gap-4 p-3 md:px-4 md:py-3 md:hover:bg-gray-100 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                <span class="hidden md:inline font-medium">Quickie</span>
            </a>
        </li>
        <li>
            <a href="/post/create" class="flex items-center md:gap-4 p-3 md:px-4 md:py-3 md:hover:bg-gray-100 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span class="hidden md:inline font-medium">Create</span>
            </a>
        </li>
        <li>
            <!-- Friends page: accepted friends plus incoming friend requests. -->
            <a href="/friends" class="flex items-center md:gap-4 p-3 md:px-4 md:py-3 md:hover:bg-gray-100 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0A5.971 5.971 0 0 0 6 18.72m12.941-3.197a3 3 0 0 0-4.682-2.72M6.942 15.522a3 3 0 0 0-4.682 2.72 9.094 9.094 0 0 0 3.741.479M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                <span class="hidden md:inline font-medium">Friends</span>
            </a>
        </li>
        <li>
            <a href="/profile/<?= $userId ?>" class="flex items-center md:gap-4 p-3 md:px-4 md:py-3 md:hover:bg-gray-100 rounded-lg transition group">
                <?php if ($currentUserPic): ?>
                    <img src="/uploads/profile_pics/<?= $currentUserPic ?>" class="w-7 h-7 rounded-full object-cover group-hover:scale-105 transition-transform border border-gray-300">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                <?php endif; ?>
                <span class="hidden md:inline font-medium">Profile</span>
            </a>
        </li>
    </ul>

    <!-- 
        4. DESKTOP LOGOUT CONTROL
        Mobile logout lives in the top bar because the bottom nav has limited icon space.
    -->
    <div class="hidden md:block mt-auto pb-4">
        <a href="/logout" class="flex items-center gap-4 px-4 py-3 hover:bg-gray-100 rounded-lg transition text-red-500 group">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 group-hover:scale-105 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</nav>

<!-- 
    DYNAMIC PAGE CONTENT AREA 
    CodeIgniter merges all other View files (like feed.php, profile.php, quickie.php) into this section natively.
    Using 'overflow-y-auto' allows only this block to organically scroll while the navigation stays anchored.
-->
<main class="flex-1 overflow-y-auto mb-16 md:mb-0 relative">
    
    <!-- 
        6. MOBILE TOP BAR
        Keeps branding and logout reachable while the main navigation sits fixed at the bottom.
    -->
    <div class="md:hidden sticky top-0 bg-white border-b border-gray-200 z-40 px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500">Unyun</h1>
        <a href="/logout" class="text-red-500">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
        </a>
    </div>

    <!-- 
        7. CODEIGNITER FRAME INJECTION TARGET
        Child views fill this slot with their own `content` section.
    -->
    <?= $this->renderSection('content') ?>
</main>

</body>
</html>
