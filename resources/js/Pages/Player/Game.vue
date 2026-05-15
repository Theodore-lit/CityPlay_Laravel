<script setup>
import GamingLayout from '@/Layouts/GamingLayout.vue';
import MapComponent from '@/Components/MapComponent.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

/**
 * Page Game : L'interface principale de jeu pour le joueur.
 * Gère la géolocalisation, le calcul de distance et l'affichage des énigmes.
 */
const props = defineProps({
    city: Object, // La ville actuelle avec ses lieux et énigmes
});

// --- États Réactifs ---
const activeTab = ref('map');           // Onglet actif : 'map' ou 'enigmas'
const userPosition = ref(null);         // Position GPS de l'utilisateur {lat, lng}
const distanceToClosest = ref(null);    // Distance formatée (ex: "250m")
const closestLocation = ref(null);      // Lieu le plus proche de l'utilisateur
const watchId = ref(null);              // ID du watcher de géolocalisation
const isNearLocation = ref(false);      // Vrai si le joueur est dans le rayon d'un lieu

/**
 * Calcule la distance entre deux points GPS (Formule de Haversine).
 * @returns {number} Distance en mètres.
 */
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371e3; // Rayon de la Terre en mètres
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c; 
};

/**
 * Met à jour les distances par rapport à tous les lieux de la ville.
 */
const updateDistances = (lat, lng) => {
    let minDistance = Infinity;
    let closest = null;

    props.city.locations.forEach(loc => {
        const d = calculateDistance(lat, lng, loc.latitude, loc.longitude);
        if (d < minDistance) {
            minDistance = d;
            closest = loc;
        }
    });

    closestLocation.value = closest;
    // Vérifie si le joueur est entré dans le périmètre autorisé du lieu
    isNearLocation.value = closest && minDistance <= closest.radius_meters;
    
    // Formate la distance pour l'affichage (m ou km)
    distanceToClosest.value = minDistance > 1000 
        ? (minDistance / 1000).toFixed(1) + 'km' 
        : Math.round(minDistance) + 'm';
};

/**
 * Démarre le suivi GPS de l'utilisateur via l'API Browser.
 */
const startTracking = () => {
    if ("geolocation" in navigator) {
        watchId.value = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                userPosition.value = { lat: latitude, lng: longitude };
                updateDistances(latitude, longitude);
            },
            (error) => {
                console.error("Erreur GPS :", error);
            },
            { enableHighAccuracy: true } // Utilise le GPS plutôt que le Wi-Fi si possible
        );
    }
};

/**
 * Valide la position du joueur pour débloquer les énigmes du lieu.
 */
const validatePosition = () => {
    if (isNearLocation.value) {
        alert(`Félicitations ! Vous êtes à ${closestLocation.value.name}. Énigme débloquée !`);
        activeTab.value = 'enigmas';
    } else {
        alert("Vous êtes encore trop loin du lieu. Rapprochez-vous !");
    }
};

// --- Cycle de vie ---
onMounted(() => {
    startTracking();
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});

/**
 * Helper pour les classes CSS des onglets mobiles.
 */
const mobileTabClass = (tab) => [
    'flex-1 flex flex-col items-center justify-center py-2 text-[10px] font-bold uppercase tracking-tighter transition-all',
    activeTab.value === tab ? 'text-gaming-blue bg-gaming-blue/10' : 'text-gray-500'
];
</script>

