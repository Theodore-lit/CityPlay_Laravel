<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { usePage, router, Head } from "@inertiajs/vue3";
import {
    MapPin,
    Navigation,
    Target,
    Zap,
    Clock,
    Pause,
    Play,
    Info,
    CheckCircle2,
    Eye,
    Star,
    Trophy,
    ArrowRight,
    HelpCircle,
    X,
    ChevronLeft,
    Brain,
    Lock,
    BookOpen,
} from "lucide-vue-next";
import MapComponent from "@/Components/MapComponent.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import AppImage from "@/Components/AppImage.vue";
import Modal from "@/Components/Modal.vue";
import { cn } from "@/lib/utils";
import gsap from "gsap";
import confetti from "canvas-confetti";
import SiteLayout from "@/Layouts/SiteLayout.vue";

const props = defineProps({
    city: Object,
    locations: Array,
    currentSession: Object,
    initialTeamPositions: Array,
    auth: Object,
});

// --- ÉTATS RÉACTIFS ---
const userPosition = ref(null);
const teamMembers = ref(props.initialTeamPositions || []);
// const isPaused = ref(false);
const totalErrors = ref(0);
const timerInterval = ref(null);
const mapRef = ref(null);
const showHintModal = ref(false);

// Modal Énigme / Questionnaire
const showRiddleModal = ref(false);
const riddleType = ref("site"); // 'unlock' or 'site'
const selectedLocation = ref(null);
const currentRiddle = ref(null);
const riddleAnswer = ref("");
const isQuestionnaireActive = ref(false);
//Persistance des données //Theo
const storageKey = `cityplay_progress_aventure_${props.currentSession?.id || "game"}`;
const savedData = JSON.parse(localStorage.getItem(storageKey)) || null;

// initialiser
const currentQuestionIndex = ref(savedData?.currentQuestionIndex || 0);
const questionnaireAnswers = ref(savedData?.questionnaireAnswers || []);
const isPaused = ref(savedData?.isPaused || false);
const pauseModal = ref(savedData?.pauseModal || false);
const outModal = ref(false);
const gameTime = ref(
    savedData?.gameTime !== undefined ? savedData.gameTime : 0,
);
const usedHints = ref(savedData?.usedHints || 0);
const showHint = ref(savedData?.showHint || false);

// Computed pour vérifier si un overlay est actif
const isAnyOverlayActive = computed(() => {
    return (
        pauseModal.value ||
        showHintModal.value ||
        showRiddleModal.value ||
        showSuccessModal.value ||
        outModal.value
    );
});

// Modal Succès
const showSuccessModal = ref(false);
const earnedStars = ref(3);
const earnedXp = ref(150);
const showHistoryModal = ref(false);

// Toast
const toast = ref({ show: false, message: "", type: "info" });

const updateGPS = () => {
    if ("geolocation" in navigator) {
        navigator.geolocation.watchPosition(
            (position) => {
                // console.log(position.coords.latitude);
                // console.log(position.coords.longitude);
                userPosition.value = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
            },
            (err) => {
                console.error("Erreur GPS:", err);
            },
            { enableHighAccuracy: true },
        );
    }
};

// --- COMPUTED ---
const rawDistance = computed(() => {
    if (!userPosition.value || !currentTarget.value) return null;
    return calculateDistance(
        userPosition.value.lat,
        userPosition.value.lng,
        currentTarget.value.latitude,
        currentTarget.value.longitude,
    );
});

const distanceToClosest = computed(() => {
    if (rawDistance.value === null) return "---";
    const dist = rawDistance.value;
    return dist > 1000
        ? (dist / 1000).toFixed(1) + "km"
        : Math.round(dist) + "m";
});

const isNearLocation = computed(() => {
    return rawDistance.value !== null && rawDistance.value < 50;
});

const proximityScore = computed(() => {
    if (rawDistance.value === null) return 0;
    const dist = rawDistance.value;
    if (dist > 1000) return 0;
    return Math.max(0, 100 - dist / 10); // 0m = 100%, 1000m = 0%
});

const cityData = computed(() => props.city);
const gameMode = computed(() =>
    props.currentSession ? "aventure" : "classique",
);

const currentTarget = computed(() => {
    if (!props.locations || !props.currentSession) return null;
    return (
        props.locations.find(
            (l) => l.id === props.currentSession.current_location_id,
        ) ||
        props.locations[0] ||
        null
    );
});

const activeEnigma = computed(() => {
    if (!currentTarget.value) return null;
    return (
        currentTarget.value.enigmas?.find(
            (e) => e.id === props.currentSession.current_enigma_id,
        ) || currentTarget.value.enigmas?.[0]
    );
});

