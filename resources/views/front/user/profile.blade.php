@extends('front.layouts.app')

@section('title', 'Akun Saya - Panambas')

@section('content')
<div class="bg-[#f9f9f9] min-h-screen py-4 md:py-8">
    <div class="max-w-7xl mx-auto px-0 md:px-8">
        <div class="flex flex-col md:flex-row gap-0 md:gap-8">
            
            <div class="hidden md:block w-full md:w-1/4 px-4 md:px-0 mb-4 md:mb-0">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center overflow-hidden">
                        @if(Auth::user()->photo)
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        @endif
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-lg">{{ Auth::user()->name }}</div>
                        <a href="{{ route('user.profile') }}" class="text-xs text-gray-500 hover:text-[#247a6b] transition"><i class="fas fa-pen mr-1"></i> Ubah Profil</a>
                    </div>
                </div>

                <div class="bg-white rounded-md border border-gray-100 p-2">
                    <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-3 text-[#247a6b] font-medium bg-[#f0f7f5] rounded-md transition">
                        <i class="far fa-user-circle w-6 text-center mr-3 text-lg"></i> Akun Saya
                    </a>
                    <a href="{{ route('user.orders') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-[#247a6b] hover:bg-gray-50 transition rounded-md mt-1">
                        <i class="fas fa-clipboard-list w-6 text-center mr-3 text-lg"></i> Pesanan Saya
                    </a>
                </div>
            </div>

            <div class="w-full md:w-3/4 bg-white rounded-md border border-gray-200 p-6 md:p-8 shadow-sm">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Profil Saya</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola informasi profil Anda untuk mengontrol, melindungi dan mengamankan akun</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex flex-col-reverse md:flex-row gap-8">
                    <div class="w-full md:w-2/3 md:pr-8 md:border-r border-gray-100">
                        
                        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="flex items-center mb-6">
                                <label class="w-1/3 text-sm text-gray-600 text-right pr-6">Email</label>
                                <div class="w-2/3 text-sm font-medium text-gray-900">
                                    {{ Auth::user()->email }} <span class="text-[#247a6b] ml-2 text-xs cursor-pointer font-bold hover:underline">Ubah</span>
                                </div>
                            </div>

                            <div class="flex items-center mb-6">
                                <label class="w-1/3 text-sm text-gray-600 text-right pr-6">Nama Lengkap</label>
                                <div class="w-2/3">
                                    <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-sm focus:outline-none focus:border-[#247a6b] focus:ring-1 focus:ring-[#247a6b] text-sm text-gray-900 transition">
                                </div>
                            </div>

                            <div class="flex items-center mb-8">
                                <label class="w-1/3 text-sm text-gray-600 text-right pr-6">Jenis Kelamin</label>
                                <div class="w-2/3 flex gap-4 text-sm text-gray-800">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="gender" value="Laki-laki" {{ Auth::user()->gender == 'Laki-laki' ? 'checked' : '' }} class="mr-2 accent-[#247a6b]"> Laki-laki
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="gender" value="Perempuan" {{ Auth::user()->gender == 'Perempuan' ? 'checked' : '' }} class="mr-2 accent-[#247a6b]"> Perempuan
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="w-1/3"></div>
                                <div class="w-2/3">
                                    <button type="submit" class="bg-[#247a6b] hover:bg-[#1b5e52] text-white px-8 py-2.5 rounded-sm shadow-sm transition font-bold text-sm">Simpan</button>
                                </div>
                            </div>
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col items-center justify-center">
                        <div class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-[#f0f7f5] border-2 border-[#247a6b] mb-5 overflow-hidden flex items-center justify-center relative group">
                            
                            <img id="preview_image" src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : '' }}" class="w-full h-full object-cover {{ Auth::user()->photo ? '' : 'hidden' }}">
                            
                            <i id="default_icon" class="fas fa-user text-[#247a6b] text-5xl {{ Auth::user()->photo ? 'hidden' : '' }}"></i>
                            
                        </div>
                        
                        <input type="file" name="photo" id="profile_photo" class="hidden" accept="image/jpeg, image/png, image/jpg" onchange="previewFile()">
                        <label for="profile_photo" class="border border-gray-300 px-4 py-2 text-sm text-gray-700 font-medium bg-white hover:border-gray-900 hover:text-gray-900 cursor-pointer rounded-sm transition">
                            Pilih Gambar
                        </label>
                        <div class="text-xs text-gray-400 mt-4 text-center leading-relaxed">
                            Ukuran gambar: maks. 1 MB<br>Format gambar: .JPEG, .PNG
                        </div>
                        
                        </form> </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fitur buat nampilin preview foto sesaat setelah dipilih dari komputer
    function previewFile() {
        const preview = document.getElementById('preview_image');
        const defaultIcon = document.getElementById('default_icon');
        const file = document.getElementById('profile_photo').files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
            preview.classList.remove('hidden');
            defaultIcon.classList.add('hidden');
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            preview.classList.add('hidden');
            defaultIcon.classList.remove('hidden');
        }
    }
</script>
@endpush