<script setup>
/**
 * Interface d'administration d'une ville (Mairie).
 * Permet la gestion des lieux, énigmes et récompenses.
 *
 * Kamal: Ajout des champs de récompenses XP par lieu.
 */
import SiteLayout from "@/Layouts/SiteLayout.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import AppImage from "@/Components/AppImage.vue";
import GlowInput from "@/Components/GlowInput.vue";
import GpsSearchInput from "@/Components/GpsSearchInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    MapPin,
    Plus,
    Target,
    ChevronLeft,
    Map,
    Sparkles,
    HelpCircle,
    Save,
    Trash2,
    Image as ImageIcon,
    List,
    Settings,
    Info,
    Navigation,
    Brain,
    Edit2,
} from "lucide-vue-next";
import { ref } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps({
    city: Object,
    auth: Object,
});

const showLocationModal = ref(false);
const showImageModal = ref(false);
const selectedLocation = ref(null);
const showEnigmaModal = ref(false);
const showCityModal = ref(false);

/**
 * Formulaire de modification de la ville kamal
 */
const cityForm = useForm({
    name: props.city.name,
    description: props.city.description,
    latitude: props.city.latitude,
    longitude: props.city.longitude,
    radius_meters: props.city.radius_meters,
    image: null,
});

/**
 * Mise à jour des coordonnées lors de la sélection GPS kamal
 */
const onCitySelect = (data) => {
    cityForm.name = data.name;
    cityForm.latitude = data.lat;
    cityForm.longitude = data.lon;
};

/**
 * Soumission des modifications de la ville kamal
 */
const submitCity = () => {
    cityForm.post(route("mairie.cities.update", props.city.id), {
        forceFormData: true,
        onSuccess: () => {
            showCityModal.value = false;
        },
    });
};

/**
 * Formulaire de gestion d'un lieu kamal
const locationForm = useForm({
    latitude: '',
    longitude: '',
    radius_meters: 5000,
    reward_xp_arrival: 0, // Kamal
    reward_xp_enigma: 0,  // Kamal
    description: '',
    history: '',
    category: 'culture',
    image: null,
});

/**
 * Soumission d'une énigme kamal
 */
const submitEnigma = () => {
    enigmaForm.post(route("mairie.enigmas.store", selectedLocation.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEnigmaModal.value = false;
            enigmaForm.reset();
        },
    });
};

/**
 * Sélection d'un lieu via la recherche GPS kamal
 */
const onLocationSelect = (data) => {
    locationForm.name = data.name;
    locationForm.latitude = data.lat;
    locationForm.longitude = data.lon;
};

/**
 * Gestion des images des lieux kamal
 */
const imageForm = useForm({
    image: null,
});

const openImageModal = (location) => {
    selectedLocation.value = location;
    imageForm.image = null;
    showImageModal.value = true;
};

const submitImage = () => {
    imageForm.post(route("mairie.locations.image", selectedLocation.value.id), {
        onSuccess: () => {
            showImageModal.value = false;
            imageForm.reset();
        },
    });
};

/**
 * Formulaire de gestion d'une énigme kamal
 */
const enigmaForm = useForm({
    id: null,
    title: "",
    content: "",
    difficulty: "easy",
    answer: "",
    indices: ["", ""],
    reward_coins: 10,
    reward_hearts: 0,
    type: "text",
    image: null,
    is_site_specific: false,
    questions: [], // Questions à choix multiples kamal
});

/**
 * Gestion dynamique des questions de l'énigme kamal
 */
const addQuestion = () => {
    enigmaForm.questions.push({
        question_text: "",
        options: [
            { option_text: "", is_correct: true },
            { option_text: "", is_correct: false },
        ],
    });
};

const removeQuestion = (index) => {
    enigmaForm.questions.splice(index, 1);
};

const addOption = (qIndex) => {
    enigmaForm.questions[qIndex].options.push({
        option_text: "",
        is_correct: false,
    });
};

const removeOption = (qIndex, oIndex) => {
    enigmaForm.questions[qIndex].options.splice(oIndex, 1);
};

const setCorrectOption = (qIndex, oIndex) => {
    enigmaForm.questions[qIndex].options.forEach((opt, idx) => {
        opt.is_correct = idx === oIndex;
    });
};

/**
 * Soumission du formulaire de lieu kamal
 */
