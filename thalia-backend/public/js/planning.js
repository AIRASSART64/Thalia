document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar-holder');
    const containerEl = document.getElementById('external-events');

    if (!calendarEl || !containerEl) {
        console.warn('Éléments du calendrier non trouvés dans le DOM.');
        return;
    }

    // Récupération des paramètres dynamiques depuis les attributs data-* du conteneur
    const seasonId = calendarEl.dataset.seasonId;
    const dropUrl = calendarEl.dataset.dropUrl;
    const eventsUrl = calendarEl.dataset.eventsUrl;
    const venuesUrl = calendarEl.dataset.venuesUrl;

    // 1. Initialiser le Drag & Drop sur la sidebar des spectacles "À planifier"
    if (typeof FullCalendar.Draggable !== 'undefined') {
        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText.trim(),
                    duration: eventEl.dataset.duration || '02:00',
                    create: false // Empêche l'ajout automatique avant la validation serveur
                };
            }
        });
    }

    // 2. Initialisation du Planning Multi-Salles (FullCalendar)
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'resourceTimeGridDay',
        locale: 'fr',
        timeZone: 'Europe/Paris',
        headerToolbar: false, // Masqué car contrôlé par vos boutons Twig
        slotMinTime: '08:00:00',
        slotMaxTime: '01:00:00',
        editable: true,
        droppable: true,
        height: '100%',
        nowIndicator: true,

        // Chargement dynamique des salles (colonnes) et des événements
        resources: venuesUrl,
        events: eventsUrl,

        // Action lorsqu'un spectacle est glissé-déposé depuis la sidebar
        drop: function (info) {
            const draggedEl = info.draggedEl;
            const showId = draggedEl.dataset.showId;
            const venueId = info.resource ? info.resource.id : null;
            const startTime = info.dateStr;

            if (!showId || !venueId) {
                alert('Veuillez déposer le spectacle dans une colonne de salle valide.');
                return;
            }

            // Requête AJAX pour enregistrer la Performance en base de données
            fetch(dropUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    show_id: showId,
                    venue_id: venueId,
                    season_id: seasonId,
                    start_time: startTime
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau ou serveur');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Recharge le planning et retire le spectacle de la sidebar
                    calendar.refetchEvents();
                    draggedEl.remove();

                    // Mettre à jour le compteur de spectacles à planifier s'il existe
                    const badge = document.getElementById('unassigned-count');
                    if (badge) {
                        let count = parseInt(badge.innerText, 10) - 1;
                        badge.innerText = Math.max(0, count);
                    }
                } else {
                    alert('Erreur : ' + (data.message || 'Impossible de planifier le spectacle.'));
                }
            })
            .catch(error => {
                console.error('Erreur lors du drop :', error);
                alert('Une erreur s\'est produite lors de la sauvegarde.');
            });
        },

        // Style personnalisé pour afficher les blocs de montage / démontage
        eventContent: function (arg) {
            let event = arg.event;
            let setup = event.extendedProps.setupDuration ? `<div class="text-[10px] bg-sky-200 text-sky-800 font-semibold px-1 py-0.5 rounded-t">MONTAGE ${event.extendedProps.setupDuration} min</div>` : '';
            let teardown = event.extendedProps.teardownDuration ? `<div class="text-[10px] bg-slate-200 text-slate-800 font-semibold px-1 py-0.5 rounded-b">DÉMONTAGE ${event.extendedProps.teardownDuration} min</div>` : '';

            return {
                html: `
                    <div class="h-full flex flex-col justify-between overflow-hidden p-1">
                        ${setup}
                        <div class="font-bold text-xs leading-tight my-1">${event.title}</div>
                        <div class="text-[10px] opacity-80">${arg.timeText}</div>
                        ${teardown}
                    </div>
                `
            };
        }
    });

    calendar.render();

    // 3. Liaison des boutons de contrôle personnalisés du header (Jour / Semaine / Mois)
    document.querySelectorAll('[data-calendar-view]').forEach(button => {
        button.addEventListener('click', function () {
            const view = this.dataset.calendarView;
            calendar.changeView(view);
        });
    });

    document.getElementById('btn-prev')?.addEventListener('click', () => calendar.prev());
    document.getElementById('btn-next')?.addEventListener('click', () => calendar.next());
    document.getElementById('btn-today')?.addEventListener('click', () => calendar.today());
});