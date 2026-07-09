<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Panambas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#247a6b] min-h-screen flex flex-col">
    <header class="bg-white py-4 px-8 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="{{ url('/') }}" class="flex items-center text-gray-900 font-bold text-3xl tracking-tighter">
                <i class="fas fa-seedling text-[#247a6b] mr-2"></i> PANAMBAS
            </a>
            <span class="text-2xl font-medium text-gray-800">Ubah Password</span>
        </div>
        <a href="#" class="text-[#247a6b] text-sm hover:underline">Butuh bantuan?</a>
    </header>

    <main class="flex-grow flex items-center justify-center px-4 md:px-20 py-10 max-w-7xl mx-auto w-full relative">
        <div class="hidden md:block w-1/2 pr-10 text-white">
            <div class="flex items-center justify-center flex-col h-full">
                <i class="fas fa-seedling text-[100px] mb-4"></i>
                <h1 class="text-5xl font-bold mb-2">Panambas</h1>
                <p class="text-2xl">Lebih Segar Lebih Cepat</p>
            </div>
        </div>

        <div class="w-full md:w-[400px] bg-white rounded-lg shadow-xl p-8">
            <h2 class="text-2xl font-medium text-gray-800 mb-8">Ubah Password</h2>

            @if($errors->any())
                <div id="errorMessage" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
                <script>setTimeout(() => { document.getElementById('errorMessage').style.display = 'none'; }, 4000);</script>
            @endif

            <form action="{{ route('forgot-password.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Terdaftar" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-[#247a6b]" required>
                </div>
                <div class="mb-4 relative">
                    <input type="password" name="password" placeholder="Password Baru" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-[#247a6b]" required>
                </div>
                <div class="mb-8 relative">
                    <input type="password" name="password_confirmation" placeholder="Konfirmasi Password Baru" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-[#247a6b]" required>
                </div>
                <button type="submit" class="w-full bg-[#247a6b] hover:bg-[#1b5e52] text-white font-medium py-3 rounded uppercase transition duration-200 shadow-md">UBAH PASSWORD</button>
            </form>

            <p class="text-center text-gray-600 text-sm mt-8">
                Ingat password? <a href="{{ route('login') }}" class="text-[#247a6b] font-bold hover:underline">Log in</a>
            </p>
        </div>
    </main>
</body>
</html>