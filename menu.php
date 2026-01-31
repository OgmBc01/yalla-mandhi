<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Traditional Yemani Mandhi" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Our Menu</h1>
            <p class="lead">Authentic Yemani flavors crafted with generations of culinary expertise.</p>
            <div style="margin-top: 30px;">
                <a href="#order" class="btn btn-primary btn-lg">
                    <i class="bi bi-whatsapp"></i> Order Now
                </a>
                <a href="contact.html" class="btn btn-secondary btn-lg">
                    <i class="bi bi-calendar-check"></i> Reserve Table
                </a>
            </div>
        </div>
    </section>

    <!-- ===== MENU CATEGORIES NAVIGATION ===== -->
    <section class="section-padding" style="background-color: var(--color-beige); position: sticky; top: 70px; z-index: 100; padding: 20px 0;">
        <div class="container">
            <div class="text-center mb-3">
                <h2 class="display-3" style="margin-bottom: 0; font-size: 1.5rem;">Menu Categories</h2>
            </div>
            
            <div class="menu-category-nav">
                <a href="#signature-mandhi" class="btn btn-outline menu-category-btn active" data-category="signature">
                    <i class="bi bi-star-fill"></i> Signature Mandhi
                </a>
                <a href="#grills-bbq" class="btn btn-outline menu-category-btn" data-category="grills">
                    <i class="bi bi-fire"></i> Grills & BBQ
                </a>
                <a href="#rice-dishes" class="btn btn-outline menu-category-btn" data-category="rice">
                    <i class="bi bi-egg-fried"></i> Rice Dishes
                </a>
                <a href="#appetizers" class="btn btn-outline menu-category-btn" data-category="appetizers">
                    <i class="bi bi-basket"></i> Appetizers
                </a>
                <a href="#family-platters" class="btn btn-outline menu-category-btn" data-category="family">
                    <i class="bi bi-people"></i> Family Platters
                </a>
                <a href="#kids-menu" class="btn btn-outline menu-category-btn" data-category="kids">
                    <i class="bi bi-emoji-smile"></i> Kids Menu
                </a>
                <a href="#beverages" class="btn btn-outline menu-category-btn" data-category="beverages">
                    <i class="bi bi-cup-straw"></i> Beverages
                </a>
                <a href="#desserts" class="btn btn-outline menu-category-btn" data-category="desserts">
                    <i class="bi bi-cake2"></i> Desserts
                </a>
            </div>
        </div>
    </section>

    <!-- ===== SIGNATURE MANDHI ===== -->
    <section id="signature-mandhi" class="section-padding menu-category">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Our Specialty</span>
                <h2 class="display-2">Signature Mandhi</h2>
                <p class="lead">The heart of Yemani cuisine - slow-cooked to perfection with authentic spices</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Traditional Lamb Mandhi" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Chef's Special</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Traditional Lamb Mandhi</h3>
                            <span class="menu-price">AED 85</span>
                        </div>
                        <p>Slow-cooked tender lamb with aromatic rice, infused with traditional Middle Eastern spices and served with special sauce.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-clock"></i> 45 min preparation
                        </div>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Chicken Mandhi" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Popular</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Chicken Mandhi</h3>
                            <span class="menu-price">AED 65</span>
                        </div>
                        <p>Juicy chicken marinated in Yemani spices, cooked with fragrant basmati rice, nuts, and raisins.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-clock"></i> 35 min preparation
                        </div>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1586190848861-99aa4a171e90?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Vegetable Mandhi" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Vegetarian</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Vegetable Mandhi</h3>
                            <span class="menu-price">AED 55</span>
                        </div>
                        <p>Assorted seasonal vegetables cooked with aromatic rice and traditional Yemani spices.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-clock"></i> 25 min preparation
                        </div>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Mixed Mandhi Platter" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Family Favorite</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Mixed Mandhi Platter</h3>
                            <span class="menu-price">AED 120</span>
                        </div>
                        <p>Combination of lamb and chicken mandhi with assorted grilled vegetables and special sauces.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> Serves 2-3
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== GRILLS & BBQ ===== -->
    <section id="grills-bbq" class="section-padding menu-category" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Grilled to Perfection</span>
                <h2 class="display-2">Grills & BBQ</h2>
                <p class="lead">Freshly grilled meats and vegetables with authentic Yemani marinades</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1546833999-b9f581a1996d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Mixed Grill Platter" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Signature</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Mixed Grill Platter</h3>
                            <span class="menu-price">AED 95</span>
                        </div>
                        <p>Assortment of grilled lamb chops, chicken tikka, kofta, and shish tawook served with grilled vegetables.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> Serves 2
                        </div>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Shish Tawook" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Shish Tawook</h3>
                            <span class="menu-price">AED 55</span>
                        </div>
                        <p>Marinated chicken chunks grilled on skewers with peppers and onions, served with garlic sauce.</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1603360946369-dc9bb6258143?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Lamb Chops" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Grilled Lamb Chops</h3>
                            <span class="menu-price">AED 75</span>
                        </div>
                        <p>Tender lamb chops marinated in Yemani spices, grilled to perfection with fresh herbs.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            3 pieces
                        </div>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1594041680534-e8c8cdebd659?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Kofta Kebab" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Kofta Kebab</h3>
                            <span class="menu-price">AED 50</span>
                        </div>
                        <p>Minced meat mixed with herbs and spices, shaped and grilled on skewers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TRADITIONAL RICE DISHES ===== -->
    <section id="rice-dishes" class="section-padding menu-category">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Fragrant & Flavorful</span>
                <h2 class="display-2">Traditional Rice Dishes</h2>
                <p class="lead">Aromatic rice dishes that are the cornerstone of Yemani cuisine</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1586190848861-99aa4a171e90?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Chicken Kabsa" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Signature</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Chicken Kabsa</h3>
                            <span class="menu-price">AED 65</span>
                        </div>
                        <p>Fragrant rice with tender chicken, nuts, raisins, and authentic Arabic spices.</p>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Mandi Rice" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Mandi Rice with Meat</h3>
                            <span class="menu-price">AED 70</span>
                        </div>
                        <p>Traditional Yemeni-style rice with slow-cooked meat, flavored with authentic spices.</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Vegetable Biryani</h3>
                            <span class="menu-price">AED 45</span>
                        </div>
                        <p>Fragrant basmati rice cooked with assorted vegetables and mild Yemani spices.</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">White Rice with Nuts</h3>
                            <span class="menu-price">AED 35</span>
                        </div>
                        <p>Steamed basmati rice topped with almonds, pine nuts, and raisins.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== APPETIZERS & STARTERS ===== -->
    <section id="appetizers" class="section-padding menu-category" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Start Your Meal</span>
                <h2 class="display-2">Appetizers & Starters</h2>
                <p class="lead">Traditional Yemani starters to awaken your taste buds</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Hummus" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Homemade Hummus</h3>
                            <span class="menu-price">AED 25</span>
                        </div>
                        <p>Creamy chickpea dip with tahini, lemon juice, and garlic, served with fresh bread.</p>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1598214886806-c87b84b7078b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Fattoush Salad" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Fattoush Salad</h3>
                            <span class="menu-price">AED 28</span>
                        </div>
                        <p>Fresh mixed vegetables with crispy bread, mint, and pomegranate dressing.</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Mutabbal</h3>
                            <span class="menu-price">AED 28</span>
                        </div>
                        <p>Smoky eggplant dip with tahini, yogurt, and garlic.</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Kibbeh</h3>
                            <span class="menu-price">AED 32</span>
                        </div>
                        <p>Fried bulgur shells stuffed with minced meat and pine nuts (4 pieces).</p>
                    </div>
                </div>
                
                <!-- Item 5 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Sambousek</h3>
                            <span class="menu-price">AED 30</span>
                        </div>
                        <p>Crispy pastry triangles stuffed with cheese or meat (6 pieces).</p>
                    </div>
                </div>
                
                <!-- Item 6 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Appetizer Platter</h3>
                            <span class="menu-price">AED 55</span>
                        </div>
                        <p>Assortment of hummus, mutabbal, fattoush, and fresh bread.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> For sharing
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FAMILY PLATTERS ===== -->
    <section id="family-platters" class="section-padding menu-category">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Perfect for Sharing</span>
                <h2 class="display-2">Family Platters</h2>
                <p class="lead">Generous portions designed for family gatherings and celebrations</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Family Feast" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Most Popular</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Family Feast Platter</h3>
                            <span class="menu-price">AED 180</span>
                        </div>
                        <p>Includes lamb mandhi, chicken mandhi, mixed grill, appetizers, rice, salads, and bread for 4-6 people.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> Serves 4-6
                        </div>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <span class="menu-tag">Great Value</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Weekend Family Package</h3>
                            <span class="menu-price">AED 220</span>
                        </div>
                        <p>Complete family meal with starters, mains, desserts, and beverages for 5-7 people.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> Serves 5-7
                        </div>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Grill Lovers Platter</h3>
                            <span class="menu-price">AED 160</span>
                        </div>
                        <p>Assorted grilled meats, vegetables, rice, and sauces for 3-4 people.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> Serves 3-4
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== KIDS MENU ===== -->
    <section id="kids-menu" class="section-padding menu-category" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Little Chefs</span>
                <h2 class="display-2">Kids Menu</h2>
                <p class="lead">Specially designed meals for our younger guests</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Chicken Nuggets & Fries</h3>
                            <span class="menu-price">AED 35</span>
                        </div>
                        <p>Crispy chicken nuggets served with french fries and ketchup.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-emoji-smile"></i> Includes juice box
                        </div>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Mini Chicken Mandhi</h3>
                            <span class="menu-price">AED 40</span>
                        </div>
                        <p>Small portion of our signature chicken mandhi, mild spice level.</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Pasta with Cheese</h3>
                            <span class="menu-price">AED 30</span>
                        </div>
                        <p>Creamy cheese pasta with mild seasoning.</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Kids Combo Meal</h3>
                            <span class="menu-price">AED 45</span>
                        </div>
                        <p>Choice of main, fries, juice, and a small dessert.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BEVERAGES ===== -->
    <section id="beverages" class="section-padding menu-category">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Refreshments</span>
                <h2 class="display-2">Beverages</h2>
                <p class="lead">Traditional and modern drinks to complement your meal</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <!-- Hot Beverages -->
                    <div>
                        <h3 style="color: var(--color-dark-brown); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--color-red);">Hot Beverages</h3>
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Arabic Coffee</span>
                                <span style="font-weight: 600;">AED 15</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Turkish Coffee</span>
                                <span style="font-weight: 600;">AED 15</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Tea with Mint</span>
                                <span style="font-weight: 600;">AED 12</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Karaki Tea</span>
                                <span style="font-weight: 600;">AED 18</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cold Beverages -->
                    <div>
                        <h3 style="color: var(--color-dark-brown); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--color-red);">Cold Beverages</h3>
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Fresh Lemon Mint</span>
                                <span style="font-weight: 600;">AED 18</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Jallab</span>
                                <span style="font-weight: 600;">AED 20</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Tamarind Juice</span>
                                <span style="font-weight: 600;">AED 18</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Soft Drinks</span>
                                <span style="font-weight: 600;">AED 10</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== DESSERTS ===== -->
    <section id="desserts" class="section-padding menu-category" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Sweet Endings</span>
                <h2 class="display-2">Desserts</h2>
                <p class="lead">Traditional Yemani sweets to complete your dining experience</p>
            </div>
            
            <div class="menu-grid">
                <!-- Item 1 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Baklava" 
                         class="menu-img">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Baklava Assortment</h3>
                            <span class="menu-price">AED 35</span>
                        </div>
                        <p>Assorted baklava pieces with pistachio, walnut, and cashew fillings.</p>
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="menu-card">
                    <img src="https://images.unsplash.com/photo-1558326567-98ae2405596b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Kunafa" 
                         class="menu-img">
                    <div class="menu-content">
                        <span class="menu-tag">Signature</span>
                        <div class="menu-header">
                            <h3 class="menu-title">Kunafa with Cheese</h3>
                            <span class="menu-price">AED 40</span>
                        </div>
                        <p>Traditional Yemani dessert with cheese filling, crispy pastry, and sugar syrup.</p>
                    </div>
                </div>
                
                <!-- Item 3 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Umm Ali</h3>
                            <span class="menu-price">AED 30</span>
                        </div>
                        <p>Traditional Middle Eastern bread pudding with nuts and raisins.</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="menu-card">
                    <div class="menu-content">
                        <div class="menu-header">
                            <h3 class="menu-title">Dessert Platter</h3>
                            <span class="menu-price">AED 65</span>
                        </div>
                        <p>Assortment of baklava, kunafa, and umm ali for sharing.</p>
                        <div style="margin-top: 15px; font-size: 0.9rem; color: var(--color-olive);">
                            <i class="bi bi-people"></i> For 2-3 people
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ORDERING CTA ===== -->
    <section id="order" class="section-padding" style="background: linear-gradient(135deg, var(--color-dark-brown) 0%, var(--color-soft-black) 100%); color: white;">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4" style="color: white;">Ready to Order?</h2>
                <p class="lead mb-5" style="opacity: 0.9;">
                    Place your order now through WhatsApp or call us directly. Delivery available in select areas.
                </p>
                
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px;">
                    <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp btn-lg">
                        <i class="bi bi-whatsapp"></i> Order on WhatsApp
                    </a>
                    <a href="tel:+971503757274" class="btn btn-secondary btn-lg">
                        <i class="bi bi-telephone"></i> Call to Order
                    </a>
                    <a href="contact.html" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                        <i class="bi bi-calendar-check"></i> Dine-In Reservation
                    </a>
                </div>
                
                <div style="background-color: rgba(255, 255, 255, 0.1); padding: 30px; border-radius: var(--border-radius); max-width: 800px; margin: 0 auto;">
                    <h4 style="color: white; margin-bottom: 20px;">Delivery Information</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div style="text-align: center;">
                            <i class="bi bi-clock" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="margin-bottom: 5px; font-weight: 600;">Delivery Time</p>
                            <p style="opacity: 0.8;">45-60 minutes</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-geo-alt" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="margin-bottom: 5px; font-weight: 600;">Delivery Areas</p>
                            <p style="opacity: 0.8;">Al Barsha, Dubai Marina, JLT</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-truck" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="margin-bottom: 5px; font-weight: 600;">Delivery Charge</p>
                            <p style="opacity: 0.8;">AED 15 (Free above AED 100)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?
    include 'includes/footer.php';
    ?>