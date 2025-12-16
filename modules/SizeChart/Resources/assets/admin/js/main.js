import tinyMCE from "@admin/js/wysiwyg";

export default function initSizeChartWysiwyg() {
    tinyMCE();
}

function initTypeToggles() {
    const typeSelect = document.querySelector('select[name="type"]');
    const htmlFields = document.querySelector('[data-size-chart-html-fields]');
    const imageFields = document.querySelector('[data-size-chart-image-fields]');

    if (!typeSelect) {
        return;
    }

    const apply = () => {
        const type = (typeSelect.value || '').toLowerCase();

        if (htmlFields) {
            htmlFields.style.display = type === 'html' ? '' : 'none';
        }

        if (imageFields) {
            imageFields.style.display = type === 'image' ? '' : 'none';
        }
    };

    typeSelect.addEventListener('change', apply);
    apply();
}

function initProductOverrideSelect() {
    const el = document.querySelector('.size-chart-product-select');

    if (!el || !window.$) {
        return;
    }

    const $el = $(el);
    const searchUrl = el.getAttribute('data-search-url');

    if (!searchUrl) {
        return;
    }

    const instance = $el[0].selectize;

    // Re-initialize only for this select, so we can add remote loading.
    if (instance) {
        instance.destroy();
    }

    $el.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        create: false,
        persist: false,
        preload: false,
        load: function (query, callback) {
            if (!query || query.length < 2) {
                return callback();
            }

            axios
                .get(searchUrl, {
                    params: {
                        query,
                        limit: 10,
                    },
                })
                .then(({ data }) => callback(data))
                .catch(() => callback());
        },
        render: {
            option: function (item, escape) {
                return `<div>${escape(item.name)} <small class="text-muted">#${escape(item.id)}</small></div>`;
            },
            item: function (item, escape) {
                return `<div>${escape(item.name)} <small class="text-muted">#${escape(item.id)}</small></div>`;
            },
        },
    });
}

initSizeChartWysiwyg();
initProductOverrideSelect();
initTypeToggles();