const submitLocation = () => {
    const url = locationForm.id
        ? route("mairie.locations.update", locationForm.id)
        : route("mairie.locations.store", props.city.id);

    locationForm.post(url, {
        onSuccess: () => {
            showLocationModal.value = false;
            locationForm.reset();
        },
    });
};

/**
 * Ouvre le modal de création/édition de lieu kamal
 */
const openLocationModal = (location = null) => {
    if (location) {
        locationForm.id = location.id;
        locationForm.name = location.name;
        locationForm.description = location.description;
        locationForm.history = location.history || '';
        locationForm.category = location.category;
        locationForm.latitude = location.latitude;
        locationForm.longitude = location.longitude;
        locationForm.radius_meters = location.radius_meters;
        locationForm.reward_xp_arrival = location.reward_xp_arrival || 0;
        locationForm.reward_xp_enigma = location.reward_xp_enigma || 0;
    } else {
        locationForm.reset();
        locationForm.id = null;
    }
    showLocationModal.value = true;
};

/**
 * Ouvre le modal de création/édition d'énigme kamal
 */
const openEnigmaModal = (location, enigma = null) => {
    selectedLocation.value = location;
    if (enigma) {
        enigmaForm.id = enigma.id;
        enigmaForm.title = enigma.title;
        enigmaForm.content = enigma.content;
        enigmaForm.difficulty = enigma.difficulty || "easy";
        enigmaForm.answer = enigma.answer;
        enigmaForm.indices =
            enigma.indices && enigma.indices.length > 0
                ? [...enigma.indices]
                : ["", ""];
        enigmaForm.reward_coins = enigma.reward_coins;
        enigmaForm.reward_hearts = enigma.reward_hearts;
        enigmaForm.type = enigma.type;
        enigmaForm.is_site_specific = enigma.is_site_specific;
        enigmaForm.image = null;

        // Chargement des questions kamal
        if (enigma.questions && enigma.questions.length > 0) {
            enigmaForm.questions = enigma.questions.map((q) => ({
                question_text: q.question_text,
                options: q.options.map((o) => ({
                    option_text: o.option_text,
                    is_correct: !!o.is_correct,
                })),
            }));
        } else {
            enigmaForm.questions = [];
        }
    } else {
        enigmaForm.reset();
        enigmaForm.id = null;
        enigmaForm.indices = ["", ""];
        enigmaForm.questions = [];
    }
    showEnigmaModal.value = true;
};

const addIndex = () => {
    enigmaForm.indices.push("");
};

const removeIndex = (index) => {
    enigmaForm.indices.splice(index, 1);
};

console.log(props.city)
</script>

