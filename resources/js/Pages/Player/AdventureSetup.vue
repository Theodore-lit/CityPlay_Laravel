<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { User, Users, MapPin, ArrowRight, Info, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
    teams: Array,
});

const selectedMode = ref(null); // 'solo' or 'team'
const selectedTeamId = ref(null);

const startAdventure = () => {
    if (selectedMode.value === 'solo') {
        // Obtenir la position GPS avant de démarrer si possible
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                router.post(route('player.adventure.solo', props.city.id), {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            }, () => {
                // En cas d'erreur ou de refus, démarrer sans position
                router.post(route('player.adventure.solo', props.city.id));
            });
        } else {
            router.post(route('player.adventure.solo', props.city.id));
        }
    } else if (selectedMode.value === 'team' && selectedTeamId.value) {
        router.post(route('teams.start-quest', { team: selectedTeamId.value, city: props.city.id }));
    }
};
</script>

<template>
  <Head title="Configuration Aventure — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-4xl px-4 sm:px-6 py-8 pb-28 md:pb-12">
      <div class="text-center mb-10">
        <div class="text-[10px] text-electric uppercase tracking-[0.3em] font-black mb-2 animate-pulse">Configuration de Mission</div>
        <h1 class="font-display text-3xl md:text-5xl neon-text uppercase tracking-tight">VOTRE <span class="text-electric">STRATÉGIE</span></h1>
        <p class="mt-4 text-muted-foreground text-sm flex items-center justify-center gap-2">
            <MapPin class="h-4 w-4 text-electric" /> Destination : {{ city.name }}
        </p>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- MODE SOLO -->
        <div
          @click="selectedMode = 'solo'"
          :class="[
            'group relative overflow-hidden rounded-[2.5rem] p-8 cursor-pointer border-2 transition-all duration-500',
            selectedMode === 'solo'
              ? 'bg-electric/10 border-electric shadow-neon scale-[1.02]'
              : 'glass border-white/10 hover:border-electric/40'
          ]"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-electric/10 blur-[60px]" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

          <div class="relative z-10">
            <div :class="[
                'h-16 w-16 rounded-2xl flex items-center justify-center mb-6 transition-transform duration-500',
                selectedMode === 'solo' ? 'bg-electric text-white scale-110 shadow-neon' : 'bg-white/10 text-electric'
            ]">
                <User class="h-8 w-8" />
            </div>

            <h2 class="font-display text-2xl mb-2">MODE SOLO</h2>
            <p class="text-sm text-muted-foreground leading-relaxed">
              Explorez la ville à votre propre rythme. Idéal pour les loups solitaires et les explorateurs contemplatifs.
            </p>

            <ul class="mt-6 space-y-3">
                <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                    <ShieldCheck class="h-3 w-3 text-electric" /> Progression individuelle
                </li>
                <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                    <ShieldCheck class="h-3 w-3 text-electric" /> Calcul d'itinéraire GPS
                </li>
            </ul>
          </div>
        </div>

        <!-- MODE ÉQUIPE -->
        <div
          @click="selectedMode = 'team'"
          :class="[
            'group relative overflow-hidden rounded-[2.5rem] p-8 cursor-pointer border-2 transition-all duration-500',
            selectedMode === 'team'
              ? 'bg-secondary/10 border-secondary shadow-purple scale-[1.02]'
              : 'glass border-white/10 hover:border-secondary/40'
          ]"
        >
          <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-secondary/10 blur-[60px]" />
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

          <div class="relative z-10">
            <div :class="[
                'h-16 w-16 rounded-2xl flex items-center justify-center mb-6 transition-transform duration-500',
                selectedMode === 'team' ? 'bg-secondary text-white scale-110 shadow-purple' : 'bg-white/10 text-secondary'
            ]">
                <Users class="h-8 w-8" />
            </div>

            <h2 class="font-display text-2xl mb-2">MODE ÉQUIPE</h2>
            <p class="text-sm text-muted-foreground leading-relaxed">
              Collaborez avec vos amis pour résoudre les mystères plus rapidement. La force est dans l'union.
            </p>

            <div v-if="selectedMode === 'team'" class="mt-6 animate-fade-up">
                <label class="block text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Choisir une équipe :</label>
                <select
                    v-model="selectedTeamId"
                    class="w-full bg-gaming-darker border border-secondary/30 rounded-xl px-4 py-2 text-sm outline-none focus:border-secondary"
                    @click.stop
                >
                    <option :value="null" disabled>Sélectionner une équipe</option>
                    <option v-for="team in teams" :key="team.id" :value="team.id">
                        {{ team.name }} ({{ team.members_count }} membres)
                    </option>
                </select>

                <div v-if="teams.length === 0" class="mt-2 p-3 rounded-xl bg-warning/10 border border-warning/20 flex items-start gap-3">
                    <Info class="h-4 w-4 text-warning shrink-0 mt-0.5" />
                    <p class="text-[10px] text-warning-foreground leading-tight uppercase font-bold">
                        Vous n'avez pas encore d'équipe.
                        <Link :href="route('teams.index')" class="underline">Créez-en une ici</Link>
                    </p>
                </div>
            </div>

            <ul v-else class="mt-6 space-y-3">
                <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                    <ShieldCheck class="h-3 w-3 text-secondary" /> Score partagé
                </li>
                <li class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                    <ShieldCheck class="h-3 w-3 text-secondary" /> Validation collaborative
                </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- ACTION BUTTON -->
      <div class="mt-12 flex justify-center">
        <NeonButton
            @click="startAdventure"
            :disabled="!selectedMode || (selectedMode === 'team' && !selectedTeamId)"
            size="xl"
            class="rounded-2xl px-12 group"
        >
            DÉMARRER L'AVENTURE
            <ArrowRight class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" />
        </NeonButton>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
