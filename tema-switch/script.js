document.addEventListener("DOMContentLoaded", () => {
    const themeButton = document.getElementById("themeButton");
    const themeModal = document.getElementById("themeModal");
    const closeModal = document.getElementById("closeModal");
    const colorOptions = document.querySelectorAll(".color-option");
    const themeOptions = document.querySelectorAll(".theme-option");
    const applyThemeBtn = document.getElementById("applyTheme");

    let selectedColor = localStorage.getItem("selectedComplementaryColor") || "#ff5722";
    let selectedTheme = localStorage.getItem("selectedTheme") || "claro";

    function applyTheme(theme, complementaryColor) {
        const root = document.documentElement;

        const themes = {
            claro: { "--bg-color": "white", "--text-color": "black", "--card-bg": "#f0f0f0" },
            escuro: { "--bg-color": "#121212", "--text-color": "#ffffff", "--card-bg": "#1e1e1e" },
            azulado: { "--bg-color": "#001f3f", "--text-color": "#f8f9fa", "--card-bg": "#007bff" },
            "azul-esverdeado": { "--bg-color": "#004d40", "--text-color": "#e0f7fa", "--card-bg": "#00796b" },
            "preto-absoluto": { "--bg-color": "#000000", "--text-color": "#ffffff", "--card-bg": "#222222" },
        };

        if (themes[theme]) {
            Object.keys(themes[theme]).forEach((key) => {
                root.style.setProperty(key, themes[theme][key]);
            });
        }

        root.style.setProperty("--complementary-color", complementaryColor);
        localStorage.setItem("selectedTheme", theme);
        localStorage.setItem("selectedComplementaryColor", complementaryColor);
    }

    // Carregar tema salvo
    applyTheme(selectedTheme, selectedColor);

    // Marcar opções selecionadas
    themeOptions.forEach(button => {
        if (button.dataset.theme === selectedTheme) {
            button.classList.add("selected");
        }
    });

    colorOptions.forEach(button => {
        if (button.dataset.color === selectedColor) {
            button.classList.add("selected");
        }
    });

    // Evento: Abrir modal
    themeButton.addEventListener("click", () => {
        themeModal.style.display = "block";
    });

    // Evento: Fechar modal
    closeModal.addEventListener("click", () => {
        themeModal.style.display = "none";
    });

    // Selecionar tema
    themeOptions.forEach(button => {
        button.addEventListener("click", () => {
            themeOptions.forEach(btn => btn.classList.remove("selected"));
            button.classList.add("selected");
            selectedTheme = button.dataset.theme;
        });
    });

    // Selecionar cor complementar
    colorOptions.forEach(button => {
        button.addEventListener("click", () => {
            colorOptions.forEach(btn => btn.classList.remove("selected"));
            button.classList.add("selected");
            selectedColor = button.dataset.color;
        });
    });

    // Aplicar tema
    applyThemeBtn.addEventListener("click", () => {
        applyTheme(selectedTheme, selectedColor);
        themeModal.style.display = "none";
    });
});