document.addEventListener('DOMContentLoaded', function() {
    const schemeSearch = document.getElementById('scheme-search');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const schemeCards = document.querySelectorAll('.scheme-card');
    const schemeDetailsButtons = document.querySelectorAll('.scheme-details-btn');
    
    // Filter schemes by category
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            // Filter cards
            schemeCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Search functionality
    if (schemeSearch) {
        schemeSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            schemeCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('.scheme-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Toggle scheme details
    schemeDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.scheme-card');
            const details = card.querySelector('.scheme-details');
            
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'grid';
                this.textContent = 'Less Details';
            } else {
                details.style.display = 'none';
                this.textContent = 'More Details';
            }
        });
    });
});