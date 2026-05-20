const data = window.LEXSTUDY_DATA;
const userContext = window.LEXSTUDY_USER || { authenticated: false, csrfToken: "", state: {} };
const storageKey = "lexstudy.v1";
const savedState = safeSavedState();

const state = {
    xp: 0,
    streak: 0,
    solvedCases: 0,
    masteredCards: 0,
    activeCaseId: data.cases[0].id,
    role: "defense",
    flashcardIndex: 0,
    cardFlipped: false,
    quizIndex: 0,
    quizScore: 0,
    answeredQuiz: false,
    ...(userContext.authenticated ? userContext.state : savedState),
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];

function persist() {
    localStorage.setItem(storageKey, JSON.stringify(state));
}

function safeSavedState() {
    try {
        return JSON.parse(localStorage.getItem(storageKey) || "{}");
    } catch (error) {
        return {};
    }
}

function activeCase() {
    return data.cases.find((item) => item.id === state.activeCaseId) || data.cases[0];
}

function levelFromXp(xp) {
    let level = 1;
    data.levels.forEach((required, index) => {
        if (xp >= required) level = index + 1;
    });
    return Math.min(level, data.levels.length);
}

function levelProgress(xp) {
    const levelIndex = levelFromXp(xp) - 1;
    const currentXp = data.levels[levelIndex] || 0;
    const nextXp = data.levels[levelIndex + 1] || xp + 1;
    const required = Math.max(1, nextXp - currentXp);
    return {
        current: Math.max(0, xp - currentXp),
        required,
        percent: Math.min(100, Math.round(((xp - currentXp) / required) * 100)),
    };
}

function applyServerState(serverState) {
    if (!serverState) return;

    Object.assign(state, {
        xp: Number(serverState.xp || 0),
        streak: Number(serverState.streak || 0),
        solvedCases: Number(serverState.solvedCases || 0),
        masteredCards: Number(serverState.masteredCards || 0),
        quizScore: Number(serverState.quizScore || 0),
    });

    persist();
    renderStats();
}

async function syncProgress(action, payload = {}) {
    if (!userContext.authenticated) return;

    try {
        const response = await fetch("progress.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": userContext.csrfToken,
            },
            body: JSON.stringify({ action, payload }),
        });

        if (!response.ok) return;

        const result = await response.json();
        if (result.authenticated && result.state) {
            applyServerState(result.state);
        }
    } catch (error) {
        // Progresso local continua disponível se a sincronização falhar.
    }
}

function addXp(amount, action = "", payload = {}) {
    state.xp += amount;
    persist();
    renderStats();

    if (action) {
        syncProgress(action, payload);
    }
}

function renderStats() {
    const progress = levelProgress(state.xp);
    const level = levelFromXp(state.xp);

    $("[data-total-xp]").textContent = state.xp;
    $("[data-streak]").textContent = state.streak;
    $("[data-solved-cases]").textContent = state.solvedCases;
    $("[data-mastered-cards]").textContent = state.masteredCards;
    $("[data-level-title]").textContent = data.levelTitles[level - 1] || "Mestre";
    $("[data-xp-current]").textContent = progress.current;
    $("[data-xp-required]").textContent = progress.required;
    $(".profile-panel .meter span").style.width = `${progress.percent}%`;
}

function renderCase() {
    const item = activeCase();

    $("[data-case-area]").textContent = item.area;
    $("[data-case-title]").textContent = item.title;
    $("[data-case-description]").textContent = item.description;

    $("[data-case-facts]").innerHTML = item.facts.map((fact) => `<span>${escapeHtml(fact)}</span>`).join("");
    $("[data-case-refs]").innerHTML = item.legal_refs.map((ref) => `<span>${escapeHtml(ref)}</span>`).join("");

    $$("[data-case-id]").forEach((button) => {
        button.classList.toggle("is-active", Number(button.dataset.caseId) === item.id);
    });

    $$("[data-role]").forEach((button) => {
        button.classList.toggle("is-active", button.dataset.role === state.role);
    });
}

function localAnalysisHtml(item, argument) {
    const words = argument.split(/\s+/).filter(Boolean).length;
    const lower = argument.toLowerCase();
    const proofTerms = ["prova", "document", "testemun", "fotografia", "nexo", "critério", "dano"];
    const structureTerms = ["porque", "logo", "assim", "por isso", "requer", "conclui"];
    const proofHits = proofTerms.filter((term) => lower.includes(term)).length;
    const structureHits = structureTerms.filter((term) => lower.includes(term)).length;
    const roleText = state.role === "defense" ? item.defense : item.attack;
    const score = Math.min(100, 32 + Math.min(words, 80) + proofHits * 7 + structureHits * 5);

    let level = "fraca";
    if (score >= 78) level = "forte";
    else if (score >= 58) level = "aproveitável";

    const missing = [];
    if (words < 45) missing.push("desenvolver a tese com mais factos");
    if (proofHits < 2) missing.push("amarrar a argumentação a prova concreta");
    if (structureHits < 1) missing.push("fechar com pedido ou consequência jurídica");

    const nextStep = missing.length ? missing.join(", ") : "antecipar a resposta da parte contrária";

    return `
        <strong>Análise: tese ${level} (${score}/100)</strong>
        <p>${escapeHtml(roleText)}</p>
        <p><b>Ponto vulnerável:</b> ${escapeHtml(nextStep)}.</p>
        <p><b>Contra-argumento provável:</b> a outra parte vai atacar a suficiência da prova e tentar deslocar a discussão para factos que ainda não demonstraste.</p>
    `;
}

