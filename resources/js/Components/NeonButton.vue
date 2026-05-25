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
    primary: 'bg-gradient-premium text-white shadow-neon hover:shadow-neon-hover hover-game',
    outline: 'border-2 border-primary/40 text-primary hover:bg-primary/5 hover-game',
    ghost: 'text-foreground/80 hover:text-primary hover:bg-primary/5',
    purple: 'bg-gradient-accent text-white shadow-purple hover:shadow-purple-hover hover-game',
};

const sizes = {
    sm: 'h-9 px-4 text-xs',
    md: 'h-11 px-6 text-sm',
    lg: 'h-14 px-8 text-base',
};

const base = 'relative inline-flex items-center justify-center gap-2 rounded-xl font-display font-bold uppercase tracking-wide transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none overflow-hidden';

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
