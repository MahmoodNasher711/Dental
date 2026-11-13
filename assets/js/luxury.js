/**
 * DENTAL — Luxury Interface Interactive Scripts
 */
document.addEventListener('DOMContentLoaded', () => {
  // Mobile Sidebar Off-Canvas Toggle (Portal & Admin)
  const sidebarToggle = document.getElementById('portalSidebarToggle');
  const sidebar = document.getElementById('portalSidebar');
  const sidebarClose = document.getElementById('portalSidebarClose');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }

  if (sidebarClose && sidebar) {
    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('show');
    });
  }

  // Close sidebar on outside click on mobile
  document.addEventListener('click', (e) => {
    if (sidebar && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && (!sidebarToggle || !sidebarToggle.contains(e.target))) {
        sidebar.classList.remove('show');
      }
    }
  });

  // Prevent double form submission on appointment booking & admin forms
  const forms = document.querySelectorAll('form[data-prevent-double-submit="true"]');
  forms.forEach((form) => {
    form.addEventListener('submit', () => {
      const btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (btn && !btn.disabled) {
        setTimeout(() => {
          btn.disabled = true;
          btn.classList.add('opacity-75');
        }, 10);
      }
    });
  });

  // Dynamic Appointment Availability Loader (booking.php)
  const dentistSelect = document.getElementById('bookingDentist');
  const dateInput = document.getElementById('bookingDate');
  const serviceSelect = document.getElementById('bookingService');
  const slotContainer = document.getElementById('slotGridContainer');

  if (dentistSelect && dateInput && serviceSelect && slotContainer) {
    const loadingText = slotContainer.dataset.loadingText || 'Loading available times...';
    const emptyText   = slotContainer.dataset.emptyText || 'No slots available for this date.';
    const errorText   = slotContainer.dataset.errorText || 'Unable to load availability. Please refresh.';

    const fetchSlots = async () => {
      const dentistId = dentistSelect.value;
      const dateVal = dateInput.value;
      const serviceId = serviceSelect.value;

      if (!dentistId || !dateVal) {
        return;
      }

      slotContainer.innerHTML = `<div class="text-muted small py-2"><span class="spinner-border spinner-border-sm me-2"></span>${loadingText}</div>`;

      try {
        const params = new URLSearchParams({
          dentist_id: dentistId,
          date: dateVal,
          service_id: serviceId || '1'
        });
        const resp = await fetch(`api_slots.php?${params.toString()}`, {
          headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json().catch(() => ({}));
        if (resp.status === 401) {
          slotContainer.innerHTML = `<div class="alert alert-warning py-2 px-3 small mb-0">${data.message || errorText}</div>`;
          return;
        }
        if (!resp.ok) throw new Error('Network error');

        if (!data.slots || data.slots.length === 0) {
          slotContainer.innerHTML = `<div class="alert alert-warning py-2 px-3 small mb-0">${data.message || emptyText}</div>`;
          return;
        }

        let html = '<div class="slot-grid">';
        data.slots.forEach((slot, idx) => {
          const id = `slot_${idx}`;
          const disabledAttr = slot.available ? '' : 'disabled';
          const disabledClass = slot.available ? '' : 'slot-disabled';
          html += `
            <div>
              <input type="radio" class="slot-radio" name="appointment_time" id="${id}" value="${slot.time}" ${disabledAttr} required>
              <label class="slot-chip ${disabledClass}" for="${id}">${slot.label}</label>
            </div>
          `;
        });
        html += '</div>';
        slotContainer.innerHTML = html;
      } catch (err) {
        slotContainer.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0">${errorText}</div>`;
      }
    };

    dentistSelect.addEventListener('change', fetchSlots);
    dateInput.addEventListener('change', fetchSlots);
    serviceSelect.addEventListener('change', fetchSlots);

    if (dentistSelect.value && dateInput.value) {
      fetchSlots();
    }
  }
});
