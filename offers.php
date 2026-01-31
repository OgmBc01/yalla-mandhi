<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Special Offers Banner" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Special Offers & Promotions</h1>
            <p class="lead">Exclusive deals and discounts on authentic Yemani cuisine. Limited time offers for our valued guests.</p>
        </div>
    </section>

    <!-- ===== CURRENT PROMOTION HIGHLIGHT ===== -->
    <section class="section-padding" style="background: linear-gradient(135deg, var(--color-red) 0%, var(--color-red-light) 100%); color: white;">
        <div class="container">
            <div class="row" style="display: flex; align-items: center; gap: 50px;">
                <div class="col" style="flex: 1;">
                    <div style="background-color: rgba(255, 255, 255, 0.1); padding: 30px; border-radius: var(--border-radius); backdrop-filter: blur(10px);">
                        <span style="background-color: white; color: var(--color-red); padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; display: inline-block; margin-bottom: 20px;">
                            LIMITED TIME OFFER
                        </span>
                        <h2 class="display-2" style="color: white; margin-bottom: 20px;">Ramadan Family Iftar Package</h2>
                        <p style="font-size: 1.2rem; margin-bottom: 25px; opacity: 0.9;">
                            Experience our special Ramadan Iftar featuring traditional Yemani dishes, dates, soups, and desserts. Perfect for family gatherings.
                        </p>
                        <div style="display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-clock" style="font-size: 1.2rem;"></i>
                                <div>
                                    <div style="font-weight: 600;">Valid Until</div>
                                    <div>April 10, 2024</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-people" style="font-size: 1.2rem;"></i>
                                <div>
                                    <div style="font-weight: 600;">For</div>
                                    <div>4-6 People</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-tag" style="font-size: 1.2rem;"></i>
                                <div>
                                    <div style="font-weight: 600;">Price</div>
                                    <div style="font-size: 1.3rem; font-weight: 700;">AED 299</div>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <a href="contact.html" class="btn btn-primary" style="background-color: white; color: var(--color-red); border-color: white;">
                                <i class="bi bi-calendar-check"></i> Reserve Now
                            </a>
                            <a href="tel:+971503757274" class="btn btn-outline" style="border-color: white; color: white;">
                                <i class="bi bi-telephone"></i> Call to Order
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col" style="flex: 1; text-align: center;">
                    <div style="position: relative; display: inline-block;">
                        <img src="https://images.unsplash.com/photo-1565299507177-b0ac66763828?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Ramadan Iftar Spread" 
                             style="border-radius: var(--border-radius); max-width: 100%; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
                        <div style="position: absolute; top: -20px; right: -20px; background-color: white; color: var(--color-red); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-direction: column; font-weight: 700; box-shadow: var(--box-shadow);">
                            <div style="font-size: 1.8rem;">30%</div>
                            <div style="font-size: 0.7rem;">OFF</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ALL OFFERS GRID ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Current Deals</span>
                <h2 class="display-2">All Available Offers</h2>
                <p class="lead">Choose from our range of special promotions designed for different occasions.</p>
            </div>
            
            <div class="offer-grid">
                <!-- Offer 1 -->
                <div class="offer-card">
                    <span class="offer-badge">Family Deal</span>
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Family Platter" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Family Feast Friday</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Every Friday, enjoy our signature Family Platter with 4 types of grilled meats, rice, salads, and appetizers for the whole family.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Original Price</div>
                                <div style="text-decoration: line-through; color: #999;">AED 320</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Offer Price</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">AED 249</div>
                            </div>
                        </div>
                        <div class="offer-validity">Valid every Friday | For 4-6 people</div>
                        <a href="contact.html" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            <i class="bi bi-cart-check"></i> Book This Offer
                        </a>
                    </div>
                </div>
                
                <!-- Offer 2 -->
                <div class="offer-card">
                    <span class="offer-badge" style="background-color: var(--color-olive);">Business Lunch</span>
                    <img src="https://images.unsplash.com/photo-1554672408-730436b60dde?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Business Lunch" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Executive Lunch Package</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Perfect for business meetings. Includes main course, salad, drink, and dessert. Available Monday to Friday, 12 PM - 3 PM.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Per Person</div>
                                <div style="text-decoration: line-through; color: #999;">AED 75</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Offer Price</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">AED 55</div>
                            </div>
                        </div>
                        <div class="offer-validity">Weekdays 12-3 PM | Min. 2 persons</div>
                        <a href="contact.html" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            <i class="bi bi-briefcase"></i> Book Business Lunch
                        </a>
                    </div>
                </div>
                
                <!-- Offer 3 -->
                <div class="offer-card">
                    <span class="offer-badge" style="background-color: var(--color-copper);">Early Bird</span>
                    <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Early Dinner" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Early Bird Special</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Dine between 5 PM - 7 PM and enjoy 20% off your total bill. Perfect for early dinners with family or friends.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Discount</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">20% OFF</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Time</div>
                                <div style="font-size: 1.2rem; font-weight: 700; color: var(--color-red);">5-7 PM</div>
                            </div>
                        </div>
                        <div class="offer-validity">Daily 5-7 PM | Dine-in only</div>
                        <a href="contact.html" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            <i class="bi bi-clock"></i> Reserve Early Table
                        </a>
                    </div>
                </div>
                
                <!-- Offer 4 -->
                <div class="offer-card">
                    <span class="offer-badge" style="background-color: var(--color-dark-brown);">Birthday</span>
                    <img src="https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Birthday Celebration" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Birthday Celebration Package</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Celebrate your birthday with us! Get a complimentary dessert platter and 15% off total bill for groups of 6 or more.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Free</div>
                                <div style="font-size: 1.2rem; font-weight: 700; color: var(--color-red);">Dessert Platter</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Discount</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">15% OFF</div>
                            </div>
                        </div>
                        <div class="offer-validity">All year round | Min. 6 persons</div>
                        <a href="contact.html" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            <i class="bi bi-gift"></i> Plan Birthday Party
                        </a>
                    </div>
                </div>
                
                <!-- Offer 5 -->
                <div class="offer-card">
                    <span class="offer-badge" style="background-color: #25D366;">Takeaway</span>
                    <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Takeaway Order" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Weekend Takeaway Deal</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Order online or through WhatsApp on weekends and get free delivery + 10% discount on orders above AED 150.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Delivery</div>
                                <div style="font-size: 1.2rem; font-weight: 700; color: #25D366;">FREE</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Discount</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">10% OFF</div>
                            </div>
                        </div>
                        <div class="offer-validity">Fri-Sun | Min. order AED 150</div>
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <a href="https://wa.me/971501234567" target="_blank" class="btn btn-whatsapp" style="flex: 1;">
                                <i class="bi bi-whatsapp"></i> Order Now
                            </a>
                            <a href="tel:+971503757274" class="btn btn-outline" style="flex: 1;">
                                <i class="bi bi-telephone"></i> Call
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Offer 6 -->
                <div class="offer-card">
                    <span class="offer-badge">Student</span>
                    <img src="https://images.unsplash.com/photo-1578474846511-04ba529f0b88?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Student Discount" 
                         class="offer-img">
                    <div class="offer-content">
                        <h3 class="offer-title">Student Discount</h3>
                        <p style="margin-bottom: 15px; opacity: 0.8;">
                            Present a valid student ID and enjoy 15% off your meal. Available all week for dine-in and takeaway.
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Discount</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">15% OFF</div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">Requirement</div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--color-red);">Student ID</div>
                            </div>
                        </div>
                        <div class="offer-validity">All days | Valid ID required</div>
                        <a href="contact.html" class="btn btn-secondary" style="width: 100%; margin-top: 20px;">
                            <i class="bi bi-mortarboard"></i> Book Student Table
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TERMS & CONDITIONS ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">Offer Terms & Conditions</h2>
                <p class="lead">Important information about our promotions</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto; background-color: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <div style="display: flex; align-items: flex-start; gap: 20px; margin-bottom: 30px;">
                    <i class="bi bi-info-circle" style="color: var(--color-red); font-size: 1.5rem; margin-top: 5px;"></i>
                    <div>
                        <h3 style="margin-bottom: 15px;">General Terms</h3>
                        <ul style="list-style-type: disc; padding-left: 20px; opacity: 0.8;">
                            <li style="margin-bottom: 10px;">All offers are valid until the specified expiration date or while stocks last</li>
                            <li style="margin-bottom: 10px;">Offers cannot be combined with other promotions or discounts</li>
                            <li style="margin-bottom: 10px;">Reservations are subject to availability</li>
                            <li style="margin-bottom: 10px;">Prices are inclusive of VAT unless otherwise stated</li>
                            <li style="margin-bottom: 10px;">Management reserves the right to modify or cancel offers without prior notice</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; align-items: flex-start; gap: 20px;">
                    <i class="bi bi-calendar-check" style="color: var(--color-olive); font-size: 1.5rem; margin-top: 5px;"></i>
                    <div>
                        <h3 style="margin-bottom: 15px;">Booking Information</h3>
                        <ul style="list-style-type: disc; padding-left: 20px; opacity: 0.8;">
                            <li style="margin-bottom: 10px;">Advance booking recommended for all special offers</li>
                            <li style="margin-bottom: 10px;">Please mention the offer name when making your reservation</li>
                            <li style="margin-bottom: 10px;">Some offers may require prepayment or deposit</li>
                            <li style="margin-bottom: 10px;">Cancellations must be made at least 24 hours in advance for refunds</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                    <h4 style="color: var(--color-dark-brown); margin-bottom: 10px;">
                        <i class="bi bi-question-circle" style="color: var(--color-red);"></i> Have Questions?
                    </h4>
                    <p style="opacity: 0.8; margin-bottom: 15px;">Contact our team for clarification on any offer terms or booking procedures.</p>
                    <a href="contact.html" class="btn btn-outline" style="margin-right: 10px;">
                        <i class="bi bi-envelope"></i> Email Us
                    </a>
                    <a href="tel:+971503757274" class="btn btn-outline">
                        <i class="bi bi-telephone"></i> Call for Details
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUBSCRIBE FOR OFFERS ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4">Never Miss an Offer!</h2>
                <p class="lead mb-5">Subscribe to our newsletter and be the first to know about new promotions and special events.</p>
                
                <div style="max-width: 600px; margin: 0 auto;">
                    <form id="offerSubscription" style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <input type="email" 
                               placeholder="Enter your email address" 
                               required
                               style="flex: 1; padding: 15px; border: 1px solid var(--color-sand); border-radius: var(--border-radius); font-size: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope-arrow-up"></i> Subscribe
                        </button>
                    </form>
                    <p style="font-size: 0.9rem; opacity: 0.6; text-align: center;">
                        We respect your privacy. Unsubscribe at any time. No spam, ever.
                    </p>
                </div>
                
                <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <div style="text-align: center;">
                        <i class="bi bi-bell" style="font-size: 2.5rem; color: var(--color-red); margin-bottom: 15px;"></i>
                        <h4>Instant Notifications</h4>
                        <p style="opacity: 0.8;">Get alerts for flash sales and last-minute offers</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-calendar-event" style="font-size: 2.5rem; color: var(--color-olive); margin-bottom: 15px;"></i>
                        <h4>Event Invitations</h4>
                        <p style="opacity: 0.8;">Exclusive invites to tasting events and celebrations</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <i class="bi bi-gift" style="font-size: 2.5rem; color: var(--color-copper); margin-bottom: 15px;"></i>
                        <h4>Birthday Surprises</h4>
                        <p style="opacity: 0.8;">Special birthday offers sent directly to you</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?
    include 'includes/footer.php';
    ?>