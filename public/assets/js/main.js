/**
 * BloodBank Management System — main.js
 * Handles: sidebar toggle, flash messages, CSRF helpers,
 *          form validation, confirmation dialogs, auto-logout
 */

'use strict';

/* ───────────────────────────────────────
   1. SIDEBAR TOGGLE (mobile)
─────────────────────────────────────── */
(function initSidebar() {
  const toggle  = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }

  // Close on route change
  document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 1024) {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
      }
    });
  });
})();


/* ───────────────────────────────────────
   2. FLASH MESSAGES (auto-dismiss)
─────────────────────────────────────── */
(function initFlashMessages() {
  const flashes = document.querySelectorAll('.flash-message');

  flashes.forEach(function(el) {
    // Auto-dismiss after 5 seconds
    const timer = setTimeout(() => dismissFlash(el), 5000);

    const closeBtn = el.querySelector('.flash-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        clearTimeout(timer);
        dismissFlash(el);
      });
    }
  });

  function dismissFlash(el) {
    el.style.transition = 'opacity .3s, transform .3s';
    el.style.opacity    = '0';
    el.style.transform  = 'translateX(20px)';
    setTimeout(() => el.remove(), 320);
  }
})();

/**
 * Programmatically show a flash message.
 * @param {string} message
 * @param {'success'|'error'|'warning'|'info'} type
 */
function showFlash(message, type = 'info') {
  let container = document.querySelector('.flash-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'flash-container';
    document.body.appendChild(container);
  }

  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

  const el = document.createElement('div');
  el.className = 'flash-message flash-' + type;
  el.innerHTML = `
    <span class="flash-icon">${icons[type] || 'ℹ️'}</span>
    <span class="flash-text">${escapeHtml(message)}</span>
    <button class="flash-close" aria-label="Dismiss">✕</button>
  `;

  container.appendChild(el);

  const timer = setTimeout(() => dismiss(el), 5000);
  el.querySelector('.flash-close').addEventListener('click', () => {
    clearTimeout(timer);
    dismiss(el);
  });

  function dismiss(node) {
    node.style.transition = 'opacity .3s, transform .3s';
    node.style.opacity    = '0';
    node.style.transform  = 'translateX(20px)';
    setTimeout(() => node.remove(), 320);
  }
}


/* ───────────────────────────────────────
   3. CONFIRMATION DIALOGS
─────────────────────────────────────── */
(function initConfirmForms() {
  // Any form with data-confirm="message" will prompt before submit
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
      const msg = this.dataset.confirm || 'Are you sure?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // Any button/link with data-confirm
  document.querySelectorAll('[data-confirm]:not(form)').forEach(el => {
    el.addEventListener('click', function(e) {
      const msg = this.dataset.confirm || 'Are you sure?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });
})();


/* ───────────────────────────────────────
   4. MULTI-STEP FORM (register)
─────────────────────────────────────── */
(function initMultiStep() {
  const form  = document.getElementById('multiStepForm');
  if (!form) return;

  const steps      = Array.from(form.querySelectorAll('.form-step'));
  const nextBtns   = form.querySelectorAll('[data-next]');
  const prevBtns   = form.querySelectorAll('[data-prev]');
  const stepDots   = document.querySelectorAll('.step');
  let current      = 0;

  function showStep(index) {
    steps.forEach((s, i) => s.classList.toggle('active', i === index));
    stepDots.forEach((dot, i) => {
      dot.classList.toggle('active',   i === index);
      dot.classList.toggle('complete', i < index);
    });
    current = index;
  }

  nextBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      if (validateStep(steps[current])) {
        showStep(current + 1);
      }
    });
  });

  prevBtns.forEach(btn => {
    btn.addEventListener('click', () => showStep(current - 1));
  });

  function validateStep(stepEl) {
    const required = stepEl.querySelectorAll('[required]');
    let valid = true;
    required.forEach(input => {
      input.classList.remove('input-error');
      if (!input.value.trim()) {
        input.classList.add('input-error');
        valid = false;
      }
    });
    if (!valid) showFlash('Please fill in all required fields.', 'warning');
    return valid;
  }

  showStep(0);
})();


