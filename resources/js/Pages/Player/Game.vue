<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import MapComponent from '@/Components/MapComponent.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
  MapPin, QrCode, Navigation, Sparkles, Lock, Target, Zap, 
  ChevronLeft, Star, Heart, Clock
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    city: Object,
});

// --- États Réactifs ---
const activeTab = ref('map');
const mapRef = ref(null);
const userPosition = ref(null);
const distanceToClosest = ref(null);
const closestLocation = ref(null);
const watchId = ref(null);
const isNearLocation = ref(false);

/**
 * Surveille le changement d'onglet pour rafraîchir la carte si nécessaire
 */
watch(activeTab, (newTab) => {
    if (newTab === 'map') {
        nextTick(() => {
            // Si on avait un mécanisme de refresh dans MapComponent
            // mapRef.value?.refreshSize();
        });
    }
});

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
    isNearLocation.value = closest && minDistance <= (closest.radius_meters || 50);
    
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
            (error) => {
                console.error("Erreur GPS :", error);
            },
            { enableHighAccuracy: true }
        );
    }
};

const validatePosition = () => {
    if (isNearLocation.value) {
        alert(`Félicitations ! Vous êtes à ${closestLocation.value.name}. Énigme débloquée !`);
    } else {
        alert("Vous êtes encore trop loin du lieu. Rapprochez-vous !");
    }
};

onMounted(() => {
    startTracking();
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});
</script>

<template>
  <Head :title="`${city.name} — Mode Aventure`" />

  <SiteLayout hideFooter>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 pb-28 md:pb-12">
      <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <!-- ZONE CARTE -->
        <div class="relative h-[60vh] lg:h-[78vh] rounded-3xl glass-strong overflow-hidden border border-electric/20 shadow-neon">
          <div class="absolute inset-0 z-0">
            <MapComponent 
              ref="mapRef" 
              :locations="city.locations" 
              :userPosition="userPosition" 
            />
          </div>
          
          <!-- Overlay de la carte -->
          <div class="absolute top-4 left-4 right-4 flex items-center justify-between z-10 pointer-events-none">
            <div class="glass-strong px-4 py-2 rounded-xl flex items-center gap-2 pointer-events-auto">
              <Navigation class="h-4 w-4 text-electric animate-pulse" />
              <span class="text-xs font-display tracking-widest">{{ city.name.toUpperCase() }} • GPS ACTIF</span>
            </div>
            <button class="h-10 px-4 rounded-xl glass-strong text-xs font-display flex items-center gap-2 hover:text-electric transition-colors pointer-events-auto">
              <QrCode class="h-4 w-4 text-electric" /> SCANNER QR
            </button>
          </div>

          <!-- Popup de découverte à proximité -->
          <div v-if="closestLocation" class="absolute bottom-4 left-4 right-4 glass-strong rounded-2xl p-4 animate-fade-up z-10">
            <div class="flex items-center gap-4">
              <div :class="cn(
                'h-14 w-14 rounded-xl grid place-items-center shadow-neon shrink-0 transition-colors',
                isNearLocation ? 'bg-gradient-electric' : 'bg-gaming-panel border border-electric/20'
              )">
                <Sparkles v-if="isNearLocation" class="h-6 w-6 text-electric-foreground" />
                <Lock v-else class="h-6 w-6 text-electric/40" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-[10px] text-electric font-display tracking-widest uppercase">
                  {{ isNearLocation ? 'LIEU DÉCOUVERT' : 'Lieu le plus proche' }}
                </div>
                <div class="font-display text-base truncate">{{ closestLocation.name }}</div>
                <div class="text-xs text-muted-foreground">{{ distanceToClosest }} • {{ isNearLocation ? 'Énigme disponible' : 'Rapprochez-vous' }}</div>
              </div>
              <NeonButton 
                size="sm" 
                :variant="isNearLocation ? 'primary' : 'outline'"
                @click="validatePosition"
              >
                {{ isNearLocation ? 'Débloquer' : 'Vérifier' }}
              </NeonButton>
            </div>
          </div>
        </div>

        <!-- BARRE LATÉRALE -->
        <aside class="space-y-4">
          <div class="rounded-2xl glass-strong p-5">
            <div class="text-xs text-electric font-display tracking-widest uppercase">Quête Actuelle</div>
            <h2 class="font-display text-xl mt-1">Exploration de {{ city.name }}</h2>
            <div class="mt-4 h-2 rounded-full bg-gaming-darker overflow-hidden">
              <div class="h-full bg-gradient-electric" style="width: 30%" />
            </div>
            <div class="mt-2 flex justify-between text-xs">
              <span class="text-muted-foreground">1 sur {{ city.locations.length }} lieux</span>
              <span class="text-electric">+1,250 XP</span>
            </div>
          </div>

          <!-- LISTE DES LIEUX -->
          <div class="rounded-2xl glass p-5 max-h-[40vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-3 sticky top-0 bg-inherit z-10">
              <h3 class="font-display text-sm flex items-center gap-2">
                <Target class="h-4 w-4 text-electric" />MISSIONS À PROXIMITÉ
              </h3>
            </div>
            <div class="space-y-3">
              <div
                v-for="loc in city.locations"
                :key="loc.id"
                class="flex items-center gap-3 p-2 rounded-xl hover:bg-electric/5 transition cursor-pointer"
                @click="activeTab = 'map'"
              >
                <div class="h-12 w-12 rounded-lg bg-gaming-darker border border-electric/10 flex items-center justify-center shrink-0">
                  <MapPin class="h-6 w-6 text-electric/40" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-bold truncate flex items-center gap-1">
                    {{ loc.name }}
                  </div>
                  <div class="text-xs text-muted-foreground flex items-center gap-2">
                    <span class="text-electric flex items-center gap-0.5"><Zap class="h-3 w-3" />+250 XP</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ÉNIGME ACTIVE -->
          <div v-if="isNearLocation" class="rounded-2xl glass-strong p-5 animate-fade-up border border-purple-neon/30 shadow-purple">
            <div class="text-xs text-purple-neon font-display tracking-widest uppercase">Énigme Active</div>
            <p class="mt-2 text-sm italic text-foreground/90">
              "Je fais face à la mer, le dos tourné à la douleur ancienne. Compte les marches devant mon arche — quel nombre trouves-tu ?"
            </p>
            <input 
              placeholder="Votre réponse..." 
              class="mt-4 w-full h-11 rounded-xl bg-gaming-darker/80 border border-purple-neon/40 px-4 text-sm focus:border-purple-neon focus:shadow-purple outline-none transition-all" 
            />
            <NeonButton variant="purple" class="w-full mt-3" size="sm">
              Soumettre la Réponse
            </NeonButton>
          </div>
          <div v-else class="rounded-2xl glass p-5 text-center">
            <Lock class="h-8 w-8 text-muted-foreground/30 mx-auto mb-2" />
            <p class="text-xs text-muted-foreground">Atteignez un lieu pour débloquer une énigme</p>
          </div>
        </aside>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
