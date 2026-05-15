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
    stats: Object,
    auth: Object,
});

const activeTab = ref('cities'); // 'cities' or 'locations'
const tableSearch = ref("");

const filteredMairies = computed(() => {
    if (!tableSearch.value) return props.mairies;
    const q = tableSearch.value.toLowerCase();
    return props.mairies.filter(m => 
        m.name.toLowerCase().includes(q) || 
        (m.description && m.description.toLowerCase().includes(q))
    );
});

const filteredLocations = computed(() => {
    if (!tableSearch.value) return props.locations;
    const q = tableSearch.value.toLowerCase();
    return props.locations.filter(l => 
        l.name.toLowerCase().includes(q) || 
        (l.city && l.city.name.toLowerCase().includes(q))
    );
});

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
            }
        );
        searchResults.value = await response.json();
    } catch (error) {
        console.error("Erreur de recherche:", error);
    } finally {
        isSearching.value = false;
    }
}
// , 500);

watch(search, (newQuery) => {
    searchCity(newQuery);
});

const selectCity = (city) => {
    selectedCity.value = city;
    search.value = city.display_name;
    searchResults.value = [];
    
    // Auto-remplissage partiel du formulaire
    form.city_name = city.name || city.display_name.split(',')[0];
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
// console.log(props.mairies);
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
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div
                        class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm"
                    >
                        <p
                            class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1"
                        >
                            Totales des Mairies
                        </p>
                        <p class="text-3xl font-black text-white">
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
                        <p class="text-3xl font-black text-gaming-green-light">
                            {{ stats.active_players }}
                        </p>
                    </div>
                    <div
                        class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm"
                    >
                        <p
                            class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1"
                        >
                            Taux de réussite
                        </p>
                        <p class="text-3xl font-black text-gaming-blue-light">
                            78%
                        </p>
                    </div>
                </div>

                <!-- Tabs Switcher -->
                <div class="flex items-center space-x-1 mb-8 bg-gaming-surface/50 p-1 rounded-2xl border border-gaming-blue/5 w-fit">
                    <button 
                        @click="activeTab = 'cities'"
                        :class="[
                            'px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                            activeTab === 'cities' ? 'bg-gaming-blue text-white shadow-lg shadow-gaming-blue/20' : 'text-gray-500 hover:text-gray-300'
                        ]"
                    >
                        Villes ({{ mairies.length }})
                    </button>
                    <button 
                        @click="activeTab = 'locations'"
                        :class="[
                            'px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300',
                            activeTab === 'locations' ? 'bg-gaming-blue text-white shadow-lg shadow-gaming-blue/20' : 'text-gray-500 hover:text-gray-300'
                        ]"
                    >
                        Lieux ({{ stats.total_locations }})
                    </button>
                </div>

                <!-- Search Filter -->
                <div class="mb-12 relative max-w-md">
                    <TextInput 
                        v-model="tableSearch"
                        type="text"
                        :placeholder="activeTab === 'cities' ? 'Rechercher une ville...' : 'Rechercher un lieu...'"
                        class="w-full bg-gaming-surface border-gaming-blue/10 text-white focus:border-gaming-blue focus:ring-gaming-blue rounded-2xl"
                    />
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Cities Cards Grid -->
                <div v-if="activeTab === 'cities'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="mairie in filteredMairies"
                        :key="mairie.id"
                        class="group bg-gaming-surface border border-gaming-blue/10 rounded-3xl overflow-hidden hover:border-gaming-blue/30 transition-all duration-500 hover:shadow-2xl hover:shadow-gaming-blue/5"
                    >
                        <!-- Card Header/Image -->
                        <div class="relative h-48 bg-gaming-dark overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-gaming-surface to-transparent z-10"></div>
                            <img 
                                v-if="mairie.image_path" 
                                :src="mairie.image_path" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gaming-blue/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4 z-20">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md border',
                                        mairie.is_active
                                            ? 'bg-gaming-green/20 text-gaming-green-light border-gaming-green/20'
                                            : 'bg-gray-500/20 text-gray-400 border-gray-500/20',
                                    ]"
                                >
                                    {{ mairie.is_active ? "Actif" : "Inactif" }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 relative">
                            <h3 class="text-xl font-black text-white mb-2 group-hover:text-gaming-blue-light transition-colors">
                                {{ mairie.name }}
                            </h3>
                            <p class="text-gray-500 text-xs leading-relaxed mb-6 line-clamp-2 h-8">
                                {{ mairie.description || 'Aucune description disponible pour cette ville.' }}
                            </p>

                            <!-- Mini Stats -->
                            <div class="flex items-center space-x-6 mb-8 py-4 border-y border-gaming-blue/5">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-600 font-bold uppercase tracking-tighter">Lieux</span>
                                    <span class="text-lg font-black text-white">{{ mairie.locations?.length || 0 }}</span>
                                </div>
                                <div class="flex flex-col border-l border-gaming-blue/5 pl-6">
                                    <span class="text-[10px] text-gray-600 font-bold uppercase tracking-tighter">Créateur</span>
                                    <span class="text-xs font-bold text-gaming-blue-light truncate w-24">
                                        {{ mairie.creator?.name || 'Inconnu' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-3">
                                <Link
                                    :href="route('admin.city.show', mairie.id)"
                                    class="w-full bg-gaming-blue/10 hover:bg-gaming-blue text-white font-bold py-3 px-4 rounded-xl text-center transition-all duration-300 flex items-center justify-center space-x-2 group/btn"
                                >
                                    <span>Gérer la ville</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                                
                                <div class="flex space-x-2">
                                    <button class="flex-1 text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-white transition-colors py-2">
                                        Modifier
                                    </button>
                                    <button class="flex-1 text-[10px] font-bold uppercase tracking-widest text-red-500/50 hover:text-red-500 transition-colors py-2">
                                        Désactiver
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations Cards Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="location in filteredLocations"
                        :key="location.id"
                        class="group bg-gaming-surface border border-gaming-blue/10 rounded-3xl overflow-hidden hover:border-gaming-blue/30 transition-all duration-500 hover:shadow-2xl hover:shadow-gaming-blue/5"
                    >
                        <!-- Card Header/Image -->
                        <div class="relative h-48 bg-gaming-dark overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-gaming-surface to-transparent z-10"></div>
                            <img 
                                v-if="location.location_images?.length > 0" 
                                :src="'/storage/' + location.location_images[0].image_path" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gaming-blue/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            
                            <!-- Category Badge -->
                            <div class="absolute top-4 right-4 z-20">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md border bg-gaming-blue/20 text-gaming-blue-light border-gaming-blue/20">
                                    {{ location.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 relative">
                            <div class="flex items-center space-x-2 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ location.city?.name }}</span>
                            </div>
                            <h3 class="text-xl font-black text-white mb-2 group-hover:text-gaming-blue-light transition-colors">
                                {{ location.name }}
                            </h3>
                            <p class="text-gray-500 text-xs leading-relaxed mb-6 line-clamp-2 h-8">
                                {{ location.description || 'Aucune description disponible pour ce lieu.' }}
                            </p>

                            <!-- Mini Stats -->
                            <div class="flex items-center space-x-6 mb-8 py-4 border-y border-gaming-blue/5">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-600 font-bold uppercase tracking-tighter">Énigmes</span>
                                    <span class="text-lg font-black text-white">{{ location.enigmas?.length || 0 }}</span>
                                </div>
                                <div class="flex flex-col border-l border-gaming-blue/5 pl-6">
                                    <span class="text-[10px] text-gray-600 font-bold uppercase tracking-tighter">Popularité</span>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-lg font-black text-white">{{ location.popularity || 0 }}</span>
                                        <span class="text-xs">⭐</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-3">
                                <Link
                                    :href="route('admin.city.show', location.city_id)"
                                    class="w-full bg-gaming-blue/10 hover:bg-gaming-blue text-white font-bold py-3 px-4 rounded-xl text-center transition-all duration-300 flex items-center justify-center space-x-2 group/btn"
                                >
                                    <span>Gérer les énigmes</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>

                                <div class="flex space-x-2">
                                    <button class="flex-1 text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-white transition-colors py-2">
                                        Modifier
                                    </button>
                                    <button class="flex-1 text-[10px] font-bold uppercase tracking-widest text-red-500/50 hover:text-red-500 transition-colors py-2">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
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
                    <div v-if="searchResults.length > 0" class="absolute z-50 w-full mt-1 bg-gaming-surface border border-gaming-blue/20 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        <div 
                            v-for="result in searchResults" 
                            :key="result.place_id"
                            @click="selectCity(result)"
                            class="p-3 hover:bg-gaming-blue/10 cursor-pointer border-b border-gaming-blue/5 last:border-0 transition-colors"
                        >
                            <div class="text-sm text-white font-medium">{{ result.display_name }}</div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-tighter">
                                {{ result.type }} • Lat: {{ result.lat }} • Lon: {{ result.lon }}
                            </div>
                        </div>
                    </div>

                    <div v-if="isSearching" class="absolute right-3 top-9">
                        <div class="animate-spin h-4 w-4 border-2 border-gaming-green border-t-transparent rounded-full"></div>
                    </div>
                </div>

                <div v-if="selectedCity" class="p-4 bg-gaming-green/5 border border-gaming-green/20 rounded-xl animate-fade-in">
                    <p class="text-xs text-gaming-green-light font-bold uppercase tracking-widest mb-2">Ville sélectionnée</p>
                    <p class="text-sm text-white">{{ selectedCity.display_name }}</p>
                    <div class="mt-2 flex space-x-4 text-[10px] text-gray-500 font-mono">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel
                            for="password"
                            value="Mot de passe"
                            class="text-gray-400"
                        />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.password"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmer"
                            class="text-gray-400"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.password_confirmation"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                    <div>
                        <InputLabel
                            for="name"
                            value="Description"
                            class="text-gray-400"
                        />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.description"
                            required
                            autofocus
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.description"
                        />
                    </div>
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
                        Créer le compte
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
