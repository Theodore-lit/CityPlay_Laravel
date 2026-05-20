<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { computed, ref, onMounted } from 'vue';
import gsap from 'gsap';

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

const sweepRef = ref(null);
const sweep2Ref = ref(null);

const variants = {
    primary: 'bg-gradient-premium text-white shadow-neon hover:shadow-neon-hover',
    outline: 'border-2 border-primary/40 text-primary hover:bg-primary/5',
    ghost: 'text-foreground/80 hover:text-primary hover:bg-primary/5',
    purple: 'bg-gradient-accent text-white shadow-purple hover:shadow-purple-hover',
};

const sizes = {
    sm: 'h-9 px-4 text-xs',
    md: 'h-11 px-6 text-sm',
    lg: 'h-14 px-8 text-base',
    xl: 'h-20 px-10 text-xl',
};

const base = 'relative inline-flex items-center justify-center gap-2 rounded-xl font-display font-black uppercase tracking-widest transition-all duration-300 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none overflow-hidden group';

const computedClasses = computed(() => cn(base, sizes[props.size], variants[props.variant], props.className));

let tl;

const handleMouseEnter = () => {
    if (sweepRef.value && sweep2Ref.value) {
        gsap.to([sweepRef.value, sweep2Ref.value], {
            left: '150%',
            duration: 0.5,
            stagger: 0.08,
            ease: "power4.out",
            overwrite: true,
            onComplete: () => {
                gsap.set([sweepRef.value, sweep2Ref.value], { left: '-150%' });
                if (tl) tl.restart();
            }
        });
    }
};

onMounted(() => {
    if (sweepRef.value && sweep2Ref.value) {
        tl = gsap.timeline({ repeat: -1, repeatDelay: 2.5 });
        
        tl.to(sweepRef.value, {
            left: '150%',
            duration: 1.2,
            ease: "power3.inOut",
        })
        .to(sweep2Ref.value, {
            left: '150%',
            duration: 1,
            ease: "power3.inOut",
        }, "-=1.0");
    }
});
</script>

<template>
    <Link v-if="href" :href="href" :class="computedClasses" @mouseenter="handleMouseEnter">
        <div class="mirror-sweep pointer-events-none" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[30%] pointer-events-none" ref="sweep2Ref"></div>
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </Link>
    <button v-else :class="computedClasses" :disabled="disabled" @mouseenter="handleMouseEnter">
        <div class="mirror-sweep pointer-events-none" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[30%] pointer-events-none" ref="sweep2Ref"></div>
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </button>
</template>
