// ===== GLOBAL VARIABLES =====
const navbar = document.getElementById('navbar');
const mobileToggle = document.querySelector('.mobile-toggle');
const navLinks = document.querySelector('.nav-links');
const dropdowns = document.querySelectorAll('.dropdown');
const testimonialTrack = document.querySelector('.testimonial-track');
const testimonialDots = document.querySelectorAll('.testimonial-dot');
const galleryItems = document.querySelectorAll('.gallery-item');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.querySelector('.lightbox-img');
const lightboxClose = document.querySelector('.lightbox-close');
const contactForm = document.getElementById('contactForm');
const reservationForm = document.getElementById('reservationForm');

// ===== NAVBAR SCROLL EFFECT =====
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// ===== MOBILE NAVIGATION TOGGLE =====
if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileToggle.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
    });
}

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
        mobileToggle.textContent = '☰';
    });
});

// ===== DROPDOWN MENUS FOR MOBILE =====
dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    const menu = dropdown.querySelector('.dropdown-menu');
    
    if (window.innerWidth <= 992) {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    }
});

// ===== TESTIMONIAL SLIDER =====
if (testimonialTrack) {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.testimonial-slide');
    const totalSlides = slides.length;
    
    function updateSlider() {
        testimonialTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update dots
        testimonialDots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }
    
    // Initialize dots
    if (testimonialDots.length > 0) {
        testimonialDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
        
        // Auto slide every 5 seconds
        setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }, 5000);
    }
}

// ===== GALLERY LIGHTBOX =====
if (galleryItems.length > 0) {
    galleryItems.forEach(item => {
        item.addEventListener('click', (e) => {
            const imgSrc = item.querySelector('img').src;
            lightboxImg.src = imgSrc;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
    
    lightboxClose.addEventListener('click', () => {
        lightbox.classList.remove('active');
        document.body.style.overflow = 'auto';
    });
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
}

// ===== FORM VALIDATION =====
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        input.classList.remove('error');
        
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
            showError(input, 'This field is required');
        } else if (input.type === 'email' && !isValidEmail(input.value)) {
            input.classList.add('error');
            isValid = false;
            showError(input, 'Please enter a valid email address');
        } else if (input.type === 'tel' && !isValidPhone(input.value)) {
            input.classList.add('error');
            isValid = false;
            showError(input, 'Please enter a valid phone number');
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone.replace(/[\s\-\(\)]/g, ''));
}

function showError(input, message) {
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    // Create error message
    const error = document.createElement('div');
    error.className = 'error-message';
    error.style.color = '#c41e3a';
    error.style.fontSize = '0.875rem';
    error.style.marginTop = '5px';
    error.textContent = message;
    
    input.parentNode.appendChild(error);
}

// ===== CONTACT FORM SUBMISSION =====
if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (validateForm(contactForm)) {
            // Here you would typically send the form data to a server
            // For now, we'll just show a success message
            showNotification('Your message has been sent successfully! We will get back to you soon.', 'success');
            contactForm.reset();
        }
    });
}

// ===== RESERVATION FORM SUBMISSION =====
if (reservationForm) {
    reservationForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (validateForm(reservationForm)) {
            // Here you would typically send the form data to a server
            // For now, we'll just show a success message
            showNotification('Your reservation has been submitted successfully! We will confirm shortly.', 'success');
            reservationForm.reset();
        }
    });
}

// ===== NOTIFICATION SYSTEM =====
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) existingNotification.remove();
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '8px';
    notification.style.color = 'white';
    notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#c41e3a';
    notification.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
    notification.style.zIndex = '3000';
    notification.style.transition = 'all 0.3s ease';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// ===== GOOGLE MAPS INTEGRATION =====
