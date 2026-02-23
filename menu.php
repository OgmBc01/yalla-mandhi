<?php
include 'includes/header.php';
?>

    <!-- ===== HERO BANNER ===== -->
    <section class="hero">
        <img src="https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Traditional Yemani Mandi" class="hero-bg">
        <div class="hero-content">
            <h1 class="display-1">Our Menu</h1>
            <p class="lead">Authentic Yemani flavors crafted with generations of culinary expertise.</p>
            <div style="margin-top: 30px;">
                <a href="#order" class="btn btn-primary btn-lg">
                    <i class="bi bi-whatsapp"></i> Order Now
                </a>
                <a href="contact.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-calendar-check"></i> Reserve Table
                </a>
            </div>
        </div>
    </section>

    <?php
    // Category icon mapping
    $category_icons = [
        'signature' => 'bi-star-fill',
        'grills' => 'bi-fire',
        'rice' => 'bi-egg-fried',
        'appetizers' => 'bi-basket',
        'family' => 'bi-people',
        'kids' => 'bi-emoji-smile',
        'beverages' => 'bi-cup-straw',
        'desserts' => 'bi-cake2',
        'default' => 'bi-tag'
    ];

    // Fetch all active categories
    $categories_query = "SELECT 
        c.*,
        COUNT(mi.id) as item_count
        FROM menu_categories c
        LEFT JOIN menu_items mi ON c.id = mi.category_id AND mi.is_available = 1
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.name ASC";

    $categories_result = $connection->query($categories_query);

    if (!$categories_result) {
        die('Error fetching categories: ' . $connection->error);
    }

    // Fetch all menu items
    $menu_items_query = "SELECT 
        mi.*,
        mc.name as category_name
        FROM menu_items mi
        JOIN menu_categories mc ON mi.category_id = mc.id
        WHERE mi.is_available = 1 AND mc.is_active = 1
        ORDER BY mc.sort_order, mi.name";

    $menu_items_result = $connection->query($menu_items_query);

    if (!$menu_items_result) {
        die('Error fetching menu items: ' . $connection->error);
    }

    // Group menu items by category
    $menu_by_category = [];
    while ($item = $menu_items_result->fetch_assoc()) {
        $menu_by_category[$item['category_id']][] = $item;
    }

    // Function to generate section ID from category name
    function getSectionId($category_name) {
        $name = strtolower($category_name);
        $name = preg_replace('/[^a-z0-9]+/', '-', $name);
        return trim($name, '-');
    }
    ?>

    <style>
    /* Reduced category button size */
    .menu-category-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
        margin: 5px 0;
    }

    .menu-category-btn {
        padding: 4px 10px !important;
        font-size: 0.8rem !important;
        border-radius: 20px !important;
        white-space: nowrap;
        border: 1px solid var(--color-red) !important;
        color: var(--color-red) !important;
        background: transparent !important;
    }

    .menu-category-btn i {
        font-size: 0.8rem;
        margin-right: 3px;
    }

    .menu-category-btn:hover,
    .menu-category-btn.active {
        background: var(--color-red) !important;
        color: white !important;
    }

    /* Reduced section padding */
    .section-padding {
        padding: 25px 0 !important;
    }

    .section-padding:first-of-type {
        padding-top: 15px !important;
    }

    /* Menu card styles */
    .menu-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(196,30,58,0.15);
    }

    .menu-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
    }

    .menu-content {
        padding: 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .menu-tag {
        display: inline-block;
        padding: 3px 8px;
        background: var(--color-red);
        color: white;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 8px;
        align-self: flex-start;
    }

    .menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .menu-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-dark-brown);
        margin: 0;
    }

    .menu-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-red);
    }

    .menu-description {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 10px;
        line-height: 1.4;
        flex: 1;
    }

    .inventory-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 12px;
        background: var(--color-olive);
        color: white;
        display: inline-block;
        margin-top: 5px;
        align-self: flex-start;
    }

    .inventory-badge.low {
        background: #f39c12;
    }

    .inventory-badge.out {
        background: #e74c3c;
    }

    /* Category navigation sticky */
    .menu-category-nav {
        position: sticky;
        top: 60px;
        z-index: 100;
        background: var(--color-beige);
        padding: 8px 0;
    }

    /* Menu grid */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
    }

    /* Section title */
    .display-2 {
        font-size: 1.8rem !important;
        margin-bottom: 5px !important;
    }

    .section-subtitle {
        font-size: 0.9rem;
        color: var(--color-olive);
        margin-bottom: 5px;
        display: block;
    }

    .lead {
        font-size: 0.95rem !important;
        margin-bottom: 10px !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .menu-category-nav {
            flex-wrap: nowrap;
            overflow-x: auto;
            justify-content: flex-start;
            padding: 8px 15px;
            -webkit-overflow-scrolling: touch;
        }
        
        .menu-category-btn {
            flex: 0 0 auto;
        }
        
        .section-padding {
            padding: 20px 0 !important;
        }
        
        .menu-img {
            height: 140px;
        }
    }
    </style>

    <!-- ===== MENU CATEGORIES NAVIGATION ===== -->
    <section class="section-padding" style="background-color: var(--color-beige); position: sticky; top: 60px; z-index: 100; padding: 10px 0;">
        <div class="container">
            <div class="text-center mb-1">
                <h2 style="margin-bottom: 0; font-size: 1.2rem; color: var(--color-red);">Menu Categories</h2>
            </div>
            
            <?php if ($categories_result->num_rows > 0): ?>
            <div class="menu-category-nav">
                <?php 
                $categories_result->data_seek(0);
                while ($category = $categories_result->fetch_assoc()): 
                    $icon = isset($category_icons[strtolower($category['name'])]) ? $category_icons[strtolower($category['name'])] : $category_icons['default'];
                    $section_id = getSectionId($category['name']);
                ?>
                <a href="#<?php echo $section_id; ?>" class="btn menu-category-btn" data-category="<?php echo $category['id']; ?>">
                    <i class="bi <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($category['name']); ?>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No categories available</p>
            <?php endif; ?>
        </div>
    </section>

    <?php 
    // Reset category result pointer for the main sections
    if ($categories_result->num_rows > 0):
        $categories_result->data_seek(0);
        $section_index = 0;
        while ($category = $categories_result->fetch_assoc()): 
            $section_id = getSectionId($category['name']);
            $icon = isset($category_icons[strtolower($category['name'])]) ? $category_icons[strtolower($category['name'])] : $category_icons['default'];
            $menu_items = isset($menu_by_category[$category['id']]) ? $menu_by_category[$category['id']] : [];
            $background = ($section_index % 2 == 1) ? 'style="background-color: var(--color-beige);"' : '';
            $section_index++;
    ?>

    <!-- ===== <?php echo strtoupper($category['name']); ?> SECTION ===== -->
    <section id="<?php echo $section_id; ?>" class="section-padding menu-category" <?php echo $background; ?>>
        <div class="container">
            <div class="text-center mb-3">
                <span class="section-subtitle"><?php echo htmlspecialchars($category['description'] ?? 'Our Specialties'); ?></span>
                <h2 class="display-2"><?php echo htmlspecialchars($category['name']); ?></h2>
                <?php if (!empty($category['description'])): ?>
                <p class="lead"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (empty($menu_items)): ?>
            <div class="text-center py-3">
                <p class="text-muted">No items available in this category at the moment.</p>
            </div>
            <?php else: ?>
            <div class="menu-grid">
                <?php foreach ($menu_items as $item): 
                    // Determine stock status
                    $stock_status = '';
                    $stock_text = '';
                    if (!empty($item['track_inventory']) && $item['available_quantity'] !== null) {
                        if ($item['available_quantity'] <= 0) {
                            $stock_status = 'out';
                            $stock_text = 'Out of Stock';
                        } elseif (!empty($item['is_daily_limited']) && $item['available_quantity'] < 5) {
                            $stock_status = 'low';
                            $stock_text = 'Only ' . $item['available_quantity'] . ' left';
                        }
                    }
                ?>
                <div class="menu-card">
                    <?php if (!empty($item['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                        alt="<?php echo htmlspecialchars($item['name']); ?>" 
                        class="menu-img"
                        onerror="this.src='https://via.placeholder.com/400x300?text=<?php echo urlencode($item['name']); ?>'">
                    <?php else: ?>
                    <img src="https://via.placeholder.com/400x300?text=<?php echo urlencode($item['name']); ?>" 
                        alt="<?php echo htmlspecialchars($item['name']); ?>" 
                        class="menu-img">
                    <?php endif; ?>
                    
                    <div class="menu-content">
                        <?php if (!empty($item['is_featured'])): ?>
                        <span class="menu-tag">Chef's Special</span>
                        <?php elseif (!empty($item['is_daily_limited'])): ?>
                        <span class="menu-tag">Limited</span>
                        <?php endif; ?>
                        
                        <div class="menu-header">
                            <h3 class="menu-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <span class="menu-price">AED <?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        
                        <?php if (!empty($item['description'])): ?>
                        <p class="menu-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($stock_status): ?>
                        <div class="inventory-badge <?php echo $stock_status; ?>">
                            <i class="bi bi-<?php echo $stock_status == 'out' ? 'x-circle' : 'exclamation-triangle'; ?> me-1"></i>
                            <?php echo $stock_text; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="container py-5">
        <div class="text-center">
            <i class="bi bi-emoji-frown display-1 text-muted mb-3"></i>
            <h3>No Menu Categories Found</h3>
            <p class="text-muted">Please check back later or contact the administrator.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== ORDERING CTA ===== -->
    <section id="order" class="section-padding" style="background: linear-gradient(135deg, var(--color-dark-brown) 0%, var(--color-soft-black) 100%); color: white;">
        <div class="container">
            <div class="text-center">
                <h2 class="display-3 mb-4" style="color: white; font-size: 1.8rem;">Ready to Order?</h2>
                <p class="lead mb-4" style="opacity: 0.9; font-size: 1rem;">
                    Place your order now through WhatsApp or call us directly.
                </p>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px;">
                    <a href="https://wa.me/971503757274" target="_blank" class="btn btn-whatsapp" style="padding: 8px 20px;">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <a href="tel:+971503757274" class="btn btn-secondary" style="padding: 8px 20px;">
                        <i class="bi bi-telephone"></i> Call
                    </a>
                    <a href="contact.php" class="btn btn-outline" style="border-color: white; color: white; padding: 8px 20px;">
                        <i class="bi bi-calendar-check"></i> Reserve
                    </a>
                </div>
                
                <div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: var(--border-radius); max-width: 700px; margin: 0 auto;">
                    <h4 style="color: white; margin-bottom: 15px; font-size: 1.2rem;">Delivery Information</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <div style="text-align: center;">
                            <i class="bi bi-clock" style="font-size: 1.5rem; color: var(--color-red); margin-bottom: 5px;"></i>
                            <p style="margin-bottom: 2px; font-weight: 600; font-size: 0.9rem;">Delivery Time</p>
                            <p style="opacity: 0.8; font-size: 0.85rem;">45-60 min</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-geo-alt" style="font-size: 1.5rem; color: var(--color-red); margin-bottom: 5px;"></i>
                            <p style="margin-bottom: 2px; font-weight: 600; font-size: 0.9rem;">Areas</p>
                            <p style="opacity: 0.8; font-size: 0.85rem;">Al Barsha, Marina, JLT</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <i class="bi bi-truck" style="font-size: 1.5rem; color: var(--color-red); margin-bottom: 5px;"></i>
                            <p style="margin-bottom: 2px; font-weight: 600; font-size: 0.9rem;">Delivery Fee</p>
                            <p style="opacity: 0.8; font-size: 0.85rem;">AED 15 (Free > AED 100)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Smooth scroll for category navigation
    document.addEventListener('DOMContentLoaded', function() {
        const navBtns = document.querySelectorAll('.menu-category-btn');
        
        navBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    const offset = 80; // Adjust for sticky header
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Active category highlight on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.menu-category');
            const navBtns = document.querySelectorAll('.menu-category-btn');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });
            
            navBtns.forEach(btn => {
                btn.classList.remove('active');
                const href = btn.getAttribute('href').substring(1); // Remove #
                if (href === current) {
                    btn.classList.add('active');
                }
            });
        });
    });
    </script>

    <!-- ===== FOOTER ===== -->
    <?php
    include 'includes/footer.php';
    ?>