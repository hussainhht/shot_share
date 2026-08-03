(() => {
  "use strict";

  const root = document.documentElement;
  const themeStorageKey = "shot-share-theme";
  const sidebarStorageKey = "shot-share-sidebar";

  const readPreference = (key) => {
    try {
      return window.localStorage.getItem(key);
    } catch (error) {
      return null;
    }
  };

  const savePreference = (key, value) => {
    try {
      window.localStorage.setItem(key, value);
    } catch (error) {
      // The interface still works when storage is unavailable.
    }
  };

  const storedTheme = readPreference(themeStorageKey);

  if (storedTheme === "light" || storedTheme === "dark") {
    root.dataset.theme = storedTheme;
  }

  if (readPreference(sidebarStorageKey) === "collapsed") {
    root.dataset.sidebar = "collapsed";
  }

  const initialiseInterface = () => {
    const sidebarToggle = document.getElementById("sidebar-toggle");
    const sidebarToggleIcon = document.getElementById("sidebar-toggle-icon");
    const themeToggle = document.getElementById("theme-toggle");
    const themeToggleIcon = document.getElementById("theme-toggle-icon");
    const themeToggleLabel = document.getElementById("theme-toggle-label");
    const systemTheme = window.matchMedia("(prefers-color-scheme: light)");

    const isSidebarCollapsed = () => root.dataset.sidebar === "collapsed";

    const updateSidebarControl = () => {
      if (!sidebarToggle || !sidebarToggleIcon) return;

      const collapsed = isSidebarCollapsed();
      const controlLabel = collapsed ? "Expand sidebar" : "Collapse sidebar";

      sidebarToggle.setAttribute("aria-expanded", String(!collapsed));
      sidebarToggle.setAttribute("aria-label", controlLabel);
      sidebarToggle.title = controlLabel;
      sidebarToggleIcon.textContent = collapsed ? "\u203a" : "\u2039";
    };

    const resolvedTheme = () => {
      if (root.dataset.theme === "light") return "light";
      if (root.dataset.theme === "dark") return "dark";

      return systemTheme.matches ? "dark" : "light";
    };

    const updateThemeControl = () => {
      if (!themeToggle || !themeToggleIcon || !themeToggleLabel) return;

      const currentTheme = resolvedTheme();
      const darkModeActive = currentTheme === "dark";
      const nextThemeLabel = darkModeActive ? "Light Mode" : "Dark Mode";

      themeToggle.setAttribute("aria-pressed", String(darkModeActive));
      themeToggle.setAttribute(
        "aria-label",
        `Switch to ${nextThemeLabel.toLowerCase()}`,
      );
      themeToggle.title = `Switch to ${nextThemeLabel}`;
      themeToggleLabel.textContent = nextThemeLabel;
      themeToggleIcon.textContent = darkModeActive ? "\u2600" : "\u25d0";
    };

    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", () => {
        const collapsed = !isSidebarCollapsed();

        if (collapsed) {
          root.dataset.sidebar = "collapsed";
        } else {
          delete root.dataset.sidebar;
        }

        savePreference(sidebarStorageKey, collapsed ? "collapsed" : "expanded");
        updateSidebarControl();
      });
    }

    if (themeToggle) {
      themeToggle.addEventListener("click", () => {
        const nextTheme = resolvedTheme() === "dark" ? "light" : "dark";

        root.dataset.theme = nextTheme;
        savePreference(themeStorageKey, nextTheme);
        updateThemeControl();
      });
    }

    if (typeof systemTheme.addEventListener === "function") {
      systemTheme.addEventListener("change", () => {
        if (!root.dataset.theme) {
          updateThemeControl();
        }
      });
    }

    updateSidebarControl();
    updateThemeControl();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialiseInterface);
  } else {
    initialiseInterface();
  }
})();
