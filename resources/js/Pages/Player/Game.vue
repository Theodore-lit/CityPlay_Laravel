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
import axios from 'axios';
import { cn } from '@/lib/utils';

const props = defineProps({
    city: Object,
    locations: Array,
    gameMode: String,
    currentSession: Object,
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
const teamMembers = ref([]);
const notifications = ref([]);
const pollingInterval = ref(null);

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

                // Envoyer la position au serveur (via Axios pour éviter les erreurs Inertia)
                axios.post(route('player.location.update'), { lat: latitude, lng: longitude })
                    .catch(err => console.error("Erreur mise à jour position:", err));

                if (accuracy > 150) {
                    console.warn(`Précision GPS faible : ${accuracy}m. L'expérience de jeu pourrait être dégradée.`);
                }
            },
            (error) => {
                console.error("Erreur GPS :", error);
                showGameToast("Erreur de localisation. Vérifiez vos paramètres GPS.", "error");
            },
            {
                enableHighAccuracy: true,
                timeout: 27000,
                maximumAge: 30000,
            }
        );
    } else {
        showGameToast("La géolocalisation n'est pas supportée par votre navigateur.", "error");
    }
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

    // Polling pour les notifications et les positions d'équipe
    pollingInterval.value = setInterval(() => {
        fetchNotifications();
        if (props.currentSession?.team_id) {
            fetchTeamLocations();
        }
    }, 5000);

    // Forcer un rafraîchissement initial une fois le layout posé et les animations terminées
    setTimeout(() => {
        if (mapRef.value && typeof mapRef.value.refreshSize === 'function') {
            mapRef.value.refreshSize();
        }
    }, 500);
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
    if (pollingInterval.value) clearInterval(pollingInterval.value);
});

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('player.notifications'));
        notifications.value = response.data;
    } catch (e) {
        console.error("Erreur notifications:", e);
    }
};

const fetchTeamLocations = async () => {
    try {
        const response = await axios.get(route('player.team.locations', props.currentSession?.team_id));
        teamMembers.value = response.data;
    } catch (e) {
        console.error("Erreur positions équipe:", e);
    }
};

