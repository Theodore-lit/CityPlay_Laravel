<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import MapComponent from '@/Components/MapComponent.vue';
import Modal from '@/Components/Modal.vue';
import {
  MapPin, QrCode, Navigation, Sparkles, Lock, Target, Zap,
  ChevronLeft, Star, Heart, Clock, Play, Info, CheckCircle2, Trophy, Brain,
  Pause, HelpCircle, Eye, XCircle, ArrowRight
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    city: Object,
    locations: Array,
    currentSession: Object,
    initialTeamPositions: Array,
});

// --- ÉTATS RÉACTIFS ---
const userPosition = ref(null);
const teamMembers = ref(props.initialTeamPositions || []);
const isPaused = ref(false);
const totalErrors = ref(0);
const showHint = ref(false);
const usedHints = ref(0);
const gameTime = ref(0);
const timerInterval = ref(null);
const mapRef = ref(null);

// Modal Énigme / Questionnaire
const showRiddleModal = ref(false);
const riddleType = ref('site'); // 'unlock' or 'site'
const selectedLocation = ref(null);
const currentRiddle = ref(null);
const riddleAnswer = ref('');
const isQuestionnaireActive = ref(false);
const currentQuestionIndex = ref(0);
const questionnaireAnswers = ref([]);

// Modal Succès
const showSuccessModal = ref(false);
const earnedStars = ref(3);
const earnedXp = ref(150);

// Toast
const toast = ref({ show: false, message: '', type: 'info' });

const updateGPS = () => {
    if ("geolocation" in navigator) {
        navigator.geolocation.watchPosition((position) => {
            console.log(position.coords.latitude)
            console.log(position.coords.longitude)
            userPosition.value = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
        }, (err) => {
            console.error("Erreur GPS:", err);
        }, { enableHighAccuracy: true });
    }
};


// --- COMPUTED ---
const rawDistance = computed(() => {
    if (!userPosition.value || !currentTarget.value) return null;
    return calculateDistance(
        userPosition.value.lat, userPosition.value.lng,
        currentTarget.value.latitude, currentTarget.value.longitude
    );
});

console.log(rawDistance.value)

const distanceToClosest = computed(() => {
    if (rawDistance.value === null) return '---';
    const dist = rawDistance.value;
    return dist > 1000 ? (dist/1000).toFixed(1) + 'km' : Math.round(dist) + 'm';
});

const isNearLocation = computed(() => {
    return rawDistance.value !== null && rawDistance.value < 50;
});

const proximityScore = computed(() => {
    if (rawDistance.value === null) return 0;
    const dist = rawDistance.value;
    if (dist > 1000) return 0;
    return Math.max(0, 100 - (dist / 10)); // 0m = 100%, 1000m = 0%
});

const cityData = computed(() => props.city);
const gameMode = computed(() => props.currentSession ? 'aventure' : 'classique');

const currentTarget = computed(() => {
    if (!props.locations || !props.currentSession) return null;
    return props.locations.find(l => l.id === props.currentSession.current_location_id) || props.locations[0] || null;
});

const activeEnigma = computed(() => {
    if (!currentTarget.value) return null;
    return currentTarget.value.enigmas?.find(e => e.id === props.currentSession.current_enigma_id) || currentTarget.value.enigmas?.[0];
});

const displayEnigma = computed(() => {
    return activeEnigma.value?.content || "Suivez le radar pour découvrir ce lieu mystérieux...";
});

