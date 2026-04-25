document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Navbar Scroll Effect
    const navbar = document.getElementById('navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 2. Intersection Observer for Scroll Animations
    // This gives the advanced, smooth fade-in effect as the user scrolls down
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.2 // Trigger when 20% of the element is visible
    };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Stop observing once the animation has triggered
                observer.unobserve(entry.target); 
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.scroll-reveal');
    revealElements.forEach(el => {
        scrollObserver.observe(el);
    });

    // 3. Animated Number Counter for Impact Section
    const counters = document.querySelectorAll('.counter');
    let hasCounted = false;

    const runCounter = () => {
        counters.forEach(counter => {
            counter.innerText = '0';
            const target = +counter.getAttribute('data-target');
            
            // Adjust the denominator to change speed (higher = slower)
            const increment = target / 100; 

            const updateCounter = () => {
                const current = +counter.innerText;
                if (current < target) {
                    counter.innerText = `${Math.ceil(current + increment)}`;
                    setTimeout(updateCounter, 20); // 20ms refresh rate
                } else {
                    counter.innerText = target;
                }
            };
            updateCounter();
        });
    };

        // 4. Dynamic News Modal Logic
    const modal = document.getElementById('newsModal');
    const closeBtn = document.querySelector('.modal-close');
    const readMoreBtns = document.querySelectorAll('.read-more');
    
    // Modal Target Elements
    const modalImg = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDate = document.getElementById('modalDate');
    const modalFullText = document.getElementById('modalFullText');

    // Open Modal Function
    readMoreBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevents page jump
            
            // Extract data from the clicked card
            const card = e.target.closest('.news-card');
            const title = card.querySelector('h3').innerText;
            const date = card.querySelector('.news-date').innerText;
            const imgSrc = card.querySelector('img').src;
            const previewText = card.querySelector('p').innerText;
            
            // Populate Modal
            modalTitle.innerText = title;
            modalDate.innerText = date;
            modalImg.src = imgSrc;
            
            // Placeholder text. Your backend dev will output the full database article here.
            modalFullText.innerHTML = `
                <p><strong>${previewText}</strong></p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            `;

            // Display Modal & Prevent Background Scrolling
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; 
        });
    });

    // Close Modal Logic (Via close button)
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Re-enable background scrolling
    });

    // Close Modal Logic (Clicking the dark background overlay)
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close Modal Logic (Pressing Escape key)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });


    // 5. Database Category Tab Switching Logic
    const dbTabs = document.querySelectorAll('.db-tab');
    
    if (dbTabs.length > 0) {
        dbTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all
                dbTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Identify which category was clicked
                const selectedCategory = tab.getAttribute('data-category');
                
                // (Frontend visual feedback)
                console.log(`Category Switched to: ${selectedCategory}`);
                
                // Note to Backend Dev: 
                // Put your AJAX/Fetch call here to query the database 
                // based on the 'selectedCategory' variable.
            });
        });

        // Search Button Placeholder Logic
        const searchBtn = document.getElementById('searchBtn');
        const searchInput = document.getElementById('searchInput');
        const applyFiltersBtn = document.getElementById('applyFilters');

        const triggerSearch = () => {
            const query = searchInput.value;
            const college = document.getElementById('collegeFilter').value;
            const year = document.getElementById('yearFilter').value;
            const activeTab = document.querySelector('.db-tab.active').getAttribute('data-category');
            
            console.log(`Searching for: "${query}" | Tab: ${activeTab} | College: ${college} | Year: ${year}`);
            alert("Frontend ready! Backend developer will connect this action to the database queries.");
        };

        if (searchBtn) searchBtn.addEventListener('click', triggerSearch);
        if (applyFiltersBtn) applyFiltersBtn.addEventListener('click', triggerSearch);
        
        // Allow pressing "Enter" in the search bar
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') triggerSearch();
            });
        }
    }

    // Trigger the counter only when the section scrolls into view
    const impactSection = document.getElementById('impact');
    if (impactSection) {
        const counterObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !hasCounted) {
                runCounter();
                hasCounted = true; // Prevents it from recounting if they scroll up and down
            }
        }, { threshold: 0.5 }); // Triggers when 50% of the section is visible

        counterObserver.observe(impactSection);
    }


});






    