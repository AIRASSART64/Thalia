import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// Initialisation globale de Tom Select
export function initTomSelect() {
    if (typeof TomSelect === 'undefined') return;

    document.querySelectorAll('.tom-select').forEach((el) => {
        if (el.classList.contains('tomselected')) return;

        new TomSelect(el, {
            create: true,
            delimiter: ',',
            persist: false,
            plugins: ['remove_button'],
        });
    });
}

// Événements DOM et Turbo
document.addEventListener("DOMContentLoaded", initTomSelect);
document.addEventListener("turbo:load", initTomSelect);

/**
 * Gestion de la prévisualisation de l'avatar de profil
 */
export function initAvatarPreview() {
    const avatarInput = document.getElementById('avatar-input');
    if (!avatarInput) return;

    // Évite d'attacher plusieurs fois l'écouteur d'événement
    if (avatarInput.dataset.previewInitialized) return;
    avatarInput.dataset.previewInitialized = "true";

    avatarInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('avatar-preview');
                const icon = document.getElementById('avatar-icon');

                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (icon) {
                    icon.classList.add('hidden');
                }

                // Décommentez la ligne ci-dessous si vous souhaitez soumettre automatiquement le formulaire lors du choix de l'image
                // document.getElementById('avatar-form')?.submit();
            };
            reader.readAsDataURL(file);
        }
    });
}

// Initialisation au chargement standard et lors des navigations Turbo
document.addEventListener("DOMContentLoaded", initAvatarPreview);
document.addEventListener("turbo:load", initAvatarPreview);

// assets/app.js


// assets/app.js

/**
 * Prévisualisation d'images (Avatar, Affiches, Visuels...)
 */
window.handleImagePreview = function(input, previewImgId, placeholderIconId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const previewImg = document.getElementById(previewImgId);
            const placeholderIcon = document.getElementById(placeholderIconId);

            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
            }
            if (placeholderIcon) {
                placeholderIcon.classList.add('hidden');
            }
        };

        reader.readAsDataURL(file);
    }
};

/**
 * Prévisualisation de documents (PDF, Dossiers d'artistes, etc.)
 */
window.handleDocumentPreview = function(input, infoContainerId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const infoContainer = document.getElementById(infoContainerId);

        if (infoContainer) {
            const fileSize = (file.size / (1024 * 1024)).toFixed(2); // Taille en Mo
            infoContainer.innerHTML = `
                <span class="font-medium text-amber-600 inline-flex items-center gap-1">
                    <i class="fa-solid fa-file-circle-check text-amber-500"></i> Nouveau fichier sélectionné :
                </span>
                <span class="font-semibold text-slate-700">${file.name} (${fileSize} Mo)</span>
            `;
        }
    }
};