// --- MÉTHODES ---
const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    timerInterval.value = setInterval(() => {
        if (!isPaused.value) gameTime.value++;
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const showGameToast = (message, type = 'info') => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

const togglePause = () => {
    isPaused.value = !isPaused.value;
};

const handleUseHint = () => {
    if (usePage().props.auth.user.xp < 50) {
        showGameToast("XP insuffisants pour un indice.", "error");
        return;
    }
    router.post(route('player.use-hint'), {}, {
        onSuccess: () => {
            showHint.value = true;
            usedHints.value++;
            showGameToast("Indice débloqué (-50 PX)", "success");
        }
    });
};

const verifyPosition = () => {
    if (isNearLocation.value && currentTarget.value) {
        isPaused.value = true; // Arrête le chrono dès la validation du lieu
        showGameToast("Position confirmée !", "success");
        setTimeout(() => {
            selectedLocation.value = currentTarget.value;
            riddleType.value = 'site';
            const locWithEnigmas = props.locations.find(l => l.id === selectedLocation.value.id);
            const enigmas = locWithEnigmas?.enigmas || [];
            currentRiddle.value = enigmas.find(e => e.is_site_specific) || enigmas[0];
            
            if (currentRiddle.value?.questions?.length > 0) {
                startQuestionnaire();
            } else {
                showRiddleModal.value = true;
            }
        }, 800);
    } else {
        const distMsg = distanceToClosest.value !== '---' ? ` (${distanceToClosest.value})` : '';
        showGameToast(`Signal trop faible. Rapprochez-vous du point cible.${distMsg}`, "warning");
    }
};

const startQuestionnaire = () => {
    isQuestionnaireActive.value = true;
    currentQuestionIndex.value = 0;
    questionnaireAnswers.value = [];
    totalErrors.value = 0;
    showRiddleModal.value = true;
};

const selectQuestionOption = (option) => {
    if (questionnaireAnswers.value[currentQuestionIndex.value]) return;

    questionnaireAnswers.value[currentQuestionIndex.value] = option;
    
    if (option.is_correct) {
        if (currentQuestionIndex.value < currentRiddle.value.questions.length - 1) {
            setTimeout(() => { currentQuestionIndex.value++; }, 1000);
        } else {
            handleSuccess();
        }
    } else {
        totalErrors.value++;
        showGameToast("Mauvaise réponse !", "error");
        // On passe quand même à la suivante pour la logique de quiz
        if (currentQuestionIndex.value < currentRiddle.value.questions.length - 1) {
            setTimeout(() => { currentQuestionIndex.value++; }, 1000);
        } else {
            handleSuccess();
        }
    }
};

const submitRiddle = () => {
    if (riddleAnswer.value.toLowerCase().trim() === currentRiddle.value.answer?.toLowerCase().trim()) {
        handleSuccess();
    } else {
        totalErrors.value++;
        showGameToast("Réponse incorrecte.", "error");
    }
};

const handleSuccess = () => {
    // Calcul des étoiles basé sur les erreurs du quiz
    earnedStars.value = Math.max(1, 3 - totalErrors.value);
    
    router.post(route('player.complete-location', selectedLocation.value.id), {
        stars: earnedStars.value,
        xp: 150,
        duration: gameTime.value // Envoyer la durée finale
    }, {
        onSuccess: () => {
            showRiddleModal.value = false;
            showSuccessModal.value = true;
            isQuestionnaireActive.value = false;
        }
    });
};

const goBackToLobby = () => {
    router.get(route('player.adventure.solo', props.city.id));
};

function calculateDistance(lat1, lon1, lat2, lon2) {
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
}

onMounted(() => {
    startTimer();
    updateGPS();
});

onUnmounted(() => {
    if (timerInterval.value) clearInterval(timerInterval.value);
});
</script>

<template>
  <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

  <SiteLayout hideFooter>
    <div class="mx-auto max-w-7xl px-2 sm:px-6 py-4 md:py-6 pb-28 md:pb-12 h-full flex flex-col bg-gaming-darker">
      
      <!-- MODE AVENTURE -->
      <div v-if="gameMode === 'aventure'" class="flex flex-col gap-4 flex-1">
          <!-- HUD TOP -->
          <div class="w-full max-w-3xl mx-auto animate-fade-up z-20">
              <div class="glass-strong rounded-2xl border border-electric/30 p-4 md:p-5 backdrop-blur-xl shadow-neon-blue relative overflow-hidden">
                  <div class="flex items-center justify-between gap-4 mb-3">
                      <div class="flex items-center gap-3">
                          <div class="h-10 w-10 rounded-xl bg-electric/10 border border-electric/20 flex items-center justify-center text-electric">
                              <Brain class="h-5 w-5 animate-pulse-soft" />
                          </div>
                          <div>
                              <div class="text-[8px] text-electric font-black uppercase tracking-[0.2em]">Mission Active</div>
                              <h3 class="font-display text-base md:text-lg text-white uppercase tracking-tight">{{ currentTarget?.display_name }}</h3>
                          </div>
                      </div>
                      <button @click="handleUseHint" :disabled="showHint || isPaused" class="h-9 px-4 rounded-xl bg-warning text-black flex items-center gap-2 text-[10px] font-black uppercase tracking-widest hover:bg-warning/80 transition-all shadow-neon-warning shrink-0">
                          <HelpCircle class="h-3.5 w-3.5" /> Indice -50px
                      </button>
                  </div>
                  <p class="text-xs md:text-sm text-foreground/90 italic leading-relaxed border-l-2 border-electric/30 pl-3">"{{ displayEnigma }}"</p>
                  <Transition name="fade">
                    <div v-if="showHint" class="mt-3 p-3 rounded-xl bg-warning/10 border border-warning/30 text-[10px] text-warning flex gap-2">
                        <Eye class="h-4 w-4 shrink-0" />
                        <div>{{ activeEnigma?.indices?.[0] || 'Observez bien les détails environnementaux.' }}</div>
                    </div>
                  </Transition>
              </div>
          </div>

          <!-- RADAR AREA -->
          <div class="flex-1 relative flex items-center justify-center min-h-[300px]">
              <div class="w-full aspect-square max-w-[400px] relative">
                  <div class="absolute inset-0 rounded-full border-2 border-electric/20 overflow-hidden shadow-neon-blue-lg bg-white">
                      <MapComponent ref="mapRef" :locations="locations" :userPosition="userPosition" :targetLocation="currentTarget" :teamMembers="teamMembers" />
                  </div>
                  <div class="absolute inset-0 pointer-events-none rounded-full border-[8px] border-gaming-darker z-10"></div>
                  <div class="absolute -top-2 -right-1 z-20 flex items-center gap-2 bg-gaming-dark/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10 shadow-lg">
                      <Clock class="h-3.5 w-3.5 text-electric" />
                      <span class="text-xs font-mono font-bold text-white">{{ formatTime(gameTime) }}</span>
                      <button @click="togglePause" class="ml-2 p-1 rounded-md hover:bg-white/10 transition-colors">
                          <Pause v-if="!isPaused" class="h-3 w-3 text-electric" />
                          <Play v-else class="h-3 w-3 text-electric" />
                      </button>
                  </div>
              </div>
          </div>

          <!-- ACTIONS BOTTOM -->
          <div class="w-full max-w-3xl mx-auto flex flex-col gap-4 mt-2 animate-fade-up">
              <div class="flex items-center justify-between gap-4">
                  <div class="flex-1 bg-white/5 border border-white/10 backdrop-blur-md px-6 py-4 rounded-2xl flex items-center justify-between">
                      <div class="flex flex-col">
                          <span class="text-[10px] text-electric font-black uppercase tracking-widest mb-1">Distance Cible</span>
                          <div class="flex items-center gap-3">
                              <Navigation class="h-5 w-5 text-electric animate-pulse" />
                              <span class="text-2xl font-display text-white tracking-wider">{{ distanceToClosest || '---' }}</span>
                          </div>
                      </div>
                      <div class="flex items-center gap-1">
                          <div v-for="i in 5" :key="i" :class="cn('h-1.5 w-4 rounded-full transition-all duration-500', i/5 <= proximityScore/100 ? (proximityScore > 80 ? 'bg-destructive shadow-neon-error' : 'bg-electric shadow-neon') : 'bg-white/10')"></div>
                      </div>
                  </div>
                  <button @click="verifyPosition" :disabled="isPaused" class="h-24 w-24 rounded-3xl bg-success text-white shadow-neon-success flex flex-col items-center justify-center gap-1 hover:scale-105 active:scale-95 transition-all border-4 border-gaming-darker shrink-0">
                      <Target :class="['h-8 w-8', isNearLocation ? 'animate-pulse' : 'opacity-80']" />
                      <span class="text-[10px] font-black uppercase tracking-widest">Vérifier</span>
                  </button>
              </div>
          </div>
      </div>

      <!-- MODE CLASSIQUE -->
      <div v-else class="grid gap-4 md:gap-6 lg:grid-cols-12 flex-1 min-h-0">
        <div class="lg:col-span-8 relative rounded-[1.5rem] md:rounded-[2.5rem] overflow-hidden border border-white/20 glass-strong shadow-lg group bg-gaming-dark h-[60vh] md:h-auto min-h-[450px]">
          <div class="absolute inset-0 z-0">
            <MapComponent :locations="locations" :userPosition="userPosition" :teamMembers="teamMembers" />
          </div>
        </div>
        <aside class="lg:col-span-4 space-y-4 overflow-y-auto max-h-[50vh] md:max-h-full custom-scrollbar pb-10">
            <div class="rounded-2xl glass-strong p-5 border border-electric/30">
                <h2 class="font-display text-lg">Exploration de {{ city?.name }}</h2>
                <p class="text-xs text-muted-foreground mt-2">Activez le mode aventure pour commencer la quête.</p>
            </div>
            <div class="space-y-2">
                <div v-for="loc in locations" :key="loc.id" class="flex items-center gap-3 p-2 rounded-xl bg-white/5 border border-white/5">
                    <div class="h-10 w-10 rounded-lg bg-gaming-darker flex items-center justify-center">
                        <Lock v-if="!loc.is_discovered" class="h-4 w-4 text-muted-foreground/30" />
                        <MapPin v-else class="h-4 w-4 text-electric" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold truncate text-foreground">{{ loc.display_name }}</div>
                        <div class="text-[8px] text-muted-foreground uppercase">{{ loc.is_discovered ? loc.category : 'Zone inconnue' }}</div>
                    </div>
                </div>
            </div>
        </aside>
      </div>

      <!-- TOAST -->
      <Transition name="toast">
        <div v-if="toast.show" :class="cn('absolute top-20 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl border backdrop-blur-xl shadow-2xl flex items-center gap-3 min-w-[280px]', toast.type === 'success' ? 'bg-success/10 border-success/30 text-success' : toast.type === 'error' ? 'bg-destructive/10 border-destructive/30 text-destructive' : 'bg-warning/10 border-warning/30 text-warning')">
            <component :is="toast.type === 'success' ? CheckCircle2 : Info" class="h-5 w-5" />
            <span class="text-xs font-bold uppercase tracking-widest">{{ toast.message }}</span>
        </div>
      </Transition>
    </div>
    <MobileTabBar />

    <!-- MODALS -->
    <Modal :show="showRiddleModal" @close="showRiddleModal = false">
        <div class="p-8 bg-gaming-darker border border-electric/20 rounded-[2.5rem] max-w-lg mx-auto">
            <div v-if="isQuestionnaireActive" class="space-y-6">
                <div class="text-[10px] text-electric font-black uppercase tracking-widest">Question {{ currentQuestionIndex + 1 }} / {{ currentRiddle?.questions?.length }}</div>
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10 text-lg text-white font-display">
                    {{ currentRiddle?.questions?.[currentQuestionIndex]?.question_text }}
                </div>
                <div class="grid gap-3">
                    <button v-for="(option, idx) in currentRiddle?.questions?.[currentQuestionIndex]?.options" :key="idx" @click="selectQuestionOption(option)" class="w-full p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-electric transition-all text-left">
                        {{ option.option_text }}
                    </button>
                </div>
            </div>
            <div v-else class="space-y-6">
                <h2 class="font-display text-2xl">{{ selectedLocation?.display_name }}</h2>
                <p class="p-6 rounded-3xl bg-white/5 border border-white/10 italic text-foreground/90 leading-relaxed">"{{ currentRiddle?.content }}"</p>
                <input v-model="riddleAnswer" @keyup.enter="submitRiddle" placeholder="Votre réponse..." class="w-full h-14 rounded-2xl bg-gaming-darker border border-electric/30 px-6 text-white outline-none focus:border-electric transition-all" />
                <NeonButton class="w-full" @click="submitRiddle">Soumettre</NeonButton>
            </div>
        </div>
    </Modal>

    <Modal :show="showSuccessModal" @close="showSuccessModal = false">
        <div class="p-10 bg-gaming-darker border border-electric/20 rounded-[3rem] text-center max-w-sm mx-auto">
            <Trophy class="h-16 w-16 text-electric mx-auto mb-6" />
            <h2 class="font-display text-3xl mb-2 text-white">FÉLICITATIONS !</h2>
            <div class="flex justify-center gap-2 mb-8">
                <Star v-for="s in 3" :key="s" :class="cn('h-8 w-8', s <= earnedStars ? 'text-yellow-400 fill-yellow-400' : 'text-white/10')" />
            </div>
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase">XP GAGNÉS</div>
                    <div class="text-xl font-display text-electric">+150 PX</div>
                </div>
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-muted-foreground uppercase">DURÉE</div>
                    <div class="text-xl font-display text-success">{{ formatTime(gameTime) }}</div>
                </div>
            </div>
            <div class="flex flex-col gap-3">
                <NeonButton size="xl" class="w-full rounded-2xl" @click="showSuccessModal = false">CONTINUER</NeonButton>
                <button @click="goBackToLobby" class="text-xs text-electric font-black uppercase tracking-[0.2em] hover:text-white transition-colors">
                    REJOUER / LOBBY
                </button>
            </div>
        </div>
    </Modal>
  </SiteLayout>
</template>

<style scoped>
.glass-strong { background: rgba(15, 15, 25, 0.8); backdrop-filter: blur(20px); }
.shadow-neon-blue { box-shadow: 0 0 20px rgba(0, 112, 255, 0.2); }
.shadow-neon-blue-lg { box-shadow: 0 0 40px rgba(0, 112, 255, 0.3); }
.shadow-neon-success { box-shadow: 0 0 20px rgba(34, 197, 94, 0.3); }
.shadow-neon-warning { box-shadow: 0 0 20px rgba(234, 179, 8, 0.3); }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translate(-50%, -20px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
