<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';

initSession();

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/app/');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($email, $password)) {
        redirect('/app/');
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trackveil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0F172A',
                        slate2: '#1E293B',
                        teal: '#14B8A6',
                        sky: '#38BDF8',
                        turquoise: '#2DD4BF',
                    },
                    boxShadow: {
                        glow: '0 0 0 2px rgba(56,189,248,.25), 0 0 40px rgba(20,184,166,.25)'
                    }
                }
            }
        }
    </script>
    <style>
        .btn-gradient { background-image: linear-gradient(135deg, #2DD4BF 0%, #38BDF8 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex p-3 rounded-2xl bg-navy text-sky-300 shadow-lg mb-4">
                    <svg viewBox="0 0 64 64" width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="20" stroke="url(#g1)" stroke-width="4"/>
                        <circle cx="32" cy="32" r="12" stroke="url(#g1)" stroke-width="4" opacity=".6"/>
                        <path d="M32 32 L50 20" stroke="url(#g1)" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="26" cy="38" r="3" fill="#38BDF8"/>
                        <circle cx="36" cy="28" r="3" fill="#2DD4BF"/>
                        <circle cx="44" cy="36" r="3" fill="#38BDF8"/>
                        <defs>
                            <linearGradient id="g1" x1="12" y1="52" x2="52" y2="12" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2DD4BF"/>
                                <stop offset="1" stop-color="#38BDF8"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Welcome to Trackveil</h1>
                <p class="text-gray-600 text-sm mt-1">Sign in to your dashboard</p>
            </div>
            
            <!-- Login Form -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/login.php">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            autocomplete="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                            placeholder="you@example.com"
                            value="<?php echo e($_POST['email'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                            placeholder="Enter your password"
                        >
                    </div>
                    
                    <button 
                        type="submit"
                        class="w-full px-4 py-3 text-white font-medium rounded-lg btn-gradient shadow hover:shadow-glow transition"
                    >
                        Sign In
                    </button>
                </form>
                
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>Test credentials: <strong>test@example.com</strong> / <strong>password123</strong></p>
                </div>
            </div>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <a href="/" class="hover:text-gray-700">← Back to website</a>
            </div>
        </div>
    </div>
</body>
</html>

