document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar-holder');
    const containerEl = document.getElementById('external-events');

    if (!calendarEl) return;

    const seasonId = calendarEl.dataset.seasonId;

    // 1. Déclarer la sidebar glissable
    if (containerEl) {
        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function (eventEl) {
                const durationMin = eventEl.dataset.duration || 120;
                const hours = Math.floor(durationMin / 60).toString().padStart(2, '0');
                const minutes = (durationMin % 60).toString().padStart(2, '0');

                return {
                    title: eventEl.dataset.title,
                    duration: `${hours}:${minutes}`,
                    extendedProps: {
                        showId: eventEl.dataset.showId
                    }
                };
            }
        });
    }

    // 2. Initialisation FullCalendar Scheduler
    const calendar = new FullCalendar.Calendar(calendarEl, {
        schedulerLicenseKey: 'CC-BY-NC-4.0',
        initialView: 'resourceTimeGridDay',
        locale: 'fr',
        firstDay: 1,
        slotMinTime: '08:00:00',
        slotMaxTime: '24:00:00',
        allDaySlot: false,
        editable: true,
        droppable: true,
        headerToolbar: false,

        // BLOCAGE DES CHEVAUCHEMENTS
        eventOverlap: false,
        selectOverlap: false,

        resources: calendarEl.dataset.venuesUrl,
        events: calendarEl.dataset.eventsUrl,

        // --- ENREGISTREMENT BDD LORS DU DROP ---
        eventReceive: function (info) {
            const resource = info.event.getResources()[0];
            const showId = info.event.extendedProps.showId;

            fetch(calendarEl.dataset.dropUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    showId: showId,
                    venueId: resource ? resource.id : null,
                    seasonId: seasonId,
                    start: info.event.startStr,
                    end: info.event.endStr
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Affecter l'ID de la BDD à l'événement FullCalendar
                        info.event.setProp('id', data.performanceId);

                        // Retirer de la sidebar "À planifier"
                        const externalItem = document.querySelector(`[data-show-id="${showId}"]`);
                        if (externalItem) externalItem.remove();

                        updateCounters(-1);
                    } else {
                        alert(data.message || 'Créneau indisponible.');
                        info.event.remove();
                    }
                })
                .catch(() => {
                    alert('Erreur réseau lors de la sauvegarde.');
                    info.event.remove();
                });
        },

        // --- MISE A JOUR APRES DEPLACEMENT / REDIMENSIONNEMENT ---
        eventDrop: syncPerformanceChange,
        eventResize: syncPerformanceChange,

        // --- SUPPRESSION D'UN SPECTACLE PROGRAMME ---
        eventClick: function (info) {
            if (!info.event.id) {
                alert("Cet événement n'a pas encore été synchronisé avec la base.");
                return;
            }

            if (confirm(`Voulez-vous retirer "${info.event.title}" du planning ?`)) {
                fetch(`/planning/delete/${info.event.id}`, {
                    method: 'DELETE'
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            info.event.remove();
                            if (data.show) {
                                addShowToSidebar(data.show);
                                updateCounters(1);
                            }
                        } else {
                            alert('Erreur lors de la suppression.');
                        }
                    })
                    .catch(() => alert('Erreur réseau.'));
            }
        }
    });

    calendar.render();

    // Synchronisation déplacement / redimensionnement BDD
    function syncPerformanceChange(info) {
        const resource = info.event.getResources()[0];

        fetch(calendarEl.dataset.dropUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                performanceId: info.event.id,
                venueId: resource ? resource.id : null,
                start: info.event.startStr,
                end: info.event.endStr
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Mise à jour impossible (conflit).');
                    info.revert();
                }
            })
            .catch(() => info.revert());
    }

    // Ré-insérer dans la liste de droite
    function addShowToSidebar(show) {
        const list = document.getElementById('external-events');
        if (!list) return;

        const card = document.createElement('div');
        card.className = 'fc-event cursor-grab active:cursor-grabbing p-4 rounded-xl border border-slate-200 bg-white shadow-sm hover:border-blue-400 hover:shadow-md transition mb-3';
        card.dataset.showId = show.id;
        card.dataset.title = show.title;
        card.dataset.duration = show.durationMin || 120;

        card.innerHTML = `
            <div class="flex justify-between items-start">
                <h4 class="font-bold text-sm text-slate-800 leading-tight">${show.title}</h4>
            </div>
            <p class="text-xs text-slate-500 mt-1">${show.companyName || 'Compagnie non renseignée'}</p>
            <div class="mt-3 pt-2 border-t border-slate-100 flex justify-between items-center text-xs text-slate-400">
                <span>${show.durationMin || 120} min</span>
                <span class="font-bold text-slate-700">Impact : ${show.estimatedCost || 0} €</span>
            </div>
        `;

        list.appendChild(card);
    }

    function updateCounters(delta) {
        const countBadge = document.getElementById('unassigned-count');
        const tabBadge = document.getElementById('unassigned-count-badge');
        if (countBadge) countBadge.textContent = Math.max(0, parseInt(countBadge.textContent || '0') + delta);
        if (tabBadge) tabBadge.textContent = Math.max(0, parseInt(tabBadge.textContent || '0') + delta);
    }
    // Fonction de mise à jour de l'affichage du budget
    function updateBudgetUI(budgetData) {
        if (!budgetData) return;

        const spentEl = document.getElementById('season-spent-budget');
        const progressBar = document.getElementById('season-budget-bar');
        const percentEl = document.getElementById('season-budget-percent');

        if (spentEl) {
            spentEl.textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(budgetData.totalSpent);
        }

        if (progressBar) {
            progressBar.style.width = `${Math.min(budgetData.percentage, 100)}%`;

            // Alerte visuelle si dépassement
            if (budgetData.percentage > 100) {
                progressBar.classList.remove('bg-blue-600', 'bg-amber-500');
                progressBar.classList.add('bg-red-600');
            } else if (budgetData.percentage > 85) {
                progressBar.classList.remove('bg-blue-600', 'bg-red-600');
                progressBar.classList.add('bg-amber-500');
            }
        }

        if (percentEl) {
            percentEl.textContent = `${budgetData.percentage}%`;
        }
    }

    // Navigation en-tête
    document.querySelectorAll('[data-calendar-view]').forEach(btn => {
        btn.addEventListener('click', function () {
            calendar.changeView(this.dataset.calendarView);
            document.querySelectorAll('[data-calendar-view]').forEach(b => {
                b.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
            });
            this.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
        });
    });

    document.getElementById('btn-prev')?.addEventListener('click', () => { calendar.prev(); updateDateTitle(); });
    document.getElementById('btn-next')?.addEventListener('click', () => { calendar.next(); updateDateTitle(); });
    document.getElementById('btn-today')?.addEventListener('click', () => { calendar.today(); updateDateTitle(); });

    function updateDateTitle() {
        const titleEl = document.getElementById('calendar-current-title');
        if (titleEl) titleEl.textContent = calendar.currentData.viewTitle;
    }
    updateDateTitle();
});