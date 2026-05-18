<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import GpsSearchInput from '@/Components/GpsSearchInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  Users, Map, Target, TrendingUp, Activity, DollarSign, 
  Plus, Settings, Building2, Brain, ChevronRight, LayoutDashboard, ShieldCheck, Zap,
  Share2, Ban, CheckCircle
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    cities: Array,
    mairies: Array,
    players: Array,
    stats: Object,
});

const adminStats = [
  { icon: Users, label: "Utilisateurs", value: props.stats.total_users || "0", delta: "+5.2%", color: "text-electric" },
  { icon: ShieldCheck, label: "Mairies", value: props.mairies?.length || "0", delta: "Actifs", color: "text-success" },
  { icon: Target, label: "Sessions", value: props.stats.total_sessions || "0", delta: "+2.1%", color: "text-purple-neon" },
  { icon: Activity, label: "Joueurs Actifs", value: props.stats.active_players || "0", delta: "Live", color: "text-warning" },
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
            alert('Lien de jeu copié (votre navigateur ne supporte pas le partage direct).');
        }
    } catch (err) {
        console.error('Erreur lors du partage :', err);
    }
};

const quickActions = [
  { icon: Building2, label: "Toutes les Villes", to: route('admin.cities') },
  { icon: ShieldCheck, label: "Gestion Mairies", to: route('admin.dashboard') },
  { icon: Users, label: "Gestion Utilisateurs", to: route('admin.dashboard') },
  { icon: TrendingUp, label: "Analyse Sessions", to: route('admin.dashboard') },
];
</script>

