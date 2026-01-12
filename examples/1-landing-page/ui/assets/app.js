/**
 * Landing Page Example - Custom JavaScript
 * 
 * Demonstrates genes.js usage with the landing page example
 */

// Run when DOM is ready
g.que("onReady", function() {
    console.log("Landing page loaded!");
    
    // Highlight active navigation link
    highlightActiveNav();
    
    // Add smooth scroll to CTA button
    addSmoothScroll();
    
    // Add form validation if on contact page
    setupContactForm();
});

/**
 * Highlight active navigation link based on current page
 */
function highlightActiveNav() {
    var currentPath = window.location.pathname.split('/').pop() || 'index';
    var navLinks = g.selAll('.nav-link');
    
    navLinks.forEach(function(link) {
        var href = link.getAttribute('href');
        if (href === currentPath || (currentPath === '' && href === 'index')) {
            link.style.background = 'var(--bg-alt)';
            link.style.fontWeight = '600';
        }
    });
}

/**
 * Add smooth scroll behavior to CTA button
 */
function addSmoothScroll() {
    var ctaButton = g.sel('.cta-button');
    
    if (ctaButton) {
        g.on('.cta-button', 'click', function(e) {
            e.preventDefault();
            
            // Scroll to features section
            var featuresSection = g.sel('.feature-card');
            if (featuresSection) {
                featuresSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}

/**
 * Setup contact form validation and submission
 */
function setupContactForm() {
    var contactForm = g.sel('.contact-form');
    
    if (contactForm) {
        g.on('.contact-form', 'submit', function(e) {
            e.preventDefault();
            
            var name = g.sel('input[name="name"]');
            var email = g.sel('input[name="email"]');
            var message = g.sel('textarea[name="message"]');
            
            // Simple validation
            if (!name || !name.value.trim()) {
                alert('Please enter your name');
                return;
            }
            
            if (!email || !email.value.trim()) {
                alert('Please enter your email');
                return;
            }
            
            if (!message || !message.value.trim()) {
                alert('Please enter a message');
                return;
            }
            
            // In a real app, you would send this to an API
            console.log('Form submitted:', {
                name: name.value,
                email: email.value,
                message: message.value
            });
            
            // Show success message
            alert('Thank you for your message! We will get back to you soon.');
            
            // Reset form
            contactForm.reset();
        });
    }
}

// Example of using genes.js state management
g.set("app.name", "Landing Page Example");
g.set("app.version", "1.0.0");

// Log app info
console.log("App:", g.get("app.name"), "v" + g.get("app.version"));
