const flipButton = document.querySelector("[data-flip-card]");
const front = document.querySelector("[data-card-front]");
const answer = document.querySelector("[data-card-answer]");
const gradeForm = document.querySelector(".grade-actions");
const flashcardContext = window.LEXSTUDY_FLASHCARD_CONTEXT || { authenticated: false };

if (flipButton && front && answer) {
    flipButton.addEventListener("click", () => {
        const answerHidden = answer.hidden;
        answer.hidden = !answerHidden;
        front.hidden = answerHidden;
        flipButton.textContent = answerHidden ? "Pergunta" : "Virar";
    });
}

if (gradeForm && front && answer) {
    gradeForm.addEventListener("click", (event) => {
        const button = event.target.closest("button[name='grade']");
        if (!button || flashcardContext.authenticated) return;
        if (!["again", "hard"].includes(button.value)) return;

        try {
            const items = JSON.parse(localStorage.getItem("lexstudy.mistakes.v1") || "[]");
            const meta = document.querySelector(".flashcard span")?.textContent || "Flashcard";
            const deck = document.querySelector(".flash-review-panel h2")?.textContent || "Revisão";
            items.unshift({
                id: Date.now(),
                status: "open",
                created_at: new Date().toISOString(),
                source_type: "flashcard",
                area: meta.split("·")[0]?.trim() || "Geral",
                title: `Flashcard: ${deck.trim()}`,
                prompt: front.textContent.trim(),
                correction: answer.textContent.trim(),
                next_step: button.value === "again"
                    ? "Voltar a responder esta carta ainda hoje."
                    : "Rever amanhã e explicar em voz alta sem olhar.",
                weight: button.value === "again" ? 4 : 3,
            });
            localStorage.setItem("lexstudy.mistakes.v1", JSON.stringify(items.slice(0, 40)));
        } catch (error) {
            // O caderno local é auxiliar; não deve bloquear o envio da revisão.
        }
    });
}