function initMap() {
    // This function will be called by the Google Maps API
    // You need to replace the coordinates with your actual location
    const location = { lat: 25.2048, lng: 55.2708 }; // Dubai coordinates as example
    
    if (typeof google !== 'undefined') {
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: location,
            styles: [
                {
                    featureType: 'all',
                    elementType: 'geometry',
                    stylers: [{ color: '#f5f1e8' }]
                },
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        const marker = new google.maps.Marker({
            position: location,
            map: map,
            title: 'Yalla Al Mandi',
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });
    }
}

// ===== DATE PICKER ENHANCEMENT =====
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
        
        // Set max date to 3 months from today
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        input.max = maxDate.toISOString().split('T')[0];
        
        // Add custom styling
        input.addEventListener('focus', () => {
            input.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
            input.parentNode.classList.remove('focused');
        });
    });
}

// ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        if (href === '#') return;
        
        e.preventDefault();
        const target = document.querySelector(href);
        
        if (target) {
            const offset = 80; // Height of fixed navbar
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ===== ACTIVE NAV LINK BASED ON SCROLL =====
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let currentSection = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.clientHeight;
        
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        
        if (href && href.includes(currentSection)) {
            link.classList.add('active');
        }
    });
}

// ===== INITIALIZE EVERYTHING WHEN DOM IS LOADED =====
document.addEventListener('DOMContentLoaded', () => {
    // Initialize components
    initDatePickers();
    updateActiveNavLink();
    
    // Add scroll event listener for active nav links
    window.addEventListener('scroll', updateActiveNavLink);
    
    // Add input event listeners to remove error styles
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('error');
            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) errorMessage.remove();
        });
    });
    
    // Load Google Maps if needed
    if (document.getElementById('map')) {
        // Load Google Maps script if not already loaded
        if (typeof google === 'undefined') {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        } else {
            initMap();
        }
    }
});

// ===== WINDOW RESIZE HANDLER =====
window.addEventListener('resize', () => {
    // Close mobile menu on resize to desktop
    if (window.innerWidth > 992) {
        navLinks.classList.remove('active');
        mobileToggle.textContent = '☰';
        
        // Reset dropdown menus for mobile
        dropdowns.forEach(dropdown => {
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.style.display = '';
        });
    }
});


// ===== GALLERY FILTER SCRIPT HANDLER =====
document.addEventListener('DOMContentLoaded', function() {
    // Gallery Filter Functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
            });
            this.classList.remove('btn-outline');
            this.classList.add('btn-primary');
            
            // Filter items
            const filterValue = this.getAttribute('data-filter');
            
            galleryItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
    // Initialize lightbox for gallery items
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxClose = document.querySelector('.lightbox-close');
    
    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            const imgAlt = this.querySelector('img').alt;
            lightboxImg.src = imgSrc;
            lightboxImg.alt = imgAlt;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close lightbox
    lightboxClose.addEventListener('click', () => {
        lightbox.classList.remove('active');
        document.body.style.overflow = 'auto';
    });
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
});


// ===== Offer Subscription Script =====
document.addEventListener('DOMContentLoaded', function() {
    // Subscription Form
    const subscriptionForm = document.getElementById('offerSubscription');
    
    if (subscriptionForm) {
        subscriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (validateEmail(email)) {
                // Simulate subscription success
                showNotification('Successfully subscribed to our offers newsletter!', 'success');
                emailInput.value = '';
            } else {
                showNotification('Please enter a valid email address.', 'error');
            }
        });
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showNotification(message, type) {
        // Remove existing notification
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) existingNotification.remove();
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '8px';
        notification.style.color = 'white';
        notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#c41e3a';
        notification.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
        notification.style.zIndex = '3000';
        notification.style.transition = 'all 0.3s ease';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});


