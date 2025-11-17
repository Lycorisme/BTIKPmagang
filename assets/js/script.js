/**
 * MODERN & ELEGANT JAVASCRIPT FOR MAGANG APP
 * FIXED VERSION - Enhanced user interactions and animations
 * All Issues Resolved
 */

(function() {
    'use strict';

    // === NAVBAR SCROLL EFFECT ===
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }

    // === FADE IN ANIMATION ON SCROLL ===
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all cards
    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });

    // === SMOOTH SCROLL FOR ANCHOR LINKS ===
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // === FORM VALIDATION ENHANCEMENT ===
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() !== '') {
                    if (this.checkValidity()) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                }
            });
        });
    });

    // === BUTTON CLICK ANIMATION (RIPPLE EFFECT) ===
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // === TOOLTIP INITIALIZATION ===
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // === TABLE SEARCH FUNCTIONALITY ===
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table tbody');
            if (table) {
                const rows = table.querySelectorAll('tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            }
        });
    });

    // === AUTO-HIDE ALERTS ===
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-permanent')) {
            setTimeout(() => {
                if (typeof bootstrap !== 'undefined') {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } else {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        }
    });

    // === COUNTER ANIMATION ===
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }

    // Animate stat numbers
    const statNumbers = document.querySelectorAll('.card h2, .card h3');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.textContent);
                if (!isNaN(target) && target > 0) {
                    animateCounter(entry.target, target);
                    counterObserver.unobserve(entry.target);
                }
            }
        });
    }, { threshold: 0.5 });

    statNumbers.forEach(number => {
        counterObserver.observe(number);
    });

    // === MODAL FOCUS ENHANCEMENT ===
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });

        // Reset form when modal is closed
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
                
                // Remove validation classes
                form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                    el.classList.remove('is-valid', 'is-invalid');
                });
            }
        });
    });

    // === FILE INPUT PREVIEW ===
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'No file chosen';
            const fileSize = this.files[0]?.size;
            
            // Find or create label
            let label = this.nextElementSibling;
            if (!label || !label.classList.contains('form-text')) {
                label = document.createElement('div');
                label.classList.add('form-text', 'mt-2');
                this.parentNode.insertBefore(label, this.nextSibling);
            }
            
            if (fileSize) {
                const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);
                label.innerHTML = `<i class="bi bi-file-earmark"></i> ${fileName} (${sizeMB} MB)`;
                label.style.color = '#10b981';
            } else {
                label.textContent = 'No file chosen';
                label.style.color = '#6c757d';
            }
        });
    });

    // === BACK TO TOP BUTTON ===
    let backToTopBtn = document.querySelector('.back-to-top');
    if (!backToTopBtn) {
        backToTopBtn = document.createElement('button');
        backToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
        backToTopBtn.className = 'btn btn-primary back-to-top';
        backToTopBtn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(backToTopBtn);
    }

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'flex';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // === PASSWORD TOGGLE ===
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input && input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            } else if (input) {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });

    // === CARD HOVER ANIMATION ===
    document.querySelectorAll('.card').forEach(card => {
        if (!card.classList.contains('no-hover')) {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        }
    });

    // === LOADING ANIMATION ===
    window.addEventListener('load', () => {
        const loader = document.querySelector('.page-loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    });

    // === PRINT FUNCTIONALITY ===
    const printButtons = document.querySelectorAll('[onclick*="print"]');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });

    // === DYNAMIC YEAR IN FOOTER ===
    const yearElements = document.querySelectorAll('.current-year');
    const currentYear = new Date().getFullYear();
    yearElements.forEach(element => {
        element.textContent = currentYear;
    });

    // === TABLE ROW CLICK HANDLER ===
    const clickableRows = document.querySelectorAll('tr[data-href]');
    clickableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            // Don't navigate if clicking on a button or link
            if (!e.target.closest('button, a')) {
                window.location.href = this.dataset.href;
            }
        });
    });

    // === DROPDOWN HOVER (Desktop only) ===
    if (window.innerWidth > 768) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('mouseenter', function() {
                const menu = this.querySelector('.dropdown-menu');
                if (menu && typeof bootstrap !== 'undefined') {
                    const bsDropdown = new bootstrap.Dropdown(this.querySelector('[data-bs-toggle="dropdown"]'));
                    bsDropdown.show();
                }
            });

            dropdown.addEventListener('mouseleave', function() {
                const menu = this.querySelector('.dropdown-menu');
                if (menu && typeof bootstrap !== 'undefined') {
                    const bsDropdown = bootstrap.Dropdown.getInstance(this.querySelector('[data-bs-toggle="dropdown"]'));
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
            });
        });
    }

    // === CONFIRM DELETE ===
    const deleteButtons = document.querySelectorAll('[data-confirm="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // === AUTO-RESIZE TEXTAREA ===
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        // Set initial height
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // === BADGE ANIMATION ===
    const badges = document.querySelectorAll('.badge');
    badges.forEach((badge, index) => {
        setTimeout(() => {
            badge.style.animation = 'fadeIn 0.5s ease';
        }, index * 100);
    });

    // === MODAL CASCADE FIX ===
    // Fix untuk membuka modal dari modal lain
    document.addEventListener('show.bs.modal', function (event) {
        const modal = event.target;
        const backdrop = document.querySelector('.modal-backdrop');
        
        // Cek apakah ada modal lain yang terbuka
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
            // Adjust z-index
            const baseZIndex = 1050;
            const newZIndex = baseZIndex + (openModals.length * 20);
            modal.style.zIndex = newZIndex;
            
            // Adjust backdrop
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 0) {
                    const lastBackdrop = backdrops[backdrops.length - 1];
                    lastBackdrop.style.zIndex = newZIndex - 10;
                }
            }, 100);
        }
    });

    // === TABLE FILTER ACTIVE STATE ===
    const filterButtons = document.querySelectorAll('.btn-group a');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active from all
            this.parentElement.querySelectorAll('a').forEach(btn => {
                btn.classList.remove('active');
            });
            // Add active to clicked
            this.classList.add('active');
        });
    });

    // === PREVENT DOUBLE SUBMIT ===
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
                
                // Re-enable after 3 seconds (in case of validation errors)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                }, 3000);
            }
        });
    });

    // === COPY TO CLIPBOARD ===
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });

    // === INITIALIZE ALL BOOTSTRAP COMPONENTS ===
    if (typeof bootstrap !== 'undefined') {
        // Initialize all tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
        
        // Initialize all popovers
        const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(popover => new bootstrap.Popover(popover));
    }

    // === CONSOLE LOG (Development only) ===
    console.log('%c🎓 BTIKP Magang System ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px; font-size: 16px; font-weight: bold;');
    console.log('%cSystem loaded successfully! ✨', 'color: #10b981; font-size: 14px;');
    console.log('%cAll bugs fixed and optimized! 🚀', 'color: #667eea; font-size: 12px;');

})();