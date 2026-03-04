<?php
/**
 * Skin Perfect Clinic - Main PHP Application
 * Converts static HTML to dynamic PHP with database content
 */
require_once 'config/database.php';

// Get clinic information with defaults
$clinic = getResult("SELECT * FROM clinic_info LIMIT 1");

if (!is_array($clinic) || empty($clinic)) {
    $clinic = [
        'clinic_name' => 'Skin Perfect Clinic',
        'phone' => '09490903999',
        'email' => 'info@skinperfectclinic.com',
        'address' => 'Old Club Road, Beside Yoda Diagnostic Lab Kothapeta',
        'city' => 'Guntur',
        'state' => 'Andhra Pradesh',
        'zip_code' => '522001',
        'experience_years' => 8,
        'rating' => 4.9,
        'total_reviews' => 550,
        'working_hours_open' => '09:00',
        'working_hours_close' => '17:00',
        'description' => 'Skin Perfect Clinic has been serving patients in Kothapeta, Guntur for over 8 years in healthcare. We specialize in treating acne, pigmentation, cysts, hair fall, nail disorders, and cosmetic skin procedures.'
    ];
}

$services_categories = executeQuery("SELECT DISTINCT category FROM services WHERE status = 'active' ORDER BY CASE WHEN category='Medical Dermatology' THEN 1 WHEN category='Hair Treatments' THEN 2 ELSE 3 END");
if (!$services_categories) $services_categories = [];

