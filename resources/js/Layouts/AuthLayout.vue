<script setup>
import { Link } from '@inertiajs/vue3';
import { Compass, MapPin } from 'lucide-vue-next';

defineProps({
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="min-h-screen relative flex items-center justify-center px-4 py-12 overflow-hidden bg-zinc-950">
        <!-- Background Effects -->
        <div class="absolute inset-0 grid-bg opacity-30" />
        <div class="absolute inset-0 bg-gradient-to-b from-primary/5 via-transparent to-primary/5" />
        
        <!-- Animated Scanner Line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/40 to-transparent animate-scan" />

        <div class="relative w-full max-w-md">
            <!-- Decorative Corners -->
            <div class="absolute -top-12 -left-12 h-24 w-24 border-t-2 border-l-2 border-primary/20 rounded-tl-3xl opacity-50" />
            <div class="absolute -bottom-12 -right-12 h-24 w-24 border-b-2 border-r-2 border-primary/20 rounded-br-3xl opacity-50" />

            <!-- Logo -->
            <Link :href="route('welcome')" class="flex flex-col items-center gap-3 mb-10 group">
                <div class="h-12 w-12 rounded-xl bg-primary border-2 border-white/20 grid place-items-center shadow-[0_0_30px_rgba(6,182,212,0.4)] group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                    <MapPin class="h-6 w-6 text-black" />
                </div>
                <div class="flex flex-col items-center">
                    <span class="font-display font-black tracking-tighter text-2xl italic text-white uppercase leading-none">CITY<span class="text-primary">PLAY</span></span>
                    <span class="text-[8px] font-black text-primary/60 tracking-[0.6em] uppercase mt-1.5 ml-1">IDENTITY_RECOGNITION</span>
                </div>
            </Link>

            <!-- Card -->
            <div class="neon-border-box rounded-[2.5rem] p-8 md:p-10 shadow-[0_0_50px_rgba(0,0,0,0.5)] animate-fade-up relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
                
                <div class="text-center mb-10">
                    <h1 class="font-display text-2xl font-black text-white uppercase italic tracking-tight">{{ title }}</h1>
                    <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mt-2">{{ subtitle }}</p>
                </div>
                
                <div class="relative z-10">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="mt-10 text-center text-[10px] font-black text-white/40 uppercase tracking-widest border-t border-white/5 pt-8">
                    <slot name="footer" />
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="mt-8 flex justify-between items-center px-4">
                <div class="text-[7px] font-black text-white/20 uppercase tracking-[0.4em]">ENCRYPTION_AES_256</div>
                <div class="text-[7px] font-black text-white/20 uppercase tracking-[0.4em]">SECURE_LINK_ACTIVE</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes scan {
  0% { transform: translateY(-100vh); }
  100% { transform: translateY(100vh); }
}
.animate-scan {
  animation: scan 8s linear infinite;
}
</style>
