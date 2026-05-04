<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Unyun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen font-sans">

<div class="w-full max-w-sm">
    <div class="bg-white border border-gray-300 p-10 flex flex-col items-center">
        <!-- 
            1. REGISTRATION BRANDING
            Presents the app logo and short onboarding line before account creation.
        -->
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500 mb-4" style="font-family: inherit;">Unyun</h1>
        <p class="text-gray-500 font-semibold text-center mb-6">Sign up to see photos and videos from your friends.</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="w-full bg-red-100 text-red-600 text-sm p-3 mb-4 rounded text-center">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- 
            2. ACCOUNT CREATION FORM
            Posts directly to Auth::store, where password hashing and optional profile data handling occurs.
        -->
        <form method="post" action="/register" class="w-full flex flex-col gap-3">
            <input type="text" name="username" placeholder="Username" value="<?= old('username') ?>" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9]+" title="Only letters and numbers allowed">
            <input type="email" name="email" placeholder="Email" value="<?= old('email') ?>" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required>
            <input type="password" name="password" placeholder="Password" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required minlength="8">
            
            <p class="text-xs text-gray-500 text-center my-3">
                By signing up, you agree to our Terms, Privacy Policy and Cookies Policy.
            </p>

            <button type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm w-full py-2.5 text-center transition">Sign up</button>
        </form>
    </div>

    <!-- 3. AUTH MODE SWITCH: Lets existing users return to the login route. -->
    <div class="bg-white border border-gray-300 p-5 text-center mt-3 text-sm">
        Have an account? <a href="/login" class="text-blue-500 font-bold hover:text-blue-700">Log in</a>
    </div>
</div>

</body>
</html>
