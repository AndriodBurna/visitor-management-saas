/**
 * Visitor Management SaaS — base UI behavior.
 * Vanilla JS only.
 */
(function () {
  "use strict";

  var shell = document.querySelector(".app-shell");

  // =========================================================
  // Localized greeting and live Kampala clock
  // =========================================================

  var liveClock = document.querySelector("[data-live-clock]");
  var timeGreeting = document.querySelector("[data-time-greeting]");

  function updateTimeDisplay() {
    var now = new Date();
    var clockFormatter = new Intl.DateTimeFormat("en-GB", {
      timeZone: "Africa/Kampala",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false
    });
    var hourFormatter = new Intl.DateTimeFormat("en-GB", {
      timeZone: "Africa/Kampala",
      hour: "numeric",
      hour12: false
    });
    var hour = parseInt(hourFormatter.format(now), 10);
    var greeting = hour < 12 ? "Good morning" : (hour < 17 ? "Good afternoon" : "Good evening");

    if (liveClock) {
      liveClock.textContent = clockFormatter.format(now);
      liveClock.setAttribute("datetime", now.toISOString());
    }
    if (timeGreeting) {
      timeGreeting.textContent = timeGreeting.textContent.replace(/^(Good morning|Good afternoon|Good evening)/, greeting);
    }
  }

  updateTimeDisplay();
  window.setInterval(updateTimeDisplay, 1000);

  // =========================================================
  // Theme: light / dark mode
  // =========================================================
  // The server renders <html data-theme="..."> using the
  // vms_theme cookie.
  //
  // JavaScript only:
  // 1. switches the current theme,
  // 2. saves the cookie,
  // 3. updates the theme controls.
  //
  // Theme preference is NOT stored in localStorage.

  function getCurrentTheme() {
    return document.documentElement.getAttribute("data-theme") === "dark"
      ? "dark"
      : "light";
  }

  function reflectThemeInControls(theme) {
    document
      .querySelectorAll("[data-theme-toggle]")
      .forEach(function (btn) {
        btn.setAttribute(
          "aria-pressed",
          theme === "dark" ? "true" : "false"
        );

        btn.setAttribute(
          "title",
          theme === "dark"
            ? "Switch to light mode"
            : "Switch to dark mode"
        );
      });

    document
      .querySelectorAll("[data-theme-option]")
      .forEach(function (option) {
        var isActive =
          option.getAttribute("data-theme-option") === theme;

        option.classList.toggle("is-active", isActive);

        option.setAttribute(
          "aria-pressed",
          isActive ? "true" : "false"
        );
      });
  }

  function setTheme(theme) {
    if (theme !== "light" && theme !== "dark") {
      return;
    }

    // Save theme preference in cookie for 1 year.
    document.cookie =
      "vms_theme=" +
      encodeURIComponent(theme) +
      "; path=/; max-age=31536000; SameSite=Lax";

    // Apply immediately on current page.
    document.documentElement.setAttribute(
      "data-theme",
      theme
    );

    reflectThemeInControls(theme);
  }

  // PHP has already rendered the correct theme from the cookie.
  // We only synchronize the buttons when the page loads.
  reflectThemeInControls(getCurrentTheme());

  // Quick sun/moon toggle.
  document
    .querySelectorAll("[data-theme-toggle]")
    .forEach(function (button) {
      button.addEventListener("click", function () {
        var current = getCurrentTheme();

        setTheme(
          current === "dark"
            ? "light"
            : "dark"
        );
      });
    });

  // Light / Dark options in user dropdown.
  document
    .querySelectorAll("[data-theme-option]")
    .forEach(function (button) {
      button.addEventListener("click", function () {
        setTheme(
          button.getAttribute("data-theme-option")
        );
      });
    });

  // =========================================================
  // Sidebar: collapse (desktop)
  // =========================================================

  var collapseBtn =
    document.querySelector("[data-sidebar-collapse]");

  if (collapseBtn && shell) {
    collapseBtn.addEventListener("click", function () {
      shell.classList.toggle(
        "is-sidebar-collapsed"
      );

      try {
        localStorage.setItem(
          "vms.sidebarCollapsed",
          shell.classList.contains(
            "is-sidebar-collapsed"
          )
            ? "1"
            : "0"
        );
      } catch (e) {
        // Storage unavailable — ignore.
      }
    });

    try {
      if (
        localStorage.getItem(
          "vms.sidebarCollapsed"
        ) === "1"
      ) {
        shell.classList.add(
          "is-sidebar-collapsed"
        );
      }
    } catch (e) {
      // Ignore.
    }
  }

  // =========================================================
  // Sidebar: open / close (mobile)
  // =========================================================

  var menuBtn =
    document.querySelector("[data-sidebar-open]");

  var overlay =
    document.querySelector("[data-sidebar-overlay]");

  if (menuBtn && shell) {
    menuBtn.addEventListener("click", function () {
      shell.classList.toggle(
        "is-sidebar-open"
      );
    });
  }

  if (overlay && shell) {
    overlay.addEventListener("click", function () {
      shell.classList.remove(
        "is-sidebar-open"
      );
    });
  }

  // =========================================================
  // Dropdown menus
  // =========================================================

  document.addEventListener(
    "click",
    function (event) {
      var trigger =
        event.target.closest(
          "[data-dropdown-trigger]"
        );

      document
        .querySelectorAll(
          ".dropdown-menu.is-open"
        )
        .forEach(function (menu) {
          if (
            !trigger ||
            menu !==
              trigger.parentElement.querySelector(
                ".dropdown-menu"
              )
          ) {
            menu.classList.remove(
              "is-open"
            );
          }
        });

      if (trigger) {
        var menu =
          trigger.parentElement.querySelector(
            ".dropdown-menu"
          );

        if (menu) {
          menu.classList.toggle(
            "is-open"
          );
        }
      }
    }
  );

  // =========================================================
  // Modals
  // =========================================================

  document
    .querySelectorAll("[data-modal-open]")
    .forEach(function (btn) {
      btn.addEventListener(
        "click",
        function () {
          var modal =
            document.getElementById(
              btn.getAttribute(
                "data-modal-open"
              )
            );

          if (modal) {
            modal.classList.add(
              "is-open"
            );
          }
        }
      );
    });

  document
    .querySelectorAll("[data-modal-close]")
    .forEach(function (btn) {
      btn.addEventListener(
        "click",
        function () {
          var modal =
            btn.closest(
              ".modal-backdrop"
            );

          if (modal) {
            modal.classList.remove(
              "is-open"
            );
          }
        }
      );
    });

  document
    .querySelectorAll(".modal-backdrop")
    .forEach(function (backdrop) {
      backdrop.addEventListener(
        "click",
        function (event) {
          if (
            event.target === backdrop
          ) {
            backdrop.classList.remove(
              "is-open"
            );
          }
        }
      );
    });

  // =========================================================
  // Dismissible alerts
  // =========================================================

  document
    .querySelectorAll("[data-alert-close]")
    .forEach(function (btn) {
      btn.addEventListener(
        "click",
        function () {
          var alertEl =
            btn.closest(".alert");

          if (alertEl) {
            alertEl.remove();
          }
        }
      );
    });

  // =========================================================
  // Table row "select all"
  // =========================================================

  var selectAll =
    document.querySelector(
      "[data-select-all]"
    );

  if (selectAll) {
    selectAll.addEventListener(
      "change",
      function () {
        document
          .querySelectorAll(
            "[data-row-select]"
          )
          .forEach(function (checkbox) {
            checkbox.checked =
              selectAll.checked;
          });
      }
    );
  }
})();