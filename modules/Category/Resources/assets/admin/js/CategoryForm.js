import CategoryTree from "./CategoryTree";
import { generateSlug } from "@admin/js/functions";

export default class {
    constructor() {
        let tree = $(".category-tree");

        new CategoryTree(this, tree);

        this.collapseAll(tree);
        this.expandAll(tree);
        this.addRootCategory();
        this.addSubCategory();
        this.removeSubmitButtonOffsetOn(
            "#image",
            ".category-details-tab li > a"
        );

        $("#category-form").on("submit", this.submit.bind(this));

        this.initFaqRepeater();
        this.initSlugGenerator();
    }

    initSlugGenerator() {
        const $nameInput = $("#name");
        const $slugInput = $("#slug");
        const $idInput = $("#id");

        if (!$nameInput.length || !$slugInput.length || !$idInput.length) {
            return;
        }

        // 1) Yeni kategori oluştururken: name değişince slug boşsa otomatik oluştur
        $nameInput.off("change.category-slug").on("change.category-slug", (e) => {
            const isNew = !$idInput.val();

            if (!isNew) {
                return;
            }

            const value = (e.currentTarget.value || "").trim();

            if (!value) {
                return;
            }

            if (!$slugInput.val()) {
                $slugInput.val(generateSlug(value));
            }
        });

        // 2) SEO sekmesindeki slug inputuna sihirli buton ekle
        if (!$slugInput.closest(".input-group").length) {
            $slugInput.wrap('<div class="input-group"></div>');
        }

        const $group = $slugInput.closest(".input-group");

        if (!$group.find(".btn-generate-category-slug").length) {
            const buttonHtml = `
                <span class="input-group-btn">
                    <button
                        type="button"
                        class="btn btn-default btn-generate-category-slug"
                        title="Slug oluştur"
                        aria-label="Slug oluştur"
                    >
                        <i class="fa fa-magic" aria-hidden="true"></i>
                    </button>
                </span>
            `;

            $group.append(buttonHtml);
        }

        $group.off("click.category-slug", ".btn-generate-category-slug")
            .on("click.category-slug", ".btn-generate-category-slug", () => {
                const base = ($nameInput.val() || $slugInput.val() || "").trim();

                if (!base) {
                    return;
                }

                $slugInput.val(generateSlug(base));
            });
    }

    collapseAll(tree) {
        $(".collapse-all").on("click", (e) => {
            e.preventDefault();

            tree.jstree("close_all");
        });
    }

    expandAll(tree) {
        $(".expand-all").on("click", (e) => {
            e.preventDefault();

            tree.jstree("open_all");
        });
    }

    addRootCategory() {
        $(".add-root-category").on("click", () => {
            this.loading(true);

            $(".add-sub-category").addClass("disabled");

            $(".category-tree").jstree("deselect_all");

            this.clear();

            // Intentionally delay 150ms so that user feel new form is loaded.
            setTimeout(this.loading, 150, false);
        });
    }

    addSubCategory() {
        $(".add-sub-category").on("click", () => {
            let selectedId = $(".category-tree").jstree("get_selected")[0];

            if (selectedId === undefined) {
                return;
            }

            this.clear();
            this.loading(true);

            window.form.appendHiddenInput(
                "#category-form",
                "parent_id",
                selectedId
            );

            // Intentionally delay 150ms so that user feel new form is loaded.
            setTimeout(this.loading, 150, false);
        });
    }

    fetchCategory(id) {
        this.loading(true);

        $(".add-sub-category").removeClass("disabled");

        axios
            .get(`/categories/${id}`)
            .then((response) => {
                this.update(response.data);
                this.loading(false);
            })
            .catch((error) => {
                error(error.response.data.message);

                this.loading(false);
            });
    }

