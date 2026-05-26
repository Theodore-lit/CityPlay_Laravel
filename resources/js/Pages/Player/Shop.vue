<script setup>
/**
 * Boutique CityPlay - Gestion des achats de ressources et boosts.
 * Logique d'économie interne et transactions XP/Diamants.
 *
 * Auteur: Kamal
 */
import { ref, computed, watch } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ShoppingBag, Heart, Gem, Zap, ChevronLeft, Sparkles, Package, Star, AlertCircle, CheckCircle
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const page = usePage();
const props = defineProps({
    user: {
        type: Object,
        default: () => ({ xp: 0, hearts: 0, diamonds: 0 })
    },
});

const loading = ref(null);
const activeCategory = ref('resources');
const successMessage = ref('');
const errorMessage = ref('');
const confirmModal = ref(null);

// Surveillance des messages flash envoyés par le serveur kamal
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        successMessage.value = flash.success;
        setTimeout(() => { successMessage.value = ''; }, 4000);
    }
    if (flash?.error) {
        errorMessage.value = flash.error;
        setTimeout(() => { errorMessage.value = ''; }, 4000);
    }
}, { deep: true });

// Catégories de la boutique kamal
const categories = [
    { id: 'resources', label: 'Ressources', icon: ShoppingBag },
    { id: 'boosts', label: 'Boosts', icon: Zap },
];

// Catalogue des articles kamal
const items = {
    resources: [
        { id: 'heart', name: "Cœur Supplémentaire", icon: Heart, price: 500, currency: "XP", value: "+1 Vie", color: "text-destructive", route: 'player.buy.heart', description: 'Augmente votre nombre de vies pour continuer à jouer', badge: null },
        { id: 'diamonds', name: "Pack Diamants", icon: Gem, price: 1000, currency: "XP", value: "+1 Diamants", color: "text-sky-400", route: 'player.buy.diamonds', description: 'Débloquez des diamants pour des achats premium', badge: 'POPULAIRE' },
        { id: 'explorer-pass', name: "Passe Exploration", icon: Package, price: 2000, currency: "XP", value: "24h Illimité", color: "text-amber-400", route: 'player.buy.explorer-pass', description: 'Explorez sans limite pendant 24 heures', badge: null },
    ],
    boosts: [
        { id: 'xp-boost', name: "Boost Multiplicateur x2", icon: Zap, price: 10, currency: "DIAMONDS", value: "Multiplicateur x2", color: "text-yellow-400", route: 'player.buy.xp-boost', description: 'Doublez vos gains d\'XP, Cœurs et Diamants pendant 1 heure', badge: 'ULTIME' },
    ],
};

/**
 * Vérifie si le joueur a les ressources nécessaires kamal
 */
const canAfford = (item) => {
    if (item.currency === 'DIAMONDS') {
        return (props.user?.diamonds || 0) >= item.price;
    }
    return (props.user?.xp || 0) >= item.price;
};

/**
 * Affiche la fenêtre de confirmation d'achat kamal
 */
const showConfirmation = (item) => {
    confirmModal.value = item;
};

/**
 * Ferme la fenêtre de confirmation kamal
 */
const closeConfirmation = () => {
    confirmModal.value = null;
};

/**
 * Exécute l'achat via une requête POST au serveur kamal
 */
const buyItem = (item) => {
    if (!item.route) {
        errorMessage.value = 'Cet article n\'est pas disponible';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
        return;
    }

    if (!canAfford(item)) {
        const missingAmount = item.currency === 'DIAMONDS'
            ? item.price - (props.user?.diamonds || 0)
            : item.price - (props.user?.xp || 0);

        errorMessage.value = `${missingAmount} ${item.currency} manquants`;
        setTimeout(() => { errorMessage.value = ''; }, 3000);
        confirmModal.value = null;
        return;
    }

    loading.value = item.id;
    router.post(route(item.route), {}, {
        onFinish: () => {
            loading.value = null;
            confirmModal.value = null;
        }
    });
};
</script>

