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
    <div class="space-y-3">
        <label v-if="label" class="block text-[9px] uppercase tracking-[0.4em] text-white/40 font-black ml-2">
            {{ label }}
        </label>
        <div class="relative group">
            <!-- Mechanical Corners -->
            <div class="absolute top-0 left-0 w-2 h-2 border-t border-l border-white/20 group-focus-within:border-primary transition-colors" />
            <div class="absolute top-0 right-0 w-2 h-2 border-t border-r border-white/20 group-focus-within:border-primary transition-colors" />
            <div class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-white/20 group-focus-within:border-primary transition-colors" />
            <div class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-white/20 group-focus-within:border-primary transition-colors" />

            <input
                :type="type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                ref="input"
                v-bind="$attrs"
                :placeholder="placeholder"
                :required="required"
                class="w-full h-12 bg-white/[0.02] border-2 border-white/5 px-6 text-sm text-white font-black tracking-widest placeholder:text-white/10 outline-none focus:border-primary/40 focus:bg-primary/5 transition-all duration-500 uppercase"
                :class="{ 'border-red-500/40 focus:border-red-500/60 focus:bg-red-500/5': error }"
            />
            
            <!-- Focus Glow Line -->
            <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-focus-within:w-full transition-all duration-700 shadow-[0_0_10px_#06b6d4]" />
        </div>
        <p v-if="error" class="text-[9px] text-red-400 font-black uppercase tracking-widest mt-1 ml-2 animate-fade-up">
            SYSTEM_ERROR: {{ error }}
        </p>
    </div>
</template>
