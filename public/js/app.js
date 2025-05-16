document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal-form');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const isEdit = document.querySelector('.pageaccueil').dataset.edit === 'true';

    // Log pour vérifier l'état du DOM
    console.log('DOM chargé. isEdit:', isEdit);

    // Ouvrir la modal (mode création)
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function () {
            console.log('Ouverture de la modal en mode création');
            modal.style.display = 'flex';
        });
    }

    // Ouvrir automatiquement la modal si on est en mode édition
    if (isEdit) {
        console.log('Ouverture automatique de la modal en mode édition');
        modal.style.display = 'flex';
    }

    // Gérer l'ouverture de la modal depuis un bouton de modification
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = btn.dataset.id;

            // Redirige vers /home?edit=ID
            const url = new URL(window.location);
            url.searchParams.set('edit', id);
            window.location.href = url;
        });
    });

    // Fermer la modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function () {
            console.log('Fermeture de la modal');

            modal.style.display = 'none';

            // Nettoie le paramètre ?edit=ID de l'URL sans recharger la page
            const url = new URL(window.location);
            url.searchParams.delete('edit');
            window.history.replaceState({}, '', url);
        });
    }

    // Fermer la modal en cliquant à l'extérieur
    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            console.log('Modal fermée par clic extérieur');
            modal.style.display = 'none';

            // Nettoie l'URL si on ferme par clic extérieur
            const url = new URL(window.location);
            url.searchParams.delete('edit');
            window.history.replaceState({}, '', url);
        }
    });
});


document.querySelectorAll('.toggle-questions').forEach(btn => {
    btn.addEventListener('click', function () {
        // On remonte jusqu'à l'élément .questionnaire qui contient tout
        const questionnaire = btn.closest('.questionnaire');
        if (!questionnaire) return;

        // On cherche l'élément .questions à l'intérieur
        const questionsDiv = questionnaire.querySelector('.questions');
        if (!questionsDiv) {
            console.warn('.questions introuvable dans .questionnaire');
            return;
        }

        // Toggle de l'affichage
        const isHidden = questionsDiv.style.display === 'none' || questionsDiv.style.display === '';
        questionsDiv.style.display = isHidden ? 'block' : 'none';
        btn.textContent = isHidden ? 'Masquer le diagnostique' : 'Dérouler le diagnostique';
    });
});



