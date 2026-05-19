<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import GpsSearchInput from '@/Components/GpsSearchInput.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  Users, Map, Target, TrendingUp, Activity, DollarSign,
  Plus, Settings, Building2, Brain, ChevronRight, Zap, Share2
} from 'lucide-vue-next';

import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    cities: Array,
    stats: Object,
});

const copyShareLink = async (city) => {
    const shareData = {
        title: `Aventure CityPlay : ${city.name}`,
        text: `Découvrez les trésors de ${city.name} à travers nos énigmes !`,
        url: `${window.location.origin}/player/game/${city.id}`
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await navigator.clipboard.writeText(shareData.url);
            alert('Lien de jeu pour votre ville copié !');
        }
    } catch (err) {
        console.error('Erreur lors du partage :', err);
    }
};

const showCityModal = ref(false);
const cityForm = useForm({
    name: '',
    description: '',
    latitude: '',
    longitude: '',
    radius_meters: 5000,
    image: null,
});

const onCitySelect = (data) => {
    cityForm.name = data.name;
    cityForm.latitude = data.lat;
    cityForm.longitude = data.lon;
};

const submitCity = () => {
    cityForm.post(route('mairie.cities.store'), {
        onSuccess: () => {
            showCityModal.value = false;
            cityForm.reset();
        }
    });
};

const adminStats = [
  { icon: Activity, label: "Sessions", value: props.stats.total_sessions || "0", delta: "+12%", color: "text-electric" },
  { icon: Users, label: "Joueurs Actifs", value: props.stats.active_players || "0", delta: "Live", color: "text-success" },
  { icon: Map, label: "Villes", value: props.cities?.length || "0", delta: "Gérées", color: "text-purple-neon" },
  { icon: Target, label: "Missions", value: props.cities?.reduce((acc, c) => acc + (c.locations_count || 0), 0) || "0", delta: "Total", color: "text-warning" },
];

const quickActions = computed(() => [
  { icon: Map, label: "Gérer les Lieux", to: route('mairie.dashboard') },
  { 
    icon: Brain, 
    label: "Gérer les Quiz", 
    to: props.cities?.length > 0 ? route('admin.cities.quizzes', props.cities[0].id) : route('mairie.dashboard') 
  },
  { icon: Target, label: "Statistiques", to: route('mairie.dashboard') },
]);
</script>