<template>
  <Head title="Super Admin — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
        <div>
          <div class="text-xs text-electric uppercase tracking-widest font-bold">Quartier Général</div>
          <h1 class="font-display text-3xl md:text-4xl mt-1">Tableau de Bord <span class="text-electric neon-text">Admin</span></h1>
        </div>
        <div class="flex gap-3">
          <NeonButton @click="showMairieModal = true" size="sm">
            <Plus class="h-4 w-4 mr-2" />Ajouter une Mairie
          </NeonButton>
        </div>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="s in adminStats" :key="s.label" class="rounded-2xl glass p-5 hover-lift">
          <div class="flex justify-between items-start">
            <component :is="s.icon" :class="`h-6 w-6 ${s.color}`" />
            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">{{ s.delta }}</span>
          </div>
          <div class="mt-3 font-display text-2xl md:text-3xl text-white">{{ s.value }}</div>
          <div class="text-[10px] uppercase tracking-widest text-muted-foreground mt-1">{{ s.label }}</div>
        </div>
      </div>

      <div class="mt-8 grid gap-8 lg:grid-cols-12">
        <!-- CITIES TABLE -->
        <div class="lg:col-span-8 rounded-[2rem] glass-strong border border-white/5 overflow-hidden">
          <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/5">
            <h2 class="font-display text-xl flex items-center gap-3">
              <Map class="h-6 w-6 text-electric" />Déploiement Global des Villes
            </h2>
            <Link :href="route('admin.cities')" class="text-xs text-electric hover:underline font-bold">Tout voir</Link>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground bg-gaming-darker/50">
                <tr class="border-b border-white/5">
                  <th class="text-left p-6">Secteur / Mission</th>
                  <th class="text-left p-6">Objectifs</th>
                  <th class="text-left p-6">Statut Tactique</th>
                  <th class="text-right p-6">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="city in cities" :key="city.id" class="border-b border-white/5 hover:bg-electric/5 transition-all group/city">
                  <td class="p-6">
                    <Link :href="route('mairie.cities.show', city.id)" class="block">
                        <div class="font-display text-base text-white group-hover/city:text-electric transition-colors">{{ city.name }}</div>
                        <div class="text-xs text-muted-foreground truncate max-w-xs mt-1 italic">{{ city.description || 'Pas de briefing.' }}</div>
                    </Link>
                  </td>
                  <td class="p-6 text-muted-foreground font-mono text-xs">{{ city.locations_count || 0 }} secteurs</td>
                  <td class="p-6">
                    <div class="flex items-center gap-2">
                        <div :class="`h-2 w-2 rounded-full ${city.is_active ? 'bg-success shadow-[0_0_10px_oklch(0.74_0.18_155)]' : 'bg-warning'}`"></div>
                        <span class="text-[10px] uppercase font-black tracking-widest text-white">{{ city.is_active ? 'Actif' : 'En Attente' }}</span>
                    </div>
                  </td>
                  <td class="p-6 text-right">
                    <div class="flex justify-end gap-2">
                      <Link :href="route('admin.cities.quizzes', city.id)" class="h-10 w-10 rounded-xl glass border-white/10 grid place-items-center text-purple-neon hover:bg-purple-neon hover:text-white transition-all" title="Gérer les Quiz">
                        <Brain class="h-5 w-5" />
                      </Link>
                      <button @click="copyShareLink(city)" class="h-10 w-10 rounded-xl glass border-white/10 grid place-items-center text-cyan-neon hover:bg-cyan-neon hover:text-white transition-all" title="Partager le lien">
                        <Share2 class="h-5 w-5" />
                      </button>
                      <Link :href="route('mairie.cities.show', city.id)" class="h-10 w-10 rounded-xl glass border-white/10 grid place-items-center text-electric hover:bg-electric hover:text-white transition-all">
                        <ChevronRight class="h-5 w-5" />
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- USERS TABLE -->
        <div class="lg:col-span-8 rounded-[2rem] glass-strong border border-white/5 overflow-hidden mt-8">
          <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/5">
            <h2 class="font-display text-xl flex items-center gap-3">
              <Users class="h-6 w-6 text-electric" />Contrôle des Joueurs
            </h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground bg-gaming-darker/50">
                <tr class="border-b border-white/5">
                  <th class="text-left p-6">Joueur</th>
                  <th class="text-left p-6">Niveau / XP</th>
                  <th class="text-left p-6">Statut</th>
                  <th class="text-right p-6">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="player in players" :key="player.id" class="border-b border-white/5 hover:bg-electric/5 transition-all group/player">
                  <td class="p-6">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full bg-electric/20 grid place-items-center text-electric font-bold border border-electric/30">
                        {{ player.name.charAt(0) }}
                      </div>
                      <div>
                        <div class="font-bold text-white">{{ player.name }}</div>
                        <div class="text-[10px] text-muted-foreground">{{ player.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="p-6">
                    <div class="text-white font-mono text-xs">LVL {{ player.level }} • {{ player.xp }} XP</div>
                  </td>
                  <td class="p-6">
                    <div class="flex items-center gap-2">
                        <div :class="`h-2 w-2 rounded-full ${player.is_active ? 'bg-success shadow-[0_0_10px_oklch(0.74_0.18_155)]' : 'bg-destructive shadow-[0_0_10px_oklch(0.62_0.24_25)]'}`"></div>
                        <span class="text-[10px] uppercase font-black tracking-widest text-white">{{ player.is_active ? 'Opérationnel' : 'Désactivé' }}</span>
                    </div>
                  </td>
                  <td class="p-6 text-right">
                    <button @click="toggleUser(player.id)" :class="`h-10 px-4 rounded-xl glass border-white/10 inline-flex items-center gap-2 text-xs font-bold transition-all ${player.is_active ? 'text-destructive hover:bg-destructive hover:text-white' : 'text-success hover:bg-success hover:text-white'}`">
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

        <!-- SIDEBAR -->
        <div class="lg:col-span-4 space-y-8">
            <!-- MAIRIES -->
            <div class="glass-strong rounded-[2rem] border border-white/5 overflow-hidden">
                <div class="p-6 border-b border-white/5 flex items-center justify-between">
                    <h2 class="font-display text-lg flex items-center gap-3"><ShieldCheck class="h-5 w-5 text-purple-neon" />Commandants</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="mairie in mairies" :key="mairie.id" class="flex items-center gap-4 p-4 rounded-2xl glass border-white/5 hover:border-purple-neon/40 transition-all">
                        <div class="h-10 w-10 rounded-xl bg-purple-neon/20 grid place-items-center text-purple-neon font-display font-black">
                            {{ mairie.name.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-white truncate">{{ mairie.name }}</div>
                            <div class="text-[10px] text-muted-foreground uppercase tracking-widest">{{ mairie.email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="glass-strong rounded-[2rem] border border-white/5 p-6">
                <h2 class="font-display text-lg mb-6 flex items-center gap-3"><Zap class="h-5 w-5 text-warning" />Protocoles</h2>
                <div class="space-y-4">
                    <button v-for="a in quickActions" :key="a.label" class="w-full flex items-center gap-4 p-4 rounded-2xl glass hover:border-electric transition-all group">
                        <div class="h-10 w-10 rounded-xl bg-gaming-darker border border-white/5 grid place-items-center group-hover:border-electric transition-colors">
                            <component :is="a.icon" class="h-5 w-5 text-electric" />
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest text-white flex-1 text-left">{{ a.label }}</span>
                        <Plus class="h-4 w-4 text-muted-foreground group-hover:text-electric transition-colors" />
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- ADD MAIRIE MODAL -->
    <div v-if="showMairieModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/80 backdrop-blur-md">
        <div class="glass-strong rounded-3xl p-8 w-full max-w-lg border border-electric/20 animate-fade-up overflow-y-auto max-h-[90vh]">
            <h2 class="font-display text-2xl text-white mb-6">Nouvelle Ville & Mairie</h2>
            <form @submit.prevent="submitMairie" class="space-y-6">
                <div class="space-y-4">
                    <GpsSearchInput 
                        v-model="mairieForm.city_name" 
                        label="Nom de la Ville (Recherche GPS)" 
                        placeholder="Ex: Cotonou, Bénin"
                        @select="onCitySelect"
                        required 
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
                        <label class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block">Briefing de la Ville</label>
                        <textarea v-model="mairieForm.description" class="w-full h-24 rounded-xl bg-gaming-darker border border-white/10 p-4 text-sm text-white placeholder:text-muted-foreground/40 focus:border-electric outline-none resize-none" placeholder="Description stratégique..."></textarea>
                    </div>

                    <div v-if="mairieForm.latitude" class="p-3 rounded-xl bg-electric/5 border border-electric/20 flex items-center justify-between animate-fade-up">
                        <div class="text-[10px] text-electric font-bold uppercase tracking-widest flex items-center gap-2">
                            <MapPin class="h-3 w-3" /> Coordonnées Verrouillées
                        </div>
                        <div class="text-[10px] font-mono text-muted-foreground">
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
