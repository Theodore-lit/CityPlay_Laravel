<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import GpsSearchInput from '@/Components/GpsSearchInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  MapPin, Plus, Target, ChevronLeft, Map, Sparkles, HelpCircle, Save, Trash2,
  Image as ImageIcon, List, Settings, Info, Navigation
} from 'lucide-vue-next';
import { ref } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    city: Object,
});

const showLocationModal = ref(false);
const showImageModal = ref(false);
const selectedLocation = ref(null);
const showEnigmaModal = ref(false);

const locationForm = useForm({
    name: '',
    description: '',
    category: 'historique',
    latitude: '',
    longitude: '',
    radius_meters: 50,
});

const onLocationSelect = (data) => {
    locationForm.name = data.name;
    locationForm.latitude = data.lat;
    locationForm.longitude = data.lon;
};

const imageForm = useForm({
    image_url: '',
});

const openImageModal = (location) => {
    selectedLocation.value = location;
    imageForm.image_url = ''; // Or existing image if field exists
    showImageModal.value = true;
};

const submitImage = () => {
    // Assuming we just store one URL in 'images' json field for now
    imageForm.post(route('mairie.locations.image', selectedLocation.value.id), {
        onSuccess: () => {
            showImageModal.value = false;
            imageForm.reset();
        }
    });
};

const enigmaForm = useForm({
    id: null,
    title: '',
    content: '',
    difficulty: 'easy',
    answer: '',
    indices: ['', ''],
    reward_coins: 10,
    reward_hearts: 0,
    type: 'text',
    image: null,
    is_site_specific: false,
});

const submitLocation = () => {
    locationForm.post(route('mairie.locations.store', props.city.id), {
        onSuccess: () => {
            showLocationModal.value = false;
            locationForm.reset();
        }
    });
};

const openEnigmaModal = (location, enigma = null) => {
    selectedLocation.value = location;
    if (enigma) {
        enigmaForm.id = enigma.id;
        enigmaForm.title = enigma.title;
        enigmaForm.content = enigma.content;
        enigmaForm.difficulty = enigma.difficulty || 'easy';
        enigmaForm.answer = enigma.answer;
        enigmaForm.indices = enigma.indices && enigma.indices.length > 0 ? [...enigma.indices] : ['', ''];
        enigmaForm.reward_coins = enigma.reward_coins;
        enigmaForm.reward_hearts = enigma.reward_hearts;
        enigmaForm.type = enigma.type;
        enigmaForm.is_site_specific = enigma.is_site_specific;
        enigmaForm.image = null; // Don't preload image file
    } else {
        enigmaForm.reset();
        enigmaForm.id = null;
        enigmaForm.indices = ['', ''];
    }
    showEnigmaModal.value = true;
};

const addIndex = () => {
    enigmaForm.indices.push('');
};

const removeIndex = (index) => {
    enigmaForm.indices.splice(index, 1);
};

const submitEnigma = () => {
    enigmaForm.post(route('mairie.enigmas.store', selectedLocation.value.id), {
        onSuccess: () => {
            showEnigmaModal.value = false;
            enigmaForm.reset();
        }
    });
};
</script>

