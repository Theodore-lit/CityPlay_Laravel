<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary', // 'primary', 'magenta', 'aggressive'
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
    primary: 'hud-btn hud-btn-primary px-6 h-11 text-[10px] font-black tracking-widest text-white uppercase',
    magenta: 'hud-btn hud-btn-magenta px-6 h-11 text-[10px] font-black tracking-widest text-white uppercase',
    aggressive: 'aggressive-play block w-full h-20 group/play',
};

const computedClasses = cn(variants[props.variant], props.className);

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
        <div class="mirror-sweep" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[20%]" ref="sweep2Ref"></div>
        <div class="relative z-10 flex flex-col items-center justify-center gap-0.5">
            <slot />
        </div>
    </Link>
    <button v-else :class="computedClasses" :disabled="disabled" @mouseenter="handleMouseEnter">
        <div class="mirror-sweep" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[20%]" ref="sweep2Ref"></div>
        <div class="relative z-10 flex flex-col items-center justify-center gap-0.5">
            <slot />
        </div>
    </button>
</template>
