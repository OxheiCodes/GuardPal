document.addEventListener('DOMContentLoaded', function() {
    // Handle connection requests
    const connectForms = document.querySelectorAll('.connect-form');
    
    connectForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button');
            
            // Disable button during submission
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Connecting...';
            
            try {
                const response = await fetch('../includes/ajax/connection.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Update button state based on success
                button.innerHTML = 'Connection Requested';
                button.classList.remove('btn-primary');
                button.classList.add('btn-secondary');
                
                // Show notification
                const notification = document.createElement('div');
                notification.className = 'position-fixed bottom-0 end-0 p-3';
                notification.style.zIndex = '11';
                notification.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Connection request sent
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
                
            } catch (error) {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-user-plus me-2"></i>Connect';
                
                // Show error notification
                const notification = document.createElement('div');
                notification.className = 'position-fixed bottom-0 end-0 p-3';
                notification.style.zIndex = '11';
                notification.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error sending connection request. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });
    });
    
    // Animations for profile components
    anime({
        targets: '.card',
        translateY: [30, 0],
        opacity: [0, 1],
        duration: 800,
        delay: anime.stagger(150),
        easing: 'easeOutQuad'
    });
});