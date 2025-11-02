(function() {
    // Wait for the DOM to be fully loaded
    document.addEventListener("DOMContentLoaded", function() {
        if (window.ChatFlowConfig && window.ChatFlowConfig.embedKey) {
            const embedKey = window.ChatFlowConfig.embedKey;
            const hostname = window.location.hostname;

            // Create the chat widget elements
            const chatContainer = document.createElement('div');
            chatContainer.id = 'chatflow-widget-container';
            chatContainer.innerHTML = `
                <div id="chatflow-header">Live Chat</div>
                <div id="chatflow-messages"></div>
                <div id="chatflow-footer">
                    <input type="text" id="chatflow-input" placeholder="Type a message...">
                    <button id="chatflow-send">Send</button>
                </div>
            `;
            document.body.appendChild(chatContainer);

            // Add some basic styling
            const style = document.createElement('style');
            style.innerHTML = `
                #chatflow-widget-container {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    width: 300px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    display: none; /* Initially hidden */
                    flex-direction: column;
                    background-color: white;
                    z-index: 9999;
                }
                #chatflow-header {
                    background-color: #007bff;
                    color: white;
                    padding: 10px;
                    border-top-left-radius: 5px;
                    border-top-right-radius: 5px;
                    cursor: pointer;
                }
                #chatflow-messages {
                    height: 200px;
                    overflow-y: auto;
                    padding: 10px;
                    border-bottom: 1px solid #ccc;
                }
                #chatflow-footer {
                    display: flex;
                    padding: 10px;
                }
                #chatflow-input {
                    flex-grow: 1;
                    border: 1px solid #ccc;
                    border-radius: 3px;
                    padding: 5px;
                }
                #chatflow-send {
                    margin-left: 10px;
                    background-color: #007bff;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    border-radius: 3px;
                    cursor: pointer;
                }
            `;
            document.head.appendChild(style);

            // Toggle chat widget visibility on header click
            const chatHeader = document.getElementById('chatflow-header');
            const chatBody = document.getElementById('chatflow-messages');
            const chatFooter = document.getElementById('chatflow-footer');

            let isChatOpen = false;

            chatHeader.addEventListener('click', () => {
                isChatOpen = !isChatOpen;
                chatContainer.style.display = isChatOpen ? 'flex' : 'none';
                if(isChatOpen && !window.ChatFlowConfig.conversationId) {
                    initChat();
                }
            });

            // Function to initialize the chat
            function initChat() {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/init.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const response = JSON.parse(xhr.responseText);
                        if(response.success) {
                            window.ChatFlowConfig.conversationId = response.conversation_id;
                            // Start polling for messages
                            pollMessages();
                        } else {
                            console.error('ChatFlow Error:', response.error);
                        }
                    } else {
                        console.error('Failed to initialize chat session.');
                    }
                };
                xhr.onerror = function() {
                    console.error('Network error while initializing chat.');
                };
                xhr.send(JSON.stringify({
                    embed_key: embedKey,
                    hostname: hostname,
                    // You would typically get customer info from a form
                    customer_name: 'Guest',
                    customer_email: 'guest@example.com'
                }));
            }

            // Function to send a message
            document.getElementById('chatflow-send').addEventListener('click', function() {
                const input = document.getElementById('chatflow-input');
                const message = input.value.trim();
                if (message && window.ChatFlowConfig.conversationId) {
                    sendMessage(message);
                    input.value = '';
                }
            });

            function sendMessage(content) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/messages.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const response = JSON.parse(xhr.responseText);
                        if(response.success) {
                            fetchMessages();
                        }
                    }
                };
                xhr.send(JSON.stringify({
                    conversation_id: window.ChatFlowConfig.conversationId,
                    sender_type: 'customer',
                    content: content
                }));
            }

            function fetchMessages() {
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
                        messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to bottom
                    }
                };
                xhr.send();
            }

            // Polling mechanism
            let pollingInterval;
            function pollMessages() {
                if(pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(fetchMessages, 3000); // Poll every 3 seconds
            }

        } else {
            console.error("ChatFlow configuration not found. Please ensure window.ChatFlowConfig.embedKey is set.");
        }
    });
})();
