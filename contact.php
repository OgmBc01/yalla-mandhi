<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="resources/img/restaurant_dining/reservation.jpg" alt="Restaurant Interior" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Contact & Reservations</h1>
            <p class="lead">Get in touch with us for reservations, inquiries, or to book your next event.</p>
            <div style="margin-top: 30px;">
                <a href="#reservation" class="btn btn-primary btn-lg">
                    <i class="bi bi-calendar-check"></i> Book a Table
                </a>
                <a href="#contact" class="btn btn-secondary btn-lg">
                    <i class="bi bi-telephone"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- ===== CONTACT TABS ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="contact-tabs">
                <div class="contact-tab-btn active" data-tab="contact-info">
                    <i class="bi bi-info-circle"></i> Contact Information
                </div>
                <div class="contact-tab-btn" data-tab="reservation">
                    <i class="bi bi-calendar-check"></i> Make Reservation
                </div>
                <div class="contact-tab-btn" data-tab="inquiry">
                    <i class="bi bi-chat-dots"></i> General Inquiry
                </div>
                <div class="contact-tab-btn" data-tab="catering">
                    <i class="bi bi-truck"></i> Catering Inquiry
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CONTACT INFORMATION TAB ===== -->
    <section id="contact" class="section-padding contact-content active">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Get in Touch</span>
                <h2 class="display-2">Contact Information</h2>
                <p class="lead">Multiple ways to reach us for any questions or assistance</p>
            </div>
            
            <!-- Contact Info Cards -->
            <div class="contact-info-grid">
                <!-- Phone -->
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <h3>Phone</h3>
                    <p style="margin-bottom: 15px; opacity: 0.8;">Call us directly for reservations or inquiries</p>
                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--color-red); margin-bottom: 20px;">
                        +971 50 375 7274
                    </div>
                    <a href="tel:+971 50 375 7274" class="btn btn-outline">
                        <i class="bi bi-telephone"></i> Call Now
                    </a>
                </div>
                
                <!-- WhatsApp -->
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h3>WhatsApp</h3>
                    <p style="margin-bottom: 15px; opacity: 0.8;">Quick responses for orders and inquiries</p>
                    <div style="font-size: 1.2rem; font-weight: 600; color: #25D366; margin-bottom: 20px;">
                        +971 50 375 7274
                    </div>
                    <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp">
                        <i class="bi bi-whatsapp"></i> Message Us
                    </a>
                </div>
                
                <!-- Email -->
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p style="margin-bottom: 15px; opacity: 0.8;">Send us detailed inquiries or feedback</p>
                    <div style="font-size: 1.1rem; font-weight: 600; color: var(--color-dark-brown); margin-bottom: 20px;">
                        info@yallaalMandi.com
                    </div>
                    <a href="mailto:info@yallaalMandi.com" class="btn btn-outline">
                        <i class="bi bi-envelope"></i> Send Email
                    </a>
                </div>
                
                <!-- Address -->
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p style="margin-bottom: 15px; opacity: 0.8;">Our flagship branch location</p>
                    <div style="font-size: 1rem; color: var(--color-dark-brown); margin-bottom: 20px;">
                        Shop No.:00 Royal Class Building, Dubai Investment<br> Park 1, Green Community <br>Village, Dubai.
                    </div>
                    <a href="https://goo.gl/maps/example" target="_blank" class="btn btn-outline">
                        <i class="bi bi-geo-alt"></i> Get Directions
                    </a>
                </div>
            </div>
            
            <!-- Social Media Links -->
            <div style="margin-top: 50px; text-align: center;">
                <h3 class="display-3 mb-4">Connect With Us</h3>
                <p class="lead mb-5">Follow us on social media for updates, promotions, and behind-the-scenes content</p>
                
                <div class="social-contact-links">
                    <a href="#" class="social-contact-link">
                        <i class="bi bi-facebook" style="color: #1877F2; font-size: 1.2rem;"></i>
                        <span>Facebook</span>
                    </a>
                    
                    <a href="#" class="social-contact-link">
                        <i class="bi bi-instagram" style="color: #E1306C; font-size: 1.2rem;"></i>
                        <span>Instagram</span>
                    </a>
                    
                    <a href="#" class="social-contact-link">
                        <i class="bi bi-twitter" style="color: #1DA1F2; font-size: 1.2rem;"></i>
                        <span>Twitter</span>
                    </a>
                    
                    <a href="#" class="social-contact-link">
                        <i class="bi bi-tiktok" style="font-size: 1.2rem;"></i>
                        <span>TikTok</span>
                    </a>
                </div>
            </div>
            
            <!-- Operating Hours -->
            <div style="margin-top: 50px; background-color: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <div class="row" style="display: flex; align-items: center; gap: 50px;">
                    <div class="col" style="flex: 1;">
                        <h3 class="display-3 mb-4">Operating Hours</h3>
                        <p style="margin-bottom: 20px; font-size: 1.1rem;">
                            We're open every day to serve you authentic Yemani cuisine. Visit us during our operating hours or book in advance to secure your table.
                        </p>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 10px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                                <span>Monday - Thursday</span>
                                <span style="font-weight: 600;">12 PM - 12 AM</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                                <span>Friday</span>
                                <span style="font-weight: 600;">12 PM - 1 AM</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                                <span>Saturday</span>
                                <span style="font-weight: 600;">12 PM - 1 AM</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                                <span>Sunday</span>
                                <span style="font-weight: 600;">12 PM - 12 AM</span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; padding: 15px; background-color: var(--color-beige); border-radius: var(--border-radius);">
                            <p style="margin: 0; display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-info-circle" style="color: var(--color-olive);"></i>
                                <span>Last reservation: 10:30 PM | Last order: 11:30 PM</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="col" style="flex: 1; text-align: center;">
                        <div style="background-color: var(--color-red); color: white; padding: 30px; border-radius: var(--border-radius);">
                            <i class="bi bi-clock" style="font-size: 3rem; margin-bottom: 20px;"></i>
                            <h3 style="color: white; margin-bottom: 10px;">Peak Hours</h3>
                            <p style="opacity: 0.9;">7:00 PM - 9:30 PM</p>
                            <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 10px;">
                                We recommend booking in advance during peak hours
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Google Map -->
            <div style="margin-top: 50px;">
                <h3 class="display-3 mb-4 text-center">Find Us</h3>
                <div class="contact-map">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== RESERVATION FORM TAB ===== -->
    <section id="reservation" class="section-padding contact-content">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Book Your Table</span>
                <h2 class="display-2">Make a Reservation</h2>
                <p class="lead">Secure your table in advance for the best dining experience</p>
            </div>
            
            <div class="reservation-form-container">
                <!-- Reservation Form -->
                <form id="reservationForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Number of Guests *</label>
                            <select class="form-control" required>
                                <option value="">Select number</option>
                                <option value="1">1 Person</option>
                                <option value="2">2 People</option>
                                <option value="3">3 People</option>
                                <option value="4">4 People</option>
                                <option value="5">5 People</option>
                                <option value="6">6 People</option>
                                <option value="7">7 People</option>
                                <option value="8">8 People</option>
                                <option value="9">9+ People</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Reservation Date *</label>
                            <input type="date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Reservation Time *</label>
                            <select class="form-control" required>
                                <option value="">Select time</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="18:30">6:30 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="19:30">7:30 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="20:30">8:30 PM</option>
                                <option value="21:00">9:00 PM</option>
                                <option value="21:30">9:30 PM</option>
                                <option value="22:00">10:00 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Special Requests</label>
                        <textarea class="form-control" rows="4" placeholder="Any special requests? (Birthday, anniversary, dietary restrictions, etc.)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Preferred Branch *</label>
                        <select class="form-control" required>
                            <option value="">Select branch</option>
                            <option value="al-barsha">DIP (Flagship)</option>
                            <option value="coming-soon-abu-dhabi" disabled>Abu Dhabi (Coming Soon)</option>
                            <option value="coming-soon-sharjah" disabled>Sharjah (Coming Soon)</option>
                        </select>
                        <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                            Note: Only DIP branch is currently accepting reservations
                        </p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            <i class="bi bi-calendar-check"></i> Confirm Reservation
                        </button>
                    </div>
                </form>
                
                <!-- Success Message -->
                <div class="form-success" id="reservationSuccess">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: var(--color-red); margin-bottom: 20px;"></i>
                    <h3 class="display-3 mb-3">Reservation Confirmed!</h3>
                    <p class="lead mb-4">
                        Thank you for your reservation. We've sent a confirmation to your email. 
                        We look forward to serving you authentic Yemani cuisine.
                    </p>
                    <p style="margin-bottom: 30px; opacity: 0.8;">
                        <strong>Note:</strong> Please arrive 10 minutes before your reservation time. 
                        For any changes, please call us at +971 50 375 7274.
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="menu.php" class="btn btn-outline">
                            <i class="bi bi-menu-button"></i> View Menu
                        </a>
                        <a href="offers.php" class="btn btn-outline">
                            <i class="bi bi-percent"></i> View Offers
                        </a>
                        <button class="btn btn-primary" id="newReservationBtn">
                            <i class="bi bi-plus-circle"></i> New Reservation
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Reservation Information -->
            <div style="margin-top: 50px; background-color: var(--color-beige); padding: 40px; border-radius: var(--border-radius);">
                <h3 class="display-3 mb-4 text-center">Reservation Policy</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <div style="text-align: center;">
                        <i class="bi bi-clock-history" style="font-size: 2rem; color: var(--color-red); margin-bottom: 15px;"></i>
                        <h4>Timing</h4>
                        <p style="opacity: 0.8;">Tables are held for 15 minutes past reservation time</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-people" style="font-size: 2rem; color: var(--color-olive); margin-bottom: 15px;"></i>
                        <h4>Group Bookings</h4>
                        <p style="opacity: 0.8;">For groups of 8+, please call us directly</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-calendar-x" style="font-size: 2rem; color: var(--color-copper); margin-bottom: 15px;"></i>
                        <h4>Cancellation</h4>
                        <p style="opacity: 0.8;">Please cancel 2 hours in advance</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-credit-card" style="font-size: 2rem; color: var(--color-dark-brown); margin-bottom: 15px;"></i>
                        <h4>Payment</h4>
                        <p style="opacity: 0.8;">No deposit required for regular bookings</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== GENERAL INQUIRY TAB ===== -->
    <section class="section-padding contact-content">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Have Questions?</span>
                <h2 class="display-2">General Inquiry</h2>
                <p class="lead">Send us your questions, feedback, or suggestions</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto;">
                <form id="inquiryForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Inquiry Type *</label>
                        <select class="form-control" required>
                            <option value="">Select inquiry type</option>
                            <option value="general">General Question</option>
                            <option value="feedback">Feedback</option>
                            <option value="complaint">Complaint</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="partnership">Partnership Opportunity</option>
                            <option value="media">Media Inquiry</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea class="form-control" rows="6" required></textarea>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            <i class="bi bi-send"></i> Send Inquiry
                        </button>
                    </div>
                </form>
                
                <!-- Success Message -->
                <div class="form-success" id="inquirySuccess">
                    <i class="bi bi-envelope-check" style="font-size: 4rem; color: var(--color-red); margin-bottom: 20px;"></i>
                    <h3 class="display-3 mb-3">Inquiry Sent!</h3>
                    <p class="lead mb-4">
                        Thank you for reaching out. We've received your inquiry and will get back to you within 24 hours.
                    </p>
                    <p style="margin-bottom: 30px; opacity: 0.8;">
                        For urgent matters, please call us at +971 50 375 7274.
                    </p>
                    <button class="btn btn-primary" id="newInquiryBtn">
                        <i class="bi bi-plus-circle"></i> New Inquiry
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CATERING INQUIRY TAB ===== -->
    <section class="section-padding contact-content">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Events & Catering</span>
                <h2 class="display-2">Catering Inquiry</h2>
                <p class="lead">Let us bring authentic Yemani cuisine to your event</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto;">
                <form id="cateringForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Organization/Company</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Event Type *</label>
                        <select class="form-control" required>
                            <option value="">Select event type</option>
                            <option value="corporate">Corporate Event</option>
                            <option value="wedding">Wedding</option>
                            <option value="birthday">Birthday Party</option>
                            <option value="family">Family Gathering</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Event Date *</label>
                            <input type="date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Number of Guests *</label>
                            <select class="form-control" required>
                                <option value="">Select number</option>
                                <option value="20-50">20-50 People</option>
                                <option value="50-100">50-100 People</option>
                                <option value="100-200">100-200 People</option>
                                <option value="200+">200+ People</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Event Location</label>
                        <input type="text" class="form-control" placeholder="Venue address (if known)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Service Type *</label>
                        <select class="form-control" required>
                            <option value="">Select service type</option>
                            <option value="delivery">Delivery Only</option>
                            <option value="setup">Setup & Service</option>
                            <option value="full">Full Catering Service</option>
                            <option value="unsure">Not Sure - Need Advice</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Details</label>
                        <textarea class="form-control" rows="4" placeholder="Tell us more about your event, dietary requirements, budget range, etc."></textarea>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            <i class="bi bi-send"></i> Submit Catering Inquiry
                        </button>
                    </div>
                </form>
                
                <!-- Success Message -->
                <div class="form-success" id="cateringSuccess">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: var(--color-red); margin-bottom: 20px;"></i>
                    <h3 class="display-3 mb-3">Inquiry Received!</h3>
                    <p class="lead mb-4">
                        Thank you for your catering inquiry. Our events team will contact you within 24 hours to discuss your requirements and provide a customized quote.
                    </p>
                    <p style="margin-bottom: 30px; opacity: 0.8;">
                        For urgent inquiries, please call our events team at +971 50 375 7274 (Ext. 2).
                    </p>
                    <button class="btn btn-primary" id="newCateringBtn">
                        <i class="bi bi-plus-circle"></i> New Catering Inquiry
                    </button>
                </div>
            </div>
            
            <!-- Catering Information -->
            <div style="margin-top: 50px;">
                <h3 class="display-3 mb-4 text-center">Why Choose Our Catering?</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <div style="text-align: center;">
                        <i class="bi bi-star" style="font-size: 2rem; color: var(--color-red); margin-bottom: 15px;"></i>
                        <h4>Authentic Cuisine</h4>
                        <p style="opacity: 0.8;">Same authentic Yemani flavors as our restaurant</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-people" style="font-size: 2rem; color: var(--color-olive); margin-bottom: 15px;"></i>
                        <h4>Professional Staff</h4>
                        <p style="opacity: 0.8;">Experienced serving staff for your event</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-truck" style="font-size: 2rem; color: var(--color-copper); margin-bottom: 15px;"></i>
                        <h4>Flexible Setup</h4>
                        <p style="opacity: 0.8;">From simple delivery to full service setup</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-cash-coin" style="font-size: 2rem; color: var(--color-dark-brown); margin-bottom: 15px;"></i>
                        <h4>Custom Packages</h4>
                        <p style="opacity: 0.8;">Tailored to your event needs and budget</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== QUICK CONTACT SECTION ===== -->
    <section class="section-padding" style="background-color: var(--color-light-gray);">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4">Need Immediate Assistance?</h2>
                <p class="lead mb-5">
                    For urgent matters or same-day reservations, contact us directly
                </p>
                
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="tel:+97141234567" class="social-contact-link phone">
                        <i class="bi bi-telephone" style="font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600;">Call Now</div>
                            <div>+971 50 375 7274</div>
                        </div>
                    </a>
                    
                    <a href="https://wa.me/971501234567" target="_blank" class="social-contact-link whatsapp">
                        <i class="bi bi-whatsapp" style="font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600;">WhatsApp</div>
                            <div>+971 50 375 7274</div>
                        </div>
                    </a>
                    
                    <a href="mailto:info@yallaalMandi.com" class="social-contact-link email">
                        <i class="bi bi-envelope" style="font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600;">Email Us</div>
                            <div>info@yallaalMandi.com</div>
                        </div>
                    </a>
                </div>
                
                <div style="margin-top: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <h4 class="mb-3">Response Time</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                        <div style="text-align: center;">
                            <i class="bi bi-whatsapp" style="font-size: 2rem; color: #25D366; margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">WhatsApp</p>
                            <p style="opacity: 0.8;">Within 15 minutes</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-telephone" style="font-size: 2rem; color: var(--color-red); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Phone Calls</p>
                            <p style="opacity: 0.8;">Immediate</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-envelope" style="font-size: 2rem; color: var(--color-olive); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Email</p>
                            <p style="opacity: 0.8;">Within 24 hours</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-calendar-check" style="font-size: 2rem; color: var(--color-copper); margin-bottom: 10px;"></i>
                            <p style="font-weight: 600;">Reservations</p>
                            <p style="opacity: 0.8;">Confirmed in 2 hours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?php
    include 'includes/footer.php';
    ?>