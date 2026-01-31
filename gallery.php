<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Restaurant Interior" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Our Gallery</h1>
            <p class="lead">A visual journey through our authentic Yemani cuisine, warm ambiance, and memorable moments.</p>
        </div>
    </section>

    <!-- ===== GALLERY FILTER ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">Experience Yalla Al Mandhi</h2>
                <p class="lead">Browse through our collection of culinary creations, restaurant spaces, and happy gatherings.</p>
            </div>
            
            <!-- Filter Buttons -->
            <div class="text-center mb-5">
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px;">
                    <button class="btn btn-primary filter-btn" data-filter="all">All Photos</button>
                    <button class="btn btn-outline filter-btn" data-filter="food">Food & Dishes</button>
                    <button class="btn btn-outline filter-btn" data-filter="restaurant">Restaurant</button>
                    <button class="btn btn-outline filter-btn" data-filter="events">Events & Gatherings</button>
                    <button class="btn btn-outline filter-btn" data-filter="people">People & Moments</button>
                </div>
            </div>
            
            <!-- Gallery Grid -->
            <div class="gallery-grid">
                <!-- Food Category -->
                <div class="gallery-item" data-category="food">
                    <img src="https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Traditional Mandhi Dish">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Signature Mandhi</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="food">
                    <img src="https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Mixed Grill Platter">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Mixed Grill Platter</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="food">
                    <img src="https://images.unsplash.com/photo-1586190848861-99aa4a171e90?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Kabsa Rice">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Chicken Kabsa</p>
                        </div>
                    </div>
                </div>
                
                <!-- Restaurant Category -->
                <div class="gallery-item" data-category="restaurant">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Restaurant Interior">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Main Dining Area</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="restaurant">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Restaurant Exterior">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Restaurant Entrance</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="restaurant">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Kitchen Area">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Open Kitchen</p>
                        </div>
                    </div>
                </div>
                
                <!-- Events Category -->
                <div class="gallery-item" data-category="events">
                    <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Family Gathering">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Family Celebration</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="events">
                    <img src="https://images.unsplash.com/photo-1554672408-730436b60dde?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Cooking Event">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Cooking Demonstration</p>
                        </div>
                    </div>
                </div>
                
                <!-- People Category -->
                <div class="gallery-item" data-category="people">
                    <img src="https://images.unsplash.com/photo-1578474846511-04ba529f0b88?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Chef Preparing Food">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Chef at Work</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="people">
                    <img src="https://images.unsplash.com/photo-1519709042477-8de6eaf1fdc5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Happy Customers">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Happy Customers</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="food">
                    <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Appetizers">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Appetizer Selection</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-item" data-category="restaurant">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Private Dining">
                    <div class="gallery-overlay">
                        <div style="text-align: center; color: white;">
                            <i class="bi bi-zoom-in" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Private Dining Area</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Statistics -->
            <div style="margin-top: 60px; padding: 40px; background-color: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <div class="text-center">
                    <h3 class="display-3 mb-4">Capturing Memories Since 2015</h3>
                    <p class="lead mb-5">Every photo tells a story of authentic flavors and warm hospitality.</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 30px;">
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-red); margin-bottom: 10px;">500+</div>
                            <p style="font-weight: 500; color: var(--color-dark-brown);">Dish Photos</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-olive); margin-bottom: 10px;">200+</div>
                            <p style="font-weight: 500; color: var(--color-dark-brown);">Events Captured</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-copper); margin-bottom: 10px;">50K+</div>
                            <p style="font-weight: 500; color: var(--color-dark-brown);">Social Media Views</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--color-dark-brown); margin-bottom: 10px;">9+</div>
                            <p style="font-weight: 500; color: var(--color-dark-brown);">Years of Memories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA SECTION ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4">See It Live, Taste It Fresh</h2>
                <p class="lead mb-5">
                    The gallery is just a preview. Experience the real thing at our restaurant.
                </p>
                
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-menu-button"></i> View Our Menu
                    </a>
                    <a href="contact.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book Your Visit
                    </a>
                </div>
                
                <div style="margin-top: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <h4 class="mb-3">Follow Our Latest Updates</h4>
                    <p style="margin-bottom: 20px; opacity: 0.8;">See more photos and behind-the-scenes moments on our social media.</p>
                    <div class="social-links" style="justify-content: center;">
                        <a href="#" class="social-icon" style="background-color: #1877F2;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon" style="background: linear-gradient(45deg, #405DE6, #833AB4, #C13584, #E1306C, #FD1D1D, #F56040, #FFDC80);">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-icon" style="background-color: #1DA1F2;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-icon" style="background-color: #FF0000;">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?php
    include 'includes/footer.php';
    ?>