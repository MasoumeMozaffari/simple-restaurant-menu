document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.filter-btn');
    var items = document.querySelectorAll('.item');

    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            var filter = button.getAttribute('data-filter');

            buttons.forEach(function(b) {
                b.classList.remove('active');
            });
            button.classList.add('active');

            items.forEach(function(item) {
                var category = item.getAttribute('data-category');
                if (filter === 'all' || filter === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
