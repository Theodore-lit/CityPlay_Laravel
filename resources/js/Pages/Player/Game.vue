<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import MapComponent from '@/Components/MapComponent.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import {
  MapPin, QrCode, Navigation, Sparkles, Lock, Target, Zap,
  ChevronLeft, Star, Heart, Clock, Play, Info, CheckCircle2, Trophy, Brain
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    city: Object,
    locations: Array,
    gameMode: String,
});

// --- États Réactifs ---
const cityData = computed(() => props.city);
const activeTab = ref('map');
const mapRef = ref(null);
const userPosition = ref(null);
const distanceToClosest = ref(null);
const closestLocation = ref(null);
const watchId = ref(null);
const isNearLocation = ref(false);

// Nouveaux états pour le gameplay
const selectedLocation = ref(null);
const showRiddleModal = ref(false);
const riddleType = ref(null); // 'unlock' or 'site'
const selectedDifficulty = ref(null);
const riddleAnswer = ref('');
const showSuccessModal = ref(false);
const earnedStars = ref(0);
const earnedXp = ref(0);
const currentRiddle = ref(null);
const usedHints = ref(0);

const selectLocation = (loc) => {
    selectedLocation.value = loc;
    if (!loc.is_discovered) {
        // Logique de déblocage par énigme
        riddleType.value = 'unlock';
        showRiddleModal.value = true;
    } else {
        activeTab.value = 'map';
    }
};

const startRiddle = (difficulty) => {
    selectedDifficulty.value = difficulty;
    // On prend une énigme de la bonne difficulté et non spécifique au site pour débloquer
    currentRiddle.value = selectedLocation.value.enigmas.find(e =>
        e.difficulty === difficulty && !e.is_site_specific
    ) || selectedLocation.value.enigmas[0];

    riddleAnswer.value = '';
    usedHints.value = 0;
};

const submitRiddle = () => {
    if (riddleAnswer.value.toLowerCase() === currentRiddle.value.answer.toLowerCase()) {
        if (riddleType.value === 'unlock') {
            handleUnlockSuccess();
        } else {
            handleSiteSuccess();
        }
    } else {
        alert("Réponse incorrecte. Réessayez !");
    }
};

const handleUnlockSuccess = () => {
    // Dans un vrai projet, on ferait un appel API ici
    router.post(route('player.unlock-location', selectedLocation.value.id), {
        difficulty: selectedDifficulty.value
    }, {
        onSuccess: () => {
            showRiddleModal.value = false;
            alert("Lieu débloqué sur la carte ! Direction l'objectif.");
        }
    });
};

const handleSiteSuccess = () => {
    const stars = 3 - usedHints.value;
    earnedStars.value = Math.max(1, stars);
    earnedXp.value = currentRiddle.value.reward_coins * 10; // Exemple

    router.post(route('player.complete-location', selectedLocation.value.id), {
        stars: earnedStars.value,
        xp: earnedXp.value
    }, {
        onSuccess: () => {
            showRiddleModal.value = false;
            showSuccessModal.value = true;
        }
    });
};

/**
 * Surveille le changement d'onglet pour rafraîchir la carte si elle passe au premier plan
 */
