<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import SiteLayout from "@/Layouts/SiteLayout.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import MapComponent from "@/Components/MapComponent.vue";
import confetti from "canvas-confetti";
import {
    MapPin,
    Navigation,
    Lock,
    Target,
    ChevronLeft,
    Star,
    Clock,
    Play,
    Info,
    CheckCircle2,
    Trophy,
    Brain,
    Pause,
    HelpCircle,
    Eye,
    BookOpen,
} from "lucide-vue-next";
import { cn } from "@/lib/utils";

const props = defineProps({
    city: Object,
    locations: Array,
    currentSession: Object,
    initialTeamPositions: Array,
    auth: Object,
    lobbySessionId: {
        type: String,
        required: true,
    },
});
console.log(props.lobbySessionId);

// --- BOUSSOLE (Compass) ---
const compassUnlocked = ref(
    localStorage.getItem("cityplay_compass_unlocked") === "1",
); // persisted locally
const compassActive = ref(false); // whether the compass is currently displayed
const compassCost = 1000; // coût en XP pour débloquer

// Calculer l'angle de la boussole en degrés (0 = nord)
function calculateBearing(lat1, lon1, lat2, lon2) {
    // formule de cap initial (bearing) en degrés
    const φ1 = (lat1 * Math.PI) / 180;
    const φ2 = (lat2 * Math.PI) / 180;
    const Δλ = ((lon2 - lon1) * Math.PI) / 180;
    const y = Math.sin(Δλ) * Math.cos(φ2);
    const x =
        Math.cos(φ1) * Math.sin(φ2) -
        Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
    const θ = Math.atan2(y, x);
    let bearing = (θ * 180) / Math.PI; // en degrés
    bearing = (bearing + 360) % 360; // normaliser 0-360
    return bearing;
}

const needleAngle = ref(0);

// Méthode pour acheter/débloquer la boussole côté front (tentative côté serveur si route présente)
const purchaseCompass = () => {
    const userXp = usePage().props.auth.user.xp || 0;
    if (userXp < compassCost) {
        showGameToast("XP insuffisants pour acheter la boussole.", "error");
        return;
    }

    // Envoi vers le backend pour décrémenter (le backend renvoie JSON)
    router.post(
        route("player.purchase-compass"),
        {},
        {
            onSuccess: (resp) => {
                // marquer localement comme débloqué et activer
                compassUnlocked.value = true;
                compassActive.value = true;
                localStorage.setItem("cityplay_compass_unlocked", "1");
                showGameToast("Boussole débloquée (-1000 XP)", "success");
            },
            onError: () => {
                showGameToast(
                    "Impossible d'acheter la boussole pour le moment.",
                    "error",
                );
            },
        },
    );
};

// --- ÉTATS RÉACTIFS ---
const userPosition = ref(null);
const teamMembers = ref(props.initialTeamPositions || []);
// const isPaused = ref(false);
const totalErrors = ref(0);
const timerInterval = ref(null);
const mapRef = ref(null);
const showHintModal = ref(false);
const outModal = ref(false);
const wasPausedBeforeOutModal = ref(false);

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
const isPaused = ref(savedData?.isPaused !== undefined ? savedData.isPaused : false);
const pauseModal = ref(isPaused.value);
const gameTime = ref(
    savedData?.gameTime !== undefined ? savedData.gameTime : 0,
);
const usedHints = ref(savedData?.usedHints || 0);
const showHint = ref(savedData?.showHint || false);

// Modal Succès
const showSuccessModal = ref(false);
const showHistoryModal = ref(false);
const earnedStars = ref(3);
const earnedXp = ref(150);

// Toast
const toast = ref({ show: false, message: "", type: "info" });

// Computed pour vérifier si un overlay est actif
const isAnyOverlayActive = computed(() => {
    return (
        pauseModal.value ||
        outModal.value ||
        showHintModal.value ||
        showRiddleModal.value ||
        showSuccessModal.value ||
        showHistoryModal.value
    );
});

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
                // Mettre à jour l'angle de la boussole si nécessaire
                if (currentTarget.value) {
                    needleAngle.value = calculateBearing(
                        userPosition.value.lat,
                        userPosition.value.lng,
                        currentTarget.value.latitude,
                        currentTarget.value.longitude,
                    );
                }
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

console.log(activeEnigma.value)

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

