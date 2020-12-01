import arrowCreate, {DIRECTION, HEAD} from 'arrows-svg';

const pieces = {
    consequences: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP_LEFT,
        toID: '-inner',
    },

    when: {
        fromDirection: DIRECTION.BOTTOM,
        toDirection: DIRECTION.TOP_LEFT,
        toID: '-inner',
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
        toID: '-inner',
    },

    how: {
        fromDirection: DIRECTION.LEFT,
        toDirection: DIRECTION.TOP_RIGHT,
        toID: '-inner',
    },

    other: {
        fromDirection: DIRECTION.RIGHT,
        toDirection: DIRECTION.LEFT,
        toID: '',
    },

    who: {
        fromDirection: DIRECTION.LEFT,
        toDirection: DIRECTION.RIGHT,
        toID: '',
    },
};

export function initArrows() {
    for (const [key, val] of Object.entries(pieces)) {
        var arrow = arrowCreate({
            classfromID: "arrow",
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
                size: 10, // custom options that will be passed to head function
                distance: 0.99,
            }
        });
        document.body.appendChild(arrow.node);
    }
}

export function resetArrows() {
    document.querySelectorAll('.arrow').forEach(e => e.remove());
}
