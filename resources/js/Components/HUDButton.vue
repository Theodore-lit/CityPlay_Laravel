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

const buttonRef = ref(null);
const sweepRef = ref(null);
const sweep2Ref = ref(null);

const variants = {
    primary: 'hud-btn hud-btn-primary px-6 h-11 text-[10px] font-black tracking-widest text-white uppercase',
    magenta: 'hud-btn hud-btn-magenta px-6 h-11 text-[10px] font-black tracking-widest text-white uppercase',
    aggressive: 'aggressive-play block w-full h-20 group/play',
};

const computedClasses = cn(variants[props.variant], props.className);

onMounted(() => {
    if (sweepRef.value && sweep2Ref.value) {
        const tl = gsap.timeline({ repeat: -1, repeatDelay: 3 });
        
        tl.to(sweepRef.value, {
            left: '150%',
            duration: 1.5,
            ease: "power2.inOut",
        })
        .to(sweep2Ref.value, {
            left: '150%',
            duration: 1.2,
            ease: "power2.inOut",
        }, "-=1.3");

        // Animation au survol
        buttonRef.value.addEventListener('mouseenter', () => {
            gsap.to([sweepRef.value, sweep2Ref.value], {
                left: '150%',
                duration: 0.6,
                stagger: 0.1,
                ease: "power4.out",
                overwrite: true,
                onComplete: () => {
                    gsap.set([sweepRef.value, sweep2Ref.value], { left: '-150%' });
                    tl.restart();
                }
            });
        });
    }
});
</script>

<template>
    <Link v-if="href" :href="href" :class="computedClasses" ref="buttonRef">
        <div class="mirror-sweep" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[20%]" ref="sweep2Ref"></div>
        <div class="relative z-10 flex flex-col items-center justify-center gap-0.5">
            <slot />
        </div>
    </Link>
    <button v-else :class="computedClasses" :disabled="disabled" ref="buttonRef">
        <div class="mirror-sweep" ref="sweepRef"></div>
        <div class="mirror-sweep opacity-30 w-[20%]" ref="sweep2Ref"></div>
        <div class="relative z-10 flex flex-col items-center justify-center gap-0.5">
            <slot />
        </div>
    </button>
</template>
