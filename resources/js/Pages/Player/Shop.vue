<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingBag, Heart, Gem, Zap, ChevronLeft, Sparkles, Package, Star
} from 'lucide-vue-next';
import NeonButton from '@/Components/NeonButton.vue';
import { cn } from '@/lib/utils';

/**
 * Configuration des articles de la boutique.
 * Permet l'achat de ressources vitales (Cœurs, Diamants) via les points XP accumulés.
 */
const items = [
    { name: "Pack de Cœurs", icon: Heart, price: 500, currency: "XP", value: "1 Vie", color: "text-destructive" },
    { name: "Diamants", icon: Gem, price: 1000, currency: "XP", value: "10 Diamants", color: "text-sky-400" },
    { name: "Pack Exploration", icon: Package, price: 2000, currency: "XP", value: "Passe Illimité 24h", color: "text-amber-400" },
];
</script>

<template>
    <Head title="Boutique — CityPlay" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="flex items-center gap-6 mb-12">
                <Link :href="route('player.hub')" class="h-12 w-12 rounded-2xl bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] grid place-items-center text-primary hover:scale-110 transition-all">
                    <ChevronLeft class="h-6 w-6" />
                </Link>
                <div>
                    <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Resources Market</div>
                    <h1 class="font-display text-4xl text-foreground uppercase italic font-black">District <span class="text-white/40">Shop</span></h1>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div v-for="item in items" :key="item.name"
                     class="group relative overflow-hidden rounded-[2.5rem] bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-8 transition-all duration-500 hover:border-primary/40 text-center">

                    <div :class="cn('h-20 w-20 mx-auto rounded-[2rem] bg-white/5 border border-white/10 grid place-items-center mb-6 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm', item.color)">
                        <component :is="item.icon" class="h-10 w-10 animate-pulse-soft" />
                    </div>

                    <h2 class="font-display text-2xl text-foreground uppercase italic font-black tracking-tight mb-2">
                        {{ item.name }}
                    </h2>
                    <p class="text-xs text-muted-foreground uppercase font-black tracking-widest mb-6">{{ item.value }}</p>

                    <div class="mt-auto">
                        <NeonButton variant="primary" class="w-full shadow-neon h-12 text-[10px] tracking-[0.2em]">
                            ACHETER ({{ item.price }} {{ item.currency }})
                        </NeonButton>
                    </div>

                    <!-- Ambient Glow -->
                    <div class="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-primary/5 blur-3xl group-hover:bg-primary/10 transition-colors" />
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