<template>
    <Head title="Boutique — CityPlay" />

    <SiteLayout>
        <!-- Success Notification -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div v-if="successMessage" class="fixed top-4 right-4 z-50 bg-green-500/90 text-white px-6 py-4 rounded-xl backdrop-blur-sm shadow-lg flex items-center gap-3">
                <CheckCircle class="h-5 w-5" />
                <span class="font-semibold">{{ successMessage }}</span>
            </div>
        </Transition>

        <!-- Error Notification -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div v-if="errorMessage" class="fixed top-4 right-4 z-50 bg-red-500/90 text-white px-6 py-4 rounded-xl backdrop-blur-sm shadow-lg flex items-center gap-3">
                <AlertCircle class="h-5 w-5" />
                <span class="font-semibold">{{ errorMessage }}</span>
            </div>
        </Transition>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Header -->
            <div class="flex items-center gap-6 mb-12">
                <Link :href="route('player.hub')" class="h-12 w-12 rounded-2xl bg-white/15 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] grid place-items-center text-primary hover:scale-110 transition-all">
                    <ChevronLeft class="h-6 w-6" />
                </Link>
                <div>
                    <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1">⚡ District Market</div>
                    <h1 class="font-display text-4xl text-foreground uppercase italic font-black">Boutique <span class="text-white/40">CityPlay</span></h1>
                </div>
            </div>

            <!-- Player Stats Bar -->
            <div class="grid grid-cols-3 gap-4 mb-12 md:grid-cols-4">
                <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/10 border border-yellow-500/30 rounded-xl p-4 text-center">
                    <div class="text-xs text-yellow-400 uppercase font-black tracking-widest mb-1">⚡ XP</div>
                    <div class="text-2xl font-black text-yellow-300">{{ (props.user?.xp || 0).toLocaleString() }}</div>
                </div>
                <div class="bg-gradient-to-br from-red-500/20 to-red-600/10 border border-red-500/30 rounded-xl p-4 text-center">
                    <div class="text-xs text-red-400 uppercase font-black tracking-widest mb-1">❤️ Vies</div>
                    <div class="text-2xl font-black text-red-300">{{ props.user?.hearts || 0 }}</div>
                </div>
                <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/10 border border-blue-500/30 rounded-xl p-4 text-center">
                    <div class="text-xs text-blue-400 uppercase font-black tracking-widest mb-1">💎 Diamants</div>
                    <div class="text-2xl font-black text-blue-300">{{ props.user?.diamonds || 0 }}</div>
                </div>
                <div class="hidden md:block bg-gradient-to-br from-purple-500/20 to-purple-600/10 border border-purple-500/30 rounded-xl p-4 text-center">
                    <div class="text-xs text-purple-400 uppercase font-black tracking-widest mb-1">🏆 Niveau</div>
                    <div class="text-2xl font-black text-purple-300">{{ Math.floor((props.user?.xp || 0) / 1000) + 1 }}</div>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="flex gap-3 mb-12 overflow-x-auto pb-2">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="activeCategory = cat.id"
                    :class="cn(
                        'px-6 py-3 rounded-xl font-black uppercase text-xs tracking-wider whitespace-nowrap transition-all flex items-center gap-2',
                        activeCategory === cat.id
                            ? 'bg-primary text-black shadow-lg shadow-primary/50'
                            : 'bg-white/10 border border-white/20 text-white/70 hover:border-primary/50 hover:text-white'
                    )"
                >
                    <component :is="cat.icon" class="h-4 w-4" />
                    {{ cat.label }}
                </button>
            </div>

            <!-- Items Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="item in items[activeCategory]"
                    :key="item.id"
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-6 transition-all duration-500 hover:border-primary/40 hover:shadow-lg hover:shadow-primary/20"
                >
                    <!-- Badge -->
                    <div v-if="item.badge" class="absolute top-3 right-3 bg-gradient-to-r from-primary to-purple-500 text-black px-3 py-1 rounded-full text-[10px] font-black">
                        {{ item.badge }}
                    </div>

                    <!-- Icon Container -->
                    <div :class="cn('h-24 w-24 mx-auto rounded-2xl bg-gradient-to-br from-white/10 to-white/5 border border-white/20 grid place-items-center mb-6 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-lg', item.color)">
                        <component :is="item.icon" class="h-12 w-12" />
                    </div>

                    <!-- Content -->
                    <div class="text-center mb-6">
                        <h2 class="font-display text-xl text-foreground uppercase italic font-black tracking-tight mb-2">
                            {{ item.name }}
                        </h2>
                        <p class="text-sm text-white/60 mb-3">{{ item.description }}</p>
                        <div class="inline-block bg-white/10 rounded-lg px-3 py-2">
                            <p class="text-xs text-primary uppercase font-black tracking-widest">{{ item.value }}</p>
                        </div>
                    </div>

                    <!-- Price & Button -->
                    <div class="border-t border-white/10 pt-4">
                        <div class="text-center mb-4">
                            <span class="text-2xl font-black text-white">{{ item.price }}</span>
                            <span class="text-xs text-yellow-400 font-black ml-2 uppercase">{{ item.currency }}</span>
                        </div>
                        <button
                            @click="showConfirmation(item)"
                            :disabled="!canAfford(item) || loading === item.id"
                            :class="cn(
                                'w-full h-12 rounded-xl font-black uppercase text-sm tracking-wider transition-all duration-300',
                                canAfford(item)
                                    ? 'bg-gradient-to-r from-primary to-purple-500 text-black hover:shadow-lg hover:shadow-primary/50 disabled:opacity-50'
                                    : 'bg-white/10 text-white/40 cursor-not-allowed'
                            )"
                        >
                            <span v-if="loading === item.id" class="inline-block animate-spin mr-2">⏳</span>
                            {{ loading === item.id ? 'Achat...' : 'ACHETER' }}
                        </button>
                        <p v-if="!canAfford(item)" class="text-[10px] text-red-400 mt-2 font-black uppercase">
                            {{ item.price - (item.currency === 'DIAMONDS' ? (props.user?.diamonds || 0) : (props.user?.xp || 0)) }} {{ item.currency }} manquants
                        </p>
                    </div>

                    <!-- Ambient Glow -->
                    <div class="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-primary/5 blur-3xl group-hover:bg-primary/10 transition-colors" />
                </div>
            </div>

            <!-- Info Section -->
            <div class="mt-16 grid gap-6 md:grid-cols-2">
                <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                    <h3 class="font-display text-lg text-foreground uppercase italic font-black mb-4 flex items-center gap-2">
                        <Sparkles class="h-5 w-5 text-primary" />
                        Comment gagner des XP ?
                    </h3>
                    <ul class="space-y-2 text-sm text-white/70">
                        <li>✓ Complétez les quiz et énigmes</li>
                        <li>✓ Participez aux compétitions</li>
                        <li>✓ Explorez les zones cachées</li>
                        <li>✓ Complétez les défis journaliers</li>
                    </ul>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                    <h3 class="font-display text-lg text-foreground uppercase italic font-black mb-4 flex items-center gap-2">
                        <Star class="h-5 w-5 text-primary" />
                        Astuces
                    </h3>
                    <p class="text-sm text-white/70 mb-4">
                        Économisez vos XP pour les articles les plus rares. Les passes exploration offrent la meilleure valeur !
                    </p>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="confirmModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div v-if="confirmModal" class="bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-2xl border border-white/30 rounded-2xl p-8 max-w-sm w-full shadow-2xl">
                        <h2 class="font-display text-2xl font-black text-foreground mb-4">Confirmer l'achat</h2>
                        <p class="text-white/70 mb-6">
                            Voulez-vous acheter <span class="font-bold text-white">{{ confirmModal.name }}</span> pour <span class="font-black text-yellow-400">{{ confirmModal.price }} {{ confirmModal.currency }}</span> ?
                        </p>
                        <div class="flex gap-4">
                            <button
                                @click="closeConfirmation"
                                class="flex-1 px-4 py-3 rounded-xl border border-white/20 text-white font-black uppercase text-sm tracking-wider hover:bg-white/10 transition-all"
                            >
                                Annuler
                            </button>
                            <button
                                @click="buyItem(confirmModal)"
                                :disabled="loading === confirmModal.id"
                                class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-purple-500 text-black font-black uppercase text-sm tracking-wider hover:shadow-lg hover:shadow-primary/50 transition-all disabled:opacity-50"
                            >
                                <span v-if="loading === confirmModal.id" class="inline-block animate-spin mr-2">⏳</span>
                                {{ loading === confirmModal.id ? 'Achat...' : 'Confirmer' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <MobileTabBar />
    </SiteLayout>
</template>
