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
<?php

    // Fetch active promotions
    $featured_promo_query = "SELECT * FROM promotions WHERE is_highlighted = 1 AND is_active = 1 ORDER BY display_order LIMIT 1";
    $featured_promo_result = $connection->query($featured_promo_query);
    $featured_promo = $featured_promo_result->num_rows > 0 ? $featured_promo_result->fetch_assoc() : null;

    // Fetch all active promotions
    $all_promos_query = "SELECT * FROM promotions WHERE is_active = 1 AND is_highlighted = 0 ORDER BY display_order, created_at DESC";
    $all_promos_result = $connection->query($all_promos_query);
    ?>

    <section class="section-padding" id="promotionsSection">
        <!-- ===== CURRENT PROMOTION HIGHLIGHT ===== -->
        <?php if ($featured_promo): ?>
        <section class="section-padding" style="background: linear-gradient(135deg, <?php echo $featured_promo['badge_color'] ?? 'var(--color-red)'; ?> 0%, <?php echo $this->adjustColorBrightness($featured_promo['badge_color'] ?? 'var(--color-red)', 20); ?> 100%); color: white;">
            <div class="container">
                <div class="row" style="display: flex; align-items: center; gap: 50px; flex-wrap: wrap;">
                    <div class="col" style="flex: 1; min-width: 300px;">
                        <div style="background-color: rgba(255, 255, 255, 0.1); padding: 30px; border-radius: var(--border-radius); backdrop-filter: blur(10px);">
                            <span style="background-color: white; color: <?php echo $featured_promo['badge_color'] ?? 'var(--color-red)'; ?>; padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; display: inline-block; margin-bottom: 20px;">
                                <?php echo htmlspecialchars($featured_promo['badge_text'] ?? 'LIMITED TIME OFFER'); ?>
                            </span>
                            <h2 class="display-2" style="color: white; margin-bottom: 20px;">
                                <?php echo htmlspecialchars($featured_promo['title']); ?>
                            </h2>
                            <p style="font-size: 1.2rem; margin-bottom: 25px; opacity: 0.9;">
                                <?php echo htmlspecialchars($featured_promo['description']); ?>
                            </p>
                            <div style="display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap;">
                                <?php if ($featured_promo['valid_until']): ?>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-clock" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <div style="font-weight: 600;">Valid Until</div>
                                        <div><?php echo date('F d, Y', strtotime($featured_promo['valid_until'])); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($featured_promo['min_persons'] || $featured_promo['max_persons']): ?>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-people" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <div style="font-weight: 600;">For</div>
                                        <div>
                                            <?php 
                                            if ($featured_promo['min_persons'] && $featured_promo['max_persons']) {
                                                echo htmlspecialchars($featured_promo['min_persons'] . '-' . $featured_promo['max_persons'] . ' People');
                                            } elseif ($featured_promo['min_persons']) {
                                                echo htmlspecialchars('Min. ' . $featured_promo['min_persons'] . ' People');
                                            } else {
                                                echo htmlspecialchars($featured_promo['max_persons'] . ' People');
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-tag" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <div style="font-weight: 600;">Price</div>
                                        <div style="font-size: 1.3rem; font-weight: 700;">
                                            <?php echo htmlspecialchars($featured_promo['currency'] . ' ' . number_format($featured_promo['offer_price'], 2)); ?>
                                            <?php if ($featured_promo['original_price']): ?>
                                                <small style="font-size: 1rem; text-decoration: line-through; opacity: 0.8; margin-left: 10px;">
                                                    <?php echo htmlspecialchars($featured_promo['currency'] . ' ' . number_format($featured_promo['original_price'], 2)); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 15px;">
                                <a href="<?php echo htmlspecialchars($featured_promo['cta_link']); ?>" class="btn btn-primary" style="background-color: white; color: <?php echo $featured_promo['badge_color'] ?? 'var(--color-red)'; ?>; border-color: white;">
                                    <i class="bi <?php echo htmlspecialchars($featured_promo['cta_icon']); ?>"></i> 
                                    <?php echo htmlspecialchars($featured_promo['cta_text']); ?>
                                </a>
                                <a href="tel:+971503757274" class="btn btn-outline" style="border-color: white; color: white;">
                                    <i class="bi bi-telephone"></i> Call to Order
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col" style="flex: 1; min-width: 300px; text-align: center;">
                        <div style="position: relative; display: inline-block;">
                            <?php if ($featured_promo['image_url']): ?>
                            <img src="../uploads/promotions/<?php echo htmlspecialchars($featured_promo['image_url']); ?>" 
                                alt="<?php echo htmlspecialchars($featured_promo['title']); ?>" 
                                style="border-radius: var(--border-radius); max-width: 100%; height: 400px; object-fit: cover; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);"
                                onerror="this.src='https://images.unsplash.com/photo-1565299507177-b0ac66763828?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1565299507177-b0ac66763828?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                alt="Promotion Image" 
                                style="border-radius: var(--border-radius); max-width: 100%; height: 400px; object-fit: cover; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
                            <?php endif; ?>
                            
                            <?php if ($featured_promo['discount_percent']): ?>
                            <div style="position: absolute; top: -20px; right: -20px; background-color: white; color: <?php echo $featured_promo['badge_color'] ?? 'var(--color-red)'; ?>; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-direction: column; font-weight: 700; box-shadow: var(--box-shadow);">
                                <div style="font-size: 1.8rem;"><?php echo htmlspecialchars($featured_promo['discount_percent']); ?>%</div>
                                <div style="font-size: 0.7rem;">OFF</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ===== ALL OFFERS GRID ===== -->
        <section class="section-padding">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-subtitle">Current Deals</span>
                    <h2 class="display-2">All Available Offers</h2>
                    <p class="lead">Choose from our range of special promotions designed for different occasions.</p>
                </div>
                
                <?php if ($all_promos_result->num_rows > 0): ?>
                <div class="offer-grid">
                    <?php while ($promo = $all_promos_result->fetch_assoc()): ?>
                    <div class="offer-card">
                        <span class="offer-badge" style="background-color: <?php echo htmlspecialchars($promo['badge_color'] ?? 'var(--color-red)'); ?>;">
                            <?php echo htmlspecialchars($promo['badge_text'] ?? 'Special Offer'); ?>
                        </span>
                        
                        <?php if ($promo['image_url']): ?>
                        <img src="../uploads/promotions/<?php echo htmlspecialchars($promo['image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($promo['title']); ?>" 
                            class="offer-img"
                            onerror="this.src='https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                            alt="Offer Image" 
                            class="offer-img">
                        <?php endif; ?>
                        
                        <div class="offer-content">
                            <h3 class="offer-title"><?php echo htmlspecialchars($promo['title']); ?></h3>
                            
                            <?php if ($promo['subtitle']): ?>
                            <p style="font-size: 0.9rem; color: var(--color-copper); margin-bottom: 10px; font-weight: 500;">
                                <?php echo htmlspecialchars($promo['subtitle']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p style="margin-bottom: 15px; opacity: 0.8; font-size: 0.95rem;">
                                <?php 
                                echo strlen($promo['short_description'] ?: $promo['description']) > 120 
                                    ? htmlspecialchars(substr($promo['short_description'] ?: $promo['description'], 0, 120)) . '...' 
                                    : htmlspecialchars($promo['short_description'] ?: $promo['description']);
                                ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <div>
                                    <?php if ($promo['original_price']): ?>
                                    <div style="font-size: 0.9rem; color: var(--color-olive); font-weight: 500;">Original Price</div>
                                    <div style="text-decoration: line-through; color: #999;">
                                        <?php echo htmlspecialchars($promo['currency'] . ' ' . number_format($promo['original_price'], 2)); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--color-red); font-weight: 500;">
                                        <?php echo $promo['discount_percent'] ? 'Offer Price' : 'Price'; ?>
                                    </div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-red);">
                                        <?php echo htmlspecialchars($promo['currency'] . ' ' . number_format($promo['offer_price'], 2)); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="offer-validity">
                                <?php
                                $validity_parts = [];
                                if ($promo['valid_until']) {
                                    $validity_parts[] = 'Until ' . date('M d, Y', strtotime($promo['valid_until']));
                                }
                                if ($promo['time_slot']) {
                                    $validity_parts[] = $promo['time_slot'];
                                }
                                if ($promo['min_persons'] || $promo['max_persons']) {
                                    if ($promo['min_persons'] && $promo['max_persons']) {
                                        $validity_parts[] = 'For ' . $promo['min_persons'] . '-' . $promo['max_persons'] . ' people';
                                    } elseif ($promo['min_persons']) {
                                        $validity_parts[] = 'Min. ' . $promo['min_persons'] . ' people';
                                    } else {
                                        $validity_parts[] = 'For ' . $promo['max_persons'] . ' people';
                                    }
                                }
                                if ($promo['requirements']) {
                                    $validity_parts[] = $promo['requirements'];
                                }
                                echo htmlspecialchars(implode(' | ', $validity_parts));
                                ?>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <a href="<?php echo htmlspecialchars($promo['cta_link']); ?>" class="btn btn-primary" style="flex: 1;">
                                    <i class="bi <?php echo htmlspecialchars($promo['cta_icon']); ?>"></i> 
                                    <?php echo htmlspecialchars($promo['cta_text']); ?>
                                </a>
                                <a href="tel:+971503757274" class="btn btn-outline" style="flex: 0 0 auto;">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-tag display-4 text-muted d-block mb-3"></i>
                    <h3>No Current Offers</h3>
                    <p class="text-muted">Check back soon for new promotions!</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </section>

    <?php
    // Helper function to adjust color brightness (add to your functions.php)
    function adjustColorBrightness($hex, $percent) {
        // This is a simplified version - in production, add proper color manipulation
        return $hex;
    }
    ?>

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
                    <a href="contact.php" class="btn btn-outline" style="margin-right: 10px;">
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
    <?php
    include 'includes/footer.php';
    ?>