<div id="livechat-container">
    <!-- Nút mở chat -->
    <div id="livechat-toggle">
        <i class="fa fa-comments"></i>
    </div>

    <!-- Hộp chat -->
    <div id="livechat-box" class="hidden">
        <div class="livechat-header">
            <span>Hỗ trợ trực tuyến</span>
            <button id="livechat-close">&times;</button>
        </div>

        <div class="livechat-messages" id="livechat-messages">
            <!-- Tin nhắn mẫu -->
            <div class="message admin">Xin chào, tôi có thể giúp gì được cho bạn!</div>
            <div class="message user">Hi, Shop</div>
        </div>

        <div class="livechat-input">
            <input type="text" id="livechat-input-text" placeholder="Nhập tin nhắn...">
            <button id="livechat-send"><i class="fa fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<style>
    /* Container */
    #livechat-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: Arial, sans-serif;
    }

    /* Nút mở chat */
    #livechat-toggle {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: #0288d1;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        font-size: 22px;
    }

    /* Hộp chat */
    #livechat-box {
        width: 320px;
        height: 420px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .hidden { display: none; }

    /* Header */
    .livechat-header {
        background: #0288d1;
        color: #fff;
        padding: 10px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .livechat-header button {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }
    #livechat-box:not(.hidden) ~ #livechat-toggle {
        display: none;
    }

    /* Messages */
    .livechat-messages {
        flex: 1;
        padding: 10px;
        overflow-y: auto;
        background: #f9f9f9;
    }

    .message {
        max-width: 75%;
        padding: 8px 12px;
        margin-bottom: 8px;
        border-radius: 15px;
        clear: both;
        font-size: 14px;
    }

    .message.admin {
        background: #e1f0ff;
        color: #333;
        float: left;
    }

    .message.user {
        background: #0288d1;
        color: #fff;
        float: right;
    }

    /* Input */
    .livechat-input {
        display: flex;
        border-top: 1px solid #ddd;
    }

    .livechat-input input {
        flex: 1;
        border: none;
        padding: 10px;
        outline: none;
    }

    .livechat-input button {
        background: #0288d1;
        color: #fff;
        border: none;
        padding: 0 16px;
        cursor: pointer;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const toggleBtn = document.getElementById("livechat-toggle");
    const chatBox   = document.getElementById("livechat-box");
    const closeBtn  = document.getElementById("livechat-close");
    const input     = document.getElementById("livechat-input-text");
    const sendBtn   = document.getElementById("livechat-send");
    const messages  = document.getElementById("livechat-messages");

    let conversationId = null;
    let lastId = 0;

    // === 1. Boot khi mở hộp chat ===
    // === 1. Boot khi mở hộp chat ===
    async function bootConversation(){
        const res = await fetch("{{ route('livechat.boot') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        });
        const data = await res.json();
        conversationId = data.conversation_id;
        console.log("Booted conversation:", conversationId);

        // 👉 Load lại toàn bộ history
        await loadHistory();
    }

    // === Load history tin nhắn ===
    async function loadHistory(){
        if(!conversationId) return;
        const res = await fetch("{{ route('livechat.poll') }}?conversation_id=" + conversationId);
        const data = await res.json();
        messages.innerHTML = ""; // xoá placeholder cũ
        data.messages.forEach(m => {
            let msg = document.createElement("div");
            msg.className = "message " + (m.direction === "out" ? "admin" : "user");
            msg.textContent = m.body;
            messages.appendChild(msg);
            lastId = m.id;
        });
        messages.scrollTop = messages.scrollHeight;
    }


    // === 2. Gửi tin nhắn ===
    async function sendMessage(){
        let text = input.value.trim();
        if(text === "" || !conversationId) return;

        // Append UI trước (optimistic update)
        let msg = document.createElement("div");
        msg.className = "message user";
        msg.textContent = text;
        messages.appendChild(msg);
        input.value = "";
        messages.scrollTop = messages.scrollHeight;

        // Gửi về backend
        await fetch("{{ route('livechat.send') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                body: text
            })
        });
    }

    // === 3. Poll tin nhắn từ server ===
    async function pollMessages(){
        if(!conversationId) return;
        const res = await fetch(
            "{{ route('livechat.poll') }}?conversation_id=" + conversationId + "&after_id=" + lastId
        );
        const data = await res.json();
        data.messages.forEach(m => {
            // Chỉ append tin nhắn "out" (CSKH gửi)
            if(m.direction === "out"){
                let msg = document.createElement("div");
                msg.className = "message admin";
                msg.textContent = m.sender_name + ": " + m.body;
                messages.appendChild(msg);
                messages.scrollTop = messages.scrollHeight;
            }
            lastId = m.id;
        });
    }
    setInterval(pollMessages, 2000);

    // === Gắn sự kiện UI ===
    toggleBtn.addEventListener("click", async () => {
        chatBox.classList.toggle("hidden");
        if(!conversationId) await bootConversation();
    });

    closeBtn.addEventListener("click", () => {
        chatBox.classList.add("hidden");
    });

    sendBtn.addEventListener("click", sendMessage);
    input.addEventListener("keypress", function(e){
        if(e.key === "Enter") sendMessage();
    });
});
</script>

