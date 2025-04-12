document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle Functionality
    const themeToggle = document.querySelector('.theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Set initial theme
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
    
    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    // Mobile Menu Toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.header__nav');
    
    mobileMenuToggle.addEventListener('click', function() {
        navMenu.classList.toggle('active');
        this.querySelector('i').classList.toggle('fa-bars');
        this.querySelector('i').classList.toggle('fa-times');
    });
    
    // Notification Dropdown Toggle
    const notificationBtn = document.querySelector('.notification');
    const notificationDropdown = document.querySelector('.notification__dropdown');
    
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        notificationDropdown.classList.remove('active');
    });
    
    // Countdown Timer Functionality
    function updateCountdowns() {
        const countdownElements = document.querySelectorAll('.countdown');
        
        countdownElements.forEach(element => {
            const endTime = new Date(element.getAttribute('data-end')).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                element.textContent = '...';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            if (days > 0) {
                element.textContent = `${days}d ${hours}h`;
            } else if (hours > 0) {
                element.textContent = `${hours}h ${minutes}m`;
            } else {
                element.textContent = `${minutes}m ${seconds}s`;
            }
        });
    }
    
    // Initialize and update countdowns every second
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
    
    // Enhanced Search Functionality
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const searchForm = document.getElementById('searchForm');
    const categoryFilter = document.getElementById('categoryFilter');
    const priceFilter = document.getElementById('priceFilter');
    const timeFilter = document.getElementById('timeFilter');
    const resetFilters = document.getElementById('resetFilters');
    
    // Sample search suggestions data
    const suggestions = [
        "Vintage Watches",
        "Art Paintings",
        "Sports Memorabilia",
        "Rare Coins",
        "Collectible Cards",
        "Antique Furniture",
        "Designer Jewelry",
        "Vinyl Records",
        "Vintage Cameras",
        "Signed Books"
    ];
    
    searchInput.addEventListener('input', function() {
        const input = this.value.toLowerCase();
        searchSuggestions.innerHTML = '';
        
        if (input.length > 1) {
            const filtered = suggestions.filter(item => 
                item.toLowerCase().includes(input))
                .slice(0, 5);
            
            if (filtered.length > 0) {
                filtered.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion__item';
                    div.textContent = item;
                    div.addEventListener('click', function() {
                        searchInput.value = item;
                        searchSuggestions.classList.remove('active');
                        filterAuctions();
                    });
                    searchSuggestions.appendChild(div);
                });
                searchSuggestions.classList.add('active');
            } else {
                searchSuggestions.classList.remove('active');
            }
        } else {
            searchSuggestions.classList.remove('active');
        }
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.classList.remove('active');
        }
    });
    
    // Filter auctions based on search and filters
    function filterAuctions() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const price = priceFilter.value;
        const time = timeFilter.value;
        
        const auctionCards = document.querySelectorAll('.auction__card');
        auctionCards.forEach(card => {
            const title = card.querySelector('.card__title').textContent.toLowerCase();
            const desc = card.querySelector('.card__description').textContent.toLowerCase();
            const priceText = card.querySelector('.card__price').textContent;
            const timeLeft = card.querySelector('.overlay__timer').textContent;
            const cardCategory = card.dataset.category;
            
            const matchesSearch = searchTerm === '' || 
                title.includes(searchTerm) || 
                desc.includes(searchTerm);
                
            const matchesCategory = category === '' || cardCategory === category;
            const matchesPrice = price === '' || checkPriceRange(priceText, price);
            const matchesTime = time === '' || checkTimeLeft(timeLeft, time);
            
            if (matchesSearch && matchesCategory && matchesPrice && matchesTime) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    function checkPriceRange(priceText, range) {
        const price = parseInt(priceText.replace(/\D/g, ''));
        if (range === '0-100') return price >= 0 && price <= 100;
        if (range === '100-500') return price > 100 && price <= 500;
        if (range === '500-1000') return price > 500 && price <= 1000;
        if (range === '1000-5000') return price > 1000 && price <= 5000;
        if (range === '5000+') return price > 5000;
        return true;
    }
    
    function checkTimeLeft(timeLeft, time) {
        if (time === '1h') return timeLeft.includes('m') || timeLeft.includes('h');
        if (time === '6h') return !timeLeft.includes('d');
        if (time === '24h') return true;
        if (time === '3d') return true;
        return true;
    }
    
    // Event listeners for filters
    [categoryFilter, priceFilter, timeFilter].forEach(filter => {
        filter.addEventListener('change', filterAuctions);
    });
    
    resetFilters.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = '';
        priceFilter.value = '';
        timeFilter.value = '';
        searchSuggestions.classList.remove('active');
        filterAuctions();
    });
    
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        filterAuctions();
    });
    
    // Enhanced Live Auctions
    const auctionsGrid = document.getElementById('auctionsGrid');
    const sortPrice = document.getElementById('sortPrice');
    const sortTime = document.getElementById('sortTime');
    
    // Sample auction data
    const auctions = [
        {
            id: 1,
            image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            title: 'Vintage Rolex Watch',
            description: '1950s Submariner, excellent condition',
            price: 1250,
            bids: 12,
            endTime: '2025-04-20T08:45:00',
            category: 'collectibles'
        },
        {
            id: 2,
            image: 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            title: 'First Edition Book',
            description: '"The Great Gatsby" 1925 original',
            price: 3450,
            bids: 8,
            endTime: '2025-04-20T12:30:00',
            category: 'collectibles'
        },
        {
            id: 3,
            image: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            title: 'Vintage Sunglasses',
            description: '1950s Ray-Ban Aviators',
            price: 320,
            bids: 5,
            endTime: '2025-04-20T18:15:00',
            category: 'collectibles'
        },
       
    ];
    
    // Render auction cards
    function renderAuctions(auctionsToRender) {
        auctionsGrid.innerHTML = '';
        
        auctionsToRender.forEach(auction => {
            const card = document.createElement('div');
            card.className = 'auction__card';
            card.dataset.id = auction.id;
            card.dataset.category = auction.category;
            
            card.innerHTML = `
                <div class="card__image">
                    <img src="${auction.image}" alt="${auction.title}">
                    <div class="image__overlay">
                        <span class="overlay__text">${auction.bids} Bids</span>
                        <span class="overlay__timer countdown" data-end="${auction.endTime}">1d 2h</span>
                    </div>
                </div>
               
            `;
            
            auctionsGrid.appendChild(card);
        });
        
        
        updateCountdowns();
        
        // // Add event listeners to bid buttons
        // document.querySelectorAll('.bid-btn').forEach(btn => {
        //     btn.addEventListener('click', function() {
        //         this.closest('.auction__card').classList.add('show-bid-form');
        //     });
        // });
        
        // Add event listeners to submit bid buttons
        // document.querySelectorAll('.submit-bid').forEach(btn => {
        //     btn.addEventListener('click', function() {
        //         const card = this.closest('.auction__card');
        //         const input = card.querySelector('.bid-form input');
        //         const bidAmount = parseInt(input.value);
        //         const currentPrice = parseInt(card.querySelector('.card__price').textContent.replace(/\D/g, ''));
                
        //         if (bidAmount > currentPrice) {
        //             // Update UI
        //             card.querySelector('.card__price').textContent = `$${bidAmount.toLocaleString()}`;
        //             card.querySelector('.card__price').classList.add('bid-updated');
        //             card.querySelector('.overlay__text').textContent = `${parseInt(card.querySelector('.overlay__text').textContent) + 1} Bids`;
        //             card.classList.remove('show-bid-form');
                    
        //             // Remove animation class after animation completes
        //             setTimeout(() => {
        //                 card.querySelector('.card__price').classList.remove('bid-updated');
        //             }, 500);
                    
        //             // Show live update
        //             showLiveUpdate(`${auctions.find(a => a.id == card.dataset.id).title} received new bid: $${bidAmount.toLocaleString()}`);
        //         } else {
        //             alert(`Your bid must be higher than the current price of $${currentPrice.toLocaleString()}`);
        //         }
        //     });
        // });
    }
    
    // Initial render
    renderAuctions(auctions);
    
    // Sorting
    sortPrice.addEventListener('click', function() {
        const sorted = [...auctions].sort((a, b) => b.price - a.price);
        renderAuctions(sorted);
    });
    
    sortTime.addEventListener('click', function() {
        const sorted = [...auctions].sort((a, b) => new Date(a.endTime) - new Date(b.endTime));
        renderAuctions(sorted);
    });
    
    // Live Updates
    function showLiveUpdate(text) {
        const updatesContainer = document.querySelector('.live-updates');
        const updateItem = document.createElement('div');
        updateItem.className = 'update__item';
        updateItem.innerHTML = `
            <span class="update__icon"><i class="fas fa-bolt"></i></span>
            <span class="update__text">${text}</span>
        `;
        
        updatesContainer.insertBefore(updateItem, updatesContainer.firstChild);
        
        // Remove after 10 seconds
        setTimeout(() => {
            updateItem.remove();
        }, 10000);
    }
    
    // Simulate live updates
    setInterval(() => {
        const randomAuction = auctions[Math.floor(Math.random() * auctions.length)];
        const bidIncrease = Math.floor(Math.random() * 500) + 50;
        const newPrice = randomAuction.price + bidIncrease;
        
        showLiveUpdate(`User${Math.floor(Math.random() * 1000)} bid $${newPrice.toLocaleString()} on ${randomAuction.title}`);
    }, 15000);
    
    // Close Notification Dropdown
    const closeDropdownBtn = document.querySelector('.dropdown__close');
    if (closeDropdownBtn) {
        closeDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.remove('active');
        });
    }
    
    // Mark notifications as read when clicked
    const notificationItems = document.querySelectorAll('.dropdown__item.unread');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('unread');
            const badge = document.querySelector('.notification__badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent);
                if (currentCount > 1) {
                    badge.textContent = currentCount - 1;
                } else {
                    badge.style.display = 'none';
                }
            }
        });
    });
});

// Add these to your existing script.js

// Profile dropdown interaction
const profileDropdown = document.querySelector('.user-profile');
if (profileDropdown) {
    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = this.querySelector('.profile__dropdown');
        dropdown.classList.toggle('active');
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function() {
    const dropdowns = document.querySelectorAll('.notification__dropdown, .profile__dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
    });
});

// Prevent dropdown close when clicking inside
document.querySelectorAll('.notification__dropdown, .profile__dropdown').forEach(dropdown => {
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});



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
                fetch('profile/additional_files/remove_watchlist.php', {
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
        fetch('profile/additional_files/submit_review.php', {
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
        fetch('profile/additional_files/update_profile.php', {
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