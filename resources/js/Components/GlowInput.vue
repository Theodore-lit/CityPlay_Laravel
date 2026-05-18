<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    label: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    required: Boolean,
    error: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-xs uppercase tracking-widest text-muted-foreground font-bold ml-1">
            {{ label }}
        </label>
        <div class="relative group">
            <input
                :type="type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                ref="input"
                v-bind="$attrs"
                :placeholder="placeholder"
                :required="required"
                class="w-full h-12 rounded-xl bg-gaming-darker border border-electric/30 px-4 text-sm text-white placeholder:text-muted-foreground/40 outline-none focus:border-electric focus:shadow-neon transition-all duration-300"
                :class="{ 'border-destructive focus:border-destructive focus:shadow-[0_0_15px_oklch(0.62_0.24_25/0.5)]': error }"
            />
        </div>
        <p v-if="error" class="text-xs text-destructive mt-1 ml-1 animate-fade-up">
            {{ error }}
        </p>
    </div>
</template>
