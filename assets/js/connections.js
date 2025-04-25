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
                
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                
                // Update button state
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
    
    // Connection action forms (accept, reject, remove)
    const connectionActionForms = document.querySelectorAll('.connection-action-form');
    
    connectionActionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('input[name="action"]').value;
            const isRemove = action === 'remove';
            
            if (isRemove && !confirm('Are you sure you want to remove this connection?')) {
                e.preventDefault();
            }
        });
    });
    
    // Connection tab switcher
    const connectionTabs = document.querySelectorAll('#connectionsTab button');
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        const tabToActivate = document.querySelector(`#connectionsTab button[data-bs-target="#${activeTab}"]`);
        
        if (tabToActivate) {
            const tab = new bootstrap.Tab(tabToActivate);
            tab.show();
        }
    }
    
    // Filter connections
    const connectionFilter = document.getElementById('connection-filter');
    
    if (connectionFilter) {
        connectionFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const connectionCards = document.querySelectorAll('.connection-card');
            
            connectionCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const jobTitle = card.querySelector('.text-muted').textContent.toLowerCase();
                const location = card.querySelector('.location-text')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || jobTitle.includes(searchTerm) || location.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}); 