const composer = document.querySelector("[data-assistant-composer]");
const input = document.querySelector("[data-assistant-input]");
const thread = document.querySelector(".assistant-thread");

function resizeInput() {
    if (!input) return;
    input.style.height = "auto";
    input.style.height = `${Math.min(input.scrollHeight, 180)}px`;
}

if (thread) {
    const latest = document.getElementById("latest");
    (latest || thread.lastElementChild)?.scrollIntoView({ block: "end" });
}

if (input) {
    resizeInput();
    input.addEventListener("input", resizeInput);
    input.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" || event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        event.preventDefault();
        if (input.value.trim() !== "") {
            composer?.requestSubmit();
        }
    });
}

if (composer) {
    composer.addEventListener("submit", (event) => {
        const submitter = event.submitter;
        const hasPromptButton = submitter?.name === "message" && submitter.value.trim() !== "";
        const hasTypedMessage = input?.value.trim() !== "";

        if (!hasPromptButton && !hasTypedMessage) {
            event.preventDefault();
            input?.focus();
            return;
        }

        composer.classList.add("is-sending");
    });
}
