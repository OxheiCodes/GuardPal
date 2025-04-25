document.addEventListener('DOMContentLoaded', function() {
    // Job search filters
    const filterForm = document.getElementById('job-filters');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const loadingIndicator = document.getElementById('loading-indicator');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            if (loadingIndicator) {
                loadingIndicator.classList.remove('d-none');
            }
            
            // Collect form data
            const formData = new FormData(filterForm);
            const searchParams = new URLSearchParams();
            
            for (const pair of formData) {
                searchParams.append(pair[0], pair[1]);
            }
            
            // Redirect to search page with filters
            window.location.href = 'search.php?' + searchParams.toString();
        });
    }
    
    // Bookmark functionality
    const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
    
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const jobId = this.dataset.jobId;
            const icon = this.querySelector('i');
            const isBookmarked = icon.classList.contains('fas');
            
            try {
                const response = await fetch('../includes/ajax/bookmark.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `job_id=${jobId}&action=${isBookmarked ? 'remove' : 'add'}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    button.classList.toggle('bookmarked');
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'position-fixed bottom-0 end-0 p-3';
                    notification.style.zIndex = '11';
                    notification.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${isBookmarked ? 'Job removed from bookmarks' : 'Job added to bookmarks'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                } else {
                    // Show error notification
                    const notification = document.createElement('div');
                    notification.className = 'position-fixed bottom-0 end-0 p-3';
                    notification.style.zIndex = '11';
                    notification.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${data.message || 'Error updating bookmark'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    
    // Job card animations
    const jobCards = document.querySelectorAll('.job-card');
    
    jobCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            anime({
                targets: card,
                translateY: -10,
                boxShadow: '0 10px 20px rgba(0, 0, 0, 0.1)',
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
        
        card.addEventListener('mouseleave', () => {
            anime({
                targets: card,
                translateY: 0,
                boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
    });
});