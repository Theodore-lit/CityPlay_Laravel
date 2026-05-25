<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
  MapPin, Calendar, Brain, ChevronLeft, Target, 
  Settings, Activity, ArrowRight, Plus, Info
} from 'lucide-vue-next';

import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    city: Object,
    auth: Object,
});

const menus = computed(() => [
  { 
    title: 'Lieux & Énigmes', 
    description: 'Gérez les secteurs et les défis de votre ville.', 
    icon: MapPin, 
    to: props.city?.id ? route('mairie.cities.show', props.city.id) : '#',
    count: props.city?.locations_count,
    color: 'text-electric',
    bg: 'bg-electric/10'
  },
  { 
    title: 'Événements Phares', 
    description: 'Annoncez les grands moments à venir.', 
    icon: Calendar, 
    to: props.city?.id ? route('mairie.cities.events', props.city.id) : '#',
    count: props.city?.events_count,
    color: 'text-accent',
    bg: 'bg-accent/10'
  },
  { 
    title: 'Quiz & Savoir', 
    description: 'Testez les connaissances des explorateurs.', 
    icon: Brain, 
    to: props.city?.id ? route('admin.cities.quizzes', props.city.id) : '#',
    count: props.city?.quizzes_count,
    color: 'text-warning',
    bg: 'bg-warning/10'
  },
  { 
    title: 'Configuration', 
    description: 'Paramètres généraux de la ville.', 
    icon: Settings, 
    to: props.city?.id ? route('mairie.cities.show', props.city.id) : '#', 
    color: 'text-muted-foreground',
    bg: 'bg-white/5'
  }
]);
</script>

<template>
  <Head :title="`Hub ${city.name} — CityPlay`" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <!-- Header -->
      <div class="flex items-center gap-6 mb-12">
        <Link v-if="auth.user.role == 'super_admin'" :href="route('admin.dashboard')" class="h-12 w-12 rounded-2xl glass grid place-items-center text-electric hover:scale-110 transition-all border border-electric/20">
          <ChevronLeft class="h-6 w-6" />
        </Link>
        <div>
          <div class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-1">Hub de Gestion</div>
          <h1 class="font-display text-3xl md:text-5xl text-white uppercase tracking-tight">{{ city.name }}</h1>
        </div>
      </div>

      <!-- City Info Card -->
      <div class="glass-strong rounded-[2.5rem] border border-white/10 p-8 mb-12 relative overflow-hidden">
        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-electric/5 blur-3xl"></div>
        <div class="relative z-10 grid md:grid-cols-3 gap-8 items-center">
          <div class="md:col-span-2">
            <h2 class="font-display text-2xl text-white mb-4 italic">"Briefing de Mission"</h2>
            <p class="text-muted-foreground leading-relaxed italic max-w-2xl">
              {{ city.description || 'Aucune description disponible pour cette destination.' }}
            </p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5 text-center">
              <div class="text-[8px] text-muted-foreground uppercase font-black mb-1">Statut</div>
              <div :class="['text-sm font-bold uppercase tracking-widest', city.is_active ? 'text-success' : 'text-warning']">
                {{ city.is_active ? 'Opérationnel' : 'En Pause' }}
              </div>
            </div>
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5 text-center">
              <div class="text-[8px] text-muted-foreground uppercase font-black mb-1">Rayon</div>
              <div class="text-sm font-bold text-white uppercase tracking-widest">{{ city.radius_meters }}m</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Menus Grid -->
      <div class="grid gap-6 md:grid-cols-2">
        <Link 
          v-for="menu in menus" 
          :key="menu.title" 
          :href="menu.to"
          class="group relative glass-strong rounded-3xl border border-white/10 p-8 hover:border-electric/50 transition-all duration-500 overflow-hidden"
        >
          <div :class="['absolute -right-4 -bottom-4 h-32 w-32 rounded-full opacity-0 group-hover:opacity-10 transition-opacity', menu.bg]"></div>
          
          <div class="relative z-10 flex items-start justify-between gap-6">
            <div class="flex-1">
              <div :class="['h-14 w-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 group-hover:rotate-3', menu.bg, menu.color]">
                <component :is="menu.icon" class="h-7 w-7" />
              </div>
              <h3 class="font-display text-2xl text-white mb-2 uppercase tracking-wide group-hover:text-electric transition-colors">{{ menu.title }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">{{ menu.description }}</p>
            </div>
            
            <div class="flex flex-col items-end gap-4">
              <div v-if="menu.count !== undefined" class="glass px-3 py-1 rounded-lg text-[10px] font-black text-white uppercase tracking-widest">
                {{ menu.count }} Éléments
              </div>
              <div class="h-10 w-10 rounded-full glass border-white/10 grid place-items-center text-white group-hover:bg-electric group-hover:text-black transition-all">
                <ArrowRight class="h-5 w-5" />
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </SiteLayout>
</template>

<style scoped>
.glass-strong {
  background: rgba(15, 15, 25, 0.8);
  backdrop-filter: blur(20px);
}
</style>
