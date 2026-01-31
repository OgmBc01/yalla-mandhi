<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1519709042477-8de6eaf1fdc5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Happy Customers at Restaurant" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">What Our Guests Say</h1>
            <p class="lead">Real experiences from our valued customers who have enjoyed authentic Yemani flavors and warm hospitality.</p>
        </div>
    </section>

    <!-- ===== REVIEW STATISTICS ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">Trusted by Thousands</h2>
                <p class="lead">Our commitment to quality and hospitality reflects in our customers' experiences</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 50px;">
                <div style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-red); margin-bottom: 10px;">4.8</div>
                    <div style="color: #FFD700; font-size: 1.5rem; margin-bottom: 10px;">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <p style="font-weight: 500; color: var(--color-dark-brown);">Average Rating</p>
                    <p style="font-size: 0.9rem; opacity: 0.7;">Based on 1,200+ reviews</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-olive); margin-bottom: 10px;">94%</div>
                    <div style="width: 100%; height: 8px; background-color: var(--color-sand); border-radius: 4px; margin: 0 auto 10px;">
                        <div style="width: 94%; height: 100%; background-color: var(--color-olive); border-radius: 4px;"></div>
                    </div>
                    <p style="font-weight: 500; color: var(--color-dark-brown);">Would Recommend</p>
                    <p style="font-size: 0.9rem; opacity: 0.7;">Customer Satisfaction</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-copper); margin-bottom: 10px;">2.5K+</div>
                    <i class="bi bi-people" style="font-size: 2rem; color: var(--color-copper); margin-bottom: 10px;"></i>
                    <p style="font-weight: 500; color: var(--color-dark-brown);">Monthly Guests</p>
                    <p style="font-size: 0.9rem; opacity: 0.7;">Happy diners every month</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-dark-brown); margin-bottom: 10px;">9+</div>
                    <i class="bi bi-award" style="font-size: 2rem; color: var(--color-dark-brown); margin-bottom: 10px;"></i>
                    <p style="font-weight: 500; color: var(--color-dark-brown);">Years of Excellence</p>
                    <p style="font-size: 0.9rem; opacity: 0.7;">Since 2015</p>
                </div>
            </div>
            
            <!-- Review Platforms -->
            <div style="background-color: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <h3 class="text-center mb-4" style="color: var(--color-dark-brown);">Find Us On Review Platforms</h3>
                <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 15px 25px; background-color: #FFD700; color: #000; border-radius: var(--border-radius); text-decoration: none; font-weight: 600;">
                        <i class="bi bi-google" style="font-size: 1.2rem;"></i>
                        Google Reviews
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 15px 25px; background-color: #FF5A5F; color: white; border-radius: var(--border-radius); text-decoration: none; font-weight: 600;">
                        <i class="bi bi-building" style="font-size: 1.2rem;"></i>
                        TripAdvisor
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 15px 25px; background-color: #FF0000; color: white; border-radius: var(--border-radius); text-decoration: none; font-weight: 600;">
                        <i class="bi bi-yelp" style="font-size: 1.2rem;"></i>
                        Zomato
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 15px 25px; background-color: #1877F2; color: white; border-radius: var(--border-radius); text-decoration: none; font-weight: 600;">
                        <i class="bi bi-facebook" style="font-size: 1.2rem;"></i>
                        Facebook Reviews
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS CAROUSEL ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-subtitle">Featured Reviews</span>
                <h2 class="display-2">Customer Testimonials</h2>
                <p class="lead">Hear directly from our satisfied guests</p>
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
                            "As a Yemani expat living in Dubai for 10 years, I've been searching for authentic Mandhi. Yalla Al Mandhi is the real deal! The flavors transported me back to my grandmother's kitchen in Damascus. The hospitality is exceptional - they make you feel like family."
                        </p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 30px;">
                            <div style="width: 60px; height: 60px; background-color: var(--color-sand); border-radius: 50%; overflow: hidden;">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Ahmed Al Hassan" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <p class="testimonial-author">Ahmed Al Hassan</p>
                                <p style="font-size: 0.9rem; opacity: 0.7;">Regular Customer</p>
                            </div>
                        </div>
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
                            "We celebrated my daughter's birthday here and the team went above and beyond. They arranged a special dessert platter and made her feel so special. The food was incredible - the mixed grill platter is a must-try! Perfect for family celebrations."
                        </p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 30px;">
                            <div style="width: 60px; height: 60px; background-color: var(--color-sand); border-radius: 50%; overflow: hidden;">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Fatima Rahman" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <p class="testimonial-author">Fatima Rahman</p>
                                <p style="font-size: 0.9rem; opacity: 0.7;">Family Celebration</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial-slide">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "I hosted a business lunch here for my team, and everyone was impressed. The executive lunch package offers great value. The service was professional yet warm, and the private dining area was perfect for our meeting. Highly recommended for corporate events!"
                        </p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 30px;">
                            <div style="width: 60px; height: 60px; background-color: var(--color-sand); border-radius: 50%; overflow: hidden;">
                                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Omar Khan" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <p class="testimonial-author">Omar Khan</p>
                                <p style="font-size: 0.9rem; opacity: 0.7;">Business Client</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-nav">
                    <div class="testimonial-dot active"></div>
                    <div class="testimonial-dot"></div>
                    <div class="testimonial-dot"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ALL REVIEWS GRID ===== -->
    <section class="section-padding" style="background-color: var(--color-light-gray);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">More Customer Experiences</h2>
                <p class="lead">Read what others are saying about their dining experience</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <!-- Review 1 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Sarah Johnson</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">2 weeks ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "The early bird special is fantastic! Great value for money. The Mandhi was perfectly cooked - tender meat and flavorful rice."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-clock"></i> Early Bird Offer
                        </span>
                    </div>
                </div>
                
                <!-- Review 2 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Khalid Al Marri</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">1 month ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "Best Yemani restaurant in Dubai! The Kabsa is exactly like what we have back home. The staff are so welcoming and attentive."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-egg-fried"></i> Traditional Dishes
                        </span>
                    </div>
                </div>
                
                <!-- Review 3 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Emma Wilson</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">3 weeks ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "Perfect for a date night! The ambiance is lovely, and the food presentation is beautiful. The dessert platter was the highlight."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-heart"></i> Romantic Dining
                        </span>
                    </div>
                </div>
                
                <!-- Review 4 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Mohammed Ali</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">2 months ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "The weekend takeaway deal is a lifesaver! Food arrived hot and fresh. The packaging is excellent. Will definitely order again."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-truck"></i> Delivery Service
                        </span>
                    </div>
                </div>
                
                <!-- Review 5 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Layla Abbas</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">1 week ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "I'm a vegetarian and was worried about options, but their vegetable Mandhi is amazing! So flavorful and satisfying."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-flower1"></i> Vegetarian Options
                        </span>
                    </div>
                </div>
                
                <!-- Review 6 -->
                <div style="background: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                        <div>
                            <h4 style="color: var(--color-dark-brown); margin-bottom: 5px;">Robert Chen</h4>
                            <div style="color: #FFD700; font-size: 0.9rem;">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </div>
                        </div>
                        <span style="font-size: 0.8rem; color: #999;">3 days ago</span>
                    </div>
                    <p style="margin-bottom: 20px; font-style: italic; opacity: 0.8;">
                        "First time trying Yemani food and I'm hooked! The staff explained everything perfectly. The experience was educational and delicious."
                    </p>
                    <div style="padding-top: 15px; border-top: 1px solid var(--color-sand);">
                        <span style="background-color: var(--color-beige); padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; color: var(--color-olive);">
                            <i class="bi bi-mortarboard"></i> First-Time Visitor
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-5">
                <button id="loadMoreReviews" class="btn btn-outline">
                    <i class="bi bi-arrow-clockwise"></i> Load More Reviews
                </button>
            </div>
        </div>
    </section>

    <!-- ===== GOOGLE REVIEWS EMBED SECTION ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">Google Reviews</h2>
                <p class="lead">See what people are saying on Google</p>
            </div>
            
            <div style="background: white; border-radius: var(--border-radius); padding: 40px; box-shadow: var(--box-shadow);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
                    <div style="width: 70px; height: 70px; background-color: #FFD700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-google" style="font-size: 2rem; color: #000;"></i>
                    </div>
                    <div>
                        <h3 style="color: var(--color-dark-brown); margin-bottom: 5px;">Yalla Al Mandhi on Google</h3>
                        <p style="opacity: 0.8;">Rated 4.8 stars from 850+ reviews</p>
                    </div>
                </div>
                
                <!-- Simulated Google Reviews (in real implementation, you would embed actual Google Reviews) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div style="border: 1px solid var(--color-sand); padding: 20px; border-radius: var(--border-radius);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <div style="font-weight: 600; color: var(--color-dark-brown);">Hassan Mohammed</div>
                                <div style="color: #FFD700; font-size: 0.8rem;">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; color: #999;">2 days ago</span>
                        </div>
                        <p style="font-size: 0.9rem; opacity: 0.8;">
                            "Authentic flavors, excellent service. The Mandhi is the best I've had outside Syria."
                        </p>
                    </div>
                    
                    <div style="border: 1px solid var(--color-sand); padding: 20px; border-radius: var(--border-radius);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <div style="font-weight: 600; color: var(--color-dark-brown);">Aisha Malik</div>
                                <div style="color: #FFD700; font-size: 0.8rem;">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; color: #999;">1 week ago</span>
                        </div>
                        <p style="font-size: 0.9rem; opacity: 0.8;">
                            "Great family restaurant. Kids loved the kids menu. Will definitely return."
                        </p>
                    </div>
                    
                    <div style="border: 1px solid var(--color-sand); padding: 20px; border-radius: var(--border-radius);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <div style="font-weight: 600; color: var(--color-dark-brown);">Carlos Rodriguez</div>
                                <div style="color: #FFD700; font-size: 0.8rem;">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; color: #999;">3 weeks ago</span>
                        </div>
                        <p style="font-size: 0.9rem; opacity: 0.8;">
                            "Amazing food and atmosphere. The staff made excellent recommendations."
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-google"></i> Write a Google Review
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SUBMIT YOUR REVIEW ===== -->
    <section class="section-padding" style="background-color: var(--color-beige);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-2">Share Your Experience</h2>
                <p class="lead">We value your feedback and would love to hear about your dining experience</p>
            </div>
            
            <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <form id="reviewForm">
                    <div class="form-group">
                        <label class="form-label">Your Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;" id="starRating">
                            <i class="bi bi-star" data-rating="1" style="font-size: 1.5rem; color: #ccc; cursor: pointer;"></i>
                            <i class="bi bi-star" data-rating="2" style="font-size: 1.5rem; color: #ccc; cursor: pointer;"></i>
                            <i class="bi bi-star" data-rating="3" style="font-size: 1.5rem; color: #ccc; cursor: pointer;"></i>
                            <i class="bi bi-star" data-rating="4" style="font-size: 1.5rem; color: #ccc; cursor: pointer;"></i>
                            <i class="bi bi-star" data-rating="5" style="font-size: 1.5rem; color: #ccc; cursor: pointer;"></i>
                        </div>
                        <input type="hidden" id="ratingValue" name="rating" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Your Review</label>
                        <textarea class="form-control" rows="5" placeholder="Tell us about your experience..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">When did you visit?</label>
                        <input type="date" class="form-control">
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="bi bi-send"></i> Submit Review
                        </button>
                    </div>
                </form>
                
                <div style="margin-top: 30px; padding: 20px; background-color: var(--color-light-gray); border-radius: var(--border-radius);">
                    <h4 style="color: var(--color-dark-brown); margin-bottom: 10px;">
                        <i class="bi bi-info-circle" style="color: var(--color-red);"></i> Note
                    </h4>
                    <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 0;">
                        Your review will be published after moderation. We appreciate honest feedback about your dining experience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <?
    include 'includes/footer.php';
    ?>