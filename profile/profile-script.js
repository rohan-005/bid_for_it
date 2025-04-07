document.addEventListener('DOMContentLoaded', function() {
    // Only run on profile page
    if (!document.querySelector('.profile-main')) return;

    // Tab Switching - Improved with history state management
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    function activateTab(tabId) {
        tabLinks.forEach(tab => tab.classList.remove('active'));
        tabPanes.forEach(pane => pane.classList.remove('active'));
        
        const activeLink = document.querySelector(`.tab-link[href="#${tabId}"]`);
        const activePane = document.getElementById(tabId);
        
        if (activeLink && activePane) {
            activeLink.classList.add('active');
            activePane.classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({ tab: tabId }, '', url);
        }
    }
    
    // Initialize tab from URL or default to 'bidding'
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'bidding';
    activateTab(initialTab);
    
    // Handle tab clicks
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('href').substring(1);
            activateTab(tabId);
        });
    });
    
    // Handle back/forward navigation
    window.addEventListener('popstate', function(e) {
        const tabId = e.state?.tab || 'bidding';
        activateTab(tabId);
    });

    // Table Sorting - Fixed to prevent disappearing rows
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
                const icon = h.querySelector('i');
                if (icon) icon.className = 'fas fa-sort';
            });
            
            // Set current header state
            this.classList.add(isAscending ? 'asc' : 'desc');
            const icon = this.querySelector('i');
            if (icon) icon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
            
            // Sort table rows
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
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
                }
                return 0;
            });
            
            // Re-append sorted rows while preserving event listeners
            rows.forEach(row => {
                const newRow = row.cloneNode(true);
                tbody.replaceChild(newRow, row);
            });
        });
    });

    // Watchlist Item Removal - Fixed with proper event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn--remove')) {
            const button = e.target.closest('.btn--remove');
            const itemId = button.dataset.itemId;
            const card = button.closest('.auction-card');
            
            if (confirm('Are you sure you want to remove this item from your watchlist?')) {
                fetch('additional_files/remove_watchlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}`
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Fade out animation before removal
                        card.style.opacity = '0';
                        card.style.transition = 'opacity 0.3s ease';
                        
                        setTimeout(() => {
                            card.remove();
                            
                            // Update watchlist count
                            const countElement = document.querySelector('.stat-item:nth-child(2) .stat-number');
                            if (countElement) {
                                const newCount = parseInt(countElement.textContent) - 1;
                                countElement.textContent = newCount;
                                
                                // Show empty state if no items left
                                if (newCount === 0) {
                                    document.querySelector('#watchlist').innerHTML = `
                                        <h2 class="tab-title">Your Watchlist</h2>
                                        <div class="empty-state">
                                            <i class="fas fa-binoculars empty-state__icon"></i>
                                            <h3>Your watchlist is empty</h3>
                                            <p>Start adding items to track auctions you're interested in</p>
                                            <a href="../home.php" class="btn btn--primary">Browse Auctions</a>
                                        </div>
                                    `;
                                }
                            }
                        }, 300);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to remove item'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item');
                });
            }
        }
    });

    // Review Modal - Improved with form reset
    const reviewModal = document.getElementById('reviewModal');
    const reviewForm = document.getElementById('reviewForm');
    
    function openReviewModal(itemId) {
        document.getElementById('review_item_id').value = itemId;
        
        // Reset form state
        reviewForm.reset();
        document.querySelectorAll('.rating-input i').forEach(star => {
            star.classList.remove('active');
        });
        
        reviewModal.classList.add('active');
    }
    
    function closeReviewModal() {
        reviewModal.classList.remove('active');
    }
    
    // Handle review button clicks with event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn--review')) {
            const button = e.target.closest('.btn--review');
            openReviewModal(button.dataset.itemId);
        }
    });
    
    // Close modal handlers
    document.querySelector('.modal__close')?.addEventListener('click', closeReviewModal);
    window.addEventListener('click', function(e) {
        if (e.target === reviewModal) {
            closeReviewModal();
        }
    });
    
    // Star Rating - Improved with hover effect
    const stars = document.querySelectorAll('.rating-input i');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            document.getElementById('ratingValue').value = rating;
            
            stars.forEach((s, index) => {
                s.classList.toggle('active', index < rating);
            });
        });
        
        star.addEventListener('mouseover', function() {
            const hoverRating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
                s.classList.toggle('hover', index < hoverRating);
            });
        });
        
        star.addEventListener('mouseout', function() {
            stars.forEach(s => s.classList.remove('hover'));
        });
    });
    
    // Review Form Submission - Fixed with proper error handling
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        
        const formData = new FormData(this);
        
        fetch('additional_files/submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                closeReviewModal();
                // Show success message
                showNotification('Thank you for your review!', 'success');
                
                // Reload won items tab to show updated rating
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to submit review');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });
    
    // Settings Form - Improved with validation
    const settingsForm = document.getElementById('profileSettings');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Validate password change if fields are filled
            const newPassword = this.elements.new_password.value;
            const confirmPassword = this.elements.confirm_password.value;
            
            if (newPassword || confirmPassword) {
                const currentPassword = this.elements.current_password.value;
                if (!currentPassword) {
                    showNotification('Please enter your current password to change password', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showNotification('New passwords do not match', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                    return;
                }
                
                if (newPassword.length < 8) {
                    showNotification('Password must be at least 8 characters', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                    return;
                }
            }
            
            const formData = new FormData(this);
            
            fetch('additional_files/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showNotification('Profile updated successfully!', 'success');
                    
                    // Update avatar if changed
                    if (data.avatar) {
                        const avatarImg = document.querySelector('.profile-avatar img');
                        if (avatarImg) {
                            avatarImg.src = data.avatar;
                        }
                    }
                    
                    // Update username in header if changed
                    const newUsername = this.elements.username.value;
                    const profileNameElements = document.querySelectorAll('.profile__name');
                    profileNameElements.forEach(el => {
                        el.textContent = newUsername;
                    });
                } else {
                    throw new Error(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
    
    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <span class="notification__icon">
                ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>'}
            </span>
            <span class="notification__message">${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('notification--fade');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // Initialize any tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.dataset.tooltip;
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            
            const rect = this.getBoundingClientRect();
            tooltipElement.style.left = `${rect.left + rect.width / 2}px`;
            tooltipElement.style.top = `${rect.top - 40}px`;
            
            document.body.appendChild(tooltipElement);
            
            this.addEventListener('mouseleave', function() {
                tooltipElement.remove();
            }, { once: true });
        });
    });
});