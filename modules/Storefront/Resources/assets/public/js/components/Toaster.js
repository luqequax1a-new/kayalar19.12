import Toastify from "toastify-js";

export function notify(message, options = {}) {
    const type = options?.type;

    const baseStyle = {
        background: "#343a40",
    };

    if (type === "success") {
        baseStyle.background = "#198754";
    }

    if (type === "error") {
        baseStyle.background = "#dc3545";
    }

    Toastify({
        text: message || trans("storefront::storefront.something_went_wrong"),
        duration: 3000,
        close: true,
        gravity: window.innerWidth > 991 ? "bottom" : "top",
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // prevents dismissing of toast on hover
        style: {
            ...baseStyle,
            ...(options.style || {}),
        },
        ...options,
    }).showToast();
}

notify.success = (message, options = {}) => notify(message, { ...options, type: "success" });
notify.error = (message, options = {}) => notify(message, { ...options, type: "error" });