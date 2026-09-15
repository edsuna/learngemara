import arrowsSvg from 'arrows-svg';
const arrowCreate = arrowsSvg.default || arrowsSvg.arrowCreate || arrowsSvg;
const DIRECTION = arrowsSvg.DIRECTION;
const HEAD = arrowsSvg.HEAD;

const pieces = {
    consequences: {
        fromDirection: DIRECTION.RIGHT,
        toDirection: DIRECTION.TOP_LEFT,
        toID: '-inner',
    },

    when: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP_LEFT,
        toID: '-top',
    },

    where: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP,
        toID: '',
    },

    toWhat: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP,
        toID: '',
    },

    withWhat: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP_RIGHT,
        toID: '-top',
    },

    how: {
        fromDirection: DIRECTION.LEFT,
        toDirection: DIRECTION.TOP_RIGHT,
        toID: '-inner',
    },

    other: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.LEFT,
        toID: '',
    },

    who: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.RIGHT,
        toID: '',
    },
};

export function initArrows() {
    // Clear first, so a second call replaces the arrows rather than drawing a
    // duplicate set of SVGs over the first.
    resetArrows();

    for (const [key, val] of Object.entries(pieces)) {
        const arrow = arrowCreate({
            className: "arrow",
            from: {
                direction: val.fromDirection,
                node: () => document.getElementById("gc-" + key),
                translation: [0, 0]
            },
            to: {
                direction: val.toDirection,
                node: () => document.getElementById("gc-act" + val.toID),
                translation: [0, 0]
            },
            head: {
                func: HEAD.NORMAL,
                size: 10,
                distance: 0.99,
            }
        });
        document.body.appendChild(arrow.node);
    }
}

/**
 * Remove every arrow SVG from the page.
 *
 * This does NOT stop the arrows' observers, and nothing here can (DIAG-21).
 * arrows-svg renders each arrow as a Preact component, bundled inside the
 * library, whose requestAnimationFrame loop only ends when that component
 * unmounts. The public API never unmounts it: the `clear()` returned by
 * arrowCreate() just calls removeChild on the SVG, and `node` is the
 * DocumentFragment it was rendered into. Removing the SVG therefore leaves its
 * loop running, and every redraw adds eight more. Measured: 288 frame requests
 * per 600 ms with one set of arrows, 1152 after four redraws, while eight SVGs
 * stay visible throughout. The fix is replacing the library (Stage 0f).
 */
export function resetArrows() {
    document.querySelectorAll('.arrow').forEach(e => e.remove());
}

if (typeof window !== 'undefined') {
    window.initArrows = initArrows;
    window.resetArrows = resetArrows;
}
