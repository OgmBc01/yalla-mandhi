<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/header.php';
?>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Traditional Yemani Mandi" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Authentic Yemani Mandi</h1>
            <p class="lead">Experience the rich flavors of traditional Yemani cuisine in a warm, family-friendly atmosphere. Where every meal tells a story.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-menu-button"></i> View Our Menu
                </a>
                <a href="contact.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-calendar-check"></i> Book a Table
                </a>
                <a href="tel:+971503757274" class="btn btn-outline btn-lg">
                    <i class="bi bi-telephone"></i> Call Now
                </a>
            </div>
        </div>
    </section>

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
        // Check if we have a database connection
        if ($connection):
            // Fetch featured menu items (3 most recent featured dishes)
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
                        // Determine image source
                        if (!empty($dish['image_url'])) {
                            // Use web root relative path for images
                            $dish_image = 'uploads/menu/' . htmlspecialchars($dish['image_url']);
                            // Check if file exists, otherwise use fallback
                            if (!file_exists($dish_image)) {
                                $dish_image = 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                            }
                        } else {
                            // Fallback image
                            $dish_image = 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                        }
                        
                        // Format price
                        $formatted_price = 'AED ' . number_format($dish['price'], 2);
                        
                        // Shorten description if too long
                        $short_description = !empty($dish['description']) ? 
                            (strlen($dish['description']) > 120 
                                ? substr($dish['description'], 0, 120) . '...' 
                                : $dish['description']) 
                            : 'A delicious signature dish from our kitchen.';
                    ?>
                        <div class="menu-card">
                            <img src="<?php echo $dish_image; ?>" 
                                 alt="<?php echo htmlspecialchars($dish['name']); ?>" 
                                 class="menu-img"
                                 loading="lazy"
                                 onerror="this.src='https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            <div class="menu-content">
                                <span class="menu-tag">Signature</span>
                                <div class="menu-header">
                                    <h3 class="menu-title"><?php echo htmlspecialchars($dish['name']); ?></h3>
                                    <span class="menu-price"><?php echo $formatted_price; ?></span>
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
                
                <div class="text-center mt-5">
                    <a href="menu.php" class="btn btn-secondary btn-lg">View Full Menu <i class="bi bi-arrow-right"></i></a>
                </div>
            <?php else: ?>
                <!-- Fallback if no featured dishes found -->
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
            <!-- Fallback if database connection failed -->
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
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
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