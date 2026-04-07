<style>
    html[data-embedded-context="1"] .fi-sidebar,
    html[data-embedded-context="1"] .fi-topbar {
        display: none !important;
    }

    html[data-embedded-context="1"] .fi-main {
        padding-top: 0 !important;
    }

    html[data-embedded-context="1"] .fi-main-ctn,
    html[data-embedded-context="1"] .fi-page {
        max-width: 100% !important;
    }
</style>

<script>
    (function () {
        try {
            var sidebarPinKey = "spms:sidebar:pinned";
            var sidebarPinned = localStorage.getItem(sidebarPinKey);

            if (sidebarPinned !== "1" && sidebarPinned !== "0") {
                sidebarPinned = "0";
            }

            document.documentElement.setAttribute(
                "data-sidebar-pinned",
                sidebarPinned
            );

            var sidebarTitleSelector = [
                ".fi-main-sidebar .fi-sidebar-item-btn",
                ".fi-main-sidebar .fi-sidebar-group-btn",
                ".fi-main-sidebar .fi-sidebar-group-dropdown-trigger-btn",
                ".fi-main-sidebar .fi-sidebar-database-notifications-btn"
            ].join(",");

            var syncSidebarItemTitles = function () {
                var isCollapsed = document.documentElement.getAttribute("data-sidebar-pinned") === "0";
                var nodes = document.querySelectorAll(sidebarTitleSelector);

                nodes.forEach(function (el) {
                    if (isCollapsed) {
                        if (el.hasAttribute("title") && !el.dataset.spmsOriginalTitle) {
                            el.dataset.spmsOriginalTitle = el.getAttribute("title") || "";
                        }
                        el.removeAttribute("title");

                        return;
                    }

                    if (el.dataset.spmsOriginalTitle !== undefined) {
                        if (el.dataset.spmsOriginalTitle.length > 0) {
                            if (el.getAttribute("title") !== el.dataset.spmsOriginalTitle) {
                                el.setAttribute("title", el.dataset.spmsOriginalTitle);
                            }
                        } else {
                            if (el.hasAttribute("title")) {
                                el.removeAttribute("title");
                            }
                        }

                        delete el.dataset.spmsOriginalTitle;
                        return;
                    }
                });
            };

            var syncSidebarItemTooltips = function () {
                var isCollapsed = document.documentElement.getAttribute("data-sidebar-pinned") === "0";
                var nodes = document.querySelectorAll(sidebarTitleSelector);

                nodes.forEach(function (el) {
                    if (!el || !el._tippy) {
                        return;
                    }

                    if (isCollapsed) {
                        el._tippy.hide();
                        el._tippy.disable();
                        el.removeAttribute("aria-describedby");
                        return;
                    }

                    el._tippy.enable();
                });
            };

            var observeSidebarDom = function () {
                var sidebar = document.querySelector(".fi-main-sidebar");
                if (!sidebar || !window.MutationObserver) {
                    return;
                }

                var observer = new MutationObserver(function () {
                    syncSidebarItemTitles();
                    syncSidebarItemTooltips();
                });

                observer.observe(sidebar, {
                    childList: true,
                    subtree: true,
                });
            };

            var observePinnedFlag = function () {
                if (!window.MutationObserver) {
                    return;
                }

                var observer = new MutationObserver(function () {
                    syncSidebarItemTitles();
                    syncSidebarItemTooltips();
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ["data-sidebar-pinned"],
                });
            };

            var isEmbedded = window.self !== window.top
                || (new URLSearchParams(window.location.search)).get("embed") === "1";

            if (isEmbedded) {
                document.documentElement.setAttribute("data-embedded-context", "1");
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", function () {
                    syncSidebarItemTitles();
                    syncSidebarItemTooltips();
                    observeSidebarDom();
                    observePinnedFlag();
                }, { once: true });
            } else {
                syncSidebarItemTitles();
                syncSidebarItemTooltips();
                observeSidebarDom();
                observePinnedFlag();
            }
        } catch (e) {
            // no-op
        }
    })();
</script>
