<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/header.php';
?>

<style>
/* Hero Slider Styles */
.hero {
    position: relative;
    height: 100vh;
    min-height: 600px;
    overflow: hidden;
}

.hero-slider {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
    z-index: 1;
}

.hero-slide.active {
    opacity: 1;
    z-index: 2;
}

.hero-bg {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.7);
}

.hero-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    z-index: 3;
    width: 90%;
    max-width: 800px;
    animation: fadeInUp 1s ease-out;
}

.hero-content h1 {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.hero-content .lead {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.dish-description {
    background: rgba(0,0,0,0.6);
    padding: 1rem;
    border-radius: 10px;
    margin: 1.5rem 0;
    font-size: 1.1rem;
    line-height: 1.6;
    border-left: 4px solid var(--color-red);
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.dish-description i {
    color: var(--color-red);
    margin-right: 10px;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

/* Slider Navigation */
.slider-nav {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 12px;
    z-index: 10;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.slider-dot.active {
    background: var(--color-red);
    transform: scale(1.2);
    border-color: white;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slider-arrow:hover {
    background: var(--color-red);
}

.slider-arrow.prev {
    left: 20px;
}

.slider-arrow.next {
    right: 20px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate(-50%, 20%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.2rem;
    }
    
    .hero-content .lead {
        font-size: 1rem;
    }
    
    .dish-description {
        font-size: 0.95rem;
        padding: 0.75rem;
    }
    
    .slider-arrow {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .slider-arrow.prev {
        left: 10px;
    }
    
    .slider-arrow.next {
        right: 10px;
    }
}

@media (max-width: 480px) {
    .hero-content h1 {
        font-size: 1.8rem;
    }
    
    .hero-buttons .btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
}
</style>

<!-- ===== HERO SLIDER SECTION ===== -->
<section class="hero">
    <div class="hero-slider">
        <!-- Slide 1: Chicken Mandi -->
            <div class="hero-slide active" data-slide="1">
                <img src="resources/img/menu_items/chicken_mandi.jpg" 
                     alt="Chicken Mandi" 
                     class="hero-bg">
            <div class="hero-content">
                <h1>Authentic Chicken Mandi</h1>
                <p class="lead">Our signature dish, loved by thousands</p>
                <div class="dish-description">
                    <i class="bi bi-quote"></i>
                    Tender chicken marinated in traditional Yemani spices, slow-cooked to perfection over charcoal, served with fragrant basmati rice infused with saffron and topped with caramelized onions and nuts. A true taste of Yemen that melts in your mouth.
                </div>
                <div class="hero-buttons">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-menu-button"></i> View Full Menu
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book a Table
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Slide 2: Mutton Mandi -->
            <div class="hero-slide" data-slide="2">
                <img src="resources/img/menu_items/mutton_mandi.jpg" 
                     alt="Mutton Mandi" 
                     class="hero-bg">
            <div class="hero-content">
                <h1>Succulent Mutton Mandi</h1>
                <p class="lead">Slow-cooked to perfection</p>
                <div class="dish-description">
                    <i class="bi bi-quote"></i>
                    Premium mutton pieces marinated in a secret blend of Yemani spices, cooked in a traditional tandoor until fork-tender. Served with aromatic mandi rice, roasted nuts, and a side of tangy tomato salsa. Each bite is a journey through Yemen's culinary heritage.
                </div>
                <div class="hero-buttons">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-menu-button"></i> View Full Menu
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book a Table
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Slide 3: Chicken Madhbi -->
            <div class="hero-slide" data-slide="3">
                <img src="resources/img/menu_items/chicken_madhbi.jpg" 
                     alt="Chicken Madhbi" 
                     class="hero-bg">
            <div class="hero-content">
                <h1>Traditional Chicken Madhbi</h1>
                <p class="lead">Grilled to smoky perfection</p>
                <div class="dish-description">
                    <i class="bi bi-quote"></i>
                    Whole chicken split and pressed on hot stones, grilled over charcoal until the skin is crispy and the meat is juicy. Marinated with a unique blend of Yemani spices and served with flatbread, fresh salad, and our signature garlic sauce. A rustic favorite!
                </div>
                <div class="hero-buttons">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-menu-button"></i> View Full Menu
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book a Table
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Slide 4: Mutton Madhbi -->
            <div class="hero-slide" data-slide="4">
                <img src="resources/img/menu_items/mutton_madhbi.jpg" 
                     alt="Mutton Madhbi" 
                     class="hero-bg">
            <div class="hero-content">
                <h1>Signature Mutton Madhbi</h1>
                <p class="lead">A true Yemani delicacy</p>
                <div class="dish-description">
                    <i class="bi bi-quote"></i>
                    Tender mutton chops pressed and grilled on hot stones, locking in all the natural juices and smoky flavors. Marinated with traditional Yemani spices and served with fragrant rice, grilled tomatoes, and a side of tangy yogurt sauce. An unforgettable culinary experience.
                </div>
                <div class="hero-buttons">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-menu-button"></i> View Full Menu
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book a Table
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Slider Navigation Arrows -->
    <button class="slider-arrow prev" onclick="changeSlide(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="slider-arrow next" onclick="changeSlide(1)">
        <i class="bi bi-chevron-right"></i>
    </button>
    
    <!-- Slider Dots -->
    <div class="slider-nav">
        <span class="slider-dot active" onclick="currentSlide(1)"></span>
        <span class="slider-dot" onclick="currentSlide(2)"></span>
        <span class="slider-dot" onclick="currentSlide(3)"></span>
        <span class="slider-dot" onclick="currentSlide(4)"></span>
    </div>
</section>

<script>
let slideIndex = 1;
let autoSlideInterval;

// Initialize slider
function showSlides(n) {
    const slides = document.getElementsByClassName("hero-slide");
    const dots = document.getElementsByClassName("slider-dot");
    
    if (n > slides.length) { slideIndex = 1; }
    if (n < 1) { slideIndex = slides.length; }
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
    }
    
    // Remove active class from all dots
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Show current slide and activate current dot
    slides[slideIndex - 1].classList.add("active");
    dots[slideIndex - 1].classList.add("active");
    
    // Reset auto-slide timer
    resetAutoSlide();
}

function changeSlide(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Auto-slide function
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000); // Change slide every 5 seconds
}

function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

// Pause auto-slide when hovering over slider
document.querySelector('.hero').addEventListener('mouseenter', () => {
    clearInterval(autoSlideInterval);
});

document.querySelector('.hero').addEventListener('mouseleave', () => {
    startAutoSlide();
});

// Start auto-slide when page loads
document.addEventListener('DOMContentLoaded', () => {
    startAutoSlide();
});

// Touch support for mobile
let touchStartX = 0;
let touchEndX = 0;

document.querySelector('.hero').addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.querySelector('.hero').addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    if (touchEndX < touchStartX - swipeThreshold) {
        // Swipe left - next slide
        changeSlide(1);
    }
    if (touchEndX > touchStartX + swipeThreshold) {
        // Swipe right - previous slide
        changeSlide(-1);
    }
}
</script>

    <!-- ===== BRAND INTRODUCTION ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Our Story</span>
                <h2 class="display-2">Welcome to Yalla Al Mandi</h2>
            </div>
            
            <div class="row" style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                <div class="col" style="flex: 1; min-width: 300px;">
                    <p style="font-size: 1.1rem; margin-bottom: 20px;">
                        Founded with a passion for authentic Yemani cuisine, Yalla Al Mandi brings generations of culinary tradition to your table. Our journey began with the desire of Mr. Zayed to share the traditional aroma of Yemani dishes with friends and the world.
                    </p>
                    <p style="font-size: 1.1rem; margin-bottom: 20px;">
                        Today, we continue this legacy by blending traditional cooking methods with modern presentation, creating an unforgettable dining experience that honors our heritage while embracing contemporary tastes.
                    </p>
                    <p style="font-size: 1.1rem; margin-bottom: 30px;">
                        Every dish is prepared with the finest ingredients, cooked slowly to perfection, and served with the warmth of Yemani hospitality.
                    </p>
                    <a href="about.php" class="btn btn-primary">Learn Our Story</a>
                </div>
                
                <div class="col" style="flex: 1; min-width: 300px;">
                    <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Yemani Cuisine Preparation" 
                         style="border-radius: var(--border-radius); box-shadow: var(--box-shadow); width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SIGNATURE DISHES PREVIEW ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Our Specialties</span>
                <h2 class="display-2">Signature Dishes</h2>
                <p class="lead">Taste our most beloved creations, crafted with generations of expertise.</p>
            </div>
            
                <?php
                // Enhanced: Use existing featured dishes code, but with improved image and description handling
                if (isset($connection) && $connection):
                    $featured_query = "SELECT mi.*, mc.name as category_name 
                                    FROM menu_items mi 
                                    LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
                                    WHERE mi.is_featured = 1 AND mi.is_available = 1 
                                    ORDER BY mi.created_at DESC 
                                    LIMIT 3";
                    $featured_result = $connection->query($featured_query);
                    if ($featured_result && $featured_result->num_rows > 0):
                    ?>
                        <div class="menu-grid">
                            <?php while ($dish = $featured_result->fetch_assoc()): 
                                $dish_image = !empty($dish['image_url']) ? 'uploads/menu/' . htmlspecialchars($dish['image_url']) : 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                                $short_description = !empty($dish['description']) ? (strlen($dish['description']) > 120 ? substr($dish['description'], 0, 120) . '...' : $dish['description']) : 'A delicious signature dish from our kitchen.';?>
                                <div class="menu-card">
                                    <img src="<?php echo $dish_image; ?>" 
                                         alt="<?php echo htmlspecialchars($dish['name']); ?>" 
                                         class="menu-img"
                                         loading="lazy">
                                    <div class="menu-content">
                                        <span class="menu-tag">Signature</span>
                                        <div class="menu-header">
                                            <h3 class="menu-title"><?php echo htmlspecialchars($dish['name']); ?></h3>
                                            <span class="menu-price">AED <?php echo number_format($dish['price'], 2); ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars($short_description); ?></p>
                                        <?php if (!empty($dish['category_name'])): ?>
                                            <div class="menu-footer">
                                                <small class="text-muted">
                                                    <i class="bi bi-tag me-1"></i>
                                                    <?php echo htmlspecialchars($dish['category_name']); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="text-center" style="margin-top: 40px;">
                            <a href="menu.php" class="btn btn-secondary btn-lg">View Full Menu <i class="bi bi-arrow-right"></i></a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-egg-fried display-1 text-muted"></i>
                            </div>
                            <h4 class="mb-3">No Featured Dishes Available</h4>
                            <p class="text-muted mb-4">Check back soon for our signature creations!</p>
                            <a href="menu.php" class="btn btn-secondary btn-lg">View Full Menu <i class="bi bi-arrow-right"></i></a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-database-exclamation display-1 text-muted"></i>
                        </div>
                        <h4 class="mb-3">Featured Dishes Temporarily Unavailable</h4>
                        <p class="text-muted mb-4">Please visit our full menu to see all our delicious dishes.</p>
                        <a href="menu.php" class="btn btn-secondary btn-lg">View Full Menu <i class="bi bi-arrow-right"></i></a>
                    </div>
                <?php endif; ?>
        </div>
    </section>

    <!-- ===== WHY CHOOSE US ===== -->
    <section class="section-padding" style="background-color: var(--color-light-gray);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Excellence</span>
                <h2 class="display-2">Why Choose Yalla Al Mandi</h2>
            </div>
            
            <div class="feature-grid">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h3>Authentic Recipes</h3>
                    <p>Generations-old Yemani recipes preserved and perfected for authentic flavors.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h3>Family-Friendly</h3>
                    <p>Warm atmosphere perfect for family gatherings and celebrations of all sizes.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <h3>Premium Quality</h3>
                    <p>Only the finest ingredients sourced from trusted suppliers for exceptional taste.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Warm Hospitality</h3>
                    <p>Traditional Yemani hospitality that makes every guest feel like family.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BRANCHES HIGHLIGHT ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="row" style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
                <div class="col" style="flex: 1; min-width: 300px;">
                    <img src="resources/img/restaurant_dining/flagship_branch.jpg" 
                         alt="Restaurant Interior" 
                         style="border-radius: var(--border-radius); box-shadow: var(--box-shadow); width: 100%; height: auto;">
                </div>
                
                <div class="col" style="flex: 1; min-width: 300px;">
                    <span class="section-subtitle">Visit Us</span>
                    <h2 class="display-3 mb-4">Experience Our Flagship Branch</h2>
                    
                    <div class="branch-info">
                        <div class="info-item">
                            <i class="bi bi-geo-alt info-icon"></i>
                            <div>
                                <strong>Address</strong>
                                <p>Shop No.:00 Royal Class Building, Dubai Investment Park 1, Green Community Village, Dubai.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="bi bi-clock info-icon"></i>
                            <div>
                                <strong>Opening Hours</strong>
                                <p>Daily: 11:00 AM - 12:00 AM<br>Weekends: 12:00 PM - 1:00 AM</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="bi bi-telephone info-icon"></i>
                            <div>
                                <strong>Contact</strong>
                                <p>+971 50 375 7274<br>+971 50 375 7274 (WhatsApp)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 30px;">
                        <a href="branches.php" class="btn btn-primary">View All Branches</a>
                        <a href="contact.php" class="btn btn-secondary">Get Directions</a>
                        <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background-color: var(--color-sand); border-radius: var(--border-radius);">
                        <p style="margin: 0; font-weight: 500;">
                            <i class="bi bi-info-circle" style="color: var(--color-olive);"></i>
                            <span style="color: var(--color-olive);"> Coming Soon:</span> New branches opening in Abu Dhabi and Sharjah
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS TEASER ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Testimonials</span>
                <h2 class="display-2">What Our Guests Say</h2>
            </div>
            
            <div class="testimonial-slider">
                <div class="testimonial-track">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-slide">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "The most authentic Yemani Mandi I've had outside of Syria. The flavors take me back to my childhood. The hospitality is exceptional!"
                        </p>
                        <p class="testimonial-author">- Ahmed Al Hassan</p>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial-slide">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p class="testimonial-text">
                            "Perfect for family dinners. The kids love the kids' menu, and we appreciate the authentic flavors. Our go-to restaurant for special occasions."
                        </p>
                        <p class="testimonial-author">- Fatima Rahman</p>
                    </div>
                </div>
                
                <div class="testimonial-nav">
                    <div class="testimonial-dot active"></div>
                    <div class="testimonial-dot"></div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="testimonials.php" class="btn btn-outline">Read More Reviews</a>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CALL TO ACTION ===== -->
    <section class="section-padding" style="background: linear-gradient(135deg, var(--color-dark-brown) 0%, var(--color-soft-black) 100%); color: white;">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4" style="color: white;">Ready for an Authentic Experience?</h2>
                <p class="lead mb-5" style="opacity: 0.9;">
                    Join us for a memorable dining experience that combines authentic Yemani flavors with warm hospitality.
                </p>
                
                <div class="hero-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-check"></i> Reserve Your Table
                    </a>
                    <a href="tel:+971503757274" class="btn btn-secondary btn-lg">
                        <i class="bi bi-telephone"></i> Call to Order
                    </a>
                    <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp btn-lg">
                        <i class="bi bi-whatsapp"></i> WhatsApp Us
                    </a>
                </div>
                
                <div style="margin-top: 40px; display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <i class="bi bi-clock" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                        <h4 style="color: white;">Open Daily</h4>
                        <p style="opacity: 0.8;">12 PM - 12 AM</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-geo-alt" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                        <h4 style="color: white;">Multiple Locations</h4>
                        <p style="opacity: 0.8;">Dubai & Coming Soon</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-truck" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                        <h4 style="color: white;">Delivery Available</h4>
                        <p style="opacity: 0.8;">Through All Platforms</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?php
    include 'includes/footer.php';
    ?>