/* ───────────────────────────────────────
   5. LIVE SEARCH / TABLE FILTER
─────────────────────────────────────── */
(function initTableSearch() {
  const input = document.getElementById('tableSearch');
  if (!input) return;

  const targetTable = document.getElementById(input.dataset.table || 'searchableTable');
  if (!targetTable) return;

  input.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const rows = targetTable.querySelectorAll('tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const match = text.includes(q);
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    const countEl = document.getElementById('tableSearchCount');
    if (countEl) countEl.textContent = visibleCount + ' result(s)';
  });
})();


/* ───────────────────────────────────────
   6. CSRF TOKEN HELPER
─────────────────────────────────────── */
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

/**
 * POST with CSRF token included.
 * @param {string} url
 * @param {object} data
 * @returns {Promise<Response>}
 */
function postWithCsrf(url, data = {}) {
  const formData = new FormData();
  formData.append('csrf_token', getCsrfToken());
  Object.entries(data).forEach(([k, v]) => formData.append(k, v));
  return fetch(url, { method: 'POST', body: formData, credentials: 'same-origin' });
}


/* ───────────────────────────────────────
   7. INVENTORY EXPIRY COUNTDOWN
─────────────────────────────────────── */
(function initExpiryCountdowns() {
  const cells = document.querySelectorAll('[data-expiry]');
  if (!cells.length) return;

  const now = Date.now();

  cells.forEach(cell => {
    const expiry = new Date(cell.dataset.expiry).getTime();
    const diff   = expiry - now;
    const days   = Math.floor(diff / 86400000);

    if (days < 0) {
      cell.innerHTML = '<span class="badge badge-danger">Expired</span>';
    } else if (days === 0) {
      cell.innerHTML = '<span class="badge badge-danger">Expires today</span>';
    } else if (days <= 3) {
      cell.innerHTML += ` <span class="badge badge-warning">${days}d left</span>`;
    } else if (days <= 7) {
      cell.innerHTML += ` <span class="badge badge-info">${days}d left</span>`;
    }
  });
})();


/* ───────────────────────────────────────
   8. BLOOD LEVEL BARS — animate on load
─────────────────────────────────────── */
(function animateBloodBars() {
  const fills = document.querySelectorAll('.blood-level-fill');
  if (!fills.length) return;

  // Set initial width to 0, then animate to target
  fills.forEach(fill => {
    const target = fill.style.width;
    fill.style.width = '0';
    requestAnimationFrame(() => {
      setTimeout(() => { fill.style.width = target; }, 100);
    });
  });
})();


/* ───────────────────────────────────────
   9. AUTO-SAVE FORM DRAFT (localStorage)
─────────────────────────────────────── */
(function initFormDraft() {
  const form = document.querySelector('form[data-draft]');
  if (!form) return;

  const key = 'draft_' + (form.dataset.draft || location.pathname);

  // Restore draft
  try {
    const saved = JSON.parse(localStorage.getItem(key) || '{}');
    Object.entries(saved).forEach(([name, value]) => {
      const el = form.elements[name];
      if (el && el.type !== 'hidden' && el.type !== 'submit') {
        el.value = value;
      }
    });
  } catch {}

  // Save on change
  form.addEventListener('input', debounce(() => {
    const data = {};
    new FormData(form).forEach((v, k) => {
      if (k !== 'csrf_token') data[k] = v;
    });
    try { localStorage.setItem(key, JSON.stringify(data)); } catch {}
  }, 500));

  // Clear on successful submit
  form.addEventListener('submit', () => {
    try { localStorage.removeItem(key); } catch {}
  });
})();


/* ───────────────────────────────────────
   10. UTILITY FUNCTIONS
─────────────────────────────────────── */
function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function debounce(fn, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

/* Dismiss alert banners */
document.querySelectorAll('.alert-banner-close').forEach(btn => {
  btn.addEventListener('click', function() {
    this.closest('.alert-banner').remove();
  });
});

/* Active nav link highlight */
(function highlightNav() {
  const path = location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && path.startsWith(href) && href !== '/') {
      link.classList.add('active');
    } else if (href === '/' && path === '/') {
      link.classList.add('active');
    }
  });
})();