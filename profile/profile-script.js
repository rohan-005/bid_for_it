// Profile Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Only run on profile page
    if (!document.querySelector('.profile-main')) return;

    // Tab Switching
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabLinks.forEach(tab => tab.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            const targetPane = document.querySelector(this.getAttribute('href'));
            if (targetPane) targetPane.classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', this.getAttribute('href').substring(1));
            window.history.pushState({}, '', url);
        });
    });

    // Table Sorting
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const sortKey = this.dataset.sort;
            const isAscending = !this.classList.contains('asc');
            
            // Reset all headers
            sortableHeaders.forEach(h => {
                h.classList.remove('asc', 'desc');
                h.querySelector('i').className = 'fas fa-sort';
            });
            
            // Set current header state
            this.classList.add(isAscending ? 'asc' : 'desc');
            const icon = this.querySelector('i');
            icon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
            
            // Sort table rows (simplified - in real app would fetch sorted data from server)
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            
            rows.sort((a, b) => {
                const aValue = a.children[columnIndex].textContent.trim();
                const bValue = b.children[columnIndex].textContent.trim();
                
                if (sortKey === 'amount') {
                    const aNum = parseFloat(aValue.replace(/[^0-9.]/g, ''));
                    const bNum = parseFloat(bValue.replace(/[^0-9.]/g, ''));
                    return isAscending ? aNum - bNum : bNum - aNum;
                } else if (sortKey === 'time') {
                    return isAscending ? 
                        new Date(aValue) - new Date(bValue) : 
                        new Date(bValue) - new Date(aValue);
                } else {
                    return isAscending ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                }
            });
            
            // Re-append sorted rows
            const tbody = table.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Watchlist Item Removal
    document.querySelectorAll('.btn--remove').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            if (confirm('Are you sure you want to remove this item from your watchlist?')) {
                // In a real app, this would be an AJAX call to the server
                fetch('additional_files/remove_watchlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ item_id: itemId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.auction-card').remove();
                        
                        // Update watchlist count
                        const countElement = document.querySelector('.stat-number:nth-child(1)');
                        if (countElement) {
                            countElement.textContent = parseInt(countElement.textContent) - 1;
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to remove item'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item');
                });
            }
        });
    });

    // Review Modal
    const reviewModal = document.getElementById('reviewModal');
    const reviewButtons = document.querySelectorAll('.btn--review');
    const closeModal = document.querySelector('.modal__close');
    
    reviewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            document.getElementById('review_item_id').value = itemId;
            reviewModal.classList.add('active');
        });
    });
    
    closeModal.addEventListener('click', function() {
        reviewModal.classList.remove('active');
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === reviewModal) {
            reviewModal.classList.remove('active');
        }
    });
    
    // Star Rating
    const stars = document.querySelectorAll('.rating-input i');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            document.getElementById('ratingValue').value = rating;
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    // Form Submission
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // In a real app, this would be an AJAX call to the server
        fetch('additional_files/submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your review!');
                reviewModal.classList.remove('active');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to submit review'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your review');
        });
    });
    
    // Settings Form
    document.getElementById('profileSettings').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // In a real app, this would be an AJAX call to the server
        fetch('additional_files/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
                if (data.avatar) {
                    document.querySelector('.profile-avatar img').src = data.avatar;
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to update profile'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating your profile');
        });
    });
});