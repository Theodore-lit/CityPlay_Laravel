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
        
        if (props.cityContext && props.cityContext.lat && props.cityContext.lon) {
            const lat = parseFloat(props.cityContext.lat);
            const lon = parseFloat(props.cityContext.lon);
            const radius = parseFloat(props.cityContext.radius_meters || 5000);
            
            const radiusInDeg = radius / 111320;
            const left = (lon - radiusInDeg).toFixed(7);
            const right = (lon + radiusInDeg).toFixed(7);
            const top = (lat + radiusInDeg).toFixed(7);
            const bottom = (lat - radiusInDeg).toFixed(7);
            
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
        <label v-if="label" class="text-[9px] uppercase tracking-[0.4em] text-white/40 font-black mb-2 ml-2 block">
            {{ label }}
        </label>
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-primary transition-colors">
                <Search class="h-4 w-4" />
            </div>
            
            <!-- Mechanical Corners -->
            <div class="absolute top-0 left-0 w-2 h-2 border-t border-l border-white/20 group-focus-within:border-primary transition-colors" />
            <div class="absolute top-0 right-0 w-2 h-2 border-t border-r border-white/20 group-focus-within:border-primary transition-colors" />

            <input
                v-model="query"
                type="text"
                :placeholder="placeholder"
                class="w-full h-12 bg-white/[0.02] border-2 border-white/5 pl-12 pr-12 text-sm text-white font-black tracking-widest placeholder:text-white/10 outline-none focus:border-primary/40 focus:bg-primary/5 transition-all duration-500 uppercase"
                @focus="showResults = results.length > 0"
                @blur="hideResults"
            />
            
            <!-- Focus Glow Line -->
            <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-focus-within:w-full transition-all duration-700 shadow-[0_0_10px_#06b6d4]" />

            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <Loader2 v-if="loading" class="h-4 w-4 animate-spin text-primary" />
                <MapPin v-else class="h-4 w-4 text-white/10 group-focus-within:text-primary/60 transition-colors" />
            </div>
        </div>

        <!-- Results Dropdown HUD -->
        <Transition name="fade">
            <div v-if="showResults && results.length > 0" class="absolute z-[100] mt-3 w-full bg-zinc-950/95 border-2 border-white/10 rounded-2xl shadow-[0_0_50px_rgba(0,0,0,0.8)] backdrop-blur-2xl overflow-hidden animate-fade-up">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                <div class="max-h-60 overflow-y-auto custom-scrollbar relative z-10">
                    <button
                        v-for="result in results"
                        :key="result.place_id"
                        @click="selectResult(result)"
                        class="w-full px-6 py-4 text-left hover:bg-primary/10 transition-all border-b border-white/5 last:border-0 group/item"
                    >
                        <div class="flex items-center gap-4">
                            <MapPin class="h-4 w-4 text-white/20 group-hover/item:text-primary transition-colors shrink-0" />
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-white/80 group-hover/item:text-white transition-colors uppercase tracking-widest leading-tight">
                                    {{ result.display_name }}
                                </span>
                                <span v-if="result.address?.city" class="text-[8px] text-white/20 font-black uppercase tracking-[0.2em] mt-1">
                                    {{ result.address.city }} // {{ result.address.country }}
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
