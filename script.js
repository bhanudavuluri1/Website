/**
 * Skin Perfect Clinic - Main JavaScript
 * Updated for dynamic content fetching from PHP APIs
 * Handles SPA Hash Routing, Animations, and UI interactions
 */

const API_BASE = './api/';

// Fetch data from API
async function fetchFromAPI(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Error fetching data' };
    }
}

// POST request to API
async function postToAPI(endpoint, payload) {
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Error submitting data' };
    }
}

// Load Home Page Services
async function loadHomeServices() {
    const container = document.getElementById('home-services-container');
    if (!container) return;

    const result = await fetchFromAPI('get_services.php');
    if (result.success && result.data && result.data.length > 0) {
        // Get first 3 services
        const homeServices = result.data.slice(0, 3);
        container.innerHTML = homeServices.map((service, index) => `
            <div class="card service-card fade-in-up" style="animation-delay: ${index * 0.1}s">
                <div class="card-icon"><i class="fa-solid ${service.icon_class}"></i></div>
                <h3>${escapeHtml(service.name)}</h3>
                <p>${escapeHtml(service.description)}</p>
            </div>
        `).join('');
    }
}

// Load All Services by Category
async function loadAllServices() {
    const container = document.getElementById('all-services-container');
    if (!container) return;

    const result = await fetchFromAPI('get_services.php');
    if (result.success && result.data.length > 0) {
        // Group services by category
        const grouped = {};
        result.data.forEach(service => {
            if (!grouped[service.category]) {
                grouped[service.category] = [];
            }
            grouped[service.category].push(service);
        });

        // Build categories HTML
        const categoryOrder = {
            'Medical Dermatology': 0,
            'Hair Treatments': 1,
            'Cosmetic Dermatology': 2
        };

        const categoryIcons = {
            'Medical Dermatology': 'fa-stethoscope',
            'Hair Treatments': 'fa-scissors',
            'Cosmetic Dermatology': 'fa-sparkles'
        };

        let html = '';
        Object.keys(grouped)
            .sort((a, b) => (categoryOrder[a] || 999) - (categoryOrder[b] || 999))
            .forEach(category => {
                html += `
                    <div class="service-category mb-5">
                        <h2 class="category-title">
                            <i class="fa-solid ${categoryIcons[category]}"></i> ${escapeHtml(category)}
                        </h2>
                        <div class="grids-3 mt-4">
                            ${grouped[category].map(service => `
                                <div class="card service-card">
                                    <h3>${escapeHtml(service.name)}</h3>
                                    <p>${escapeHtml(service.description)}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

        container.innerHTML = html;
    }
}

// Load Doctor Profile
async function loadDoctor() {
    const container = document.getElementById('doctor-container');
    if (!container) return;

    const result = await fetchFromAPI('get_doctor.php');
    if (result.success && result.data) {
        const doctor = result.data;
        container.innerHTML = `
            <div class="doctor-img flex-center">
                <img src="${escapeHtml(doctor.image_path)}" alt="${escapeHtml(doctor.name)}" class="doctor-photo">
            </div>
            <div class="doctor-details p-5">
                <h2 class="text-primary mb-2">${escapeHtml(doctor.name)}</h2>
                <h4 class="text-muted mb-4">${escapeHtml(doctor.qualification)}</h4>
                <div class="specialization mb-4">
                    <strong>Specialization:</strong> ${escapeHtml(doctor.specialization)} <br>
                    <strong>Experience:</strong> ${doctor.experience_years}+ Years
                </div>
                <p>${escapeHtml(doctor.bio)}</p>
                <a href="#contact" class="btn btn-primary mt-4">Consult ${escapeHtml(doctor.name.split(' ')[1])}</a>
            </div>
        `;
    }
}

// Load Reviews
async function loadReviews() {
    const container = document.getElementById('reviews-container');
    if (!container) return;

    const result = await fetchFromAPI('get_reviews.php');
    if (result.success && result.data.length > 0) {
        container.innerHTML = result.data.map(review => `
            <div class="card review-card">
                <div class="stars text-accent mb-3">
                    ${Array(review.rating).fill('<i class="fa-solid fa-star"></i>').join('')}
                </div>
                <p class="review-text">"${escapeHtml(review.review_text)}"</p>
                <h4 class="mt-4 text-primary">- ${escapeHtml(review.patient_name)}</h4>
            </div>
        `).join('');
    }
}

// Load Gallery
async function loadGallery() {
    const container = document.getElementById('gallery-container');
    if (!container) return;

    const result = await fetchFromAPI('get_gallery.php');
    if (result.success && result.data.length > 0) {
        container.innerHTML = result.data.map(item => `
            <div class="gallery-item card text-center p-5">
                ${item.image_path ? `<img src="${escapeHtml(item.image_path)}" alt="${escapeHtml(item.title)}" style="max-height: 200px; margin-bottom: 15px;">` : `<i class="fa-solid fa-image text-muted text-4xl mb-3"></i>`}
                <h3>${escapeHtml(item.title)}</h3>
                <p class="text-muted text-sm mt-2">${escapeHtml(item.description || '')}</p>
            </div>
        `).join('');
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Navbar Scroll Effect & Mobile Menu ---
    const navbar = document.querySelector('.navbar');
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinksContainer = document.querySelector('.nav-links');
    const navLinks = document.querySelectorAll('.nav-link');

    // Scroll styling
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile Menu Toggle
    mobileToggle.addEventListener('click', () => {
        navLinksContainer.classList.toggle('active');
        const icon = mobileToggle.querySelector('i');
        if (navLinksContainer.classList.contains('active')) {
            icon.classList.replace('fa-bars', 'fa-xmark');
        } else {
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    });

    // Close mobile menu when link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinksContainer.classList.remove('active');
            mobileToggle.querySelector('i').classList.replace('fa-xmark', 'fa-bars');
        });
    });


    // --- 2. Single Page Application (Hash Routing) ---
    const views = document.querySelectorAll('.view');

    async function handleRouting() {
        // Get hash from URL, default to 'home'
        let hash = window.location.hash.replace('#', '') || 'home';

        // Ensure valid view, otherwise fallback to home
        const targetView = document.getElementById(`${hash}-view`);
        if (!targetView) {
            hash = 'home';
        }

        // Hide all views, display target view
        views.forEach(view => {
            view.classList.remove('active-view');
            setTimeout(() => view.classList.add('hidden'), 300);
            view.classList.add('hidden');
        });

        const activeView = document.getElementById(`${hash}-view`);
        if (activeView) {
            activeView.classList.remove('hidden');
            // Trigger reflow for animation
            void activeView.offsetWidth;
            activeView.classList.add('active-view');
        }

        // IMPORTANT: Unobserve and Re-observe all elements when view changes
        const animatedElements = document.querySelectorAll('.fade-in-up, .slide-in-left, .slide-in-right, .zoom-in');
        animatedElements.forEach(el => el.classList.remove('visible'));

        // Update active nav link
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${hash}`) {
                link.classList.add('active');
            }
        });

        // Scroll to top when view changes
        window.scrollTo({ top: 0, behavior: 'instant' });

        // Load data for specific views
        if (hash === 'services') {
            await loadAllServices();
        } else if (hash === 'doctor') {
            await loadDoctor();
        } else if (hash === 'gallery') {
            await loadGallery();
        } else if (hash === 'reviews') {
            await loadReviews();
        } else if (hash === 'home') {
            const homeContainer = document.getElementById('home-services-container');
            if (homeContainer && homeContainer.children.length === 0) {
                await loadHomeServices();
            } else {
                await loadHomeServices();
            }
        }

        // Re-trigger animations
        initAnimations();
    }

    // Listen for hash changes
    window.addEventListener('hashchange', handleRouting);
    // Initial load route check
    handleRouting();


    // --- 3. Scroll Animations (Intersection Observer) ---
    function initAnimations() {
        const animatedElements = document.querySelectorAll('.fade-in-up, .slide-in-left, .slide-in-right, .zoom-in');

        // Reset element visibility classes to re-trigger
        animatedElements.forEach(el => el.classList.remove('visible'));

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target); // Animate only once
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        animatedElements.forEach(el => observer.observe(el));
    }


    // --- 3.5 Appointment Slot Management with Booking Conflict Detection ---
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const phoneInput = document.getElementById('phone');
    const messageEl = document.getElementById('form-message');

    if (dateInput && phoneInput) {
        // Set minimum date to today and maximum to today + 10 days
        const today = new Date();
        const minDateStr = today.toISOString().split('T')[0];
        dateInput.setAttribute('min', minDateStr);

        const maxDate = new Date(today);
        maxDate.setDate(today.getDate() + 10);
        const maxDateStr = maxDate.toISOString().split('T')[0];
        dateInput.setAttribute('max', maxDateStr);

        // Load time slots when date is selected
        dateInput.addEventListener('change', async () => {
            const selectedDate = dateInput.value;
            const phone = phoneInput.value.trim();

            if (!selectedDate) {
                timeSelect.innerHTML = '<option value="">Select a time slot</option>';
                if (messageEl) messageEl.style.display = 'none';
                return;
            }

            // Check if selected date is Sunday (day 0)
            const dateObj = new Date(selectedDate + 'T00:00:00');
            if (dateObj.getDay() === 0) {
                alert('We are closed on Sundays. Please select another day.');
                dateInput.value = '';
                timeSelect.innerHTML = '<option value="">Select a time slot</option>';
                if (messageEl) messageEl.style.display = 'none';
                return;
            }

            // If phone is provided, check for existing bookings
            if (phone) {
                const checkResult = await fetchFromAPI(`check_booking.php?phone=${encodeURIComponent(phone)}&date=${selectedDate}`);

                if (checkResult.has_booking) {
                    // Patient already has a booking on this date
                    if (messageEl) {
                        messageEl.style.display = 'block';
                        messageEl.className = 'alert alert-error';
                        messageEl.style.backgroundColor = '#FEE2E2';
                        messageEl.style.color = '#991B1B';
                        messageEl.style.border = '1px solid #F87171';
                        messageEl.innerHTML = `<strong>⚠️ Already Booked!</strong> You already have an appointment on ${selectedDate} at ${checkResult.existing_time} (${checkResult.status}). Please select a different date or call us to reschedule.`;
                    }

                    timeSelect.innerHTML = '<option value="">Not available - Already booked</option>';
                    timeSelect.disabled = true;
                    return;
                } else {
                    // Clear message if phone check passed
                    if (messageEl && messageEl.innerHTML.includes('Already Booked')) {
                        messageEl.style.display = 'none';
                    }
                    timeSelect.disabled = false;
                }
            }

            // Fetch available slots
            const result = await fetchFromAPI(`get_slots.php?date=${selectedDate}`);

            if (result.success && result.data.length > 0) {
                timeSelect.innerHTML = '<option value="">Select a time slot</option>';
                result.data.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = slot;
                    timeSelect.appendChild(option);
                });
            } else {
                timeSelect.innerHTML = '<option value="">No slots available</option>';
            }
        });
    }


    // --- 4. Form Submission to Backend ---
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();
            const appointmentDate = document.getElementById('appointment_date').value;
            const appointmentTime = document.getElementById('appointment_time').value;
            const consultationMode = document.getElementById('consultation_mode').value;
            const message = document.getElementById('message').value.trim();
            const btn = bookingForm.querySelector('button');
            const messageEl = document.getElementById('form-message');
            const originalText = btn.innerHTML;

            // Validate date and time
            if (!appointmentDate || !appointmentTime) {
                messageEl.style.display = 'block';
                messageEl.className = 'alert alert-error';
                messageEl.style.backgroundColor = '#FEE2E2';
                messageEl.style.color = '#991B1B';
                messageEl.style.border = '1px solid #F87171';
                messageEl.innerHTML = '<strong>Error:</strong> Please select both date and time for the appointment.';
                return;
            }

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            // Send appointment to backend
            const result = await postToAPI('book_appointment.php', {
                patient_name: name,
                phone: phone,
                email: email,
                appointment_date: appointmentDate,
                appointment_time: appointmentTime,
                consultation_mode: consultationMode,
                message: message
            });

            setTimeout(() => {
                if (result.success) {
                    messageEl.style.display = 'block';
                    messageEl.className = 'alert alert-success';
                    messageEl.style.backgroundColor = '#D1FAE5';
                    messageEl.style.color = '#065F46';
                    messageEl.style.border = '1px solid #6EE7B7';
                    messageEl.innerHTML = '<strong>Success!</strong> ' + result.message;

                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Request Sent';
                    btn.classList.replace('btn-primary', 'btn-success');
                    btn.style.backgroundColor = '#10B981';
                    bookingForm.reset();

                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.replace('btn-success', 'btn-primary');
                        btn.style.backgroundColor = '';
                        btn.style.opacity = '1';
                        btn.disabled = false;
                        messageEl.style.display = 'none';
                    }, 3000);
                } else {
                    messageEl.style.display = 'block';
                    messageEl.className = 'alert alert-error';
                    messageEl.style.backgroundColor = '#FEE2E2';
                    messageEl.style.color = '#7F1D1D';
                    messageEl.style.border = '1px solid #FCA5A5';
                    messageEl.innerHTML = '<strong>Error!</strong> ' + result.message;

                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }
            }, 1500);
        });
    }
});
