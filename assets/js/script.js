// === NAVIGATION & SIDEBAR ===
const navToggle = document.getElementById('nav-toggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const closeBtn = document.getElementById('close-btn');
const navLinks = document.querySelectorAll('.nav-link');
const sidebarLinks = document.querySelectorAll('.sidebar-link');
const allLinks = [...navLinks, ...sidebarLinks];

function closeSidebar() {
    navToggle?.classList.remove('active');
    sidebar?.classList.remove('active');
    overlay?.classList.remove('active');
    document.body.style.overflow = 'auto';
}

navToggle?.addEventListener('click', () => {
    navToggle.classList.toggle('active');
    sidebar?.classList.toggle('active');
    overlay?.classList.toggle('active');
    document.body.style.overflow = sidebar?.classList.contains('active') ? 'hidden' : 'auto';
});

closeBtn?.addEventListener('click', closeSidebar);
overlay?.addEventListener('click', closeSidebar);

allLinks.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const targetSection = link.getAttribute('data-section');
        const targetElement = document.getElementById(targetSection);

        if (targetElement) {
            allLinks.forEach(l => l.classList.remove('active'));
            document.querySelector(`.nav-link[data-section="${targetSection}"]`)?.classList.add('active');
            document.querySelector(`.sidebar-link[data-section="${targetSection}"]`)?.classList.add('active');

            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (sidebar?.classList.contains('active')) closeSidebar();
        }
    });
});

// === SCROLLSPY UNTUK NAVBAR ===
// gunakan semua <section> yang memiliki id agar scrollspy bekerja dengan benar
const sections = document.querySelectorAll('section[id]');
const navbar = document.getElementById('navbar');
const navbarHeight = navbar?.offsetHeight || 0;

const sectionObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const sectionId = entry.target.id;
            allLinks.forEach(link => link.classList.remove('active'));
            document.querySelector(`.nav-link[data-section="${sectionId}"]`)?.classList.add('active');
            document.querySelector(`.sidebar-link[data-section="${sectionId}"]`)?.classList.add('active');
        }
    });
}, { root: null, rootMargin: `-${navbarHeight + 50}px 0px -50% 0px`, threshold: 0 });

sections.forEach(section => sectionObserver.observe(section));

// === NAVBAR BLUR ON SCROLL ===
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (!navbar) return;
    if (scrollTop > 50) {
        navbar.style.backdropFilter = 'blur(10px)';
        navbar.style.backgroundColor = 'rgba(27, 38, 44, 0.95)';
    } else {
        navbar.style.backdropFilter = 'none';
        navbar.style.backgroundColor = '#1b262c';
    }
});

// === RESPONSIVE SIDEBAR CLOSE ===
window.addEventListener('resize', () => {
    if (window.innerWidth > 768 && sidebar?.classList.contains('active')) {
        closeSidebar();
    }
});

// === DOM READY ===
document.addEventListener('DOMContentLoaded', () => {
    // aktifkan link pertama
    document.querySelector('.nav-link[data-section="beranda"]')?.classList.add('active');
    document.querySelector('.sidebar-link[data-section="beranda"]')?.classList.add('active');

    // tombol hero
    const heroBtn = document.querySelector('.hero-btn');
    heroBtn?.addEventListener('click', () => console.log('Navigating to map...'));

    // animasi hero text & image
    const heroObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('animate-in');
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.hero-text, .hero-image').forEach(el => heroObserver.observe(el));

    // floating particles
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        const particleCount = 15;
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'floating-particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                background: rgba(209, 212, 213, 0.3);
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: particleFloat ${Math.random() * 10 + 15}s linear infinite;
                animation-delay: ${Math.random() * 5}s;
                z-index: 1;
            `;
            heroSection.appendChild(particle);
        }
    }

    // mouse move circle effect
    document.addEventListener('mousemove', e => {
        const mouseX = (e.clientX / window.innerWidth) * 2 - 1;
        const mouseY = (e.clientY / window.innerHeight) * 2 - 1;
        document.querySelectorAll('.circle').forEach((circle, index) => {
            const speed = (index + 1) * 0.5;
            circle.style.transform = `translate(${mouseX * speed}px, ${mouseY * speed}px)`;
        });
    });

    // parallax hero
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        document.querySelector('.hero-text')?.style.setProperty('transform', `translateY(${scrolled * 0.5}px)`);
        document.querySelector('.hero-image')?.style.setProperty('transform', `translateY(${scrolled * 0.15}px)`);
    });

    // circle hover feedback
    document.querySelectorAll('.circle').forEach(circle => {
        circle.addEventListener('mouseenter', () => {
            circle.style.transform += ' scale(1.1)';
            circle.style.filter = 'brightness(1.2)';
        });
        circle.addEventListener('mouseleave', () => {
            circle.style.transform = circle.style.transform.replace(' scale(1.1)', '');
            circle.style.filter = 'brightness(1)';
        });
    });

    // ripple effect for hero-btn
    if (heroBtn) {
        heroBtn.style.position = 'relative';
        heroBtn.style.overflow = 'hidden';
        heroBtn.addEventListener('click', e => {
            const ripple = document.createElement('span');
            const rect = heroBtn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
                z-index: 0;
            `;
            heroBtn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    }

    // timeline animation
    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('show'), index * 200);
            }
        });
    }, { threshold: 0.3, rootMargin: '0px 0px -100px 0px' });

    document.querySelectorAll('.timeline-item').forEach(item => timelineObserver.observe(item));

    // Observers untuk menjalankan animasi/menambahkan kelas .visible pada elemen-elemen
    const visibleObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

    // Observasi elemen-elemen yang secara default di-CSS diset opacity:0
    document.querySelectorAll(
        '.section-title, .section-title h2, .title-line, .underline, .tagline, .description, .feature-card, .expertise, .image-content'
    ).forEach(el => visibleObserver.observe(el));

    // Pastikan judul yang memiliki animasi (jika ada) juga dimulai
    const titlePlayObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.style.animationPlayState = 'running';
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.section-title h2, .title-line').forEach(el => titlePlayObserver.observe(el));

    document.querySelectorAll('.timeline-card').forEach(card => {
        card.addEventListener('click', () => {
            const stepNumber = card.querySelector('.step-number')?.textContent || '';
            console.log(`${stepNumber} clicked`);
            card.style.transform = 'scale(0.98) translateY(-10px)';
            setTimeout(() => card.style.transform = '', 150);
        });
    });
});

// === ON LOAD ===
window.addEventListener('load', () => {
    document.querySelector('.hero-section')?.classList.add('loaded');
});

// === GLOBAL STYLE APPEND ===
const style = document.createElement('style');
style.textContent = `
@keyframes particleFloat {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
}
.floating-particle { pointer-events: none; }
@keyframes ripple {
    to { transform: scale(4); opacity: 0; }
}
`;
document.head.appendChild(style);

// global smooth scroll
document.documentElement.style.scrollBehavior = 'smooth';

// Animasi fade-in scroll
const cards = document.querySelectorAll(".fitur-card");

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "scale(1)";
      entry.target.style.transition = "all 0.6s ease";
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.2 });

cards.forEach((card) => {
  observer.observe(card);
});

