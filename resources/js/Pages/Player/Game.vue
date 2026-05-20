<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import HUDButton from '@/Components/HUDButton.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import MapComponent from '@/Components/MapComponent.vue';
import Modal from '@/Components/Modal.vue';
import {
  MapPin, QrCode, Navigation, Sparkles, Lock, Target, Zap,
  ChevronLeft, Star, Heart, Clock, Play, Info, CheckCircle2, Trophy, Brain,
  Pause, HelpCircle, Eye, XCircle, ArrowRight, Activity
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import gsap from 'gsap';

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

  <SiteLayout hideFooter isHUD isMapPage>
    <HUDHeader />

    <div class="mx-auto max-w-7xl px-4 py-4 md:py-6 pb-28 md:pb-12 h-full flex flex-col relative z-10">
      
      <!-- MODE AVENTURE -->
      <div v-if="gameMode === 'aventure'" class="flex flex-col gap-6 flex-1">
          <!-- HUD TOP MISSION CONTROL -->
          <div class="w-full max-w-4xl mx-auto animate-fade-up z-20">
              <div class="neon-border-box p-5 md:p-6 overflow-hidden group">
                  <div class="neon-corner top-0 left-0 border-r-0 border-b-0 scale-75" />
                  <div class="neon-corner top-0 right-0 border-l-0 border-b-0 scale-75" />
                  
                  <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                      <div class="flex items-center gap-5 flex-1">
                          <div class="h-16 w-16 rounded-2xl bg-primary/10 border-2 border-primary/40 flex items-center justify-center text-primary relative overflow-hidden shrink-0">
                              <div class="absolute inset-0 bg-primary/5 animate-pulse" />
                              <Brain class="h-8 w-8 relative z-10 drop-shadow-[0_0_8px_#06b6d4]" />
                          </div>
                          <div>
                              <div class="flex items-center gap-2 mb-1">
                                  <div class="h-1 w-1 rounded-full bg-primary animate-ping" />
                                  <span class="text-[9px] text-primary font-black uppercase tracking-[0.3em]">MISSION_OBJECTIVE // ACTIVE</span>
                              </div>
                              <h3 class="font-display text-xl md:text-2xl text-white uppercase italic font-black tracking-tighter">{{ currentTarget?.display_name }}</h3>
                          </div>
                      </div>

                      <div class="flex items-center gap-4 w-full md:w-auto">
                          <HUDButton @click="handleUseHint" :disabled="showHint || isPaused" variant="magenta" class="h-10 px-6 text-[9px] flex-1 md:flex-none">
                              <HelpCircle class="h-3.5 w-3.5 mr-2" /> DÉCODER_INDICE
                          </HUDButton>
                          <div class="h-10 px-6 rounded-xl border border-white/10 bg-white/5 flex items-center gap-3 backdrop-blur-xl shrink-0">
                              <Clock class="h-4 w-4 text-primary animate-pulse" />
                              <span class="text-sm font-black text-white italic tracking-tighter">{{ formatTime(gameTime) }}</span>
                          </div>
                      </div>
                  </div>

                  <div class="mt-6 p-5 rounded-[1.5rem] bg-white/[0.02] border border-white/5 relative overflow-hidden">
                      <div class="absolute top-0 left-0 h-full w-0.5 bg-primary drop-shadow-[0_0_8px_#06b6d4]" />
                      <p class="text-sm md:text-base text-white/80 italic font-bold uppercase tracking-widest leading-relaxed pl-4">"{{ displayEnigma }}"</p>
                      
                      <Transition name="fade">
                        <div v-if="showHint" class="mt-4 p-4 rounded-xl bg-amber-500/5 border border-amber-500/20 text-[10px] text-amber-500 font-black tracking-widest flex gap-3 animate-fade-up">
                            <Eye class="h-4 w-4 shrink-0" />
                            <div class="uppercase leading-relaxed">{{ activeEnigma?.indices?.[0] || 'OBSERVEZ_ATTENTIVEMENT_VOTRE_ENVIRONNEMENT_IMMÉDIAT.' }}</div>
                        </div>
                      </Transition>
                  </div>
              </div>
          </div>

          <!-- RADAR AREA - ULTRA REALISTIC -->
          <div class="flex-1 relative flex items-center justify-center min-h-[350px]">
              <div class="relative w-full aspect-square max-w-[450px] p-8">
                  <!-- RADAR BACKGROUND EFFECTS -->
                  <div class="absolute inset-0 rounded-full border border-primary/20 bg-primary/5 backdrop-blur-sm" />
                  <div class="absolute inset-4 rounded-full border border-primary/10" />
                  <div class="absolute inset-1/4 rounded-full border border-primary/5" />
                  
                  <!-- SWEEP LINE EFFECT -->
                  <div class="absolute inset-0 rounded-full animate-spin [animation-duration:4s] pointer-events-none z-30">
                      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0.5 h-1/2 bg-gradient-to-t from-primary/60 to-transparent" />
                  </div>

                  <div class="absolute inset-0 rounded-full border-4 border-zinc-950 overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.8)] z-20">
                      <MapComponent ref="mapRef" :locations="locations" :userPosition="userPosition" :targetLocation="currentTarget" :teamMembers="teamMembers" />
                      <!-- GRID OVERLAY ON MAP -->
                      <div class="absolute inset-0 grid-bg opacity-30 pointer-events-none" />
                  </div>

                  <!-- RADAR DECORATIONS -->
                  <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-zinc-950 border border-primary/40 rounded-full z-40 text-[8px] font-black text-primary tracking-[0.4em] uppercase shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                      SATELLITE_LINK_ESTABLISHED
                  </div>

                  <!-- CONTROLS -->
                  <div class="absolute bottom-4 right-4 z-40 flex flex-col gap-2">
                      <button @click="togglePause" class="h-12 w-12 rounded-xl bg-zinc-950/80 border border-white/10 backdrop-blur-md flex items-center justify-center text-primary hover:border-primary/50 transition-all shadow-xl">
                          <Pause v-if="!isPaused" class="h-5 w-5" />
                          <Play v-else class="h-5 w-5" />
                      </button>
                  </div>
              </div>
          </div>

          <!-- ACTIONS BOTTOM -->
          <div class="w-full max-w-4xl mx-auto flex flex-col gap-4 mt-auto animate-fade-up">
              <div class="flex items-center gap-6">
                  <div class="flex-1 neon-border-box p-5 rounded-[2rem] flex items-center justify-between overflow-hidden">
                      <div class="flex flex-col">
                          <span class="text-[9px] text-primary font-black uppercase tracking-[0.4em] mb-2">TARGET_DISTANCE</span>
                          <div class="flex items-center gap-4">
                              <div class="h-10 w-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center">
                                  <Navigation :class="cn('h-6 w-6 text-primary transition-all duration-300', isNearLocation ? 'animate-bounce' : 'animate-pulse')" />
                              </div>
                              <span class="text-3xl font-display text-white font-black italic tracking-tighter drop-shadow-[0_0_10px_rgba(255,255,255,0.2)]">
                                  {{ distanceToClosest || '---' }}
                              </span>
                          </div>
                      </div>
                      
                      <!-- PROXIMITY SIGNAL BARS -->
                      <div class="flex flex-col items-end gap-2">
                          <div class="text-[8px] text-white/30 font-black uppercase tracking-widest">SIGNAL_STRENGTH</div>
                          <div class="flex items-center gap-1.5 h-6">
                              <div v-for="i in 8" :key="i" 
                                   :class="cn('w-2 rounded-sm transition-all duration-700', 
                                   i/8 <= proximityScore/100 ? (proximityScore > 85 ? 'bg-red-500 shadow-[0_0_12px_#ef4444]' : 'bg-primary shadow-[0_0_12px_#06b6d4]') : 'bg-white/5',
                                   'h-' + (i + 2))"
                                   :style="{ height: `${(i/8) * 100}%` }">
                              </div>
                          </div>
                      </div>
                  </div>

                  <HUDButton @click="verifyPosition" :disabled="isPaused" variant="primary" class="h-28 w-28 rounded-[2.5rem] shadow-[0_0_40px_rgba(6,182,212,0.2)] border-4 border-zinc-950 group shrink-0">
                      <Target :class="cn('h-10 w-10 transition-all duration-500', isNearLocation ? 'scale-125 text-white drop-shadow-[0_0_15px_#06b6d4]' : 'text-primary opacity-60 group-hover:opacity-100')" />
                      <span class="text-[9px] font-black uppercase tracking-widest mt-2">VÉRIFIER</span>
                  </HUDButton>
              </div>
          </div>
      </div>

      <!-- MODE CLASSIQUE -->
      <div v-else class="grid gap-6 lg:grid-cols-12 flex-1 min-h-0">
        <div class="lg:col-span-8 relative rounded-[3rem] overflow-hidden border-2 border-white/5 hud-glass-card shadow-2xl group bg-zinc-950 h-[55vh] md:h-auto min-h-[450px]">
          <div class="absolute inset-0 z-0">
            <MapComponent :locations="locations" :userPosition="userPosition" :teamMembers="teamMembers" />
          </div>
          <!-- Grid and HUD overlays for the map -->
          <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />
          <div class="absolute top-6 left-6 flex items-center gap-3 bg-zinc-950/80 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
              <Activity class="h-4 w-4 text-primary animate-pulse" />
              <span class="text-[10px] font-black text-white uppercase tracking-[0.3em]">MAP_STREAM_ACTIVE</span>
          </div>
        </div>

        <aside class="lg:col-span-4 flex flex-col gap-6 overflow-hidden">
            <div class="neon-border-box p-6 rounded-[2rem]">
                <div class="flex items-center gap-3 mb-4">
                    <MapPin class="h-5 w-5 text-primary" />
                    <h2 class="font-display text-xl text-white font-black uppercase italic tracking-tighter">EXPLORATION // {{ city?.name }}</h2>
                </div>
                <p class="text-[11px] text-white/40 font-bold uppercase tracking-widest leading-relaxed border-l border-white/10 pl-4">Activez le mode aventure pour commencer la quête de données.</p>
                <HUDButton :href="route('player.adventure.setup', city?.id)" variant="primary" class="w-full mt-6 h-12 rounded-xl">
                    INITIER_SEQUENCE_AVENTURE
                </HUDButton>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-3 pr-2">
                <div v-for="loc in locations" :key="loc.id" class="flex items-center gap-5 p-4 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.05] hover:border-primary/20 transition-all group">
                    <div class="h-12 w-12 rounded-xl bg-zinc-900 border border-white/10 flex items-center justify-center shrink-0 group-hover:border-primary/40 transition-all">
                        <Lock v-if="!loc.is_discovered" class="h-5 w-5 text-white/20" />
                        <MapPin v-else class="h-5 w-5 text-primary drop-shadow-[0_0_8px_#06b6d4]" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-black text-white uppercase italic tracking-tight truncate group-hover:text-primary transition-colors">{{ loc.display_name }}</div>
                        <div class="text-[9px] text-white/30 font-black uppercase tracking-widest mt-1">{{ loc.is_discovered ? loc.category : 'ZONE_ENCRYPTÉE' }}</div>
                    </div>
                </div>
            </div>
        </aside>
      </div>

      <!-- TOAST - HUD STYLE -->
      <Transition name="toast">
        <div v-if="toast.show" :class="cn('absolute top-24 left-1/2 -translate-x-1/2 z-[100] px-8 py-4 rounded-2xl border-2 backdrop-blur-2xl shadow-[0_0_50px_rgba(0,0,0,0.5)] flex items-center gap-4 min-w-[320px]', toast.type === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-400' : toast.type === 'error' ? 'bg-red-500/10 border-red-500/30 text-red-400' : 'bg-amber-500/10 border-amber-500/30 text-amber-400')">
            <div class="h-8 w-8 rounded-lg bg-current opacity-10 absolute inset-0" />
            <component :is="toast.type === 'success' ? CheckCircle2 : (toast.type === 'error' ? XCircle : Info)" class="h-6 w-6 relative z-10" />
            <span class="text-[11px] font-black uppercase tracking-[0.2em] relative z-10">{{ toast.message }}</span>
        </div>
      </Transition>
    </div>
    <MobileTabBar />

    <!-- MODALS - HUD STYLE -->
    <Modal :show="showRiddleModal" @close="showRiddleModal = false">
        <div class="p-1 rounded-[3rem] bg-gradient-to-br from-primary/40 via-white/5 to-purple-500/40">
            <div class="p-10 bg-zinc-950 rounded-[2.9rem] max-w-lg mx-auto relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-20" />
                
                <div v-if="isQuestionnaireActive" class="space-y-8 relative z-10">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] text-primary font-black uppercase tracking-[0.4em]">DATA_QUERY // {{ currentQuestionIndex + 1 }} / {{ currentRiddle?.questions?.length }}</div>
                        <Activity class="h-4 w-4 text-primary animate-pulse" />
                    </div>
                    <div class="p-8 rounded-[2rem] bg-white/[0.03] border border-white/10 text-xl text-white font-display font-black uppercase italic tracking-tighter leading-tight">
                        {{ currentRiddle?.questions?.[currentQuestionIndex]?.question_text }}
                    </div>
                    <div class="grid gap-4">
                        <button v-for="(option, idx) in currentRiddle?.questions?.[currentQuestionIndex]?.options" :key="idx" @click="selectQuestionOption(option)" 
                                class="w-full p-6 rounded-2xl bg-white/5 border border-white/10 hover:border-primary hover:bg-primary/5 transition-all text-left group">
                            <div class="flex items-center gap-4">
                                <div class="h-6 w-6 rounded border border-white/20 flex items-center justify-center text-[10px] font-black group-hover:border-primary group-hover:text-primary transition-all">
                                    {{ String.fromCharCode(65 + idx) }}
                                </div>
                                <span class="text-sm font-bold text-white/80 group-hover:text-white transition-colors uppercase tracking-widest">{{ option.option_text }}</span>
                            </div>
                        </button>
                    </div>
                </div>
                <div v-else class="space-y-8 relative z-10">
                    <h2 class="font-display text-3xl text-white font-black uppercase italic tracking-tighter">{{ selectedLocation?.display_name }}</h2>
                    <div class="p-8 rounded-[2rem] bg-white/[0.03] border border-white/10 relative overflow-hidden">
                        <div class="absolute top-0 left-0 h-full w-1 bg-primary" />
                        <p class="italic text-white/80 font-bold uppercase tracking-widest leading-relaxed">"{{ currentRiddle?.content }}"</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-[9px] text-white/20 font-black uppercase tracking-[0.4em] ml-2">INPUT_DECRYPTION_KEY</div>
                        <input v-model="riddleAnswer" @keyup.enter="submitRiddle" placeholder="VOTRE_REPONSE..." 
                               class="w-full h-16 rounded-2xl bg-zinc-900 border-2 border-white/10 px-8 text-white font-black tracking-widest outline-none focus:border-primary transition-all uppercase placeholder:text-white/10" />
                    </div>
                    <HUDButton variant="primary" class="w-full h-14 rounded-2xl" @click="submitRiddle">TRANSMETTRE_DONNÉES</HUDButton>
                </div>
            </div>
        </div>
    </Modal>

    <Modal :show="showSuccessModal" @close="showSuccessModal = false">
        <div class="p-1 rounded-[3.5rem] bg-gradient-to-br from-green-500/40 via-white/5 to-primary/40">
            <div class="p-12 bg-zinc-950 rounded-[3.4rem] text-center max-w-sm mx-auto relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-20" />
                
                <div class="relative z-10">
                    <div class="h-24 w-24 rounded-full bg-green-500/10 border-2 border-green-500/40 flex items-center justify-center mx-auto mb-8 shadow-[0_0_40px_rgba(34,197,94,0.2)]">
                        <Trophy class="h-12 w-12 text-green-400 drop-shadow-[0_0_12px_#22c55e] animate-bounce" />
                    </div>
                    
                    <h2 class="font-display text-4xl text-white font-black uppercase italic tracking-tighter mb-4">MISSION_SUCCÈS</h2>
                    
                    <div class="flex justify-center gap-4 mb-10">
                        <Star v-for="s in 3" :key="s" :class="cn('h-10 w-10 drop-shadow-[0_0_10px_rgba(234,179,8,0.5)]', s <= earnedStars ? 'text-amber-400 fill-amber-400' : 'text-white/10')" />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-10">
                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2">XP_COLLECTED</div>
                            <div class="text-2xl font-display text-primary font-black italic">+{{ earnedXp }}</div>
                        </div>
                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10">
                            <div class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2">TIME_ELAPSED</div>
                            <div class="text-2xl font-display text-green-400 font-black italic">{{ formatTime(gameTime) }}</div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <HUDButton variant="primary" size="xl" class="w-full h-16 rounded-[1.5rem]" @click="showSuccessModal = false">
                            CONTINUER_SEQUENCE
                        </HUDButton>
                        <button @click="goBackToLobby" class="text-[10px] text-primary font-black uppercase tracking-[0.4em] hover:text-white transition-colors">
                            REJOUER // SECTOR_LOBBY
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
  </SiteLayout>
</template>

<style scoped>
.hud-glass-card { background: rgba(15, 15, 25, 0.8); backdrop-filter: blur(20px); }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translate(-50%, -20px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
