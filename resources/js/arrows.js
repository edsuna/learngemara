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

/**
 * Arrows currently on the page, each as the { node, clear } pair arrowCreate
 * returns. `clear` cancels that arrow's requestAnimationFrame observer -- the
 * loop that keeps it attached to its ovals. Dropping the handle and removing
 * only the SVG leaves the loop running forever, once per arrow per draw.
 */
const liveArrows = [];

export function initArrows() {
    // Idempotent: drawing without clearing first would stack a second set of
    // arrows, and a second set of observers, on top of the existing ones.
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
        liveArrows.push(arrow);
    }
}

export function resetArrows() {
    while (liveArrows.length) {
        const arrow = liveArrows.pop();

        try {
            arrow.clear?.();
        } catch {
            // Already cancelled, or the anchors are gone. Releasing the
            // observer is best-effort; the DOM sweep below is not.
        }
    }

    // Removal goes through the DOM, not arrow.node: the handle arrowCreate
    // returns is accepted by appendChild but has no .remove(), so calling it
    // throws and would abandon the rest of the loop mid-way.
    document.querySelectorAll('.arrow').forEach(e => e.remove());
}

/** Live arrow count, so the observer bookkeeping is observable from a test. */
export function arrowCount() {
    return liveArrows.length;
}

if (typeof window !== 'undefined') {
    window.initArrows = initArrows;
    window.resetArrows = resetArrows;
    window.arrowCount = arrowCount;
}
