<script setup>
import { Link } from '@inertiajs/vue3';
import { Compass } from 'lucide-vue-next';

defineProps({
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="min-h-screen relative flex items-center justify-center px-4 py-12 overflow-hidden bg-gaming-darker">
        <!-- Background Effects -->
        <div class="absolute inset-0 grid-bg opacity-40 z-0" />
        <div class="absolute -top-32 left-1/4 h-96 w-96 rounded-full bg-electric/20 blur-3xl z-0" />
        <div class="absolute -bottom-32 right-1/4 h-96 w-96 rounded-full bg-purple-neon/20 blur-3xl z-0" />

        <div class="relative w-full max-w-md z-10">
            <!-- Logo -->
            <Link :href="route('welcome')" class="flex items-center justify-center gap-2 mb-8 group">
                <div class="h-10 w-10 rounded-lg bg-gradient-electric grid place-items-center shadow-neon group-hover:scale-110 transition-transform">
                    <Compass class="h-5 w-5 text-electric-foreground" />
                </div>
                <span class="font-display font-black tracking-widest text-white">
                    CITY<span class="text-electric neon-text">PLAY</span>
                </span>
            </Link>

            <!-- Card -->
            <div class="glass-strong rounded-3xl p-8 shadow-elevated animate-fade-up border border-electric/20 relative">
                <h1 class="font-display text-2xl text-center text-white">{{ title }}</h1>
                <p class="text-sm text-muted-foreground text-center mt-2">{{ subtitle }}</p>
                
                <div class="mt-8">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="mt-6 text-center text-sm text-muted-foreground border-t border-electric/10 pt-6">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>
