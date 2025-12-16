import { nprogress } from "@admin/js/NProgress";
import { fullscreenMode } from "@admin/js/functions";
import Alpine from "alpinejs";
import tinyMce from "@admin/js/wysiwyg";
import Errors from "@admin/js/Errors";

window.Alpine = Alpine;

let textEditor;
let tagsSelect;

Alpine.data("postEdit", ({ formData = {}, meta = {}, tags = [] }) => ({
    formSubmitting: false,
    formSubmissionType: null,
    form: {
        ...formData,
        featured_image: Array.isArray(formData.featured_image)
            ? {}
            : formData.featured_image,
    },
    errors: new Errors(),

    init() {
        this.form.meta = {
            ...(meta.meta_title && {
                meta_title: meta.meta_title,
            }),
            ...(meta.meta_description && {
                meta_description: meta.meta_description,
            }),
        };

        nprogress();
        fullscreenMode();

        textEditor = this.initTinyMce();
        tagsSelect = this.initTagsSelectize();

        const tagIds = Array.isArray(tags)
            ? (tags.length && typeof tags[0] === "object"
                ? tags.map((tag) => tag.id)
                : tags)
            : [];

        if (tagsSelect && tagsSelect.length && tagIds.length) {
            tagsSelect[0].selectize.setValue(tagIds.map((id) => String(id)));
        }

        this.initRelatedProductsSelectize();
    },

    initTinyMce() {
        return tinyMce({
            setup: (editor) => {
                editor.on("change", () => {
                    editor.save();
                    editor.getElement().dispatchEvent(new Event("input"));

                    this.errors.clear("description");
                });
            },
        });
    },

    initRelatedProductsSelectize() {
        const relatedSelect = $("#blog-related-products-select");

        if (!relatedSelect.length) {
            return;
        }

        const preselected = relatedSelect.data("selected") || [];

        const selectizeInstance = relatedSelect.selectize({
            plugins: ["remove_button"],
            delimiter: ",",
            persist: false,
            create: (input) => {
                // Sadece sayısal ID girişine izin ver; aksi halde yoksay.
                const numeric = parseInt(input, 10);

                if (Number.isNaN(numeric)) {
                    return false;
                }

                return { value: numeric, text: numeric };
            },
        })[0].selectize;

        if (preselected.length) {
            selectizeInstance.setValue(preselected.map((id) => String(id)));
        }
    },

    initTagsSelectize() {
        return $(".selectize-tags").selectize({
            plugins: ["remove_button"],
            delimiter: ",",
            persist: true,
            create: (input, callback) => {
                const trimmed = (input || "").trim();

                if (!trimmed) {
                    return callback();
                }

                // Eğer aynı isimde mevcut bir tag varsa, onu kullan.
                const existingOption = $("#tags option").filter((_, el) => {
                    return el.text.toLowerCase() === trimmed.toLowerCase();
                }).first();

                if (existingOption.length) {
                    return callback({
                        value: existingOption.val(),
                        text: existingOption.text(),
                    });
                }

                axios
                    .post("/blog/tags", { name: trimmed })
                    .then(({ data }) => {
                        if (!data || !data.id) {
                            callback();

                            return;
                        }

                        // Yeni tag'i select'e ekle ve seç.
                        callback({ value: String(data.id), text: trimmed });
                    })
                    .catch(() => {
                        callback();
                    });
            },
            onChange: (values) => {
                this.form.tags = values;
            },
        });
    },

    focusDescriptionField() {
        textEditor.get("description").focus();
    },

    addFeaturedImage() {
        const picker = new MediaPicker({ type: "image" });

        picker.on("select", ({ id, path }) => {
            this.form.featured_image = {
                id: +id,
                path,
            };
        });
    },

    removeFeaturedImage() {
        this.form.featured_image = {};
    },

    focusFirstErrorField(formElements) {
        const errorKeys = Object.keys(this.errors.errors);

        const firstErrorField = [...formElements].find((element) => {
            return errorKeys.includes(element.name);
        });

        if (firstErrorField && firstErrorField.classList.contains("wysiwyg")) {
            textEditor.get(firstErrorField.getAttribute("name")).focus();

            return;
        }

        if (firstErrorField) {
            firstErrorField.focus();
        }
    },

    handleSubmit({ submissionType }) {
        this.formSubmitting = true;
        this.formSubmissionType = submissionType;

        const {
            id,
            title,
            description,
            slug,
            meta,
            featured_image,
            publish_status,
            blog_category_id,
            tags,
        } = this.form;

        // Get FAQ data from DOM
        const faqs = [];
        const faqInputs = document.querySelectorAll('[name^="faqs"]');
        const faqIndexMap = {};
        
        faqInputs.forEach(input => {
            const match = input.name.match(/faqs\[(\d+)\]\[(question|answer)\]/);
            if (match) {
                const index = parseInt(match[1]);
                const field = match[2];
                
                if (!faqIndexMap[index]) {
                    faqIndexMap[index] = {};
                }
                
                faqIndexMap[index][field] = input.value;
            }
        });
        
        // Convert to array and filter out empty items
        Object.values(faqIndexMap).forEach(faq => {
            if (faq.question || faq.answer) {
                faqs.push({
                    question: faq.question || '',
                    answer: faq.answer || ''
                });
            }
        });

        axios
            .put(
                `/blog/posts/${this.form.id}`,
                {
                    id,
                    title,
                    description,
                    slug,
                    meta,
                    publish_status,
                    blog_category_id,
                    tags,
                    faqs,
                    files: {
                        featured_image: featured_image.id
                            ? [featured_image.id]
                            : [],
                    },
                },
                {
                    params: {
                        ...(submissionType === "save_and_exit" && {
                            exit_flash: true,
                        }),
                    },
                }
            )
            .then((response) => {
                if (submissionType === "save_and_exit") {
                    location.href = "/admin/blog/posts";

                    return;
                }

                success(response.data.message);
            })
            .catch(({ response }) => {
                this.errors.reset();
                this.errors.record(response.data.errors);
                this.focusFirstErrorField(this.$refs.form.elements);

                error(response.data.message);
            })
            .finally(() => {
                this.formSubmitting = false;
                this.formSubmissionType = null;
            });
    },
}));

Alpine.start();
