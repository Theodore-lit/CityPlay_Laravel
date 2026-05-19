import { readonly, ref } from 'vue';

const SHOW_DELAY_MS = 200;

let pendingCount = 0;
let showTimer = null;

const isDisplayed = ref(false);

export function startLoading() {
    pendingCount += 1;

    if (pendingCount === 1 && !isDisplayed.value) {
        clearTimeout(showTimer);
        showTimer = setTimeout(() => {
            if (pendingCount > 0) {
                isDisplayed.value = true;
            }
        }, SHOW_DELAY_MS);
    }
}

export function stopLoading() {
    pendingCount = Math.max(0, pendingCount - 1);

    if (pendingCount === 0) {
        clearTimeout(showTimer);
        isDisplayed.value = false;
    }
}

export function useLoaderStore() {
    return {
        isDisplayed: readonly(isDisplayed),
    };
}

export { isDisplayed };
