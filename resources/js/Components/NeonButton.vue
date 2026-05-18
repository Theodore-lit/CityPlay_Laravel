<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
    size: {
        type: String,
        default: 'md',
    },
    href: {
        type: String,
        default: null,
    },
    className: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    }
});

const variants = {
    primary: 'bg-gradient-electric text-white shadow-neon hover:shadow-[0_0_40px_oklch(0.82_0.15_232/0.7)] border border-white/20',
    outline: 'border-2 border-electric/60 text-electric hover:bg-electric/10 hover:shadow-neon backdrop-blur-sm',
    ghost: 'text-foreground/80 hover:text-electric hover:bg-electric/10',
    purple: 'bg-gradient-purple text-white shadow-purple hover:shadow-[0_0_36px_oklch(0.72_0.18_300/0.7)] border border-white/20',
};

const sizes = {
    sm: 'h-9 px-4 text-sm',
    md: 'h-11 px-6 text-sm',
    lg: 'h-14 px-8 text-base',
};

const base = 'relative inline-flex items-center justify-center gap-2 rounded-xl font-display font-bold uppercase tracking-wider transition-all duration-300 active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none overflow-hidden';

const computedClasses = computed(() => cn(base, sizes[props.size], variants[props.variant], props.className));
</script>

<template>
    <Link v-if="href" :href="href" :class="computedClasses">
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </Link>
    <button v-else :class="computedClasses" :disabled="disabled">
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </button>
</template>