const markAsRead = async (notif) => {
    try {
        await axios.post(route('player.notifications.read', notif.id));
        notifications.value = notifications.value.filter(n => n.id !== notif.id);
        if (notif.data?.action_url) {
            window.location.href = notif.data.action_url;
        }
    } catch (e) {
        console.error("Erreur lecture notification:", e);
    }
};
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
              :team-members="teamMembers"
              @location-reached="isNearLocation = true"
            />
          </div>

          <!-- Notifications Overlay -->
          <div v-if="notifications.length > 0" class="absolute top-4 left-4 right-4 z-50 pointer-events-none">
            <div v-for="n in notifications" :key="n.id" class="mb-2 pointer-events-auto">
              <div class="glass-strong border-electric/30 p-4 rounded-2xl flex items-center justify-between shadow-2xl animate-bounce-soft">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-electric/20 flex items-center justify-center">
                    <Zap class="h-5 w-5 text-electric" />
                  </div>
                  <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-electric">ALERTE ESCOUADE</div>
                    <div class="text-xs font-bold text-white">{{ n.message }}</div>
                  </div>
                </div>
                <button @click="markAsRead(n)" class="h-8 w-8 rounded-lg bg-electric text-black flex items-center justify-center">
                  <Play class="h-4 w-4" />
                </button>
              </div>
            </div>
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
              <div class="glass-strong rounded-[1.2rem] md:rounded-[2rem] border border-primary/30 p-3 md:p-5 backdrop-blur-xl shadow-2xl animate-fade-up">
                <div class="flex items-center gap-3 md:gap-4 mb-2 md:mb-3">
                  <div class="h-8 md:h-10 w-8 md:w-10 rounded-lg md:rounded-xl bg-primary/10 border border-primary/20 grid place-items-center text-primary shrink-0">
                    <Brain class="h-4 md:h-5 w-4 md:w-5 animate-pulse-soft" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-[6px] md:text-[8px] text-primary font-black uppercase tracking-[0.3em]">Mission en cours</div>
                    <h3 class="font-display text-sm md:text-lg text-white uppercase tracking-tight truncate">{{ currentTarget?.display_name }}</h3>
                  </div>
                  <div class="flex items-center gap-1 md:gap-2 shrink-0">
                    <div v-for="i in 5" :key="i" :class="cn('h-1.5 w-3 md:w-5 rounded-full transition-all duration-500', i/5 <= proximityScore/100 ? 'bg-primary shadow-[0_0_10px_rgba(34,211,238,0.8)]' : 'bg-white/10')"></div>
                  </div>
                </div>

                <p class="text-[10px] md:text-xs text-white/60 italic mb-3 md:mb-4 line-clamp-1 md:line-clamp-2 leading-relaxed">
                  "{{ displayEnigma }}"
                </p>

                <div class="flex gap-2 md:gap-3">
                  <NeonButton
                    variant="outline"
                    size="sm"
                    class="flex-1 rounded-lg md:rounded-xl text-[9px] md:text-[10px] h-9 md:h-11 border-primary/20 text-primary"
                    @click="selectLocation(currentTarget)"
                  >
                    VOIR L'ÉNIGME
                  </NeonButton>
                  <NeonButton
                    size="sm"
                    class="flex-1 rounded-lg md:rounded-xl text-[9px] md:text-[10px] h-9 md:h-11 group relative overflow-hidden bg-gradient-adventure border-none"
                    @click="validatePosition"
                  >
                    <div v-if="isNearLocation" class="absolute inset-0 bg-white/20 animate-shimmer"></div>
                    <div v-if="isNearLocation" class="flex items-center justify-center gap-1">
                        <CheckCircle2 class="h-3 w-3" /> DÉPLOYER
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
          <div class="rounded-2xl glass-strong p-4 md:p-5 border border-primary/20">
            <div class="text-[10px] text-primary font-display tracking-widest uppercase">Quête Actuelle</div>
            <h2 class="font-display text-lg md:text-xl mt-1 text-white">Exploration de {{ cityData?.name }}</h2>
            <div class="mt-4 h-1.5 md:h-2 rounded-full bg-white/5 overflow-hidden p-[1px]">
              <div
                class="h-full bg-gradient-adventure rounded-full transition-all duration-1000"
                :style="{ width: `${(locations.filter(l => l.is_discovered).length / locations.length) * 100}%` }"
              />
            </div>
            <div class="mt-2 flex justify-between text-[10px] md:text-xs font-bold uppercase tracking-widest">
              <span class="text-white/40">{{ locations.filter(l => l.is_discovered).length }} sur {{ locations.length }} missions</span>
              <span class="text-primary">+{{ locations.filter(l => l.is_discovered).length * 250 }} XP</span>
            </div>
          </div>

          <!-- LISTE DES LIEUX -->
          <div class="rounded-2xl glass-strong p-4 md:p-5 max-h-[30vh] md:max-h-[50vh] overflow-y-auto custom-scrollbar border border-white/5">
            <div class="flex items-center justify-between mb-4 sticky top-0 bg-inherit z-10">
              <h3 class="font-display text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 text-white/60">
                <Target class="h-4 w-4 text-primary" />OBJECTIFS DE ZONE
              </h3>
            </div>
            <div class="space-y-3">
              <div
                v-for="loc in locations"
                :key="loc.id"
                class="flex items-center gap-3 p-3 rounded-2xl hover:bg-primary/5 transition-all cursor-pointer group/loc border border-transparent hover:border-primary/20"
                @click="selectLocation(loc)"
              >
                <div :class="cn(
                    'h-12 w-12 rounded-xl border flex items-center justify-center shrink-0 transition-all duration-500 overflow-hidden',
                    loc.is_discovered ? 'bg-primary/10 border-primary/30' : (loc.is_current_target ? 'bg-warning/10 border-warning/30 scale-110 shadow-lg shadow-warning/20' : 'bg-white/5 border-white/10')
                )">
                  <img v-if="loc.is_discovered && loc.images && loc.images[0]" :src="loc.images[0]" class="h-full w-full object-cover" />
                  <MapPin v-else-if="loc.is_discovered" class="h-6 w-6 text-primary" />
                  <Brain v-else-if="loc.is_current_target" class="h-6 w-6 text-warning animate-pulse-soft" />
                  <Lock v-else class="h-5 w-5 text-white/10" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-black uppercase tracking-tight truncate flex items-center gap-2 text-white/90">
                    {{ loc.display_name }}
                    <div v-if="loc.is_discovered" class="flex gap-0.5">
                        <Star v-for="s in 3" :key="s" :class="cn('h-2.5 w-2.5', s <= (loc.stars || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-white/5')" />
                    </div>
                  </div>
                  <div class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">
                    {{ loc.is_discovered ? loc.category : 'Zone Cryptée' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ÉNIGME ACTIVE -->
          <div v-if="currentSession?.current_enigma_id" class="rounded-2xl glass-strong p-5 animate-fade-up border border-primary/30 shadow-[0_0_20px_rgba(34,211,238,0.1)]">
            <div class="text-[10px] text-primary font-black uppercase tracking-[0.2em]">Objectif Actuel</div>
            <div class="mt-2 text-[10px] text-white/40 uppercase tracking-widest flex items-center gap-1 font-bold">
                <Target class="h-3.5 w-3.5 text-primary" />
                {{ locations.find(l => l.id === currentSession.current_location_id)?.display_name }}
            </div>
            <p class="mt-4 text-xs italic text-white/80 leading-relaxed line-clamp-3">
              "{{ locations.find(l => l.id === currentSession.current_location_id)?.enigmas?.find(e => e.id === currentSession.current_enigma_id)?.content || 'Résolvez l\'énigme pour avancer.' }}"
            </p>
            <div class="mt-5">
                <NeonButton size="sm" class="w-full rounded-xl bg-gradient-adventure border-none text-[10px] font-black uppercase tracking-widest" @click="selectLocation(locations.find(l => l.id === currentSession.current_location_id))">
                    OUVRIR LE DOSSIER
                </NeonButton>
            </div>
          </div>
          <div v-else class="rounded-[2rem] glass-strong p-8 text-center border border-primary/20">
            <div class="h-16 w-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <Trophy class="h-10 w-10 text-primary animate-bounce-soft" />
            </div>
            <p class="text-[10px] text-primary font-black uppercase tracking-[0.3em] mb-2">Opération Réussie</p>
            <h3 class="font-display text-lg text-white uppercase italic font-black">Légende Établie</h3>
          </div>
        </aside>
      </div>
    </div>
    <MobileTabBar />

    <!-- MODAL ÉNIGME -->
    <Modal :show="showRiddleModal" @close="showRiddleModal = false">
        <div class="p-8 bg-gaming-darker border border-primary/20 rounded-[2.5rem] overflow-hidden relative max-w-lg mx-auto shadow-[0_0_50px_rgba(34,211,238,0.15)]">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="relative z-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <div class="text-[10px] text-primary font-black uppercase tracking-[0.3em] mb-2">
                            {{ riddleType === 'unlock' ? 'DÉBLOCAGE DE ZONE' : 'MISSION SUR SITE' }}
                        </div>
                        <h2 class="font-display text-3xl text-white uppercase italic font-black leading-none">{{ selectedLocation?.display_name }}</h2>
                    </div>
                    <div class="h-14 w-14 rounded-2xl bg-primary/10 border border-primary/20 grid place-items-center text-primary shadow-2xl shadow-primary/20">
                        <Brain v-if="riddleType === 'unlock'" class="h-8 w-8" />
                        <MapPin v-else class="h-8 w-8" />
                    </div>
                </div>

                <!-- SELECTION DIFFICULTÉ (SEULEMENT POUR DÉBLOCAGE) -->
                <div v-if="riddleType === 'unlock' && !selectedDifficulty" class="space-y-4">
                    <p class="text-sm text-white/50 mb-6 font-medium">Choisissez la complexité de l'énigme pour localiser ce lieu sur la carte :</p>
                    <button
                        @click="startRiddle('easy')"
                        class="w-full p-5 rounded-3xl bg-white/5 border border-white/10 hover:border-success/40 hover:bg-success/5 transition-all text-left group flex items-center justify-between"
                    >
                        <div>
                            <span class="font-black uppercase tracking-tight text-white group-hover:text-success transition-colors">Niveau Facile</span>
                            <p class="text-[10px] text-white/40 mt-1 uppercase font-bold tracking-widest">+100 XP • Localisation immédiate</p>
                        </div>
                        <span class="text-[10px] px-3 py-1 rounded-full bg-success/20 text-success font-black tracking-widest">LÉGER</span>
                    </button>
                    <button
                        @click="startRiddle('medium')"
                        class="w-full p-5 rounded-3xl bg-white/5 border border-white/10 hover:border-warning/40 hover:bg-warning/5 transition-all text-left group flex items-center justify-between"
                    >
                        <div>
                            <span class="font-black uppercase tracking-tight text-white group-hover:text-warning transition-colors">Niveau Moyen</span>
                            <p class="text-[10px] text-white/40 mt-1 uppercase font-bold tracking-widest">+250 XP • Localisation précise</p>
                        </div>
                        <span class="text-[10px] px-3 py-1 rounded-full bg-warning/20 text-warning font-black tracking-widest">TACTIQUE</span>
                    </button>
                    <button
                        @click="startRiddle('hard')"
                        class="w-full p-5 rounded-3xl bg-white/5 border border-white/10 hover:border-primary/40 hover:bg-primary/5 transition-all text-left group flex items-center justify-between"
                    >
                        <div>
                            <span class="font-black uppercase tracking-tight text-white group-hover:text-primary transition-colors">Niveau Difficile</span>
                            <p class="text-[10px] text-white/40 mt-1 uppercase font-bold tracking-widest">+500 XP • Bonus d'exploration</p>
                        </div>
                        <span class="text-[10px] px-3 py-1 rounded-full bg-primary/20 text-primary font-black tracking-widest">LÉGENDAIRE</span>
                    </button>
                </div>

                <!-- QUESTIONNAIRE ACTIF (SITE) -->
                <div v-else-if="isQuestionnaireActive && currentRiddle?.questions?.[currentQuestionIndex]" class="space-y-6 animate-fade-up">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[10px] text-primary font-black uppercase tracking-[0.2em]">SÉQUENCE {{ currentQuestionIndex + 1 }} / {{ currentRiddle.questions.length }}</div>
                        <div class="flex gap-1.5">
                            <div v-for="i in currentRiddle.questions.length" :key="i" :class="cn('h-1.5 w-6 rounded-full transition-all duration-500', i-1 <= currentQuestionIndex ? 'bg-primary shadow-[0_0_10px_rgba(34,211,238,0.5)]' : 'bg-white/10')"></div>
                        </div>
                    </div>

                    <div class="p-8 rounded-[2rem] bg-white/5 border border-white/10 text-xl text-white font-display italic leading-tight shadow-inner">
                        {{ currentRiddle.questions[currentQuestionIndex].question_text }}
                    </div>

                    <div class="grid gap-4">
                        <button
                            v-for="(option, idx) in currentRiddle.questions[currentQuestionIndex].options"
                            :key="idx"
                            @click="selectQuestionOption(option)"
                            :class="cn(
                                'w-full p-5 rounded-[1.5rem] border text-left transition-all duration-300 group flex items-center justify-between',
                                questionnaireAnswers[currentQuestionIndex] === option
                                    ? (option.is_correct ? 'bg-success/20 border-success shadow-[0_0_20px_rgba(34,197,94,0.3)]' : 'bg-destructive/20 border-destructive shadow-[0_0_20px_rgba(239,68,68,0.3)]')
                                    : 'bg-white/5 border-white/10 hover:border-primary hover:bg-primary/5'
                            )"
                        >
                            <span class="text-sm font-black uppercase tracking-tight text-white/80 group-hover:text-white">{{ option.option_text }}</span>
                            <div v-if="questionnaireAnswers[currentQuestionIndex] === option" class="h-6 w-6 rounded-full grid place-items-center">
                                <CheckCircle2 v-if="option.is_correct" class="h-5 w-5 text-success" />
                                <XCircle v-else class="h-5 w-5 text-destructive" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- L'ÉNIGME CLASSIQUE (UNLOCK) -->
                <div v-else-if="currentRiddle" class="space-y-6 animate-fade-up">
                    <div v-if="currentRiddle.image_path" class="rounded-[2rem] overflow-hidden border border-white/10 mb-6 aspect-video bg-gaming-darker shadow-2xl">
                        <img :src="currentRiddle.image_path" class="w-full h-full object-cover" alt="Indice visuel" />
                    </div>

                    <div class="p-8 rounded-[2rem] bg-white/5 border border-white/10 italic text-white/90 leading-relaxed relative shadow-inner">
                        <Info class="absolute -top-4 -right-4 h-12 w-12 text-primary/10" />
                        "{{ currentRiddle.content }}"
                    </div>

                    <div class="relative">
                        <label class="text-[10px] text-primary font-black uppercase tracking-[0.3em] mb-3 block">RÉPONSE REQUISE</label>
                        <input
                            v-model="riddleAnswer"
                            @keyup.enter="submitRiddle"
                            placeholder="DÉCHIFFRER ICI..."
                            class="w-full h-16 rounded-2xl bg-gaming-darker border border-primary/30 px-8 text-white placeholder:text-white/20 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all font-display uppercase tracking-widest shadow-2xl"
                        />
                        <button @click="submitRiddle" class="absolute right-3 top-9 h-10 w-10 rounded-xl bg-primary text-black flex items-center justify-center hover:scale-110 transition-transform">
                            <ArrowRight class="h-5 w-5" />
                        </button>
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