// ===== Testimonials Page Specific Script =====
document.addEventListener('DOMContentLoaded', function() {
    // Star Rating System
    const stars = document.querySelectorAll('#starRating i');
    const ratingInput = document.getElementById('ratingValue');
    
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.getAttribute('data-rating');
            highlightStars(rating);
        });
        
        star.addEventListener('click', function() {
            const rating = this.getAttribute('data-rating');
            ratingInput.value = rating;
            highlightStars(rating);
        });
    });
    
    function highlightStars(rating) {
        stars.forEach(star => {
            const starRating = star.getAttribute('data-rating');
            if (starRating <= rating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill');
                star.style.color = '#FFD700';
            } else {
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star');
                star.style.color = '#ccc';
            }
        });
    }
    
    // Review Form Submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = ratingInput.value;
            if (rating === '0') {
                alert('Please select a rating');
                return;
            }
            
            // Simulate form submission
            showNotification('Thank you for your review! It will be published after moderation.', 'success');
            reviewForm.reset();
            ratingInput.value = '0';
            highlightStars(0);
        });
    }
    
    // Load More Reviews
    const loadMoreBtn = document.getElementById('loadMoreReviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Simulate loading more reviews
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
            this.disabled = true;
            
            setTimeout(() => {
                showNotification('More reviews loaded successfully!', 'success');
                this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Load More Reviews';
                this.disabled = false;
            }, 1500);
        });
    }
    
    // Testimonial Slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.testimonial-dot');
    const totalSlides = slides.length;
    
    function updateSlider() {
        const track = document.querySelector('.testimonial-track');
        if (track) {
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }
    }
    
    // Initialize dots
    if (dots.length > 0) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
        
        // Auto slide every 5 seconds
        setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }, 5000);
    }
    
    function showNotification(message, type) {
        // Remove existing notification
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) existingNotification.remove();
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '8px';
        notification.style.color = 'white';
        notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#c41e3a';
        notification.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
        notification.style.zIndex = '3000';
        notification.style.transition = 'all 0.3s ease';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});


// ===== Menu Page Specific Script =====
document.addEventListener('DOMContentLoaded', function() {
    // Menu Category Navigation
    const categoryButtons = document.querySelectorAll('.menu-category-btn');
    const menuCategories = document.querySelectorAll('.menu-category');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active button
            categoryButtons.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
            });
            this.classList.remove('btn-outline');
            this.classList.add('btn-primary');
            
            // Scroll to category
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offset = 150; // Adjusted for sticky menu
                const targetPosition = targetElement.offsetTop - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Highlight active category on scroll
    function updateActiveCategory() {
        let currentCategory = '';
        
        menuCategories.forEach(category => {
            const categoryTop = category.offsetTop - 200;
            const categoryHeight = category.clientHeight;
            
            if (window.scrollY >= categoryTop && window.scrollY < categoryTop + categoryHeight) {
                currentCategory = category.getAttribute('id');
            }
        });
        
        categoryButtons.forEach(button => {
            const href = button.getAttribute('href');
            if (href === `#${currentCategory}`) {
                button.classList.remove('btn-outline');
                button.classList.add('btn-primary');
            } else {
                button.classList.remove('btn-primary');
                button.classList.add('btn-outline');
            }
        });
    }
    
    // Add scroll event listener
    window.addEventListener('scroll', updateActiveCategory);
    
    // Initialize active category
    updateActiveCategory();
    
    // Order buttons functionality
    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', function() {
            const itemName = this.querySelector('.menu-title').textContent;
            const itemPrice = this.querySelector('.menu-price').textContent;
            
            // Create order button
            const orderBtn = document.createElement('button');
            orderBtn.className = 'btn btn-primary';
            orderBtn.style.width = '100%';
            orderBtn.style.marginTop = '15px';
            orderBtn.innerHTML = `<i class="bi bi-cart-plus"></i> Add to Order`;
            
            orderBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const whatsappMessage = `Hello! I would like to order: ${itemName} - ${itemPrice}`;
                const whatsappUrl = `https://wa.me/971501234567?text=${encodeURIComponent(whatsappMessage)}`;
                window.open(whatsappUrl, '_blank');
            });
            
            // Check if button already exists
            const existingBtn = this.querySelector('.order-btn');
            if (!existingBtn) {
                this.querySelector('.menu-content').appendChild(orderBtn);
            }
        });
    });
});


