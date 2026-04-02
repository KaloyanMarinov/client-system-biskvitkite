import flatpickr from "flatpickr";
import { Bulgarian } from "flatpickr/dist/l10n/bg.js"

class Script {

  constructor() {
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
}

new Script;
