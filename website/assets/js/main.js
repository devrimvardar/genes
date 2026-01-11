// Genes Framework Website - Interactive Features

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Mobile menu toggle (if needed in future)
function toggleMobileMenu() {
    const nav = document.querySelector('.nav-menu');
    if (nav) {
        nav.classList.toggle('mobile-open');
    }
}

// Add active state to current page in navigation
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href.includes('page=' + currentPage) || (currentPage === 'home' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
});

// Code block copy functionality
document.querySelectorAll('pre code').forEach(block => {
    const button = document.createElement('button');
    button.className = 'copy-button';
    button.textContent = 'Copy';
    button.style.cssText = `
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(255,255,255,0.2);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85em;
        opacity: 0;
        transition: opacity 0.3s;
    `;
    
    const pre = block.parentElement;
    pre.style.position = 'relative';
    
    pre.addEventListener('mouseenter', () => {
        button.style.opacity = '1';
    });
    
    pre.addEventListener('mouseleave', () => {
        button.style.opacity = '0';
    });
    
    button.addEventListener('click', async () => {
        const text = block.textContent;
        try {
            await navigator.clipboard.writeText(text);
            button.textContent = 'Copied!';
            setTimeout(() => {
                button.textContent = 'Copy';
            }, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
    });
    
    pre.appendChild(button);
});

// Example: Add animation on scroll (optional)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe feature cards for fade-in animation
document.querySelectorAll('.feature-card, .example-card, .doc-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});
