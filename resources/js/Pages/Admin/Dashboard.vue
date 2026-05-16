<script setup>
import GamingLayout from "@/Layouts/GamingLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    mairies: Array,
    locations: Array,
    users: Array,
    players: Array,
    stats: Object,
    auth: Object,
});

const activeTab = ref("cities"); // 'cities', 'locations' or 'players'
const tableSearch = ref("");

const filteredMairies = computed(() => {
    if (!tableSearch.value) return props.mairies;
    const q = tableSearch.value.toLowerCase();
    return props.mairies.filter(
        (m) =>
            m.name.toLowerCase().includes(q) ||
            (m.description && m.description.toLowerCase().includes(q)),
    );
});

const filteredLocations = computed(() => {
    if (!tableSearch.value) return props.locations;
    const q = tableSearch.value.toLowerCase();
    return props.locations.filter(
        (l) =>
            l.name.toLowerCase().includes(q) ||
            (l.city && l.city.name.toLowerCase().includes(q)),
    );
});

const filteredPlayers = computed(() => {
    if (!tableSearch.value) return props.players;
    const q = tableSearch.value.toLowerCase();
    return props.players.filter(
        (p) =>
            p.name.toLowerCase().includes(q) ||
            p.email.toLowerCase().includes(q),
    );
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        alert("Lien copié dans le presse-papier !");
    });
};

const search = ref("");
const searchResults = ref([]);
const selectedCity = ref(null);
const isSearching = ref(false);

