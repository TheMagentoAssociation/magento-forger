<style>
    #magento-opensource-universe-bar-wrapper {
        background-color: #3c3c3c;
    }

    #magento-opensource-universe-bar {
        width: 1320px;
        margin-left: auto;
        margin-right: auto;
    }

    #magento-opensource-universe-bar-mainnav {
        padding: 0;
        margin: 0;
    }

    #magento-opensource-universe-bar-mainnav li {
        padding: 0.4rem 1rem;
        margin: 0;
        display: inline-flex;
        align-items: center;
        border-right: 1px solid #888888;
        font-size: 0.9rem;
        position: relative;
    }

    @media (max-width: 767.98px) {
        #magento-opensource-universe-bar-mainnav li {
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
        }
        .magento-opensource-universe-bar-intro {
            display: none;
        }
        
        /* Mobile dropdown as bottom sheet */
        #magento-opensource-universe-bar-mainnav li ul,
        #magento-opensource-universe-bar-mainnav li ul.magento-opensource-universe-bar-subnav {
            display: none !important;
        }
        
        .mobile-dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
        }
        
        /* Hide mobile overlay and content on desktop */
        @media (min-width: 768px) {
            .mobile-dropdown-overlay,
            .mobile-dropdown-content {
                display: none !important;
            }
        }
        
        .mobile-dropdown-overlay.active {
            display: block;
        }
        
        .mobile-dropdown-content {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #3c3c3c;
            padding: 1rem;
            border-top: 3px solid #ee6524;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
            transform: translateY(100%);
            transition: transform 0.3s ease;
            display: none;
        }
        
        .mobile-dropdown-overlay.active .mobile-dropdown-content {
            transform: translateY(0);
            display: block;
        }
        
        .mobile-dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #555;
        }
        
        .mobile-dropdown-header h3 {
            color: #fff;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .mobile-dropdown-close {
            background: none;
            border: none;
            color: #bbbbbb;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
        }
        
        .mobile-dropdown-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .mobile-dropdown-links a {
            color: #bbbbbb;
            text-decoration: none;
            padding: 0.75rem;
            background-color: #4a4a4a;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .mobile-dropdown-links a:hover {
            background-color: #555;
            color: #fff;
        }
    }

    #magento-opensource-universe-bar-mainnav li:last-child {
        border-right: none;
    }

    #magento-opensource-universe-bar-mainnav li a {
        text-decoration: none;
        color: #bbbbbb;
        display: flex;
        align-items: center;
        height: 100%;
    }
    #magento-opensource-universe-bar-mainnav li a svg {
        margin-top: -2px;
    }

    #magento-opensource-universe-bar-mainnav li ul {
        position: absolute;
        z-index: 100;
        top: 100%;
        left: 0;
        padding: 0;
        margin: 0;
        display: none;
    }

    #magento-opensource-universe-bar-mainnav li:hover ul {
        display: block;
    }
    
    #magento-opensource-universe-bar-mainnav li.active ul {
        display: block;
    }

    #magento-opensource-universe-bar-mainnav li ul li {
        display: block;
        background-color: #3c3c3c;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        border: none;
    }

    .magento-opensource-universe-bar-intro {
        color: #dddddd;
    }

    /* Media Queries for Bootstrap Breakpoints */
    @media (max-width: 575.98px) {
        #magento-opensource-universe-bar {
            width: 100%;
            padding: 0 10px;
        }
        #magento-opensource-universe-bar-mainnav {
            overflow-x: auto;
            white-space: nowrap;
        }
        
        #magento-opensource-universe-bar-mainnav li {
            position: relative;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        #magento-opensource-universe-bar {
            width: 540px;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        #magento-opensource-universe-bar {
            width: 720px;
        }
    }

    @media (min-width: 992px) and (max-width: 1199.98px) {
        #magento-opensource-universe-bar {
            width: 960px;
        }
    }

    @media (min-width: 1200px) and (max-width: 1399.98px) {
        #magento-opensource-universe-bar {
            width: 1140px;
        }
    }

    @media (min-width: 1400px) {
        #magento-opensource-universe-bar {
            width: 1320px;
        }
    }
</style>

<div id="magento-opensource-universe-bar-wrapper">
    <nav id="magento-opensource-universe-bar">
        <ul id="magento-opensource-universe-bar-mainnav">
            <li class="magento-opensource-universe-bar-intro">Explore the Magento Open Source Universe</li>
            <li>
                <a href="https://magento-opensource.com" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Magento Opensource
                </a>
            </li>
            <li>
                <a href="https://magentoassociation.org" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Magento Association
                </a>
            </li>
            <li>
                <a href="https://meet-magento.com" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Meet Magento
                </a>
            </li>
            <li>
                <a href="#" onclick="toggleMobileDropdown(event, this)">
                    <svg class="dropdown-chevron" width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Development Resources
                </a>
                <ul class="magento-opensource-universe-bar-subnav">
                    <li>
                        <a href="https://github.com/magento/magento2" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            GitHub Repository
                        </a>
                    </li>
                    <li>
                        <a href="https://forger.magento-opensource.com" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            Magento Forger
                        </a>
                    </li>
                    <!--<li>
                        <a href="https://docs.magento-opensource.com" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            Documentation
                        </a>
                    </li>-->
                </ul>
            </li>
        </ul>
    </nav>
</div>

<!-- Mobile dropdown overlay - hidden by default -->
<div class="mobile-dropdown-overlay" id="mobileDropdownOverlay" style="display: none;">
    <div class="mobile-dropdown-content">
        <div class="mobile-dropdown-header">
            <h3>Development Resources</h3>
            <button class="mobile-dropdown-close" onclick="closeMobileDropdown()">&times;</button>
        </div>
        <div class="mobile-dropdown-links">
            <a href="https://github.com/magento/magento2" target="_blank">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
                </svg>
                GitHub Repository
            </a>
            <a href="https://forger.magento-opensource.com" target="_blank">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.292-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.292c.415.764-.42 1.6-1.185 1.184l-.292-.159a1.873 1.873 0 0 0-2.692 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.693-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.292A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                </svg>
                Magento Forger
            </a>
        </div>
    </div>
</div>

<script>
function toggleMobileDropdown(event, element) {
    event.preventDefault();
    event.stopPropagation();
    
    // Check if we're on mobile (screen width < 768px)
    if (window.innerWidth < 768) {
        openMobileDropdown();
    } else {
        // Desktop behavior - do nothing, let CSS hover handle it
        return;
    }
}

function openMobileDropdown() {
    const overlay = document.getElementById('mobileDropdownOverlay');
    overlay.style.display = 'block';
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent body scroll
}

function closeMobileDropdown() {
    const overlay = document.getElementById('mobileDropdownOverlay');
    overlay.classList.remove('active');
    // Add a delay to hide after the animation completes
    setTimeout(() => {
        overlay.style.display = 'none';
    }, 300); // Match the CSS transition duration
    document.body.style.overflow = ''; // Restore body scroll
}

// Close dropdown when clicking on overlay background
document.addEventListener('click', function(event) {
    const overlay = document.getElementById('mobileDropdownOverlay');
    if (event.target === overlay) {
        closeMobileDropdown();
    }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const overlay = document.getElementById('mobileDropdownOverlay');
        if (overlay.classList.contains('active')) {
            closeMobileDropdown();
        }
    }
});

// Initialize chevron direction on load
document.addEventListener('DOMContentLoaded', function() {
    const chevron = document.querySelector('.dropdown-chevron path');
    if (chevron) {
        chevron.setAttribute('d', 'M3 1l4 4-4 4'); // Right arrow
    }
});
</script>
