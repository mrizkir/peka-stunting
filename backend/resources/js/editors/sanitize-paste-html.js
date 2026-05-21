const ALLOWED = new Set([
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'p', 'ul', 'ol', 'li', 'br', 'strong', 'b', 'em', 'i',
]);

const REMOVE = new Set([
    'script', 'style', 'img', 'a', 'iframe', 'object', 'embed', 'svg',
    'video', 'audio', 'form', 'input', 'button', 'link', 'meta',
    'table', 'thead', 'tbody', 'tr', 'td', 'th', 'font',
]);

const TEXT_ALIGN = new Set(['left', 'right', 'center', 'justify']);

const UNWRAP = new Set([
    'div', 'span', 'section', 'article', 'header', 'footer', 'main',
    'figure', 'figcaption', 'aside', 'nav', 'table', 'tbody', 'thead', 'tr', 'td', 'th',
]);

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function extractTextAlign(style) {
    const match = style?.match(/text-align\s*:\s*(left|right|center|justify)/i);

    return match ? match[1].toLowerCase() : null;
}

function filterAttributes(element) {
    let textAlign = extractTextAlign(element.getAttribute('style'));

    const alignAttr = element.getAttribute('align')?.toLowerCase().trim();
    if (alignAttr && TEXT_ALIGN.has(alignAttr)) {
        textAlign ??= alignAttr;
    }

    while (element.attributes.length > 0) {
        element.removeAttribute(element.attributes[0].name);
    }

    if (textAlign) {
        element.setAttribute('style', `text-align: ${textAlign}`);
    }
}

function unwrapElement(element) {
    const parent = element.parentNode;
    if (! parent) {
        return;
    }

    while (element.firstChild) {
        parent.insertBefore(element.firstChild, element);
    }

    parent.removeChild(element);
}

function normalizeNode(node) {
    if (! node.hasChildNodes()) {
        return;
    }

    const children = Array.from(node.childNodes);

    for (const child of children) {
        if (child.nodeType === Node.COMMENT_NODE) {
            child.remove();
            continue;
        }

        if (child.nodeType !== Node.ELEMENT_NODE) {
            continue;
        }

        const tag = child.tagName.toLowerCase();

        if (REMOVE.has(tag)) {
            child.remove();
            continue;
        }

        if (UNWRAP.has(tag) || ! ALLOWED.has(tag)) {
            unwrapElement(child);
            normalizeNode(node);
            continue;
        }

        filterAttributes(child);

        normalizeNode(child);
    }
}

/**
 * Bersihkan HTML hasil salin Word / halaman web ke tag typography yang diizinkan.
 */
export function sanitizePastedHtml(html) {
    if (! html?.trim()) {
        return '';
    }

    const cleaned = html
        .replace(/<!--[\s\S]*?-->/g, '')
        .replace(/<(\/?)(o|w|v|st1):[^>]*>/gi, '');

    const doc = new DOMParser().parseFromString(
        `<div>${cleaned}</div>`,
        'text/html',
    );

    const root = doc.body.firstElementChild ?? doc.body;
    normalizeNode(root);

    const result = root.innerHTML.trim();

    return result || '';
}

/**
 * Teks polos (tanpa HTML) → paragraf & list sederhana.
 */
export function plainTextToHtml(text) {
    if (! text?.trim()) {
        return '';
    }

    const blocks = text.trim().split(/\n{2,}/);
    const parts = [];

    for (const block of blocks) {
        const lines = block.split('\n').map((line) => line.trim()).filter(Boolean);
        if (lines.length === 0) {
            continue;
        }

        const bulletLines = lines.filter((line) => /^([•·▪‣\-*]|\u2022)\s+/.test(line));
        const orderedLines = lines.filter((line) => /^\d+[\.\)]\s+/.test(line));

        if (bulletLines.length === lines.length) {
            parts.push(
                '<ul>'
                + lines
                    .map((line) => `<li>${escapeHtml(line.replace(/^([•·▪‣\-*]|\u2022)\s+/, ''))}</li>`)
                    .join('')
                + '</ul>',
            );
            continue;
        }

        if (orderedLines.length === lines.length) {
            parts.push(
                '<ol>'
                + lines
                    .map((line) => `<li>${escapeHtml(line.replace(/^\d+[\.\)]\s+/, ''))}</li>`)
                    .join('')
                + '</ol>',
            );
            continue;
        }

        parts.push(`<p>${escapeHtml(lines.join('\n')).replace(/\n/g, '<br>')}</p>`);
    }

    return parts.join('');
}