const runConfetti = () => {
    confetti({
        particleCount: 180,
        spread: 85,
        origin: { y: 0.6 },
        colors: ["#60a5fa", "#38bdf8", "#facc15", "#10b981"],
    });
    confetti({
        particleCount: 120,
        spread: 140,
        origin: { y: 0.6 },
        scalar: 0.8,
        colors: ["#fff", "#e0f2fe", "#fde68a"],
    });
};

const togglePause = () => {
    // On inverse l'état de la pause
    isPaused.value = !isPaused.value;
    
    // La modal doit correspondre EXACTEMENT à l'état de la pause
    pauseModal.value = isPaused.value;
};

const resumeGame = () => {
    outModal.value = false;
    pauseModal.value = false; // On s'assure de fermer la modal de pause aussi
    isPaused.value = false;   // On relance le jeu (pause = false)
};

const openOutModal = () => {
    wasPausedBeforeOutModal.value = isPaused.value;
    outModal.value = true;
    isPaused.value = true;
};


const handleUseHint = () => {
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

const askIndice = () => {
    if (usePage().props.auth.user.xp < 50) {
        showGameToast("XP insuffisants pour un indice.", "error");
        return;
    }
    showHintModal.value = true;
}

const verifyPosition = () => {
    if (isNearLocation.value && currentTarget.value) {
        isPaused.value = true; // Arrête le chrono dès la validation du lieu
        showGameToast("Position confirmée !", "success");
        setTimeout(() => {
            selectedLocation.value = currentTarget.value;
            riddleType.value = "site";
            const locWithEnigmas = props.locations.find(
                (l) => l.id === selectedLocation.value.id,
            );
            const questions = activeEnigma.value?.questions || [];
            // currentRiddle.value =
            //     enigmas.find((e) => e.is_site_specific) || enigmas[0];

            if (questions?.length > 0) {
                startQuestionnaire();
            } else {
                // Enigme sans questions : valide directement et affiche succès
                handleSuccess();
            }
        }, 800);
    } else {
        const distMsg =
            distanceToClosest.value !== "---"
                ? ` (${distanceToClosest.value})`
                : "";
        showGameToast(
            `Signal trop faible. Rapprochez-vous du point cible.${distMsg}`,
            "warning",
        );
    }
};

// Observer la cible ou la position utilisateur pour mettre à jour l'aiguille
watch([userPosition, currentTarget], ([u, t]) => {
    if (!u || !t) return;
    needleAngle.value = calculateBearing(u.lat, u.lng, t.latitude, t.longitude);
});

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
        if (
            currentQuestionIndex.value <
            currentRiddle.value.questions.length - 1
        ) {
            setTimeout(() => {
                currentQuestionIndex.value++;
            }, 1000);
        } else {
            handleSuccess();
        }
    } else {
        totalErrors.value++;
        showGameToast("Mauvaise réponse !", "error");
        // On passe quand même à la suivante pour la logique de quiz
        if (
            currentQuestionIndex.value <
            currentRiddle.value.questions.length - 1
        ) {
            setTimeout(() => {
                currentQuestionIndex.value++;
            }, 1000);
        } else {
            handleSuccess();
        }
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
            xp: 150,
            duration: gameTime.value, // Envoyer la durée finale
        },
        {
            onSuccess: () => {
                showRiddleModal.value = false;
                showSuccessModal.value = true;
                isQuestionnaireActive.value = false;
                runConfetti();
            },
        },
    );
};

