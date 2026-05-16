<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
  Users, Map, Target, TrendingUp, Activity, DollarSign, 
  Plus, Settings, Building2, Brain, ChevronRight 
} from 'lucide-vue-next';

defineProps({
    cities: Array,
    stats: Object,
});

const adminStats = [
  { icon: Users, label: "Joueurs", value: "12,438", delta: "+8.2%", color: "text-electric" },
  { icon: Activity, label: "Actifs Aujourd'hui", value: "2,184", delta: "+12%", color: "text-success" },
  { icon: Target, label: "Missions Jouées", value: "48.2K", delta: "+5.1%", color: "text-purple-neon" },
  { icon: DollarSign, label: "Revenus", value: "$8,420", delta: "+18%", color: "text-warning" },
];

const quickActions = [
  { icon: Building2, label: "Gérer les Villes", to: "/admin/cities" },
  { icon: Brain, label: "Console Maire", to: "/mayor" },
  { icon: Users, label: "Gérer les Utilisateurs", to: "/admin" },
  { icon: Target, label: "Créer une Mission", to: "/admin" },
];
</script>

<template>
  <Head title="Console Admin — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
        <div>
          <div class="text-xs text-purple-neon uppercase tracking-widest font-bold">Centre de Contrôle</div>
          <h1 class="font-display text-3xl md:text-4xl mt-1">Console Administration</h1>
        </div>
        <div class="flex gap-3">
          <NeonButton variant="outline" size="sm">
            <Settings class="h-4 w-4 mr-2" />Paramètres
          </NeonButton>
          <NeonButton size="sm">
            <Plus class="h-4 w-4 mr-2" />Nouvelle Ville
          </NeonButton>
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
            <select class="bg-gaming-darker border border-electric/30 rounded-lg px-3 h-9 text-sm text-foreground outline-none focus:border-electric">
              <option>7 derniers jours</option>
              <option>30 derniers jours</option>
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
          <h2 class="font-display text-lg mb-4">Actions Rapides</h2>
          <div class="space-y-3">
            <Link v-for="a in quickActions" :key="a.label" :href="a.to" class="w-full flex items-center gap-3 p-3 rounded-xl glass hover:border-electric hover:text-electric transition text-left">
              <component :is="a.icon" class="h-5 w-5 text-electric" />
              <span class="text-sm font-medium flex-1">{{ a.label }}</span>
              <Plus class="h-4 w-4" />
            </Link>
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
              <tr v-for="city in cities" :key="city.id" class="border-b border-electric/5 hover:bg-electric/5 transition-colors">
                <td class="p-4">
                  <div class="font-bold text-white">{{ city.name }}</div>
                  <div class="text-xs text-muted-foreground truncate max-w-xs">{{ city.description }}</div>
                </td>
                <td class="p-4 text-muted-foreground">{{ city.locations_count || 0 }} lieux</td>
                <td class="p-4 text-white">{{ Math.floor(Math.random() * 2000) }}</td>
                <td class="p-4">
                  <span :class="`px-2 py-1 rounded-md text-[10px] font-bold uppercase ${city.is_active ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning'}`">
                    {{ city.is_active ? 'Live' : 'Draft' }}
                  </span>
                </td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <Link 
                      :href="route('mairie.city.toggle', city.id)" 
                      method="patch" 
                      as="button"
                      :class="`text-xs font-bold px-3 py-1 rounded-lg transition-colors ${city.is_active ? 'text-warning hover:bg-warning/10' : 'text-success hover:bg-success/10'}`"
                    >
                      {{ city.is_active ? 'Désactiver' : 'Activer' }}
                    </Link>
                    <button class="text-electric hover:text-white transition-colors">
                      <ChevronRight class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
