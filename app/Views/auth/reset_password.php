<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Unyun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen font-sans">

<div class="w-full max-w-sm">
    <div class="bg-white border border-gray-300 p-10 flex flex-col items-center">
        <!-- 
            1. RESET PASSWORD BRANDING
            Keeps the standalone auth page visually aligned with login and registration.
        -->
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500 mb-2" style="font-family: inherit;">Unyun</h1>
        <p class="text-gray-500 text-sm mb-6 text-center font-semibold">Create a new password</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="w-full bg-red-100 text-red-600 text-sm p-3 mb-4 rounded text-center">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- 
            2. PASSWORD RESET FORM
            Posts the locked email plus both password fields to Auth::resetPasswordSubmit.
        -->
        <form method="post" action="/reset-password-submit" class="w-full flex flex-col gap-3">
            <input type="hidden" name="email" value="<?= esc($email) ?>">
            
            <div class="bg-gray-100 border border-gray-200 text-sm rounded-sm text-gray-500 w-full p-2.5 text-center cursor-not-allowed">
                <?= esc($email) ?>
            </div>

            <input type="password" name="password" placeholder="New Password" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required>
            
            <button type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm w-full py-2.5 mt-2 text-center transition">Reset Password</button>
        </form>
    </div>

    <!-- 3. AUTH MODE SWITCH: Lets the user return to login if they remember their credentials. -->
    <div class="bg-white border border-gray-300 p-5 text-center mt-3 text-sm">
        Remembered it? <a href="/login" class="text-blue-500 font-bold hover:text-blue-700">Back to Login</a>
    </div>
</div>

</body>
</html>