const goBackToLobby = () => {
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
    startTimer();
    updateGPS();

    if (compassUnlocked.value) {
        compassActive.value = true;
    }
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

const outGame = () => {
    outModal.value = false;
    localStorage.removeItem(storageKey);
    // Nettoyer le localStorage lors de la sortie définitive si on veut repartir de zéro,
    // ou simplement quitter vers le lobby.
    router.get(route("player.mission-lobby", props.lobbySessionId));
};
</script>

<template>
    <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

    <SiteLayout hideFooter>
        <div
            class="mx-auto w-full px-2 sm:px-6 py-4 md:py-6 pb-28 md:pb-12 h-screen flex flex-col bg-gaming-darker"
        >
            <button
                @click="openOutModal"
                class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20"
            >
                <ChevronLeft class="h-6 w-6" />
            </button>
            <!-- MODE AVENTURE -->
            <div
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
                                @click="askIndice"
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
                            class="absolute inset-0 rounded-full border-2 border-electric/20 overflow-hidden shadow-neon-blue-lg bg-white"
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
                        <!-- Boussole overlay: achat / activation + affichage -->
                        <!-- <div class="absolute left-3 bottom-3 z-30">
                            <div
                                v-if="!compassUnlocked"
                                class="flex items-center gap-2"
                            >
                                <button
                                    @click="purchaseCompass"
                                    class="px-3 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-400 text-white font-bold shadow-neon"
                                >
                                    Acheter boussole -1000 PX
                                </button>
                            </div>
                            <div v-else class="flex items-center gap-2">
                                <button
                                    @click="compassActive = !compassActive"
                                    :class="[
                                        'p-3 rounded-full border-2',
                                        compassActive
                                            ? 'bg-electric/20 border-electric text-white'
                                            : 'bg-white/6 border-white/10 text-white/80',
                                    ]"
                                >
                                    Simple icône de boussole
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            :stroke-width="2"
                                            stroke="currentColor"
                                            class="opacity-60"
                                        />
                                        <path
                                            d="M12 6 L15 12 L12 15 L9 12 Z"
                                            fill="currentColor"
                                            class="opacity-90"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div> -->

                        <!-- Visual compass element -->
                        <!-- <div
                            v-if="compassActive && compassUnlocked"
                            class="absolute inset-0 z-25 flex items-center justify-center pointer-events-none"
                        >
                            <div
                                class="h-44 w-44 rounded-full bg-gradient-to-br from-black/60 to-white/5 border border-electric/30 p-3 shadow-2xl flex items-center justify-center backdrop-blur-sm"
                            >
                                <div
                                    class="relative h-full w-full flex items-center justify-center"
                                >
                                    <div
                                        class="absolute inset-0 rounded-full border-2 border-white/10 flex items-center justify-center"
                                    >
                                        <div
                                            class="w-full h-[1px] bg-white/5 absolute"
                                        ></div>
                                        <div
                                            class="h-full w-[1px] bg-white/5 absolute"
                                        ></div>
                                    </div>

                                    <div
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <div
                                            :style="{
                                                transform: `rotate(${needleAngle}deg)`,
                                            }"
                                            class="transition-transform duration-700 ease-out flex items-center justify-center"
                                        >
                                            <svg
                                                class="h-32 w-32"
                                                viewBox="0 0 100 100"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <defs>
                                                    <linearGradient
                                                        id="needleGradient"
                                                        x1="0%"
                                                        y1="0%"
                                                        x2="0%"
                                                        y2="100%"
                                                    >
                                                        <stop
                                                            offset="0%"
                                                            stop-color="#ff7a7a"
                                                        />
                                                        <stop
                                                            offset="100%"
                                                            stop-color="#f43f5e"
                                                        />
                                                    </linearGradient>
                                                </defs>

                                                <path
                                                    d="M50 10L58 50L50 46L42 50L50 10Z"
                                                    fill="url(#needleGradient)"
                                                />

                                                <path
                                                    d="M50 90L42 50L50 54L58 50L50 90Z"
                                                    fill="#10b981"
                                                    fill-opacity="0.9"
                                                />

                                                <circle
                                                    cx="50"
                                                    cy="50"
                                                    r="3.5"
                                                    fill="white"
                                                    class="shadow-sm"
                                                />
                                                <circle
                                                    cx="50"
                                                    cy="50"
                                                    r="1.5"
                                                    fill="#1e293b"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <span
                                        class="absolute top-1 text-[10px] font-bold text-white/40"
                                        >N</span
                                    >
                                    <span
                                        class="absolute bottom-1 text-[10px] font-bold text-white/20"
                                        >S</span
                                    >
                                </div>
                            </div>
                        </div> -->
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
                                class="flex cursor-pointer items-center gap-1 rounded-lg p-1 bg-amber-600"
                            >
                                <p v-if="!isPaused" class="text-white text-lg">Pause</p>
                                <p v-else class="text-white text-lg">Commencer</p>
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

        <!-- MODALS -->
        <Transition name="fade-scale">
            <div
                v-if="showRiddleModal"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-3xl rounded-[2rem] border border-electric/20 bg-gradient-to-br from-gaming-darker/95 to-slate-950/95 p-8 shadow-[0_0_50px_rgba(34,197,94,0.18)]"
                >
                    <button
                        @click="showRiddleModal = false"
                        class="absolute right-5 top-5 h-11 w-11 rounded-2xl border border-white/10 bg-white/5 text-white transition hover:bg-white/10"
                        aria-label="Fermer"
                    >
                        ✕
                    </button>

                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="grid h-14 w-14 place-items-center rounded-3xl bg-electric/10 border border-electric/30 text-electric">
                                <Brain class="h-6 w-6" />
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-[0.35em] text-electric/70 font-black">
                                    Épreuve en cours
                                </div>
                                <h3 class="text-3xl font-display text-white">
                                    Résous le questionnaire
                                </h3>
                            </div>
                        </div>
                        <div class="rounded-3xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white/80">
                            Question {{ currentQuestionIndex + 1 }} / {{ currentRiddle?.questions?.length }}
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 text-lg text-white font-display shadow-inner shadow-black/20">
                            {{ currentRiddle?.questions?.[currentQuestionIndex]?.question_text }}
                        </div>

                        <div class="grid gap-4">
                            <button
                                v-for="(option, idx) in currentRiddle?.questions?.[currentQuestionIndex]?.options"
                                :key="idx"
                                @click="selectQuestionOption(option)"
                                :class="cn(
                                    'w-full rounded-3xl border px-6 py-4 text-left text-sm font-semibold transition-all',
                                    questionnaireAnswers.value[currentQuestionIndex.value] === option
                                        ? 'border-electric bg-electric/10 text-white'
                                        : 'border-white/10 bg-white/5 text-white hover:border-electric hover:bg-electric/10',
                                )"
                            >
                                {{ option.option_text }}
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-3 items-center justify-between text-xs uppercase tracking-[0.3em] text-white/60">
                            <span class="rounded-full bg-white/5 px-3 py-2 text-white/90">
                                Erreurs : {{ totalErrors }}
                            </span>
                            <span class="rounded-full bg-white/5 px-3 py-2 text-success">
                                Prochaine étape : {{ currentQuestionIndex + 1 === currentRiddle?.questions?.length ? 'Terminer' : 'Suivante' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade-scale">
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/90 backdrop-blur-3xl"
            >
                <div
                    class="relative w-full max-w-sm overflow-hidden rounded-[2.5rem] border border-electric/30 bg-gradient-to-br from-[#09101d] via-gaming-darker/95 to-slate-950/95 p-8 shadow-[0_0_60px_rgba(34,197,94,0.25)]"
                >
                    <!-- <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-electric to-success" />
                    <Trophy class="mx-auto mb-6 h-16 w-16 text-electric" />
                    <h2 class="font-display text-4xl text-white">Félicitations</h2>
                    <p class="mt-3 text-sm uppercase tracking-[0.3em] text-electric/70">Lieu validé</p>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 text-center">
                            <div class="text-[10px] uppercase tracking-[0.25em] text-white/50">XP gagnés</div>
                            <div class="mt-3 text-3xl font-display text-electric">+150</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 text-center">
                            <div class="text-[10px] uppercase tracking-[0.25em] text-white/50">Durée</div>
                            <div class="mt-3 text-3xl font-display text-success">{{ formatTime(gameTime) }}</div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-center gap-2 text-white/80">
                        <Star v-for="s in 3" :key="s" :class="cn('h-8 w-8', s <= earnedStars ? 'text-yellow-400 fill-yellow-400' : 'text-white/10')" />
                    </div>

                    <div class="mt-10 flex flex-col gap-3">
                        <button
                            @click="showHistoryModal = true"
                            class="w-full rounded-3xl border border-electric/30 bg-electric/10 px-4 py-3 text-sm uppercase tracking-[0.25em] text-electric transition hover:border-electric hover:bg-electric/20"
                        >
                            📖 Découvrir l'histoire
                        </button>
                        <button
                            @click="goBackToLobby"
                            class="w-full rounded-3xl border border-white/10 bg-white/5 px-4 py-3 text-sm uppercase tracking-[0.25em] text-white transition hover:border-white/30 hover:bg-white/10"
                        >
                            Retour au Lobby
                        </button>
                    </div> -->



                    <!-- <div class="relative z-10 space-y-6"> -->
                    <!-- Image du lieu -->
                    <div class="relative h-48 md:h-64 w-full rounded-3xl overflow-hidden border border-white/20 shadow-2xl">
                        <AppImage
                            :src="selectedLocation?.location_images?.[0]?.image_path || selectedLocation?.cover_image"
                            fallback="/images/placeholders/location.jpg"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-transparent to-transparent"></div>
                    </div>

                    <div class="flex items-center gap-4 mb-2">
                        <div class="h-14 w-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 shadow-neon-sm">
                            <BookOpen class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="text-[10px] text-amber-500 font-black uppercase tracking-[0.3em]">Chroniques de la Cité</div>
                            <h2 class="font-display text-3xl text-white uppercase italic font-black">{{ selectedLocation?.name }}</h2>
                        </div>
                    </div>

                    <div class="space-y-4 text-white/80 leading-relaxed text-sm">
                        <p class="font-bold text-amber-500/80 italic">" {{ selectedLocation?.description }} "</p>
                        <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent my-6"></div>
                        <div class="bg-white/5 p-6 rounded-3xl border border-white/10 prose prose-invert max-w-none">
                            {{ selectedLocation?.history }}
                        </div>
                    </div>

                    <div class="pt-4">
                        <button
                            @click="showHistoryModal = false"
                            class="w-full py-4 rounded-2xl bg-amber-500 text-black font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all shadow-neon"
                        >
                            FERMER LES ARCHIVES
                        </button>
                    </div>
                <!-- </div> -->
                </div>
            </div>
        </Transition>

        <!-- Modal Histoire -->
        <Transition name="fade-scale">
            <div
                v-if="showHistoryModal"
                class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/90 backdrop-blur-3xl"
            >
                <!-- <div class="relative z-10 space-y-6"> -->
                    <!-- Image du lieu -->
                    <div class="relative h-48 md:h-64 w-full rounded-3xl overflow-hidden border border-white/20 shadow-2xl">
                        <AppImage
                            :src="selectedLocation?.location_images?.[0]?.image_path || selectedLocation?.cover_image"
                            fallback="/images/placeholders/location.jpg"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-transparent to-transparent"></div>
                    </div>

                    <div class="flex items-center gap-4 mb-2">
                        <div class="h-14 w-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 shadow-neon-sm">
                            <BookOpen class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="text-[10px] text-amber-500 font-black uppercase tracking-[0.3em]">Chroniques de la Cité</div>
                            <h2 class="font-display text-3xl text-white uppercase italic font-black">{{ selectedLocation?.name }}</h2>
                        </div>
                    </div>

                    <div class="space-y-4 text-white/80 leading-relaxed text-sm">
                        <p class="font-bold text-amber-500/80 italic">" {{ selectedLocation?.description }} "</p>
                        <div class="h-px w-full bg-gradient-to-r from-transparent via-white/10 to-transparent my-6"></div>
                        <div class="bg-white/5 p-6 rounded-3xl border border-white/10 prose prose-invert max-w-none">
                            {{ selectedLocation?.history }}
                        </div>
                    </div>

                    <div class="pt-4">
                        <button
                            @click="showHistoryModal = false"
                            class="w-full py-4 rounded-2xl bg-amber-500 text-black font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all shadow-neon"
                        >
                            FERMER LES ARCHIVES
                        </button>
                    </div>
                <!-- </div> -->
            </div>
        </Transition>
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
                    class="text-4xl font-display font-black text-red-400 tracking-wider mb-3"
                >
                    Quitter la partie ?
                </h3>
                <p class="text-white/80 mb-8 text-sm leading-relaxed">
                    Si tu quittes maintenant, ta progression sera sauvegardée et tu reviendras au lobby.
                </p>
                <div class="flex gap-4 justify-center">
                    <button
                        @click="resumeGame"
                        class="w-full py-4 rounded-2xl bg-white/10 border border-white/20 text-white font-display font-bold text-lg tracking-widest hover:bg-white/20 transition-all"
                    >
                        Annuler
                    </button>
                    <button
                        @click="outGame"
                        class="w-full py-4 rounded-2xl bg-red-500/15 text-red-400 border border-red-500/30 font-display font-black text-lg tracking-widest hover:bg-red-500/25 transition-all"
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
                    class="text-4xl font-display font-black text-electric tracking-wider mb-3"
                >
                    PAUSE
                </h3>
                <p class="text-electric/80 mb-10 text-base leading-relaxed">
                    La mission est en pause. Reprends quand tu es prêt.
                </p>

                <button
                    @click="togglePause"
                    class="w-full py-4 rounded-2xl bg-electric text-black font-display font-bold text-lg tracking-widest hover:scale-105 active:scale-95 transition-all"
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
