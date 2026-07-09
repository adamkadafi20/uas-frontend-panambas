<div id="shopeeChatPanel" class="fixed right-16 bottom-0 w-[750px] h-[550px] bg-white shadow-2xl rounded-t-lg border border-gray-300 z-[10000] hidden flex-row overflow-hidden transition-all duration-300">
    <div class="w-[250px] flex flex-col border-r border-gray-200 bg-white">
        <div class="p-3 text-white flex justify-between items-center" style="background-color: #247a6b;">
            <span class="font-semibold text-sm"><i class="fas fa-comments mr-2"></i> Chat Pembeli</span>
        </div>
        <div class="overflow-y-auto flex-1 custom-scrollbar" id="chatContactList"></div>
    </div>

    <div class="flex-1 flex flex-col bg-[#f8f9fa]">
        <div class="p-3 bg-white flex items-center justify-between border-b border-gray-200 shadow-sm">
            <div class="flex items-center">
                <img id="activeChatAvatar" src="https://ui-avatars.com/api/?name=Pilih+Kontak&background=e2e8f0&color=64748b" class="w-8 h-8 rounded-full">
                <div class="ml-3"><h3 id="activeChatName" class="text-sm font-semibold text-gray-800">Pilih obrolan</h3></div>
            </div>
            <button onclick="toggleShopeeChat()" class="text-gray-500"><i class="fas fa-times"></i></button>
        </div>

        <div id="chatMessagesBox" class="flex-1 p-4 overflow-y-auto space-y-4" style="background-image: url('https://w0.peakpx.com/wallpaper/580/630/壓制-whatsapp-wallpaper-doodle-patterns-light-background.jpg');">
        </div>

        <div id="chatSelectionPanel" class="hidden flex-1 flex-col bg-white overflow-hidden border-t">
            <div class="p-2 bg-gray-50 flex items-center border-b">
                <button onclick="closeSelectionPanel()" class="mr-3 text-gray-500 hover:text-red-500"><i class="fas fa-arrow-left"></i></button>
                <input type="text" id="chatSearchInput" class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-xs outline-none focus:border-[#247a6b]" placeholder="Cari...">
            </div>
            <div id="chatSelectionList" class="flex-1 overflow-y-auto p-2 space-y-2"></div>
        </div>

        <div class="bg-white border-t border-gray-200">
            <div class="p-2 flex items-center">
                <input type="file" id="adminFileInput" class="hidden">
                <input type="text" id="adminChatInput" class="flex-1 border border-gray-300 rounded-full py-2 px-4 text-xs" placeholder="Ketik pesan...">
                <button onclick="sendAdminChat()" class="ml-2 w-8 h-8 bg-[#247a6b] text-white rounded-full"><i class="fas fa-paper-plane text-xs"></i></button>
            </div>
            <div class="flex items-center space-x-6 px-4 pb-2 pt-1 text-gray-500 border-t border-gray-100">
                <button onclick="openAdminFileUploader('image')" class="hover:text-[#247a6b]"><i class="fas fa-image"></i></button>
                <button onclick="openAdminFileUploader('video')" class="hover:text-[#247a6b]"><i class="fas fa-video"></i></button>
                <button onclick="openSelectionPanel('product')" class="hover:text-[#247a6b]"><i class="fas fa-box-open"></i></button>
                <button onclick="openSelectionPanel('order')" class="hover:text-[#247a6b]"><i class="fas fa-receipt"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    let activeUserId = null;
    let selectionMode = '';

    // ==========================================
    // 1. LOGIKA NOTIF & DAFTAR KONTAK
    // ==========================================
    function loadGlobalUnreadAdmin() {
        $.get('/chat/unread-count', function(data) {
            let count = data.unread_count;
            let badge = $('#adminGlobalUnreadBadge');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).removeClass('hidden');
            } else {
                badge.addClass('hidden');
            }
        });
    }
    setInterval(loadGlobalUnreadAdmin, 5000);
    loadGlobalUnreadAdmin();

    function loadContactList() {
        $.get('/chat/list', function(users) {
            let html = '';
            users.forEach(user => {
                let badgeHtml = user.unread_count > 0 
                    ? `<span class="bg-[#247a6b] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">${user.unread_count > 99 ? '99+' : user.unread_count}</span>` 
                    : '';
                let nameClass = user.unread_count > 0 ? 'font-bold text-gray-900' : 'font-semibold text-gray-700';

                html += `
                <div onclick="openChat(${user.id}, '${user.name}')" class="contact-item flex items-center p-3 cursor-pointer border-b hover:bg-gray-50 transition relative">
                    <img src="https://ui-avatars.com/api/?name=${user.name}&background=247a6b&color=fff" class="w-10 h-10 rounded-full shadow-sm">
                    <div class="ml-3 flex-1 overflow-hidden">
                        <div class="flex justify-between items-center mb-0.5">
                            <h3 class="text-xs ${nameClass} truncate pr-2">${user.name}</h3>
                            <span class="text-[9px] text-gray-400 whitespace-nowrap">${user.latest_time}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-[11px] text-gray-500 truncate w-32">${user.latest_message}</p>
                            ${badgeHtml}
                        </div>
                    </div>
                </div>`;
            });
            $('#chatContactList').html(html);
        });
    }

    function toggleShopeeChat() {
        const panel = document.getElementById('shopeeChatPanel');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            panel.classList.add('flex');
            loadContactList(); 
        } else {
            panel.classList.add('hidden');
            panel.classList.remove('flex');
            if (window.chatInterval) clearInterval(window.chatInterval);
        }
    }

    // ==========================================
    // 2. LOGIKA RENDER BUBBLE CHAT KOMPLIT
    // ==========================================
    function renderChatBubble(msg, isMine, time) {
        let content = '';
        if (msg.type === 'image') {
            content = `<img src="/storage/${msg.file_path}" class="w-32 rounded cursor-pointer" onclick="window.open(this.src)">`;
        } else if (msg.type === 'video') {
            content = `<video src="/storage/${msg.file_path}" controls class="w-32 rounded"></video>`;
        } else if (msg.type === 'product') {
            let p = JSON.parse(msg.message);
            // Cek kalau harganya rentang
            let priceVal = String(p.total);
            let displayPrice = priceVal.includes('-') 
                ? priceVal.split('-').map(num => new Intl.NumberFormat('id-ID').format(num)).join(' - Rp ') 
                : new Intl.NumberFormat('id-ID').format(priceVal);

            content = `
            <div class="bg-white border rounded p-2 w-48 cursor-pointer hover:shadow-md transition" onclick="window.open('/product/${p.id}', '_blank')">
                <div class="bg-gray-100 h-16 flex items-center justify-center rounded mb-1"><i class="fas fa-box-open text-gray-400 text-xl"></i></div>
                <p class="text-xs font-bold truncate">${p.title}</p>
                <p class="text-[10px] text-[#247a6b]">Rp ${displayPrice}</p>
            </div>`;
        } else if (msg.type === 'order') {
            let o = JSON.parse(msg.message);
            content = `
            <div class="bg-white border rounded p-2 w-48 cursor-pointer hover:shadow-md transition" onclick="window.open('/admin/orders/${o.id}', '_blank')">
                <p class="text-xs font-bold"><i class="fas fa-receipt text-[#247a6b]"></i> ${o.title}</p>
                <p class="text-[10px] text-gray-500 mb-1">Total: Rp ${new Intl.NumberFormat('id-ID').format(o.total)}</p>
                <button class="w-full border border-[#247a6b] text-[#247a6b] text-[9px] py-1 rounded">Cek Detail Pesanan</button>
            </div>`;
        } else {
            content = `<p class="text-[13px]">${msg.message}</p>`;
        }
        
        return `<div class="flex ${isMine ? 'justify-end' : 'justify-start'} mt-2">
                    <div class="px-3 py-2 rounded-lg ${isMine ? 'bg-[#e6f2f0]' : 'bg-white'} border shadow-sm max-w-[70%]">
                        ${content}
                        <small class="text-[9px] block text-right text-gray-400 mt-1">${time}</small>
                    </div>
                </div>`;
    }

    // ==========================================
    // 3. LOGIKA KIRIM & TARIK PESAN
    // ==========================================
    function openChat(userId, userName) {
        activeUserId = userId;
        document.getElementById('activeChatName').innerText = userName;
        document.getElementById('activeChatAvatar').src = `https://ui-avatars.com/api/?name=${userName}&background=247a6b&color=fff`;
        
        if (window.chatInterval) clearInterval(window.chatInterval);
        fetchAdminMessages();
        window.chatInterval = setInterval(fetchAdminMessages, 3000);
    }

    function fetchAdminMessages() {
        if (!activeUserId) return;
        $.get('/chat/fetch/' + activeUserId, function(data) {
            let html = '';
            data.messages.forEach(msg => {
                let time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                html += renderChatBubble(msg, msg.sender_id == data.my_id, time);
            });

            const chatBox = $('#chatMessagesBox');
            
            // Cek apakah posisi scroll user lagi di bawah (batas toleransi 50px)
            let isNearBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 50;
            
            // Isi konten pesan
            chatBox.html(html);
            
            // Cuma scroll ke bawah otomatis kalau user emang lagi di posisi bawah
            if (isNearBottom) {
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }
            
            loadContactList(); 
            loadGlobalUnreadAdmin(); 
        });
    }

    function sendAdminChat() {
        let msg = $('#adminChatInput').val();
        if (!msg || !activeUserId) return;
        $.post('/chat/send', { receiver_id: activeUserId, message: msg, type: 'text' }, () => {
            $('#adminChatInput').val('');
            fetchAdminMessages();
        });
    }

    function openAdminFileUploader(type) {
        if(!activeUserId) { alert('Pilih pembeli dulu Bre!'); return; }
        let input = document.getElementById('adminFileInput');
        input.onchange = (e) => {
            let formData = new FormData();
            formData.append('receiver_id', activeUserId);
            formData.append('file', e.target.files[0]);
            formData.append('type', type);
            $.ajax({
                url: '/chat/send', type: 'POST', data: formData,
                processData: false, contentType: false,
                success: () => fetchAdminMessages()
            });
        };
        input.click();
    }

    // ==========================================
    // 4. LOGIKA PANEL CARI PRODUK & PESANAN
    // ==========================================
    function openSelectionPanel(mode) {
        if (!activeUserId) { alert('Pilih obrolan dulu Bre!'); return; }
        selectionMode = mode;
        $('#chatMessagesBox').addClass('hidden');
        $('#chatSelectionPanel').removeClass('hidden').addClass('flex');
        $('#chatSearchInput').attr('placeholder', mode === 'product' ? 'Ketik nama produk...' : 'Ketik No Pesanan (Inv)...').val('');
        fetchSelectionData('');
    }

    function closeSelectionPanel() {
        $('#chatSelectionPanel').removeClass('flex').addClass('hidden');
        $('#chatMessagesBox').removeClass('hidden');
    }

    $('#chatSearchInput').on('keyup', function() {
        fetchSelectionData($(this).val());
    });

    function fetchSelectionData(search) {
        let url = selectionMode === 'product' ? '/chat/products' : '/chat/orders';
        $.get(url, { search: search }, function(data) {
            let html = '';
            data.forEach(item => {
                if (selectionMode === 'product') {
                    // Format tampilan harga kalau ada rentangnya (Pake String)
                    let priceVal = String(item.price_label);
                    let displayPrice = priceVal.includes('-') 
                        ? priceVal.split('-').map(num => new Intl.NumberFormat('id-ID').format(num)).join(' - Rp ') 
                        : new Intl.NumberFormat('id-ID').format(priceVal);

                    html += `
                    <div class="border rounded p-2 mb-2 flex justify-between items-center bg-white hover:bg-gray-50">
                        <div>
                            <p class="text-xs font-bold">${item.title}</p>
                            <p class="text-[10px] text-green-600">Rp ${displayPrice}</p>
                        </div>
                        <button onclick="sendItem('product', ${item.id}, '${item.title.replace(/'/g, "\\'")}', '${item.price_label}')" class="bg-[#247a6b] text-white text-[10px] px-2 py-1 rounded">Kirim</button>
                    </div>`;
                } else {
                    let invNumber = item.invoice_number || ('INV-' + item.id);
                    html += `
                    <div class="border rounded p-2 mb-2 flex justify-between items-center bg-white hover:bg-gray-50">
                        <div>
                            <p class="text-xs font-bold"><i class="fas fa-receipt"></i> ${invNumber}</p>
                            <p class="text-[10px] text-gray-500">Rp ${item.grand_total} - ${item.status}</p>
                        </div>
                        <button onclick="sendItem('order', ${item.id}, '${invNumber}', '${item.grand_total}')" class="bg-[#247a6b] text-white text-[10px] px-2 py-1 rounded">Kirim</button>
                    </div>`;
                }
            });
            $('#chatSelectionList').html(html);
        });
    }

    function sendItem(type, id, title, amount) {
        let payload = JSON.stringify({ id: id, title: title, total: amount });
        $.post('/chat/send', { receiver_id: activeUserId, type: type, reference_id: id, message: payload }, function() {
            closeSelectionPanel();
            fetchAdminMessages();
        });
    }

    function toggleAdminChat(userId, userName) {
        const panel = document.getElementById('shopeeChatPanel');
        
        // 1. Pastikan panel chat-nya ngebuka dulu (kalau lagi ketutup)
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            panel.classList.add('flex');
            loadContactList(); 
        }
        
        // 2. Langsung eksekusi buka ruang obrolan sama user tersebut
        openChat(userId, userName);
    }
</script>