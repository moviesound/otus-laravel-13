import "jsvectormap/dist/jsvectormap.css";
import "flatpickr/dist/flatpickr.min.css";
import "../css/satoshi.css";
import "../css/style.css";
import { apiFetch } from "./lib/api"

import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import flatpickr from "flatpickr";
import chart01 from "./components/chart-01";
import chart02 from "./components/chart-02";
import chart03 from "./components/chart-03";
import chart04 from "./components/chart-04";
import map01 from "./components/map-01";

window.apiFetch = apiFetch;

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.store('modal', {
    open: false,
    content: '',

    alertOpen: false,
    alertMessage: '',
    reloadOnClose: false,

    confirmOpen: false,
    confirmMessage: '',
    confirmAction: null,

    openModal(html) {
        this.content = html
        this.open = true
    },

    close() {
        this.open = false
        this.content = ''
    },

    showAlert(message, reload = false) {
        this.alertMessage = message
        this.alertOpen = true
        this.reloadOnClose = reload
    },

    closeAlert() {
        this.alertOpen = false
        this.alertMessage = ''

        if (this.reloadOnClose) {
            window.location.reload()
        }

        this.reloadOnClose = false
    },

    showConfirm(message, action) {
        this.confirmMessage = message
        this.confirmAction = action
        this.confirmOpen = true
    },

    closeConfirm() {
        this.confirmOpen = false
        this.confirmMessage = ''
        this.confirmAction = null
    },

    async submit(e) {
        const form = e.target;
        const method = form.querySelector('[name=_method]')?.value || 'POST';

        const { res, data } = await apiFetch(form.action, {
            method,
            headers: {
                'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
            },
            body: new FormData(form),
        });

        if (!res.ok) {
            this.showAlert(data.message || 'Ошибка');
            return;
        }

        this.close();
        this.showAlert(data.message || 'Успешно', true);
    },
    async delete(e, id) {
        const form = e.currentTarget;
        const modal = Alpine.store('modal');

        modal.showConfirm('Удалить запись?', async () => {

            const method = form.querySelector('[name=_method]')?.value || 'DELETE';

            const { res, data } = await apiFetch(form.action, {
                method,
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
                },
            });

            if (!res.ok) {
                modal.showAlert(data.message || 'Ошибка');
                return;
            }

            modal.showAlert(data.message || 'Удалено', true);
        });
    },

});
Alpine.start();

// Init flatpickr
flatpickr(".datepicker", {
  mode: "range",
  static: true,
  monthSelectorType: "static",
  dateFormat: "M j, Y",
  defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
  prevArrow:
    '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M5.4 10.8l1.4-1.4-4-4 4-4L5.4 0 0 5.4z" /></svg>',
  nextArrow:
    '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M1.4 10.8L0 9.4l4-4-4-4L1.4 0l5.4 5.4z" /></svg>',
  onReady: (selectedDates, dateStr, instance) => {
    // eslint-disable-next-line no-param-reassign
    instance.element.value = dateStr.replace("to", "-");
    const customClass = instance.element.getAttribute("data-class");
    instance.calendarContainer.classList.add(customClass);
  },
  onChange: (selectedDates, dateStr, instance) => {
    // eslint-disable-next-line no-param-reassign
    instance.element.value = dateStr.replace("to", "-");
  },
});

flatpickr(".form-datepicker", {
  mode: "single",
  static: true,
  monthSelectorType: "static",
  dateFormat: "M j, Y",
  prevArrow:
    '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M5.4 10.8l1.4-1.4-4-4 4-4L5.4 0 0 5.4z" /></svg>',
  nextArrow:
    '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M1.4 10.8L0 9.4l4-4-4-4L1.4 0l5.4 5.4z" /></svg>',
});

// Document Loaded
document.addEventListener("DOMContentLoaded", () => {
  chart01();
  chart02();
  chart03();
  chart04();
  map01();
});
