...
<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Settings, Trophy, ShoppingBag, Map, User, Bell, ChevronRight, Sparkles, Heart, Gem, Zap
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const shortcuts = [
    { label: "Compétitions", icon: Trophy, desc: "Défiez les autres explorateurs", route: "player.competitions", color: "text-amber-400" },
    { label: "Boutique", icon: ShoppingBag, desc: "Cœurs, diamants et packs", route: "player.shop", color: "text-emerald-400" },
    { label: "Villes", icon: Map, desc: "Explorer les districts", route: "player.cities", color: "text-sky-400" },
    { label: "Profil", icon: User, desc: "Statistiques et succès", route: "profile.edit", color: "text-purple-400" },
];
</script>

<template>
    <Head title="Hub Control — CityPlay" />

    <SiteLayout>
        <div class="mx-auto max-w-4xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
            <div class="flex items-center gap-6 mb-12 animate-in fade-in slide-in-from-left-4 duration-700">
                <div class="h-16 w-16 rounded-[2rem] bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] grid place-items-center text-primary">
                    <Settings class="h-8 w-8 animate-spin-slow" />
                </div>
                <div>
                    <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Terminal System</div>
                    <h1 class="font-display text-4xl text-foreground uppercase italic font-black">Control <span class="text-white/40">Hub</span></h1>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Link v-for="s in shortcuts" :key="s.label" :href="route(s.route)"
                      class="group relative overflow-hidden rounded-[2.5rem] bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-8 transition-all duration-500 hover:border-primary/40 hover:scale-[1.02] active:scale-95">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-5">
                            <div :class="cn('h-14 w-14 rounded-2xl bg-white/5 border border-white/10 grid place-items-center transition-all duration-500 group-hover:scale-110 group-hover:border-primary/20', s.color)">
                                <component :is="s.icon" class="h-7 w-7" />
                            </div>
                            <div>
                                <h2 class="font-display text-xl text-foreground uppercase italic font-black tracking-tight group-hover:text-primary transition-colors">{{ s.label }}</h2>
                                <p class="text-xs text-muted-foreground mt-1">{{ s.desc }}</p>
                            </div>
                        </div>
                        <ChevronRight class="h-5 w-5 text-white/20 group-hover:text-primary group-hover:translate-x-1 transition-all" />
                    </div>

                    <!-- Ambient Glow -->
                    <div class="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-primary/5 blur-3xl group-hover:bg-primary/10 transition-colors" />
                </Link>
            </div>

            <!-- Additional Settings / Info Section -->
            <div class="mt-12 space-y-6">
                <div class="bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] rounded-[2.5rem] p-8">
                    <h3 class="font-display text-sm text-primary uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <Sparkles class="h-4 w-4" /> Account Overview
                    </h3>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl glass border border-white/5 text-center group">
                            <Heart class="h-5 w-5 mx-auto text-destructive mb-2 group-hover:scale-110 transition-transform" />
                            <div class="text-xl font-display font-black text-foreground">{{ $page.props.auth.user?.hearts || 0 }}</div>
                            <div class="text-[8px] uppercase font-black text-muted-foreground tracking-widest">Lives</div>
                        </div>
                        <div class="p-4 rounded-2xl glass border border-white/5 text-center group">
                            <Gem class="h-5 w-5 mx-auto text-sky-400 mb-2 group-hover:scale-110 transition-transform" />
                            <div class="text-xl font-display font-black text-foreground">{{ $page.props.auth.user?.diamonds || 0 }}</div>
                            <div class="text-[8px] uppercase font-black text-muted-foreground tracking-widest">Diamonds</div>
                        </div>
                        <div class="p-4 rounded-2xl glass border border-white/5 text-center group">
                            <Zap class="h-5 w-5 mx-auto text-amber-400 mb-2 group-hover:scale-110 transition-transform" />
                            <div class="text-xl font-display font-black text-foreground">{{ $page.props.auth.user?.xp || 0 }}</div>
                            <div class="text-[8px] uppercase font-black text-muted-foreground tracking-widest">XP Level</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
