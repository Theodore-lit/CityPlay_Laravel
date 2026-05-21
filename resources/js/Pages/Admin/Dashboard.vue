<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import GpsSearchInput from '@/Components/GpsSearchInput.vue';
import Pagination from '@/Components/Pagination.vue';
import AppImage from '@/Components/AppImage.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  Users, Map, Target, TrendingUp, Activity, DollarSign,
  Plus, Settings, Building2, Brain, ChevronRight, LayoutDashboard, ShieldCheck, Zap,
  Share2, Ban, CheckCircle, MapPin
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    cities: Object,
    mairies: Array,
    players: Object,
    stats: Object,
});

const adminStats = computed(() => [
  { icon: Users, label: "Utilisateurs", value: props.stats.total_users || "0", delta: "+5.2%", color: "text-electric" },
  { icon: ShieldCheck, label: "Mairies", value: props.mairies?.length || "0", delta: "Actifs", color: "text-success" },
  { icon: Target, label: "Sessions", value: props.stats.total_sessions || "0", delta: "+2.1%", color: "text-purple-neon" },
  { icon: Activity, label: "Joueurs Actifs", value: props.stats.active_players || "0", delta: "Live", color: "text-warning" },
]);

const showMairieModal = ref(false);
const showCityModal = ref(false);
const selectedCity = ref(null);

const mairieForm = useForm({
    city_name: '',
    email: '',
    description: '',
    latitude: '',
    longitude: '',
    radius_meters: 5000,
});

const cityForm = useForm({
    id: null,
    name: '',
    description: '',
    latitude: '',
    longitude: '',
    radius_meters: 5000,
    image: null,
});

const openCityModal = (city = null) => {
    selectedCity.value = city;
    if (city) {
        cityForm.id = city.id;
        cityForm.name = city.name;
        cityForm.description = city.description;
        cityForm.latitude = city.latitude;
        cityForm.longitude = city.longitude;
        cityForm.radius_meters = city.radius_meters;
    } else {
        cityForm.reset();
        cityForm.id = null;
    }
    showCityModal.value = true;
};

const submitCity = () => {
    const url = cityForm.id
        ? route('mairie.cities.update', cityForm.id)
        : route('admin.mairie-city.store');

    cityForm.post(url, {
        forceFormData: true,
        onSuccess: () => {
            showMairieModal.value = false;
            showCityModal.value = false;
            cityForm.reset();
        }
    });
};

const onCitySelect = (data) => {
    mairieForm.city_name = data.name;
    mairieForm.latitude = data.lat;
    mairieForm.longitude = data.lon;
};

