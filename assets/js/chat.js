document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chat-box');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const receiverId = document.getElementById('receiver-id');
    
    // Scroll to bottom of chat
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    
    // Handle message form submission
    if (messageForm) {
        messageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            try {
                const response = await fetch('../includes/ajax/send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `receiver_id=${receiverId.value}&message=${encodeURIComponent(message)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add message to chat
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message sent';
                    messageDiv.innerHTML = `
                        ${message}
                        <div class="small text-muted">Just now</div>
                    `;
                    chatBox.appendChild(messageDiv);
                    
                    // Clear input and scroll to bottom
                    messageInput.value = '';
                    chatBox.scrollTop = chatBox.scrollHeight;
                    
                    // Focus input field for next message
                    messageInput.focus();
                } else {
                    alert('Failed to send message. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sending message. Please try again.');
            }
        });
    }
    
    // Auto-refresh chat messages (every 5 seconds)
    if (chatBox && receiverId) {
        setInterval(async () => {
            try {
                const response = await fetch(`../includes/ajax/get_messages.php?receiver_id=${receiverId.value}`);
                const data = await response.json();
                
                if (data.success && data.messages) {
                    // Clear and reload messages
                    chatBox.innerHTML = '';
                    data.messages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.is_sent ? 'sent' : 'received'}`;
                        messageDiv.innerHTML = `
                            ${message.message}
                            <div class="small text-muted">${message.formatted_time}</div>
                        `;
                        chatBox.appendChild(messageDiv);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            } catch (error) {
                console.error('Error refreshing messages:', error);
            }
        }, 5000);
    }
    
    // Add animation effects
    anime({
        targets: '.message',
        translateY: [10, 0],
        opacity: [0, 1],
        delay: anime.stagger(100),
        duration: 500,
        easing: 'easeOutQuad'
    });
});