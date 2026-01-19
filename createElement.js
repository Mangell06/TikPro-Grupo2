export function createElement(tag, parent = "", className = "", attr = {}) {
    const $element = $(tag);
    if (className) $element.addClass(className);
    if (attr && typeof attr === "object") {
        for (const key in attr) $element.attr(key, attr[key]);
    }
    if (parent) $(parent).append($element);
    return $element;
}