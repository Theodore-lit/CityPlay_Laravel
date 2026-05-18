<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl bg-gaming-darker border border-electric/30 px-4 h-12 text-sm text-white focus:border-electric focus:ring-electric placeholder:text-muted-foreground/40 transition-all duration-300"
        v-model="model"
        ref="input"
    />
</template>