<template>
    <Head :title="`Gérer ${city.name} — CityPlay HQ`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
            <!-- Header -->
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10"
            >
                <div class="flex items-center gap-5">
                    <Link
                        :href="route('mairie.city.hub', props.city.id)"
                        class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20"
                    >
                        <ChevronLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div
                            class="text-xs text-electric uppercase tracking-[0.2em] font-black mb-1"
                        >
                            Centre de Commandement
                        </div>
                        <h1
                            class="font-display text-3xl md:text-5xl text-foreground"
                        >
                            {{ city.name }}
                        </h1>
                    </div>
                </div>
                <div class="flex gap-3">
                    <NeonButton
                        @click="showCityModal = true"
                        variant="outline"
                        size="sm"
                    >
                        <Edit2 class="h-4 w-4 mr-2" />Modifier
                    </NeonButton>
                    <NeonButton @click="showLocationModal = true" size="sm">
                        <Plus class="h-4 w-4 mr-2" />Ajouter un Lieu
                    </NeonButton>
                    <Link
                        :href="route('admin.cities.quizzes', city.id)"
                        class="h-10 w-10 rounded-xl bg-dark/5 border border-dark/10 grid place-items-center text-dark hover:bg-purple-neon hover:text-dark hover:shadow-neon-purple transition-all"
                        title="Gérer les Quiz"
                    >
                        <Brain class="h-5 w-5" />
                    </Link>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Sidebar: City Overview -->
                <div class="lg:col-span-4 space-y-6">
                    <div
                        class="rounded-[2.5rem] glass overflow-hidden border border-white/10 group"
                    >
                        <div
                            class="h-48 w-full bg-gaming-darker relative overflow-hidden"
                        >
                            <AppImage
                                :src="city.image_url || city.image_path"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-700"
                            >
                                <template #placeholder>
                                    <div
                                        class="h-full w-full grid place-items-center opacity-20"
                                    >
                                        <Map class="h-12 w-12" />
                                    </div>
                                </template>
                            </AppImage>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-gaming-dark via-transparent to-transparent"
                            />
                        </div>

                        <div class="relative z-10 p-6">
                            <h2
                                class="font-display text-xl mb-3 flex items-center gap-2 text-foreground"
                            >
                                <Info class="h-5 w-5 text-electric" />Briefing
                                de la ville
                            </h2>
                            <p
                                class="text-sm text-muted-foreground leading-relaxed italic border-l-2 border-electric/30 pl-4"
                            >
                                {{
                                    city.description ||
                                    "Aucune description stratégique pour cette ville."
                                }}
                            </p>

                            <div class="mt-8 grid grid-cols-2 gap-4">
                                <div
                                    class="glass p-5 rounded-3xl text-center border border-white/5"
                                >
                                    <div
                                        class="text-3xl font-display text-electric"
                                    >
                                        {{ city.locations.length }}
                                    </div>
                                    <div
                                        class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mt-1"
                                    >
                                        Lieux Actifs
                                    </div>
                                </div>
                                <div
                                    class="glass p-5 rounded-3xl text-center border border-white/5"
                                >
                                    <div
                                        class="text-3xl font-display text-purple-neon"
                                    >
                                        {{
                                            city.locations.reduce(
                                                (acc, loc) =>
                                                    acc +
                                                    (loc.enigmas?.length || 0),
                                                0,
                                            )
                                        }}
                                    </div>
                                    <div
                                        class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mt-1"
                                    >
                                        Énigmes
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="glass-strong rounded-3xl p-6 border border-white/5"
                    >
                        <h3
                            class="font-display text-sm uppercase tracking-widest text-muted-foreground mb-4"
                        >
                            Statistiques de Joueurs
                        </h3>
                        <div class="space-y-4">
                            <div
                                class="flex justify-between items-center text-sm"
                            >
                                <span class="text-muted-foreground"
                                    >Explorateurs uniques</span
                                >
                                <span class="font-display text-foreground"
                                    >1,240</span
                                >
                            </div>
                            <div
                                class="flex justify-between items-center text-sm"
                            >
                                <span class="text-muted-foreground"
                                    >Taux de réussite</span
                                >
                                <span class="font-display text-success"
                                    >68%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content: Locations List -->
                <div class="lg:col-span-8 space-y-6">
                    <div
                        class="glass-strong rounded-[2rem] border border-white/5 overflow-hidden"
                    >
                        <div
                            class="p-8 border-b border-white/5 flex items-center justify-between bg-white/5"
                        >
                            <h2
                                class="font-display text-xl flex items-center gap-3 text-foreground"
                            >
                                <Navigation
                                    class="h-6 w-6 text-electric"
                                />Déploiement des Objectifs
                            </h2>
                            <span
                                class="px-4 py-1 rounded-full bg-electric/10 text-electric text-[10px] font-black tracking-widest border border-electric/20 uppercase"
                            >
                                En Ligne
                            </span>
                        </div>

                        <div class="divide-y divide-white/5">
                            <div
                                v-for="loc in city.locations"
                                :key="loc.id"
                                class="p-8 hover:bg-electric/5 transition-all duration-500 group"
                            >
                                <div
                                    class="flex flex-col md:flex-row md:items-start justify-between gap-6"
                                >
                                    <div
                                        class="flex items-start gap-5 group/loc"
                                    >
                                        <div
                                            class="h-14 w-14 rounded-2xl overflow-hidden bg-gaming-darker border border-white/10 shrink-0 shadow-inner group-hover:border-electric/40 transition-colors"
                                        >
                                            <AppImage
                                                :src="
                                                    loc.location_images?.[0]?.image_path ||
                                                    loc.cover_image ||
                                                    loc.image_urls?.[0] ||
                                                    loc.images?.[0]
                                                "
                                                class="h-full w-full object-cover group-hover/loc:scale-110 transition-transform"
                                            >
                                                <template #placeholder>
                                                    <div
                                                        class="h-full w-full grid place-items-center text-white/20"
                                                    >
                                                        <ImageIcon
                                                            class="h-7 w-7"
                                                        />
                                                    </div>
                                                </template>
                                            </AppImage>
                                        </div>
                                        <div>
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <h3
                                                    class="font-display text-xl text-foreground group-hover/loc:text-electric transition-colors"
                                                >
                                                    {{ loc.name }}
                                                </h3>
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded bg-white/10 text-muted-foreground uppercase font-bold"
                                                    >{{
                                                        loc.category ||
                                                        "Découverte"
                                                    }}</span
                                                >
                                            </div>
                                            <p
                                                class="text-sm text-muted-foreground mt-1 line-clamp-1"
                                            >
                                                {{
                                                    loc.description ||
                                                    "Aucune description."
                                                }}
                                            </p>
                                            <div
                                                class="text-[10px] font-mono text-electric/60 flex items-center gap-4 mt-3"
                                            >
                                                <span
                                                    class="flex items-center gap-1"
                                                >
                                                    <Target class="h-3 w-3" />
                                                    GPS: {{ loc.latitude }},
                                                    {{ loc.longitude }}
                                                </span>
                                                <span
                                                    class="flex items-center gap-1"
                                                >
                                                    <Navigation
                                                        class="h-3 w-3"
                                                    />
                                                    Rayon:
                                                    {{ loc.radius_meters }}m
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            @click="openEnigmaModal(loc)"
                                            class="h-11 px-5 rounded-xl bg-electric/10 text-electric border border-electric/30 hover:bg-electric/20 text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2"
                                        >
                                            <Plus class="h-4 w-4" /> Enigma
                                        </button>
                                        <button
                                            @click="openLocationModal(loc)"
                                            class="h-11 w-11 rounded-xl glass border-white/10 text-muted-foreground hover:text-white transition-all grid place-items-center"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="openImageModal(loc)"
                                            class="h-11 w-11 rounded-xl glass border-white/10 text-muted-foreground hover:text-white transition-all grid place-items-center"
                                        >
                                            <ImageIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive hover:bg-destructive hover:text-white transition-all grid place-items-center"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Enigmas List for this location -->
                                <div
                                    v-if="loc.enigmas?.length"
                                    class="mt-8 space-y-4 ml-0 md:ml-16"
                                >
                                    <div
                                        v-for="enigma in loc.enigmas"
                                        :key="enigma.id"
                                        class="p-5 rounded-3xl bg-purple-neon/5 border border-purple-neon/10 animate-fade-up relative overflow-hidden group/enigma"
                                    >
                                        <div
                                            class="absolute right-0 bottom-5 p-4 opacity-0 group-hover/enigma:opacity-100 transition-opacity"
                                        >
                                            <button
                                                @click="
                                                    openEnigmaModal(loc, enigma)
                                                "
                                                class="text-purple-neon hover:text-white transition-colors"
                                            >
                                                <Edit2 class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <div class="space-y-4 pl-7">
                                            <div
                                                class="h-48 w-full rounded-2xl bg-gaming-darker border border-white/10 overflow-hidden relative group/enigma_img"
                                            >
                                                <AppImage
                                                    :src="
                                                        enigma.image_url ||
                                                        enigma.image_path
                                                    "
                                                    class="w-full h-full object-cover group-hover/enigma_img:scale-105 transition-transform"
                                                >
                                                    <template #placeholder>
                                                        <div
                                                            class="w-full h-full flex flex-col items-center justify-center text-white/10"
                                                        >
                                                            <ImageIcon
                                                                class="h-12 w-12 mb-2"
                                                            />
                                                            <span
                                                                class="text-[10px] uppercase font-bold tracking-widest"
                                                                >Aucun visuel
                                                                tactique</span
                                                            >
                                                        </div>
                                                    </template>
                                                </AppImage>
                                            </div>
                                            <div>
                                                <div
                                                    class="text-[10px] text-purple-neon font-black uppercase tracking-[0.2em] mb-1"
                                                >
                                                    Briefing
                                                </div>
                                                <h4
                                                    class="font-display text-xl text-white"
                                                >
                                                    {{
                                                        enigma.title ||
                                                        "Énigme sans titre"
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-sm text-muted-foreground mt-2 line-clamp-2 italic"
                                                >
                                                    "{{ enigma.content }}"
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="mt-4 pl-7 flex flex-wrap gap-4 items-center"
                                        >
                                            <div
                                                class="text-[10px] font-bold text-success flex items-center gap-1.5 uppercase tracking-widest"
                                            >
                                                <Sparkles class="h-3.5 w-3.5" />
                                                Recompense:
                                                {{ enigma.reward_coins }}
                                                Coins
                                            </div>
                                            <div
                                                v-if="enigma.indices?.length"
                                                class="text-[10px] font-bold text-warning flex items-center gap-1.5 uppercase tracking-widest"
                                            >
                                                <List class="h-3.5 w-3.5" />
                                                {{
                                                    enigma.indices.length
                                                }}
                                                Indices configurés
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!city.locations.length"
                                class="p-24 text-center text-muted-foreground bg-gaming-darker/30"
                            >
                                <div
                                    class="h-20 w-20 rounded-full bg-electric/5 border border-electric/10 grid place-items-center mx-auto mb-6"
                                >
                                    <MapPin
                                        class="h-10 w-10 opacity-20 text-electric"
                                    />
                                </div>
                                <h3
                                    class="font-display text-xl text-foreground mb-2"
                                >
                                    Zone Vierge
                                </h3>
                                <p class="max-w-xs mx-auto text-sm">
                                    Aucun objectif tactique n'a été déployé dans
                                    cette ville.
                                </p>
                                <NeonButton
                                    @click="showLocationModal = true"
                                    variant="outline"
                                    size="sm"
                                    class="mt-8"
                                >
                                    Déployer le premier lieu
                                </NeonButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADD LOCATION MODAL -->
        <div
            v-if="showLocationModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl"
        >
            <div
                class="glass-strong rounded-[2.5rem] p-10 w-full max-w-xl border border-electric/30 animate-fade-up shadow-neon"
            >
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="h-12 w-12 rounded-2xl bg-electric/20 grid place-items-center text-electric"
                    >
                        <MapPin class="h-6 w-6" />
                    </div>
                    <h2 class="font-display text-3xl text-foreground">
                        Nouveau Secteur
                    </h2>
                </div>

                <form @submit.prevent="submitLocation" class="space-y-6">
                    <GpsSearchInput
                        v-model="locationForm.name"
                        label="Nom de reconnaissance (Recherche GPS)"
                        placeholder="Ex: Place de l'Indépendance"
                        :city-context="{
                            lat: city.latitude,
                            lon: city.longitude,
                            radius_meters: city.radius_meters,
                        }"
                        @select="onLocationSelect"
                        required
                    />

                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                            >Secteur Tactique (Brève description)</label
                        >
                        <textarea
                            v-model="locationForm.description"
                            class="w-full h-20 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-foreground placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none transition-all"
                            placeholder="Décrivez l'importance de ce lieu..."
                        ></textarea>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                            >Archives Historiques (Détails complets)</label
                        >
                        <textarea
                            v-model="locationForm.history"
                            class="w-full h-32 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-foreground placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none transition-all"
                            placeholder="Racontez l'histoire complète de ce lieu pour les explorateurs..."
                        ></textarea>
                    </div>

                    <div
                        v-if="locationForm.latitude"
                        class="p-4 rounded-2xl bg-electric/5 border border-electric/20 flex items-center justify-between animate-fade-up"
                    >
                        <div
                            class="text-[10px] text-electric font-bold uppercase tracking-widest flex items-center gap-2"
                        >
                            <Target class="h-4 w-4" /> Coordonnées Verrouillées
                        </div>
                        <div class="text-xs font-mono text-foreground">
                            {{ locationForm.latitude }},
                            {{ locationForm.longitude }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <GlowInput
                            label="Latitude (Manuel)"
                            type="number"
                            step="any"
                            v-model="locationForm.latitude"
                            required
                        />
                        <GlowInput
                            label="Longitude (Manuel)"
                            type="number"
                            step="any"
                            v-model="locationForm.longitude"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <GlowInput
                            label="Rayon (m)"
                            type="number"
                            v-model="locationForm.radius_meters"
                            required
                        />
                        <div class="space-y-2">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                                >Catégorie</label
                            >
                            <select
                                v-model="locationForm.category"
                                class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-foreground focus:border-electric outline-none appearance-none"
                            >
                                <option value="historique">Historique</option>
                                <option value="culturel">Culturel</option>
                                <option value="nature">Nature</option>
                                <option value="moderne">Moderne</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-4">
                        <NeonButton
                            type="button"
                            variant="outline"
                            class="flex-1"
                            @click="showLocationModal = false"
                        >
                            Abandonner</NeonButton
                        >
                        <NeonButton
                            type="submit"
                            class="flex-1"
                            :disabled="
                                locationForm.processing ||
                                !locationForm.latitude
                            "
                            >Confirmer le Déploiement
                        </NeonButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- ENIGMA MODAL -->
        <div
            v-if="showEnigmaModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl"
        >
            <div
                class="glass-strong rounded-[2.5rem] p-10 w-full max-w-2xl border border-purple-neon/30 animate-fade-up shadow-purple max-h-[90vh] overflow-y-auto custom-scrollbar relative"
            >
                <div
                    class="absolute inset-0 grid-bg opacity-10 pointer-events-none"
                />

                <div class="flex items-center gap-4 mb-8 relative z-10">
                    <div
                        class="h-12 w-12 rounded-2xl bg-purple-neon/20 grid place-items-center text-purple-neon"
                    >
                        <HelpCircle class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="font-display text-3xl text-foreground">
                            {{
                                enigmaForm.id
                                    ? "Éditer l'Énigme"
                                    : "Nouvelle Énigme"
                            }}
                        </h2>
                        <p
                            class="text-xs text-muted-foreground uppercase tracking-widest mt-1"
                        >
                            Cible: {{ selectedLocation?.name }}
                        </p>
                    </div>
                </div>

                <form
                    @submit.prevent="submitEnigma"
                    class="space-y-6 relative z-10"
                >
                    <div class="grid grid-cols-2 gap-6">
                        <GlowInput
                            label="Titre stratégique"
                            v-model="enigmaForm.title"
                            placeholder="Ex: Le Secret du Palais"
                            required
                        />
                        <div class="space-y-2">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                                >Difficulté</label
                            >
                            <select
                                v-model="enigmaForm.difficulty"
                                class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white focus:border-purple-neon outline-none"
                            >
                                <option value="easy">Facile (Déblocage)</option>
                                <option value="medium">Moyen (Tactique)</option>
                                <option value="hard">
                                    Difficile (Légendaire)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                                >Type de Verrou</label
                            >
                            <select
                                v-model="enigmaForm.type"
                                class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white focus:border-purple-neon outline-none"
                            >
                                <option value="text">Question textuelle</option>
                                <option value="qr">Scan QR Code</option>
                                <option value="image">
                                    Reconnaissance Image
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <input
                                type="checkbox"
                                id="is_site"
                                v-model="enigmaForm.is_site_specific"
                                class="rounded bg-gaming-darker border-purple-neon/30 text-purple-neon focus:ring-purple-neon"
                            />
                            <label
                                for="is_site"
                                class="text-xs text-white font-bold cursor-pointer"
                                >Énigme sur site (GPS requis)</label
                            >
                        </div>
                    </div> -->

                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                            >Visuel Tactique (Énigme)</label
                        >
                        <input
                            type="file"
                            @change="enigmaForm.image = $event.target.files[0]"
                            class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-purple-neon/10 file:text-purple-neon hover:file:bg-purple-neon/20 transition-all"
                        />
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1"
                            >Contenu de l'Énigme</label
                        >
                        <textarea
                            v-model="enigmaForm.content"
                            class="w-full h-24 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-purple-neon outline-none resize-none transition-all"
                            placeholder="L'énigme que le joueur doit résoudre..."
                            required
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <GlowInput
                            label="Réponse attendue (Navigation)"
                            v-model="enigmaForm.answer"
                            placeholder="La réponse pour débloquer..."
                            required
                        />
                        <div class="grid grid-cols-2 gap-2">
                            <GlowInput
                                label="XP"
                                type="number"
                                v-model="enigmaForm.reward_coins"
                                required
                            />
                            <GlowInput
                                label="Vies ❤️"
                                type="number"
                                v-model="enigmaForm.reward_hearts"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-1">
                            <label
                                class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black"
                                >Indices tactiques</label
                            >
                            <button
                                type="button"
                                @click="addIndex"
                                class="text-xs text-purple-neon hover:underline font-bold flex items-center gap-1"
                            >
                                <Plus class="h-3 w-3" /> Ajouter un indice
                            </button>
                        </div>
                        <div
                            v-for="(index, i) in enigmaForm.indices"
                            :key="i"
                            class="flex gap-2"
                        >
                            <div class="flex-1">
                                <input
                                    v-model="enigmaForm.indices[i]"
                                    class="w-full h-11 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-purple-neon outline-none"
                                    :placeholder="`Indice #${i + 1}`"
                                />
                            </div>
                            <button
                                type="button"
                                @click="removeIndex(i)"
                                class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive grid place-items-center hover:bg-destructive hover:text-white transition-all"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- QUESTIONNAIRE SECTION -->
                    <div class="space-y-6 pt-6 border-t border-white/10">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="font-display text-lg text-foreground"
                                >
                                    Questionnaire de Site
                                </h3>
                                <p
                                    class="text-[10px] text-muted-foreground uppercase tracking-widest"
                                >
                                    Questions posées après validation GPS
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="addQuestion"
                                class="h-10 px-4 rounded-xl bg-purple-neon/10 text-purple-neon border border-purple-neon/20 hover:bg-purple-neon/20 transition-all text-[10px] font-black uppercase tracking-widest flex items-center gap-2"
                            >
                                <Plus class="h-4 w-4" /> Ajouter Question
                            </button>
                        </div>

                        <div
                            v-for="(question, qIndex) in enigmaForm.questions"
                            :key="qIndex"
                            class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-4 relative group/q"
                        >
                            <button
                                type="button"
                                @click="removeQuestion(qIndex)"
                                class="absolute -top-2 -right-2 h-8 w-8 rounded-full bg-destructive text-white grid place-items-center opacity-0 group-hover/q:opacity-100 transition-opacity shadow-lg"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black"
                                    >Question #{{ qIndex + 1 }}</label
                                >
                                <input
                                    v-model="question.question_text"
                                    class="w-full h-11 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm focus:border-purple-neon outline-none"
                                    placeholder="Ex: Quel animal est sculpté sur la porte ?"
                                    required
                                />
                            </div>

                            <div
                                class="space-y-3 pl-4 border-l-2 border-purple-neon/20"
                            >
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-[9px] uppercase tracking-widest text-muted-foreground font-black"
                                        >Propositions</label
                                    >
                                    <button
                                        type="button"
                                        @click="addOption(qIndex)"
                                        class="text-[9px] text-purple-neon hover:underline font-bold"
                                    >
                                        + Ajouter Option
                                    </button>
                                </div>

                                <div
                                    v-for="(option, oIndex) in question.options"
                                    :key="oIndex"
                                    class="flex items-center gap-3 group/o"
                                >
                                    <label
                                        class="relative flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :name="`correct_${qIndex}`"
                                            :checked="option.is_correct"
                                            @change="
                                                setCorrectOption(qIndex, oIndex)
                                            "
                                            class="sr-only"
                                        />
                                        <div
                                            :class="
                                                cn(
                                                    'h-6 w-6 rounded-full border-2 transition-all flex items-center justify-center',
                                                    option.is_correct
                                                        ? 'border-purple-neon bg-purple-neon/20 shadow-purple'
                                                        : 'border-white/10 hover:border-purple-neon/50',
                                                )
                                            "
                                        >
                                            <div
                                                v-if="option.is_correct"
                                                class="h-2.5 w-2.5 rounded-full bg-purple-neon animate-pulse-soft"
                                            ></div>
                                        </div>
                                    </label>
                                    <input
                                        v-model="option.option_text"
                                        class="flex-1 h-10 rounded-xl bg-gaming-darker/50 border border-white/10 px-4 text-xs focus:border-purple-neon outline-none"
                                        placeholder="Proposition de réponse..."
                                        required
                                    />
                                    <button
                                        v-if="question.options.length > 2"
                                        type="button"
                                        @click="removeOption(qIndex, oIndex)"
                                        class="h-10 w-10 rounded-xl glass border-destructive/10 text-destructive/40 hover:text-destructive grid place-items-center opacity-0 group-hover/o:opacity-100 transition-all"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="enigmaForm.questions.length === 0"
                            class="p-8 text-center border-2 border-dashed border-white/5 rounded-3xl"
                        >
                            <HelpCircle
                                class="h-10 w-10 text-white/5 mx-auto mb-3"
                            />
                            <p class="text-xs text-muted-foreground">
                                Aucun questionnaire configuré pour cette énigme.
                            </p>
                        </div>
                    </div>

                    <div
                        class="py-5 flex gap-4 sticky bottom-0 bg-black w-full px-4"
                    >
                        <NeonButton
                            type="button"
                            variant="outline"
                            class="flex-1"
                            @click="showEnigmaModal = false"
                        >
                            Annuler</NeonButton
                        >
                        <NeonButton
                            type="submit"
                            variant="purple"
                            class="flex-1"
                            :disabled="enigmaForm.processing"
                        >
                            Sauvegarder l'Énigme</NeonButton
                        >
                    </div>
                </form>
            </div>
        </div>

        <!-- IMAGE MODAL -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl"
        >
            <div
                class="glass-strong rounded-[2.5rem] p-10 w-full max-w-md border border-electric/30 animate-fade-up shadow-neon"
            >
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="h-12 w-12 rounded-2xl bg-electric/20 grid place-items-center text-electric"
                    >
                        <ImageIcon class="h-6 w-6" />
                    </div>
                    <h2 class="font-display text-3xl text-white">
                        Images du Secteur
                    </h2>
                </div>

                <form @submit.prevent="submitImage" class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block"
                            >Visuel du Lieu</label
                        >
                        <input
                            type="file"
                            @change="imageForm.image = $event.target.files[0]"
                            class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-electric/10 file:text-electric hover:file:bg-electric/20 transition-all"
                            required
                        />
                    </div>
                    <p
                        class="text-[10px] text-muted-foreground uppercase tracking-widest leading-relaxed"
                    >
                        Cette image sera utilisée pour aider les joueurs à
                        identifier le lieu lors de leur exploration.
                    </p>

                    <div class="pt-6 flex gap-4">
                        <NeonButton
                            type="button"
                            variant="outline"
                            class="flex-1"
                            @click="showImageModal = false"
                        >
                            Annuler</NeonButton
                        >
                        <NeonButton
                            type="submit"
                            class="flex-1"
                            :disabled="imageForm.processing"
                            >Mettre à jour
                        </NeonButton>
                    </div>
                </form>
            </div>
        </div>

        <MobileTabBar />

        <!-- EDIT CITY MODAL -->
        <div
            v-if="showCityModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/80 backdrop-blur-md"
        >
            <div
                class="glass-strong rounded-3xl p-8 w-full max-w-md border border-electric/20 animate-fade-up"
            >
                <h2 class="font-display text-2xl text-white mb-6">
                    Modifier la Ville
                </h2>
                <form @submit.prevent="submitCity" class="space-y-4">
                    <GpsSearchInput
                        v-model="cityForm.name"
                        label="Nom de la Ville (Recherche GPS)"
                        placeholder="Ex: Porto-Novo, Bénin"
                        @select="onCitySelect"
                        required
                    />

                    <GlowInput
                        v-model="cityForm.radius_meters"
                        type="number"
                        label="Rayon de la zone (mètres)"
                        placeholder="5000"
                        required
                    />

                    <div>
                        <label
                            class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block"
                            >Briefing de la Ville</label
                        >
                        <textarea
                            v-model="cityForm.description"
                            class="w-full h-24 rounded-xl bg-gaming-darker border border-white/10 p-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none transition-all"
                            placeholder="Description de la ville et ses trésors..."
                        ></textarea>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block"
                            >Image de couverture</label
                        >
                        <input
                            type="file"
                            @change="cityForm.image = $event.target.files[0]"
                            class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-electric/10 file:text-electric hover:file:bg-electric/20 transition-all"
                        />
                    </div>

                    <div
                        v-if="cityForm.latitude"
                        class="p-3 rounded-xl bg-electric/5 border border-electric/20 flex items-center justify-between animate-fade-up"
                    >
                        <div
                            class="text-[10px] text-electric font-bold uppercase tracking-widest flex items-center gap-2"
                        >
                            <MapPin class="h-3 w-3" /> Coordonnées Verrouillées
                        </div>
                        <div
                            class="text-[10px] font-mono text-muted-foreground"
                        >
                            {{ cityForm.latitude }}, {{ cityForm.longitude }}
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <NeonButton
                            type="button"
                            variant="outline"
                            class="flex-1"
                            @click="showCityModal = false"
                            >Annuler</NeonButton
                        >
                        <NeonButton
                            type="submit"
                            class="flex-1"
                            :disabled="
                                cityForm.processing || !cityForm.latitude
                            "
                            >Enregistrer</NeonButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--purple-neon);
    border-radius: 10px;
}
</style>
