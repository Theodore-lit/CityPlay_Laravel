<script setup>
import GamingLayout from '@/Layouts/GamingLayout.vue';
import MapComponent from '@/Components/MapComponent.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    city: Object,
});

// --- États Réactifs ---
const activeTab = ref('map');
const mapRef = ref(null); // Référence pour appeler MapComponent
const userPosition = ref(null);
const distanceToClosest = ref(null);
const closestLocation = ref(null);
const watchId = ref(null);
const isNearLocation = ref(false);

/**
 * Correctif : Surveille le changement d'onglet pour réparer la carte
 */
watch(activeTab, (newTab) => {
    if (newTab === 'map') {
        nextTick(() => {
            mapRef.value?.refreshSize();
        });
    }
});

const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371e3;
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
    isNearLocation.value = closest && minDistance <= closest.radius_meters;
    distanceToClosest.value = minDistance > 1000
        ? (minDistance / 1000).toFixed(1) + 'km'
        : Math.round(minDistance) + 'm';
};

const startTracking = () => {
    if ("geolocation" in navigator) {
        watchId.value = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                userPosition.value = { lat: latitude, lng: longitude };
                updateDistances(latitude, longitude);
            },
            (error) => console.error("Erreur GPS :", error),
            { enableHighAccuracy: true }
        );
    }
};

const validatePosition = () => {
    if (isNearLocation.value) {
        alert(`Félicitations ! Vous êtes à ${closestLocation.value.name}.`);
        activeTab.value = 'enigmas';
    } else {
        alert("Rapprochez-vous encore !");
    }
};

onMounted(() => startTracking());
onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});

const mobileTabClass = (tab) => [
    'flex-1 flex flex-col items-center justify-center py-2 text-[10px] font-bold uppercase transition-all',
    activeTab.value === tab ? 'text-gaming-blue bg-gaming-blue/10' : 'text-gray-500'
];
</script>

<template>
    <Head :title="city.name" />

    <GamingLayout>
        <div class="h-[calc(100vh-4rem)] flex overflow-hidden bg-gaming-dark">
            <!-- Sidebar Desktop -->
            <aside class="hidden md:flex w-96 bg-gaming-surface border-r border-gaming-blue/20 flex-col z-10">
                <div class="p-6 border-b border-gaming-blue/10">
                    <h2 class="text-2xl font-black text-gaming-blue-light uppercase tracking-tighter">{{ city.name }}</h2>
                </div>

                <!-- Navigation Desktop -->
                <div class="flex border-b border-gaming-blue/10">
                    <button @click="activeTab = 'map'" :class="['flex-1 py-4 text-sm font-bold uppercase', activeTab === 'map' ? 'text-gaming-blue border-b-2 border-gaming-blue bg-gaming-blue/5' : 'text-gray-500']">Carte</button>
                    <button @click="activeTab = 'enigmas'" :class="['flex-1 py-4 text-sm font-bold uppercase', activeTab === 'enigmas' ? 'text-gaming-green border-b-2 border-gaming-green bg-gaming-green/5' : 'text-gray-500']">Énigmes</button>
                </div>

                <!-- Contenu Desktop -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div v-if="activeTab === 'map'" class="space-y-4">
                        <div v-for="location in city.locations" :key="location.id" class="bg-gaming-dark/50 border border-gaming-blue/10 rounded-xl p-4 hover:border-gaming-blue/40 transition-all cursor-pointer">
                            <h4 class="font-bold text-white">{{ location.name }}</h4>
                            <p class="text-[10px] text-gaming-blue uppercase">{{ location.category }}</p>
                        </div>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="loc in city.locations" :key="loc.id">
                            <div v-for="enigma in loc.enigmas" :key="enigma.id" class="bg-gaming-dark/50 border border-gaming-green/10 rounded-xl p-4 mb-3">
                                <h4 class="font-bold text-white">{{ enigma.title }}</h4>
                                <p class="text-xs text-gray-400">{{ enigma.content }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gaming-blue/10">
                    <button @click="validatePosition" :class="['w-full font-black py-3 rounded-xl uppercase transition-all', isNearLocation ? 'bg-gaming-green text-white shadow-lg' : 'bg-gray-700 text-gray-400 cursor-not-allowed']">
                        {{ isNearLocation ? 'Valider Position' : 'Trop loin' }}
                    </button>
                </div>
            </aside>

            <!-- Vue Principale (Mobile) -->
            <main class="flex-1 relative flex flex-col h-full overflow-hidden">
                <header class="md:hidden bg-gaming-surface border-b border-gaming-blue/20 p-4 flex justify-between items-center shrink-0">
                    <h2 class="text-lg font-black text-gaming-blue-light uppercase">{{ city.name }}</h2>
                    <span class="text-xs text-gaming-blue-light font-bold">📍 {{ distanceToClosest || '--' }}</span>
                </header>

                <div class="flex-1 relative">
                    <!-- LA CARTE -->
                    <div v-show="activeTab === 'map'" class="absolute inset-0">
                        <MapComponent ref="mapRef" :locations="city.locations" :userPosition="userPosition" />
                        <button @click="validatePosition" class="md:hidden absolute bottom-6 right-4 w-14 h-14 rounded-full shadow-2xl z-20 flex items-center justify-center text-2xl transition-all" :class="isNearLocation ? 'bg-gaming-green animate-bounce' : 'bg-gaming-surface border border-gaming-blue/30 text-gray-500'">
                            {{ isNearLocation ? '✅' : '🔒' }}
                        </button>
                    </div>

                    <!-- LES ENIGMES MOBILE -->
                    <div v-if="activeTab === 'enigmas'" class="md:hidden absolute inset-0 bg-gaming-dark overflow-y-auto p-4 space-y-6">
                        <div v-for="loc in city.locations" :key="'m-'+loc.id">
                            <h3 class="text-gaming-blue-light font-black text-xs uppercase mb-3">{{ loc.name }}</h3>
                            <div v-for="enigma in loc.enigmas" :key="'me-'+enigma.id" class="bg-gaming-surface border border-gaming-green/20 rounded-2xl p-4 mb-4">
                                <h4 class="font-bold text-white">{{ enigma.title }}</h4>
                                <p class="text-sm text-gray-400 mt-2">{{ enigma.content }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Mobile -->
                <nav class="md:hidden bg-gaming-surface border-t border-gaming-blue/20 flex shrink-0 h-16">
                    <button @click="activeTab = 'map'" :class="mobileTabClass('map')"><span>🗺️</span><span>Carte</span></button>
                    <button @click="activeTab = 'enigmas'" :class="mobileTabClass('enigmas')"><span>🧩</span><span>Énigmes</span></button>
                    <button @click="activeTab = 'inventory'" :class="mobileTabClass('inventory')"><span>🎒</span><span>Sac</span></button>
                </nav>
            </main>
        </div>
    </GamingLayout>
</template>
