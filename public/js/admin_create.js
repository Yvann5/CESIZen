document.addEventListener('DOMContentLoaded', () => {
    const questionsContainer = document.getElementById('questions-container');
    const addQuestionBtn = document.getElementById('add-question');
    let questionIndex = questionsContainer.querySelectorAll('.question-block').length;

    function decodeHtml(html) {
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    }

    function initAddReponse(questionBlock) {
        const reponsesContainer = questionBlock.querySelector('.reponses-container');
        if (!reponsesContainer) return;

        const addReponseBtn = reponsesContainer.querySelector('.add-reponse');
        if (!addReponseBtn) return;

        let reponseIndex = reponsesContainer.querySelectorAll('.reponse-block').length;

        addReponseBtn.addEventListener('click', () => {
            const prototype = decodeHtml(reponsesContainer.dataset.prototype);
            const newForm = prototype.replace(/__name__/g, reponseIndex);

            const div = document.createElement('div');
            div.classList.add('reponse-block');
            div.innerHTML = newForm;

            reponsesContainer.insertBefore(div, addReponseBtn);
            reponseIndex++;
        });
    }

    // Initialisation des réponses pour les questions existantes
    questionsContainer.querySelectorAll('.question-block').forEach(questionBlock => {
        initAddReponse(questionBlock);
    });

    // Ajout d'une question
    addQuestionBtn.addEventListener('click', () => {
        const prototype = decodeHtml(questionsContainer.dataset.prototype);
        const newForm = prototype.replace(/__name__/g, questionIndex);

        const div = document.createElement('div');
        div.classList.add('question-block');
        div.innerHTML = newForm;

        // Ajout dans le DOM
        questionsContainer.appendChild(div);

        // Initialiser les réponses pour cette question
        initAddReponse(div);

        questionIndex++;
    });
});
