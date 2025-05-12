document.addEventListener('DOMContentLoaded', function () {
    const questionContainer = document.getElementById('questionnaire_questions');
    const addQuestionButton = document.getElementById('add-question');
    let questionIndex = questionContainer.querySelectorAll('.question-block').length;

    addQuestionButton.addEventListener('click', function () {
        let prototype = questionContainer.dataset.prototype;
        let newForm = prototype.replace(/__name__/g, questionIndex);
        let div = document.createElement('div');
        div.classList.add('question-block');
        div.innerHTML = newForm;
        questionContainer.appendChild(div);

        addReponseHandler(div); // ajoute le bouton réponse à la nouvelle question
        questionIndex++;
    });

    // Initialiser les boutons de réponses pour les questions existantes
    document.querySelectorAll('.question-block').forEach((block) => {
        addReponseHandler(block);
    });

    function addReponseHandler(questionBlock) {
        const reponseContainer = questionBlock.querySelector('.reponses-container');
        if (!reponseContainer) return;

        let reponseIndex = reponseContainer.querySelectorAll('.reponse-block').length;

        const addReponseBtn = document.createElement('button');
        addReponseBtn.type = 'button';
        addReponseBtn.classList.add('add-reponse');
        addReponseBtn.textContent = 'Ajouter une réponse';
        questionBlock.appendChild(addReponseBtn);

        addReponseBtn.addEventListener('click', function () {
            const prototype = reponseContainer.dataset.prototype;
            const newForm = prototype.replace(/__name__/g, reponseIndex);
            const div = document.createElement('div');
            div.classList.add('reponse-block');
            div.innerHTML = newForm;
            reponseContainer.appendChild(div);
            reponseIndex++;
        });
    }
});
