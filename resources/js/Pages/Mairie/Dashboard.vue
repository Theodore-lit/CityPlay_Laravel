<script setup>
import GamingLayout from '@/Layouts/GamingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    cities: Array,
    stats: Object,
});
</script>

<template>
    <Head title="Administration Mairie" />

    <GamingLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-gaming-green-light leading-tight uppercase tracking-widest">
                    Tableau de bord Mairie
                </h2>
                <button class="bg-gaming-green hover:bg-gaming-green-dark text-white font-bold py-2 px-6 rounded-lg transition-all uppercase text-sm tracking-wider">
                    + Créer une ville
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Parties Totales</p>
                        <p class="text-3xl font-black text-white">{{ stats.total_sessions }}</p>
                    </div>
                    <div class="bg-gaming-surface border border-gaming-green/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Joueurs Actifs</p>
                        <p class="text-3xl font-black text-gaming-green-light">{{ stats.active_players }}</p>
                    </div>
                    <div class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Taux de réussite</p>
                        <p class="text-3xl font-black text-gaming-blue-light">78%</p>
                    </div>
                </div>

                <!-- Cities Management -->
                <div class="bg-gaming-surface border border-gaming-blue/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-gaming-blue/10">
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider">Ma Ville</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gaming-dark/30">
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Ville</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Statut</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Lieux</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gaming-blue/5">
                                <tr v-for="city in cities" :key="city.id" class="hover:bg-gaming-blue/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white">{{ city.name }}</div>
                                        <div class="text-xs text-gray-500 truncate w-48">{{ city.description }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2 py-1 rounded text-[10px] font-bold uppercase', city.is_active ? 'bg-gaming-green/20 text-gaming-green-light' : 'bg-gray-500/20 text-gray-500']">
                                            {{ city.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        {{ city.locations_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-3">
                                            <button class="text-gaming-blue-light hover:text-white transition-colors text-sm font-bold">Modifier</button>
                                            <button class="text-red-500 hover:text-white transition-colors text-sm font-bold">Désactiver</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GamingLayout>
</template>
