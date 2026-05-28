<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import {
  ArrowRight, MapPin, Trophy, Sparkles, Compass, Zap, Star, Shield, Gamepad2, QrCode
} from 'lucide-vue-next';

// Images - Note: using Vite's URL for images
import heroImg from '../../images/hero-benin.jpg';
import ouidah from '../../images/city-ouidah.jpg';
import portoNovo from '../../images/city-porto-novo.jpg';
import abomey from '../../images/city-abomey.jpg';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    auth: Object,
});

const stats = [
  { v: '12K+', l: 'Explorateurs' },
  { v: '48', l: 'Missions' },
  { v: '5', l: 'Villes' },
];

const features = [
  { icon: MapPin, title: 'Mode Aventure GPS', desc: 'Missions réelles basées sur le GPS. Marchez, explorez et découvrez votre terrain de jeu.' },
  { icon: Zap, title: 'Système XP & Niveaux', desc: 'Gagnez de l\'expérience pour chaque quiz et énigme. Grimpez les échelons.' },
  { icon: QrCode, title: 'Chasse aux QR Codes', desc: 'Scannez des codes cachés sur les monuments pour débloquer des secrets rares.' },
  { icon: Trophy, title: 'Classements Mondiaux', desc: 'Affrontez des explorateurs du monde entier. Gloire et trophées vous attendent.' },
  { icon: Shield, title: 'Patrimoine Authentique', desc: 'Des missions co-conçues avec des historiens pour découvrir le vrai Bénin.' },
  { icon: Sparkles, title: 'Visuels Cinématiques', desc: 'Animations immersives et interface futuriste pour une aventure totale.' },
];

const cities = [
  { img: ouidah, name: 'Ouidah', tag: 'Mystic Coast', missions: 12 },
  { img: portoNovo, name: 'Porto-Novo', tag: 'Royal Capital', missions: 9 },
  { img: abomey, name: 'Abomey', tag: 'Ancient Kingdom', missions: 14 },
];

const testimonials = [
  { name: 'Aïcha K.', role: 'Niveau 32', text: 'Parcourir Ouidah en résolvant des énigmes était comme vivre un film.' },
  { name: 'Marc T.', role: 'Passionné', text: 'Le Mode Aventure est génial. La chasse aux QR codes est addictive.' },
  { name: 'Fatou D.', role: 'Voyageuse', text: 'Design premium, contenu profond. Une vraie découverte du pays.' },
];
</script>

