// Homepage animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate page elements
    anime({
        targets: '.display-4',
        translateY: [-50, 0],
        opacity: [0, 1],
        duration: 1000,
        easing: 'easeOutExpo'
    });

    anime({
        targets: '.lead',
        translateY: [50, 0],
        opacity: [0, 1],
        duration: 1000,
        delay: 300,
        easing: 'easeOutExpo'
    });

    anime({
        targets: '.btn-lg',
        scale: [0.8, 1],
        opacity: [0, 1],
        duration: 1000,
        delay: 600,
        easing: 'easeOutExpo'
    });

    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                anime({
                    targets: entry.target,
                    translateY: [30, 0],
                    opacity: [0, 1],
                    duration: 800,
                    easing: 'easeOutQuad'
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });
});