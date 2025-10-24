// Cricket Live Score - Main JavaScript File
// Disable right-click, text selection, and zoom

// Disable right-click
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

// Disable text selection
document.addEventListener('selectstart', function(e) {
    e.preventDefault();
    return false;
});

// Disable copy
document.addEventListener('copy', function(e) {
    e.preventDefault();
    return false;
});

// Disable cut
document.addEventListener('cut', function(e) {
    e.preventDefault();
    return false;
});

// Disable keyboard shortcuts for zoom and devtools
document.addEventListener('keydown', function(e) {
    // Prevent Ctrl/Cmd + (+, -, 0) for zoom
    if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '0' || e.key === '=')) {
        e.preventDefault();
        return false;
    }
    
    // Prevent Ctrl+Shift+I (DevTools)
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        return false;
    }
    
    // Prevent F12 (DevTools)
    if (e.key === 'F12') {
        e.preventDefault();
        return false;
    }
    
    // Prevent Ctrl+U (View Source)
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
        return false;
    }
    
    // Prevent Ctrl+S (Save)
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        return false;
    }
});

// Disable mouse wheel zoom
document.addEventListener('wheel', function(e) {
    if (e.ctrlKey) {
        e.preventDefault();
        return false;
    }
}, { passive: false });

// Disable pinch zoom on touch devices
document.addEventListener('touchstart', function(e) {
    if (e.touches.length > 1) {
        e.preventDefault();
    }
}, { passive: false });

document.addEventListener('touchmove', function(e) {
    if (e.touches.length > 1) {
        e.preventDefault();
    }
}, { passive: false });

// Prevent double-tap zoom on mobile
let lastTouchEnd = 0;
document.addEventListener('touchend', function(e) {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) {
        e.preventDefault();
    }
    lastTouchEnd = now;
}, false);

// Add CSS to disable text selection
const style = document.createElement('style');
style.textContent = `
    * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }
    
    input, textarea {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }
`;
document.head.appendChild(style);

// Mobile menu toggle functionality
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
        mobileMenu.classList.toggle('hidden');
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.auto-hide-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
