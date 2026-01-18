import { fullscreenMode } from "./functions";
import NProgress from "nprogress";
import { Turkish } from "flatpickr/dist/l10n/tr.js";

export default class {
    constructor() {
        this.selectize();
        this.dateTimePicker();
        this.changeAccordionTabState();
        this.preventChangingCurrentTab();
        this.buttonLoading();
        this.confirmationModal();
        this.tooltip();
        this.imageHoverPreview();
        this.shortcuts();
        this.nprogress();
        this.setActiveAccordionTabQueryParam();
        this.setDefaultActiveAccordionTabQueryParam();
        this.stopDropdownPropagation();

        fullscreenMode();
    }

    stopDropdownPropagation() {
        $(".dropdown-menu").on("click", (e) => {
            e.stopPropagation();
        });
    }

    selectize() {
        let selects = $("select.selectize").removeClass(
            "form-control custom-select-black"
        );

        let options = _.merge(
            {
                valueField: "id",
                labelField: "name",
                searchField: "name",
                delimiter: ",",
                persist: true,
                selectOnTab: true,
                hideSelected: true,
                allowEmptyOption: true,
                onItemAdd(value) {
                    this.getItem(value)[0].innerHTML = this.getItem(
                        value
                    )[0].innerHTML.replace(/¦––\s/g, "");
                },
                onInitialize() {
                    for (let index in this.options) {
                        let label = this.options[index].name;
                        let value = this.options[index].id;

                        this.$control
                            .find(`.item[data-value="${value}"]`)
                            .html(
                                label.replace(/¦––\s/g, "") +
                                '<a href="javascript:void(0)" class="remove" tabindex="-1">×</a>'
                            );
                    }
                },
            },
            ...FleetCart.selectize
        );

        for (let select of selects) {
            select = $(select);

            let create = true;
            let plugins = ["remove_button", "restore_on_backspace"];

            if (select.hasClass("prevent-creation")) {
                create = false;

                plugins.remove("restore_on_backspace");
            }

            select.selectize(_.merge(options, { create, plugins }));
        }
    }

    dateTimePicker(elements) {
        elements = elements || $(".datetime-picker");

        elements = elements instanceof jQuery ? elements : $(elements);

        for (let el of elements) {
            $(el).flatpickr({
                mode: el.hasAttribute("data-range") ? "range" : "single",
                enableTime: el.hasAttribute("data-time"),
                noCalender: el.hasAttribute("data-no-calender"),
                altInput: true,
                altFormat: "d.m.Y",
                dateFormat: "Y-m-d",
                locale: Turkish,
            });
        }
    }

    changeAccordionTabState() {
        $('.accordion-box [data-toggle="tab"]').on("click", (e) => {
            if (!$(e.currentTarget).parent().hasClass("active")) {
                $(".accordion-tab li.active").removeClass("active");
            }
        });
    }

    preventChangingCurrentTab() {
        $('[data-toggle="tab"]').on("click", (e) => {
            let targetElement = $(e.currentTarget);

            if (targetElement.parent().hasClass("active")) {
                return false;
            }
        });
    }

