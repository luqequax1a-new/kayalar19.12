<template>
    <div class="box">
        <div class="box-header">
            <h5>{{ trans("product::products.group.general") }}</h5>
        </div>

        <div class="box-body">
            <div class="form-group row">
                <label for="name" class="col-sm-12 control-label text-left">
                    {{ trans("product::attributes.name") }}
                    <span class="text-red">*</span>
                </label>

                <div class="col-sm-12">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        v-model="form.name"
                        @change="
                            if (
                                window.location.pathname.endsWith(
                                    'products/create'
                                )
                            ) {
                                setProductSlug($event.target.value);
                            }
                        "
                    />

                    <span
                        class="help-block text-red"
                        v-if="errors.has('name')"
                        v-text="errors.get('name')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="description"
                    class="col-sm-12 control-label text-left"
                    @click="focusEditor"
                >
                    {{ trans("product::attributes.description") }}
                    <span class="text-red">*</span>
                </label>

                <div class="col-sm-12">
                    <textarea
                        name="description"
                        id="description"
                        class="form-control wysiwyg"
                        v-model="form.description"
                    >
                    </textarea>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('description')"
                        v-text="errors.get('description')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="brand-id" class="col-sm-12 control-label text-left">
                    {{ trans("product::attributes.brand_id") }}
                </label>

                <div class="col-sm-6">
                    <select
                        name="brand_id"
                        id="brand-id"
                        class="form-control custom-select-black"
                        v-model="form.brand_id"
                    >
                        <option value="">
                            {{ trans("admin::admin.form.please_select") }}
                        </option>

                        <option
                            v-for="(brand, index) in brands"
                            :key="index"
                            :value="brand.value"
                        >
                            {{ brand.name }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('brand_id')"
                        v-text="errors.get('brand_id')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="primary-category-id" class="col-sm-12 control-label text-left">
                    Default Category
                </label>

                <div class="col-sm-6">
                    <select
                        name="primary_category_id"
                        id="primary-category-id"
                        v-model="form.primary_category_id"
                        ref="primaryCategoryField"
                    >
                        <option value="">
                            {{ trans("admin::admin.form.please_select") }}
                        </option>

                        <option
                            v-for="(category, index) in categories"
                            :key="index"
                            :value="category.value"
                        >
                            {{ category.name }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('primary_category_id')"
                        v-text="errors.get('primary_category_id')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="google-taxonomy" class="col-sm-12 control-label text-left">
                    Google Ürün Kategorisi
                </label>

                <div class="col-sm-6">
                    <select
                        name="google_product_category_path"
                        id="google-taxonomy"
                        v-model="form.google_product_category_path"
                        ref="googleTaxonomyField"
                    >
                        <option :value="form.google_product_category_path" v-if="form.google_product_category_path">
                            {{ form.google_product_category_path }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('google_product_category_path')"
                        v-text="errors.get('google_product_category_path')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="categories"
                    class="col-sm-12 control-label text-left"
                >
                    {{ trans("product::attributes.categories") }}
                </label>

                <div class="col-sm-6">
                    <select
                        name="categories"
                        id="categories"
                        multiple
                        v-model="form.categories"
                        ref="categoriesField"
                    >
                        <option
                            v-for="(category, index) in categories"
                            :key="index"
                            :value="category.value"
                        >
                            {{ category.name }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('categories')"
                        v-text="errors.get('categories')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="tags" class="col-sm-12 control-label text-left">
                    {{ trans("product::attributes.tags") }}
                </label>

                <div class="col-sm-6">
                    <select
                        name="tags"
                        id="tags"
                        multiple
                        v-model="form.tags"
                        ref="tagsField"
                    >
                        <option
                            v-for="(tag, index) in tags"
                            :key="index"
                            :value="tag.value"
                        >
                            {{ tag.name }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('tags')"
                        v-text="errors.get('tags')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="tax-class-id"
                    class="col-sm-12 control-label text-left"
                >
                    {{ trans("product::attributes.tax_class_id") }}
                </label>

                <div class="col-sm-6">
                    <select
                        name="tax_class_id"
                        id="tax-class-id"
                        class="form-control custom-select-black"
                        v-model="form.tax_class_id"
                    >
                        <option value="">
                            {{ trans("admin::admin.form.please_select") }}
                        </option>

                        <option
                            v-for="(taxClass, index, key) in taxClasses"
                            :key="key"
                            :value="index"
                        >
                            {{ taxClass }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('tax_class_id')"
                        v-text="errors.get('tax_class_id')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="sale-unit-id" class="col-sm-12 control-label text-left">
                    {{ trans("product::attributes.sale_unit_id") }}
                </label>

                <div class="col-sm-6">
                    <select
                        name="sale_unit_id"
                        id="sale-unit-id"
                        class="form-control custom-select-black"
                        v-model="form.sale_unit_id"
                    >
                        <option value="">
                            {{ trans("admin::admin.form.please_select") }}
                        </option>

                        <option
                            v-for="(unit, index) in units"
                            :key="index"
                            :value="unit.value"
                        >
                            {{ unit.name }}
                        </option>
                    </select>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('sale_unit_id')"
                        v-text="errors.get('sale_unit_id')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="is_virtual"
                    class="col-sm-12 control-label text-left"
                >
                    {{ trans("product::attributes.is_virtual") }}
                </label>

                <div class="col-sm-6">
                    <div class="switch">
                        <input
                            type="checkbox"
                            name="is_virtual"
                            id="is-virtual"
                            v-model="form.is_virtual"
                        />

                        <label
                            for="is-virtual"
                            v-html="
                                trans(
                                    'product::products.form.the_product_won\'t_be_shipped'
                                )
                            "
                        >
                        </label>
                    </div>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('is_virtual')"
                        v-text="errors.get('is_virtual')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="is-active"
                    class="col-sm-12 control-label text-left"
                >
                    {{ trans("product::attributes.is_active") }}
                </label>

                <div class="col-sm-9">
                    <div class="switch">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is-active"
                            v-model="form.is_active"
                        />

                        <label for="is-active">
                            {{
                                trans(
                                    "product::products.form.enable_the_product"
                                )
                            }}
                        </label>

                        <span
                            class="help-block text-red"
                            v-if="errors.has('is_active')"
                            v-text="errors.get('is_active')"
                        ></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";
import tinyMCE from "@admin/js/wysiwyg";
import { toaster } from "@admin/js/Toaster";

const textEditor = ref(null);
const brands = ref(FleetCart.data["brands"] ?? {});
const categories = ref(FleetCart.data["categories"] ?? {});
const categoriesField = ref(null);
const primaryCategoryField = ref(null);
const taxClasses = FleetCart.data["tax-classes"] ?? {};
const units = ref(FleetCart.data["units"] ?? {});
const tags = ref(FleetCart.data["tags"] ?? {});
const tagsField = ref(null);
const googleTaxonomyField = ref(null);

const { form, shouldResetForm, errors, focusField } = useForm();
const { setProductSlug } = useProductMethods();

function focusEditor() {
    textEditor.value.get("description").focus();
}

function initTextEditor() {
    textEditor.value = tinyMCE({
        selector: "#description",
        setup: (editor) => {
            editor.on("change", () => {
                editor.save();
                editor.getElement().dispatchEvent(new Event("input"));

                const key =
                    editor.getElement().getAttribute("name") ||
                    editor.getElement().getAttribute("id");

                if (key) {
                    errors.clear(key);
                }
            });
        },
    });
}

function initCategoriesSelectize() {
    $(categoriesField.value).selectize({
        plugins: ["remove_button"],
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        hideSelected: false,
        allowEmptyOption: true,
        onChange: (values) => {
            form.categories = values;
        },
        onItemAdd(value) {
            this.getItem(value)[0].innerHTML = this.getItem(
                value
            )[0].innerHTML.replace(/¦––\s/g, "");
        },
        onItemRemove(value) {
            const element = [...this.$dropdown_content.children()].find(
                (el) => el.getAttribute("data-value") === value
            );

            if (element) {
                element.classList.remove("selected");
            }
        },
        onInitialize() {
            $("#categories")
                .next()
                .find("[data-value]")
                .each((_, el) => {
                    $(el).html(
                        $(el).text().slice(0, -1).replace(/¦––\s/g, "") +
                            '<a href="javascript:void(0)" class="remove" tabindex="-1">×</a>'
                    );
                });
        },
    });
}

function initPrimaryCategorySelectize() {
    $(primaryCategoryField.value).selectize({
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        allowEmptyOption: true,
        onChange: (value) => {
            form.primary_category_id = value || null;
        },
        onInitialize() {
            $("#primary-category-id")
                .next()
                .find("[data-value]")
                .each((_, el) => {
                    $(el).html($(el).text().replace(/¦––\s/g, ""));
                });
        },
    });
}

function initGoogleTaxonomySelectize() {
    $(googleTaxonomyField.value).selectize({
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        allowEmptyOption: true,
        maxItems: 1,
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        preload: 'focus',
        openOnFocus: true,
        loadThrottle: 250,
        load: function(query, callback) {
            const q = (query || '').trim();
            $.ajax({
                url: '/admin/google-taxonomy',
                data: q ? { q } : {},
                success: function(resp) {
                    callback(resp && resp.results ? resp.results : []);
                },
                error: function() { callback([]); }
            });
        },
        onChange: (value) => {
            form.google_product_category_path = value || null;
        },
        onItemAdd(value) {
            const item = this.getItem(value)[0];
            if (item) {
                item.setAttribute('title', value);
            }
        },
        onInitialize() {
            const val = form.google_product_category_path;
            if (val) {
                const selectize = $(googleTaxonomyField.value)[0].selectize;
                selectize.addOption({ id: val, text: val });
                selectize.setValue(val);
            }
        },
    });
}

function initTagsSelectize() {
    $(tagsField.value).selectize({
        plugins: ["remove_button"],
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        hideSelected: true,
        allowEmptyOption: true,
        create: (input, callback) => {
            const name = (input || "").trim();

            if (!name) {
                return callback();
            }

            errors.clear("tags");

            window.axios
                .post("/tags", {
                    name,
                })
                .then((response) => {
                    const tag = response?.data?.tag;

                    if (!tag?.id) {
                        return callback();
                    }

                    tags.value = [...tags.value, { name: tag.name, value: tag.id }];

                    callback({
                        value: tag.id,
                        text: tag.name,
                    });
                })
                .catch((error) => {
                    const message =
                        error?.response?.data?.message ||
                        error?.message ||
                        "Unable to create tag.";

                    try {
                        const validationErrors = error?.response?.data?.errors;
                        if (validationErrors) {
                            errors.record(validationErrors);
                        }
                    } catch (_) {}

                    toaster.error(message);

                    callback();
                });
        },
        onChange: (values) => {
            form.tags = values;
        },
    });
}

function resetFields() {
    try {
        textEditor.value.get("description").setContent("");
        textEditor.value.get("description").execCommand("mceCancel");
    } catch (_) {}

    try {
        textEditor.value.get("short-description").setContent("");
        textEditor.value.get("short-description").execCommand("mceCancel");
    } catch (_) {}

    $(categoriesField.value)[0].selectize.clear();
    $(tagsField.value)[0].selectize.clear();

    [
        ...$(categoriesField.value)[0].selectize.$dropdown_content.children(),
    ].forEach((el) => {
        if (el.classList.contains("selected")) {
            el.classList.remove("selected");
        }
    });
}

watch(shouldResetForm, () => {
    resetFields();

    focusField({
        selector: "#name",
    });
});

onMounted(() => {
    initTextEditor();
    initCategoriesSelectize();
    initPrimaryCategorySelectize();
    initTagsSelectize();
    initGoogleTaxonomySelectize();

    if (!form.primary_category_id && Array.isArray(form.categories) && form.categories.length > 0) {
        form.primary_category_id = form.categories[0];
    }
});

watch(() => form.categories, (vals) => {
    if (!vals || vals.length === 0) {
        form.primary_category_id = null;
        return;
    }
    if (!vals.includes(form.primary_category_id)) {
        form.primary_category_id = vals[0];
    }
}, { deep: true });

watch(() => form.primary_category_id, (val) => {
    if (!val) return;
    if (!Array.isArray(form.categories)) {
        form.categories = [];
    }
    if (!form.categories.includes(val)) {
        form.categories = [...form.categories, val];
        const selectize = $(categoriesField.value)[0]?.selectize;
        if (selectize) {
            selectize.addItem(val);
        }
    }
});
</script>