<template>
  <Head title="CityPlay — Découvrez le Bénin par l'Aventure" />

  <SiteLayout>
    <section class="relative min-h-[85vh] overflow-hidden flex items-center">
      <div class="absolute inset-0">
        <!-- <img :src="heroImg" alt="Cinematic Benin skyline" class="h-full w-full object-cover opacity-40" /> -->
        <div class="absolute inset-0 bg-gradient-to-b from-gaming-darker/60 via-gaming-darker/80 to-gaming-darker" />
        <div class="absolute inset-0 grid-bg opacity-20" />
      </div>

      <div class="relative mx-auto max-w-7xl px-6 w-full">
        <div class="max-w-3xl animate-fade-up">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] uppercase tracking-wider font-bold text-electric/80">
            <Sparkles class="h-3 w-3" /> Saison 01 — L'Éveil
          </div>
          <h1 class="mt-4 font-display text-4xl md:text-6xl lg:text-7xl font-black leading-tight tracking-tight">
            Explorez le Bénin
            <span class="block text-electric/90">Comme une Légende.</span>
          </h1>
          <p class="mt-4 max-w-lg text-sm md:text-base text-muted-foreground/80 leading-relaxed">
            Un jeu d'aventure touristique cinématique. Résolvez des énigmes dans les palais royaux et devenez l'ultime explorateur.
          </p>

          <div class="mt-8 flex flex-wrap gap-4">
            <NeonButton :href="auth.user ? auth.user.role === 'super_admin' ? route('admin.dashboard') : auth.user.role === 'mairie' ? route('mairie.dashboard') : route('player.leaderboard') : route('login')" size="lg" class="shadow-lg shadow-electric/20">
              Explorer le Bénin <ArrowRight class="ml-2 h-4 w-4" />
            </NeonButton>
            <NeonButton :href="auth.user ? auth.user.role === 'super_admin' ? route('admin.dashboard') : auth.user.role === 'mairie' ? route('mairie.dashboard') : route('player.modes') : route('login')" variant="outline" size="lg" class="border-electric/30 text-electric/90">
              <Compass class="mr-2 h-4 w-4" /> Destinations
            </NeonButton>
          </div>

          <div class="mt-12 grid grid-cols-3 gap-4 max-w-md border-l border-electric/20 pl-6">
            <div v-for="s in stats" :key="s.l">
              <div class="font-display text-xl md:text-2xl text-electric/90 font-bold">{{ s.v }}</div>
              <div class="text-[10px] uppercase tracking-widest text-muted-foreground mt-1">{{ s.l }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="relative py-20 px-6 bg-gaming-darker/50">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-bold text-electric/70 uppercase">
            <Zap class="h-3 w-3" /> Mécaniques
          </div>
          <h2 class="mt-4 font-display text-2xl md:text-4xl font-bold">Un Nouveau Type de Tourisme</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <div
            v-for="(f, i) in features"
            :key="i"
            class="group relative rounded-2xl p-6 glass-dark border border-white/5 hover:border-electric/20 transition-all duration-300"
          >
            <div class="h-10 w-10 rounded-lg bg-electric/10 border border-electric/20 grid place-items-center mb-4">
              <component :is="f.icon" class="h-5 w-5 text-electric" />
            </div>
            <h3 class="font-display text-lg font-semibold">{{ f.title }}</h3>
            <p class="mt-2 text-xs leading-relaxed text-muted-foreground/70">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="relative py-20 px-6">
      <div class="mx-auto max-w-7xl">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div class="max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-bold text-electric/70 uppercase">
              <MapPin class="h-3 w-3" /> Destinations
            </div>
            <h2 class="mt-4 font-display text-2xl md:text-4xl font-bold">Villes Légendaires</h2>
          </div>
          <Link :href="route('player.cities')" class="text-xs text-electric/80 hover:text-electric transition-colors flex items-center gap-2 font-bold uppercase tracking-wider">
            Voir toutes les villes <ArrowRight class="h-3 w-3" />
          </Link>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <Link
            v-for="c in cities"
            :key="c.name"
            :href="route('player.cities')"
            class="group relative overflow-hidden rounded-xl glass hover:shadow-2xl hover:shadow-electric/10 transition-all duration-500 block aspect-[4/5]"
          >
            <img :src="c.img" :alt="c.name" loading="lazy"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/20 to-transparent" />
            <div class="absolute bottom-5 left-5 right-5">
              <span class="text-[10px] text-electric font-black uppercase tracking-[0.2em]">{{ c.tag }}</span>
              <h3 class="font-display text-2xl mt-1">{{ c.name }}</h3>
              <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 border-t border-white/10 pt-3">
                <span>{{ c.missions }} MISSIONS DISPONIBLES</span>
                <ArrowRight class="h-4 w-4 text-electric group-hover:translate-x-1 transition-transform" />
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <section class="relative py-20 px-6">
      <div class="mx-auto max-w-5xl relative overflow-hidden rounded-2xl bg-electric/5 border border-electric/10 p-10 md:p-16 text-center">
        <div class="absolute inset-0 grid-bg opacity-10" />
        <div class="relative">
          <Compass class="h-10 w-10 mx-auto text-electric/80 mb-6" />
          <h2 class="font-display text-2xl md:text-4xl font-bold">
            Votre Aventure <span class="text-electric">Commence Ici.</span>
          </h2>
          <p class="mt-4 text-sm text-muted-foreground/70 max-w-lg mx-auto">
            Créez votre compte explorateur gratuit et débloquez votre première mission en quelques secondes.
          </p>
          <div class="mt-10 flex justify-center gap-4 flex-wrap">
            <NeonButton :href="route('register')" size="lg">
              Commencer <ArrowRight class="ml-2 h-4 w-4" />
            </NeonButton>
            <NeonButton :href="route('player.cities')" variant="outline" size="lg" class="border-electric/20 text-white/80">
              Parcourir les Villes
            </NeonButton>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>

<style scoped>
.font-display {
  letter-spacing: -0.02em;
}
.glass-dark {
  background: rgba(15, 15, 25, 0.6);
  backdrop-filter: blur(12px);
}
</style>
