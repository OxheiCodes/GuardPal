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
    
    // Preview profile image before upload
    const profileImageInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-preview');
    
    if (profileImageInput && profilePreview) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // SIA License Number Validation
    const siaLicenseInput = document.getElementById('sia_license_number');
    
    if (siaLicenseInput) {
        // Format as user types (only allow numbers)
        siaLicenseInput.addEventListener('input', function() {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
            
            // Validate field
            if (this.value.length > 0 && this.value.length !== 16) {
                this.classList.add('is-invalid');
                const feedback = this.nextElementSibling || document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'SIA License Number must be exactly 16 digits';
                if (!this.nextElementSibling) {
                    this.parentNode.appendChild(feedback);
                }
            } else {
                this.classList.remove('is-invalid');
                if (this.nextElementSibling && this.nextElementSibling.className === 'invalid-feedback') {
                    this.nextElementSibling.remove();
                }
            }
        });
        
        // Format on blur (add spaces for readability)
        siaLicenseInput.addEventListener('blur', function() {
            if (this.value.length === 16) {
                // Add a space every 4 digits for display
                const formattedValue = this.value.replace(/(\d{4})(?=\d)/g, '$1 ');
                this.setAttribute('data-formatted', formattedValue);
            }
        });
        
        // Return to raw format on focus
        siaLicenseInput.addEventListener('focus', function() {
            this.value = this.value.replace(/\s/g, '');
        });
    }
    
    // Work experience current job checkbox
    const isCurrentCheckbox = document.getElementById('is_current');
    const endDateGroup = document.getElementById('end-date-group');
    const endDateInput = document.getElementById('end_date');
    
    if (isCurrentCheckbox && endDateGroup && endDateInput) {
        function toggleEndDate() {
            if (isCurrentCheckbox.checked) {
                endDateGroup.style.display = 'none';
                endDateInput.removeAttribute('required');
                endDateInput.value = '';
            } else {
                endDateGroup.style.display = 'block';
                endDateInput.setAttribute('required', 'required');
            }
        }
        
        isCurrentCheckbox.addEventListener('change', toggleEndDate);
        toggleEndDate(); // Initialize on page load
    }
    
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