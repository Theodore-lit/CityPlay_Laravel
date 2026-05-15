<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Bienvenue sur CityPlay" />
    
    <div class="min-h-screen bg-gaming-dark text-white font-sans selection:bg-gaming-blue selection:text-white">
        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full pointer-events-none">
                <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-gaming-blue/20 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-gaming-green/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
                <nav class="flex justify-between items-center py-8">
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gaming-blue rounded-lg flex items-center justify-center shadow-gaming">
                            <span class="text-2xl">🏙️</span>
                        </div>
                        <span class="text-2xl font-black tracking-tighter uppercase italic">CityPlay</span>
                    </div>

                    <div v-if="canLogin" class="space-x-4">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" 
                              class="text-sm font-bold uppercase tracking-widest hover:text-gaming-blue-light transition-colors">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm font-bold uppercase tracking-widest hover:text-gaming-blue-light transition-colors">
                                Connexion
                            </Link>
                            <Link v-if="canRegister" :href="route('register')" 
                                  class="bg-gaming-blue hover:bg-gaming-blue-dark px-6 py-2 rounded-full text-sm font-bold uppercase tracking-widest transition-all shadow-gaming">
                                Inscription
                            </Link>
                        </template>
                    </div>
                </nav>

                <div class="py-24 lg:py-40 text-center">
                    <h1 class="text-6xl lg:text-8xl font-black uppercase tracking-tighter italic leading-none mb-8">
                        Explore ta ville <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-gaming-blue-light to-gaming-green-light">Comme un terrain de jeu</span>
                    </h1>
                    <p class="max-w-2xl mx-auto text-lg text-gray-400 mb-12">
                        CityPlay transforme chaque coin de rue en une énigme. Relève des défis, collectionne des récompenses et découvre l'histoire secrète de ta ville.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <Link :href="route('register')" class="w-full sm:w-auto bg-gaming-green hover:bg-gaming-green-dark text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-gaming-green transition-all transform hover:-translate-y-1">
                            Commencer l'aventure
                        </Link>
                        <button class="w-full sm:w-auto border border-gaming-blue/30 hover:bg-gaming-blue/10 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest transition-all">
                            Voir les villes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="py-24 bg-gaming-surface/30">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="text-center p-8 bg-gaming-surface border border-gaming-blue/10 rounded-3xl hover:border-gaming-blue/40 transition-all">
                        <div class="text-4xl mb-6">📍</div>
                        <h3 class="text-xl font-bold uppercase mb-4">Géolocalisation</h3>
                        <p class="text-gray-400 text-sm">Le jeu s'adapte à ta position réelle pour une immersion totale.</p>
                    </div>
                    <div class="text-center p-8 bg-gaming-surface border border-gaming-green/10 rounded-3xl hover:border-gaming-green/40 transition-all">
                        <div class="text-4xl mb-6">🧠</div>
                        <h3 class="text-xl font-bold uppercase mb-4">Énigmes Variées</h3>
                        <p class="text-gray-400 text-sm">Des devinettes historiques aux défis d'observation modernes.</p>
                    </div>
                    <div class="text-center p-8 bg-gaming-surface border border-gaming-blue/10 rounded-3xl hover:border-gaming-blue/40 transition-all">
                        <div class="text-4xl mb-6">🏆</div>
                        <h3 class="text-xl font-bold uppercase mb-4">Récompenses</h3>
                        <p class="text-gray-400 text-sm">Gagne des pièces et des cœurs pour débloquer des indices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
