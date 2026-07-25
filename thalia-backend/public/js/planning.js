document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-holder');
    const containerEl = document.getElementById('external-events');

    if (!calendarEl) return;

    // 1. Activation du Drag & Drop depuis la sidebar
    if (containerEl) {
        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function(eventEl) {
                return {
                    title: eventEl.dataset.title,
                    duration: '02:00', // Durée par défaut
                    extendedProps: {
                        showId: eventEl.dataset.showId
                    }
                };
            }
        });
    }

    // 2. Initialisation de FullCalendar Scheduler
    const calendar = new FullCalendar.Calendar(calendarEl, {
        schedulerLicenseKey: 'CC-BY-NC-4.0', // Clé de licence
        initialView: 'resourceTimeGridDay',
        locale: 'fr',
        firstDay: 1,
        slotMinTime: '08:00:00',
        slotMaxTime: '24:00:00',
        allDaySlot: false,
        editable: true,
        droppable: true,
        headerToolbar: false, // On utilise nos propres boutons personnalisés (En-tête Thalia)

        // Chargement des Salles (Colonnes)
        resources: calendarEl.dataset.venuesUrl,

        // Chargement des Événements
        events: calendarEl.dataset.eventsUrl,

        // Réception d'un élément glissé depuis la sidebar
        eventReceive: function(info) {
            const showId = info.event.extendedProps.showId;
            const venueId = info.event.getResources()[0]?.id;
            const start = info.event.startStr;
            const end = info.event.endStr;

            fetch(calendarEl.dataset.dropUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    showId: showId,
                    venueId: venueId,
                    start: start,
                    end: end
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Supprimer la carte de la sidebar
                    const externalItem = document.querySelector(`[data-show-id="${showId}"]`);
                    if (externalItem) externalItem.remove();

                    // Décrémenter le compteur
                    const countBadge = document.getElementById('unassigned-count');
                    const tabBadge = document.getElementById('unassigned-count-badge');
                    if (countBadge) countBadge.textContent = Math.max(0, parseInt(countBadge.textContent) - 1);
                    if (tabBadge) tabBadge.textContent = Math.max(0, parseInt(tabBadge.textContent) - 1);
                } else {
                    info.event.remove(); // Annuler si erreur
                }
            });
        },

        // Déplacement ou redimensionnement d'un événement existant
        eventDrop: updatePerformanceDates,
        eventResize: updatePerformanceDates
    });

    calendar.render();

    // Fonction de mise à jour suite au déplacement
    function updatePerformanceDates(info) {
        fetch(calendarEl.dataset.dropUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                performanceId: info.event.id,
                venueId: info.event.getResources()[0]?.id,
                start: info.event.startStr,
                end: info.event.endStr
            })
        });
    }

    // 3. Liaison des boutons d'en-tête Thalia
    document.querySelectorAll('[data-calendar-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.calendarView;
            calendar.changeView(view);
            
            // Style actif
            document.querySelectorAll('[data-calendar-view]').forEach(b => {
                b.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                b.classList.add('hover:text-slate-900');
            });
            this.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
        });
    });

    document.getElementById('btn-prev')?.addEventListener('click', () => {
        calendar.prev();
        updateDateTitle();
    });
    document.getElementById('btn-next')?.addEventListener('click', () => {
        calendar.next();
        updateDateTitle();
    });
    document.getElementById('btn-today')?.addEventListener('click', () => {
        calendar.today();
        updateDateTitle();
    });

    function updateDateTitle() {
        const titleEl = document.getElementById('calendar-current-title');
        if (titleEl) {
            titleEl.textContent = calendar.currentData.viewTitle;
        }
    }
    updateDateTitle();
});