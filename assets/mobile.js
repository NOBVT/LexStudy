(() => {
  const root = document.documentElement;
  const body = document.body;

  const setViewportHeight = () => {
    root.style.setProperty("--vh", `${window.innerHeight * 0.01}px`);
  };

  const setInputMode = () => {
    body.classList.toggle("is-touch-device", matchMedia("(pointer: coarse)").matches);
  };

  setViewportHeight();
  setInputMode();
  window.addEventListener("resize", setViewportHeight, { passive: true });
  window.addEventListener("orientationchange", setViewportHeight, { passive: true });

  if ("serviceWorker" in navigator && (location.protocol === "https:" || location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
    window.addEventListener("load", () => {
      navigator.serviceWorker.register("sw.js").catch(() => {
        body.dataset.pwa = "unsupported";
      });
    });
  }

  const updateNetworkState = () => {
    body.dataset.network = navigator.onLine ? "online" : "offline";
  };

  updateNetworkState();
  window.addEventListener("online", updateNetworkState);
  window.addEventListener("offline", updateNetworkState);

  document.addEventListener("click", async (event) => {
    const button = event.target.closest("[data-copy-value]");
    if (!button) {
      return;
    }

    const value = button.getAttribute("data-copy-value") || "";
    if (!value) {
      return;
    }

    try {
      await navigator.clipboard.writeText(value);
      button.dataset.copied = "true";
      const original = button.textContent;
      button.textContent = "Copiado";
      setTimeout(() => {
        button.textContent = original;
        delete button.dataset.copied;
      }, 1400);
    } catch {
      button.dataset.copied = "error";
    }
  });
})();
