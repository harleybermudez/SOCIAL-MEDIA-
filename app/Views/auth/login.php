<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Unyun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen font-sans">

<div class="w-full max-w-sm">
    <div class="bg-white border border-gray-300 p-10 flex flex-col items-center">
        <!-- 
            1. AUTHENTICATION BRANDING
            Displays the core gradient logo centered in a minimalist box.
        -->
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500 mb-8" style="font-family: inherit;">Unyun</h1>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="w-full bg-red-100 text-red-600 text-sm p-3 mb-4 rounded text-center">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="w-full bg-green-100 text-green-700 text-sm p-3 mb-4 rounded text-center">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/login" class="w-full flex flex-col gap-3">
            <input type="email" id="emailInput" name="email" placeholder="Email" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required>
            <input type="password" name="password" placeholder="Password" class="bg-gray-50 border border-gray-300 text-sm rounded-sm focus:ring-gray-400 focus:border-gray-400 block w-full p-2.5 outline-none" required>
            
            <button type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm w-full py-2.5 mt-2 text-center transition">Log In</button>
            <a href="#" id="resetBtn" class="text-xs text-center text-gray-400 mt-2 pointer-events-none transition-colors duration-200">Forgot Password?</a>
        </form>
    </div>

    <div class="bg-white border border-gray-300 p-5 text-center mt-3 text-sm">
        Don't have an account? <a href="/register" class="text-blue-500 font-bold hover:text-blue-700">Sign up</a>
    </div>
</div>



<script>
    /**
     * 2. DYNAMIC LOGIN ACTION LISTENERS
     * The "Forgot Password" link remains dead (unclickable) natively.
     * When the user types an email string into the first input, this script awakens the link
     * and injects their email as a GET parameter so the reset-password route can intercept it.
     */
    const emailInput = document.getElementById('emailInput');
    const resetBtn = document.getElementById('resetBtn');

    emailInput.addEventListener('input', (e) => {
        if (e.target.value.trim() !== '') {
            resetBtn.classList.remove('text-gray-400', 'pointer-events-none');
            resetBtn.classList.add('text-blue-500', 'hover:text-blue-700');
            resetBtn.href = '/reset-password?email=' + encodeURIComponent(e.target.value);
        } else {
            resetBtn.classList.add('text-gray-400', 'pointer-events-none');
            resetBtn.classList.remove('text-blue-500', 'hover:text-blue-700');
            resetBtn.href = '#';
        }
    });
</script>

</body>
</html>