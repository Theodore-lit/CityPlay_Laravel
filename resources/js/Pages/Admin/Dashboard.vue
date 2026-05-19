<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  Users, Map, Target, Activity, Plus, Building2, 
  Brain, ChevronRight, ShieldCheck, Zap, Share2, 
  Ban, CheckCircle, Calendar, MapPin
} from 'lucide-vue-next';

import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import GpsSearchInput from '@/Components/GpsSearchInput.vue';

const props = defineProps({
    cities: { type: Array, default: () => [] },
    mairies: { type: Array, default: () => [] },
    players: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const adminStats = [
  { icon: Users, label: "Utilisateurs", value: props.stats.total_users || "0", delta: "+5.2%", color: "text-primary dark:text-cyan-400" },
  { icon: ShieldCheck, label: "Mairies", value: props.mairies?.length || "0", delta: "Actifs", color: "text-emerald-600 dark:text-emerald-400" },
  { icon: Target, label: "Sessions", value: props.stats.total_sessions || "0", delta: "+2.1%", color: "text-purple-600 dark:text-purple-400" },
  { icon: Activity, label: "Joueurs Actifs", value: props.stats.active_players || "0", delta: "Live", color: "text-amber-600 dark:text-amber-400" },
];

const showMairieModal = ref(false);
const mairieForm = useForm({
    city_name: '',
    email: '',
    description: '',
    latitude: '',
    longitude: '',
    radius_meters: 5000,
});

const onCitySelect = (data) => {
    mairieForm.city_name = data.name;
    mairieForm.latitude = data.lat;
    mairieForm.longitude = data.lon;
};

const submitMairie = () => {
    mairieForm.post(route('admin.mairie-city.store'), {
        onSuccess: () => {
            showMairieModal.value = false;
            mairieForm.reset();
        }
    });
};

const toggleUser = (userId) => {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet utilisateur ? S\'il est désactivé, il ne pourra plus se connecter.')) {
        useForm({}).patch(route('admin.users.toggle', userId));
    }
};

const copyShareLink = async (city) => {
    const shareData = {
        title: `Aventure CityPlay : ${city.name}`,
        text: `Rejoins-moi pour explorer ${city.name} et résoudre des énigmes passionnantes sur CityPlay !`,
        url: `${window.location.origin}/player/game/${city.id}`
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await navigator.clipboard.writeText(shareData.url);
            alert('Lien de jeu copié dans le presse-papiers.');
        }
    } catch (err) {
        console.error('Erreur lors du partage :', err);
    }
};

const quickActions = [
  { icon: Building2, label: "Toutes les Villes", to: route('admin.cities') },
  { icon: ShieldCheck, label: "Gestion Mairies", to: route('admin.dashboard') },
  { icon: Users, label: "Gestion Utilisateurs", to: route('admin.dashboard') },
  { icon: Activity, label: "Analyse Sessions", to: route('admin.dashboard') },
];
</script>

