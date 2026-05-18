<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Berhasil!</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-100 h-screen flex items-center justify-center font-sans">

<div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-sm text-center transform transition duration-500 hover:scale-105">
    
    <div class="flex justify-center mb-6">
        <div class="bg-green-500 w-20 h-20 rounded-full flex items-center justify-center shadow-lg shadow-green-200">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
    </div>

    <h2 class="text-2xl font-bold text-green-600 mb-1">Login Berhasil! ✨</h2>
    <p class="text-gray-500 text-sm mb-6">OTP valid, akunmu aman.</p>

    <div class="bg-green-500 text-white py-2 rounded-lg font-bold mb-6 tracking-widest text-sm">
        AKUN TERVERIFIKASI
    </div>

    <a href="{{ route('otp.dashboard') }}" 
       class="block w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-2xl transition duration-300 shadow-lg shadow-pink-200 uppercase text-sm tracking-wider">
        Masuk ke Dashboard
    </a>
</div>

</body>
</html>