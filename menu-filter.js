document.addEventListener('DOMContentLoaded', function() {
    var categoryButtons = document.querySelectorAll('.filter-btn');
    var badgeButtons = document.querySelectorAll('.filter-badge');
    var items = document.querySelectorAll('.item');
    var searchInput = document.getElementById('menu-search');
    var filterCount = document.createElement('div');
    filterCount.className = 'filter-count';
    var wrapper = document.querySelector('.simple-menu-wrapper');
    if (wrapper) {
        wrapper.insertBefore(filterCount, wrapper.querySelector('.items-grid'));
    }
    
    function updateCount(visibleCount, totalCount) {
        if (filterCount) {
            if (visibleCount === totalCount) {
                filterCount.textContent = '🍽️ ' + totalCount + ' آیتم در منو';
            } else {
                filterCount.textContent = '🔍 ' + visibleCount + ' از ' + totalCount + ' آیتم یافت شد';
            }
        }
    }
    
    function filterItems() {
        var activeCategory = document.querySelector('.filter-btn.active');
        var categoryFilter = activeCategory ? activeCategory.getAttribute('data-filter') : 'all';
        
        var activeBadges = [];
        badgeButtons.forEach(function(b) {
            if (b.classList.contains('active')) {
                activeBadges.push(b.getAttribute('data-filter'));
            }
        });
        
        var searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        
        var visibleCount = 0;
        var totalCount = items.length;
        
        items.forEach(function(item) {
            var itemCategory = item.getAttribute('data-category');
            var itemBadges = item.getAttribute('data-badges') ? 
                item.getAttribute('data-badges').split(',') : [];
            
            var title = item.querySelector('.item-info h3')?.textContent?.toLowerCase() || '';
            var desc = item.querySelector('.item-info p')?.textContent?.toLowerCase() || '';
            
            var categoryMatch = (categoryFilter === 'all' || categoryFilter === itemCategory);
            
            var badgesMatch = true;
            if (activeBadges.length > 0) {
                badgesMatch = activeBadges.some(function(badge) {
                    return itemBadges.includes(badge);
                });
            }
            
            var searchMatch = true;
            if (searchTerm.length > 0) {
                searchMatch = title.includes(searchTerm) || desc.includes(searchTerm);
            }
            
            if (categoryMatch && badgesMatch && searchMatch) {
                item.style.display = 'flex';
                item.classList.remove('hidden');
                item.classList.add('show');
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.classList.add('hidden');
                item.classList.remove('show');
            }
        });
        
        updateCount(visibleCount, totalCount);
    }
    
    categoryButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            categoryButtons.forEach(function(b) {
                b.classList.remove('active');
            });
            button.classList.add('active');
            filterItems();
        });
    });
    
    badgeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            button.classList.toggle('active');
            filterItems();
        });
    });
    
    if (searchInput) {
        var searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterItems();
            }, 300);
        });
    }
    
    filterItems();
});