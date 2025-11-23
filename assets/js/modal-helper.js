/**
 * MODAL HELPER
 * Menangani semua masalah z-index dan behavior modal
 * Include setelah Bootstrap JS
 */

(function() {
  'use strict';

  // ==========================================
  // MODAL MANAGER CLASS
  // ==========================================
  class ModalManager {
    constructor() {
      this.activeModals = [];
      this.baseZIndex = 1050;
      this.zIndexStep = 10;
      this.init();
    }

    init() {
      this.setupEventListeners();
      this.fixExistingModals();
      console.log('✅ Modal Manager initialized');
    }

    setupEventListeners() {
      // Event ketika modal akan ditampilkan
      document.addEventListener('show.bs.modal', (event) => {
        this.onModalShow(event);
      });

      // Event ketika modal selesai ditampilkan
      document.addEventListener('shown.bs.modal', (event) => {
        this.onModalShown(event);
      });

      // Event ketika modal akan disembunyikan
      document.addEventListener('hide.bs.modal', (event) => {
        this.onModalHide(event);
      });

      // Event ketika modal selesai disembunyikan
      document.addEventListener('hidden.bs.modal', (event) => {
        this.onModalHidden(event);
      });
    }

    onModalShow(event) {
      const modal = event.target;
      
      // Tambahkan ke array active modals
      if (!this.activeModals.includes(modal)) {
        this.activeModals.push(modal);
      }

      // Set z-index untuk modal baru
      setTimeout(() => {
        this.adjustZIndexes();
      }, 10);
    }

    onModalShown(event) {
      const modal = event.target;
      
      // Pastikan body tidak bisa scroll
      document.body.style.overflow = 'hidden';
      
      // Focus management
      const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
      if (firstInput) {
        firstInput.focus();
      }

      // Adjust z-index lagi untuk memastikan
      this.adjustZIndexes();
    }

    onModalHide(event) {
      const modal = event.target;
      
      // Remove dari array
      const index = this.activeModals.indexOf(modal);
      if (index > -1) {
        this.activeModals.splice(index, 1);
      }
    }

    onModalHidden(event) {
      // Cleanup orphaned backdrops
      this.cleanupBackdrops();

      // Jika tidak ada modal aktif, restore body scroll
      if (this.activeModals.length === 0) {
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.classList.remove('modal-open');
      }

      // Adjust z-index untuk modal yang tersisa
      this.adjustZIndexes();
    }

    adjustZIndexes() {
      const allBackdrops = document.querySelectorAll('.modal-backdrop');
      const allModals = document.querySelectorAll('.modal.show');

      // Set z-index untuk backdrops
      allBackdrops.forEach((backdrop, index) => {
        const zIndex = this.baseZIndex + (index * this.zIndexStep);
        backdrop.style.zIndex = zIndex;
      });

      // Set z-index untuk modals
      allModals.forEach((modal, index) => {
        const baseZ = this.baseZIndex + 5 + (index * this.zIndexStep);
        
        modal.style.zIndex = baseZ;
        
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
          dialog.style.zIndex = baseZ + 1;
        }

        const content = modal.querySelector('.modal-content');
        if (content) {
          content.style.zIndex = baseZ + 2;
        }
      });

      // Log untuk debugging
      console.log(`📊 Active modals: ${allModals.length}, Backdrops: ${allBackdrops.length}`);
    }

    cleanupBackdrops() {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      const visibleModals = document.querySelectorAll('.modal.show');

      // Jika tidak ada modal visible, hapus semua backdrop
      if (visibleModals.length === 0) {
        backdrops.forEach(backdrop => {
          backdrop.remove();
        });
        return;
      }

      // Jika ada modal, pastikan jumlah backdrop sesuai
      if (backdrops.length > visibleModals.length) {
        const excessBackdrops = backdrops.length - visibleModals.length;
        for (let i = 0; i < excessBackdrops; i++) {
          if (backdrops[i]) {
            backdrops[i].remove();
          }
        }
      }
    }

    fixExistingModals() {
      // Fix modal yang sudah ada di halaman
      const modals = document.querySelectorAll('.modal');
      
      modals.forEach(modal => {
        // Tambahkan click handler untuk close on backdrop
        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal && modal.dataset.bsBackdrop !== 'static') {
              bsModal.hide();
            }
          }
        });

        // Prevent content clicks from closing modal
        const content = modal.querySelector('.modal-content');
        if (content) {
          content.addEventListener('click', (e) => {
            e.stopPropagation();
          });
        }
      });
    }

    // Public method untuk force fix z-index
    forceFixZIndex() {
      this.adjustZIndexes();
    }

    // Public method untuk cleanup manual
    cleanup() {
      this.cleanupBackdrops();
      if (this.activeModals.length === 0) {
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.classList.remove('modal-open');
      }
    }
  }

  // ==========================================
  // UTILITY FUNCTIONS
  // ==========================================

  // Function untuk membuka modal secara programmatic
  window.openModal = function(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    } else {
      console.error(`Modal with id "${modalId}" not found`);
    }
  };

  // Function untuk menutup modal
  window.closeModal = function(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) {
        modal.hide();
      }
    } else {
      console.error(`Modal with id "${modalId}" not found`);
    }
  };

  // Function untuk menutup semua modal
  window.closeAllModals = function() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modalEl => {
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) {
        modal.hide();
      }
    });
  };

  // Function untuk toggle modal
  window.toggleModal = function(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
      let modal = bootstrap.Modal.getInstance(modalEl);
      if (!modal) {
        modal = new bootstrap.Modal(modalEl);
      }
      modal.toggle();
    }
  };

  // ==========================================
  // INITIALIZE
  // ==========================================

  // Initialize ketika DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      window.modalManager = new ModalManager();
    });
  } else {
    window.modalManager = new ModalManager();
  }

  // ==========================================
  // EMERGENCY FIX
  // ==========================================

  // Function untuk emergency fix jika modal stuck
  window.fixModalZIndex = function() {
    if (window.modalManager) {
      window.modalManager.forceFixZIndex();
      console.log('🔧 Modal z-index fixed manually');
    }
  };

  // Keyboard shortcut untuk emergency cleanup (Ctrl+Shift+M)
  document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.shiftKey && e.key === 'M') {
      console.log('🆘 Emergency modal cleanup triggered');
      window.closeAllModals();
      if (window.modalManager) {
        window.modalManager.cleanup();
      }
    }
  });

  // ==========================================
  // PERIODIC CHECK (Optional)
  // ==========================================

  // Check setiap 2 detik untuk orphaned backdrops
  setInterval(() => {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    const visibleModals = document.querySelectorAll('.modal.show');
    
    if (backdrops.length > 0 && visibleModals.length === 0) {
      console.log('🧹 Cleaning orphaned backdrops...');
      backdrops.forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    }
  }, 2000);

  // ==========================================
  // CONSOLE INFO
  // ==========================================

  console.log('%c🎭 Modal Helper Loaded', 'color: #2563eb; font-size: 14px; font-weight: bold;');
  console.log('%cAvailable functions:', 'color: #64748b; font-weight: bold;');
  console.log('  • openModal(id) - Open modal by ID');
  console.log('  • closeModal(id) - Close modal by ID');
  console.log('  • toggleModal(id) - Toggle modal by ID');
  console.log('  • closeAllModals() - Close all modals');
  console.log('  • fixModalZIndex() - Manual z-index fix');
  console.log('  • Ctrl+Shift+M - Emergency cleanup');

})();