document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "backend/index.php/api/urls";

  const shortenForm = document.getElementById("shortenForm");
  const originalUrlInput = document.getElementById("originalUrl");
  const submitBtn = document.getElementById("submitBtn");
  const refreshBtn = document.getElementById("refreshBtn");
  const copyBtn = document.getElementById("copyBtn");
  const closeStatsBtn = document.getElementById("closeStats");
  const messageBox = document.getElementById("message");
  const listContainer = document.getElementById("listContainer");
  const createdResult = document.getElementById("createdResult");
  const createdId = document.getElementById("createdId");
  const createdCode = document.getElementById("createdCode");
  const createdOriginal = document.getElementById("createdOriginal");
  const createdShortUrl = document.getElementById("createdShortUrl");
  const statsCard = document.getElementById("statsCard");
  const statsContent = document.getElementById("statsContent");

  const requiredElements = {
    shortenForm,
    originalUrlInput,
    submitBtn,
    refreshBtn,
    copyBtn,
    closeStatsBtn,
    messageBox,
    listContainer,
    createdResult,
    createdId,
    createdCode,
    createdOriginal,
    createdShortUrl,
    statsCard,
    statsContent
  };

  const missing = Object.entries(requiredElements)
    .filter(([, value]) => !value)
    .map(([key]) => key);

  if (missing.length > 0) {
    console.error("Faltan elementos en el HTML:", missing);
    return;
  }

  let currentShortUrl = "";

  function getProjectBasePath() {
    let path = window.location.pathname;

    if (path.endsWith("index.html")) {
      path = path.replace(/\/index\.html$/, "");
    } else if (path.endsWith("/")) {
      path = path.slice(0, -1);
    }

    return path;
  }

  function buildShortUrl(shortCode) {
    const basePath = getProjectBasePath();
    return `${window.location.origin}${basePath}/backend/index.php/${shortCode}`;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function showMessage(text, type = "success") {
    messageBox.textContent = text;
    messageBox.className = `message ${type}`;
    messageBox.classList.remove("hidden");
  }

  function hideMessage() {
    messageBox.textContent = "";
    messageBox.className = "message hidden";
  }

  async function request(url, options = {}) {
    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json"
      },
      ...options
    });

    const raw = await response.text();
    let data = null;

    try {
      data = raw ? JSON.parse(raw) : null;
    } catch (error) {
      throw new Error(`La respuesta no vino en JSON. Revisa la ruta ${url}`);
    }

    if (!response.ok) {
      throw new Error(
        (data && (data.error || data.message)) || `Error HTTP ${response.status}`
      );
    }

    if (data && data.error) {
      throw new Error(data.error);
    }

    return data;
  }

  function renderCreatedUrl(data) {
    const dynamicShortUrl = buildShortUrl(data.short_code);

    createdId.textContent = data.id;
    createdCode.textContent = data.short_code;
    createdOriginal.textContent = data.original_url;
    createdShortUrl.textContent = dynamicShortUrl;
    createdShortUrl.href = dynamicShortUrl;
    currentShortUrl = dynamicShortUrl;
    createdResult.classList.remove("hidden");
  }

  function renderUrlList(urls) {
    if (!Array.isArray(urls) || urls.length === 0) {
      listContainer.innerHTML = `<p class="empty">Todavía no hay URLs registradas.</p>`;
      return;
    }

    listContainer.innerHTML = urls
      .map((url) => {
        const dynamicShortUrl = buildShortUrl(url.short_code);

        return `
          <article class="url-item">
            <p><strong>ID:</strong> ${escapeHtml(url.id)}</p>
            <p><strong>Código:</strong> ${escapeHtml(url.short_code)}</p>
            <p><strong>Original:</strong> ${escapeHtml(url.original_url)}</p>
            <p><strong>Corta:</strong> <a href="${escapeHtml(dynamicShortUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(dynamicShortUrl)}</a></p>
            <p><strong>Clicks:</strong> ${escapeHtml(url.click_count ?? 0)}</p>
            <p><strong>Creada:</strong> ${escapeHtml(url.created_at ?? "N/D")}</p>
            <div class="actions">
              <button type="button" data-copy="${escapeHtml(dynamicShortUrl)}">Copiar</button>
              <button type="button" class="secondary" data-stats="${escapeHtml(url.id)}">Ver estadísticas</button>
            </div>
          </article>
        `;
      })
      .join("");
  }

  async function loadUrls() {
    listContainer.innerHTML = `<p class="empty">Cargando URLs...</p>`;

    try {
      const data = await request(API_BASE, {
        method: "GET"
      });
      renderUrlList(data);
    } catch (error) {
      listContainer.innerHTML = `<p class="empty">${escapeHtml(error.message)}</p>`;
    }
  }

  async function loadStats(id) {
    try {
      const data = await request(`${API_BASE}/${id}/stats`, {
        method: "GET"
      });

      const countries =
        Array.isArray(data.countries) && data.countries.length > 0
          ? `<ul>${data.countries.map((country) => `<li>${escapeHtml(country)}</li>`).join("")}</ul>`
          : `<p>No hay países registrados todavía.</p>`;

      const clicksByDay =
        Array.isArray(data.clicks_by_day) && data.clicks_by_day.length > 0
          ? `<ul>${data.clicks_by_day.map((item) => `<li>${escapeHtml(item.day)}: ${escapeHtml(item.total)} clics</li>`).join("")}</ul>`
          : `<p>No hay clics registrados por día.</p>`;

      statsContent.innerHTML = `
        <div class="stats-box">
          <p><strong>ID:</strong> ${escapeHtml(data.id)}</p>
          <p><strong>Código corto:</strong> ${escapeHtml(data.short_code)}</p>
          <p><strong>URL original:</strong> ${escapeHtml(data.original_url)}</p>
          <p><strong>Total de clics:</strong> ${escapeHtml(data.click_count)}</p>
          <p><strong>Fecha de creación:</strong> ${escapeHtml(data.created_at)}</p>
          <h3>Países detectados</h3>
          ${countries}
          <h3>Clics por día</h3>
          ${clicksByDay}
        </div>
      `;

      statsCard.classList.remove("hidden");
      statsCard.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch (error) {
      showMessage(error.message, "error");
    }
  }

  async function copyText(text) {
    try {
      await navigator.clipboard.writeText(text);
      showMessage("Enlace copiado al portapapeles.");
    } catch (error) {
      showMessage("No fue posible copiar el enlace.", "error");
    }
  }

  shortenForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideMessage();
    createdResult.classList.add("hidden");
    submitBtn.disabled = true;

    try {
      const payload = {
        original_url: originalUrlInput.value.trim()
      };

      const data = await request(API_BASE, {
        method: "POST",
        body: JSON.stringify(payload)
      });

      renderCreatedUrl(data);
      shortenForm.reset();
      showMessage("La URL se creó correctamente.");
      await loadUrls();
    } catch (error) {
      showMessage(error.message, "error");
    } finally {
      submitBtn.disabled = false;
    }
  });

  refreshBtn.addEventListener("click", loadUrls);

  copyBtn.addEventListener("click", () => {
    if (currentShortUrl) {
      copyText(currentShortUrl);
    }
  });

  closeStatsBtn.addEventListener("click", () => {
    statsCard.classList.add("hidden");
    statsContent.innerHTML = "";
  });

  listContainer.addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button) return;

    const copyTarget = button.getAttribute("data-copy");
    const statsTarget = button.getAttribute("data-stats");

    if (copyTarget) {
      copyText(copyTarget);
    }

    if (statsTarget) {
      loadStats(statsTarget);
    }
  });

  loadUrls();
});