    update(category) {
        window.form.removeErrors();

        $(".btn-delete").removeClass("hide");
        $(".form-group .help-block").remove();

        $("#confirmation-form").attr(
            "action",
            `${window.FleetCart.baseUrl}/admin/categories/${category.id}`
        );

        $("#id-field").removeClass("hide");

        $("#id").val(category.id);
        $("#name").val(category.name);

        $("#slug").val(category.slug);
        $("#slug-field").removeClass("hide");
        $(".category-details-tab .seo-tab").removeClass("hide");

        $("#is_searchable").prop("checked", category.is_searchable);
        $("#is_active").prop("checked", category.is_active);

        $("#meta_title").val(category.meta_title || "");
        $("#meta_description").val(category.meta_description || "");

        // Kategori açıklamasını hem textarea'ya hem de mevcutsa TinyMCE editörüne yansıt
        const description = category.description || "";
        $("#description").val(description);

        if (window.tinymce && window.tinymce.get("description")) {
            window.tinymce.get("description").setContent(description);
        }

        $(".logo .image-holder-wrapper").html(
            this.categoryImage("logo", category.logo)
        );
        $(".banner .image-holder-wrapper").html(
            this.categoryImage("banner", category.banner)
        );

        $('#category-form input[name="parent_id"]').remove();
        $("#meta_title").val(category.meta_title);
        $("#meta_description").val(category.meta_description);

        const faqItems = Array.isArray(category.faq_items)
            ? category.faq_items
            : [];

        this.renderFaqItems(faqItems);
    }

    categoryImage(fieldName, file) {
        if (!file.exists) {
            return this.imagePlaceholder();
        }

        return `
            <div class="image-holder">
                <img src="${file.path}">
                <button type="button" class="btn remove-image" data-input-name="files[${fieldName}]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M6.00098 17.9995L17.9999 6.00053" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.9999 17.9995L6.00098 6.00055" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <input type="hidden" name="files[${fieldName}]" value="${file.id}">
            </div>
        `;
    }

    clear() {
        $("#id-field").addClass("hide");

        $("#id").val("");
        $("#name").val("");

        $("#slug").val("");
        $("#slug-field").addClass("hide");
        $(".category-details-tab .seo-tab").addClass("hide");

        $("#is_searchable").prop("checked", false);
        $("#is_active").prop("checked", false);

        // Clear description textarea and TinyMCE editor
        $("#description").val("");
        if (window.tinymce && window.tinymce.get("description")) {
            window.tinymce.get("description").setContent("");
        }

        // Clear SEO fields
        $("#meta_title").val("");
        $("#meta_description").val("");

        $(".logo .image-holder-wrapper").html(this.imagePlaceholder());
        $(".banner .image-holder-wrapper").html(this.imagePlaceholder());

        $(".btn-delete").addClass("hide");
        $(".form-group .help-block").remove();

        $('#category-form input[name="parent_id"]').remove();

        $(".general-information-tab a").click();

        this.renderFaqItems([]);
    }

    imagePlaceholder() {
        return `
            <div class="image-holder placeholder">
                <i class="fa fa-picture-o"></i>
            </div>
        `;
    }

    loading(state) {
        if (state === true) {
            $(".overlay.loader").removeClass("hide");
        } else {
            $(".overlay.loader").addClass("hide");
        }
    }

    submit(e) {
        let selectedId = $(".category-tree").jstree("get_selected")[0];

        this.reindexFaqItems();

        // Remove completely empty FAQ items before submit to satisfy validation rules
        const $faqContainer = $("#faq-items");

        if ($faqContainer.length) {
            $faqContainer.find(".faq-item-row").each(function () {
                const $row = $(this);
                const question =
                    ($row
                        .find(".faq-question-input")
                        .val() || "")
                        .trim();
                const answer =
                    ($row
                        .find(".faq-answer-textarea")
                        .val() || "")
                        .trim();

                if (!question && !answer) {
                    $row.remove();
                }
            });

            // If no FAQ rows remain, ensure no faq_items inputs are submitted
            if (!$faqContainer.find(".faq-item-row").length) {
                $faqContainer
                    .find(".faq-question-input, .faq-answer-textarea")
                    .remove();
            } else {
                // Reindex again after removals
                this.reindexFaqItems();
            }
        }

        if (!$("#slug-field").hasClass("hide")) {
            window.form.appendHiddenInput("#category-form", "_method", "put");

            $("#category-form").attr(
                "action",
                `${window.FleetCart.baseUrl}/admin/categories/${selectedId}`
            );
        }

        e.currentTarget.submit();
    }