watch(activeTab, (newTab) => {
    if (newTab === 'map') {
        nextTick(() => {
            // Appel de la méthode exposée du MapComponent pour forcer l'affichage des tuiles
            if (mapRef.value && typeof mapRef.value.refreshSize === 'function') {
                mapRef.value.refreshSize();
            }
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
    if (!props.city || !props.city.locations) return;

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
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
};

const validatePosition = () => {
    if (isNearLocation.value && closestLocation.value) {
        selectedLocation.value = closestLocation.value;
        riddleType.value = 'site';
        // On sélectionne l'énigme spécifique au site si elle existe
        currentRiddle.value = selectedLocation.value.enigmas.find(e => e.is_site_specific) || selectedLocation.value.enigmas[0];
        riddleAnswer.value = '';
        usedHints.value = 0;
        showRiddleModal.value = true;
    } else {
        alert("Vous êtes encore trop loin du lieu. Rapprochez-vous !");
    }
};

onMounted(() => {
    startTracking();
    // Forcer un rafraîchissement initial une fois le layout posé et les animations terminées
    setTimeout(() => {
        if (mapRef.value && typeof mapRef.value.refreshSize === 'function') {
            mapRef.value.refreshSize();
        }
    }, 500);
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});
</script>

<template>
  <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

  <SiteLayout hideFooter>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 pb-28 md:pb-12">
      <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <!-- ZONE CARTE -->
        <div class="relative h-[60vh] lg:h-[78vh] rounded-3xl glass-strong overflow-hidden border border-electric/20 shadow-neon">
          <div class="absolute inset-0 z-0">
            <MapComponent
              ref="mapRef"
              :locations="locations"
              :userPosition="userPosition"
            />
          </div>

          <!-- Overlay de la carte -->
          <div class="absolute top-4 left-4 right-4 flex items-center justify-between z-10 pointer-events-none">
            <div class="glass-strong px-4 py-2 rounded-xl flex items-center gap-2 pointer-events-auto">
              <Navigation class="h-4 w-4 text-electric animate-pulse" />
              <span class="text-xs font-display tracking-widest">{{ cityData?.name?.toUpperCase() || 'VILLE' }} • GPS ACTIF</span>
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
            <h2 class="font-display text-xl mt-1">Exploration de {{ cityData?.name }}</h2>
            <div class="mt-4 h-2 rounded-full bg-gaming-darker overflow-hidden">
              <div
                class="h-full bg-gradient-electric transition-all duration-1000"
                :style="{ width: `${(locations.filter(l => l.is_discovered).length / locations.length) * 100}%` }"
              />
            </div>
            <div class="mt-2 flex justify-between text-xs">
              <span class="text-muted-foreground">{{ locations.filter(l => l.is_discovered).length }} sur {{ locations.length }} lieux</span>
              <span class="text-electric">+{{ locations.filter(l => l.is_discovered).length * 250 }} XP</span>
            </div>
          </div>

          <!-- LISTE DES LIEUX -->
          <div class="rounded-2xl glass p-5 max-h-[40vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-3 sticky top-0 bg-inherit z-10">
              <h3 class="font-display text-sm flex items-center gap-2">
                <Target class="h-4 w-4 text-electric" />VOTRE PROGRESSION
              </h3>
            </div>
            <div class="space-y-3">
              <div
                v-for="loc in locations"
                :key="loc.id"
                class="flex items-center gap-3 p-2 rounded-xl hover:bg-electric/5 transition cursor-pointer group/loc"
                @click="selectLocation(loc)"
              >
                <div :class="cn(
                    'h-12 w-12 rounded-lg border flex items-center justify-center shrink-0 transition-colors',
                    loc.is_discovered ? 'bg-electric/10 border-electric/20' : 'bg-gaming-darker border-white/5'
                )">
                  <MapPin v-if="loc.is_discovered" class="h-6 w-6 text-electric" />
                  <Lock v-else class="h-5 w-5 text-muted-foreground/30" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-bold truncate flex items-center gap-2 text-foreground">
                    {{ loc.display_name }}
                    <div v-if="loc.is_discovered" class="flex gap-0.5">
                        <Star v-for="s in 3" :key="s" :class="cn('h-2.5 w-2.5', s <= (loc.stars || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-white/10')" />
                    </div>
                  </div>
                  <div class="text-[10px] text-muted-foreground uppercase tracking-widest">
                    {{ loc.is_discovered ? loc.category : 'Zone inconnue' }}
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

    <!-- MODAL ÉNIGME -->
    <Modal :show="showRiddleModal" @close="showRiddleModal = false">
        <div class="p-8 bg-gaming-darker border border-electric/20 rounded-[2.5rem] overflow-hidden relative max-w-lg mx-auto">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="text-[10px] text-electric font-display tracking-widest uppercase mb-1">
                            {{ riddleType === 'unlock' ? 'DÉBLOCAGE DE ZONE' : 'MISSION SUR SITE' }}
                        </div>
                        <h2 class="font-display text-2xl text-foreground">{{ selectedLocation?.display_name }}</h2>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-electric/10 border border-electric/20 grid place-items-center text-electric">
                        <Brain v-if="riddleType === 'unlock'" class="h-6 w-6" />
                        <MapPin v-else class="h-6 w-6" />
                    </div>
                </div>

                <!-- SELECTION DIFFICULTÉ (SEULEMENT POUR DÉBLOCAGE) -->
                <div v-if="riddleType === 'unlock' && !selectedDifficulty" class="space-y-4">
                    <p class="text-sm text-muted-foreground mb-6">Choisissez la complexité de l'énigme pour localiser ce lieu sur la carte :</p>
                    <button
                        @click="startRiddle('easy')"
                        class="w-full p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-success/40 hover:bg-success/5 transition-all text-left group"
                    >
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-foreground group-hover:text-success transition-colors">Niveau Facile</span>
                            <span class="text-[10px] px-2 py-1 rounded bg-success/20 text-success font-black">LÉGER</span>
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-1">+100 XP • Localisation immédiate</p>
                    </button>
                    <button
                        @click="startRiddle('medium')"
                        class="w-full p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-warning/40 hover:bg-warning/5 transition-all text-left group"
                    >
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-foreground group-hover:text-warning transition-colors">Niveau Moyen</span>
                            <span class="text-[10px] px-2 py-1 rounded bg-warning/20 text-warning font-black">TACTIQUE</span>
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-1">+250 XP • Localisation précise</p>
                    </button>
                    <button
                        @click="startRiddle('hard')"
                        class="w-full p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-destructive/40 hover:bg-destructive/5 transition-all text-left group"
                    >
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-foreground group-hover:text-destructive transition-colors">Niveau Difficile</span>
                            <span class="text-[10px] px-2 py-1 rounded bg-destructive/20 text-destructive font-black">LÉGENDAIRE</span>
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-1">+500 XP • Bonus d'exploration</p>
                    </button>
                </div>

                <!-- L'ÉNIGME -->
                <div v-else-if="currentRiddle" class="space-y-6 animate-fade-up">
                    <div v-if="currentRiddle.image_path" class="rounded-2xl overflow-hidden border border-white/10 mb-4 aspect-video bg-gaming-darker">
                        <img :src="currentRiddle.image_path" class="w-full h-full object-cover" alt="Indice visuel" />
                    </div>

                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 italic text-foreground/90 leading-relaxed relative">
                        <Info class="absolute -top-3 -right-3 h-8 w-8 text-electric/20" />
                        "{{ currentRiddle.content }}"
                    </div>

                    <div>
                        <label class="text-[10px] text-muted-foreground uppercase tracking-[0.2em] font-black mb-2 block">VOTRE RÉPONSE</label>
                        <input
                            v-model="riddleAnswer"
                            @keyup.enter="submitRiddle"
                            placeholder="Saisissez la solution..."
                            class="w-full h-14 rounded-2xl bg-gaming-darker border border-electric/30 px-6 text-foreground placeholder:text-muted-foreground/40 focus:border-electric focus:ring-4 focus:ring-electric/10 outline-none transition-all font-display"
                        />
                    </div>

                    <div class="flex gap-3">
                        <NeonButton variant="outline" class="flex-1" @click="usedHints++" :disabled="usedHints >= 2">
                            Indice ({{ 2 - usedHints }})
                        </NeonButton>
                        <NeonButton class="flex-1" @click="submitRiddle">
                            Soumettre
                        </NeonButton>
                    </div>

                    <p v-if="usedHints > 0" class="text-xs text-warning animate-pulse bg-warning/5 p-3 rounded-xl border border-warning/20">
                        💡 {{ currentRiddle.indices?.[usedHints - 1] || 'Observez bien les détails historiques.' }}
                    </p>
                </div>
            </div>
        </div>
    </Modal>

    <!-- MODAL SUCCÈS -->
    <Modal :show="showSuccessModal" @close="showSuccessModal = false">
        <div class="p-10 bg-gaming-darker border border-electric/20 rounded-[3rem] overflow-hidden relative text-center max-w-sm mx-auto">
            <div class="absolute inset-0 bg-gradient-to-b from-electric/10 to-transparent pointer-events-none" />

            <div class="relative z-10">
                <div class="h-24 w-24 rounded-3xl bg-gradient-electric mx-auto grid place-items-center shadow-neon mb-6 animate-bounce">
                    <Trophy class="h-12 w-12 text-white" />
                </div>

                <h2 class="font-display text-3xl text-foreground mb-2">MISSION RÉUSSIE !</h2>
                <p class="text-muted-foreground text-sm mb-8">Vous avez percé les mystères de ce lieu.</p>

                <div class="flex justify-center gap-2 mb-8">
                    <Star
                        v-for="s in 3"
                        :key="s"
                        :class="cn(
                            'h-10 w-10 transition-all duration-700',
                            s <= earnedStars ? 'text-yellow-400 fill-yellow-400 scale-110' : 'text-white/5'
                        )"
                        :style="{ transitionDelay: `${s * 200}ms` }"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-muted-foreground uppercase mb-1">XP GAGNÉS</div>
                        <div class="text-xl font-display text-electric">+{{ earnedXp }}</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-muted-foreground uppercase mb-1">STATUT</div>
                        <div class="text-xl font-display text-success">EXPERT</div>
                    </div>
                </div>

                <NeonButton class="w-full" @click="showSuccessModal = false">
                    Continuer l'Exploration
                </NeonButton>
            </div>
        </div>
    </Modal>
  </SiteLayout>
</template>
