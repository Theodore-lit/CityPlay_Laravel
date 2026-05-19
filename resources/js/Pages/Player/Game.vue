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
    currentSession: Object,
    initialTeamPositions: Array,
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
const teamMembers = ref(props.initialTeamPositions || []); // Initialisation avec les positions déjà connues

const nextTarget = computed(() => {
    if (!props.currentSession || !props.currentSession.discovery_sequence) return null;
    const sequence = props.currentSession.discovery_sequence;
    const currentIndex = sequence.indexOf(props.currentSession.current_location_id);

    if (currentIndex !== -1 && sequence[currentIndex + 1]) {
        return props.locations.find(l => l.id === sequence[currentIndex + 1]);
    }
    return null;
});

const activeEnigma = computed(() => {
    if (!props.currentSession || !props.locations) return null;

    // Si on est sur place (navigation résolue), on pourrait vouloir montrer l'énigme suivante
    // Mais pour l'instant, on affiche l'énigme active de la session
    const loc = props.locations.find(l => l.id === props.currentSession.current_location_id);
    if (!loc) return null;

    const enigma = loc.enigmas?.find(e => e.id === props.currentSession.current_enigma_id);
    return enigma;
});

const displayEnigma = computed(() => {
    // Si on a déjà débloqué le lieu actuel (is_site_specific actif ou déjà découvert)
    // On montre l'énigme du LIEU SUIVANT dans le HUD pour guider le joueur
    if (activeEnigma.value?.is_site_specific && nextTarget.value) {
        return nextTarget.value.enigmas?.find(e => !e.is_site_specific)?.content || "Suivez le radar vers la prochaine zone.";
    }
    return activeEnigma.value?.content || "Analysez le radar pour localiser le prochain secret.";
});
const currentTarget = computed(() => {
    return props.locations.find(l => l.is_current_target);
});

const proximityScore = computed(() => {
    if (!distanceToClosest.value) return 0;
    const dist = parseFloat(distanceToClosest.value.replace(/[^0-9.]/g, ''));
    const isKm = distanceToClosest.value.includes('km');
    const meters = isKm ? dist * 1000 : dist;

    // On considère que 500m est le début de la zone "chaude"
    if (meters > 500) return 0;
    return Math.round(((500 - meters) / 500) * 100);
});

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

// Questionnaire state
const currentQuestionIndex = ref(0);
const questionnaireAnswers = ref([]);
const isQuestionnaireActive = ref(false);

const selectLocation = (loc) => {
    selectedLocation.value = loc;

    if (loc.is_discovered) {
        activeTab.value = 'map';
        return;
    }

    if (loc.is_current_target) {
        // On vérifie si on a déjà résolu l'énigme de navigation
        const currentEnigma = props.city.locations.find(l => l.id === loc.id).enigmas.find(e => e.id === props.currentSession?.current_enigma_id);

        if (currentEnigma && !currentEnigma.is_site_specific) {
            riddleType.value = 'unlock';
            isQuestionnaireActive.value = false;
            showRiddleModal.value = true;
        } else {
            // Déjà résolu l'énigme de navigation, on doit se rendre sur place
            activeTab.value = 'map';
        }
    } else {
        showGameToast("Ce lieu est encore verrouillé. Suivez l'ordre de l'aventure !", "error");
    }
};

const startRiddle = (difficulty) => {
    selectedDifficulty.value = difficulty;
    // On prend une énigme de la bonne difficulté et non spécifique au site pour débloquer
    const enigmas = selectedLocation.value.enigmas || [];
    currentRiddle.value = enigmas.find(e =>
        e.difficulty === difficulty && !e.is_site_specific
    ) || enigmas[0];

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
        showGameToast("Réponse incorrecte. Réessayez !", "error");
    }
};

