const data = window.LEXSTUDY_DATA;
const userContext = window.LEXSTUDY_USER || { authenticated: false, csrfToken: "", state: {} };
const storageKey = "lexstudy.v1";
const mistakeStorageKey = "lexstudy.mistakes.v1";
const savedState = safeSavedState();

const state = {
    xp: 0,
    streak: 0,
    solvedCases: 0,
    activeCaseId: data.cases[0].id,
    role: "defense",
    ...(userContext.authenticated ? userContext.state : savedState),
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];

function safeSavedState() {
    try {
        return JSON.parse(localStorage.getItem(storageKey) || "{}");
    } catch (error) {
        return {};
    }
}

function persist() {
    localStorage.setItem(storageKey, JSON.stringify(state));
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

function renderStats() {
    const progress = levelProgress(state.xp);
    const level = levelFromXp(state.xp);

    $("[data-total-xp]").textContent = state.xp;
    $("[data-streak]").textContent = state.streak;
    $("[data-solved-cases]").textContent = state.solvedCases;
    $("[data-level-title]").textContent = data.levelTitles[level - 1] || "Mestre";
    $("[data-xp-current]").textContent = progress.current;
    $("[data-xp-required]").textContent = progress.required;
    $(".profile-panel .meter span").style.width = `${progress.percent}%`;
}

function applyServerState(serverState) {
    if (!serverState) return;
    Object.assign(state, {
        xp: Number(serverState.xp || 0),
        streak: Number(serverState.streak || 0),
        solvedCases: Number(serverState.solvedCases || 0),
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
        if (result.authenticated && result.state) applyServerState(result.state);
    } catch (error) {
        // Mantém a sessão local se a rede ou a base de dados falharem.
    }
}

function addXp(amount, action = "", payload = {}) {
    state.xp += amount;
    persist();
    renderStats();
    if (action) syncProgress(action, payload);
}

function saveLocalMistake(mistake) {
    if (userContext.authenticated) return;

    try {
        const items = JSON.parse(localStorage.getItem(mistakeStorageKey) || "[]");
        items.unshift({
            id: Date.now(),
            status: "open",
            created_at: new Date().toISOString(),
            ...mistake,
        });
        localStorage.setItem(mistakeStorageKey, JSON.stringify(items.slice(0, 40)));
    } catch (error) {
        // O caderno local é auxiliar; não deve bloquear o treino.
    }
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

    $("[data-case-report]").hidden = true;
}

function localAnalysisHtml(item, argument) {
    const words = argument.split(/\s+/).filter(Boolean).length;
    const lower = argument.toLowerCase();
    const proofTerms = ["prova", "document", "testemun", "fotografia", "nexo", "critério", "dano", "art"];
    const structureTerms = ["porque", "logo", "assim", "por isso", "requer", "conclui", "deve"];
    const proofHits = proofTerms.filter((term) => lower.includes(term)).length;
    const structureHits = structureTerms.filter((term) => lower.includes(term)).length;
    const roleText = state.role === "defense" ? item.defense : item.attack;
    const score = Math.min(100, 28 + Math.min(words, 84) + proofHits * 7 + structureHits * 4);
    const level = score >= 78 ? "forte" : (score >= 58 ? "aproveitável" : "fraca");
    const missing = [];

    if (words < 55) missing.push("mais desenvolvimento factual");
    if (proofHits < 2) missing.push("prova concreta");
    if (structureHits < 2) missing.push("conclusão jurídica mais clara");

    return `
        <strong>Análise: tese ${level} (${score}/100)</strong>
        <p>${escapeHtml(roleText)}</p>
        <p><b>Fragilidade:</b> ${escapeHtml(missing.length ? missing.join(", ") : "antecipar melhor a resposta da outra parte")}.</p>
        <p><b>Próximo exercício:</b> reescreve a tese em três blocos: facto provado, norma aplicável, consequência jurídica.</p>
    `;
}

async function analyseArgument() {
    const item = activeCase();
    const argument = $("[data-argument]").value.trim();

    if (argument.length < 15) {
        $("[data-coach-output]").innerHTML = "<strong>Tese insuficiente</strong><p>Escreve uma posição jurídica concreta antes da análise.</p>";
        return;
    }

    $("[data-coach-output]").innerHTML = "<strong>A analisar</strong><p>A preparar feedback jurídico.</p>";

    try {
        const response = await fetch("coach.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ case_id: item.id, role: state.role, argument }),
        });

        if (!response.ok) throw new Error("coach failed");
        const result = await response.json();
        const html = result.html || localAnalysisHtml(item, argument);
        $("[data-coach-output]").innerHTML = html;
        appendSessionLog(item.title, html);
    } catch (error) {
        const html = localAnalysisHtml(item, argument);
        $("[data-coach-output]").innerHTML = html;
        appendSessionLog(item.title, html);
    }

    const feedbackText = $("[data-coach-output]").textContent || "";
    const mistake = shouldLogCaseMistake(argument, feedbackText)
        ? buildCaseMistake(item, argument, feedbackText)
        : null;
    if (mistake) saveLocalMistake(mistake);

    addXp(data.xpRewards.coachFeedback, "coach_feedback", {
        case_id: item.id,
        role: state.role,
        ...(mistake ? { mistake } : {}),
    });
}

function shouldLogCaseMistake(argument, feedbackText) {
    const words = argument.split(/\s+/).filter(Boolean).length;
    const lower = feedbackText.toLowerCase();
    return words < 70 || ["fragilidade", "falha", "vulner", "insuficiente", "fraca"].some((term) => lower.includes(term));
}

function buildCaseMistake(item, argument, feedbackText) {
    const roleLabel = state.role === "defense" ? "Defesa" : "Acusação";

    return {
        source_type: "case",
        source_id: item.id,
        area: item.area,
        title: `${roleLabel}: ${item.title}`,
        prompt: argument,
        correction: feedbackText.slice(0, 900) || "Rever a estrutura da tese e aplicar melhor os factos às normas.",
        next_step: "Reescrever a tese em três blocos: facto provado, norma aplicável, conclusão.",
        weight: argument.split(/\\s+/).filter(Boolean).length < 55 ? 4 : 2,
    };
}

function appendSessionLog(title, html) {
    const log = $("[data-session-log]");
    const text = stripHtml(html);
    log.innerHTML = `
        <strong>Histórico da sessão</strong>
        <p><b>${escapeHtml(title)}</b></p>
        <p>${escapeHtml(text.slice(0, 260))}${text.length > 260 ? "..." : ""}</p>
    `;
}

function generateReport() {
    const item = activeCase();
    const argument = $("[data-argument]").value.trim();
    const roleLabel = state.role === "defense" ? "Defesa" : "Acusação";
    const report = $("[data-case-report]");

    report.hidden = false;
    report.innerHTML = `
        <span class="eyebrow">Relatório de treino</span>
        <h3>${escapeHtml(item.title)}</h3>
        <p><b>Papel:</b> ${roleLabel}</p>
        <p><b>Problema jurídico:</b> ${escapeHtml(item.description)}</p>
        <p><b>Tese registada:</b> ${escapeHtml(argument || "Sem tese escrita.")}</p>
        <p><b>Factos-chave:</b> ${escapeHtml(item.facts.join(" "))}</p>
        <p><b>Referências:</b> ${escapeHtml(item.legal_refs.join(", "))}</p>
    `;
}

function stripHtml(html) {
    const node = document.createElement("div");
    node.innerHTML = html;
    return node.textContent || "";
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

$("[data-random-case]").addEventListener("click", () => {
    const currentIndex = data.cases.findIndex((item) => item.id === state.activeCaseId);
    state.activeCaseId = data.cases[(currentIndex + 1) % data.cases.length].id;
    persist();
    renderCase();
});

$("[data-coach-submit]").addEventListener("click", analyseArgument);
$("[data-generate-report]").addEventListener("click", generateReport);
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

renderStats();
renderCase();
