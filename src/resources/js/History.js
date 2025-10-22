export const History = (() => {

    let stack = [];
    let decayDuration = 5000;
    let onChange = () => {};

    function init({ decay = 5000, onChange: changeCallback = () => {} } = {}) {

        decayDuration = decay;
        onChange = changeCallback;

    }

    function add(undoFunction) {

        if (typeof undoFunction !== 'function') {
            throw new Error("Undo function must be a function.");
        }

        const timeoutId = setTimeout(() => {
            expireUndo(undoFunction);
        }, decayDuration);

        stack.push({ fn: undoFunction, timeoutId });
        triggerChange();

    }

    function back() {

        if (stack.length === 0) {

            console.warn("Nothing to undo.");
            return;

        }

        const { fn, timeoutId } = stack.pop();
        clearTimeout(timeoutId);

        try {

            fn();

        } catch (e) { }

        resetTimers();
        triggerChange();

    }

    function expireUndo(undoFunction) {

        const index = stack.findIndex(entry => entry.fn === undoFunction);
        if (index !== -1) {

            stack.splice(index, 1);
            triggerChange();

        }

    }

    function resetTimers() {

        for (const entry of stack) {

            clearTimeout(entry.timeoutId);
            entry.timeoutId = setTimeout(() => {
                expireUndo(entry.fn);
            }, decayDuration);

        }

    }

    function triggerChange() {

        onChange(stack.length);

    }

    function clear() {

        for (const entry of stack) {
            clearTimeout(entry.timeoutId);
        }

        stack = [];
        triggerChange();

    }

    function getCount() {

        return stack.length;

    }

    return { init, add, back, clear, getCount };

})();
