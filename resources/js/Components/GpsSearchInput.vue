<script setup>
import { ref, watch } from 'vue';
import { Search, MapPin, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    label: String,
    placeholder: String,
    modelValue: String,
    cityContext: Object, // Optionnel : pour restreindre la recherche à une zone
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref(props.modelValue || '');
const results = ref([]);
const loading = ref(false);
const showResults = ref(false);

const searchGps = async () => {
    if (query.value.length < 3) {
        results.value = [];
        return;
    }

    loading.value = true;
    try {
        let url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query.value)}&limit=5`;
        
        // Si on a un contexte de ville (lat/lon/radius), on peut essayer de restreindre
        if (props.cityContext && props.cityContext.lat && props.cityContext.lon) {
            // approximation: 1 degré lat ~ 111km, 1 degré lon ~ 111km * cos(lat)
            const radiusKm = (props.cityContext.radius_meters || 5000) / 1000;
            const latDegreeOffset = radiusKm / 111;
            const lonDegreeOffset = radiusKm / (111 * Math.cos(props.cityContext.lat * (Math.PI / 180)));
            
            const left = props.cityContext.lon - lonDegreeOffset;
            const right = props.cityContext.lon + lonDegreeOffset;
            const top = props.cityContext.lat + latDegreeOffset;
            const bottom = props.cityContext.lat - latDegreeOffset;
            
            url += `&viewbox=${left},${top},${right},${bottom}&bounded=1`;
        }

        const response = await fetch(url, {
            headers: {
                'Accept-Language': 'fr',
                'User-Agent': 'CityPlay-App'
            }
        });
        results.value = await response.json();
        showResults.value = true;
    } catch (error) {
        console.error('Erreur de recherche GPS:', error);
    } finally {
        loading.value = false;
    }
};

let timeout = null;
watch(query, (newQuery) => {
    emit('update:modelValue', newQuery);
    clearTimeout(timeout);
    timeout = setTimeout(searchGps, 500);
});

const selectResult = (result) => {
    query.value = result.display_name;
    showResults.value = false;
    emit('select', {
        name: result.display_name,
        lat: parseFloat(result.lat),
        lon: parseFloat(result.lon),
        address: result.address
    });
};

const hideResults = () => {
    setTimeout(() => {
        showResults.value = false;
    }, 200);
};
</script>

<template>
    <div class="relative">
        <label v-if="label" class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block">
            {{ label }}
        </label>
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-electric transition-colors">
                <Search class="h-4 w-4" />
            </div>
            <input
                v-model="query"
                type="text"
                :placeholder="placeholder"
                class="w-full h-11 rounded-xl bg-gaming-darker border border-white/10 pl-10 pr-10 text-sm text-white placeholder:text-muted-foreground/40 focus:border-electric outline-none transition-all"
                @focus="showResults = results.length > 0"
                @blur="hideResults"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <Loader2 v-if="loading" class="h-4 w-4 text-electric animate-spin" />
                <MapPin v-else class="h-4 w-4 text-muted-foreground" />
            </div>
        </div>

        <!-- RÉSULTATS -->
        <div v-if="showResults && results.length > 0" class="absolute z-[110] left-0 right-0 mt-2 rounded-2xl glass-strong border border-electric/20 overflow-hidden shadow-neon max-h-60 overflow-y-auto">
            <button
                v-for="result in results"
                :key="result.place_id"
                type="button"
                class="w-full text-left p-3 hover:bg-electric/10 border-b border-white/5 last:border-0 transition-colors"
                @click="selectResult(result)"
            >
                <div class="text-sm text-white font-bold truncate">{{ result.display_name }}</div>
                <div class="text-[10px] text-muted-foreground uppercase tracking-tighter">
                    LAT: {{ parseFloat(result.lat).toFixed(4) }} • LON: {{ parseFloat(result.lon).toFixed(4) }}
                </div>
            </button>
        </div>
    </div>
</template>
