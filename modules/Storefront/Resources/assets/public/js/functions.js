export function trans(langKey, replace = {}) {
    let line = window.FleetCart.langs[langKey] ?? "";

    const keys = Object.keys(replace).sort((a, b) => b.length - a.length);
    for (const key of keys) {
        const value = replace[key];
        const re = new RegExp(`:${key}(?![A-Za-z_])`, "g");
        line = line.replace(re, value);
    }

    return line;
}

let _currencyFormatter = null;
let _currencyFormatterKey = null;

export function formatCurrency(amount) {
    const locale = FleetCart.locale.replace("_", "-");
    const currency = FleetCart.currency;
    const key = `${locale}|${currency}`;

    if (!_currencyFormatter || _currencyFormatterKey !== key) {
        _currencyFormatterKey = key;
        _currencyFormatter = new Intl.NumberFormat(locale, {
            ...(FleetCart.locale === "ar" && {
                numberingSystem: "arab",
            }),
            style: "currency",
            currency,
            currencyDisplay: "symbol",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    const formatted = _currencyFormatter.format(amount);

    return formatted.replace("TRY", "₺");
}

export function generateUid() {
    const timestamp = Math.floor(Math.random() * Date.now()).toString(36);
    const randomPart = Math.random().toString(36).substring(2, 8);

    return (timestamp + randomPart).substring(0, 12);
}
