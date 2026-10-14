"use strict";
const toggle = document.querySelector(".menu-toggle"),
  nav = document.querySelector(".main-nav");
if (toggle && nav) {
  toggle.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    toggle.setAttribute("aria-expanded", String(open));
    toggle.querySelector("i").className = open
      ? "fa-solid fa-xmark"
      : "fa-solid fa-bars";
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      nav.classList.remove("open");
      toggle.setAttribute("aria-expanded", "false");
    }
  });
}
const year = document.getElementById("year");
if (year) year.textContent = new Date().getFullYear();
// Replace the placeholder site key in assets/js/site-config.js before enabling forms.
async function getToken(action) {
  if (
    !window.DRIVE4U_CONFIG ||
    !window.DRIVE4U_CONFIG.recaptchaSiteKey ||
    window.DRIVE4U_CONFIG.recaptchaSiteKey.includes("REPLACE_")
  )
    throw Error("reCAPTCHA is not configured yet. Please add your site key.");
  const key = window.DRIVE4U_CONFIG.recaptchaSiteKey;
  if (!window.grecaptcha) {
    await new Promise((resolve, reject) => {
      const script = document.createElement("script");
      script.src =
        "https://www.google.com/recaptcha/api.js?render=" +
        encodeURIComponent(key);
      script.async = true;
      script.onload = resolve;
      script.onerror = () => reject(Error("Could not load reCAPTCHA."));
      document.head.appendChild(script);
    });
  }
  return new Promise((resolve, reject) => {
    grecaptcha.ready(() =>
      grecaptcha.execute(key, { action }).then(resolve).catch(reject),
    );
  });
}
for (const form of document.querySelectorAll(".ajax-form")) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const status = form.querySelector(".form-status"),
      button = form.querySelector('button[type="submit"]');
    status.textContent = "Sending your message…";
    status.className = "form-status";
    button.disabled = true;
    try {
      const csrf = await fetch("api/csrf.php", {
        credentials: "same-origin",
        cache: "no-store",
      });
      if (!csrf.ok) throw Error("Unable to start a secure form session.");
      const data = await csrf.json();
      form.elements.csrf_token.value = data.token;
      form.elements.recaptcha_token.value = await getToken(form.dataset.form);
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });
      const result = await response
        .json()
        .catch(() => ({ message: "An unexpected server error occurred." }));
      if (!response.ok || !result.ok)
        throw Error(result.message || "Unable to send your message.");
      status.textContent = result.message;
      status.classList.add("success");
      form.reset();
    } catch (err) {
      status.textContent = err.message || "Please try again later.";
      status.classList.add("error");
    } finally {
      button.disabled = false;
    }
  });
}