// ===== Branches Page Specific Script =====
document.addEventListener('DOMContentLoaded', function() {
    // Branch Tab Functionality
    const branchTabBtns = document.querySelectorAll('.branch-tab-btn');
    const branchContents = document.querySelectorAll('.branch-content');
    
    branchTabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const branchId = this.getAttribute('data-branch');
            
            // Update active tab
            branchTabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding content
            branchContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === `${branchId}-content`) {
                    content.classList.add('active');
                }
            });
        });
    });
    
    // Initialize Google Maps (for Al Barsha branch)
    function initMap() {
        // Al Barsha location coordinates
        const alBarshaLocation = { lat: 25.1193, lng: 55.1984 };
        
        if (typeof google !== 'undefined') {
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: alBarshaLocation,
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'geometry',
                        stylers: [{ color: '#f5f1e8' }]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{ color: '#e6dfd3' }]
                    }
                ]
            });
            
            const marker = new google.maps.Marker({
                position: alBarshaLocation,
                map: map,
                title: 'Yalla Al Mandi - Al Barsha Branch',
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            
            // Info window
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h3 style="margin: 0 0 10px 0; color: #c41e3a;">Yalla Al Mandi</h3>
                        <p style="margin: 0 0 5px 0;">Al Barsha, Dubai</p>
                        <p style="margin: 0 0 5px 0;">+971 4 123 4567</p>
                        <a href="https://goo.gl/maps/example" target="_blank" style="color: #c41e3a; text-decoration: none;">
                            Get Directions →
                        </a>
                    </div>
                `
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    }
    
    // Call initMap if Google Maps is loaded
    if (typeof google !== 'undefined') {
        initMap();
    }
    
    // Notify Me Form
    const notifyForm = document.querySelector('#coming-soon-content form');
    if (notifyForm) {
        notifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            
            if (emailInput.value.trim()) {
                // Show success message
                showNotification('Thank you! We\'ll notify you when we open in your area.', 'success');
                emailInput.value = '';
            }
        });
    }
    
    // Franchise Inquiry
    const franchiseBtn = document.querySelector('a[href="contact.html"] .bi-briefcase');
    if (franchiseBtn) {
        franchiseBtn.closest('a').addEventListener('click', function(e) {
            // You could add specific tracking or form pre-filling here
            localStorage.setItem('inquiryType', 'franchise');
        });
    }
    
    function showNotification(message, type) {
        // Remove existing notification
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) existingNotification.remove();
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '8px';
        notification.style.color = 'white';
        notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#c41e3a';
        notification.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
        notification.style.zIndex = '3000';
        notification.style.transition = 'all 0.3s ease';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});


    
// ===== Contact Page Specific Script =====
document.addEventListener('DOMContentLoaded', function() {
    // Tab Functionality
    const tabBtns = document.querySelectorAll('.contact-tab-btn');
    const tabContents = document.querySelectorAll('.contact-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding content
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId || 
                    (tabId === 'contact-info' && content.id === 'contact')) {
                    content.classList.add('active');
                }
            });
            
            // Update URL hash
            if (tabId === 'contact-info') {
                history.replaceState(null, null, '#contact');
            } else {
                history.replaceState(null, null, '#' + tabId);
            }
        });
    });
    
    // Check URL hash on load
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'contact') {
        const tabBtn = document.querySelector(`.contact-tab-btn[data-tab="${hash}"]`);
        if (tabBtn) {
            tabBtn.click();
        }
    }
    
    // Form Submissions
    const reservationForm = document.getElementById('reservationForm');
    const inquiryForm = document.getElementById('inquiryForm');
    const cateringForm = document.getElementById('cateringForm');
    
    // Reservation Form
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (validateForm(this)) {
                // Show success message
                document.getElementById('reservationSuccess').classList.add('active');
                this.style.display = 'none';
            }
        });
    }
    
    // Inquiry Form
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                document.getElementById('inquirySuccess').classList.add('active');
                this.style.display = 'none';
            }
        });
    }
    
    // Catering Form
    if (cateringForm) {
        cateringForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                document.getElementById('cateringSuccess').classList.add('active');
                this.style.display = 'none';
            }
        });
    }
    
    // New Form Buttons
    document.getElementById('newReservationBtn')?.addEventListener('click', function() {
        document.getElementById('reservationSuccess').classList.remove('active');
        reservationForm.style.display = 'block';
        reservationForm.reset();
        window.scrollTo({ top: reservationForm.offsetTop - 100, behavior: 'smooth' });
    });
    
    document.getElementById('newInquiryBtn')?.addEventListener('click', function() {
        document.getElementById('inquirySuccess').classList.remove('active');
        inquiryForm.style.display = 'block';
        inquiryForm.reset();
        window.scrollTo({ top: inquiryForm.offsetTop - 100, behavior: 'smooth' });
    });
    
    document.getElementById('newCateringBtn')?.addEventListener('click', function() {
        document.getElementById('cateringSuccess').classList.remove('active');
        cateringForm.style.display = 'block';
        cateringForm.reset();
        window.scrollTo({ top: cateringForm.offsetTop - 100, behavior: 'smooth' });
    });
    
    // Form Validation
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            input.classList.remove('error');
            
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
                showError(input, 'This field is required');
            } else if (input.type === 'email' && !isValidEmail(input.value)) {
                input.classList.add('error');
                isValid = false;
                showError(input, 'Please enter a valid email address');
            } else if (input.type === 'tel' && !isValidPhone(input.value)) {
                input.classList.add('error');
                isValid = false;
                showError(input, 'Please enter a valid phone number');
            }
        });
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isValidPhone(phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
    
    function showError(input, message) {
        // Remove existing error message
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) existingError.remove();
        
        // Create error message
        const error = document.createElement('div');
        error.className = 'error-message';
        error.style.color = '#c41e3a';
        error.style.fontSize = '0.875rem';
        error.style.marginTop = '5px';
        error.textContent = message;
        
        input.parentNode.appendChild(error);
    }
    
    // Date input restrictions
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
        
        // Set max date to 3 months from today
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        input.max = maxDate.toISOString().split('T')[0];
    });
    
    // Initialize Google Maps
    function initMap() {
        const alBarshaLocation = { lat: 25.1193, lng: 55.1984 };
        
        if (typeof google !== 'undefined') {
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: alBarshaLocation,
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'geometry',
                        stylers: [{ color: '#f5f1e8' }]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            });
            
            const marker = new google.maps.Marker({
                position: alBarshaLocation,
                map: map,
                title: 'Yalla Al Mandi',
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h3 style="margin: 0 0 10px 0; color: #c41e3a;">Yalla Al Mandi</h3>
                        <p style="margin: 0 0 5px 0;">Al Barsha, Dubai</p>
                        <p style="margin: 0 0 5px 0;">+971 4 123 4567</p>
                        <a href="https://goo.gl/maps/example" target="_blank" style="color: #c41e3a; text-decoration: none;">
                            Get Directions →
                        </a>
                    </div>
                `
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    }
    
    if (typeof google !== 'undefined') {
        initMap();
    }
    
    // Remove error on input
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('error');
            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) errorMessage.remove();
        });
    });
});