const searchCity =
    // debounce(
    async (query) => {
        if (!query || query.length < 3) {
            searchResults.value = [];
            return;
        }

        isSearching.value = true;
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`,
                {
                    headers: {
                        "Accept-Language": "fr",
                    },
                },
            );
            searchResults.value = await response.json();
        } catch (error) {
            console.error("Erreur de recherche:", error);
        } finally {
            isSearching.value = false;
        }
    };
// , 500);

const share = async (lien) => {
    const shareData = {
        title: `CityPlay`,
        text: "Découvrez le Bénin avec CitPlay 😅",
        url: lien,
    };
    if (navigator.share && navigator.canShare?.(shareData)) {
        try {
            await navigator.share(shareData);
        } catch (err) {
            // L'utilisateur a annulé ou une erreur est survenue
            if (err.name !== "AbortError") {
                copyToClipboard(lien);
                console.error("Erreur de partage :", err);
            }
        }
    } else {
        // Fallback pour desktop ou navigateurs non supportés
        copyToClipboard(lien);
    }
};

watch(search, (newQuery) => {
    searchCity(newQuery);
});

const selectCity = (city) => {
    selectedCity.value = city;
    search.value = city.display_name;
    searchResults.value = [];

    // Auto-remplissage partiel du formulaire
    form.city_name = city.name || city.display_name.split(",")[0];
    form.latitude = city.lat;
    form.longitude = city.lon;
};

const showingAddMairieModal = ref(false);

const form = useForm({
    email: "",
    password: "",
    password_confirmation: "",
    city_name: "",
    description: "",
    latitude: "",
    longitude: "",
    image: "",
    opening_hours: { start: "08:00", end: "20:00" },
});

const openModal = () => {
    showingAddMairieModal.value = true;
};

const closeModal = () => {
    showingAddMairieModal.value = false;
    form.reset();
    form.clearErrors();
    search.value = "";
    selectedCity.value = null;
    searchResults.value = [];
};

const submit = () => {
    if (!selectedCity.value) {
        alert("Veuillez sélectionner une ville dans la liste.");
        return;
    }

    form.post(route("admin.mairie.store"), {
        onSuccess: () => closeModal(),
    });
};
// console.log(props.locations);
</script>

<template>
    <Head title="Administration Mairie" />

    <GamingLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-2xl text-gaming-green-light leading-tight uppercase tracking-widest"
                >
                    Tableau de Administrateur
                </h2>
                <button
                    @click="openModal"
                    class="bg-gaming-green hover:bg-gaming-green-dark text-white font-bold py-2 px-6 rounded-lg transition-all uppercase text-sm tracking-wider"
                >
                    + Ajouter une ville
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- General Sharing Link & Stats Overview -->
                <div class="mb-12">
                    <div
                        class="bg-white border border-gray-100 p-8 rounded-[2.5rem] shadow-sm mb-12 flex flex-col md:flex-row items-center justify-between gap-6"
                    >
                        <div>
                            <h3
                                class="text-xl font-black text-gray-900 uppercase tracking-widest mb-2"
                            >
                                Lien de partage général
                            </h3>
                            <p class="text-gray-500 text-sm">
                                Partagez ce lien pour permettre aux joueurs de
                                choisir leur ville.
                            </p>
                        </div>
                        <div
                            class="flex items-center space-x-4 w-full md:w-auto"
                        >
                            <div
                                @click="share(route('player.dashboard'))"
                                class="cursor-pointer bg-gaming-orange text-white p-3 rounded-2xl shadow-lg shadow-gaming-orange/20"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                                    />
                                </svg>
                            </div>
                            <div
                                class="bg-gray-50 px-6 py-3 rounded-2xl border border-gray-100 text-gaming-orange font-bold text-sm truncate max-w-xs md:max-w-md"
                            >
                                {{ route("player.dashboard") }}
                            </div>
                            <button
                                @click="
                                    copyToClipboard(route('player.dashboard'))
                                "
                                class="bg-gaming-orange hover:bg-gaming-orange-dark text-white p-1 rounded-xl transition-all shadow-lg shadow-gaming-orange/20"
                                title="Copier le lien"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm"
                        >
                            <p
                                class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1"
                            >
                                Totales des Mairies
                            </p>
                            <p class="text-3xl font-black text-dark">
                                {{ mairies.length }}
                            </p>
                        </div>
                        <div
                            class="bg-gaming-surface border border-gaming-green/10 p-6 rounded-2xl shadow-sm"
                        >
                            <p
                                class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1"
                            >
                                Joueurs Actifs
                            </p>
                            <p
                                class="text-3xl font-black text-gaming-green-light"
                            >
                                {{ stats.active_players }}
                            </p>
                        </div>
                        <div
                            class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm"
                        >
                            <p
                                class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1"
                            >
                                Total joueurs
                            </p>
                            <p
                                class="text-3xl font-black text-gaming-blue-light"
                            >
                               {{ players.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Switcher -->
                <div
                    class="flex items-center space-x-1 mb-8 bg-white p-1.5 rounded-2xl border border-gray-200 w-fit shadow-sm"
                >
                    <button
                        @click="activeTab = 'cities'"
                        :class="[
                            'px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                            activeTab === 'cities'
                                ? 'bg-gaming-orange text-white shadow-lg shadow-gaming-orange/20'
                                : 'text-gray-400 hover:text-gray-600',
                        ]"
                    >
                        Villes ({{ mairies.length }})
                    </button>
                    <button
                        @click="activeTab = 'locations'"
                        :class="[
                            'px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                            activeTab === 'locations'
                                ? 'bg-gaming-orange text-white shadow-lg shadow-gaming-orange/20'
                                : 'text-gray-400 hover:text-gray-600',
                        ]"
                    >
                        Lieux ({{ stats.total_locations }})
                    </button>
                    <button
                        @click="activeTab = 'players'"
                        :class="[
                            'px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                            activeTab === 'players'
                                ? 'bg-gaming-orange text-white shadow-lg shadow-gaming-orange/20'
                                : 'text-gray-400 hover:text-gray-600',
                        ]"
                    >
                        Users ({{ users.length + 1}})
                    </button>
                </div>

                <!-- Search Filter -->
                <div class="mb-12 relative max-w-md">
                    <TextInput
                        v-model="tableSearch"
                        type="text"
                        :placeholder="
                            activeTab === 'cities'
                                ? 'Rechercher une ville...'
                                : activeTab === 'locations'
                                  ? 'Rechercher un lieu...'
                                  : 'Rechercher un utilisateur...'
                        "
                        class="w-full bg-white border-gray-200 text-gray-900 focus:border-gaming-orange focus:ring-gaming-orange rounded-2xl h-12 px-5 shadow-sm"
                    />
                    <div
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Cities Cards Grid -->
                <div
                    v-if="activeTab === 'cities'"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10"
                >
                    <div
                        v-for="mairie in filteredMairies"
                        :key="mairie.id"
                        class="group bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden hover:border-gaming-orange/30 transition-all duration-500 hover:shadow-2xl hover:shadow-gaming-orange/5"
                    >
                        <!-- Card Header/Image -->
                        <div class="relative h-56 bg-gray-100 overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent z-10"
                            ></div>
                            <img
                                v-if="mairie.image_path"
                                :src="'/storage/' + mairie.image_path"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-200 bg-gray-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-20 w-20"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-6 right-6 z-20">
                                <span
                                    :class="[
                                        'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md border shadow-sm',
                                        mairie.is_active
                                            ? 'bg-green-500/10 text-green-600 border-green-500/20'
                                            : 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                    ]"
                                >
                                    {{ mairie.is_active ? "Actif" : "Inactif" }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-8 relative">
                            <h3
                                class="text-2xl font-black text-gray-900 mb-3 group-hover:text-gaming-orange transition-colors"
                            >
                                {{ mairie.name }}
                            </h3>
                            <p
                                class="text-gray-500 text-sm leading-relaxed mb-8 line-clamp-2 h-10"
                            >
                                {{
                                    mairie.description ||
                                    "Préparez-vous pour une aventure urbaine épique dans cette ville."
                                }}
                            </p>

                            <!-- Mini Stats -->
                            <div
                                class="flex items-center space-x-8 mb-10 py-6 border-y border-gray-100"
                            >
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"
                                        >Lieux</span
                                    >
                                    <span
                                        class="text-xl font-black text-gray-900"
                                        >{{
                                            mairie.locations?.length || 0
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex flex-col border-l border-gray-100 pl-8"
                                >
                                    <span
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"
                                        >Mairie</span
                                    >
                                    <span
                                        class="text-sm font-bold text-gaming-orange truncate w-32"
                                    >
                                        {{
                                            mairie.mairie?.name ||
                                            mairie.creator?.name ||
                                            "Admin"
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-4">
                                <Link
                                    :href="route('admin.city.show', mairie.id)"
                                    class="w-full bg-gaming-orange hover:bg-gaming-orange-dark text-white font-black py-4 px-6 rounded-2xl text-center transition-all duration-300 flex items-center justify-center space-x-3 group/btn shadow-lg shadow-gaming-orange/20"
                                >
                                    <span
                                        class="uppercase text-xs tracking-widest"
                                        >Gérer la ville</span
                                    >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 group-hover/btn:translate-x-1 transition-transform"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                                        />
                                    </svg>
                                </Link>

                                <!-- Share City Link -->
                                <button
                                    @click="
                                        copyToClipboard(
                                            route('player.game', mairie.id),
                                        )
                                    "
                                    class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 font-bold py-3 px-6 rounded-2xl text-center transition-all duration-300 flex items-center justify-center space-x-2 group/share"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gaming-orange group-hover/share:scale-110 transition-transform"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                                        />
                                    </svg>
                                    <span
                                        class="text-[10px] uppercase tracking-widest"
                                        >Lien de partage joueur</span
                                    >
                                </button>

                                <div class="flex space-x-4">
                                    <button
                                        class="flex-1 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 transition-colors py-2 border border-transparent hover:border-gray-200 rounded-xl"
                                    >
                                        Modifier
                                    </button>
                                    <button
                                        class="flex-1 text-[10px] font-black uppercase tracking-[0.2em] text-red-400 hover:text-red-600 transition-colors py-2 border border-transparent hover:border-red-100 rounded-xl"
                                    >
                                        Arrêter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations Cards Grid -->
                <div
                    v-else-if="activeTab === 'locations'"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10"
                >
                    <div
                        v-for="location in filteredLocations"
                        :key="location.id"
                        class="group bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden hover:border-gaming-orange/30 transition-all duration-500 hover:shadow-2xl hover:shadow-gaming-orange/5"
                    >
                        <!-- Card Header/Image -->
                        <div class="relative h-56 bg-gray-100 overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent z-10"
                            ></div>
                            <img
                                v-if="location.location_images?.length > 0"
                                :src="
                                    '/storage/' +
                                    location.location_images[0].image_path
                                "
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-200 bg-gray-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-20 w-20"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </div>

                            <!-- Category Badge -->
                            <div class="absolute top-6 right-6 z-20">
                                <span
                                    class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md border bg-white/50 text-gaming-orange border-gaming-orange/20 shadow-sm"
                                >
                                    {{ location.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-8 relative">
                            <div class="flex items-center space-x-2 mb-3">
                                <span
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]"
                                    >{{ location.city?.name }}</span
                                >
                            </div>
                            <h3
                                class="text-2xl font-black text-gray-900 mb-3 group-hover:text-gaming-orange transition-colors"
                            >
                                {{ location.name }}
                            </h3>
                            <p
                                class="text-gray-500 text-sm leading-relaxed mb-8 line-clamp-2 h-10"
                            >
                                {{
                                    location.description ||
                                    "Un lieu mystérieux rempli de secrets à découvrir."
                                }}
                            </p>

                            <!-- Mini Stats -->
                            <div
                                class="flex items-center space-x-8 mb-10 py-6 border-y border-gray-100"
                            >
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"
                                        >Énigmes</span
                                    >
                                    <span
                                        class="text-xl font-black text-gray-900"
                                        >{{
                                            location.enigmas?.length || 0
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex flex-col border-l border-gray-100 pl-8"
                                >
                                    <span
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"
                                        >Note</span
                                    >
                                    <div class="flex items-center space-x-1">
                                        <span
                                            class="text-xl font-black text-gray-900"
                                            >{{
                                                location.popularity || 4.5
                                            }}</span
                                        >
                                        <span class="text-sm text-gaming-orange"
                                            >★</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-4">
                                <Link
                                    :href="
                                        route(
                                            'admin.city.show',
                                            location.city_id,
                                        )
                                    "
                                    class="w-full bg-white hover:bg-gray-50 text-gaming-orange border-2 border-gaming-orange/20 hover:border-gaming-orange font-black py-4 px-6 rounded-2xl text-center transition-all duration-300 flex items-center justify-center space-x-3 group/btn shadow-sm"
                                >
                                    <span
                                        class="uppercase text-xs tracking-widest"
                                        >Gérer les énigmes</span
                                    >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 group-hover/btn:translate-x-1 transition-transform"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                                        />
                                    </svg>
                                </Link>

                                <div class="flex space-x-4">
                                    <button
                                        class="flex-1 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 transition-colors py-2 border border-transparent hover:border-gray-200 rounded-xl"
                                    >
                                        Détails
                                    </button>
                                    <button
                                        class="flex-1 text-[10px] font-black uppercase tracking-[0.2em] text-red-400 hover:text-red-600 transition-colors py-2 border border-transparent hover:border-red-100 rounded-xl"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Players Table -->
                <div
                    v-else-if="activeTab === 'players'"
                    class="bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 border-b border-gray-100"
                                >
                                    <th
                                        class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400"
                                    >
                                        Joueur
                                    </th>
                                    <th
                                        class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400"
                                    >
                                        Email
                                    </th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Rôle</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Statut</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Dernière activité</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="player in filteredPlayers" :key="player.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-full bg-gaming-orange/10 flex items-center justify-center text-gaming-orange font-black text-sm border border-gaming-orange/20 shadow-sm">
                                                {{ player.name.charAt(0) }}
                                            </div>
                                            <span class="font-black text-gray-900">{{ player.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-sm text-gray-500 font-medium">{{ player.email }}</td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                                            :class="player.role === 'mairie' ? 'bg-blue-50 text-blue-600' : 'bg-gaming-orange/10 text-gaming-orange'">
                                            {{ player.role }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                                            :class="player.is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                                            {{ player.is_active ? 'Actif' : 'Désactivé' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-sm text-gray-400">
                                        {{ player.last_active_at ? new Date(player.last_active_at).toLocaleString() : 'Jamais' }}
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center space-x-4">
                                            <button 
                                                @click="$inertia.post(route('admin.user.toggle-status', player.id))"
                                                class="text-[10px] font-black uppercase tracking-widest transition-colors"
                                                :class="player.is_active ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'"
                                            >
                                                {{ player.is_active ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GamingLayout>

    <!-- Modal Ajout Mairie -->
    <Modal :show="showingAddMairieModal" @close="closeModal">
        <div class="p-6 bg-gaming-surface border border-gaming-blue/20">
            <h2
                class="text-xl font-bold text-white uppercase tracking-widest mb-6 border-b border-gaming-blue/10 pb-4"
            >
                Ajouter une nouvelle Mairie
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="relative">
                    <InputLabel
                        for="city"
                        value="Rechercher une ville (OpenStreetMap)"
                        class="text-gray-400"
                    />
                    <TextInput
                        id="city"
                        type="text"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="search"
                        placeholder="Ex: Cotonou, Bénin..."
                        autocomplete="off"
                    />

                    <!-- Suggestions Dropdown -->
                    <div
                        v-if="searchResults.length > 0"
                        class="absolute z-50 w-full mt-1 bg-gaming-surface border border-gaming-blue/20 rounded-lg shadow-xl max-h-60 overflow-y-auto"
                    >
                        <div
                            v-for="result in searchResults"
                            :key="result.place_id"
                            @click="selectCity(result)"
                            class="p-3 hover:bg-gaming-blue/10 cursor-pointer border-b border-gaming-blue/5 last:border-0 transition-colors"
                        >
                            <div class="text-sm text-white font-medium">
                                {{ result.display_name }}
                            </div>
                            <div
                                class="text-[10px] text-gray-500 uppercase tracking-tighter"
                            >
                                {{ result.type }} • Lat: {{ result.lat }} • Lon:
                                {{ result.lon }}
                            </div>
                        </div>
                    </div>

                    <div v-if="isSearching" class="absolute right-3 top-9">
                        <div
                            class="animate-spin h-4 w-4 border-2 border-gaming-green border-t-transparent rounded-full"
                        ></div>
                    </div>
                </div>

                <div
                    v-if="selectedCity"
                    class="p-4 bg-gaming-green/5 border border-gaming-green/20 rounded-xl animate-fade-in"
                >
                    <p
                        class="text-xs text-gaming-green-light font-bold uppercase tracking-widest mb-2"
                    >
                        Ville sélectionnée
                    </p>
                    <p class="text-sm text-white">
                        {{ selectedCity.display_name }}
                    </p>
                    <div
                        class="mt-2 flex space-x-4 text-[10px] text-gray-500 font-mono"
                    >
                        <span>LAT: {{ selectedCity.lat }}</span>
                        <span>LON: {{ selectedCity.lon }}</span>
                    </div>
                </div>

                <div>
                    <InputLabel
                        for="city_name"
                        value="Nom d'affichage de la ville"
                        class="text-gray-400"
                    />
                    <TextInput
                        id="city_name"
                        type="text"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="form.city_name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.city_name" />
                </div>
                <div>
                    <InputLabel
                        for="description"
                        value="Description"
                        class="text-gray-400"
                    />
                    <TextInput
                        id="description"
                        type="text"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="form.description"
                        required
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.description"
                    />
                </div>

                <div>
                    <InputLabel
                        for="email"
                        value="Email"
                        class="text-gray-400"
                    />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="form.email"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel
                        for="en_image"
                        value="Une image pour la ville"
                        class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                    />
                    <input
                        id="en_image"
                        type="file"
                        @input="form.image = $event.target.files[0]"
                        class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gaming-blue/10 file:text-gaming-blue-light hover:file:bg-gaming-blue/20 cursor-pointer"
                    />
                </div>
                <div class="flex items-center justify-end mt-8 space-x-4">
                    <SecondaryButton
                        @click="closeModal"
                        class="bg-transparent border-gray-600 text-gray-400 hover:bg-gray-800 transition-all"
                    >
                        Annuler
                    </SecondaryButton>

                    <PrimaryButton
                        class="bg-gaming-green hover:bg-gaming-green-dark shadow-gaming-green/20"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Créer cette ville
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