const handleUnlockSuccess = () => {
    router.post(route('player.unlock-location', selectedLocation.value.id), {
        difficulty: selectedDifficulty.value,
        team_id: props.currentSession?.team_id
    }, {
        onSuccess: () => {
            showRiddleModal.value = false;
            showGameToast("Lieu débloqué ! Direction la cible.", "success");
        }
    });
};

const startQuestionnaire = () => {
    currentQuestionIndex.value = 0;
    questionnaireAnswers.value = [];
    isQuestionnaireActive.value = true;
    showRiddleModal.value = true;
};

const selectQuestionOption = (option) => {
    questionnaireAnswers.value[currentQuestionIndex.value] = option;

    setTimeout(() => {
        if (currentQuestionIndex.value < currentRiddle.value.questions.length - 1) {
            currentQuestionIndex.value++;
        } else {
            submitQuestionnaire();
        }
    }, 500);
};

const submitQuestionnaire = () => {
    const correctCount = questionnaireAnswers.value.filter(a => a.is_correct).length;
    const totalCount = currentRiddle.value.questions.length;

    if (correctCount === totalCount) {
        handleSiteSuccess();
    } else {
        showGameToast(`Vous avez ${correctCount}/${totalCount} bonnes réponses. Réessayez !`, "warning");
        currentQuestionIndex.value = 0;
        questionnaireAnswers.value = [];
    }
};