async function analyseArgument() {
    const item = activeCase();
    const argument = $("[data-argument]").value.trim();

    if (argument.length < 15) {
        $("[data-coach-output]").innerHTML = "<strong>Tese insuficiente</strong><p>Escreve pelo menos uma ideia jurídica concreta antes da análise.</p>";
        return;
    }

    $("[data-coach-output]").innerHTML = "<strong>A analisar</strong><p>A preparar feedback jurídico.</p>";

    try {
        const response = await fetch("coach.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                case_id: item.id,
                role: state.role,
                argument,
            }),
        });

        if (!response.ok) throw new Error("coach failed");

        const result = await response.json();
        $("[data-coach-output]").innerHTML = result.html || localAnalysisHtml(item, argument);
    } catch (error) {
        $("[data-coach-output]").innerHTML = localAnalysisHtml(item, argument);
    }

    addXp(data.xpRewards.coachFeedback, "coach_feedback", {
        case_id: item.id,
        role: state.role,
    });
}

function renderFlashcard() {
    const card = data.flashcards[state.flashcardIndex % data.flashcards.length];
    $("[data-card-area]").textContent = card.area;
    $("[data-card-front]").textContent = state.cardFlipped ? card.back : card.front;
    $("[data-card-back]").hidden = true;
    $("[data-card-flip]").textContent = state.cardFlipped ? "Pergunta" : "Virar";
}

function renderQuiz() {
    const question = data.quiz[state.quizIndex % data.quiz.length];
    $("[data-quiz-counter]").textContent = `${state.quizIndex + 1} / ${data.quiz.length}`;
    $("[data-quiz-score]").textContent = state.quizScore;
    $("[data-quiz-question]").textContent = question.question;
    $("[data-quiz-explanation]").textContent = "";
    state.answeredQuiz = false;

    $("[data-quiz-options]").innerHTML = question.options
        .map((option, index) => `<button type="button" data-option="${index}">${escapeHtml(option)}</button>`)
        .join("");
}

function answerQuiz(index) {
    if (state.answeredQuiz) return;

    const question = data.quiz[state.quizIndex % data.quiz.length];
    const isCorrect = index === question.answer;
    state.answeredQuiz = true;

    $$("[data-option]").forEach((button) => {
        const option = Number(button.dataset.option);
        if (option === question.answer) button.classList.add("correct");
        if (option === index && !isCorrect) button.classList.add("wrong");
    });

    if (isCorrect) {
        state.quizScore += 1;
        addXp(data.xpRewards.quizCorrect, "quiz_correct", {
            quiz_index: state.quizIndex,
            quiz_id: question.id || state.quizIndex,
        });
    }

    $("[data-quiz-score]").textContent = state.quizScore;
    $("[data-quiz-explanation]").textContent = question.explanation;

    window.setTimeout(() => {
        state.quizIndex = (state.quizIndex + 1) % data.quiz.length;
        persist();
        renderQuiz();
    }, 2300);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

$$("[data-case-id]").forEach((button) => {
    button.addEventListener("click", () => {
        state.activeCaseId = Number(button.dataset.caseId);
        persist();
        renderCase();
    });
});

$$("[data-role]").forEach((button) => {
    button.addEventListener("click", () => {
        state.role = button.dataset.role;
        persist();
        renderCase();
    });
});

const randomCaseButton = $("[data-random-case]");
if (randomCaseButton) {
    randomCaseButton.addEventListener("click", () => {
        const currentIndex = data.cases.findIndex((item) => item.id === state.activeCaseId);
        state.activeCaseId = data.cases[(currentIndex + 1) % data.cases.length].id;
        persist();
        renderCase();
    });
}

$("[data-coach-submit]").addEventListener("click", analyseArgument);

$("[data-mark-solved]").addEventListener("click", () => {
    const item = activeCase();
    state.solvedCases += 1;
    addXp(data.xpRewards.caseSolved, "case_solved", {
        case_id: item.id,
        role: state.role,
    });
    persist();
    renderStats();
});

$("[data-card-flip]").addEventListener("click", () => {
    state.cardFlipped = !state.cardFlipped;
    persist();
    renderFlashcard();
});

$("[data-card-mastered]").addEventListener("click", () => {
    const card = data.flashcards[state.flashcardIndex % data.flashcards.length];
    const masteredCardId = card.id || ((state.flashcardIndex % data.flashcards.length) + 1);
    state.masteredCards += 1;
    state.flashcardIndex = (state.flashcardIndex + 1) % data.flashcards.length;
    state.cardFlipped = false;
    addXp(data.xpRewards.cardReview, "card_mastered", {
        card_id: masteredCardId,
    });
    persist();
    renderFlashcard();
    renderStats();
});

$("[data-quiz-options]").addEventListener("click", (event) => {
    const button = event.target.closest("[data-option]");
    if (!button) return;
    answerQuiz(Number(button.dataset.option));
});

renderStats();
renderCase();
renderFlashcard();
renderQuiz();
