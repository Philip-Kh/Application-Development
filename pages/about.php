<?php
/**
 * About Us Page
 */
require_once __DIR__ . '/../includes/public_header.php';
?>

<!-- Hero Section -->
<div class="hero-section" style="padding: 3rem 1rem; border-radius: 0;">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">About Us</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Full Company Information</p>
    </div>
</div>

<main class="main-content" style="margin-top: 0;">
    <div class="container">
        <div class="card" style="margin-top: -2rem; position: relative; z-index: 10;">
            <div class="card-body" style="padding: 3rem;">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
                    
                    <!-- About Content -->
                    <div>
                        <h2 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 2rem;">
                            <i class="fas fa-building"></i> Who We Are
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--gray-600); margin-bottom: 1.5rem;">
                            Quick Mart is your trusted partner for high-quality products. We are dedicated to providing the best shopping experience with a wide range of items to meet all your needs. Quality and customer satisfaction are our top priorities.
                        </p>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--gray-600);">
                            Established with a vision to simplify inventory and order management, we strive to deliver excellence in every transaction.
                        </p>
                    </div>

                    <!-- Contact Content -->
                    <div style="background: var(--gray-100); padding: 2rem; border-radius: var(--radius-xl);">
                        <h2 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 2rem;">
                            <i class="fas fa-headset"></i> Contact Us
                        </h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem; box-shadow: var(--shadow-sm);">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <small style="color: var(--gray-500); display: block; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Contact Person</small>
                                    <strong style="font-size: 1.2rem; color: var(--dark);">PHILIPPE</strong>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem; box-shadow: var(--shadow-sm);">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <small style="color: var(--gray-500); display: block; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Email Address</small>
                                    <strong style="font-size: 1.2rem; color: var(--dark); word-break: break-all;">20017840@STD.LTUC.EDU.JO</strong>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem; box-shadow: var(--shadow-sm);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <small style="color: var(--gray-500); display: block; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Location</small>
                                    <strong style="font-size: 1.2rem; color: var(--dark);">Amman, Jordan</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