// ===== SIGNUP PAGE SCRIPT (from signup.php) =====
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    // Password strength checker
    if (passwordInput && strengthFill && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkSignupPasswordStrength(password);
            // Update strength bar
            strengthFill.style.width = strength.score * 25 + '%';
            // Update colors and text
            switch(strength.score) {
                case 1:
                    strengthFill.style.backgroundColor = '#ff4444';
                    strengthText.textContent = 'Password strength: Very weak';
                    break;
                case 2:
                    strengthFill.style.backgroundColor = '#ffbb33';
                    strengthText.textContent = 'Password strength: Weak';
                    break;
                case 3:
                    strengthFill.style.backgroundColor = '#00C851';
                    strengthText.textContent = 'Password strength: Good';
                    break;
                case 4:
                    strengthFill.style.backgroundColor = '#007E33';
                    strengthText.textContent = 'Password strength: Strong';
                    break;
                default:
                    strengthFill.style.backgroundColor = '#e0e0e0';
                    strengthText.textContent = 'Password strength: Very weak';
            }
        });
    }
    function checkSignupPasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return {
            score: Math.min(score, 4),
            max: 4
        };
    }
    // Form validation
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const username = this.querySelector('[name="username"]');
            const email = this.querySelector('[name="email"]');
            const password = this.querySelector('[name="password"]');
            const confirmPassword = this.querySelector('[name="confirm_password"]');
            const terms = this.querySelector('[name="terms"]');
            let isValid = true;
            clearSignupErrors();
            // Validate username
            if (!username.value.trim()) {
                showSignupError(username, 'Username is required');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                showSignupError(username, 'Username can only contain letters, numbers, and underscores');
                isValid = false;
            } else if (username.value.length < 3) {
                showSignupError(username, 'Username must be at least 3 characters');
                isValid = false;
            }
            // Validate email
            if (!email.value.trim()) {
                showSignupError(email, 'Email is required');
                isValid = false;
            } else if (!isValidSignupEmail(email.value)) {
                showSignupError(email, 'Please enter a valid email address');
                isValid = false;
            }
            // Validate password
            if (!password.value.trim()) {
                showSignupError(password, 'Password is required');
                isValid = false;
            } else if (!validateSignupPassword(password.value)) {
                showSignupError(password, 'Password must be at least 8 characters with uppercase, lowercase, and number');
                isValid = false;
            }
            // Validate confirm password
            if (!confirmPassword.value.trim()) {
                showSignupError(confirmPassword, 'Please confirm your password');
                isValid = false;
            } else if (password.value !== confirmPassword.value) {
                showSignupError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }
            // Validate terms
            if (!terms.checked) {
                const termsError = document.createElement('div');
                termsError.className = 'error-message';
                termsError.style.color = '#c41e3a';
                termsError.style.fontSize = '0.875rem';
                termsError.style.marginTop = '10px';
                termsError.textContent = 'You must agree to the Terms of Service';
                terms.parentNode.appendChild(termsError);
                isValid = false;
            }
            if (!isValid) {
                e.preventDefault();
            }
        });
        // Remove error on input
        signupForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMessage = this.parentNode.querySelector('.error-message');
                if (errorMessage) errorMessage.remove();
            });
        });
    }
    // Social signup buttons
    document.querySelectorAll('.btn-outline').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.querySelector('i').className.includes('google') ? 'Google' : 'Facebook';
            alert(`${platform} signup integration would be implemented here.`);
        });
    });
    // Helper functions (scoped to signup page)
    function showSignupError(input, message) {
        input.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#c41e3a';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }
    function clearSignupErrors() {
        document.querySelectorAll('.error').forEach(el => {
            el.classList.remove('error');
        });
        document.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
    }
    function isValidSignupEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    function validateSignupPassword(password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password);
    }
});
// ===== LOGIN PAGE SCRIPT (from login.php) =====
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Client-side validation
            const usernameEmail = this.querySelector('[name="username_email"]');
            const password = this.querySelector('[name="password"]');
            let isValid = true;
            // Clear previous errors
            clearLoginErrors();
            // Validate username/email
            if (!usernameEmail.value.trim()) {
                showLoginError(usernameEmail, 'Username or email is required');
                isValid = false;
            }
            // Validate password
            if (!password.value.trim()) {
                showLoginError(password, 'Password is required');
                isValid = false;
            } else if (password.value.length < 6) {
                showLoginError(password, 'Password must be at least 6 characters');
                isValid = false;
            }
            if (!isValid) {
                e.preventDefault();
            }
        });
        // Remove error on input
        loginForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMessage = this.parentNode.querySelector('.error-message');
                if (errorMessage) errorMessage.remove();
            });
        });
    }
    // Social login buttons
    document.querySelectorAll('.btn-outline').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.querySelector('i').className.includes('google') ? 'Google' : 'Facebook';
            alert(`${platform} login integration would be implemented here.`);
        });
    });
    // Remember me cookie check
    const rememberCookie = getLoginCookie('remember_user');
    if (rememberCookie && document.querySelector('[name="username_email"]')) {
        document.querySelector('[name="username_email"]').value = rememberCookie;
        document.querySelector('[name="remember"]').checked = true;
    }
    // Helper functions (scoped to login page)
    function showLoginError(input, message) {
        input.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#c41e3a';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }
    function clearLoginErrors() {
        document.querySelectorAll('.error').forEach(el => {
            el.classList.remove('error');
        });
        document.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
    }
    function getLoginCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
});
