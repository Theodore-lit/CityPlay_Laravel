<script setup>
import GamingLayout from '@/Layouts/GamingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    cities: Array,
});
</script>

<template>
    <Head title="Choisis ta ville" />

    <GamingLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-gaming-blue-light leading-tight uppercase tracking-widest">
                Villes disponibles
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="city in cities" :key="city.id" 
                         class="bg-gaming-surface border border-gaming-blue/20 rounded-2xl overflow-hidden hover:border-gaming-blue/50 transition-all duration-300 hover:shadow-gaming group">
                        <div class="h-48 bg-gray-800 relative overflow-hidden">
                            <img v-if="city.image_path" :src="city.image_path" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gaming-blue/20 to-gaming-green/20">
                                <span class="text-4xl text-gaming-blue/40">🏙️</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark to-transparent opacity-60"></div>
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-gaming-green text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">Actif</span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-gaming-blue-light transition-colors">{{ city.name }}</h3>
                            <p class="text-gray-400 text-sm mb-6 line-clamp-2">{{ city.description }}</p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2 text-xs text-gray-500 uppercase tracking-widest">
                                    <span>{{ city.locations_count || 0 }} Lieux</span>
                                    <span>•</span>
                                    <span>8-22h</span>
                                </div>
                                
                                <Link :href="route('player.game', city.id)" 
                                      class="bg-gaming-blue hover:bg-gaming-blue-dark text-white font-bold py-2 px-6 rounded-full transition-all hover:shadow-gaming-blue transform hover:-translate-y-0.5">
                                    Jouer
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GamingLayout>
</template>
