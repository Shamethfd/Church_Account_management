function recalcTotals() {
  let total = 0;
  for (let w = 1; w <= 4; w++) {
    const fields = ['collection_amount','other_offerings','thanks_offering','christian_service','monthly_offering'];
    let rowSum = 0;
    for (const f of fields) {
      const el = document.querySelector(`[data-week="${w}"][name$="[${f}]"]`);
      if (!el) continue;
      rowSum += parseFloat(el.value) || 0;
    }
    const out = document.querySelector(`#week_${w}_sum`);
    if (out) out.textContent = rowSum.toFixed(2);
    total += rowSum;
  }
  const totalEl = document.querySelector('#total_collection');
  if (totalEl) totalEl.value = total.toFixed(2);
}

document.addEventListener('input', (e) => {
  if (e.target && e.target.matches('input[type="number"]')) {
    recalcTotals();
  }
});

document.addEventListener('DOMContentLoaded', () => {
  recalcTotals();
  const layout = document.querySelector('.layout');
  const toggle = document.querySelector('[data-nav-toggle]');
  if (layout && toggle) {
    toggle.addEventListener('click', () => {
      layout.classList.toggle('is-open');
    });
  }
});