const submitMairie = () => {
    mairieForm.post(route('admin.mairie-city.store'), {
        forceFormData: true,
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

// const quickActions = [
//   { icon: Building2, label: "Toutes les Villes", to: route('admin.cities') },
//   { icon: ShieldCheck, label: "Gestion Mairies", to: route('admin.dashboard') },
//   { icon: Users, label: "Gestion Utilisateurs", to: route('admin.dashboard') },
//   { icon: TrendingUp, label: "Analyse Sessions", to: route('admin.dashboard') },
// ];
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
      <div class="mb-10 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="s in adminStats" :key="s.label" class="rounded-3xl bg-white/5 backdrop-blur-xl p-6 border border-white/5 hover:border-electric/30 transition-all group relative overflow-hidden shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-electric/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
              <div :class="`h-12 w-12 rounded-2xl bg-gaming-darker border border-white/10 grid place-items-center group-hover:scale-110 group-hover:border-electric/50 transition-all ${s.color}`">
                  <component :is="s.icon" class="h-6 w-6" />
              </div>
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-electric bg-electric/10 border border-electric/20 px-2 py-1 rounded-lg shadow-neon-sm">{{ s.delta }}</span>
            </div>
            <div class="font-display text-3xl text-white font-black tracking-tight">{{ s.value }}</div>
            <div class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground mt-1 font-bold group-hover:text-electric/70 transition-colors">{{ s.label }}</div>
          </div>
        </div>
      </div>

      <!-- CITIES TABLE -->
            <div class="bg-white/5 rounded-[2.5rem] backdrop-blur-xl border border-white/5 overflow-hidden shadow-3xl shadow-black/50 group/table">
                <div class="p-8 border-b border-white/5 flex items-center justify-between bg-gradient-to-r from-electric/10 via-white/5 to-transparent relative overflow-hidden">
                    <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:20px_20px]"></div>
                    <h2 class="font-display text-2xl flex items-center gap-4 text-white relative z-10">
                        <div class="h-12 w-12 rounded-2xl bg-electric/10 border border-electric/20 grid place-items-center shadow-neon-sm">
                            <Map class="h-7 w-7 text-electric" />
                        </div>
                        Déploiement des Villes
                    </h2>
                    <div class="px-4 py-1.5 rounded-full bg-electric/5 border border-electric/10">
                        <span class="text-[10px] uppercase font-black tracking-[0.2em] text-electric">Page {{ cities.current_page }} <span class="mx-1 opacity-30">/</span> {{ cities.last_page }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground bg-black/40">
                            <tr>
                                <th class="text-left p-6">Secteur / Mission</th>
                                <th class="text-left p-6">Maire</th>
                                <th class="text-left p-6">Objectifs</th>
                                <th class="text-left p-6">Statut Tactique</th>
                                <th class="text-right p-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="city in cities.data" :key="city.id" class="hover:bg-electric/5 transition-all group/city">
                                <td class="p-6">
                                    <Link :href="route('mairie.cities.show', city.id)" class="flex items-center gap-4 group/link">
                                        <div class="h-12 w-16 rounded-xl bg-gaming-darker border border-white/10 overflow-hidden shrink-0">
                                            <AppImage
                                                :src="city.image_url || city.image_path"
                                                class="w-full h-full object-cover group-hover/link:scale-110 transition-transform"
                                                alt=""
                                            >
                                                <template #placeholder>
                                                    <div class="w-full h-full flex items-center justify-center text-white/20">
                                                        <ImageIcon class="h-6 w-6" />
                                                    </div>
                                                </template>
                                            </AppImage>
                                        </div>
                                        <div>
                                            <div class="font-display text-lg text-white group-hover/city:text-electric transition-colors">{{ city.name }}</div>
                                            <div class="text-xs text-muted-foreground truncate max-w-xs mt-1 font-medium opacity-60">{{ city.description || 'Pas de briefing.' }}</div>
                                        </div>
                                    </Link>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-lg bg-purple-neon/10 border border-purple-neon/20 grid place-items-center text-[10px] text-purple-neon font-black">
                                            {{ city.creator?.name.charAt(0) }}
                                        </div>
                                        <span class="text-xs font-bold text-white/80">{{ city.creator?.name || 'Admin' }}</span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] font-mono text-muted-foreground group-hover/city:text-electric transition-colors">
                                        {{ city.locations_count || 0 }} SECTEURS
                                    </span>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-2.5">
                                        <div :class="cn(
                                            'h-2 w-2 rounded-full animate-pulse',
                                            city.is_active ? 'bg-success shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-warning shadow-[0_0_12px_rgba(234,179,8,0.5)]'
                                        )"></div>
                                        <span :class="cn(
                                            'text-[10px] uppercase font-black tracking-widest',
                                            city.is_active ? 'text-success' : 'text-warning'
                                        )">{{ city.is_active ? 'Opérationnel' : 'En Attente' }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button @click="openCityModal(city)" class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-electric hover:bg-electric hover:text-white hover:shadow-neon-sm transition-all" title="Modifier la ville">
                                            <Edit2 class="h-5 w-5" />
                                        </button>
                                        <Link :href="route('admin.cities.quizzes', city.id)" class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-purple-neon hover:bg-purple-neon hover:text-white hover:shadow-neon-purple transition-all" title="Gérer les Quiz">
                                            <Brain class="h-5 w-5" />
                                        </Link>
                                        <button @click="copyShareLink(city)" class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 grid place-items-center text-cyan-neon hover:bg-cyan-neon hover:text-white hover:shadow-neon-cyan transition-all" title="Partager le lien">
                                            <Share2 class="h-5 w-5" />
                                        </button>
                                        <Link :href="route('mairie.city.hub', city.id)" class="h-10 w-10 rounded-xl bg-electric/10 border border-electric/20 grid place-items-center text-electric hover:bg-electric hover:text-white hover:shadow-neon-sm transition-all">
                                            <ChevronRight class="h-5 w-5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-black/20 border-t border-white/5">
                    <Pagination :links="cities.links" />
                </div>
            </div>
      <div class="mt-8 grid gap-8 lg:grid-cols-12">
        <!-- MAIN COLUMN -->
        <div class="lg:col-span-9 space-y-8">

            <!-- USERS TABLE -->
            <div class="rounded-[2.5rem] bg-white/6 backdrop-blur-xl border border-white/5 overflow-hidden shadow-3xl shadow-black/50 group/table">
                <div class="p-8 border-b border-white/5 flex items-center justify-between bg-gradient-to-r from-purple-neon/10 via-white/5 to-transparent relative overflow-hidden">
                    <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:20px_20px]"></div>
                    <h2 class="font-display text-2xl flex items-center gap-4 text-white relative z-10">
                        <div class="h-12 w-12 rounded-2xl bg-purple-neon/10 border border-purple-neon/20 grid place-items-center shadow-neon-purple">
                            <Users class="h-7 w-7 text-purple-neon" />
                        </div>
                        Contrôle des Joueurs
                    </h2>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] uppercase font-black tracking-[0.2em] text-muted-foreground bg-white/5 px-4 py-1.5 rounded-full border border-white/10">
                            Unité {{ players.current_page }} <span class="mx-1 opacity-30">/</span> {{ players.last_page }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground bg-black/40">
                            <tr>
                                <th class="text-left p-6">Avatar / Identité</th>
                                <th class="text-left p-6">Capacités (LVL/XP)</th>
                                <th class="text-left p-6">Statut Tactique</th>
                                <th class="text-right p-6">Protocoles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="player in players.data" :key="player.id" class="hover:bg-electric/5 transition-all group/player">
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-electric/20 to-electric/5 border border-electric/30 grid place-items-center text-electric font-display text-xl font-black shadow-inner">
                                            {{ player.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-base">{{ player.name }}</div>
                                            <div class="text-xs text-muted-foreground font-medium opacity-50">{{ player.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-3">
                                        <div class="px-3 py-1.5 rounded-lg bg-electric/5 border border-electric/20">
                                            <span class="text-[10px] font-black text-electric uppercase tracking-tighter">LVL {{ player.level }}</span>
                                        </div>
                                        <div class="text-xs font-mono text-muted-foreground font-bold">{{ player.xp.toLocaleString() }} XP</div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-2.5">
                                        <div :class="cn(
                                            'h-2 w-2 rounded-full',
                                            player.is_active ? 'bg-green-400 shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-reed-400 shadow-[0_0_12px_rgba(239,68,68,0.5)]'
                                        )"></div>
                                        <span class="text-[10px] uppercase font-black tracking-widest text-white/90">{{ player.is_active ? 'Opérationnel' : 'Neutralisé' }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-right">
                                    <button @click="toggleUser(player.id)" :class="cn(
                                        'h-10 px-6 rounded-xl font-black uppercase tracking-[0.1em] text-[10px] transition-all border flex items-center gap-2 ml-auto',
                                        player.is_active 
                                            ? 'bg-red-600 border-red-200 text-white hover:bg-white hover:text-red-600 hover:shadow-neon-red' 
                                            : 'bg-green-600 border-green-200 text-white hover:bg-white hover:text-green-600 hover:shadow-neon-green'
                                    )">
                                        <Ban v-if="player.is_active" class="h-3.5 w-3.5" />
                                        <CheckCircle v-else class="h-3.5 w-3.5" />
                                        {{ player.is_active ? 'Bannir' : 'Rétablir' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-black/20 border-t border-white/5">
                    <Pagination :links="players.links" />
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="lg:col-span-3 space-y-8">
            <!-- PROTOCOLE SECTION (Refactored) -->
            <div class="rounded-[2.5rem] bg-gradient-to-b from-purple-neon/20 to-gaming-dark/40 border border-purple-neon/30 p-8 shadow-2xl shadow-purple-neon/10">
                <div class="h-14 w-14 rounded-2xl bg-purple-neon/20 border border-purple-neon/40 grid place-items-center mb-6 shadow-neon-purple">
                    <ShieldCheck class="h-8 w-8 text-purple-neon" />
                </div>
                <h2 class="font-display text-2xl text-white mb-2 font-black italic tracking-tight">Espace admin optimisé</h2>
                <p class="text-xs text-purple-neon/60 uppercase tracking-[0.2em] font-bold mb-8">Capacités du système</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-4 group">
                        <div class="mt-1 h-1.5 w-1.5 rounded-full bg-purple-neon group-hover:scale-150 transition-transform shadow-neon-purple"></div>
                        <div class="flex-1">
                            <div class="text-sm font-black text-white uppercase tracking-wider mb-1">Gestion des villes</div>
                            <div class="text-[10px] text-muted-foreground font-medium leading-relaxed opacity-60">Déploiement et supervision tactique de tous les secteurs.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 group">
                        <div class="mt-1 h-1.5 w-1.5 rounded-full bg-purple-neon group-hover:scale-150 transition-transform shadow-neon-purple"></div>
                        <div class="flex-1">
                            <div class="text-sm font-black text-white uppercase tracking-wider mb-1">Gestion utilisateurs</div>
                            <div class="text-[10px] text-muted-foreground font-medium leading-relaxed opacity-60">Contrôle total des accès et analyse des performances joueurs.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 group">
                        <div class="mt-1 h-1.5 w-1.5 rounded-full bg-purple-neon group-hover:scale-150 transition-transform shadow-neon-purple"></div>
                        <div class="flex-1">
                            <div class="text-sm font-black text-white uppercase tracking-wider mb-1">Supervision Quiz</div>
                            <div class="text-[10px] text-muted-foreground font-medium leading-relaxed opacity-60">Validation et équilibrage des défis intellectuels.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 p-4 rounded-2xl bg-white/5 border border-white/10 text-center italic">
                    <span class="text-[10px] text-muted-foreground font-medium">Système CityPlay v2.4 — Statut : Optimal</span>
                </div>
            </div>

            <!-- QUICK ACTIONS (Protocoles)
            <div class="rounded-[2.5rem] bg-gaming-dark/40 border border-white/5 p-8 shadow-2xl">
                <h2 class="font-display text-xl mb-8 flex items-center gap-3 text-white">
                    <Zap class="h-5 w-5 text-warning shadow-neon-yellow" />Protocoles Actifs
                </h2>
                <div class="grid grid-cols-1 gap-4">
                    <Link v-for="a in quickActions" :key="a.label" :href="a.to" class="flex items-center gap-4 p-5 rounded-2xl bg-white/5 border border-white/5 hover:border-electric/50 hover:bg-electric/5 transition-all group">
                        <div class="h-11 w-11 rounded-xl bg-gaming-darker border border-white/10 grid place-items-center group-hover:border-electric/50 transition-all shadow-inner">
                            <component :is="a.icon" class="h-5 w-5 text-electric group-hover:shadow-neon-sm" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/80 flex-1 group-hover:text-white transition-colors">{{ a.label }}</span>
                        <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:text-electric transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>
            </div> -->
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

                    <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold mb-1 block">Blason de la Ville</label>
                    <input type="file" @change="mairieForm.image = $event.target.files[0]" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-electric/10 file:text-electric hover:file:bg-electric/20 transition-all" />
                </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <NeonButton type="button" variant="outline" class="flex-1" @click="showMairieModal = false">Annuler</NeonButton>
                    <NeonButton type="submit" class="flex-1" :disabled="mairieForm.processing || !mairieForm.latitude">Déployer Mission</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT CITY MODAL -->
    <div v-if="showCityModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gaming-darker/80 backdrop-blur-md">
        <div class="glass-strong rounded-3xl p-8 w-full max-w-md border border-electric/20 animate-fade-up">
            <h2 class="font-display text-2xl text-white mb-6">Modifier la Ville</h2>
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
                    <input type="file" @change="cityForm.image = $event.target.files[0]" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-electric/10 file:text-electric hover:file:bg-electric/20 transition-all" />
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
                    <NeonButton type="submit" class="flex-1" :disabled="cityForm.processing || !cityForm.latitude">Enregistrer</NeonButton>
                </div>
            </form>
        </div>
    </div>

    <!-- <MobileTabBar /> -->
  </SiteLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 243, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 243, 255, 0.3);
}
</style>
