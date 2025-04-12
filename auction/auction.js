document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let currentSort = 'ending';
    let currentCategory = 'all';
    let currentSearch = '';
    
    // Modal functionality
    const setupModals = () => {
        document.querySelectorAll('.btn-bid').forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.getAttribute('data-target');
                document.querySelector(modalId).style.display = 'block';
            });
        });
        
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });
    };
    
    // Watchlist functionality
   // Watchlist functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-watchlist').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const itemId = this.dataset.itemId;
            const isActive = this.classList.contains('active');
            const action = isActive ? 'remove' : 'add';
            
            try {
                const response = await fetch('../phpfiles/get_watchlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}&action=${action}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.classList.toggle('active');
                    // Update watchlist count if exists
                    const watchlistCount = document.querySelector('.watchlist-count');
                    if (watchlistCount) {
                        const currentCount = parseInt(watchlistCount.textContent);
                        watchlistCount.textContent = action === 'add' ? currentCount + 1 : currentCount - 1;
                    }
                    showToast(action === 'add' ? 'Added to watchlist' : 'Removed from watchlist');
                } else {
                    showToast(data.message || 'Operation failed', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            }
        });
    });
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
    
    // Sorting functionality
    const sortItems = () => {
        const items = Array.from(document.querySelectorAll('.auction-item'));
        
        items.sort((a, b) => {
            const aPrice = parseFloat(a.querySelector('.price-amount').textContent.replace('$', ''));
            const bPrice = parseFloat(b.querySelector('.price-amount').textContent.replace('$', ''));
            const aTime = a.querySelector('.auction-time-left').getAttribute('data-end-time');
            const bTime = b.querySelector('.auction-time-left').getAttribute('data-end-time');
            
            switch(currentSort) {
                case 'ending':
                    return new Date(aTime) - new Date(bTime);
                case 'newest':
                    return new Date(bTime) - new Date(aTime);
                case 'price-low':
                    return aPrice - bPrice;
                case 'price-high':
                    return bPrice - aPrice;
                default:
                    return 0;
            }
        });
        
        const container = document.querySelector('.auction-grid');
        items.forEach(item => container.appendChild(item));
    };
    
    // Filter functionality
    const filterItems = () => {
        document.querySelectorAll('.auction-item').forEach(item => {
            const matchesCategory = currentCategory === 'all' || 
                                  item.getAttribute('data-category') === currentCategory;
            
            const matchesSearch = item.querySelector('h3').textContent.toLowerCase()
                .includes(currentSearch.toLowerCase()) || 
                item.querySelector('.auction-item-description').textContent.toLowerCase()
                .includes(currentSearch.toLowerCase());
            
            if (matchesCategory && matchesSearch) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    };
    
    // Timer functionality
    const updateTimers = () => {
        document.querySelectorAll('.auction-time-left').forEach(timer => {
            const endTime = timer.getAttribute('data-end-time');
            if (!endTime) return;
            
            const now = new Date();
            const end = new Date(endTime);
            const diff = end - now;
            
            if (diff <= 0) {
                timer.textContent = 'Ended';
                timer.closest('.auction-item').querySelector('.btn-bid').disabled = true;
                timer.closest('.auction-item').querySelector('.btn-bid').textContent = 'Auction Ended';
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            
            timer.textContent = `${days}d ${hours}h`;
        });
    };
    
    // Event listeners for filters
    document.getElementById('sort').addEventListener('change', function() {
        currentSort = this.value;
        sortItems();
    });
    
    document.getElementById('category').addEventListener('change', function() {
        currentCategory = this.value;
        filterItems();
    });
    
    document.getElementById('search').addEventListener('input', function() {
        currentSearch = this.value;
        filterItems();
    });
    
    // Initialize everything
    setupModals();
    setupWatchlist();
    sortItems();
    filterItems();
    updateTimers();
    
    // Update timers every minute
    setInterval(updateTimers, 60000);
});