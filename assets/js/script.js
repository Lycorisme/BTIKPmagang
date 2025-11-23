/**
 * MAGANG APP - CORE SCRIPT
 * Focus: Efficiency, Modal Fixes, and Form Handling
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // 1. === THE MODAL FIX (SOLUSI LAYAR HITAM) ===
    // Bootstrap 5 sering bermasalah dengan backdrop jika modal ada di dalam container/table.
    // Script ini memaksa semua modal pindah ke tag <body> agar z-index nya benar.
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        document.body.appendChild(modal);
    });

    // 2. === PREVENT DOUBLE SUBMISSION ===
    // Mencegah user klik tombol simpan berkali-kali (menghindari duplikasi data).
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Cek validitas HTML5 dulu
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            // Jika valid, matikan tombol submit
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.style.width = btn.offsetWidth + 'px'; // Kunci lebar tombol
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                // Safety: Hidupkan lagi tombol jika server tidak merespon dalam 10 detik
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 10000);
            }
        });
    });

    // 3. === PASSWORD TOGGLE (SHOW/HIDE) ===
    // Script untuk icon mata pada input password
    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('data-target'));
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // 4. === FILE INPUT FILENAME PREVIEW ===
    // Menampilkan nama file yang dipilih (karena input file default Bootstrap jelek)
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // MB
                
                // Cek apakah sudah ada helper text
                let helper = this.nextElementSibling;
                if (!helper || !helper.classList.contains('form-text')) {
                    helper = document.createElement('div');
                    helper.className = 'form-text mt-2 text-primary';
                    this.parentNode.insertBefore(helper, this.nextSibling);
                }
                helper.innerHTML = `<i class="bi bi-paperclip"></i> ${fileName} (${fileSize} MB)`;
            }
        });
    });

    // 5. === AUTO CLOSE ALERTS ===
    // Menutup notifikasi alert otomatis setelah 4 detik
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        }, 4000);
    });

    // 6. === INITIALIZE BOOTSTRAP TOOLTIPS ===
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    console.log('MagangApp v2.0 Loaded - Efficiency & Fixes Applied.');
});