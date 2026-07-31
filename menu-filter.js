document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.item');
    const searchInput = document.getElementById('menu-search');

    let currentFilter = 'all';

    function filterItems() {

        const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';

        items.forEach(function(item) {

            const category = item.getAttribute('data-category');
            const title = item.querySelector('h3').textContent.toLowerCase();
            const description = item.querySelector('.item-info p').textContent.toLowerCase();

            const matchCategory =
                currentFilter === 'all' || currentFilter === category;

            const matchSearch =
                title.includes(searchText) ||
                description.includes(searchText);

            if (matchCategory && matchSearch) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }

        });

    }

    buttons.forEach(function(button) {

        button.addEventListener('click', function() {

            buttons.forEach(function(btn) {
                btn.classList.remove('active');
            });

            button.classList.add('active');

            currentFilter = button.dataset.filter;

            filterItems();

        });

    });

    if (searchInput) {

        searchInput.addEventListener('keyup', function() {
            filterItems();
        });

    }

});