    initFaqRepeater() {
        const $container = $("#faq-items");

        if (!$container.length) {
            return;
        }

        $("#add-faq-item").on("click", (e) => {
            e.preventDefault();
            const index = $container.find(".faq-item-row").length;
            const row = this.buildFaqRow(index, "", "");
            $container.append(row);
        });

        $container.on("click", ".faq-remove", (e) => {
            e.preventDefault();
            $(e.currentTarget).closest(".faq-item-row").remove();
            this.reindexFaqItems();
        });

        $container.on("click", ".faq-move-up", (e) => {
            e.preventDefault();
            const $row = $(e.currentTarget).closest(".faq-item-row");
            const $prev = $row.prev(".faq-item-row");
            if ($prev.length) {
                $row.insertBefore($prev);
                this.reindexFaqItems();
            }
        });

        $container.on("click", ".faq-move-down", (e) => {
            e.preventDefault();
            const $row = $(e.currentTarget).closest(".faq-item-row");
            const $next = $row.next(".faq-item-row");
            if ($next.length) {
                $row.insertAfter($next);
                this.reindexFaqItems();
            }
        });

        // Ensure indices are correct on first load
        this.reindexFaqItems();
    }

    buildFaqRow(index, question, answer) {
        const $row = $(
            '<div class="faq-item-row panel panel-default m-b-10" data-index="' +
            index +
            '">' +
            '<div class="panel-heading clearfix">' +
            '  <div class="pull-left" style="width: 70%;">' +
            '    <input type="text" class="form-control input-sm faq-question-input" />' +
            "  </div>" +
            '  <div class="pull-right text-right" style="width: 30%;">' +
            '    <button type="button" class="btn btn-xs btn-default faq-move-up"><i class="fa fa-arrow-up"></i></button>' +
            '    <button type="button" class="btn btn-xs btn-default faq-move-down"><i class="fa fa-arrow-down"></i></button>' +
            '    <button type="button" class="btn btn-xs btn-danger faq-remove"><i class="fa fa-times"></i></button>' +
            "  </div>" +
            "</div>" +
            '<div class="panel-body">' +
            '  <textarea class="form-control input-sm faq-answer-textarea" rows="3"></textarea>' +
            "</div>" +
            "</div>"
        );

        $row.find(".faq-question-input").val(question || "");
        $row.find(".faq-answer-textarea").val(answer || "");

        return $row;
    }

    reindexFaqItems() {
        $("#faq-items .faq-item-row").each(function (index) {
            $(this).attr("data-index", index);

            $(this)
                .find(".faq-question-input")
                .attr("name", `faq_items[${index}][question]`);

            $(this)
                .find(".faq-answer-textarea")
                .attr("name", `faq_items[${index}][answer]`);
        });
    }

    renderFaqItems(items) {
        const $container = $("#faq-items");

        if (!$container.length) {
            return;
        }

        $container.empty();

        if (!items || !items.length) {
            items = [{ question: "", answer: "" }];
        }

        items.forEach((item, index) => {
            const question = item.question ?? item.q ?? "";
            const answer = item.answer ?? item.a ?? "";
            const row = this.buildFaqRow(index, question, answer);
            $container.append(row);
        });

        this.reindexFaqItems();
    }

    removeSubmitButtonOffsetOn(tabs, tabsSelector = null) {
        tabs = Array.isArray(tabs) ? tabs : [tabs];

        $(tabsSelector).on("click", (e) => {
            if (tabs.includes(e.currentTarget.getAttribute("href"))) {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .removeClass("col-md-offset-3");
                }, 150);
            } else {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .addClass("col-md-offset-3");
                }, 150);
            }
        });
    }
}