const handleSiteSuccess = () => {
    const stars = 3 - usedHints.value;
    earnedStars.value = Math.max(1, stars);
    earnedXp.value = currentRiddle.value.reward_coins * 10 || 250;

    router.post(route('player.complete-location', selectedLocation.value.id), {
        stars: earnedStars.value,
        xp: earnedXp.value,
        team_id: props.currentSession?.team_id
    }, {
        onSuccess: () => {
            showRiddleModal.value = false;
            showSuccessModal.value = true;
            isQuestionnaireActive.value = false;
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
    if (!props.city || !props.locations) return;

    // Priorité à la cible actuelle en mode aventure
    if (currentTarget.value) {
        const d = calculateDistance(lat, lng, currentTarget.value.latitude, currentTarget.value.longitude);
        closestLocation.value = currentTarget.value;
        isNearLocation.value = d <= (currentTarget.value.radius_meters || 50);
        distanceToClosest.value = d > 1000
            ? (d / 1000).toFixed(1) + 'km'
            : Math.round(d) + 'm';
        return;
    }

    let minDistance = Infinity;
    let closest = null;

    props.locations.forEach(loc => {
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
                const { latitude, longitude, accuracy } = position.coords;
                userPosition.value = { lat: latitude, lng: longitude };
                updateDistances(latitude, longitude);

                // Si on est en équipe, on met à jour notre position sur le serveur
                if (props.currentSession?.team_id) {
                    updateUserPositionOnServer(latitude, longitude);
                }

                if (accuracy > 30) {
                    console.warn(`Précision GPS faible : ${accuracy}m`);
                }
            },
            (error) => {
                console.error("Erreur GPS :", error);
                showGameToast("Erreur de localisation. Vérifiez vos paramètres GPS.", "error");
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0,
            }
        );
    } else {
        showGameToast("La géolocalisation n'est pas supportée par votre navigateur.", "error");
    }
};

const updateUserPositionOnServer = (lat, lng) => {
    router.post(route('player.update-position'), {
        lat: lat,
        lng: lng,
        team_id: props.currentSession?.team_id
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            // Le serveur renvoie la position des autres membres de l'équipe via flash
            if (page.props.flash?.teamPositions) {
                teamMembers.value = page.props.flash.teamPositions;
            }
        }
    });
};

const validatePosition = () => {
    if (isNearLocation.value && closestLocation.value) {
        if (!closestLocation.value.is_current_target && !closestLocation.value.is_discovered) {
            showGameToast("Ce n'est pas votre destination actuelle !", "error");
            return;
        }

        showGameToast("Position confirmée ! Accès à l'énigme locale...", "success");

        setTimeout(() => {
            selectedLocation.value = closestLocation.value;
            riddleType.value = 'site';

            // On cherche l'énigme de site
            const enigmas = selectedLocation.value.enigmas || [];
            currentRiddle.value = enigmas.find(e => e.is_site_specific) || enigmas[0];

            riddleAnswer.value = '';
            usedHints.value = 0;

            if (currentRiddle.value?.questions?.length > 0) {
                startQuestionnaire();
            } else {
                showRiddleModal.value = true;
            }
        }, 1000);
    } else {
        showGameToast("Signal trop faible. Rapprochez-vous du point cible.", "warning");
    }
};

// Toast simple pour le feedback
const toast = ref({ show: false, message: '', type: 'info' });
const showGameToast = (msg, type = 'info') => {
    toast.value = { show: true, message: msg, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 4000);
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

<style scoped>
.vertical-text {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    transform: rotate(180deg);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translate(-50%, -20px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px) scale(0.95);
}
</style>

<template>
  <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

  <SiteLayout hideFooter>
    <div class="mx-auto max-w-7xl px-2 sm:px-6 py-4 md:py-6 pb-28 md:pb-12 h-full flex flex-col">
      <div class="grid gap-4 md:gap-6 lg:grid-cols-12 flex-1 min-h-0">
        <!-- MAIN MAP / GAME AREA -->
        <div class="lg:col-span-8 relative rounded-[1.5rem] md:rounded-[2.5rem] overflow-hidden border border-white/20 glass-strong shadow-lg group bg-gaming-dark h-[50vh] md:h-auto min-h-[350px]">
          <div class="absolute inset-0 z-0">
            <MapComponent
              ref="mapRef"
              :locations="locations"
              :userPosition="userPosition"
              :targetLocation="currentTarget"
              :teamMembers="teamMembers"
            />
          </div>

          <!-- HUD OVERLAY -->
          <div class="absolute inset-0 pointer-events-none z-20 flex flex-col justify-between p-3 md:p-6">
            <!-- HUD TOP: Radar Status -->
            <div class="flex justify-between items-start gap-2">
              <div class="px-2 md:px-4 py-1.5 md:py-2 rounded-lg md:rounded-xl glass-strong border-electric/30 flex items-center gap-2 md:gap-3 backdrop-blur-md">
                <div class="relative">
                  <div class="h-1.5 md:h-2 w-1.5 md:w-2 rounded-full bg-electric animate-ping"></div>
                  <div class="absolute inset-0 h-1.5 md:h-2 w-1.5 md:w-2 rounded-full bg-electric shadow-neon"></div>
                </div>
                <div class="flex flex-col">
                  <span class="text-[6px] md:text-[8px] font-black uppercase tracking-[0.2em] text-electric">Signal GPS</span>
                  <span class="text-[8px] md:text-[10px] font-bold text-foreground">SYNC: STABLE</span>
                </div>
              </div>

              <div class="px-2 md:px-4 py-1.5 md:py-2 rounded-lg md:rounded-xl glass-strong border-electric/30 flex items-center gap-2 md:gap-3 backdrop-blur-md">
                <div class="flex flex-col text-right">
                  <span class="text-[6px] md:text-[8px] font-black uppercase tracking-[0.2em] text-electric">Distance Cible</span>
                  <span class="text-[8px] md:text-[10px] font-bold text-foreground">{{ distanceToClosest || 'RECHERCHE...' }}</span>
                </div>
                <Navigation class="h-3 md:h-4 w-3 md:w-4 text-electric animate-pulse" />
              </div>
            </div>

            <!-- HUD MIDDLE: Proximity Gauge (Hidden on very small mobile) -->
            <div class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 hidden sm:flex flex-col items-center gap-4">
              <div class="h-32 md:h-48 w-1 md:w-1.5 bg-white/5 rounded-full overflow-hidden border border-white/10 relative">
                <div
                  class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-electric via-cyan-400 to-white transition-all duration-500 shadow-neon"
                  :style="{ height: `${proximityScore}%` }"
                ></div>
              </div>
              <span class="text-[6px] md:text-[8px] font-black vertical-text tracking-widest text-electric/60 uppercase">Proximité</span>
            </div>

            <!-- HUD BOTTOM: Mission Brief -->
            <div class="relative pointer-events-auto w-full max-w-2xl mx-auto">
              <div class="glass-strong rounded-[1.2rem] md:rounded-[2rem] border border-electric/30 p-3 md:p-5 backdrop-blur-xl shadow-2xl animate-fade-up">
                <div class="flex items-center gap-3 md:gap-4 mb-2 md:mb-3">
                  <div class="h-8 md:h-10 w-8 md:w-10 rounded-lg md:rounded-xl bg-electric/10 border border-electric/20 grid place-items-center text-electric shrink-0">
                    <Brain class="h-4 md:h-5 w-4 md:w-5 animate-pulse-soft" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-[6px] md:text-[8px] text-electric font-black uppercase tracking-[0.3em]">Mission en cours</div>
                    <h3 class="font-display text-sm md:text-lg text-foreground uppercase tracking-tight truncate">{{ currentTarget?.display_name }}</h3>
                  </div>
                  <div class="flex items-center gap-1 md:gap-2 shrink-0">
                    <div v-for="i in 5" :key="i" :class="cn('h-1 w-2 md:w-4 rounded-full transition-colors', i/5 <= proximityScore/100 ? 'bg-electric shadow-neon' : 'bg-white/10')"></div>
                  </div>
                </div>

                <p class="text-[10px] md:text-xs text-muted-foreground italic mb-3 md:mb-4 line-clamp-1 md:line-clamp-2 leading-relaxed">
                  "{{ displayEnigma }}"
                </p>

                <div class="flex gap-2 md:gap-3">
                  <NeonButton
                    variant="outline"
                    size="sm"
                    class="flex-1 rounded-lg md:rounded-xl text-[9px] md:text-[10px] h-9 md:h-11"
                    @click="selectLocation(currentTarget)"
                  >
                    VOIR L'ÉNIGME
                  </NeonButton>
                  <NeonButton
                    size="sm"
                    class="flex-1 rounded-lg md:rounded-xl text-[9px] md:text-[10px] h-9 md:h-11 group relative overflow-hidden"
                    @click="validatePosition"
                  >
                    <div v-if="isNearLocation" class="absolute inset-0 bg-white/20 animate-shimmer"></div>
                    <div v-if="isNearLocation" class="flex items-center justify-center gap-1">
                        <CheckCircle2 class="h-3 w-3" /> PRÊT
                    </div>
                    <div v-else class="flex items-center justify-center gap-1">
                        <Navigation class="h-3 w-3 opacity-50" /> VÉRIFIER
                    </div>
                  </NeonButton>
                </div>
              </div>
            </div>
          </div>

          <!-- TOAST NOTIFICATION -->
          <Transition name="toast">
            <div v-if="toast.show"
                 :class="cn(
                    'absolute top-20 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl border backdrop-blur-xl shadow-2xl flex items-center gap-3 min-w-[280px]',
                    toast.type === 'success' ? 'bg-success/10 border-success/30 text-success' :
                    toast.type === 'error' ? 'bg-destructive/10 border-destructive/30 text-destructive' :
                    'bg-warning/10 border-warning/30 text-warning'
                 )">
                <component :is="toast.type === 'success' ? CheckCircle2 : Info" class="h-5 w-5" />
                <span class="text-xs font-bold uppercase tracking-widest">{{ toast.message }}</span>
            </div>
          </Transition>

          <div class="absolute inset-0 pointer-events-none border-[8px] md:border-[12px] border-white/5 rounded-[1.5rem] md:rounded-[2.5rem] z-20" />
        </div>

        <!-- BARRE LATÉRALE -->
        <aside class="lg:col-span-4 space-y-4 overflow-y-auto max-h-[50vh] md:max-h-full custom-scrollbar pb-10 md:pb-0">
          <div class="rounded-2xl glass-strong p-4 md:p-5">
            <div class="text-[10px] text-electric font-display tracking-widest uppercase">Quête Actuelle</div>
            <h2 class="font-display text-lg md:text-xl mt-1">Exploration de {{ cityData?.name }}</h2>
            <div class="mt-4 h-1.5 md:h-2 rounded-full bg-gaming-darker overflow-hidden">
              <div
                class="h-full bg-gradient-electric transition-all duration-1000"
                :style="{ width: `${(locations.filter(l => l.is_discovered).length / locations.length) * 100}%` }"
              />
            </div>
            <div class="mt-2 flex justify-between text-[10px] md:text-xs">
              <span class="text-muted-foreground">{{ locations.filter(l => l.is_discovered).length }} sur {{ locations.length }} lieux</span>
              <span class="text-electric font-bold">+{{ locations.filter(l => l.is_discovered).length * 250 }} XP</span>
            </div>
          </div>

          <!-- LISTE DES LIEUX -->
          <div class="rounded-2xl glass p-4 md:p-5 max-h-[30vh] md:max-h-[50vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-3 sticky top-0 bg-inherit z-10">
              <h3 class="font-display text-xs md:text-sm flex items-center gap-2">
                <Target class="h-3 md:h-4 w-3 md:w-4 text-electric" />VOTRE PROGRESSION
              </h3>
            </div>
            <div class="space-y-2 md:space-y-3">
              <div
                v-for="loc in locations"
                :key="loc.id"
                class="flex items-center gap-2 md:gap-3 p-2 rounded-xl hover:bg-electric/5 transition cursor-pointer group/loc"
                @click="selectLocation(loc)"
              >
                <div :class="cn(
                    'h-10 w-10 md:h-12 md:w-12 rounded-lg border flex items-center justify-center shrink-0 transition-colors overflow-hidden',
                    loc.is_discovered ? 'bg-electric/10 border-electric/20' : (loc.is_current_target ? 'bg-warning/10 border-warning/20' : 'bg-gaming-darker border-white/5')
                )">
                  <img v-if="loc.is_discovered && loc.images && loc.images[0]" :src="'/storage/' + loc.images[0]" class="h-full w-full object-cover" />
                  <MapPin v-else-if="loc.is_discovered" class="h-5 md:h-6 w-5 md:w-6 text-electric" />
                  <Brain v-else-if="loc.is_current_target" class="h-5 md:h-6 w-5 md:w-6 text-warning animate-pulse" />
                  <Lock v-else class="h-4 md:h-5 w-4 md:w-5 text-muted-foreground/30" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-[12px] md:text-sm font-bold truncate flex items-center gap-2 text-foreground">
                    {{ loc.display_name }}
                    <div v-if="loc.is_discovered" class="flex gap-0.5">
                        <Star v-for="s in 3" :key="s" :class="cn('h-2 md:h-2.5 w-2 md:w-2.5', s <= (loc.stars || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-white/10')" />
                    </div>
                  </div>
                  <div class="text-[8px] md:text-[10px] text-muted-foreground uppercase tracking-widest">
                    {{ loc.is_discovered ? loc.category : 'Zone inconnue' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ÉNIGME ACTIVE -->
          <div v-if="currentSession?.current_enigma_id" class="rounded-2xl glass-strong p-5 animate-fade-up border border-electric/30 shadow-neon">
            <div class="text-xs text-electric font-display tracking-widest uppercase">Objectif Actuel</div>
            <div class="mt-2 text-[10px] text-muted-foreground uppercase tracking-widest flex items-center gap-1">
                <Target class="h-3 w-3" />
                {{ locations.find(l => l.id === currentSession.current_location_id)?.display_name }}
            </div>
            <p class="mt-3 text-xs italic text-foreground/90 line-clamp-3">
              "{{ locations.find(l => l.id === currentSession.current_location_id)?.enigmas?.find(e => e.id === currentSession.current_enigma_id)?.content || 'Résolvez l\'énigme pour avancer.' }}"
            </p>
            <div class="mt-4">
                <NeonButton size="sm" class="w-full rounded-xl" @click="selectLocation(locations.find(l => l.id === currentSession.current_location_id))">
                    VOIR LA MISSION
                </NeonButton>
            </div>
          </div>
          <div v-else class="rounded-2xl glass p-5 text-center">
            <Trophy class="h-8 w-8 text-electric mx-auto mb-2 animate-bounce" />
            <p class="text-xs text-muted-foreground uppercase font-black tracking-widest">Aventure terminée !</p>
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

                <!-- QUESTIONNAIRE ACTIF (SITE) -->
                <div v-else-if="isQuestionnaireActive && currentRiddle?.questions?.[currentQuestionIndex]" class="space-y-6 animate-fade-up">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[10px] text-electric font-black uppercase tracking-widest">Question {{ currentQuestionIndex + 1 }} / {{ currentRiddle.questions.length }}</div>
                        <div class="flex gap-1">
                            <div v-for="i in currentRiddle.questions.length" :key="i" :class="cn('h-1 w-4 rounded-full transition-all', i-1 <= currentQuestionIndex ? 'bg-electric shadow-neon' : 'bg-white/10')"></div>
                        </div>
                    </div>

                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 text-lg text-white font-display leading-relaxed">
                        {{ currentRiddle.questions[currentQuestionIndex].question_text }}
                    </div>

                    <div class="grid gap-3">
                        <button
                            v-for="(option, idx) in currentRiddle.questions[currentQuestionIndex].options"
                            :key="idx"
                            @click="selectQuestionOption(option)"
                            :class="cn(
                                'w-full p-4 rounded-2xl border text-left transition-all duration-300 group flex items-center justify-between',
                                questionnaireAnswers[currentQuestionIndex] === option
                                    ? (option.is_correct ? 'bg-success/20 border-success shadow-neon-success' : 'bg-destructive/20 border-destructive shadow-neon-error')
                                    : 'bg-white/5 border-white/10 hover:border-electric hover:bg-electric/5'
                            )"
                        >
                            <span class="text-sm font-medium text-foreground group-hover:text-white">{{ option.option_text }}</span>
                            <div v-if="questionnaireAnswers[currentQuestionIndex] === option" class="h-5 w-5 rounded-full grid place-items-center">
                                <CheckCircle2 v-if="option.is_correct" class="h-4 w-4 text-success" />
                                <XCircle v-else class="h-4 w-4 text-destructive" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- L'ÉNIGME CLASSIQUE (UNLOCK) -->
                <div v-else-if="currentRiddle" class="space-y-6 animate-fade-up">
                    <div v-if="currentRiddle.image_path" class="rounded-2xl overflow-hidden border border-white/10 mb-4 aspect-video bg-gaming-darker">
                        <img :src="'/storage/' + currentRiddle.image_path" class="w-full h-full object-cover" alt="Indice visuel" />
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

                <div class="flex flex-col gap-3">
                    <NeonButton size="xl" class="w-full rounded-2xl group" @click="showSuccessModal = false">
                        {{ currentSession?.current_enigma_id ? 'CONTINUER L\'AVENTURE' : 'TERMINER LA QUÊTE' }}
                        <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
                    </NeonButton>

                    <Link v-if="!currentSession?.current_enigma_id || currentSession?.status === 'completed'" :href="route('player.cities')">
                        <NeonButton variant="outline" size="lg" class="w-full rounded-2xl">
                            REVENIR AUX VILLES
                        </NeonButton>
                    </Link>
                </div>
            </div>
        </div>
    </Modal>
  </SiteLayout>
</template>