const displayEnigma = computed(() => {
    return (
        activeEnigma.value?.content ||
        "Suivez le radar pour découvrir ce lieu mystérieux..."
    );
});

// --- MÉTHODES ---
const startTimer = () => {
    if (timerInterval.value) clearInterval(timerInterval.value);
    timerInterval.value = setInterval(() => {
        if (!isPaused.value) {
            gameTime.value = Number(gameTime.value) + 1;
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
};

const showGameToast = (message, type = "info") => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

const togglePause = () => {
    isPaused.value = !isPaused.value;
    pauseModal.value = isPaused.value;
};

const toggleOut = () => {
    isPaused.value = true;
    outModal.value = isPaused.value;
}

const resumeGame = () => {
    isPaused.value = false;
    pauseModal.value = false;
    outModal.value = false;
};

const handleUseHint = () => {
    if (usePage().props.auth.user.xp < 50) {
        showGameToast("XP insuffisants pour un indice.", "error");
        return;
    }
    router.post(
        route("player.use-hint"),
        {},
        {
            onSuccess: () => {
                showHint.value = true;
                usedHints.value = (usedHints.value || 0) + 1;
                showGameToast("Indice débloqué (-50 PX)", "success");
            },
        },
    );
    showHintModal.value = false;
};

const verifyPosition = () => {
    if (!userPosition.value || !currentTarget.value) return;

    if (isNearLocation.value) {
        gsap.to(".radar-circle", {
            scale: 1.2,
            opacity: 0,
            duration: 0.5,
            ease: "power2.out",
        });

        isPaused.value = true;
        router.post(
            route("player.unlock-location", currentTarget.value.id),
            {},
            {
                onSuccess: (page) => {
                    const session = page.props.currentSession;

                    // On attend un peu que le loader disparaisse pour lancer les confettis
                    setTimeout(() => {
                        confetti({
                            particleCount: 150,
                            spread: 70,
                            origin: { y: 0.6 },
                            zIndex: 10000, // Au-dessus du loader s'il reste
                            colors: ["#0070ff", "#00f2ff", "#ffffff"],
                        });
                    }, 500);

                    if (session.status === "completed") {
                        showSuccessModal.value = true;
                    } else {
                        // On passe au questionnaire (Bonus)
                        selectedLocation.value = currentTarget.value;
                        currentRiddle.value = currentTarget.value.enigmas.find(
                            (e) => e.is_site_specific,
                        );
                        if (currentRiddle.value?.questions?.length > 0) {
                            isQuestionnaireActive.value = true;
                            currentQuestionIndex.value = 0;
                            questionnaireAnswers.value = [];
                            showRiddleModal.value = true;

                            // Animation d'entrée du questionnaire
                            nextTick(() => {
                                gsap.from(".question-card", {
                                    y: 50,
                                    opacity: 0,
                                    duration: 0.8,
                                    ease: "back.out(1.7)",
                                });
                            });
                        } else {
                            showSuccessModal.value = true;
                        }
                    }
                },
            },
        );
    } else {
        totalErrors.value++;
        showGameToast("Vous n'êtes pas encore sur le lieu exact.", "error");
    }
};

const startQuestionnaire = () => {
    isQuestionnaireActive.value = true;
    currentQuestionIndex.value = 0;
    questionnaireAnswers.value = [];
    totalErrors.value = 0;
    showRiddleModal.value = true;
    showGameToast("Lieu trouvé !", "success");
};

const selectedOption = ref(null);

const selectQuestionOption = (option) => {
    if (selectedOption.value) return; // Empêche de changer après sélection
    selectedOption.value = option;

    if (!option.is_correct) {
        totalErrors.value++;
        showGameToast("Mauvaise réponse !", "error");
    } else {
        showGameToast("Bien joué !", "success");
    }
};

const nextQuestion = () => {
    if (!selectedOption.value) return;

    questionnaireAnswers.value[currentQuestionIndex.value] =
        selectedOption.value;
    selectedOption.value = null;

    if (currentQuestionIndex.value < currentRiddle.value.questions.length - 1) {
        currentQuestionIndex.value++;

        // Animation de transition
        nextTick(() => {
            gsap.from(".question-card", {
                x: 30,
                opacity: 0,
                duration: 0.5,
                ease: "power2.out",
            });
        });
    } else {
        handleSuccess();
    }
};

const submitRiddle = () => {
    if (
        riddleAnswer.value.toLowerCase().trim() ===
        currentRiddle.value.answer?.toLowerCase().trim()
    ) {
        handleSuccess();
    } else {
        totalErrors.value++;
        showGameToast("Réponse incorrecte.", "error");
    }
};

const handleSuccess = () => {
    // Calcul des étoiles basé sur les erreurs du quiz
    earnedStars.value = Math.max(1, 3 - totalErrors.value);

    router.post(
        route("player.complete-location", selectedLocation.value.id),
        {
            stars: earnedStars.value,
            xp: selectedLocation.value.enigmas[0]?.reward_coins || 150,
            duration: gameTime.value, // Envoyer la durée finale
        },
        {
            onSuccess: () => {
                showRiddleModal.value = false;
                showSuccessModal.value = true;
                isQuestionnaireActive.value = false;
            },
        },
    );
};

const goBackToLobby = () => {
    isPaused.value = true;
    gameTime.value = 0;
    usedHints.value = 0;
    showHint.value = false;
    // Nettoyer le localStorage lors de la sortie définitive si on veut repartir de zéro,
    // ou simplement quitter vers le lobby.
    router.get(route("player.adventure.solo", props.city.id));
};

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3;
    const φ1 = (lat1 * Math.PI) / 180;
    const φ2 = (lat2 * Math.PI) / 180;
    const Δφ = ((lat2 - lat1) * Math.PI) / 180;
    const Δλ = ((lon2 - lon1) * Math.PI) / 180;
    const a =
        Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

onMounted(() => {
    console.log(activeEnigma.value);
    startTimer();
    updateGPS();
});

onUnmounted(() => {
    if (timerInterval.value) clearInterval(timerInterval.value);
});

watch(
    [
        currentQuestionIndex,
        questionnaireAnswers,
        gameTime,
        isPaused,
        pauseModal,
        
        usedHints,
        showHint,
    ],
    ([
        newIndex,
        newAnswers,
        newTime,
        newProgressPaused,
        newPauseModal,
        newUsedHints,
        newShowHint,
    ]) => {
        const dataToSave = {
            currentQuestionIndex: newIndex,
            questionnaireAnswers: newAnswers,
            gameTime: newTime,
            isPaused: newProgressPaused,
            pauseModal: newPauseModal,
            usedHints: newUsedHints,
            showHint: newShowHint,
        };
        localStorage.setItem(storageKey, JSON.stringify(dataToSave));
    },
    { deep: true },
);
</script>

<template>
    <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

    <SiteLayout hideFooter>
        <div
            class="mx-auto w-full px-2 sm:px-6 py-4 md:py-6 pb-28 md:pb-12 h-screen flex flex-col bg-gaming-darker"
        >
            <div class="flex gap-3 items-center">
                <button
                    @click="toggleOut"
                    class="grid h-12 w-12 rounded-2xl glass place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>
            </div>
            <!-- MODE AVENTURE -->
            <div
                v-if="gameMode === 'aventure'"
                class="flex flex-col gap-4 flex-1"
            >
                <!-- HUD TOP -->
                <div
                    v-if="!isAnyOverlayActive"
                    class="w-full max-w-3xl mx-auto animate-fade-up z-20"
                >
                    <div
                        class="glass-strong mt-5 rounded-2xl border border-electric/30 p-4 md:p-5 backdrop-blur-xl shadow-neon-blue relative overflow-hidden"
                    >
                        <div
                            class="flex items-center justify-between gap-4 mb-3"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-xl bg-electric/10 border border-electric/20 flex items-center justify-center text-electric"
                                >
                                    <Brain class="h-5 w-5 animate-pulse-soft" />
                                </div>
                                <div>
                                    <div
                                        class="text-[8px] text-electric font-black uppercase tracking-[0.2em]"
                                    >
                                        Mission Active
                                    </div>
                                    <h3
                                        class="font-display text-base md:text-lg text-white uppercase tracking-tight"
                                    >
                                        {{ currentTarget?.display_name }}
                                    </h3>
                                </div>
                            </div>
                            <button
                                @click="showHintModal = true"
                                :disabled="
                                    activeEnigma?.indices.length == usedHints ||
                                    isPaused
                                "
                                class="h-9 px-4 rounded-xl bg-warning text-white flex items-center gap-2 text-[10px] font-black uppercase tracking-widest hover:bg-warning/80 transition-all shadow-neon-warning shrink-0"
                            >
                                <HelpCircle class="h-3.5 w-3.5" /> Indice -50px
                            </button>
                        </div>
                        <p
                            class="text-xs md:text-sm text-foreground/90 italic leading-relaxed border-l-2 border-electric/30 pl-3"
                        >
                            "{{ displayEnigma }}"
                        </p>
                        <Transition name="fade">
                            <div v-if="showHint">
                                <div
                                    v-for="i in usedHints"
                                    :key="i"
                                    class="mt-3 p-3 rounded-xl bg-warning/10 border border-warning/30 text-[10px] text-warning flex gap-2"
                                >
                                    <Eye class="h-4 w-4 shrink-0" />
                                    <div>
                                        {{
                                            activeEnigma?.indices?.[i - 1] ||
                                            "Observez bien les détails environnementaux."
                                        }}
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- RADAR AREA -->
                <div
                    class="flex-1 relative flex items-center justify-center min-h-[300px]"
                >
                    <div class="w-full aspect-square max-w-[400px] relative">
                        <div
                            class="absolute inset-0 rounded-full border-2 border-electric/20 overflow-hidden shadow-neon-blue-lg bg-gaming-darker"
                        >
                            <MapComponent
                                v-if="!isAnyOverlayActive"
                                ref="mapRef"
                                :locations="locations"
                                :userPosition="userPosition"
                                :targetLocation="currentTarget"
                                :teamMembers="teamMembers"
                            />
                        </div>
                        <div
                            v-if="!isAnyOverlayActive"
                            class="absolute inset-0 pointer-events-none rounded-full border-[8px] border-gaming-darker z-10"
                        ></div>
                        <div
                            v-if="!isAnyOverlayActive"
                            class="absolute -top-2 -right-1 z-20 flex items-center gap-2 bg-gaming-dark/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10 shadow-lg"
                        >
                            <Clock class="h-3.5 w-3.5 text-electric" />
                            <span
                                class="text-xs font-mono font-bold text-white"
                                >{{ formatTime(gameTime) }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- ACTIONS BOTTOM -->
                <div
                    v-if="!isAnyOverlayActive"
                    class="w-full max-w-3xl mx-auto flex flex-col gap-4 mt-2 animate-fade-up"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div
                            class="flex-1 bg-white/5 border border-white/10 backdrop-blur-md px-6 py-4 rounded-2xl flex items-center justify-between"
                        >
                            <div class="flex flex-col">
                                <span
                                    class="text-[10px] text-electric font-black uppercase tracking-widest mb-1"
                                    >Distance Cible</span
                                >
                                <div class="flex items-center gap-3">
                                    <Navigation
                                        class="h-5 w-5 text-electric animate-pulse"
                                    />
                                    <span
                                        class="text-2xl font-display text-white tracking-wider"
                                        >{{ distanceToClosest || "---" }}</span
                                    >
                                </div>
                            </div>
                            <button
                                @click="togglePause"
                                class="flex cursor-pointer items-center rounded-lg p-1 bg-amber-500"
                            >
                                <p class="text-white text-lg">{{!isPaused? 'Pause' : 'Commencer'}}</p>
                                <div
                                    class="ml-2 p-1 rounded-md hover:bg-white/10 transition-colors"
                                >
                                    <Pause
                                        v-if="!isPaused"
                                        class="h-4 w-4 text-electric"
                                    />
                                    <Play
                                        v-else
                                        class="h-3 w-3 text-electric"
                                    />
                                </div>
                                <!-- <div v-for="i in 5" :key="i" :class="cn('h-1.5 w-4 rounded-full transition-all duration-500', i/5 <= proximityScore/100 ? (proximityScore > 80 ? 'bg-destructive shadow-neon-error' : 'bg-electric shadow-neon') : 'bg-white/10')"></div> -->
                            </button>
                        </div>
                        <button
                            @click="verifyPosition"
                            :disabled="isPaused"
                            class="h-24 w-24 rounded-3xl bg-success text-white shadow-neon-success flex flex-col items-center justify-center gap-1 hover:scale-105 active:scale-95 transition-all border-4 border-gaming-darker shrink-0"
                        >
                            <Target
                                :class="[
                                    'h-8 w-8',
                                    isNearLocation
                                        ? 'animate-pulse'
                                        : 'opacity-80',
                                ]"
                            />
                            <span
                                class="text-[10px] font-black uppercase tracking-widest"
                                >Vérifier</span
                            >
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODE CLASSIQUE -->
            <!-- <div
                v-else
                class="grid gap-4 md:gap-6 lg:grid-cols-12 flex-1 min-h-0"
            >
                <div
                    class="lg:col-span-8 relative rounded-[1.5rem] md:rounded-[2.5rem] overflow-hidden border border-white/20 glass-strong shadow-lg group bg-gaming-dark h-[60vh] md:h-auto min-h-[450px]"
                >
                    <div class="absolute inset-0 z-0">
                        <MapComponent
                            :locations="locations"
                            :userPosition="userPosition"
                            :teamMembers="teamMembers"
                        />
                    </div>
                </div>
                <aside
                    class="lg:col-span-4 space-y-4 overflow-y-auto max-h-[50vh] md:max-h-full custom-scrollbar pb-10"
                >
                    <div
                        class="rounded-2xl glass-strong p-5 border border-electric/30"
                    >
                        <h2 class="font-display text-lg">
                            Exploration de {{ city?.name }}
                        </h2>
                        <p class="text-xs text-muted-foreground mt-2">
                            Activez le mode aventure pour commencer la quête.
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="loc in locations"
                            :key="loc.id"
                            class="flex items-center gap-3 p-2 rounded-xl bg-white/5 border border-white/5"
                        >
                            <div
                                class="h-10 w-10 rounded-lg bg-gaming-darker flex items-center justify-center"
                            >
                                <Lock
                                    v-if="!loc.is_discovered"
                                    class="h-4 w-4 text-muted-foreground/30"
                                />
                                <MapPin v-else class="h-4 w-4 text-electric" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="text-sm font-bold truncate text-foreground"
                                >
                                    {{ loc.display_name }}
                                </div>
                                <div
                                    class="text-[8px] text-muted-foreground uppercase"
                                >
                                    {{
                                        loc.is_discovered
                                            ? loc.category
                                            : "Zone inconnue"
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div> -->

            <!-- TOAST -->
            <Transition name="toast">
                <div
                    v-if="toast.show"
                    :class="
                        cn(
                            'absolute top-20 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl border backdrop-blur-xl shadow-2xl flex items-center gap-3 min-w-[280px]',
                            toast.type === 'success'
                                ? 'bg-success/10 border-success/30 text-success'
                                : toast.type === 'error'
                                  ? 'bg-destructive/10 border-destructive/30 text-destructive'
                                  : 'bg-warning/10 border-warning/30 text-warning',
                        )
                    "
                >
                    <component
                        :is="toast.type === 'success' ? CheckCircle2 : Info"
                        class="h-5 w-5"
                    />
                    <span class="text-xs font-bold uppercase tracking-widest">{{
                        toast.message
                    }}</span>
                </div>
            </Transition>
        </div>
        <MobileTabBar />

        <!-- BONUS QUESTIONNAIRE OVERLAY -->
        <Transition name="fade-scale">
            <div
                v-if="showRiddleModal"
                class="fixed inset-0 z-[150] bg-gaming-dark/95 backdrop-blur-3xl flex flex-col items-center justify-center p-6"
            >
                <!-- Decorative background elements -->
                <div
                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-electric to-transparent opacity-50"
                ></div>
                <div
                    class="absolute -top-20 -right-20 w-80 h-80 bg-electric/10 rounded-full blur-[100px]"
                ></div>

                <div class="w-full max-w-2xl space-y-8 relative">
                    <!-- Progress Bar -->
                    <div v-if="isQuestionnaireActive" class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div class="space-y-1">
                                <div
                                    class="text-[10px] text-electric font-black uppercase tracking-[0.4em]"
                                >
                                    Mission Bonus
                                </div>
                                <h2
                                    class="text-3xl font-display text-white uppercase italic font-black tracking-tighter"
                                >
                                    Exploration
                                    <span class="text-white/40">Data</span>
                                </h2>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-[10px] text-muted-foreground uppercase font-black tracking-widest"
                                >
                                    Progression
                                </div>
                                <div class="text-xl font-display text-white">
                                    {{ currentQuestionIndex + 1 }} /
                                    {{ currentRiddle?.questions?.length }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="h-2 w-full bg-white/5 rounded-full overflow-hidden border border-white/10 p-0.5"
                        >
                            <div
                                class="h-full bg-electric rounded-full shadow-neon transition-all duration-700 ease-out"
                                :style="{
                                    width: `${((currentQuestionIndex + 1) / currentRiddle?.questions?.length) * 100}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Question Card -->
                    <div
                        v-if="isQuestionnaireActive"
                        class="question-card space-y-6"
                    >
                        <div
                            class="glass-strong p-8 md:p-12 rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden group"
                        >
                            <div
                                class="absolute -top-10 -left-10 h-32 w-32 bg-electric/5 rounded-full blur-3xl group-hover:bg-electric/10 transition-colors"
                            ></div>

                            <div class="relative z-10 flex gap-6 items-start">
                                <div
                                    class="h-14 w-14 rounded-2xl bg-electric/10 border border-electric/20 flex items-center justify-center shrink-0"
                                >
                                    <HelpCircle class="h-7 w-7 text-electric" />
                                </div>
                                <div
                                    class="text-xl md:text-2xl font-display text-white leading-tight uppercase italic font-black"
                                >
                                    {{
                                        currentRiddle?.questions?.[
                                            currentQuestionIndex
                                        ]?.question_text
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Options Grid -->
                        <div class="grid gap-4">
                            <button
                                v-for="(option, idx) in currentRiddle
                                    ?.questions?.[currentQuestionIndex]
                                    ?.options"
                                :key="idx"
                                @click="selectQuestionOption(option)"
                                :disabled="selectedOption !== null"
                                :class="
                                    cn(
                                        'w-full p-6 rounded-2xl border transition-all duration-300 flex items-center justify-between group',
                                        selectedOption === option
                                            ? option.is_correct
                                                ? 'bg-success/20 border-success shadow-neon-success'
                                                : 'bg-destructive/20 border-destructive shadow-neon-error'
                                            : selectedOption &&
                                                option.is_correct
                                              ? 'bg-success/10 border-success/50'
                                              : 'bg-white/5 border-white/10 hover:border-electric/50 hover:bg-white/10',
                                    )
                                "
                            >
                                <span
                                    :class="
                                        cn(
                                            'text-sm font-bold uppercase tracking-wider',
                                            selectedOption === option
                                                ? 'text-white'
                                                : 'text-white/60',
                                        )
                                    "
                                >
                                    {{ option.option_text }}
                                </span>
                                <div
                                    v-if="selectedOption === option"
                                    class="h-6 w-6 rounded-full flex items-center justify-center"
                                >
                                    <CheckCircle2
                                        v-if="option.is_correct"
                                        class="h-5 w-5 text-success"
                                    />
                                    <X
                                        v-else
                                        class="h-5 w-5 text-destructive"
                                    />
                                </div>
                            </button>
                        </div>

                        <!-- Next Action -->
                        <div class="flex justify-end pt-4">
                            <NeonButton
                                v-if="selectedOption"
                                @click="nextQuestion"
                                size="xl"
                                class="px-10 group"
                            >
                                {{
                                    currentQuestionIndex <
                                    currentRiddle.questions.length - 1
                                        ? "QUESTION SUIVANTE"
                                        : "TERMINER LA MISSION"
                                }}
                                <ArrowRight
                                    class="ml-2 h-5 w-5 group-hover:translate-x-2 transition-transform"
                                />
                            </NeonButton>
                        </div>
                    </div>

                    <!-- Riddle Mode (Fallback/Legacy if no questions) -->
                    <div v-else class="space-y-6 text-center">
                        <div
                            class="h-20 w-20 bg-electric/10 rounded-[2rem] border border-electric/20 flex items-center justify-center mx-auto mb-8 shadow-neon shadow-electric/10"
                        >
                            <Brain class="h-10 w-10 text-electric" />
                        </div>
                        <h2
                            class="font-display text-4xl text-white uppercase italic font-black mb-2"
                        >
                            {{ selectedLocation?.display_name }}
                        </h2>
                        <p
                            class="text-white/50 italic mb-10 leading-relaxed text-lg max-w-lg mx-auto"
                        >
                            "{{ currentRiddle?.content }}"
                        </p>
                        <div class="relative max-w-md mx-auto">
                            <input
                                v-model="riddleAnswer"
                                @keyup.enter="submitRiddle"
                                placeholder="DÉCRYPTEZ LE SECRET..."
                                class="w-full h-16 rounded-2xl bg-white/5 border border-white/10 px-8 text-white outline-none focus:border-electric transition-all text-center tracking-widest font-black placeholder:text-white/20"
                            />
                        </div>
                        <NeonButton
                            size="xl"
                            class="w-full max-w-md mt-6"
                            @click="submitRiddle"
                            >VALIDER LA RÉPONSE</NeonButton
                        >
                    </div>
                </div>
            </div>
        </Transition>

        <!-- VICTORY / SUCCESS OVERLAY -->
        <Transition name="fade-scale">
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 z-[160] bg-gaming-dark/95 backdrop-blur-3xl flex flex-col items-center justify-center p-6 overflow-hidden"
            >
                <!-- Victory Glow -->
                <div
                    class="absolute inset-0 bg-gradient-to-b from-electric/20 via-transparent to-success/10 pointer-events-none"
                ></div>
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-electric/10 rounded-full blur-[120px] animate-pulse"
                ></div>

                <div
                    class="w-full max-w-lg relative z-10 text-center space-y-10"
                >
                    <!-- Icon & Title -->
                    <div class="space-y-4">
                        <div class="relative inline-block">
                            <div
                                class="absolute inset-0 bg-electric blur-2xl opacity-30 animate-pulse"
                            ></div>
                            <div
                                class="relative h-24 w-24 rounded-[2rem] bg-electric/10 border-2 border-electric flex items-center justify-center mx-auto shadow-neon"
                            >
                                <Trophy class="h-12 w-12 text-electric" />
                            </div>
                        </div>
                        <h2
                            class="font-display text-5xl md:text-6xl text-white uppercase italic font-black tracking-tighter leading-none"
                        >
                            Mission
                            <span class="text-electric">Accomplie !</span>
                        </h2>
                        <div
                            v-if="selectedLocation"
                            class="space-y-2 animate-fade-up"
                        >
                            <h3
                                class="text-2xl font-display text-white uppercase tracking-tight"
                            >
                                {{ selectedLocation.name }}
                            </h3>
                            <p
                                class="text-sm text-white/60 italic leading-relaxed max-w-sm mx-auto"
                            >
                                " {{ selectedLocation.description }} "
                            </p>
                        </div>
                        <p
                            v-else
                            class="text-white/40 font-black uppercase tracking-[0.4em] text-xs"
                        >
                            Mission accomplie avec succès
                        </p>
                    </div>

                    <!-- Stars Animation -->
                    <div class="flex justify-center gap-4">
                        <Star
                            v-for="s in 3"
                            :key="s"
                            :class="
                                cn(
                                    'h-12 w-12 transition-all duration-1000 delay-[500ms]',
                                    s <= earnedStars
                                        ? 'text-warning fill-warning drop-shadow-neon scale-110'
                                        : 'text-white/5 opacity-20',
                                )
                            "
                        />
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="glass-strong p-6 rounded-3xl border border-white/10 space-y-1"
                        >
                            <div
                                class="text-[10px] text-white/40 font-black uppercase tracking-widest"
                            >
                                XP GAGNÉS
                            </div>
                            <div class="text-3xl font-display text-electric">
                                +150 PX
                            </div>
                        </div>
                        <div
                            class="glass-strong p-6 rounded-3xl border border-white/10 space-y-1"
                        >
                            <div
                                class="text-[10px] text-white/40 font-black uppercase tracking-widest"
                            >
                                DURÉE
                            </div>
                            <div class="text-3xl font-display text-success">
                                {{ formatTime(gameTime) }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="space-y-4 pt-4">
                        <NeonButton
                            size="xl"
                            class="w-full h-16 rounded-2xl text-base tracking-[0.3em]"
                            @click="
                                goBackToLobby();
                                showSuccessModal = false;
                            "
                        >
                            CONTINUER L'AVENTURE
                        </NeonButton>

                        <button
                            v-if="selectedLocation?.history"
                            @click="showHistoryModal = true"
                            class="w-full py-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-500 font-black text-xs tracking-[0.2em] hover:bg-amber-500/20 transition-all flex items-center justify-center gap-2"
                        >
                            <BookOpen class="h-4 w-4" />
                            LIRE L'HISTOIRE DU LIEU
                        </button>

                        <button
                            @click="goBackToLobby"
                            class="group flex items-center justify-center gap-2 mx-auto text-white/30 hover:text-electric transition-colors uppercase font-black tracking-widest text-[10px] pt-2"
                        >
                            RETOUR AU LOBBY
                            <ArrowRight
                                class="h-4 w-4 group-hover:translate-x-2 transition-transform"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL HISTOIRE (IN GAME) -->
        <Modal :show="showHistoryModal" @close="showHistoryModal = false">
            <div
                class="p-8 bg-gaming-darker border border-amber-500/30 rounded-[2.5rem] max-w-2xl mx-auto relative overflow-hidden shadow-2xl"
            >
                <div
                    class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl"
                />

                <div class="relative z-10 space-y-6">
                    <!-- Image du lieu -->
                    <div
                        class="relative h-48 w-full rounded-3xl overflow-hidden border border-white/10 shadow-2xl"
                    >
                        <AppImage
                            :src="
                                selectedLocation?.location_images?.[0]
                                    ?.image_path ||
                                selectedLocation?.cover_image
                            "
                            fallback="/images/placeholders/location.jpg"
                            class="w-full h-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-transparent to-transparent"
                        ></div>
                    </div>

                    <div class="flex items-center gap-4 mb-2">
                        <div
                            class="h-14 w-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 shadow-neon-sm"
                        >
                            <BookOpen class="h-7 w-7" />
                        </div>
                        <div>
                            <div
                                class="text-[10px] text-amber-500 font-black uppercase tracking-[0.3em]"
                            >
                                Chroniques de la Cité
                            </div>
                            <h2
                                class="font-display text-3xl text-white uppercase italic font-black"
                            >
                                {{ selectedLocation?.name }}
                            </h2>
                        </div>
                    </div>

                    <div
                        class="space-y-4 text-white/80 leading-relaxed text-sm max-h-[40vh] overflow-y-auto pr-4 custom-scrollbar"
                    >
                        <p class="font-bold text-amber-500/80 italic">
                            " {{ selectedLocation?.description }} "
                        </p>
                        <div
                            class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent my-6"
                        ></div>
                        <div
                            class="bg-white/5 p-6 rounded-3xl border border-white/5 prose prose-invert max-w-none"
                        >
                            {{ selectedLocation?.history }}
                        </div>
                    </div>

                    <div class="pt-4">
                        <button
                            @click="showHistoryModal = false"
                            class="w-full py-4 rounded-2xl bg-amber-500 text-black font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all shadow-neon"
                        >
                            RETOUR AU SUCCÈS
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Indice -->
        <div
            v-if="showHintModal"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        >
            <!-- Backdrop ultra fort -->
            <div class="absolute inset-0 bg-black/90 backdrop-blur-3xl"></div>

            <div
                class="relative glass-strong max-w-md w-full p-8 rounded-3xl border border-white/10 shadow-2xl text-center"
            >
                <h3 class="text-2xl font-display font-bold text-white mb-3">
                    Débloquer un indice ?
                </h3>
                <p class="text-sm text-muted-foreground mb-8">
                    Cela va coûter
                    <span class="text-red-400 font-bold">-50 XP</span>.<br />
                    Es-tu sûr ?
                </p>

                <div class="flex gap-4 justify-center">
                    <button
                        @click="showHintModal = false"
                        class="px-8 py-3 rounded-2xl border border-white/10 text-white hover:bg-white/5 transition-all"
                    >
                        Annuler
                    </button>
                    <button
                        @click="handleUseHint"
                        class="px-8 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-black font-bold hover:brightness-110 transition-all"
                    >
                        Oui, je veux l'indice
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Out -->
        <div
            v-if="outModal"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        >
            <!-- Backdrop très fort -->
            <div class="absolute inset-0 bg-black/85 backdrop-blur-3xl"></div>

            <div
                class="relative max-w-md w-full p-8 md:p-10 rounded-3xl border border-electric/30 bg-gaming-darker/95 backdrop-blur-2xl shadow-2xl text-center"
            >
                <h3
                    class="text-4xl font-display font-black text-red-500 tracking-wider mb-2"
                >
                    Voulez-vous Quitter la partie ??
                </h3>
                <div class="flex gap-4 justify-center">
                <button
                    @click="resumeGame"
                    class="w-full py-4 rounded-2xl bg-electric border-gay-300 text-primary font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all"
                >
                    Annuler
                </button>
                <button
                    @click="goBackToLobby()"
                    class="w-full py-4 rounded-2xl animate-pulse bg-red-500/20 text-red-500 border border-red-500/30 font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all"
                >
                    Quitter
                </button>
                </div>
            </div>
        </div>
        <!-- Modal Pause -->
        <div
            v-if="pauseModal"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        >
            <!-- Backdrop très fort -->
            <div class="absolute inset-0 bg-black/85 backdrop-blur-3xl"></div>

            <div
                class="relative max-w-md w-full p-8 md:p-10 rounded-3xl border border-electric/30 bg-gaming-darker/95 backdrop-blur-2xl shadow-2xl text-center"
            >
                <h3
                    class="text-4xl font-display font-black text-white tracking-wider mb-2"
                >
                    PAUSE
                </h3>
                <p class="text-muted-foreground mb-10">
                    La mission est en pause
                </p>

                <button
                    @click="resumeGame"
                    class="w-full py-4 rounded-2xl animate-pulse bg-amber-500/20 text-amber-500 border border-amber-500/30 font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all"
                >
                    REPRENDRE LA MISSION
                </button>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.glass-strong {
    background: rgba(15, 15, 25, 0.8);
    backdrop-filter: blur(20px);
}
.shadow-neon-blue {
    box-shadow: 0 0 20px rgba(0, 112, 255, 0.2);
}
.shadow-neon-blue-lg {
    box-shadow: 0 0 40px rgba(0, 112, 255, 0.3);
}
.shadow-neon-success {
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}
.shadow-neon-warning {
    box-shadow: 0 0 20px rgba(234, 179, 8, 0.3);
}
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translate(-50%, -20px);
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

.drop-shadow-neon {
    filter: drop-shadow(0 0 8px rgba(0, 112, 255, 0.5));
}
</style>