    removeSubmitButtonOffsetOn(tabs, tabsSelector = null) {
        tabs = Array.isArray(tabs) ? tabs : [tabs];
        $(tabsSelector || ".accordion-tab li > a").on("click", (e) => {
            if (tabs.includes(e.currentTarget.getAttribute("href"))) {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .removeClass("col-md-offset-2");
                }, 150);
            } else {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .addClass("col-md-offset-2");
                }, 150);
            }
        });
    }

    buttonLoading() {
        $(document).on("click", "[data-loading]", (e) => {
            let button = $(e.currentTarget);

            button
                .data("loading-text", button.html())
                .addClass("btn-loading")
                .button("loading");
        });
    }

    stopButtonLoading(button) {
        button = button instanceof jQuery ? button : $(button);

        button
            .data("loading-text", button.html())
            .removeClass("btn-loading")
            .button("reset");
    }

    confirmationModal() {
        let confirmationModal = $("#confirmation-modal");

        $("[data-confirm]").on("click", () => {
            confirmationModal.modal("show");
        });

        confirmationModal.find("form").on("submit", () => {
            confirmationModal.find("button.delete").prop("disabled", true);
        });

        confirmationModal.on("hidden.bs.modal", () => {
            confirmationModal.find("button.delete").prop("disabled", false);
        });

        confirmationModal.on("shown.bs.modal", () => {
            confirmationModal.find("button.delete").focus();
        });
    }

    tooltip() {
        $('[data-toggle="tooltip"]')
            .tooltip({ trigger: "hover" })
            .on("click", (e) => {
                $(e.currentTarget).tooltip("hide");
            });
    }

    shortcuts() {
        Mousetrap.bind("f1", () => {
            window.open("https://docs.envaysoft.com/", "_blank");
        });

        Mousetrap.bind("?", () => {
            $("#keyboard-shortcuts-modal").modal();
        });
    }

    nprogress() {
        NProgress.configure({ showSpinner: false });

        $(document).ajaxStart(() => NProgress.start());
        $(document).ajaxComplete(() => NProgress.done());
    }

    setActiveAccordionTabQueryParam() {
        $('.accordion-box a[data-toggle="tab"]').on("click", (e) => {
            const target = $(e.currentTarget);
            const targetForm = target.closest("form");
            const queryParam = `?tab=${target.attr("href").slice(1)}`;

            targetForm.attr(
                "action",
                `${targetForm.attr("action").split("?")[0]}${queryParam}`
            );

            history.replaceState(
                null,
                null,
                `${window.location.pathname}${queryParam}`
            );
        });
    }

    setDefaultActiveAccordionTabQueryParam() {
        const activeAccordionTab = $(".accordion-tab li.active a");

        if (activeAccordionTab.length !== 0) {
            const activeAccordionTabForm = activeAccordionTab.closest("form");
            const queryParam = `?tab=${activeAccordionTab
                .attr("href")
                .slice(1)}`;

            activeAccordionTabForm.attr(
                "action",
                `${activeAccordionTabForm.attr("action")}${queryParam}`
            );

            history.replaceState(
                null,
                null,
                `${window.location.pathname}${queryParam}`
            );
        }
    }

    imageHoverPreview() {
        const PREVIEW_ID = 'fc-image-hover-preview';
        const createPreview = (src) => {
            let el = document.getElementById(PREVIEW_ID);
            if (!el) {
                el = document.createElement('div');
                el.id = PREVIEW_ID;
                el.className = 'image-hover-preview';
                el.innerHTML = `<img src="${src}" alt="preview">`;
                document.body.appendChild(el);
            } else {
                const img = el.querySelector('img');
                if (img && img.getAttribute('src') !== src) {
                    img.setAttribute('src', src);
                }
            }
            return el;
        };

        const movePreview = (el, x, y) => {
            const offset = 16;
            const width = 500; // Match CSS width
            const height = 500; // Match CSS height
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            let left = x + offset;
            let top = y + offset;

            // If preview goes off the right edge, flip it to the left side of the cursor
            if (left + width > viewportWidth) {
                left = x - width - offset;
            }

            // If preview goes off the bottom edge, flip it to the top side of the cursor
            if (top + height > viewportHeight) {
                top = y - height - offset;
            }

            // Safety boundary checks
            if (left < 0) left = 10;
            if (top < 0) top = 10;

            el.style.left = `${left}px`;
            el.style.top = `${top}px`;
        };

        const removePreview = () => {
            const el = document.getElementById(PREVIEW_ID);
            if (el) el.remove();
        };

        $(document).on('mouseenter', '.thumbnail-holder img', function (e) {
            const src = e.currentTarget.getAttribute('src');
            const el = createPreview(src);
            movePreview(el, e.clientX, e.clientY);
        });

        $(document).on('mousemove', '.thumbnail-holder img', function (e) {
            const el = document.getElementById(PREVIEW_ID);
            if (el) movePreview(el, e.clientX, e.clientY);
        });

        $(document).on('mouseleave', '.thumbnail-holder img', function () {
            removePreview();
        });
    }
}