$reviews = executeQuery("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
if (!$reviews) $reviews = [];

$doctor = getResult("SELECT * FROM doctors WHERE status = 'active' LIMIT 1");
if (!$doctor) {
    $doctor = [
        'name' => 'Dr. Sindhura Manne',
        'qualification' => 'MBBS, MD (DVL)',
        'specialization' => 'Dermatology & Cosmetology',
        'experience_years' => 8,
        'bio' => 'Dr. Sindhura Manne is a highly esteemed Dermatologist and Cosmetologist with over 8 years of extensive clinical experience. Dedicated to providing personalized and compassionate care, she specializes in treating a wide array of skin, hair, and nail conditions. Her practice utilizes state-of-the-art modern technology and evidence-based treatments tailored to meet every patient\'s unique aesthetic and medical goals.',
        'image_path' => 'images/profile_dp.png',
        'contact_phone' => '09490903999'
    ];
}

$gallery_items = executeQuery("SELECT * FROM gallery WHERE status = 'active' ORDER BY sort_order ASC");
if (!$gallery_items) $gallery_items = [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($clinic['clinic_name']); ?> - Guntur</title>
    <meta name="description"
        content="Advanced Dermatology & Cosmetology Care in Guntur. Skin Perfect Clinic offers high-quality dermatology and cosmetology treatments.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#home" class="logo">
                <i class="fa-solid fa-leaf text-primary"></i>
                Skin Perfect <span class="font-light">Clinic</span>
            </a>

            <button class="mobile-toggle" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <ul class="nav-links">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About Us</a></li>
                <li><a href="#services" class="nav-link">Services</a></li>
                <li><a href="#doctor" class="nav-link">Doctor</a></li>
                <li><a href="#gallery" class="nav-link">Gallery</a></li>
                <li><a href="#reviews" class="nav-link">Reviews</a></li>
                <li><a href="#contact" class="nav-link nav-btn">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main id="app-content">
        <!-- Home View -->
        <section id="home-view" class="view active-view">
            <!-- Hero -->
            <header class="hero">
                <div class="container grids-2 items-center" style="position: relative; z-index: 1;">
                    <div class="hero-content fade-in-up">
                        <span class="badge">#1 Skin Clinic in Guntur</span>
                        <h1>Advanced Dermatology & <br><span class="text-primary">Cosmetology Care</span></h1>
                        <p>Skin Perfect Clinic offers high-quality dermatology and cosmetology treatments. We provide
                            expert care for skin, hair, and nail problems with modern technology and personalized treatment
                            plans.
                        </p>
                        <div class="hero-actions">
                            <a href="tel:<?php echo htmlspecialchars($clinic['phone']); ?>" class="btn btn-primary"><i class="fa-solid fa-phone"></i> Call:
                                <?php echo htmlspecialchars($clinic['phone']); ?></a>
                            <a href="#contact" class="btn btn-outline"><i class="fa-solid fa-calendar-check"></i> Book
                                Appointment</a>
                        </div>
                    </div>
                    <div class="hero-image slide-in-right text-center">
                        <img src="<?php echo htmlspecialchars($doctor['image_path'] ?? 'images/profile_dp.png'); ?>"
                            alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="hero-doctor-img">
                    </div>
                </div>
            </header>

            <!-- Quick About -->
            <section class="section quick-about">
                <div class="container grids-2">
                    <div class="about-text slide-in-left">
                        <h2><?php echo htmlspecialchars($clinic['experience_years']); ?>+ Years of <span class="text-primary">Excellence</span></h2>
                        <p><?php echo htmlspecialchars($clinic['description']); ?></p>
                        <div class="stats">
                            <div class="stat-item">
                                <i class="fa-solid fa-star text-accent"></i>
                                <h3><?php echo htmlspecialchars($clinic['rating']); ?></h3>
                                <span>Rating</span>
                            </div>
                            <div class="stat-item">
                                <i class="fa-solid fa-users text-primary"></i>
                                <h3><?php echo htmlspecialchars($clinic['total_reviews']); ?>+</h3>
                                <span>Reviews</span>
                            </div>
                        </div>
                    </div>
                    <div class="about-features slide-in-right">
                        <ul class="feature-list">
                            <li><i class="fa-solid fa-check-circle"></i> Experienced Dermatologist</li>
                            <li><i class="fa-solid fa-check-circle"></i> Advanced Equipment</li>
                            <li><i class="fa-solid fa-check-circle"></i> Modern Technology</li>
                            <li><i class="fa-solid fa-check-circle"></i> Affordable Pricing</li>
                        </ul>
                        <a href="#about" class="btn btn-primary mt-4">Learn More About Us</a>
                    </div>
                </div>
            </section>

            <!-- Services Preview -->
            <section class="section bg-light">
                <div class="container">
                    <div class="section-title text-center">
                        <h2>Our <span class="text-primary">Services</span></h2>
                        <p>Comprehensive care for your skin, hair, and cosmetic needs.</p>
                    </div>
<div class="grids-3 mt-5" id="home-services-container">
<?php
if (!empty($services_categories)) {
    $homeServices = executeQuery("SELECT * FROM services WHERE status='active' ORDER BY sort_order ASC LIMIT 3");
    if (!empty($homeServices)) {
        foreach ($homeServices as $index => $service) {
            $delay = $index * 0.1;
            echo "<div class='card service-card fade-in-up' style='animation-delay: {$delay}s'>
                    <div class='card-icon'><i class='fa-solid " . htmlspecialchars($service['icon_class'] ?? 'fa-stethoscope') . "'></i></div>
                    <h3>" . htmlspecialchars($service['name']) . "</h3>
                    <p>" . htmlspecialchars($service['description']) . "</p>
                  </div>";
        }
    } else {
        echo "<p>No services found.</p>";
    }
} else {
    echo "<p>No services found.</p>";
}
?>
</div>
                    <div class="text-center mt-5"><a href="#services" class="btn btn-outline nav-link">View All Services</a>
                        
                    </div>
                </div>
            </section>

            <!-- Why Choose Us -->
            <section class="section why-choose">
                <div class="container text-center">
                    <h2 class="section-title mb-5">Why Choose <span class="text-primary">Skin Perfect Clinic?</span>
                    </h2>
                    <div class="grids-4">
                        <div class="feature-box">
                            <i class="fa-solid fa-user-doctor"></i>
                            <h4>Experienced Specialist</h4>
                        </div>
                        <div class="feature-box">
                            <i class="fa-solid fa-wallet"></i>
                            <h4>Affordable Pricing</h4>
                        </div>
                        <div class="feature-box">
                            <i class="fa-solid fa-microscope"></i>
                            <h4>Modern Equipment</h4>
                        </div>
                        <div class="feature-box">
                            <i class="fa-solid fa-face-smile"></i>
                            <h4>High Patient Satisfaction</h4>
                        </div>
                    </div>
                </div>
            </section>
        </section> <!-- End Home View -->

        <!-- About View -->
        <section id="about-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>About <span class="text-primary">Us</span></h1>
                    <p>Discover our mission, vision, and the team behind Skin Perfect Clinic.</p>
                </div>
            </header>
            <section class="section">
                <div class="container grids-2">
                    <div class="about-info">
                        <h2>Welcome to <span class="text-primary"><?php echo htmlspecialchars($clinic['clinic_name'] ?? 'Skin Perfect Clinic'); ?></span></h2>
                        <p><?php echo htmlspecialchars($clinic['description'] ?? 'Quality dermatology care.'); ?></p>
                        <ul class="custom-list mt-4">
                            <li><strong>Skin Problems:</strong> From acne to psoriasis, we treat it all.</li>
                            <li><strong>Hair Problems:</strong> Comprehensive treatments for hair loss.</li>
                            <li><strong>Nail Disorders:</strong> Expert care for infections and abnormalities.</li>
                            <li><strong>Cosmetic Concerns:</strong> Aesthetic treatments to enhance natural beauty.</li>
                        </ul>
                    </div>
                    <div class="vision-box bg-primary text-white p-5 rounded">
                        <i class="fa-solid fa-eye text-accent text-4xl mb-3"></i>
                        <h3>Our Vision</h3>
                        <p class="mt-3">To become the most trusted skin clinic in <?php echo htmlspecialchars($clinic['city'] ?? 'Guntur'); ?> by delivering safe, effective,
                            and affordable treatments with exceptional patient care and cutting-edge technology.</p>
                    </div>
                </div>
            </section>
        </section>

        <!-- Services View -->
        <section id="services-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>Our <span class="text-primary">Services</span></h1>
                    <p>Comprehensive care for your skin, hair, and cosmetic needs.</p>
                </div>
            </header>
            <section class="section">
                <div class="container" id="all-services-container">
<?php
if (!empty($services_categories)) {
    $allServices = executeQuery("SELECT * FROM services WHERE status='active' ORDER BY sort_order ASC");
    if (!empty($allServices)) {
        $current_category = '';
        foreach ($allServices as $service) {
            if ($current_category != $service['category']) {
                if ($current_category != '') {
                    echo "</div></div>";
                }
                echo "<div class='service-category mb-5'>";
                echo "<h2 class='category-title'><i class='fa-solid fa-stethoscope'></i> " . htmlspecialchars($service['category']) . "</h2>";
                echo "<div class='grids-3 mt-4'>";
                $current_category = $service['category'];
            }
            echo "<div class='card service-card fade-in-up'>
                    <h3>" . htmlspecialchars($service['name']) . "</h3>
                    <p>" . htmlspecialchars($service['description']) . "</p>
                  </div>";
        }
        echo "</div></div>";
    } else {
        echo "<p>No services found.</p>";
    }
} else {
    echo "<p>No services found.</p>";
}
?>
                    <!-- Services loaded dynamically via JavaScript -->
                </div>
            </section>
        </section>

        <!-- Doctor View -->
        <section id="doctor-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>Meet Our <span class="text-primary">Specialist</span></h1>
                </div>
            </header>
            <section class="section">
                <div class="container">
                    <div class="doctor-profile card grids-2 p-0 overflow-hidden shadow-lg" id="doctor-container">
                        <!-- Doctor loaded dynamically via JavaScript -->
                    </div>
                </div>
            </section>
        </section>

        <!-- Gallery View -->
        <section id="gallery-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>Clinic <span class="text-primary">Gallery</span></h1>
                    <p>Take a tour of our modern facilities and state-of-the-art equipment.</p>
                </div>
            </header>
            <section class="section">
                <div class="container">
                    <div class="gallery-grid" id="gallery-container">
                        <!-- Gallery loaded dynamically via JavaScript -->
                    </div>
                </div>
            </section>
        </section>

        <!-- Reviews View -->
        <section id="reviews-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>Patient <span class="text-primary">Testimonials</span></h1>
                    <p>See why over <?php echo htmlspecialchars($clinic['total_reviews']); ?>+ patients trust <?php echo htmlspecialchars($clinic['clinic_name']); ?>.</p>
                </div>
            </header>
            <section class="section bg-light">
                <div class="container">
                    <div class="review-stats text-center mb-5">
                        <div class="text-5xl text-accent font-bold"><?php echo htmlspecialchars($clinic['rating']); ?></div>
                        <div class="stars text-accent text-2xl my-2">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <p class="text-muted">Based on <?php echo htmlspecialchars($clinic['total_reviews']); ?>+ verified reviews</p>
                    </div>

                    <div class="grids-3 mt-5" id="reviews-container">
                        <!-- Reviews loaded dynamically via JavaScript -->
                    </div>
                </div>
            </section>
        </section>

        <!-- Contact View -->
        <section id="contact-view" class="view hidden">
            <header class="page-header">
                <div class="container">
                    <h1>Get In <span class="text-primary">Touch</span></h1>
                    <p>Book an appointment or visit our clinic today.</p>
                </div>
            </header>
            <section class="section">
                <div class="container grids-2">
                    <div class="contact-info">
                        <div class="info-box mb-4 flex items-start">
                            <div class="icon-wrap text-2xl text-primary mr-4 mt-1"><i
                                    class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <h3>Visit Us</h3>
                                <p class="text-muted"><?php echo htmlspecialchars($clinic['address']); ?><br><?php echo htmlspecialchars($clinic['city']); ?>,
                                    <?php echo htmlspecialchars($clinic['state']); ?> – <?php echo htmlspecialchars($clinic['zip_code']); ?></p>
                            </div>
                        </div>
                        <div class="info-box mb-4 flex items-start">
                            <div class="icon-wrap text-2xl text-primary mr-4 mt-1"><i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <h3>Call Us</h3>
                                <p class="text-muted text-xl font-bold mt-1"><?php echo htmlspecialchars($clinic['phone']); ?></p>
                            </div>
                        </div>
                        <div class="info-box flex items-start">
                            <div class="icon-wrap text-2xl text-primary mr-4 mt-1"><i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <h3>Working Hours</h3>
                                <p class="text-muted mt-1">Monday – Saturday: <?php echo htmlspecialchars($clinic['working_hours_open']); ?> – <?php echo htmlspecialchars($clinic['working_hours_close']); ?><br>Sunday: <span
                                        class="text-danger font-medium">Closed</span></p>
                            </div>
                        </div>

                        <!-- Map Placeholder -->
                        <div class="map-container mt-5 rounded overflow-hidden shadow">
                            <iframe src="https://www.google.com/maps?q=<?php echo urlencode($clinic['address']); ?>,<?php echo urlencode($clinic['city']); ?>&output=embed"
                                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                        </div>
                    </div>

                    <div class="contact-form-container card shadow-lg">
                        <h2 class="mb-4 text-primary">Book an Appointment</h2>
                        <form id="booking-form" class="appointment-form">
                            <div class="form-group mb-3">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" class="form-control" placeholder="Your Phone Number"
                                    required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" class="form-control" placeholder="Your Email">
                            </div>
                            <div class="form-group mb-3">
                                <label for="appointment_date">Preferred Date</label>
                                <input type="date" id="appointment_date" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="appointment_time">Preferred Time</label>
                                <select id="appointment_time" class="form-control" required>
                                    <option value="">Select a time slot</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="consultation_mode">Consultation Mode</label>
                                <select id="consultation_mode" class="form-control" required>
                                    <option value="offline">Offline (In-Clinic)</option>
                                    <option value="online">Online (Video Consultation)</option>
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label for="message">Message / Reason for Visit</label>
                                <textarea id="message" rows="4" class="form-control"
                                    placeholder="How can we help you?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-lg shadow-md hover-lift">Confirm
                                Appointment <i class="fa-solid fa-paper-plane ml-2"></i></button>
                            <div id="form-message" style="display:none; margin-top: 15px; padding: 10px; border-radius: 5px;"></div>
                        </form>
                    </div>
                </div>
            </section>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-3">
        <div class="container grids-3 mb-4">
            <div class="footer-brand">
                <h3 class="flex items-center text-2xl mb-3"><i class="fa-solid fa-leaf text-primary mr-2"></i> <?php echo htmlspecialchars($clinic['clinic_name'] ?? 'Skin Perfect Clinic'); ?></h3>
                <p class="text-muted-light">Advanced Dermatology & Cosmetology Care in <?php echo htmlspecialchars($clinic['city'] ?? 'Guntur'); ?>. Bringing confidence
                    through healthy skin.</p>
            </div>
            <div class="footer-links">
                <h4 class="mb-3">Quick Links</h4>
                <ul class="clean-list">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#reviews">Reviews</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4 class="mb-3">Connect</h4>
                <a href="tel:<?php echo htmlspecialchars($clinic['phone'] ?? '09490903999'); ?>" class="text-white hover-primary block mb-2"><i
                        class="fa-solid fa-phone mr-2"></i> <?php echo htmlspecialchars($clinic['phone'] ?? '09490903999'); ?></a>
                <a href="https://maps.app.goo.gl/Lzo8UdJC5Jt3fxZx7" target="_blank"
                    class="text-white hover-primary block mb-3">
                    <i class="fa-solid fa-location-dot mr-2"></i> <?php echo htmlspecialchars($clinic['city'] ?? 'Guntur'); ?>, <?php echo htmlspecialchars($clinic['state'] ?? 'Andhra Pradesh'); ?></a>
                <div class="social-links flex mt-3">
                    <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="container border-top pt-3 text-center text-muted-light text-sm">
            <p>&copy; 2026 <?php echo htmlspecialchars($clinic['clinic_name'] ?? 'Skin Perfect Clinic'); ?>. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Fixed WhatsApp Button -->
    <a href="https://wa.me/<?php echo urlencode(preg_replace('/[^\d]/', '', $clinic['phone'])); ?>" class="whatsapp-btn shadow-lg" target="_blank" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <script src="script.js"></script>
</body>

</html>
