// ===================================
// Modern Notification System
// ===================================
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    
    const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
                 type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : 
                 '<i class="fas fa-info-circle"></i>';
    
    notification.innerHTML = `
        ${icon}
        <div class="notification-content">
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// ===================================
// Registration Form Modal
// ===================================
function openRegistrationForm(type) {
    const modal = document.getElementById('registrationModal');
    const modalTitle = document.getElementById('modalTitle');
    const delegateType = document.getElementById('delegateType');
    const hotelPreference = document.getElementById('hotelPreference');
    const dietaryReq = document.getElementById('dietaryReq');
    
    delegateType.value = type;
    
    if (type === 'local') {
        modalTitle.textContent = 'Local Delegate Registration - ₦40,000';
        hotelPreference.style.display = 'none';
        dietaryReq.style.display = 'none';
    } else {
        modalTitle.textContent = 'Foreign Delegate Registration - $300';
        hotelPreference.style.display = 'block';
        dietaryReq.style.display = 'block';
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRegistrationForm() {
    const modal = document.getElementById('registrationModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    document.getElementById('registrationForm').reset();
}

// Handle registration form submission
function initRegistrationForm() {
    const registrationForm = document.getElementById('registrationForm');
    
    registrationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(registrationForm);
        const data = Object.fromEntries(formData.entries());
        
        // Disable submit button
        const submitBtn = registrationForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, 'success');
                closeRegistrationForm();
            } else {
                const errorMsg = result.errors ? 
                    `${result.message}<br><small>${result.errors.join('<br>')}</small>` : 
                    result.message;
                showNotification(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error submitting registration. Please check your connection and try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
    
    // Close modal when clicking outside
    const modal = document.getElementById('registrationModal');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeRegistrationForm();
        }
    });
}

// ===================================
// Countdown Timer
// ===================================
function initCountdown() {
    const eventDate = new Date('April 24, 2026 09:00:00').getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const timeLeft = eventDate - now;
        
        if (timeLeft < 0) {
            document.getElementById('countdown').innerHTML = '<p class="event-live">Event is Live!</p>';
            return;
        }
        
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
        
        document.getElementById('days').textContent = String(days).padStart(3, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// ===================================
// Smooth Scrolling
// ===================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const navHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = target.offsetTop - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navMenu = document.getElementById('navMenu');
                const hamburger = document.getElementById('hamburger');
                if (navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                }
            }
        });
    });
}

// ===================================
// Mobile Navigation Toggle
// ===================================
function initMobileNav() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        }
    });
}

// ===================================
// Navbar Scroll Effect
// ===================================
function initNavbarScroll() {
    const navbar = document.getElementById('navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// ===================================
// Form Submission Handler
// ===================================
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(contactForm);
        const data = Object.fromEntries(formData.entries());
        
        // Disable submit button
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        
        try {
            const response = await fetch('api/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, 'success');
                contactForm.reset();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error sending message. Please try again later.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
}

// ===================================
// Scroll Animations (Intersection Observer)
// ===================================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements
    const animateElements = document.querySelectorAll('.topic-card, .speaker-card, .pricing-card, .stat-item');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// ===================================
// Active Navigation Link
// ===================================
function initActiveNavigation() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    window.addEventListener('scroll', () => {
        let current = '';
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// ===================================
// Initialize All Functions
// ===================================
document.addEventListener('DOMContentLoaded', () => {
    initCountdown();
    initSmoothScroll();
    initMobileNav();
    initNavbarScroll();
    initContactForm();
    initScrollAnimations();
    initActiveNavigation();
    initRegistrationForm();
    
    // Add loading animation complete
    document.body.classList.add('loaded');
});

// ===================================
// Window Load Event
// ===================================
window.addEventListener('load', () => {
    // Hide any loading screens if present
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.style.display = 'none';
    }
});

// ===================================
// Prevent Default Form Submission on Enter
// ===================================
document.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        const form = e.target.closest('form');
        if (form && form.id === 'contactForm') {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    }
});
