import flatpickr from "flatpickr";
import { Bulgarian } from "flatpickr/dist/l10n/bg.js"

class Script {

  constructor() {
    this.dataPicker();
    this.toggleExportPeriod();
    this.toggleOrderExportPeriod();
    this.subscriptionProducts();
    this.requirePreparationDate();
  }

  dataPicker() {
    flatpickr('.js-datepicker', {
      locale: Bulgarian,
      dateFormat: 'd.m.Y',
      defaultHour: 0,
      defaultMinute: 0
    });

    const eventData = {
      "2026-02-14": 3,
      "2026-02-20": 1,
      "2026-02-25": 5
    };

    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth(); // 0-11

    flatpickr("#subSchedule", {
      inline: true,
      locale: Bulgarian,
      minDate: new Date(currentYear, currentMonth, 1),
      maxDate: new Date(currentYear, currentMonth + 1, 0),
      showMonths: 1,

      onReady: function (selectedDates, dateStr, instance) {
        instance.prevMonthNav.style.display = "none";
        instance.nextMonthNav.style.display = "none";
        instance.monthNav.style.pointerEvents = "none";
      },

      onDayCreate: function (dObj, dStr, fp, dayElem) {
        const date = dayElem.dateObj;
        const dateStrKey = date.getFullYear() + "-" +
          String(date.getMonth() + 1).padStart(2, '0') + "-" +
          String(date.getDate()).padStart(2, '0');

        if (eventData[dateStrKey]) {
          const badge = document.createElement("span");
          badge.className = "event-count-badge";
          badge.innerHTML = eventData[dateStrKey];
          dayElem.appendChild(badge);
        }
      }
    });
  }

  toggleExportPeriod() {
    const selectStatus = document.querySelector('.igs_export_sub_status select') as HTMLSelectElement;
    const selectPeriod = document.querySelector('.igs_export_sub_period') as HTMLElement;

    if (selectStatus && selectPeriod) {
      selectStatus.addEventListener('change', () => {
        if (selectStatus.value !== 'wc-active') {
          selectPeriod.style.display = 'none';
        } else {
          selectPeriod.style.display = '';
        }
      });
    }
  }

  subscriptionProducts() {
    const itemsList = document.getElementById('igs-subscription-items');
    if ( !itemsList ) return;

    const form = itemsList.closest('form') as HTMLFormElement;
    const jq   = (window as any).jQuery;

    const initProductSearch = (select: HTMLSelectElement) => {
      if ( !jq ) return;
      const metaBoxes = (window as any).woocommerce_admin_meta_boxes;
      jq(select).selectWoo({
        ajax: {
          url:      (window as any).ajaxurl || metaBoxes?.ajax_url,
          dataType: 'json',
          delay:    250,
          data: (params: any) => ({
            term:     params.term,
            action:   'igs_search_subscription_products',
            security: metaBoxes?.search_products_nonce || '',
          }),
          processResults: (data: any) => ({
            results: Object.entries(data).map(([id, text]) => ({ id, text }))
          }),
          cache: true,
        },
        minimumInputLength: 1,
        placeholder: select.getAttribute('data-placeholder') || '',
        allowClear:  false,
      });
    };

    // Init selectWoo on all existing product selects.
    itemsList.querySelectorAll<HTMLSelectElement>('.igs-product-search').forEach(initProductSearch);

    // When a product select changes, sync the hidden pid input.
    // Must use jQuery delegation because selectWoo fires change via $.trigger(),
    // which does not reliably bubble to native addEventListener on the parent.
    if ( jq ) {
      jq(itemsList).on('change', '.igs-product-search', function() {
        const val = jq(this).val() as string;
        jq(this).closest('.igs-item-row').find('.igs-pid').val( val || '0' );
      });
    }

    // Remove row – prevent removing the last one.
    itemsList.addEventListener('click', (e: Event) => {
      const target = e.target as HTMLElement;
      if ( !target.classList.contains('igs-remove-item') ) return;
      if ( itemsList.querySelectorAll('.igs-item-row').length <= 1 ) {
        alert('The subscription must contain at least one product.');
        return;
      }
      target.closest('.igs-item-row')?.remove();
    });

    // Add new empty row.
    const addBtn = document.getElementById('igs-add-product-btn') as HTMLButtonElement;
    if ( addBtn ) {
      addBtn.addEventListener('click', () => {
        const tbody = itemsList.querySelector('tbody') || itemsList;
        const row   = document.createElement('tr');
        row.className = 'igs-item-row';
        row.innerHTML = `
          <td class="p-5">
            <input type="hidden" name="igs_line_product_id[]" value="0" class="igs-pid">
            <select class="field igs-product-search w-100"
                    data-placeholder="Search product…"></select>
          </td>
          <td class="p-5 w-80">
            <input type="number" name="igs_line_qty[]" value="1" min="1" class="field ta-c">
          </td>
          <td class="p-5 w-80">
            <button type="button" class="button tc-1 bg-h-1 tc-h-w igs-remove-item">Delete</button>
          </td>
        `;
        tbody.appendChild(row);
        const newSelect = row.querySelector<HTMLSelectElement>('.igs-product-search');
        if ( newSelect ) initProductSearch(newSelect);
      });
    }

    // Client-side validation before submit.
    if ( form ) {
      form.addEventListener('submit', (e: Event) => {
        const zeroPids = itemsList.querySelectorAll<HTMLInputElement>('.igs-pid[value="0"]');
        if ( zeroPids.length > 0 ) {
          e.preventDefault();
          alert('Please select replacement products for all deleted items before saving.');
          return;
        }
        if ( itemsList.querySelectorAll('.igs-item-row').length === 0 ) {
          e.preventDefault();
          alert('The subscription must contain at least one product.');
        }
      });
    }
  }

  toggleOrderExportPeriod() {
    const selectPeriod = document.querySelector('.igs_export_order_period select') as HTMLSelectElement;
    const datePicker   = document.querySelector('.igs_export_order_date') as HTMLElement;

    if (!selectPeriod || !datePicker) return;

    const toggle = () => {
      datePicker.style.display = selectPeriod.value === 'date' ? '' : 'none';
    };

    selectPeriod.addEventListener('change', toggle);
    toggle();
  }

  requirePreparationDate() {
    const prepField = document.getElementById('igs_preparation_date') as HTMLInputElement;
    if ( !prepField ) return; // not on WC order edit page

    const statusSelect = document.getElementById('order_status') as HTMLSelectElement;
    if ( !statusSelect ) return;

    const form = prepField.closest('form') as HTMLFormElement;
    if ( !form ) return;

    const validate = (): boolean => {
      if ( statusSelect.value === 'wc-cooking' && !prepField.value.trim() ) {
        prepField.style.outline = '2px solid red';
        prepField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        prepField.focus();
        return false;
      }
      return true;
    };

    form.addEventListener('submit', (e: Event) => {
      if ( !validate() ) {
        e.preventDefault();
        e.stopPropagation();
      }
    });

    // Clear highlight once the user fills in the field.
    prepField.addEventListener('input', () => {
      prepField.style.outline = '';
    });

    // Also re-run on status change so the outline clears if status changes away from wc-cooking.
    statusSelect.addEventListener('change', () => {
      if ( statusSelect.value !== 'wc-cooking' ) {
        prepField.style.outline = '';
      }
    });
  }

}

new Script;
