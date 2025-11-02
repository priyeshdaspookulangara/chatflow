(function() {
    document.addEventListener("DOMContentLoaded", function() {
        if (!window.ChatFlowConfig || !window.ChatFlowConfig.embedKey) {
            console.error("ChatFlow configuration not found. Please ensure window.ChatFlowConfig.embedKey is set.");
            return;
        }

        const embedKey = window.ChatFlowConfig.embedKey;
        const hostname = window.location.hostname;
        let isChatOpen = false;

        // --- Create Chat Bubble ---
        const chatBubble = document.createElement('div');
        chatBubble.id = 'chatflow-bubble';
        chatBubble.textContent = '💬'; // Simple emoji icon
        document.body.appendChild(chatBubble);

        // --- Create Chat Container ---
        const chatContainer = document.createElement('div');
        chatContainer.id = 'chatflow-widget-container';
        chatContainer.innerHTML = `
            <div id="chatflow-header">
                <span class="chatflow-header-back">&lt;</span>
                <div class="chatflow-header-avatars">
                    <span></span><span></span><span></span>
                </div>
                <span class="chatflow-header-title">Hi there 👋</span>
                <span class="chatflow-header-close">&hellip;</span>
            </div>
            <div id="chatflow-body">
                <div id="chatflow-pre-chat">
                    <div class="chatflow-avatar"></div>
                    <p class="chatflow-intro-text">Please introduce yourself:</p>
                    <form id="chatflow-intro-form">
                        <input type="email" id="chatflow-email-input" placeholder="Enter your email..." required>
                        <div class="chatflow-newsletter">
                            <input type="checkbox" id="chatflow-newsletter-checkbox">
                            <label for="chatflow-newsletter-checkbox">Sign up for our newsletter</label>
                        </div>
                        <button type="submit" id="chatflow-send-intro">Send</button>
                    </form>
                </div>
                <div id="chatflow-chat-view" style="display: none;">
                    <div id="chatflow-messages"></div>
                    <div id="chatflow-footer">
                        <input type="text" id="chatflow-input" placeholder="Type a message...">
                        <button id="chatflow-send">Send</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(chatContainer);

        // --- Add CSS Styling ---
        const style = document.createElement('style');
        style.innerHTML = `
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
            #chatflow-bubble {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background-color: #007bff;
                color: white;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 30px;
                cursor: pointer;
                z-index: 9998;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            #chatflow-widget-container {
                position: fixed;
                bottom: 90px; /* Position above the bubble */
                right: 20px;
                width: 350px;
                border: none;
                border-radius: 16px;
                display: none;
                flex-direction: column;
                background-color: #f8f9fa; /* Light grey background */
                z-index: 9999;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                overflow: hidden;
            }
            #chatflow-header {
                display: flex;
                align-items: center;
                padding: 12px 15px;
                background-color: #fff;
                border-bottom: 1px solid #e9ecef;
            }
            .chatflow-header-back, .chatflow-header-close { font-size: 24px; color: #6c757d; cursor: pointer; }
            .chatflow-header-back { visibility: hidden; } /* Hidden by default */
            .chatflow-header-title { flex-grow: 1; text-align: center; font-weight: 500; color: #212529; }
            .chatflow-header-avatars { display: flex; margin: 0 10px; }
            .chatflow-header-avatars span { width: 24px; height: 24px; border-radius: 50%; background-color: #e9ecef; border: 2px solid #fff; margin-left: -8px; }
            #chatflow-body { padding: 20px; background-color: #fff; border-radius: 0 0 16px 16px; }
            #chatflow-pre-chat { text-align: center; }
            .chatflow-avatar { width: 60px; height: 60px; border-radius: 50%; background-color: #e9ecef; margin: 0 auto 15px; }
            .chatflow-intro-text { font-weight: bold; font-size: 1.1em; margin-bottom: 20px; color: #343a40; }
            #chatflow-intro-form input[type="email"] {
                width: 100%;
                padding: 12px;
                border: 1px solid #ced4da;
                border-radius: 8px;
                margin-bottom: 15px;
                box-sizing: border-box;
            }
            .chatflow-newsletter { display: flex; align-items: center; margin-bottom: 20px; font-size: 0.9em; color: #495057; }
            .chatflow-newsletter input { margin-right: 8px; }
            #chatflow-intro-form button {
                width: 100%;
                padding: 12px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1em;
                cursor: pointer;
            }
        `;
        document.head.appendChild(style);

        // --- Event Listeners ---
        chatBubble.addEventListener('click', toggleChat);
        document.querySelector('.chatflow-header-close').addEventListener('click', toggleChat);

        document.getElementById('chatflow-intro-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('chatflow-email-input');
            const userEmail = emailInput.value.trim();
            if (userEmail) {
                // Transition to chat view
                document.getElementById('chatflow-pre-chat').style.display = 'none';
                document.getElementById('chatflow-chat-view').style.display = 'flex';
                document.querySelector('.chatflow-header-back').style.visibility = 'visible';

                // Initialize the chat session
                if (!window.ChatFlowConfig.conversationId) {
                    initChat(userEmail);
                }
            }
        });

        function toggleChat() {
            isChatOpen = !isChatOpen;
            chatContainer.style.display = isChatOpen ? 'flex' : 'none';
            chatBubble.style.display = isChatOpen ? 'none' : 'flex';
        }

        // --- API Functions ---
        function initChat(customerEmail) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/init.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.ChatFlowConfig.conversationId = response.conversation_id;
                        pollMessages(); // Start polling for messages
                    } else {
                        console.error('ChatFlow Error:', response.error);
                    }
                } else {
                    console.error('Failed to initialize chat session.');
                }
            };
            xhr.onerror = function() { console.error('Network error during chat initialization.'); };
            xhr.send(JSON.stringify({
                embed_key: embedKey,
                hostname: hostname,
                customer_name: 'Customer', // Or derive from email
                customer_email: customerEmail
            }));
        }

        document.getElementById('chatflow-send').addEventListener('click', function() {
            const input = document.getElementById('chatflow-input');
            if (input.value.trim() && window.ChatFlowConfig.conversationId) {
                sendMessage(input.value.trim());
                input.value = '';
            }
        });

        function sendMessage(content) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/messages.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    fetchMessages(); // Immediately fetch messages after sending
                }
            };
            xhr.send(JSON.stringify({
                conversation_id: window.ChatFlowConfig.conversationId,
                sender_type: 'customer',
                content: content
            }));
        }

        function fetchMessages() {
            if (!window.ChatFlowConfig.conversationId) return;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `/api/messages.php?conversation_id=${window.ChatFlowConfig.conversationId}`, true);
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const messages = JSON.parse(xhr.responseText);
                    const messagesContainer = document.getElementById('chatflow-messages');
                    messagesContainer.innerHTML = ''; // Clear existing messages
                    messages.forEach(msg => {
                        const msgElement = document.createElement('div');
                        msgElement.textContent = `[${msg.sender_type}] ${msg.content}`;
                        messagesContainer.appendChild(msgElement);
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            };
            xhr.send();
        }

        let pollingInterval;
        function pollMessages() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(fetchMessages, 4000); // Poll every 4 seconds
        }
    });
})();
