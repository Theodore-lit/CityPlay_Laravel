<script setup>
import GamingLayout from "@/Layouts/GamingLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import debounce from "lodash/debounce";
import L from 'leaflet';
import { onMounted, nextTick } from "vue";

const props = defineProps({
    city: Object,
    isMairie: {
        type: Boolean,
        default: false
    }
});

const showingAddLocationModal = ref(false);
const showingAddEnigmaModal = ref(false);
const selectedLocationId = ref(null);
const search = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const selectedPlace = ref("");

// Map management
const mapContainer = ref(null);
let map = null;
let marker = null;

const initMap = () => {
    if (map) return;
    
    map = L.map(mapContainer.value).setView([props.city.latitude, props.city.longitude], 13);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
    }).addTo(map);

    marker = L.marker([props.city.latitude, props.city.longitude]).addTo(map);
};

const updateMap = (lat, lon) => {
    if (!map) return;
    const pos = [lat, lon];
    map.setView(pos, 16);
    marker.setLatLng(pos);
};

watch(showingAddLocationModal, async (val) => {
    if (val) {
        await nextTick();
        initMap();
    }
});

const searchPlaces = debounce(async (query) => {
    if (!query || typeof query !== 'string' || query.length < 3) {
        searchResults.value = [];
        return;
    }

    // Calcul d'un bounding box approximatif autour de la ville (approx 10km)
    const delta = 0.1; 
    const viewbox = [
        parseFloat(props.city.longitude) - delta,
        parseFloat(props.city.latitude) + delta,
        parseFloat(props.city.longitude) + delta,
        parseFloat(props.city.latitude) - delta
    ].join(',');

    isSearching.value = true;
    try {
        // Suppression des headers personnalisés pour éviter les problèmes de CORS (preflight OPTIONS)
        // On utilise l'URL avec les paramètres directement
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5&viewbox=${viewbox}&bounded=1&accept-language=fr`;
        
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        searchResults.value = Array.isArray(data) ? data : [];
    } catch (e) {
        console.error("Erreur lors de la récupération des lieux:", e);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}, 500);

watch(search, (newQuery) => {
    searchPlaces(newQuery);
});

const selectPlace = (place) => {
    selectedPlace.value = place;
    search.value = place.display_name;
    searchResults.value = [];

    // Auto-remplissage partiel du formulaire
    locationForm.name = place.name || place.display_name.split(',')[0];
    locationForm.latitude = place.lat;
    locationForm.longitude = place.lon;
    
    updateMap(place.lat, place.lon);
};

const locationForm = useForm({
    city_id: props.city.id,
    name: "",
    description: "",
    category: "culture",
    latitude: props.city.latitude || 0,
    longitude: props.city.longitude || 0,
    radius_meters: 200,
    images: [],
});

const enigmaForm = useForm({
    location_id: "",
    title: "",
    content: "",
    difficulty: "medium",
    answer: "",
    reward_coins: 10,
    reward_hearts: 0,
    type: "devinette",
    image: null,
    responses: [{ content: "", is_correct: true }],
});

const addResponse = () => {
    enigmaForm.responses.push({ content: "", is_correct: false });
};

const removeResponse = (index) => {
    if (enigmaForm.responses.length > 1) {
        enigmaForm.responses.splice(index, 1);
    }
};

const setCorrectResponse = (index) => {
    enigmaForm.responses.forEach((r, i) => {
        r.is_correct = i === index;
    });
};

const openLocationModal = () => {
    showingAddLocationModal.value = true;
};

const openEnigmaModal = (locationId) => {
    selectedLocationId.value = locationId;
    enigmaForm.location_id = locationId;
    enigmaForm.responses = [{ content: "", is_correct: true }];
    showingAddEnigmaModal.value = true;
};

const submitLocation = () => {
    locationForm.post(route("admin.location.store"), {
        onSuccess: () => {
            showingAddLocationModal.value = false;
            locationForm.reset();
            search.value = "";
            searchResults.value = [];
        },
    });
};

const submitEnigma = () => {
    enigmaForm.post(route("admin.enigma.store"), {
        onSuccess: () => {
            showingAddEnigmaModal.value = false;
            enigmaForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="'Gestion - ' + city.name" />

    <GamingLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <Link
                        v-if="!isMairie"
                        :href="route('admin.dashboard')"
                        class="text-gaming-orange hover:text-gaming-orange-dark transition-colors"
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
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                    </Link>
                    <h2
                        class="font-black text-2xl text-gray-900 uppercase tracking-widest"
                    >
                        {{ city.name }}
                    </h2>
                </div>
                <div class="flex items-center space-x-4">
                    <span v-if="isMairie" class="px-4 py-1.5 rounded-full bg-gaming-orange/10 text-gaming-orange text-[10px] font-black uppercase tracking-widest border border-gaming-orange/20">
                        Espace Mairie
                    </span>
                    <button
                        @click="openLocationModal"
                        class="bg-gaming-orange hover:bg-gaming-orange-dark text-white font-bold py-2.5 px-6 rounded-xl transition-all uppercase text-sm tracking-widest shadow-lg shadow-gaming-orange/20"
                    >
                        + Nouveau Lieu
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- City Info Header -->
                <div
                    class="bg-white border border-gray-100 rounded-[2.5rem] p-10 mb-12 relative overflow-hidden shadow-sm"
                >
                    <div
                        class="absolute top-0 right-0 w-96 h-96 bg-gaming-orange/5 rounded-full -mr-48 -mt-48 blur-3xl"
                    ></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col md:flex-row gap-10">
                            <!-- City Image Display -->
                            <div class="w-full md:w-1/3 h-64 rounded-3xl overflow-hidden shadow-md bg-gray-100 border border-gray-100">
                                <img 
                                    v-if="city.image_path" 
                                    :src="city.image_path" 
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1">
                                <p
                                    class="text-gaming-orange text-[10px] font-black uppercase tracking-[0.4em] mb-3"
                                >
                                    Détails de la ville
                                </p>
                                <h3
                                    class="text-gray-500 text-base leading-relaxed max-w-2xl mb-10"
                                >
                                    {{
                                        city.description ||
                                        "Explorez, jouez et découvrez les secrets de cette ville magnifique."
                                    }}
                                </h3>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-10">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2"
                                            >Géré par</span
                                        >
                                        <span class="text-gray-900 font-black">{{
                                            city.creator?.name
                                        }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2"
                                            >Position</span
                                        >
                                        <span class="text-gray-900 font-bold text-xs"
                                            >{{ city.latitude }},
                                            {{ city.longitude }}</span
                                        >
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2"
                                            >Rayon</span
                                        >
                                        <span class="text-gray-900 font-black"
                                            >{{ city.radius_meters }}m</span
                                        >
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2"
                                            >Points d'intérêt</span
                                        >
                                        <span class="text-gray-900 font-black">{{
                                            city.locations.length
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations & Enigmas Section -->
                <h3
                    class="text-2xl font-black text-gray-900 uppercase tracking-widest mb-10 flex items-center"
                >
                    <span class="w-12 h-1.5 bg-gaming-orange rounded-full mr-5"></span>
                    Lieux & Énigmes
                </h3>

                <div class="space-y-10">
                    <div
                        v-for="location in city.locations"
                        :key="location.id"
                        class="bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden group shadow-sm hover:shadow-md transition-all duration-300"
                    >
                        <div
                            class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 border-b border-gray-50"
                        >
                            <div class="flex items-center space-x-8">
                                <div
                                    class="w-24 h-24 rounded-3xl bg-gray-100 flex items-center justify-center border border-gray-100 group-hover:border-gaming-orange/20 transition-all overflow-hidden shadow-inner"
                                >
                                    <img 
                                        v-if="location.location_images && location.location_images.length > 0" 
                                        :src="'/storage/' + location.location_images[0].image_path" 
                                        class="w-full h-full object-cover"
                                    />
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10 text-gray-300"
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
                                <div>
                                    <div
                                        class="flex items-center space-x-4 mb-2"
                                    >
                                        <h4
                                            class="text-xl font-black text-gray-900 uppercase tracking-wider"
                                        >
                                            {{ location.name }}
                                        </h4>
                                        <span
                                            class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-gray-100 text-gray-500 border border-gray-200"
                                        >
                                            {{ location.category }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm text-gray-400 line-clamp-1 italic"
                                    >
                                        {{ location.description }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-6">
                                <div class="text-right hidden md:block border-r border-gray-100 pr-6">
                                    <p
                                        class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"
                                    >
                                        Énigmes
                                    </p>
                                    <p class="text-gray-900 font-black text-lg">
                                        {{ location.enigmas?.length || 0 }}
                                    </p>
                                </div>
                                <button
                                    @click="openEnigmaModal(location.id)"
                                    class="bg-white hover:bg-gaming-orange text-gaming-orange hover:text-white font-black py-3 px-6 rounded-2xl text-[10px] uppercase tracking-[0.2em] transition-all border-2 border-gaming-orange/20 hover:border-gaming-orange shadow-sm"
                                >
                                    + Ajouter Énigme
                                </button>
                            </div>
                        </div>

                        <!-- Enigmas List -->
                        <div
                            v-if="location.enigmas && location.enigmas.length > 0"
                            class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/50"
                        >
                            <div
                                v-for="enigma in location.enigmas"
                                :key="enigma.id"
                                class="bg-white border border-gray-100 rounded-3xl p-6 hover:border-gaming-orange/30 hover:shadow-md transition-all flex gap-6"
                            >
                                <!-- Enigma Image -->
                                <div class="w-24 h-24 rounded-2xl bg-gray-50 overflow-hidden flex-shrink-0 border border-gray-100 shadow-inner">
                                    <img 
                                        v-if="enigma.image_path" 
                                        :src="'/storage/' + enigma.image_path" 
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <div
                                        class="flex justify-between items-start mb-3"
                                    >
                                        <h5
                                            class="text-base font-black text-gray-900 uppercase tracking-wide"
                                        >
                                            {{ enigma.title }}
                                        </h5>
                                        <span
                                            :class="[
                                                'text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-full border shadow-sm',
                                                enigma.difficulty === 'hard'
                                                    ? 'bg-red-50 text-red-600 border-red-100'
                                                    : enigma.difficulty === 'medium'
                                                      ? 'bg-gaming-orange/5 text-gaming-orange border-gaming-orange/10'
                                                      : 'bg-green-50 text-green-600 border-green-100',
                                            ]"
                                        >
                                            {{ enigma.difficulty }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs text-gray-500 line-clamp-2 mb-4 italic leading-relaxed"
                                    >
                                        "{{ enigma.content }}"
                                    </p>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Réponse</span>
                                            <span class="text-xs font-bold text-gaming-orange">{{ enigma.answer || 'Choix multiples' }}</span>
                                        </div>
                                        <div class="flex space-x-4 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
                                            <span class="text-[10px] font-black">🪙 {{ enigma.reward_coins }}</span>
                                            <span class="text-[10px] font-black text-red-500">❤️ {{ enigma.reward_hearts }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-10 text-center bg-gray-50/50">
                            <p
                                class="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em] italic"
                            >
                                Aucune énigme pour ce lieu. Commencez l'aventure !
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GamingLayout>

    <!-- Modal Nouveau Lieu -->
    <Modal
        :show="showingAddLocationModal"
        @close="showingAddLocationModal = false"
        max-width="4xl"
    >
        <div class="bg-gaming-surface border border-gaming-blue/20 rounded-3xl overflow-hidden shadow-2xl shadow-gaming-blue/10">
            <div class="p-8 border-b border-gaming-blue/10 bg-gaming-dark/50 flex justify-between items-center">
                <h2 class="text-2xl font-black text-white uppercase tracking-[0.2em]">
                    Nouveau Lieu
                </h2>
                <button @click="showingAddLocationModal = false" class="text-gray-500 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="submitLocation" class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <!-- Left Side: Search & Info -->
                    <div class="space-y-8">
                        <div class="relative">
                            <InputLabel
                                for="place"
                                value="1. Rechercher le lieu sur la carte"
                                class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                            />
                            <div class="relative">
                                <TextInput
                                    id="place"
                                    type="text"
                                    class="block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green rounded-2xl h-12 pl-4 pr-10"
                                    v-model="search"
                                    placeholder="Ex: Musée, Place, Monument..."
                                    autocomplete="off"
                                />
                                
                                <!-- Suggestions Dropdown -->
                                <div
                                    v-if="searchResults.length > 0"
                                    class="absolute z-50 w-full mt-2 bg-gaming-surface border border-gaming-blue/20 rounded-2xl shadow-2xl max-h-64 overflow-y-auto backdrop-blur-xl"
                                >
                                    <div
                                        v-for="result in searchResults"
                                        :key="result.place_id"
                                        @click="selectPlace(result)"
                                        class="p-4 hover:bg-gaming-blue/10 cursor-pointer border-b border-gaming-blue/5 last:border-0 transition-all flex flex-col gap-1"
                                    >
                                        <div class="text-sm text-white font-bold truncate">
                                            {{ result.display_name }}
                                        </div>
                                        <div class="text-[9px] text-gray-500 uppercase font-black tracking-widest">
                                            {{ result.type }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="isSearching" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <div class="animate-spin h-5 w-5 border-2 border-gaming-green border-t-transparent rounded-full"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <InputLabel
                                    for="loc_name"
                                    value="2. Nom d'affichage"
                                    class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                                />
                                <TextInput
                                    id="loc_name"
                                    v-model="locationForm.name"
                                    class="w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-blue rounded-2xl h-12 px-4"
                                    placeholder="Donnez un nom à ce lieu"
                                    required
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="loc_cat"
                                    value="3. Catégorie"
                                    class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                                />
                                <select
                                    id="loc_cat"
                                    v-model="locationForm.category"
                                    class="w-full bg-gaming-dark border-gaming-blue/20 text-white rounded-2xl h-12 px-4 text-sm focus:ring-gaming-blue focus:border-gaming-blue transition-all"
                                >
                                    <option value="culture">Culture</option>
                                    <option value="historique">Historique</option>
                                    <option value="nature">Nature</option>
                                    <option value="monument">Monument</option>
                                    <option value="restaurant">Restaurant</option>
                                </select>
                            </div>

                            <div>
                                <InputLabel
                                    for="loc_desc"
                                    value="4. Description"
                                    class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                                />
                                <textarea
                                    id="loc_desc"
                                    v-model="locationForm.description"
                                    rows="3"
                                    placeholder="Décrivez brièvement ce lieu..."
                                    class="w-full bg-gaming-dark border-gaming-blue/20 text-white rounded-2xl text-sm focus:ring-gaming-blue focus:border-gaming-blue p-4 transition-all"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Map & Images -->
                    <div class="space-y-8">
                        <div>
                            <InputLabel
                                value="Aperçu géographique"
                                class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                            />
                            <div 
                                ref="mapContainer" 
                                class="w-full h-[250px] rounded-3xl border border-gaming-blue/20 bg-gaming-dark overflow-hidden shadow-inner"
                            ></div>
                        </div>

                        <div>
                            <InputLabel
                                for="loc_images"
                                value="Images (Sélection multiple)"
                                class="text-gaming-blue-light text-[11px] font-black uppercase tracking-widest mb-3"
                            />
                            <div class="relative group">
                                <input
                                    id="loc_images"
                                    type="file"
                                    multiple
                                    @input="locationForm.images = $event.target.files"
                                    class="hidden"
                                />
                                <label 
                                    for="loc_images" 
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gaming-blue/20 rounded-3xl cursor-pointer bg-gaming-dark/30 hover:bg-gaming-blue/5 hover:border-gaming-blue/40 transition-all group"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500 mb-2 group-hover:text-gaming-blue-light transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest group-hover:text-white transition-colors">
                                        {{ locationForm.images.length > 0 ? locationForm.images.length + ' images sélectionnées' : 'Cliquez pour uploader' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-12 pt-8 border-t border-gaming-blue/10">
                    <SecondaryButton @click="showingAddLocationModal = false" class="rounded-xl px-8 py-3">
                        Annuler
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="locationForm.processing"
                        class="bg-gaming-blue hover:bg-gaming-blue-dark rounded-xl px-8 py-3 shadow-lg shadow-gaming-blue/20"
                    >
                        Confirmer le lieu
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>


    <!-- Modal Nouvelle Énigme -->
    <Modal :show="showingAddEnigmaModal" @close="showingAddEnigmaModal = false">
        <div class="p-8 bg-gaming-surface border border-gaming-blue/20">
            <h2
                class="text-xl font-black text-white uppercase tracking-widest mb-8 border-b border-gaming-blue/10 pb-6"
            >
                Ajouter une énigme
            </h2>
            <form @submit.prevent="submitEnigma" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <InputLabel
                            for="en_title"
                            value="Titre de l'énigme"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <TextInput
                            id="en_title"
                            v-model="enigmaForm.title"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white focus:border-gaming-blue"
                            required
                        />
                    </div>
                    <div>
                        <InputLabel
                            for="en_diff"
                            value="Difficulté"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <select
                            id="en_diff"
                            v-model="enigmaForm.difficulty"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white rounded-xl text-sm focus:ring-gaming-blue focus:border-gaming-blue"
                        >
                            <option value="easy">Facile</option>
                            <option value="medium">Moyenne</option>
                            <option value="hard">Difficile</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel
                            for="en_type"
                            value="Type"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <select
                            id="en_type"
                            v-model="enigmaForm.type"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white rounded-xl text-sm focus:ring-gaming-blue focus:border-gaming-blue"
                        >
                            <option value="devinette">Devinette</option>
                            <option value="aventure">Aventure</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel
                            for="en_content"
                            value="Contenu / Énigme"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <textarea
                            id="en_content"
                            v-model="enigmaForm.content"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white rounded-xl text-sm focus:ring-gaming-blue focus:border-gaming-blue rows-4"
                            required
                        ></textarea>
                    </div>

                    <!-- Propositions de réponses -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex justify-between items-center">
                            <InputLabel
                                value="Propositions de réponses"
                                class="text-gray-500 text-[10px] font-black uppercase tracking-widest"
                            />
                            <button
                                type="button"
                                @click="addResponse"
                                class="text-[10px] font-black uppercase tracking-widest text-gaming-blue-light hover:text-white transition-colors"
                            >
                                + Ajouter une option
                            </button>
                        </div>

                        <div
                            v-for="(resp, index) in enigmaForm.responses"
                            :key="index"
                            class="flex items-center space-x-3 bg-gaming-dark/50 p-3 rounded-2xl border border-gaming-blue/5"
                        >
                            <input
                                type="radio"
                                :name="'correct_resp'"
                                :checked="resp.is_correct"
                                @change="setCorrectResponse(index)"
                                class="w-4 h-4 text-gaming-green bg-gaming-dark border-gaming-blue/20 focus:ring-gaming-green"
                            />
                            <TextInput
                                v-model="resp.content"
                                placeholder="Texte de la proposition..."
                                class="flex-1 bg-transparent border-0 focus:ring-0 text-sm h-8"
                                required
                            />
                            <button
                                v-if="enigmaForm.responses.length > 1"
                                type="button"
                                @click="removeResponse(index)"
                                class="text-red-500/50 hover:text-red-500 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                        <InputError
                            class="mt-2"
                            :message="enigmaForm.errors.responses"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="en_ans"
                            value="Réponse texte (si applicable)"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <TextInput
                            id="en_ans"
                            v-model="enigmaForm.answer"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white focus:border-gaming-blue"
                        />
                    </div>
                    <div>
                        <InputLabel
                            for="en_image"
                            value="Image de l'énigme"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <input
                            id="en_image"
                            type="file"
                            @input="enigmaForm.image = $event.target.files[0]"
                            class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gaming-blue/10 file:text-gaming-blue-light hover:file:bg-gaming-blue/20 cursor-pointer"
                        />
                    </div>
                    <div class="flex-1">
                        <InputLabel
                            for="en_coins"
                            value="🪙 Pièces"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <TextInput
                            id="en_coins"
                            type="number"
                            v-model="enigmaForm.reward_coins"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white focus:border-gaming-blue"
                        />
                    </div>
                    <div class="flex-1">
                        <InputLabel
                            for="en_hearts"
                            value="❤️ Cœurs"
                            class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2"
                        />
                        <TextInput
                            id="en_hearts"
                            type="number"
                            v-model="enigmaForm.reward_hearts"
                            class="w-full bg-gaming-dark border-gaming-blue/10 text-white focus:border-gaming-blue"
                        />
                    </div>
                </div>
                <div
                    class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gaming-blue/10"
                >
                    <SecondaryButton @click="showingAddEnigmaModal = false"
                        >Annuler</SecondaryButton
                    >
                    <PrimaryButton
                        :disabled="enigmaForm.processing"
                        class="bg-gaming-green"
                        >Ajouter l'énigme</PrimaryButton
                    >
                </div>
            </form>
        </div>
    </Modal>
</template>
