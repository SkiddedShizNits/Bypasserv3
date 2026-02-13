(function() {
    'use strict';
    
    const disableDevTools = () => {
        const detectDevTools = () => {
            const threshold = 160;
            if (window.outerWidth - window.innerWidth > threshold || 
                window.outerHeight - window.innerHeight > threshold) {
                document.body.innerHTML = '<h1 style="color:red;text-align:center;margin-top:50px;">DevTools Detected!</h1>';
            }
        };
        
        setInterval(detectDevTools, 1000);
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                (e.ctrlKey && e.shiftKey && e.key === 'J') ||
                (e.ctrlKey && e.key === 'U')) {
                e.preventDefault();
                return false;
            }
        });
        
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            return false;
        });
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', disableDevTools);
    } else {
        disableDevTools();
    }
})();