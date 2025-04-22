document.addEventListener('DOMContentLoaded', function() {
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
                    this.classList.toggle('bookmarked');
                    
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
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});