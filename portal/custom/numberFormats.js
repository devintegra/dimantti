
function currencyMX(inputValue) {
    inputValue = inputValue.replace(/,/g, '');
    var parts = inputValue.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}
