<script setup>
import { computed } from 'vue';
import { storageUrl } from '@/lib/storageUrl';

const props = defineProps({
    src: {
        type: [String, null],
        default: null,
    },
    alt: {
        type: String,
        default: '',
    },
    fallback: {
        type: String,
        default: null,
    },
});

const resolvedSrc = computed(() => storageUrl(props.src) || props.fallback || null);
</script>

<template>
    <img
        v-if="resolvedSrc"
        :src="resolvedSrc"
        :alt="alt"
        v-bind="$attrs"
    />
    <slot v-else name="placeholder" />
</template>