<template>
  <Head title="Console Maire — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
        <div>
          <div class="text-xs text-purple-neon uppercase tracking-widest font-bold">Centre de Contrôle</div>
          <h1 class="font-display text-3xl md:text-4xl mt-1">Console Administration</h1>
        </div>
        <div class="flex gap-3">
          <!-- La mairie ne crée plus de ville, seulement des lieux via les villes existantes -->
        </div>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="s in adminStats" :key="s.label" class="rounded-2xl glass p-5 hover-lift">
          <div class="flex justify-between items-start">
            <component :is="s.icon" :class="`h-6 w-6 ${s.color}`" />
            <span class="text-xs text-success">{{ s.delta }}</span>
          </div>
          <div class="mt-3 font-display text-2xl md:text-3xl">{{ s.value }}</div>
          <div class="text-xs uppercase tracking-widest text-muted-foreground mt-1">{{ s.label }}</div>
        </div>
      </div>

      <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <!-- ENGAGEMENT CHART PLACEHOLDER -->
        <div class="lg:col-span-2 rounded-2xl glass-strong p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg flex items-center gap-2"><TrendingUp class="h-5 w-5 text-electric" />Engagement</h2>
            <select class="bg-gaming-darker border border-electric/30 rounded-lg px-3 h-9 text-sm text-white outline-none focus:border-electric transition-all">
              <option class="bg-gaming-darker">7 derniers jours</option>
              <option class="bg-gaming-darker">30 derniers jours</option>
            </select>
          </div>
          <div class="h-56 flex items-end gap-2">
            <div v-for="(h, i) in [40, 65, 50, 80, 70, 95, 88, 92, 75, 100, 85, 90]" :key="i"
                 class="flex-1 rounded-t-lg bg-gradient-to-t from-electric/80 to-purple-neon/80 hover:from-electric hover:to-purple-neon transition-all relative group"
                 :style="{ height: `${h}%` }">
              <div class="absolute -top-7 left-1/2 -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100 transition glass px-2 py-0.5 rounded whitespace-nowrap">{{ h * 24 }}</div>
            </div>
          </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="rounded-2xl glass-strong p-6">
          <h2 class="font-display text-lg mb-4 flex items-center gap-2"><Zap class="h-5 w-5 text-warning" />Actions Rapides</h2>
          <div class="space-y-3">
            <template v-for="a in quickActions" :key="a.label">
              <button
                v-if="a.action"
                @click="a.action"
                class="w-full flex items-center gap-3 p-3 rounded-xl glass hover:border-electric hover:text-electric transition text-left group"
              >
                <div class="h-9 w-9 rounded-lg bg-gaming-darker border border-white/5 grid place-items-center group-hover:border-electric transition-colors">
                  <component :is="a.icon" class="h-5 w-5 text-electric" />
                </div>
                <span class="text-sm font-medium flex-1 text-white">{{ a.label }}</span>
                <Plus class="h-4 w-4 text-muted-foreground group-hover:text-electric transition-colors" />
              </button>
              <Link
                v-else
                :href="a.to"
                class="w-full flex items-center gap-3 p-3 rounded-xl glass hover:border-electric hover:text-electric transition text-left group"
              >
                <div class="h-9 w-9 rounded-lg bg-gaming-darker border border-white/5 grid place-items-center group-hover:border-electric transition-colors">
                  <component :is="a.icon" class="h-5 w-5 text-electric" />
                </div>
                <span class="text-sm font-medium flex-1 text-white">{{ a.label }}</span>
                <Plus class="h-4 w-4 text-muted-foreground group-hover:text-electric transition-colors" />
              </Link>
            </template>
          </div>
        </div>
      </div>

      <!-- CITIES TABLE -->
      <div class="mt-8 rounded-2xl glass-strong overflow-hidden border border-electric/10">
        <div class="p-5 border-b border-electric/10 flex items-center justify-between">
          <h2 class="font-display text-lg">Mes Villes & Missions</h2>
          <Link href="/admin" class="text-xs text-electric hover:underline">Gérer tout</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted-foreground bg-gaming-darker/50">
              <tr class="border-b border-electric/10">
                <th class="text-left p-4">Ville / Mission</th>
                <th class="text-left p-4">Lieux</th>
                <th class="text-left p-4">Sessions</th>
                <th class="text-left p-4">Statut</th>
                <th class="text-right p-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="city in cities" :key="city.id" class="border-b border-electric/5 hover:bg-electric/5 transition-colors group/city">
                <td class="p-4">
                  <Link :href="route('mairie.cities.show', city.id)" class="block">
                    <div class="font-display text-base text-white group-hover/city:text-electric transition-colors">{{ city.name }}</div>
                    <div class="text-xs text-muted-foreground truncate max-w-xs mt-1 italic">{{ city.description || 'Pas de briefing.' }}</div>
                  </Link>
                </td>
                <td class="p-4 text-muted-foreground font-mono text-xs">{{ city.locations_count || 0 }} secteurs</td>
                <td class="p-4 text-white">
                  <div class="flex items-center gap-2">
                    <div :class="`h-2 w-2 rounded-full ${city.is_active ? 'bg-success shadow-[0_0_10px_oklch(0.74_0.18_155)]' : 'bg-warning'}`"></div>
                    <span class="text-[10px] uppercase font-black tracking-widest text-white">{{ city.is_active ? 'Actif' : 'En Attente' }}</span>
                  </div>
                </td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-3">
                    <button @click="copyShareLink(city)" class="h-9 w-9 rounded-xl glass border-white/10 grid place-items-center text-purple-neon hover:bg-purple-neon hover:text-white transition-all shadow-sm" title="Partager le lien">
                      <Share2 class="h-5 w-5" />
                    </button>
                    <Link :href="route('admin.cities.quizzes', city.id)" class="h-9 w-9 rounded-xl glass border-white/10 grid place-items-center text-warning hover:bg-warning hover:text-white transition-all shadow-sm" title="Gérer les Quiz">
                      <Brain class="h-5 w-5" />
                    </Link>
                    <Link
                      :href="route('mairie.city.toggle', city.id)"
                      method="patch"
                      as="button"
                      :class="`text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-xl border transition-all ${city.is_active ? 'text-warning border-warning/20 hover:bg-warning/10' : 'text-success border-success/20 hover:bg-success/10'}`"
                    >
                      {{ city.is_active ? 'Désactiver' : 'Activer' }}
                    </Link>
                    <Link :href="route('mairie.cities.show', city.id)" class="h-9 w-9 rounded-xl glass border-white/10 grid place-items-center text-electric hover:bg-electric hover:text-white transition-all shadow-sm">
                      <ChevronRight class="h-5 w-5" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ADD CITY MODAL -->
    <div v-if="showCityModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/80 backdrop-blur-md">
        <div class="glass-strong rounded-3xl p-8 w-full max-w-md border border-electric/20 animate-fade-up">
            <h2 class="font-display text-2xl text-white mb-6">Nouvelle Ville</h2>
            <form @submit.prevent="submitCity" class="space-y-4">
                <GpsSearchInput
                    v-model="cityForm.name"
                    label="Nom de la Ville (Recherche GPS)"
                    placeholder="Ex: Porto-Novo, Bénin"
                    @select="onCitySelect"
                    required
                />

                <GlowInput v-model="cityForm.radius_meters" type="number" label="Rayon de la zone (mètres)" placeholder="5000" required />

                <div>
                    <label class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block">Briefing de la Ville</label>
                    <textarea v-model="cityForm.description" class="w-full h-24 rounded-xl bg-gaming-darker border border-white/10 p-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none transition-all" placeholder="Description de la ville et ses trésors..."></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block">Image de couverture</label>
                    <input type="file" @input="cityForm.image = $event.target.files[0]" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-electric/10 file:text-electric hover:file:bg-electric/20 transition-all" />
                </div>

                <div v-if="cityForm.latitude" class="p-3 rounded-xl bg-electric/5 border border-electric/20 flex items-center justify-between animate-fade-up">
                    <div class="text-[10px] text-electric font-bold uppercase tracking-widest flex items-center gap-2">
                        <MapPin class="h-3 w-3" /> Coordonnées Verrouillées
                    </div>
                    <div class="text-[10px] font-mono text-muted-foreground">
                        {{ cityForm.latitude }}, {{ cityForm.longitude }}
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <NeonButton type="button" variant="outline" class="flex-1" @click="showCityModal = false">Annuler</NeonButton>
                    <NeonButton type="submit" class="flex-1" :disabled="cityForm.processing || !cityForm.latitude">Créer</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <MobileTabBar />
  </SiteLayout>
</template>
