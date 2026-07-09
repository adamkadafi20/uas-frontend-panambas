<div id="draggableBuyerChatWidget" class="fixed right-4 bottom-4 md:right-6 md:bottom-6 flex flex-col space-y-2 z-[9999]" style="cursor: grab;">
    <div id="buyerChatWidgetBtn" class="bg-[#247a6b] text-white p-3 md:p-4 shadow-lg rounded-full hover:bg-[#1b5e52] transition-all transform hover:scale-105 relative flex items-center justify-center w-12 h-12 md:w-14 md:h-14" style="cursor: grab;">
        <i class="fas fa-comments text-xl md:text-2xl pointer-events-none"></i>
        <span id="buyerGlobalUnreadBadge" class="hidden absolute top-0 right-0 bg-red-500 text-white text-[9px] md:text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white pointer-events-none">0</span>
    </div>
</div>

<div id="buyerChatPanel" class="fixed inset-0 md:inset-auto md:right-6 md:bottom-24 w-full md:w-[350px] h-full md:h-[500px] bg-white md:shadow-2xl md:rounded-xl border-0 md:border border-gray-200 z-[10000] hidden flex-col overflow-hidden transition-all duration-300">
    
    <div class="p-3 bg-[#247a6b] flex items-center justify-between shadow-sm z-10 text-white">
        <div class="flex items-center">
            <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-[#247a6b] font-bold text-lg shadow-sm">P</div>
            <div class="ml-3 leading-tight">
                <h3 class="text-sm font-bold">Panambas Official</h3>
                <p class="text-[10px] text-green-100"><i class="fas fa-circle text-[8px] mr-1 text-green-300"></i>Online</p>
            </div>
        </div>
        <button onclick="toggleBuyerChat()" class="hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center transition"><i class="fas fa-times text-lg"></i></button>
    </div>
    
    <div id="buyerChatMessages" class="flex-1 p-4 overflow-y-auto space-y-4" style="background-color: #fdfdfd; background-image: url('https://w0.peakpx.com/wallpaper/580/630/壓制-whatsapp-wallpaper-doodle-patterns-light-background.jpg'); background-size: 300px; background-blend-mode: soft-light;">
        <div class="flex justify-start">
            <div class="bg-white px-3 py-2.5 rounded-xl rounded-tl-none shadow-sm max-w-[85%] border border-gray-100">
                <p class="text-[13px] text-gray-800 leading-relaxed">Halo Kak {{ Auth::user()->name ?? 'Pelanggan' }}! Ada yang bisa kami bantu?</p>
                <span class="block text-[9px] text-gray-400 text-right mt-1.5">Otomatis</span>
            </div>
        </div>
    </div>

    <div id="buyerSelectionPanel" class="hidden flex-1 flex-col bg-white overflow-hidden border-t">
        <div class="p-2 bg-gray-50 flex items-center border-b">
            <button onclick="closeBuyerSelection()" class="mr-3 text-gray-500 hover:text-red-500 transition"><i class="fas fa-arrow-left text-lg"></i></button>
            <input type="text" id="buyerSearchInput" class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-[13px] outline-none focus:border-[#247a6b]" placeholder="Cari...">
        </div>
        <div id="buyerSelectionList" class="flex-1 overflow-y-auto p-2 space-y-2 bg-gray-50"></div>
    </div>
    
    <div class="bg-white border-t border-gray-200 flex flex-col pb-safe">
        <input type="file" id="buyerFileInput" class="hidden">

        <div class="p-2.5 flex items-center bg-gray-50/50">
            <input type="text" id="buyerChatInput" class="flex-1 bg-white border border-gray-300 rounded-full py-2.5 px-4 text-[13px] text-gray-700 outline-none focus:border-[#247a6b] focus:ring-1 focus:ring-[#247a6b] shadow-sm" placeholder="Ketik pesan...">
            <button onclick="sendBuyerChat()" class="ml-2 w-10 h-10 bg-[#247a6b] text-white rounded-full flex items-center justify-center hover:bg-[#1b5e52] transition shadow-md"><i class="fas fa-paper-plane text-sm md:text-base pr-0.5"></i></button>
        </div>

        <div class="flex items-center justify-around px-2 pb-3 pt-1 text-gray-500 bg-white">
            <button onclick="openFileUploader('image')" class="hover:text-[#247a6b] transition p-2 rounded-full hover:bg-gray-50" title="Kirim Foto"><i class="fas fa-image text-[20px]"></i></button>
            <button onclick="openFileUploader('video')" class="hover:text-[#247a6b] transition p-2 rounded-full hover:bg-gray-50" title="Kirim Video"><i class="fas fa-video text-[20px]"></i></button>
            <button onclick="openBuyerSelection('product')" class="hover:text-[#247a6b] transition p-2 rounded-full hover:bg-gray-50" title="Bagikan Produk"><i class="fas fa-box-open text-[20px]"></i></button>
            <button onclick="openBuyerSelection('order')" class="hover:text-[#247a6b] transition p-2 rounded-full hover:bg-gray-50" title="Kirim Pesanan Saya"><i class="fas fa-receipt text-[20px]"></i></button>
        </div>
    </div>
</div>

<script>
    const adminId = 1; 
    let buyerChatInterval = null;
    let currentUploadType = 'image'; 
    let buyerSelectionMode = '';

    function toggleBuyerChat() {
        const panel = document.getElementById('buyerChatPanel');
        const body = document.body;
        
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            panel.classList.add('flex');
            if(window.innerWidth < 768) body.style.overflow = 'hidden';
            
            fetchBuyerMessages();
            buyerChatInterval = setInterval(fetchBuyerMessages, 3000);
            
            document.getElementById('buyerGlobalUnreadBadge').classList.add('hidden');
        } else {
            panel.classList.add('hidden');
            panel.classList.remove('flex');
            body.style.overflow = ''; 
            clearInterval(buyerChatInterval);
        }
    }

    function loadGlobalUnreadBuyer() {
        fetch('/chat/unread-count', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            let count = data.unread_count;
            let badge = document.getElementById('buyerGlobalUnreadBadge');
            
            const panel = document.getElementById('buyerChatPanel');
            if (count > 0 && panel.classList.contains('hidden')) {
                badge.innerText = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
    }
    
    setInterval(loadGlobalUnreadBuyer, 5000);
    loadGlobalUnreadBuyer(); 

    // PERBAIKAN RENDER FOTO: Pakai {{ asset('storage') }} biar path-nya absolut dan nggak pecah!
    function renderChatBubble(msg, isMine, time) {
        let content = '';
        if (msg.type === 'image' && msg.file_path) {
            content = `<img src="{{ asset('storage') }}/${msg.file_path}" class="rounded-lg w-full max-w-[200px] cursor-pointer border border-gray-200 mb-1" onclick="window.open(this.src, '_blank')">`;
        } else if (msg.type === 'video' && msg.file_path) {
            content = `<video src="{{ asset('storage') }}/${msg.file_path}" controls class="rounded-lg w-full max-w-[200px] border border-gray-200 mb-1"></video>`;
        } else if (msg.type === 'product') {
            let p = JSON.parse(msg.message);
            let priceVal = String(p.total);
            let displayPrice = priceVal.includes('-') 
                ? priceVal.split('-').map(num => new Intl.NumberFormat('id-ID').format(num)).join(' - Rp ') 
                : new Intl.NumberFormat('id-ID').format(priceVal);

            content = `
            <div class="bg-white border rounded p-2 w-48 cursor-pointer hover:shadow-md transition" onclick="window.open('/product/${p.id}', '_blank')">
                <div class="bg-gray-100 h-16 flex items-center justify-center rounded mb-1"><i class="fas fa-box-open text-gray-400 text-xl"></i></div>
                <p class="text-[13px] font-bold text-gray-800 truncate">${p.title}</p>
                <p class="text-[11px] text-[#247a6b]">Rp ${displayPrice}</p>
            </div>`;
        } else if (msg.type === 'order') {
            let o = JSON.parse(msg.message);
            content = `
            <div class="bg-white border rounded p-2 w-48 cursor-pointer hover:shadow-md transition" onclick="alert('Halaman detail pesanan pembeli belum dibikin Bre!')">
                <p class="text-[13px] font-bold text-gray-800"><i class="fas fa-receipt text-[#247a6b]"></i> ${o.title}</p>
                <p class="text-[11px] text-gray-500 mb-1">Total: Rp ${new Intl.NumberFormat('id-ID').format(o.total)}</p>
                <button class="w-full border border-[#247a6b] text-[#247a6b] text-[10px] py-1 rounded">Cek Detail Pesanan</button>
            </div>`;
        } else {
            content = `<p class="text-[13px] text-gray-800 leading-relaxed break-words">${msg.message}</p>`;
        }

        if (isMine) {
            return `
            <div class="flex justify-end mt-2">
                <div class="px-3 py-2.5 rounded-xl rounded-tr-none shadow-sm max-w-[85%] border" style="background-color: #e6f2f0; border-color: #247a6b;">
                    ${content}
                    <div class="flex justify-end items-center mt-1.5 space-x-1">
                        <span class="text-[9px] text-gray-500">${time}</span>
                        <i class="fas fa-check-double text-[10px]" style="color: #247a6b;"></i>
                    </div>
                </div>
            </div>`;
        } else {
            return `
            <div class="flex justify-start mt-2">
                <div class="bg-white px-3 py-2.5 rounded-xl rounded-tl-none shadow-sm max-w-[85%] border border-gray-100">
                    ${content}
                    <span class="block text-[9px] text-gray-400 text-right mt-1.5">${time}</span>
                </div>
            </div>`;
        }
    }

    function fetchBuyerMessages() {
        fetch('/chat/fetch/' + adminId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            let html = '';
            const myId = data.my_id;
            
            data.messages.forEach(function(msg) {
                let time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                html += renderChatBubble(msg, msg.sender_id == myId, time);
            });

            if(html !== '') {
                const chatBox = document.getElementById('buyerChatMessages');
                let isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 30;
                
                chatBox.innerHTML = html;
                if(isScrolledToBottom) chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
    }

    function sendBuyerChat() {
        const input = document.getElementById('buyerChatInput');
        const message = input.value.trim();
        if (message === '') return;
        
        input.value = ''; 
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ receiver_id: adminId, message: message, type: 'text' })
        }).then(() => {
            fetchBuyerMessages();
            setTimeout(() => { document.getElementById('buyerChatMessages').scrollTop = document.getElementById('buyerChatMessages').scrollHeight; }, 100);
        });
    }

    document.getElementById('buyerChatInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') { sendBuyerChat(); e.preventDefault(); }
    });

    function openFileUploader(type) {
        currentUploadType = type;
        const fileInput = document.getElementById('buyerFileInput');
        if (type === 'image') fileInput.accept = 'image/png, image/jpeg, image/jpg';
        else fileInput.accept = 'video/mp4, video/quicktime';
        fileInput.click();
    }

    document.getElementById('buyerFileInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const chatBox = document.getElementById('buyerChatMessages');
        chatBox.innerHTML += `
        <div class="flex justify-end mt-2" id="tempLoading">
            <div class="px-3 py-2 rounded-xl rounded-tr-none shadow-sm bg-gray-100 text-gray-500 text-xs border border-gray-200">
                <i class="fas fa-spinner fa-spin mr-2"></i> Mengirim ${currentUploadType}...
            </div>
        </div>`;
        chatBox.scrollTop = chatBox.scrollHeight;

        let formData = new FormData();
        formData.append('receiver_id', adminId);
        formData.append('file', file);
        formData.append('type', currentUploadType);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('/chat/send', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('buyerFileInput').value = ''; 
            fetchBuyerMessages(); 
        })
        .catch(error => {
            alert('Gagal upload! Pastikan ukurannya di bawah 10MB.');
            document.getElementById('tempLoading').remove(); 
        });
    });

    function openBuyerSelection(mode) {
        buyerSelectionMode = mode;
        document.getElementById('buyerChatMessages').classList.add('hidden');
        
        const panel = document.getElementById('buyerSelectionPanel');
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        
        const input = document.getElementById('buyerSearchInput');
        input.placeholder = mode === 'product' ? 'Cari nama produk...' : 'Cari No Pesanan (Inv)...';
        input.value = '';
        fetchBuyerSelection('');
    }

    function closeBuyerSelection() {
        const panel = document.getElementById('buyerSelectionPanel');
        panel.classList.remove('flex');
        panel.classList.add('hidden');
        document.getElementById('buyerChatMessages').classList.remove('hidden');
    }

    document.getElementById('buyerSearchInput').addEventListener('keyup', function(e) {
        fetchBuyerSelection(e.target.value);
    });

    function fetchBuyerSelection(search) {
        let url = buyerSelectionMode === 'product' ? `/chat/products?search=${search}` : `/chat/orders?search=${search}`;
        
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.forEach(item => {
                if (buyerSelectionMode === 'product') {
                    let priceVal = String(item.price_label);
                    let displayPrice = priceVal.includes('-') 
                        ? priceVal.split('-').map(num => new Intl.NumberFormat('id-ID').format(num)).join(' - Rp ') 
                        : new Intl.NumberFormat('id-ID').format(priceVal);

                    html += `
                    <div class="border rounded p-2 mb-2 flex justify-between items-center bg-white shadow-sm">
                        <div>
                            <p class="text-[13px] font-bold text-gray-800">${item.title}</p>
                            <p class="text-[11px] text-[#247a6b]">Rp ${displayPrice}</p>
                        </div>
                        <button onclick="sendBuyerItem('product', ${item.id}, '${item.title.replace(/'/g, "\\'")}', '${item.price_label}')" class="bg-[#247a6b] text-white text-[11px] px-3 py-1.5 rounded hover:bg-[#1b5e52] transition">Kirim</button>
                    </div>`;
                } else {
                    let invNumber = item.invoice_number || ('INV-' + item.id);
                    html += `
                    <div class="border rounded p-2 mb-2 flex justify-between items-center bg-white shadow-sm">
                        <div>
                            <p class="text-[13px] font-bold text-gray-800"><i class="fas fa-receipt text-gray-400"></i> ${invNumber}</p>
                            <p class="text-[11px] text-gray-500">Rp ${new Intl.NumberFormat('id-ID').format(item.grand_total)} - ${item.status}</p>
                        </div>
                        <button onclick="sendBuyerItem('order', ${item.id}, '${invNumber}', '${item.grand_total}')" class="bg-[#247a6b] text-white text-[11px] px-3 py-1.5 rounded hover:bg-[#1b5e52] transition">Kirim</button>
                    </div>`;
                }
            });
            document.getElementById('buyerSelectionList').innerHTML = html;
        })
        .catch(error => console.error('Error fetching selection:', error));
    }

    function sendBuyerItem(type, id, title, amount) {
        const payload = JSON.stringify({ id: id, title: title, total: amount });
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ receiver_id: adminId, type: type, reference_id: id, message: payload })
        }).then(() => {
            closeBuyerSelection();
            fetchBuyerMessages();
        });
    }

    // ==========================================
    // LOGIKA DRAG & DROP WIDGET (SUPPORT PC & MOBILE/HP)
    // ==========================================
    const widgetWrapFront = document.getElementById('draggableBuyerChatWidget');
    const widgetBtnFront = document.getElementById('buyerChatWidgetBtn');

    if (widgetWrapFront && widgetBtnFront) {
        let isDraggingFront = false;
        let startXFront, startYFront;

        // --- SENSOR MOUSE (UNTUK LAPTOP/PC) ---
        widgetWrapFront.onmousedown = function(e) {
            e.preventDefault();
            isDraggingFront = false;
            startXFront = e.clientX;
            startYFront = e.clientY;
            document.onmouseup = closeDragElementFront;
            document.onmousemove = elementDragFront;
        };

        function elementDragFront(e) {
            e.preventDefault();
            if (Math.abs(e.clientX - startXFront) > 3 || Math.abs(e.clientY - startYFront) > 3) isDraggingFront = true;
            let pos1 = startXFront - e.clientX;
            let pos2 = startYFront - e.clientY;
            startXFront = e.clientX;
            startYFront = e.clientY;

            widgetWrapFront.style.top = (widgetWrapFront.offsetTop - pos2) + "px";
            widgetWrapFront.style.left = (widgetWrapFront.offsetLeft - pos1) + "px";
            widgetWrapFront.style.bottom = "auto";
            widgetWrapFront.style.right = "auto";
        }

        function closeDragElementFront() {
            document.onmouseup = null;
            document.onmousemove = null;
        }

        // --- SENSOR SENTUHAN JARI (UNTUK MOBILE/HP) ---
        widgetWrapFront.addEventListener('touchstart', function(e) {
            isDraggingFront = false;
            startXFront = e.touches[0].clientX;
            startYFront = e.touches[0].clientY;
        }, {passive: false});

        widgetWrapFront.addEventListener('touchmove', function(e) {
            e.preventDefault(); // Mencegah layar ikutan ke-scroll pas tombol ditarik
            if (Math.abs(e.touches[0].clientX - startXFront) > 3 || Math.abs(e.touches[0].clientY - startYFront) > 3) isDraggingFront = true;
            
            let pos1 = startXFront - e.touches[0].clientX;
            let pos2 = startYFront - e.touches[0].clientY;
            startXFront = e.touches[0].clientX;
            startYFront = e.touches[0].clientY;

            widgetWrapFront.style.top = (widgetWrapFront.offsetTop - pos2) + "px";
            widgetWrapFront.style.left = (widgetWrapFront.offsetLeft - pos1) + "px";
            widgetWrapFront.style.bottom = "auto";
            widgetWrapFront.style.right = "auto";
        }, {passive: false});

        // --- PENENTU: KLIK BUKA CHAT / CUMA DIGESER ---
        widgetBtnFront.onclick = function(e) {
            if (isDraggingFront) {
                e.preventDefault();
                return; 
            }
            toggleBuyerChat(); 
        };
    }
</script>