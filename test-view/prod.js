        // Add interactivity for filter items
        document.querySelectorAll('.filter-item').forEach(item => {
            item.addEventListener('click', function() {
                // Toggle active state
                this.style.backgroundColor = this.style.backgroundColor === 'rgb(243, 244, 246)' ? '' : '#f3f4f6';
            });
        });
        
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Simple feedback
                const originalText = this.textContent;
                this.textContent = 'Added!';
                this.style.backgroundColor = '#4caf50';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.backgroundColor = '#2196f3';
                }, 1500);
            });
        });
        
        // Search functionality
        document.querySelector('.search-input').addEventListener('input', function() {
            // Simple search placeholder functionality
            console.log('Searching for:', this.value);
        });