<template>
    <Head :title="city.name" />

    <GamingLayout>
        <div class="h-[calc(100vh-4rem)] flex overflow-hidden">
            <!-- Sidebar Desktop -->
            <div class="hidden md:flex w-96 bg-gaming-surface border-r border-gaming-blue/20 flex-col z-10">
                <div class="p-6 border-b border-gaming-blue/10">
                    <h2 class="text-2xl font-black text-gaming-blue-light uppercase tracking-tighter">{{ city.name }}</h2>
                    <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Exploration en cours</p>
                </div>

                <!-- Navigation Onglets Desktop -->
                <div class="flex border-b border-gaming-blue/10">
                    <button @click="activeTab = 'map'" 
                            :class="['flex-1 py-4 text-sm font-bold uppercase tracking-wider transition-all', activeTab === 'map' ? 'text-gaming-blue border-b-2 border-gaming-blue bg-gaming-blue/5' : 'text-gray-500 hover:text-gray-300']">
                        Carte
                    </button>
                    <button @click="activeTab = 'enigmas'" 
                            :class="['flex-1 py-4 text-sm font-bold uppercase tracking-wider transition-all', activeTab === 'enigmas' ? 'text-gaming-green border-b-2 border-gaming-green bg-gaming-green/5' : 'text-gray-500 hover:text-gray-300']">
                        Énigmes
                    </button>
                </div>

                <!-- Liste des Lieux/Énigmes Desktop -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div v-if="activeTab === 'map'">
                        <div class="space-y-4">
                            <div v-for="location in city.locations" :key="location.id" 
                                 class="bg-gaming-dark/50 border border-gaming-blue/10 rounded-xl p-4 hover:border-gaming-blue/40 transition-all cursor-pointer group">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-white group-hover:text-gaming-blue-light transition-colors">{{ location.name }}</h4>
                                    <span class="text-[10px] bg-gaming-blue/20 text-gaming-blue px-1.5 py-0.5 rounded uppercase font-bold">{{ location.category }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ location.description }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'enigmas'">
                        <div class="space-y-4">
                            <div v-for="location in city.locations" :key="'loc-'+location.id">
                                <div v-for="enigma in location.enigmas" :key="enigma.id"
                                     class="bg-gaming-dark/50 border border-gaming-green/10 rounded-xl p-4 hover:border-gaming-green/40 transition-all cursor-pointer group">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold text-white group-hover:text-gaming-green-light transition-colors">{{ enigma.title }}</h4>
                                        <span :class="['text-[10px] px-1.5 py-0.5 rounded uppercase font-bold', 
                                            enigma.difficulty === 'easy' ? 'bg-green-500/20 text-green-500' : 
                                            enigma.difficulty === 'medium' ? 'bg-yellow-500/20 text-yellow-500' : 'bg-red-500/20 text-red-500']">
                                            {{ enigma.difficulty }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 line-clamp-2">{{ enigma.content }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de Validation Desktop -->
                <div class="p-6 border-t border-gaming-blue/10">
                    <button @click="validatePosition"
                            :class="['w-full font-black py-3 rounded-xl uppercase tracking-tighter transition-all transform hover:-translate-y-0.5', 
                                     isNearLocation ? 'bg-gaming-green shadow-gaming-green text-white' : 'bg-gray-700 text-gray-400 cursor-not-allowed']">
                        {{ isNearLocation ? 'Valider Position GPS' : 'Approchez-vous d\'un lieu' }}
                    </button>
                </div>
            </div>

            <!-- Vue Principale (Mobile-First) -->
            <div class="flex-1 relative flex flex-col h-full">
                <!-- Header Mobile -->
                <div class="md:hidden bg-gaming-surface border-b border-gaming-blue/20 p-4 flex justify-between items-center shrink-0">
                    <h2 class="text-lg font-black text-gaming-blue-light uppercase tracking-tighter">{{ city.name }}</h2>
                    <div class="flex items-center space-x-2 text-xs font-bold uppercase">
                        <span class="text-gray-500">Distance :</span>
                        <span class="text-gaming-blue-light">{{ distanceToClosest || '--' }}</span>
                    </div>
                </div>

                <!-- Zone d'affichage Contenu Mobile -->
                <div class="flex-1 relative">
                    <!-- Vue Carte -->
                    <div v-show="activeTab === 'map'" class="absolute inset-0">
                        <MapComponent :locations="city.locations" :userPosition="userPosition" />
                        
                        <!-- Bouton Flottant de Validation Mobile -->
                        <div class="md:hidden absolute bottom-24 right-4 flex flex-col space-y-3 z-10">
                            <button @click="validatePosition" 
                                    :class="['w-14 h-14 rounded-full flex items-center justify-center shadow-lg transition-all', 
                                             isNearLocation ? 'bg-gaming-green animate-bounce text-white' : 'bg-gaming-surface border border-gaming-blue/30 text-gray-400']">
                                <span class="text-2xl">✅</span>
                            </button>
                        </div>
                    </div>

                    <!-- Vue Énigmes Mobile -->
                    <div v-if="activeTab === 'enigmas'" class="md:hidden absolute inset-0 bg-gaming-dark overflow-y-auto p-4 space-y-4">
                        <div v-for="location in city.locations" :key="'mloc-'+location.id" class="space-y-3">
                            <h3 class="text-gaming-blue-light font-black uppercase tracking-widest text-sm border-b border-gaming-blue/10 pb-1">{{ location.name }}</h3>
                            <div v-for="enigma in location.enigmas" :key="'menig-'+enigma.id"
                                 class="bg-gaming-surface border border-gaming-green/20 rounded-2xl p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-bold text-white">{{ enigma.title }}</h4>
                                    <span class="text-[10px] bg-gaming-green/20 text-gaming-green px-1.5 py-0.5 rounded uppercase font-bold">{{ enigma.difficulty }}</span>
                                </div>
                                <p class="text-sm text-gray-400 leading-relaxed">{{ enigma.content }}</p>
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex space-x-3">
                                        <span class="text-xs text-yellow-400 font-bold">🪙 {{ enigma.reward_coins }}</span>
                                        <span class="text-xs text-red-500 font-bold">❤️ {{ enigma.reward_hearts }}</span>
                                    </div>
                                    <button class="bg-gaming-blue text-white px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest">Répondre</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barre de Navigation Basse Mobile -->
                <div class="md:hidden bg-gaming-surface border-t border-gaming-blue/20 flex shrink-0">
                    <button @click="activeTab = 'map'" :class="mobileTabClass('map')">
                        <span class="text-xl mb-0.5">🗺️</span>
                        <span>Carte</span>
                    </button>
                    <button @click="activeTab = 'enigmas'" :class="mobileTabClass('enigmas')">
                        <span class="text-xl mb-0.5">🧩</span>
                        <span>Énigmes</span>
                    </button>
                    <button @click="activeTab = 'inventory'" :class="mobileTabClass('inventory')">
                        <span class="text-xl mb-0.5">🎒</span>
                        <span>Sac</span>
                    </button>
                </div>
            </div>
        </div>
    </GamingLayout>
</template>

<style scoped>
:deep(main) {
    height: calc(100vh - 4rem);
    overflow: hidden;
}

/* Scrollbar Personnalisée Style Gaming */
::-webkit-scrollbar {
    width: 4px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(37, 99, 235, 0.2);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(37, 99, 235, 0.4);
}
</style>
