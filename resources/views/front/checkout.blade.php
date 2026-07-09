@extends('front.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-8 text-gray-800">Checkout Pesanan</h2>
    <form action="{{ route('process.order') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- BAGIAN KIRI: ALAMAT -->
            <div class="space-y-6">
                <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                    <h3 class="font-bold text-lg text-gray-800">Alamat Pengiriman</h3>
                    @if($addresses->isNotEmpty())
                        <button type="button" onclick="openAddressModal()" class="text-[#247a6b] text-sm font-bold hover:underline">
                            Pilih Alamat Lain
                        </button>
                    @endif
                </div>
                
                @php
                    $activeAddress = $addresses->where('is_primary', true)->first() ?? $addresses->first();
                @endphp

                @if($activeAddress)
                    <div class="border border-[#247a6b] rounded-lg p-5 relative bg-[#f0f7f5] shadow-sm transition-all">
                        <div class="flex items-start">
                            <div class="bg-white p-2 rounded-full shadow-sm mr-4 mt-1 border border-[#247a6b]/20">
                                <i class="fas fa-map-marker-alt text-[#247a6b] text-lg w-5 text-center"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-[15px]">
                                    <span id="display-name">{{ $activeAddress->receiver_name }}</span> 
                                    <span class="text-gray-500 font-normal ml-2" id="display-phone">{{ $activeAddress->phone }}</span>
                                </p>
                                <p class="text-gray-600 text-sm mt-2 leading-relaxed" id="display-address">
                                    {{ $activeAddress->detail_address }}<br>
                                    {{ $activeAddress->district }}, {{ $activeAddress->city }}, {{ $activeAddress->province }}, {{ $activeAddress->postal_code }}
                                </p>
                                @if($activeAddress->is_primary)
                                    <span class="inline-block mt-3 px-2.5 py-1 bg-[#247a6b]/10 text-[#247a6b] text-[10px] font-bold uppercase rounded border border-[#247a6b]/20">Alamat Utama</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Input Hidden yang dikirim ke Database Seller (Format Rapi) -->
                    <input type="hidden" name="name" id="input-name" value="{{ $activeAddress->receiver_name }}">
                    <input type="hidden" name="phone" id="input-phone" value="{{ $activeAddress->phone }}">
                    <input type="hidden" name="address" id="input-address" value="{{ preg_replace("/\r|\n/", " ", $activeAddress->detail_address) }}, Kec. {{ $activeAddress->district }}, Kota/Kab. {{ $activeAddress->city }}, Provinsi {{ $activeAddress->province }} ({{ $activeAddress->postal_code }})">
                    <input type="hidden" name="province" id="input-province" value="{{ $activeAddress->province }}">
                @else
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center bg-gray-50 transition-all hover:border-[#247a6b]/50">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-gray-100">
                            <i class="fas fa-map-marked-alt text-2xl text-gray-300"></i>
                        </div>
                        <p class="text-gray-500 text-sm mb-5 font-medium">Lu belum masukin alamat pengiriman nih, Bre.</p>
                        <button type="button" onclick="openNewAddressModal()" class="bg-[#247a6b] text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-[#1b5e52] hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i> Tambah Alamat
                        </button>
                    </div>
                @endif
            </div>

            <!-- BAGIAN KANAN: RINGKASAN PESANAN -->
            <div class="bg-white border border-gray-100 shadow-xl shadow-gray-200/40 p-6 rounded-2xl">
                <h3 class="font-bold text-gray-800 mb-5">Ringkasan Pesanan</h3>
                
                <div class="mb-5 space-y-3">
                    @foreach($cartItems as $item)
                        <div class="flex justify-between text-sm py-2">
                            <span class="text-gray-700">
                                {{ $item->product->title }} 
                                @if($item->var_name) <span class="text-gray-400">({{ $item->var_name }})</span> @endif
                                <span class="text-[#247a6b] font-bold ml-1">x{{ $item->qty }}</span>
                            </span>
                            <span class="font-medium text-gray-900">Rp{{ number_format($item->calculated_price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-900">Rp<span id="subtotalDisplay">{{ number_format($cartTotal, 0, ',', '.') }}</span></span>
                    </div>
                    <div class="flex justify-between text-sm items-center">
                        <span class="text-gray-500">Ongkos Kirim <span id="wilayahLabel" class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded ml-1 font-medium">...</span></span>
                        <span id="ongkirContainer" class="font-medium text-gray-900">
                            Rp<span id="ongkirDisplay">0</span>
                        </span>
                    </div>
                    
                    <div id="promoContainer" class="text-xs text-red-500 hidden bg-red-50 p-2 rounded border border-red-100">
                        <i class="fas fa-info-circle mr-1"></i> Kurang <span id="kurangNominal" class="font-bold"></span> lagi buat Gratis Ongkir
                    </div>
                    
                    <div class="pt-4 font-bold flex justify-between text-lg border-t border-gray-100 mt-4">
                        <span class="text-gray-800">Total Bayar</span>
                        <span class="text-[#247a6b]">Rp<span id="totalBayarDisplay">0</span></span>
                    </div>
                </div>
                
                <h3 class="font-bold text-gray-800 mt-8 mb-4">Metode Pembayaran</h3>
                <div class="space-y-3">
                    <label class="flex items-center p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-[#247a6b] hover:bg-[#f0f7f5] transition-all">
                        <input type="radio" name="payment" value="qris" class="mr-3 text-[#247a6b] focus:ring-[#247a6b]" required> 
                        <span class="font-medium text-gray-700 text-sm">QRIS / E-Wallet (Otomatis)</span>
                    </label>
                    <label class="flex items-center p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-[#247a6b] hover:bg-[#f0f7f5] transition-all">
                        <input type="radio" name="payment" value="transfer" class="mr-3 text-[#247a6b] focus:ring-[#247a6b]"> 
                        <span class="font-medium text-gray-700 text-sm">Transfer Bank Manual</span>
                    </label>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="confirmCancel()" class="w-1/3 bg-white border border-gray-200 text-gray-600 font-bold py-3.5 rounded-xl hover:bg-gray-50 transition-all text-sm">
                        Batalkan
                    </button>
                    <button type="submit" class="w-2/3 bg-[#247a6b] text-white font-bold py-3.5 rounded-xl hover:bg-[#1b5e52] hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm {{ !$activeAddress ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$activeAddress ? 'disabled' : '' }}>
                        Buat Pesanan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- MODAL PILIH ALAMAT YANG SUDAH ADA -->
<div id="addressModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[1000] hidden items-center justify-center transition-all">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl flex flex-col max-h-[85vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-lg text-gray-800">Pilih Alamat Pengiriman</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-red-500 w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1 space-y-4 bg-gray-50/50">
            @foreach($addresses as $addr)
                <div class="flex items-start border p-5 bg-white rounded-xl transition-all relative cursor-pointer hover:border-[#247a6b] hover:shadow-md {{ $addr->is_primary ? 'border-[#247a6b] ring-1 ring-[#247a6b]' : 'border-gray-200' }}">
                    <label class="flex-1 flex items-start cursor-pointer">
                        <input type="radio" name="select_address" class="mt-1 mr-4 text-[#247a6b] focus:ring-[#247a6b]" 
                               onclick="selectAddress('{{ $addr->receiver_name }}', '{{ $addr->phone }}', '{{ preg_replace("/\r|\n/", " ", $addr->detail_address) }}<br>{{ $addr->district }}, {{ $addr->city }}, {{ $addr->province }}, {{ $addr->postal_code }}', '{{ $addr->province }}', '{{ $addr->city }}', '{{ $addr->district }}', '{{ $addr->postal_code }}')" 
                               {{ $addr->id == ($activeAddress->id ?? 0) ? 'checked' : '' }}>
                        <div class="pr-10">
                            <p class="font-bold text-gray-900 text-sm">{{ $addr->receiver_name }} <span class="text-gray-500 font-normal ml-2">{{ $addr->phone }}</span></p>
                            <p class="text-gray-600 text-[13px] mt-1.5 leading-relaxed">{{ $addr->detail_address }}<br>{{ $addr->district }}, {{ $addr->city }}, {{ $addr->province }}, {{ $addr->postal_code }}</p>
                            @if($addr->is_primary)
                                <span class="inline-block mt-3 px-2 py-0.5 bg-[#247a6b]/10 text-[#247a6b] text-[10px] font-bold uppercase rounded border border-[#247a6b]/20">Utama</span>
                            @endif
                        </div>
                    </label>
                    <button type="button" 
                            onclick="editAddress('{{ $addr->id }}', '{{ $addr->receiver_name }}', '{{ $addr->phone }}', '{{ $addr->province }}', '{{ $addr->city }}', '{{ $addr->district }}', '{{ $addr->postal_code }}', '{{ preg_replace("/\r|\n/", " ", $addr->detail_address) }}')" 
                            class="text-[#247a6b] text-sm font-medium hover:underline absolute right-5 top-5">
                        Ubah
                    </button>
                </div>
            @endforeach
        </div>

        <div class="p-6 border-t border-gray-100 bg-white">
            <button type="button" onclick="openNewAddressModal()" class="w-full border-2 border-[#247a6b] text-[#247a6b] font-bold py-3 rounded-xl hover:bg-[#f0f7f5] transition-all flex items-center justify-center text-sm">
                <i class="fas fa-plus mr-2"></i> Tambah Alamat Baru
            </button>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / UBAH ALAMAT BARU (Modern & Fresh) -->
<div id="newAddressModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[1050] hidden items-center justify-center transition-all">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white">
            <h3 class="font-bold text-xl text-gray-800 tracking-tight" id="modalFormTitle">Detail Pengiriman</h3>
            <button type="button" onclick="closeNewAddressModal()" class="text-gray-400 hover:text-red-500 transition-colors bg-gray-50 hover:bg-red-50 rounded-full w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="formTambahAlamat" class="flex flex-col flex-1 overflow-hidden bg-gray-50/50">
            <input type="hidden" name="address_id" id="form-address-id">

            <div class="p-8 overflow-y-auto space-y-6 flex-1">
                <!-- Section Kontak -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h4 class="text-[11px] font-bold text-[#247a6b] uppercase tracking-widest mb-4 flex items-center"><i class="fas fa-user-circle mr-2 text-sm"></i> Info Penerima</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="receiver_name" id="form-name" placeholder="Nama Lengkap Penerima" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium" required>
                        <input type="text" name="phone" id="form-phone" placeholder="No. WhatsApp / Telepon" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium" required>
                    </div>
                </div>

                <!-- Section Wilayah (Lengkap 38 Provinsi) -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h4 class="text-[11px] font-bold text-[#247a6b] uppercase tracking-widest mb-4 flex items-center"><i class="fas fa-map-marked-alt mr-2 text-sm"></i> Wilayah Pengiriman</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <select name="province" id="form-province" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-700 font-medium" required>
                            <option value="" disabled selected>Pilih Provinsi...</option>
                            <!-- Pulau Sumatera -->
                            <option value="ACEH">Aceh</option>
                            <option value="SUMATERA UTARA">Sumatera Utara</option>
                            <option value="SUMATERA BARAT">Sumatera Barat</option>
                            <option value="RIAU">Riau</option>
                            <option value="KEPULAUAN RIAU">Kepulauan Riau</option>
                            <option value="JAMBI">Jambi</option>
                            <option value="BENGKULU">Bengkulu</option>
                            <option value="SUMATERA SELATAN">Sumatera Selatan</option>
                            <option value="KEPULAUAN BANGKA BELITUNG">Kepulauan Bangka Belitung</option>
                            <option value="LAMPUNG">Lampung</option>
                            <!-- Pulau Jawa -->
                            <option value="BANTEN">Banten</option>
                            <option value="DKI JAKARTA">DKI Jakarta</option>
                            <option value="JAWA BARAT">Jawa Barat</option>
                            <option value="JAWA TENGAH">Jawa Tengah</option>
                            <option value="DI YOGYAKARTA">DI Yogyakarta</option>
                            <option value="JAWA TIMUR">Jawa Timur</option>
                            <!-- Pulau Bali & Nusa Tenggara -->
                            <option value="BALI">Bali</option>
                            <option value="NUSA TENGGARA BARAT">Nusa Tenggara Barat</option>
                            <option value="NUSA TENGGARA TIMUR">Nusa Tenggara Timur</option>
                            <!-- Pulau Kalimantan -->
                            <option value="KALIMANTAN BARAT">Kalimantan Barat</option>
                            <option value="KALIMANTAN TENGAH">Kalimantan Tengah</option>
                            <option value="KALIMANTAN SELATAN">Kalimantan Selatan</option>
                            <option value="KALIMANTAN TIMUR">Kalimantan Timur</option>
                            <option value="KALIMANTAN UTARA">Kalimantan Utara</option>
                            <!-- Pulau Sulawesi -->
                            <option value="SULAWESI UTARA">Sulawesi Utara</option>
                            <option value="GORONTALO">Gorontalo</option>
                            <option value="SULAWESI TENGAH">Sulawesi Tengah</option>
                            <option value="SULAWESI BARAT">Sulawesi Barat</option>
                            <option value="SULAWESI SELATAN">Sulawesi Selatan</option>
                            <option value="SULAWESI TENGGARA">Sulawesi Tenggara</option>
                            <!-- Pulau Maluku -->
                            <option value="MALUKU">Maluku</option>
                            <option value="MALUKU UTARA">Maluku Utara</option>
                            <!-- Pulau Papua -->
                            <option value="PAPUA">Papua</option>
                            <option value="PAPUA BARAT">Papua Barat</option>
                            <option value="PAPUA SELATAN">Papua Selatan</option>
                            <option value="PAPUA TENGAH">Papua Tengah</option>
                            <option value="PAPUA PEGUNUNGAN">Papua Pegunungan</option>
                            <option value="PAPUA BARAT DAYA">Papua Barat Daya</option>
                        </select>
                        <input type="text" name="city" id="form-city" placeholder="Kota / Kabupaten" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="district" id="form-district" placeholder="Kecamatan" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium" required>
                        <input type="text" name="postal_code" id="form-postal" placeholder="Kode Pos" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium" required>
                    </div>
                </div>

                <!-- Section Alamat Lengkap -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h4 class="text-[11px] font-bold text-[#247a6b] uppercase tracking-widest mb-4 flex items-center"><i class="fas fa-home mr-2 text-sm"></i> Detail Alamat & Patokan</h4>
                    <textarea name="detail_address" id="form-detail" placeholder="Tuliskan nama jalan, perumahan, RT/RW, nomor rumah, dan patokan (Cth: Depan toko ikan, pagar warna hitam...)" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#247a6b]/20 focus:border-[#247a6b] transition-all text-sm text-gray-800 placeholder-gray-400 font-medium resize-none" required></textarea>
                </div>
            </div>

            <div class="p-6 bg-white border-t border-gray-100 flex justify-end">
                <button type="button" onclick="closeNewAddressModal()" class="px-6 py-2.5 text-gray-500 font-bold text-sm hover:text-gray-800 transition-colors mr-3 bg-white border border-gray-200 rounded-lg">Batal</button>
                <button type="button" id="btnSimpanAlamat" onclick="saveNewAddress()" class="px-8 py-2.5 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> Simpan & Gunakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const baseCartTotal = {{ $cartTotal }};
    const listProvinsiJawa = ['DKI JAKARTA', 'JAWA BARAT', 'JAWA TENGAH', 'DI YOGYAKARTA', 'JAWA TIMUR', 'BANTEN'];

    function formatRupiah(angka) { 
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
    }

    function calculateOngkir(provinsi = null) {
        let inputProv = document.getElementById('input-province');
        if(!inputProv) return; 
        
        if(!provinsi) { 
            provinsi = inputProv.value.toUpperCase(); 
        }
        
        let ongkir = 0;
        let container = document.getElementById('ongkirContainer');
        let promo = document.getElementById('promoContainer');
        
        document.getElementById('wilayahLabel').innerText = provinsi;

        if (baseCartTotal >= 500000) {
            ongkir = 0;
            container.innerHTML = '<span class="text-[#247a6b] font-bold">Gratis Ongkir!</span>';
            promo.classList.add('hidden');
        } else {
            if (listProvinsiJawa.includes(provinsi)) { 
                ongkir = 20000; 
            } else { 
                ongkir = 35000; 
            }
            container.innerHTML = 'Rp<span id="ongkirDisplay">' + formatRupiah(ongkir) + '</span>';
            promo.classList.remove('hidden');
            let kurang = 500000 - baseCartTotal;
            document.getElementById('kurangNominal').innerText = 'Rp' + formatRupiah(kurang);
        }

        let totalBayar = baseCartTotal + ongkir;
        document.getElementById('totalBayarDisplay').innerText = formatRupiah(totalBayar);
    }

    window.onload = function() { calculateOngkir(); };

    function confirmCancel() {
        if(confirm('Yakin ingin membatalkan pesanan?')) { 
            window.location.href = "{{ url('/') }}"; 
        }
    }

    function openAddressModal() {
        document.getElementById('addressModal').classList.remove('hidden');
        document.getElementById('addressModal').classList.add('flex');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
        document.getElementById('addressModal').classList.remove('flex');
    }

    function openNewAddressModal() {
        document.getElementById('modalFormTitle').innerText = 'Detail Pengiriman';
        
        document.getElementById('form-address-id').value = ''; 
        document.getElementById('form-name').value = '';
        document.getElementById('form-phone').value = '';
        document.getElementById('form-province').value = '';
        document.getElementById('form-city').value = '';
        document.getElementById('form-district').value = '';
        document.getElementById('form-postal').value = '';
        document.getElementById('form-detail').value = '';

        if(document.getElementById('addressModal')) {
            document.getElementById('addressModal').classList.add('hidden');
        }
        document.getElementById('newAddressModal').classList.remove('hidden');
        document.getElementById('newAddressModal').classList.add('flex');
    }

    function editAddress(id, name, phone, province, city, district, postal, detail) {
        document.getElementById('modalFormTitle').innerText = 'Ubah Detail Pengiriman';
        
        document.getElementById('form-address-id').value = id;
        document.getElementById('form-name').value = name;
        document.getElementById('form-phone').value = phone;
        document.getElementById('form-province').value = province;
        document.getElementById('form-city').value = city;
        document.getElementById('form-district').value = district;
        document.getElementById('form-postal').value = postal;
        document.getElementById('form-detail').value = detail;

        document.getElementById('addressModal').classList.add('hidden');
        document.getElementById('newAddressModal').classList.remove('hidden');
        document.getElementById('newAddressModal').classList.add('flex');
    }

    function closeNewAddressModal() {
        document.getElementById('newAddressModal').classList.add('hidden');
        document.getElementById('newAddressModal').classList.remove('flex');
        
        if({{ $addresses->isNotEmpty() ? 'true' : 'false' }}) {
            openAddressModal(); 
        }
    }

    function saveNewAddress() {
        const form = document.getElementById('formTambahAlamat');
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const btn = document.getElementById('btnSimpanAlamat');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        btn.disabled = true;

        fetch('{{ route('address.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            if(data.success) {
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        })
        .catch(error => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Waduh, ada error Bre! Pastikan datanya lengkap.');
            console.error(error);
        });
    }

    // LOGIKA PERAPIHAN ALAMAT (Sistem AI Formatting)
    function selectAddress(name, phone, addressHTML, province, city, district, postal) {
        // Tampilkan di UI Pembeli
        document.getElementById('display-name').innerText = name;
        document.getElementById('display-phone').innerText = phone;
        document.getElementById('display-address').innerHTML = addressHTML;

        // Ambil baris pertama sebagai patokan detail jalan
        let detailJalan = addressHTML.split('<br>')[0].trim(); 
        
        // Susun kalimat rapi ala AI untuk masuk ke Database (Dashboard Seller)
        let susunanRapi = `${detailJalan}, Kec. ${district}, Kota/Kab. ${city}, Provinsi ${province} (${postal})`;

        // Masukkan ke input tersembunyi
        document.getElementById('input-name').value = name;
        document.getElementById('input-phone').value = phone;
        document.getElementById('input-address').value = susunanRapi;
        document.getElementById('input-province').value = province; 

        // Hitung ulang ongkir berdasarkan provinsi baru
        calculateOngkir(province);
        
        // Tutup otomatis modalnya
        setTimeout(closeAddressModal, 200);
    }
</script>
@endsection