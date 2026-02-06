<?php

// Check if user is logged in
$error_message = '';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    $error_message = 'Unauthorized access. Please log in.';
}

// Get testimonial ID
if (empty($error_message)) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $error_message = 'Invalid testimonial ID.';
    }
}

$testimonial = null;
$testimonial_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (empty($error_message)) {
    // Fetch testimonial data
    $testimonial_query = "SELECT * FROM testimonials WHERE id = ?";
    $testimonial_stmt = $connection->prepare($testimonial_query);
    $testimonial_stmt->bind_param("i", $testimonial_id);
    $testimonial_stmt->execute();
    $testimonial_result = $testimonial_stmt->get_result();
    if ($testimonial_result->num_rows === 0) {
        $error_message = 'Testimonial not found.';
    } else {
        $testimonial = $testimonial_result->fetch_assoc();
    }
    $testimonial_stmt->close();
}
?>


<div class="main-content">
    <div class="container-fluid">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger my-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif ($testimonial): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Testimonial Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="testimonials.php">Testimonials</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($testimonial['customer_name']); ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="testimonials.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <a href="testimonials.php?source=edit_testimonial&id=<?php echo $testimonial_id; ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" 
                            onclick="testimonialShowDeleteConfirm(
                                <?php echo $testimonial_id; ?>,
                                '<?php echo htmlspecialchars(addslashes($testimonial['customer_name']), ENT_QUOTES); ?>'
                            )">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>

            <!-- Testimonial Profile Card -->
            <div class="row mb-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <?php if (!empty($testimonial['customer_image'])): ?>
                                <img src="../uploads/testimonials/<?php echo htmlspecialchars($testimonial['customer_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($testimonial['customer_name']); ?>"
                                     class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['customer_name']); ?>&background=random&size=150'">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person display-1 text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h3><?php echo htmlspecialchars($testimonial['customer_name']); ?></h3>
                            
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <?php if ($testimonial['is_approved']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Approved
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Rating Display -->
                            <div class="mb-3">
                                <?php
                                $rating = $testimonial['rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $rating):
                                        echo '<i class="bi bi-star-fill text-warning fs-4"></i>';
                                    else:
                                        echo '<i class="bi bi-star text-muted fs-4"></i>';
                                    endif;
                                endfor;
                                ?>
                                <span class="fw-bold ms-2 fs-4"><?php echo $rating; ?>/5</span>
                            </div>
                            
                            <div class="text-start">
                                <p><strong>Submitted:</strong> <?php echo date('F d, Y', strtotime($testimonial['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <!-- Review Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-chat-left-quote me-2"></i>Customer Review</h5>
                        </div>
                        <div class="card-body">
                            <div class="testimonial-review" style="white-space: pre-wrap;">
                                <?php echo htmlspecialchars($testimonial['review']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <?php if (!$testimonial['is_approved']): ?>
                                        <button class="btn btn-outline-success w-100" 
                                                onclick="toggleTestimonialApproval(<?php echo $testimonial_id; ?>, 1)">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary w-100" 
                                                onclick="toggleTestimonialApproval(<?php echo $testimonial_id; ?>, 0)">
                                            <i class="bi bi-x-lg me-1"></i> Unapprove
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-primary w-100" 
                                            onclick="copyToClipboard('<?php echo addslashes(htmlspecialchars($testimonial['review'])); ?>')">
                                        <i class="bi bi-clipboard me-1"></i> Copy Review
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-info w-100" 
                                       onclick="shareTestimonial(<?php echo $testimonial_id; ?>)">
                                        <i class="bi bi-share me-1"></i> Share
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        testimonialShowSuccess('Review copied to clipboard');
    }).catch(() => {
        testimonialShowError('Failed to copy review');
    });
}

function shareTestimonial(testimonialId) {
    // This function can be extended to share on social media
    const url = window.location.origin + '/testimonials.php?source=view_testimonial&id=' + testimonialId;
    if (navigator.share) {
        navigator.share({
            title: 'Customer Testimonial',
            text: 'Check out this customer testimonial!',
            url: url
        });
    } else {
        copyToClipboard(url);
        testimonialShowSuccess('Link copied to clipboard');
    }
}
</script>