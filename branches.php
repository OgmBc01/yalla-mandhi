<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Restaurant Exterior" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Our Branches</h1>
            <p class="lead">Find your nearest Yalla Al Mandhi for an authentic Yemani dining experience.</p>
            <div style="margin-top: 30px;">
                <a href="#locations" class="btn btn-primary btn-lg">
                    <i class="bi bi-geo-alt"></i> View Locations
                </a>
                <a href="contact.html" class="btn btn-secondary btn-lg">
                    <i class="bi bi-calendar-check"></i> Reserve Table
                </a>
            </div>
        </div>
    </section>

    <!-- ===== BRANCHES TABS ===== -->
    <section id="locations" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Our Locations</span>
                <h2 class="display-2">Find Us Near You</h2>
                <p class="lead">Visit any of our branches for the same authentic Yemani flavors and warm hospitality</p>
            </div>
            
            <!-- Branch Tabs -->
            <div class="branch-tab">
                <div class="branch-tab-btn active" data-branch="al-barsha">
                    <i class="bi bi-star-fill"></i> Al Barsha (Flagship)
                </div>
                <div class="branch-tab-btn" data-branch="coming-soon">
                    <i class="bi bi-clock"></i> Coming Soon
                </div>
            </div>
            
            <!-- Al Barsha Branch Content -->
            <div class="branch-content active" id="al-barsha-content">
                <div class="branch-highlight">
                    <span class="section-subtitle" style="display: block; margin-bottom: 10px;">Flagship Branch</span>
                    <h2 class="display-3">Dubai Investment Park (DIP), Dubai</h2>
                    <p class="lead">Our first and largest branch, offering the complete Yalla Al Mandhi experience.</p>
                </div>
                
                <div class="row" style="display: flex; gap: 50px; margin-bottom: 50px;">
                    <div class="col" style="flex: 1;">
                        <div class="branch-card">
                            <h3 class="display-3 mb-4">Branch Information</h3>
                            
                            <div class="branch-info">
                                <div class="info-item">
                                    <i class="bi bi-geo-alt info-icon"></i>
                                    <div>
                                        <strong>Address</strong>
                                        <p>Shop No.:00 Royal Class Building, Dubai Investment Park 1, Green Community Village, Dubai.</p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="bi bi-telephone info-icon"></i>
                                    <div>
                                        <strong>Phone Numbers</strong>
                                        <p>+971 50 375 7274 (Restaurant)<br>+971 50 375 7274 (WhatsApp Orders)</p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="bi bi-envelope info-icon"></i>
                                    <div>
                                        <strong>Email</strong>
                                        <p>dip@yallaalmandhi.com</p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="bi bi-car-front info-icon"></i>
                                    <div>
                                        <strong>Parking</strong>
                                        <p>Free valet parking available<br>Mall parking accessible</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Opening Hours -->
                        <div class="branch-card" style="margin-top: 30px;">
                            <h3 class="display-3 mb-4">Opening Hours</h3>
                            <div class="opening-hours-grid">
                                <div class="hour-item">
                                    <span>Monday - Thursday</span>
                                    <span style="font-weight: 600;">12 PM - 12 AM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Friday</span>
                                    <span style="font-weight: 600;">12 PM - 1 AM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Saturday</span>
                                    <span style="font-weight: 600;">12 PM - 1 AM</span>
                                </div>
                                <div class="hour-item">
                                    <span>Sunday</span>
                                    <span style="font-weight: 600;">12 PM - 12 AM</span>
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px; padding: 15px; background-color: var(--color-beige); border-radius: var(--border-radius);">
                                <p style="margin: 0; display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-info-circle" style="color: var(--color-olive);"></i>
                                    <span>Last orders for dine-in: 11:30 PM | Kitchen closes at 12:00 AM</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col" style="flex: 1;">
                        <!-- Branch Features -->
                        <div class="branch-card">
                            <h3 class="display-3 mb-4">Branch Features</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                                <div style="text-align: center;">
                                    <div style="width: 70px; height: 70px; background-color: var(--color-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.5rem;">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <h4 style="color: var(--color-dark-brown);">Capacity</h4>
                                    <p style="opacity: 0.8;">30 seats indoors<br>12 seats outdoors</p>
                                </div>
                                
                                <div style="text-align: center;">
                                    <div style="width: 70px; height: 70px; background-color: var(--color-olive); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.5rem;">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                    <h4 style="color: var(--color-dark-brown);">Private Dining (Hall)</h4>
                                    <p style="opacity: 0.8;">2 Family Hall<br>Capacity: 4-10 people</p>
                                </div>
                                
                                <div style="text-align: center;">
                                    <div style="width: 70px; height: 70px; background-color: var(--color-copper); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.5rem;">
                                        <i class="bi bi-wifi"></i>
                                    </div>
                                    <h4 style="color: var(--color-dark-brown);">Amenities</h4>
                                    <p style="opacity: 0.8;">Free WiFi<br>Prayer room available<br>Baby Chair</p>
                                </div>
                                
                                <div style="text-align: center;">
                                    <div style="width: 70px; height: 70px; background-color: var(--color-dark-brown); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.5rem;">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <h4 style="color: var(--color-dark-brown);">Delivery</h4>
                                    <p style="opacity: 0.8;">Through all platforms<br>Free: Less than 10 KM<br>10 AED: Above 10 KM</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div style="margin-top: 30px;">
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <a href="https://goo.gl/maps/example" target="_blank" class="btn btn-primary">
                                    <i class="bi bi-geo-alt"></i> Get Directions
                                </a>
                                <a href="contact.html" class="btn btn-secondary">
                                    <i class="bi bi-calendar-check"></i> Reserve Table
                                </a>
                                <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp">
                                    <i class="bi bi-whatsapp"></i> WhatsApp Order
                                </a>
                                <a href="tel:+971503757274" class="btn btn-outline">
                                    <i class="bi bi-telephone"></i> Call Branch
                                </a>
                            </div>
                            
                            <!-- Delivery Zones -->
                            <div style="margin-top: 30px; padding: 20px; background-color: var(--color-beige); border-radius: var(--border-radius);">
                                <h4 style="color: var(--color-dark-brown); margin-bottom: 10px;">
                                    <i class="bi bi-truck"></i> Delivery Zones
                                </h4>
                                <p style="opacity: 0.8; margin-bottom: 10px;">This branch delivers to:</p>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">DIP 1</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">DIP 2</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Furjan</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Discovery Garden</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Al Barsha</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Dubai South</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Dubai Marina</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">JLT</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Meadows</span>
                                    <span style="background-color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">Jumeirah</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Google Map -->
                <div class="map-container">
                    <div id="map"></div>
                </div>
                
                <!-- How to Get There -->
                <div style="margin-top: 50px;">
                    <h3 class="display-3 mb-4">How to Get There</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 15px;">
                                <i class="bi bi-car-front" style="color: var(--color-red);"></i> By Car
                            </h4>
                            <p style="opacity: 0.8;">Located next to Dubai Investment Authority Building, Green Community. Ample free parking available.</p>
                        </div>
                        
                        <!-- <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 15px;">
                                <i class="bi bi-bus-front" style="color: var(--color-olive);"></i> By Public Transport
                            </h4>
                            <p style="opacity: 0.8;">Someting comes here...</p>
                        </div> -->
                        
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 15px;">
                                <i class="bi bi-taxi-front" style="color: var(--color-copper);"></i> By Taxi
                            </h4>
                            <p style="opacity: 0.8;">Ask for "Dubai Investments" or use Google Maps location. Taxis available at all times.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Coming Soon Branch Content -->
            <div class="branch-content" id="coming-soon-content">
                <div class="coming-soon-card">
                    <h2 class="display-2" style="color: white; margin-bottom: 20px;">Expanding Across UAE</h2>
                    <p class="lead" style="opacity: 0.9; margin-bottom: 30px;">
                        We're excited to announce new Yalla Al Mandhi locations coming soon to serve more communities.
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 40px;">
                        <div style="text-align: center;">
                            <div style="width: 100px; height: 100px; background-color: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 2px dashed rgba(255, 255, 255, 0.3);">
                                <i class="bi bi-building" style="font-size: 2.5rem; color: var(--color-red);"></i>
                            </div>
                            <h3 style="color: white;">Abu Dhabi</h3>
                            <p style="opacity: 0.8;">Al Reem Island<br>Opening: Q4 2024</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="width: 100px; height: 100px; background-color: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 2px dashed rgba(255, 255, 255, 0.3);">
                                <i class="bi bi-shop" style="font-size: 2.5rem; color: var(--color-red);"></i>
                            </div>
                            <h3 style="color: white;">Sharjah</h3>
                            <p style="opacity: 0.8;">Al Majaz Waterfront<br>Opening: Q1 2025</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="width: 100px; height: 100px; background-color: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 2px dashed rgba(255, 255, 255, 0.3);">
                                <i class="bi bi-building" style="font-size: 2.5rem; color: var(--color-red);"></i>
                            </div>
                            <h3 style="color: white;">Dubai Hills</h3>
                            <p style="opacity: 0.8;">Dubai Hills Mall<br>Opening: Q2 2025</p>
                        </div>
                    </div>
                    
                    <div style="max-width: 600px; margin: 0 auto;">
                        <h3 style="color: white; margin-bottom: 20px;">Stay Updated</h3>
                        <p style="opacity: 0.9; margin-bottom: 20px;">Be the first to know when we open in your area.</p>
                        
                        <form style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="email" 
                                   placeholder="Enter your email" 
                                   style="flex: 1; padding: 15px; border: none; border-radius: var(--border-radius);">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-bell"></i> Notify Me
                            </button>
                        </form>
                        
                        <p style="font-size: 0.9rem; opacity: 0.7;">
                            <i class="bi bi-shield-check"></i> We respect your privacy and will only send relevant updates.
                        </p>
                    </div>
                </div>
                
                <!-- Franchise Opportunity -->
                <div style="margin-top: 50px; background-color: var(--color-beige); padding: 40px; border-radius: var(--border-radius);">
                    <div class="row" style="display: flex; align-items: center; gap: 50px;">
                        <div class="col" style="flex: 1;">
                            <h2 class="display-3 mb-4">Franchise Opportunities</h2>
                            <p style="margin-bottom: 20px; font-size: 1.1rem;">
                                Interested in bringing authentic Yemani cuisine to your city? We're looking for passionate partners to expand the Yalla Al Mandhi experience.
                            </p>
                            <div style="margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <i class="bi bi-check-circle" style="color: var(--color-red);"></i>
                                    <span>Complete training and support</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <i class="bi bi-check-circle" style="color: var(--color-red);"></i>
                                    <span>Proven business model</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-check-circle" style="color: var(--color-red);"></i>
                                    <span>Marketing and operational support</span>
                                </div>
                            </div>
                            <a href="contact.html" class="btn btn-primary">
                                <i class="bi bi-briefcase"></i> Inquire About Franchising
                            </a>
                        </div>
                        
                        <div class="col" style="flex: 1; text-align: center;">
                            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                 alt="Restaurant Expansion" 
                                 style="border-radius: var(--border-radius); max-width: 100%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA SECTION ===== -->
    <section class="section-padding" style="background-color: var(--color-light-gray);">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4">Visit Us Today</h2>
                <p class="lead mb-5">
                    Experience authentic Yemani hospitality and cuisine at our Al Barsha branch.
                </p>
                
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="contact.html" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-check"></i> Book Your Table
                    </a>
                    <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp btn-lg">
                        <i class="bi bi-whatsapp"></i> Order for Delivery
                    </a>
                    <a href="tel:+971503757274" class="btn btn-secondary btn-lg">
                        <i class="bi bi-telephone"></i> Call for Inquiry
                    </a>
                </div>
                
                <div style="margin-top: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <h4 class="mb-3">Need Help Finding Us?</h4>
                    <p style="margin-bottom: 20px; opacity: 0.8;">Our team is available to assist you with directions and any questions about your visit.</p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <i class="bi bi-headset" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Customer Service</p>
                            <p style="opacity: 0.8;">+971 50 375 7274</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-envelope" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Email Support</p>
                            <p style="opacity: 0.8;">support@yallaalmandhi.com</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-clock" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Support Hours</p>
                            <p style="opacity: 0.8;">9 AM - 11 PM Daily</p>
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