<template>
  <Head :title="`Gérer ${city.name} — CityPlay HQ`" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-5">
            <Link :href="route('mairie.dashboard')" class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all shadow-neon border border-electric/20">
            <ChevronLeft class="h-6 w-6" />
            </Link>
            <div>
            <div class="text-xs text-electric uppercase tracking-[0.2em] font-black mb-1">Centre de Commandement</div>
            <h1 class="font-display text-3xl md:text-5xl text-foreground">{{ city.name }}</h1>
            </div>
        </div>
        <div class="flex gap-3">
            <NeonButton variant="outline" size="sm">
                <Settings class="h-4 w-4 mr-2" />Paramètres Ville
            </NeonButton>
            <NeonButton @click="showLocationModal = true" size="sm">
                <Plus class="h-4 w-4 mr-2" />Ajouter un Lieu
            </NeonButton>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-12">
        <!-- Sidebar: City Overview -->
        <div class="lg:col-span-4 space-y-6">
          <div class="glass-strong rounded-[2rem] p-6 border border-white/5 relative overflow-hidden">
            <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none" />

            <div class="relative z-10">
                <div class="aspect-[16/10] rounded-2xl overflow-hidden mb-6 bg-gaming-darker border border-white/10 shadow-elevated">
                    <img v-if="city.image_path" :src="city.image_path" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full grid place-items-center text-electric/10">
                        <Map class="h-16 w-16" />
                    </div>
                </div>

                <h2 class="font-display text-xl mb-3 flex items-center gap-2 text-foreground">
                    <Info class="h-5 w-5 text-electric" />Briefing de Mission
                </h2>
                <p class="text-sm text-muted-foreground leading-relaxed italic border-l-2 border-electric/30 pl-4">
                    {{ city.description || 'Aucune description stratégique pour cette ville.' }}
                </p>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="glass p-5 rounded-3xl text-center border border-white/5">
                        <div class="text-3xl font-display text-electric">{{ city.locations.length }}</div>
                        <div class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mt-1">Lieux Actifs</div>
                    </div>
                    <div class="glass p-5 rounded-3xl text-center border border-white/5">
                        <div class="text-3xl font-display text-purple-neon">
                            {{ city.locations.reduce((acc, loc) => acc + (loc.enigmas?.length || 0), 0) }}
                        </div>
                        <div class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mt-1">Énigmes</div>
                    </div>
                </div>
            </div>
          </div>

          <div class="glass-strong rounded-3xl p-6 border border-white/5">
              <h3 class="font-display text-sm uppercase tracking-widest text-muted-foreground mb-4">Statistiques de Joueurs</h3>
              <div class="space-y-4">
                  <div class="flex justify-between items-center text-sm">
                      <span class="text-muted-foreground">Explorateurs uniques</span>
                      <span class="font-display text-foreground">1,240</span>
                  </div>
                  <div class="flex justify-between items-center text-sm">
                      <span class="text-muted-foreground">Taux de réussite</span>
                      <span class="font-display text-success">68%</span>
                  </div>
              </div>
          </div>
        </div>

        <!-- Main Content: Locations List -->
        <div class="lg:col-span-8 space-y-6">
          <div class="glass-strong rounded-[2rem] border border-white/5 overflow-hidden">
            <div class="p-8 border-b border-white/5 flex items-center justify-between bg-white/5">
              <h2 class="font-display text-xl flex items-center gap-3 text-foreground">
                <Navigation class="h-6 w-6 text-electric" />Déploiement des Objectifs
              </h2>
              <span class="px-4 py-1 rounded-full bg-electric/10 text-electric text-[10px] font-black tracking-widest border border-electric/20 uppercase">
                En Ligne
              </span>
            </div>

            <div class="divide-y divide-white/5">
              <div v-for="loc in city.locations" :key="loc.id" class="p-8 hover:bg-electric/5 transition-all duration-500 group">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                  <div class="flex items-start gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-gaming-darker border border-white/10 grid place-items-center text-electric shrink-0 shadow-inner group-hover:border-electric/40 transition-colors">
                      <MapPin class="h-7 w-7" />
                    </div>
                    <div>
                      <div class="flex items-center gap-3">
                          <h3 class="font-display text-xl text-foreground">{{ loc.name }}</h3>
                          <span class="text-[10px] px-2 py-0.5 rounded bg-white/10 text-muted-foreground uppercase font-bold">{{ loc.category }}</span>
                      </div>
                      <p class="text-sm text-muted-foreground mt-1 line-clamp-1">{{ loc.description || 'Aucune description.' }}</p>
                      <div class="text-[10px] font-mono text-electric/60 flex items-center gap-4 mt-3">
                        <span class="flex items-center gap-1"><Target class="h-3 w-3" /> GPS: {{ loc.latitude }}, {{ loc.longitude }}</span>
                        <span class="flex items-center gap-1"><Navigation class="h-3 w-3" /> Rayon: {{ loc.radius_meters }}m</span>
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
                      @click="openImageModal(loc)"
                      class="h-11 w-11 rounded-xl glass border-white/10 text-muted-foreground hover:text-white transition-all grid place-items-center"
                    >
                      <ImageIcon class="h-4 w-4" />
                    </button>
                    <button class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive hover:bg-destructive hover:text-white transition-all grid place-items-center">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <!-- Enigmas List for this location -->
                <div v-if="loc.enigmas?.length" class="mt-8 space-y-4 ml-0 md:ml-16">
                  <div v-for="enigma in loc.enigmas" :key="enigma.id"
                       class="p-5 rounded-3xl bg-purple-neon/5 border border-purple-neon/10 animate-fade-up relative overflow-hidden group/enigma">
                    <div class="absolute top-0 right-0 p-4 opacity-0 group-hover/enigma:opacity-100 transition-opacity">
                        <button @click="openEnigmaModal(loc, enigma)" class="text-purple-neon hover:text-white transition-colors">
                            <Settings class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex items-center gap-3 mb-2">
                        <HelpCircle class="h-4 w-4 text-purple-neon" />
                        <span class="text-xs font-black uppercase tracking-widest text-purple-neon">{{ enigma.title || 'Énigme sans titre' }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-neon/10 text-purple-neon border border-purple-neon/20">{{ enigma.type }}</span>
                    </div>
                    <p class="text-sm italic text-muted-foreground pl-7">"{{ enigma.content }}"</p>
                    <div class="mt-4 pl-7 flex flex-wrap gap-4 items-center">
                        <div class="text-[10px] font-bold text-success flex items-center gap-1.5 uppercase tracking-widest">
                            <Sparkles class="h-3.5 w-3.5" /> Recompense: {{ enigma.reward_coins }} Coins
                        </div>
                        <div v-if="enigma.indices?.length" class="text-[10px] font-bold text-warning flex items-center gap-1.5 uppercase tracking-widest">
                            <List class="h-3.5 w-3.5" /> {{ enigma.indices.length }} Indices configurés
                        </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="!city.locations.length" class="p-24 text-center text-muted-foreground bg-gaming-darker/30">
                <div class="h-20 w-20 rounded-full bg-electric/5 border border-electric/10 grid place-items-center mx-auto mb-6">
                    <MapPin class="h-10 w-10 opacity-20 text-electric" />
                </div>
                <h3 class="font-display text-xl text-foreground mb-2">Zone Vierge</h3>
                <p class="max-w-xs mx-auto text-sm">Aucun objectif tactique n'a été déployé dans cette ville.</p>
                <NeonButton @click="showLocationModal = true" variant="outline" size="sm" class="mt-8">
                    Déployer le premier lieu
                </NeonButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ADD LOCATION MODAL -->
    <div v-if="showLocationModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl">
        <div class="glass-strong rounded-[2.5rem] p-10 w-full max-w-xl border border-electric/30 animate-fade-up shadow-neon">
            <div class="flex items-center gap-4 mb-8">
                <div class="h-12 w-12 rounded-2xl bg-electric/20 grid place-items-center text-electric">
                    <MapPin class="h-6 w-6" />
                </div>
                <h2 class="font-display text-3xl text-foreground">Nouveau Secteur</h2>
            </div>

            <form @submit.prevent="submitLocation" class="space-y-6">
                <GpsSearchInput
                    v-model="locationForm.name"
                    label="Nom de reconnaissance (Recherche GPS)"
                    placeholder="Ex: Place de l'Indépendance"
                    :city-context="{ lat: city.latitude, lon: city.longitude, radius_meters: city.radius_meters }"
                    @select="onLocationSelect"
                    required
                />

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Secteur Tactique</label>
                    <textarea v-model="locationForm.description" class="w-full h-24 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-foreground placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none transition-all" placeholder="Décrivez l'importance de ce lieu..."></textarea>
                </div>

                <div v-if="locationForm.latitude" class="p-4 rounded-2xl bg-electric/5 border border-electric/20 flex items-center justify-between animate-fade-up">
                    <div class="text-[10px] text-electric font-bold uppercase tracking-widest flex items-center gap-2">
                        <Target class="h-4 w-4" /> Coordonnées Verrouillées
                    </div>
                    <div class="text-xs font-mono text-foreground">
                        {{ locationForm.latitude }}, {{ locationForm.longitude }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <GlowInput label="Latitude (Manuel)" type="number" step="any" v-model="locationForm.latitude" required />
                    <GlowInput label="Longitude (Manuel)" type="number" step="any" v-model="locationForm.longitude" required />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <GlowInput label="Rayon (m)" type="number" v-model="locationForm.radius_meters" required />
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Catégorie</label>
                        <select v-model="locationForm.category" class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-foreground focus:border-electric outline-none appearance-none">
                            <option value="historique">Historique</option>
                            <option value="culturel">Culturel</option>
                            <option value="nature">Nature</option>
                            <option value="moderne">Moderne</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 flex gap-4">
                    <NeonButton type="button" variant="outline" class="flex-1" @click="showLocationModal = false">Abandonner</NeonButton>
                    <NeonButton type="submit" class="flex-1" :disabled="locationForm.processing || !locationForm.latitude">Confirmer le Déploiement</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <!-- ENIGMA MODAL -->
    <div v-if="showEnigmaModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl">
        <div class="glass-strong rounded-[2.5rem] p-10 w-full max-w-2xl border border-purple-neon/30 animate-fade-up shadow-purple max-h-[90vh] overflow-y-auto custom-scrollbar relative">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="flex items-center gap-4 mb-8 relative z-10">
                <div class="h-12 w-12 rounded-2xl bg-purple-neon/20 grid place-items-center text-purple-neon">
                    <HelpCircle class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="font-display text-3xl text-white">{{ enigmaForm.id ? 'Éditer l\'Énigme' : 'Nouvelle Énigme' }}</h2>
                    <p class="text-xs text-muted-foreground uppercase tracking-widest mt-1">Cible: {{ selectedLocation?.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submitEnigma" class="space-y-6 relative z-10">
                <div class="grid grid-cols-2 gap-6">
                    <GlowInput label="Titre stratégique" v-model="enigmaForm.title" placeholder="Ex: Le Secret du Palais" required />
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Difficulté</label>
                        <select v-model="enigmaForm.difficulty" class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white focus:border-purple-neon outline-none">
                            <option value="easy">Facile (Déblocage)</option>
                            <option value="medium">Moyen (Tactique)</option>
                            <option value="hard">Difficile (Légendaire)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Type de Verrou</label>
                        <select v-model="enigmaForm.type" class="w-full h-12 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white focus:border-purple-neon outline-none">
                            <option value="text">Question textuelle</option>
                            <option value="qr">Scan QR Code</option>
                            <option value="image">Reconnaissance Image</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" id="is_site" v-model="enigmaForm.is_site_specific" class="rounded bg-gaming-darker border-purple-neon/30 text-purple-neon focus:ring-purple-neon" />
                        <label for="is_site" class="text-xs text-white font-bold cursor-pointer">Énigme sur site (GPS requis)</label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Image de l'Énigme (Optionnel)</label>
                    <input type="file" @input="enigmaForm.image = $event.target.files[0]" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-purple-neon/10 file:text-purple-neon hover:file:bg-purple-neon/20 transition-all" />
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black ml-1">Contenu de l'Énigme</label>
                    <textarea v-model="enigmaForm.content" class="w-full h-24 rounded-2xl bg-gaming-darker border border-white/10 p-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-purple-neon outline-none resize-none transition-all" placeholder="L'énigme que le joueur doit résoudre..." required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <GlowInput label="Réponse" v-model="enigmaForm.answer" placeholder="La réponse exacte..." required />
                    <div class="grid grid-cols-2 gap-2">
                        <GlowInput label="XP" type="number" v-model="enigmaForm.reward_coins" required />
                        <GlowInput label="Vies ❤️" type="number" v-model="enigmaForm.reward_hearts" required />
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <label class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-black">Indices tactiques</label>
                        <button type="button" @click="addIndex" class="text-xs text-purple-neon hover:underline font-bold flex items-center gap-1">
                            <Plus class="h-3 w-3" /> Ajouter un indice
                        </button>
                    </div>
                    <div v-for="(index, i) in enigmaForm.indices" :key="i" class="flex gap-2">
                        <div class="flex-1">
                            <input v-model="enigmaForm.indices[i]" class="w-full h-11 rounded-xl bg-gaming-darker border border-white/10 px-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-purple-neon outline-none" :placeholder="`Indice #${i+1}`" />
                        </div>
                        <button type="button" @click="removeIndex(i)" class="h-11 w-11 rounded-xl glass border-destructive/20 text-destructive grid place-items-center hover:bg-destructive hover:text-white transition-all">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="pt-6 flex gap-4 sticky bottom-0 bg-inherit pb-2">
                    <NeonButton type="button" variant="outline" class="flex-1" @click="showEnigmaModal = false">Annuler</NeonButton>
                    <NeonButton type="submit" variant="purple" class="flex-1" :disabled="enigmaForm.processing">Sauvegarder l'Énigme</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <!-- IMAGE MODAL -->
    <div v-if="showImageModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/90 backdrop-blur-xl">
        <div class="glass-strong rounded-[2.5rem] p-10 w-full max-w-md border border-electric/30 animate-fade-up shadow-neon">
            <div class="flex items-center gap-4 mb-8">
                <div class="h-12 w-12 rounded-2xl bg-electric/20 grid place-items-center text-electric">
                    <ImageIcon class="h-6 w-6" />
                </div>
                <h2 class="font-display text-3xl text-white">Images du Secteur</h2>
            </div>

            <form @submit.prevent="submitImage" class="space-y-6">
                <GlowInput label="URL de l'image de reconnaissance" v-model="imageForm.image_url" placeholder="https://..." required />
                <p class="text-[10px] text-muted-foreground uppercase tracking-widest leading-relaxed">
                    Cette image sera utilisée pour aider les joueurs à identifier le lieu lors de leur exploration.
                </p>

                <div class="pt-6 flex gap-4">
                    <NeonButton type="button" variant="outline" class="flex-1" @click="showImageModal = false">Annuler</NeonButton>
                    <NeonButton type="submit" class="flex-1" :disabled="imageForm.processing">Mettre à jour</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <MobileTabBar />
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
