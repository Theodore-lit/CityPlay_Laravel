<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import SiteLayout from "@/Layouts/SiteLayout.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import MapComponent from "@/Components/MapComponent.vue";
import Modal from "@/Components/Modal.vue";
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
} from "lucide-vue-next";
import { cn } from "@/lib/utils";

const props = defineProps({
    city: Object,
    locations: Array,
    currentSession: Object,
    initialTeamPositions: Array,
    auth: Object,
});

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
        showSuccessModal.value
    );
});

// Modal Succès
const showSuccessModal = ref(false);
const earnedStars = ref(3);
const earnedXp = ref(150);

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
    pauseModal.value = !pauseModal.value;
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
    if (isNearLocation.value && currentTarget.value) {
        isPaused.value = true; // Arrête le chrono dès la validation du lieu
        showGameToast("Position confirmée !", "success");
        setTimeout(() => {
            selectedLocation.value = currentTarget.value;
            riddleType.value = "site";
            const locWithEnigmas = props.locations.find(
                (l) => l.id === selectedLocation.value.id,
            );
            const enigmas = locWithEnigmas?.enigmas || [];
            currentRiddle.value =
                enigmas.find((e) => e.is_site_specific) || enigmas[0];

            if (currentRiddle.value?.questions?.length > 0) {
                startQuestionnaire();
            } else {
                showRiddleModal.value = true;
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
    console.log(activeEnigma?.indices);

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
    isPaused.value = false;
    gameTime.value = 0;
    // Nettoyer le localStorage lors de la sortie définitive si on veut repartir de zéro,
    // ou simplement quitter vers le lobby.
    router.get(route("player.cities"));
};
</script>

<template>
    <Head :title="`${city?.name || 'Aventure'} — Mode Aventure`" />

    <SiteLayout hideFooter>
        <div
            class="mx-auto w-full px-2 sm:px-6 py-4 md:py-6 pb-28 md:pb-12 h-screen flex flex-col bg-gaming-darker"
        >
            <button
                @click="outGame"
                class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20"
            >
                <ChevronLeft class="h-6 w-6" />
            </button>
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
                                <p class="text-white text-lg">Pause</p>
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
            <div
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
        <Modal :show="showRiddleModal" @close="showRiddleModal = false">
            <div
                class="p-8 bg-gaming-darker border border-electric/20 rounded-[2.5rem] max-w-lg mx-auto"
            >
                <h2>
                    Félicitaion d'avoir gagner la partie. Voici des bonus pour
                    vous
                </h2>
                <div v-if="isQuestionnaireActive" class="space-y-6">
                    <div
                        class="text-[10px] text-electric font-black uppercase tracking-widest"
                    >
                        Question {{ currentQuestionIndex + 1 }} /
                        {{ currentRiddle?.questions?.length }}
                    </div>
                    <div
                        class="p-6 rounded-3xl bg-white/5 border border-white/10 text-lg text-white font-display"
                    >
                        {{
                            currentRiddle?.questions?.[currentQuestionIndex]
                                ?.question_text
                        }}
                    </div>
                    <div class="grid gap-3">
                        <button
                            v-for="(option, idx) in currentRiddle?.questions?.[
                                currentQuestionIndex
                            ]?.options"
                            :key="idx"
                            @click="selectQuestionOption(option)"
                            class="w-full p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-electric transition-all text-left"
                        >
                            {{ option.option_text }}
                        </button>
                    </div>
                </div>
                <div v-else class="space-y-6">
                    <h2 class="font-display text-2xl">
                        {{ selectedLocation?.display_name }}
                    </h2>
                    <p
                        class="p-6 rounded-3xl bg-white/5 border border-white/10 italic text-foreground/90 leading-relaxed"
                    >
                        "{{ currentRiddle?.content }}"
                    </p>
                    <input
                        v-model="riddleAnswer"
                        @keyup.enter="submitRiddle"
                        placeholder="Votre réponse..."
                        class="w-full h-14 rounded-2xl bg-gaming-darker border border-electric/30 px-6 text-white outline-none focus:border-electric transition-all"
                    />
                    <NeonButton class="w-full" @click="submitRiddle"
                        >Soumettre</NeonButton
                    >
                </div>
            </div>
        </Modal>

        <Modal
            :show="showSuccessModal"
            @close="showSuccessModal = false"
            class="fixed inset-0 bg-black/80 backdrop-blur-2xl z-[100]"
        >
            <div
                class="relative z-[110] p-10 bg-gaming-darker border border-electric rounded-[3rem] text-center max-w-sm mx-auto"
            >
                <Trophy class="h-16 w-16 text-electric mx-auto mb-6" />
                <h2 class="font-display text-3xl mb-2 text-white">
                    FÉLICITATIONS !
                </h2>
                <div class="flex justify-center gap-2 mb-8">
                    <Star
                        v-for="s in 3"
                        :key="s"
                        :class="
                            cn(
                                'h-8 w-8',
                                s <= earnedStars
                                    ? 'text-yellow-400 fill-yellow-400'
                                    : 'text-white/10',
                            )
                        "
                    />
                </div>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div
                        class="p-4 rounded-2xl bg-white/5 border border-white/10"
                    >
                        <div
                            class="text-[10px] text-muted-foreground uppercase"
                        >
                            XP GAGNÉS
                        </div>
                        <div class="text-xl font-display text-electric">
                            +150 PX
                        </div>
                    </div>
                    <div
                        class="p-4 rounded-2xl bg-white/5 border border-white/10"
                    >
                        <div
                            class="text-[10px] text-muted-foreground uppercase"
                        >
                            DURÉE
                        </div>
                        <div class="text-xl font-display text-success">
                            {{ formatTime(gameTime) }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <NeonButton
                        size="xl"
                        class="w-full rounded-2xl"
                        @click="showSuccessModal = false"
                        >CONTINUER</NeonButton
                    >
                    <button
                        @click="goBackToLobby"
                        class="text-xs text-electric font-black uppercase tracking-[0.2em] hover:text-white transition-colors"
                    >
                        REJOUER / LOBBY
                    </button>
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