<template>
  <Head title="Super Admin — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 py-10 pb-28 sm:px-6 md:pb-12 text-foreground">
      
      <!-- HEADER -->
      <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="text-xs font-black uppercase tracking-widest text-primary dark:text-cyan-400">Quartier Général</div>
          <h1 class="font-display text-3xl md:text-4xl mt-1 uppercase italic font-black">
            Tableau de Bord <span class="text-primary neon-text dark:text-cyan-400">Admin</span>
          </h1>
        </div>
        <div class="flex gap-3">
          <NeonButton @click="showMairieModal = true" size="sm">
            <Plus class="mr-2 h-4 w-4" /> Ajouter une Mairie
          </NeonButton>
        </div>
      </div>

      <!-- STATS GRID -->
      <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div v-for="s in adminStats" :key="s.label" class="glass group relative overflow-hidden rounded-2xl p-5 shadow-sm transition-all hover:translate-y-[-2px] hover:shadow-md">
          <div class="flex items-start justify-between">
            <component :is="s.icon" :class="['h-6 w-6', s.color]" />
            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80">{{ s.delta }}</span>
          </div>
          <div class="font-display text-2xl font-black mt-3 text-foreground dark:text-white md:text-3xl">{{ s.value }}</div>
          <div class="mt-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ s.label }}</div>
        </div>
      </div>

      <!-- MAIN LAYOUT CONTENT -->
      <div class="mt-8 grid gap-8 lg:grid-cols-12">
        
        <!-- CITIES WORKSPACE -->
        <div class="glass-strong overflow-hidden rounded-[2rem] lg:col-span-8">
          <div class="flex items-center justify-between border-b border-border bg-muted/30 p-6">
            <h2 class="font-display text-xl flex items-center gap-3 uppercase italic font-black">
              <Map class="h-6 w-6 text-primary dark:text-cyan-400" /> Déploiement Global des Villes
            </h2>
            <Link :href="route('admin.cities')" class="text-xs font-black uppercase tracking-wider text-primary hover:underline dark:text-cyan-400">Tout voir</Link>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gaming-darker/60 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">
                <tr class="border-b border-border">
                  <th class="p-6 text-left">Secteur / Mission</th>
                  <th class="p-6 text-left">Objectifs</th>
                  <th class="p-6 text-left">Statut Tactique</th>
                  <th class="p-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr v-for="city in cities" :key="city.id" class="group/city transition-all hover:bg-primary/5">
                  <td class="p-6">
                    <Link :href="route('mairie.cities.show', city.id)" class="block">
                      <div class="font-display text-base font-black uppercase text-foreground dark:text-white group-hover/city:text-primary dark:group-hover/city:text-cyan-400 transition-colors">{{ city.name }}</div>
                      <div class="mt-1 max-w-xs truncate text-xs font-medium italic text-muted-foreground">{{ city.description || 'Pas de briefing.' }}</div>
                    </Link>
                  </td>
                  <td class="p-6 font-mono text-xs text-muted-foreground font-semibold">{{ city.locations_count || 0 }} secteurs</td>
                  <td class="p-6">
                    <div class="flex items-center gap-2">
                      <div :class="['h-2 w-2 rounded-full', city.is_active ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-amber-500']" />
                      <span class="text-[10px] font-black uppercase tracking-widest text-foreground dark:text-white">
                        {{ city.is_active ? 'Actif' : 'En Attente' }}
                      </span>
                    </div>
                  </td>
                  <td class="p-6 text-right">
                    <div class="flex justify-end gap-2">
                      <Link :href="route('mairie.cities.events', city.id)" class="glass flex h-10 w-10 items-center justify-center rounded-xl text-accent hover:bg-accent hover:text-white dark:hover:text-black transition-all" title="Gérer les Événements">
                        <Calendar class="h-5 w-5" />
                      </Link>
                      <Link :href="route('admin.cities.quizzes', city.id)" class="glass flex h-10 w-10 items-center justify-center rounded-xl text-purple-600 dark:text-purple-400 hover:bg-purple-600 dark:hover:bg-purple-400 hover:text-white dark:hover:text-black transition-all" title="Gérer les Quiz">
                        <Brain class="h-5 w-5" />
                      </Link>
                      <button @click="copyShareLink(city)" class="glass flex h-10 w-10 items-center justify-center rounded-xl text-sky-600 dark:text-sky-400 hover:bg-sky-600 dark:hover:bg-sky-400 hover:text-white dark:hover:text-black transition-all" title="Partager le lien">
                        <Share2 class="h-5 w-5" />
                      </button>
                      <Link :href="route('mairie.cities.show', city.id)" class="glass flex h-10 w-10 items-center justify-center rounded-xl text-primary dark:text-cyan-400 hover:bg-primary dark:hover:bg-cyan-400 hover:text-white dark:hover:text-black transition-all">
                        <ChevronRight class="h-5 w-5" />
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- USERS CONTROL WORKSPACE -->
        <div class="glass-strong overflow-hidden rounded-[2rem] lg:col-span-8 lg:mt-0">
          <div class="flex items-center justify-between border-b border-border bg-muted/30 p-6">
            <h2 class="font-display text-xl flex items-center gap-3 uppercase italic font-black">
              <Users class="h-6 w-6 text-primary dark:text-cyan-400" /> Contrôle des Joueurs
            </h2>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gaming-darker/60 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">
                <tr class="border-b border-border">
                  <th class="p-6 text-left">Joueur</th>
                  <th class="p-6 text-left">Niveau / XP</th>
                  <th class="p-6 text-left">Statut</th>
                  <th class="p-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr v-for="player in players" :key="player.id" class="group/player transition-all hover:bg-primary/5">
                  <td class="p-6">
                    <div class="flex items-center gap-3">
                      <div class="glass flex h-10 w-10 items-center justify-center rounded-full border-primary/20 bg-primary/10 font-display font-black text-primary dark:text-cyan-400">
                        {{ player.name ? player.name.charAt(0).toUpperCase() : 'P' }}
                      </div>
                      <div>
                        <div class="font-bold text-foreground dark:text-white">{{ player.name }}</div>
                        <div class="text-[10px] text-muted-foreground font-medium">{{ player.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="p-6">
                    <div class="font-mono text-xs font-semibold text-foreground dark:text-white">LVL {{ player.level }} • {{ player.xp }} XP</div>
                  </td>
                  <td class="p-6">
                    <div class="flex items-center gap-2">
                      <div :class="['h-2 w-2 rounded-full', player.is_active ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.5)]']" />
                      <span class="text-[10px] font-black uppercase tracking-widest text-foreground dark:text-white">
                        {{ player.is_active ? 'Opérationnel' : 'Désactivé' }}
                      </span>
                    </div>
                  </td>
                  <td class="p-6 text-right">
                    <button 
                      @click="toggleUser(player.id)" 
                      :class="['inline-flex h-10 items-center gap-2 rounded-xl border border-border px-4 text-xs font-black uppercase tracking-wider transition-all', 
                               player.is_active ? 'bg-rose-500/10 text-rose-600 hover:bg-rose-600 hover:text-white dark:text-rose-400 dark:hover:bg-rose-500' : 'bg-emerald-500/10 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:text-emerald-400 dark:hover:bg-emerald-500']"
                    >
                      <Ban v-if="player.is_active" class="h-4 w-4" />
                      <CheckCircle v-else class="h-4 w-4" />
                      {{ player.is_active ? 'Désactiver' : 'Réactiver' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- RIGHT SIDEBAR (COMMANDANTS & PROTOCOLES) -->
        <div class="space-y-8 lg:col-span-4">
          
          <!-- COMMANDANTS SECTION -->
          <div class="glass-strong overflow-hidden rounded-[2rem]">
            <div class="border-b border-border p-6 bg-muted/20">
              <h2 class="font-display text-lg flex items-center gap-3 uppercase italic font-black">
                <ShieldCheck class="h-5 w-5 text-purple-600 dark:text-purple-400" /> Commandants
              </h2>
            </div>
            <div class="p-4 space-y-3 max-h-[360px] overflow-y-auto custom-scrollbar">
              <div v-for="mairie in mairies" :key="mairie.id" class="glass flex items-center gap-4 p-4 rounded-2xl border-border transition-all hover:border-purple-500/40">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 font-display font-black text-purple-600 dark:text-purple-400 border border-purple-500/20">
                  {{ mairie.name ? mairie.name.charAt(0).toUpperCase() : 'M' }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="truncate text-sm font-black text-foreground dark:text-white uppercase tracking-wide">{{ mairie.name }}</div>
                  <div class="truncate text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">{{ mairie.email }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- QUICK PROTOCOLS MODULE -->
          <div class="glass-strong rounded-[2rem] p-6">
            <h2 class="font-display text-lg mb-6 flex items-center gap-3 uppercase italic font-black">
              <Zap class="h-5 w-5 text-amber-500" /> Protocoles
            </h2>
            <div class="space-y-4">
              <Link 
                v-for="action in quickActions" 
                :key="action.label" 
                :href="action.to" 
                class="glass flex w-full items-center gap-4 p-4 rounded-2xl border-border transition-all hover:border-primary dark:hover:border-cyan-400 group"
              >
                <div class="glass flex h-10 w-10 items-center justify-center rounded-xl border-border bg-muted/50 group-hover:border-primary dark:group-hover:border-cyan-400 transition-colors">
                  <component :is="action.icon" class="h-5 w-5 text-primary dark:text-cyan-400" />
                </div>
                <span class="flex-1 text-left text-xs font-black uppercase tracking-widest text-foreground dark:text-white">{{ action.label }}</span>
                <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:text-primary dark:group-hover:text-cyan-400 transition-colors" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL LAYOUT: ADD MAIRIE -->
    <div v-if="showMairieModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 backdrop-blur-md bg-gaming-darker/60 dark:bg-gaming-darker/80">
      <div class="absolute inset-0" @click="showMairieModal = false" />
      
      <div class="glass-strong relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl p-8 border border-border shadow-xl animate-fade-up custom-scrollbar">
        <h2 class="font-display text-2xl uppercase italic font-black text-foreground dark:text-white mb-6">Nouvelle Ville & Mairie</h2>
        
        <form @submit.prevent="submitMairie" class="space-y-6">
          <div class="space-y-4">
            <GpsSearchInput 
              v-model="mairieForm.city_name" 
              label="Nom de la Ville (Recherche GPS)" 
              placeholder="Ex: Cotonou, Bénin"
              required 
              @select="onCitySelect"
            />
            
            <div class="grid gap-4 md:grid-cols-2">
              <GlowInput v-model="mairieForm.email" type="email" label="Email Mairie" placeholder="contact@mairie.bj" required />
              <GlowInput v-model="mairieForm.radius_meters" type="number" label="Rayon Tactique (mètres)" placeholder="5000" required />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <GlowInput v-model="mairieForm.latitude" type="number" step="any" label="Latitude (Manuel)" placeholder="Ex: 6.3654" required />
              <GlowInput v-model="mairieForm.longitude" type="number" step="any" label="Longitude (Manuel)" placeholder="Ex: 2.4183" required />
            </div>

            <div>
              <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-muted-foreground">Briefing de la Ville</label>
              <textarea 
                v-model="mairieForm.description" 
                class="h-24 w-full resize-none rounded-xl border border-border bg-input p-4 text-sm text-foreground placeholder:text-muted-foreground/40 outline-none focus:border-primary dark:focus:border-cyan-400" 
                placeholder="Description stratégique..."
              />
            </div>

            <div v-if="mairieForm.latitude" class="flex items-center justify-between border border-emerald-500/20 bg-emerald-500/5 p-3 rounded-xl animate-fade-up">
              <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                <MapPin class="h-3 w-3" /> Coordonnées Verrouillées
              </div>
              <div class="font-mono text-[10px] font-bold text-muted-foreground">
                {{ mairieForm.latitude }}, {{ mairieForm.longitude }}
              </div>
            </div>
          </div>

          <div class="pt-4 flex gap-3">
            <NeonButton type="button" variant="outline" class="flex-1" @click="showMairieModal = false">Annuler</NeonButton>
            <NeonButton type="submit" class="flex-1" :disabled="mairieForm.processing || !mairieForm.latitude">Déployer Mission</NeonButton>
          </div>
        </form>
      </div>
    </div>

    <MobileTabBar />
  </SiteLayout>
</template>