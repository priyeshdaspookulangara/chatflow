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
            <div id="chatflow-header">Live Chat (Click to Close)</div>
            <div id="chatflow-messages"></div>
            <div id="chatflow-footer">
                <input type="text" id="chatflow-input" placeholder="Type a message...">
                <button id="chatflow-send">Send</button>
            </div>
        `;
        document.body.appendChild(chatContainer);

        // --- Add CSS Styling ---
        const style = document.createElement('style');
        style.innerHTML = `
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
            }
            #chatflow-widget-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 320px;
                height: 450px;
                border: 1px solid #ccc;
                border-radius: 10px;
                display: none; /* Initially hidden */
                flex-direction: column;
                background-color: white;
                z-index: 9999;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            #chatflow-header {
                background-color: #007bff;
                color: white;
                padding: 15px;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
                cursor: pointer;
            }
            #chatflow-messages {
                flex-grow: 1;
                overflow-y: auto;
                padding: 15px;
            }
            #chatflow-footer {
                display: flex;
                padding: 10px;
                border-top: 1px solid #eee;
            }
            #chatflow-input {
                flex-grow: 1;
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 10px;
            }
            #chatflow-send {
                margin-left: 10px;
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 5px;
                cursor: pointer;
            }
        `;
        document.head.appendChild(style);

        // --- Event Listeners ---
        chatBubble.addEventListener('click', toggleChat);
        document.getElementById('chatflow-header').addEventListener('click', toggleChat);

        function toggleChat() {
            isChatOpen = !isChatOpen;
            chatContainer.style.display = isChatOpen ? 'flex' : 'none';
            chatBubble.style.display = isChatOpen ? 'none' : 'flex';

            // Initialize chat on first open
            if (isChatOpen && !window.ChatFlowConfig.conversationId) {
                initChat();
            }
        }

        // --- API Functions ---
        function initChat() {
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
                customer_name: 'Guest',
                customer_email: 'guest@example.com'
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
