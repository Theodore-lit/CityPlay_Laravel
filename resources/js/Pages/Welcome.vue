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
});

const stats = [
  { v: '12K+', l: 'Explorateurs Actifs' },
  { v: '48', l: 'Missions' },
  { v: '5', l: 'Villes Légendaires' },
];

const features = [
  { icon: MapPin, title: 'Mode Aventure GPS', desc: 'Missions réelles basées sur le GPS. Marchez, explorez, découvrez. La ville est votre terrain de jeu.' },
  { icon: Zap, title: 'Système XP & Niveaux', desc: 'Gagnez de l\'expérience pour chaque quiz, énigme et découverte. Grimpez les échelons.' },
  { icon: QrCode, title: 'Chasse aux QR Codes', desc: 'Scannez des codes cachés sur les monuments, débloquez des secrets et des récompenses rares.' },
  { icon: Trophy, title: 'Classements Mondiaux', desc: 'Affrontez des explorateurs du monde entier chaque semaine. Gloire et trophées vous attendent.' },
  { icon: Shield, title: 'Patrimoine Authentique', desc: 'Chaque mission est co-conçue avec des historiens locaux pour découvrir le vrai Bénin.' },
  { icon: Sparkles, title: 'Visuels Cinématiques', desc: 'Animations immersives, bandes-son dynamiques et interface futuriste pour l\'aventure.' },
];

const cities = [
  { img: ouidah, name: 'Ouidah', tag: 'Mystic Coast', missions: 12 },
  { img: portoNovo, name: 'Porto-Novo', tag: 'Royal Capital', missions: 9 },
  { img: abomey, name: 'Abomey', tag: 'Ancient Kingdom', missions: 14 },
];

const testimonials = [
  { name: 'Aïcha K.', role: 'Exploratrice Niveau 32', text: 'Parcourir Ouidah en résolvant des énigmes était comme vivre un film. J\'ai appris plus en un week-end qu\'avec n\'importe quel guide.' },
  { name: 'Marc T.', role: 'Passionné de Patrimoine', text: 'Le Mode Aventure est génial. La chasse aux QR au palais royal m\'a littéralement donné des frissons.' },
  { name: 'Fatou D.', role: 'Voyageuse Solo', text: 'Design premium, contenu profond. Je suis venue pour le jeu, je suis restée pour le Bénin.' },
];
</script>

<template>
  <Head title="CityPlay — Découvrez le Bénin par l'Aventure" />

  <SiteLayout>
    <!-- HERO -->
    <section class="relative min-h-[92vh] overflow-hidden">
      <div class="absolute inset-0">
        <img :src="heroImg" alt="Cinematic Benin skyline" class="h-full w-full object-cover opacity-50" />
        <div class="absolute inset-0 bg-gradient-to-b from-gaming-darker/40 via-gaming-darker/70 to-gaming-darker" />
        <div class="absolute inset-0 grid-bg opacity-30" />
      </div>
      <div class="relative mx-auto max-w-7xl px-6 pt-20 pb-24 md:pt-32 md:pb-40">
        <div class="max-w-3xl animate-fade-up">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-xs font-medium text-electric">
            <Sparkles class="h-3.5 w-3.5" /> Saison 01 — L'Éveil
          </div>
          <h1 class="mt-6 font-display text-5xl md:text-7xl lg:text-8xl font-black leading-[0.95]">
            Explorez le Bénin
            <span class="block text-electric neon-text">Comme une Légende.</span>
          </h1>
          <p class="mt-6 max-w-xl text-base md:text-lg text-muted-foreground">
            Un jeu d'aventure touristique cinématique. Résolvez des énigmes dans les anciens palais royaux,
            chassez des reliques dans les marchés vibrants et devenez l'ultime explorateur.
          </p>
          <div class="mt-10 flex flex-wrap gap-4">
            <NeonButton :href="route('register')" size="lg">
              Commencer l'Aventure <ArrowRight class="h-4 w-4" />
            </NeonButton>
            <NeonButton :href="route('player.modes')" variant="outline" size="lg">
              <Gamepad2 class="h-4 w-4" /> Voir la Démo
            </NeonButton>
          </div>
          <div class="mt-14 grid grid-cols-3 gap-6 max-w-lg">
            <div v-for="s in stats" :key="s.l">
              <div class="font-display text-2xl md:text-3xl text-electric neon-text">{{ s.v }}</div>
              <div class="text-xs uppercase tracking-widest text-muted-foreground mt-1">{{ s.l }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="absolute bottom-6 left-1/2 -translate-x-1/2 animate-float">
        <div class="h-10 w-6 rounded-full border-2 border-electric/60 grid place-items-start p-1">
          <div class="h-2 w-1 rounded-full bg-electric" />
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="relative py-24 px-6">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-14">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-xs font-medium text-electric">
            <Sparkles class="h-3 w-3" /> Mécaniques de Jeu
          </div>
          <h2 class="mt-4 font-display text-3xl md:text-5xl">Un Nouveau Type de Tourisme</h2>
        </div>
        
        <div class="grid gap-6 md:grid-cols-3">
          <div
            v-for="(f, i) in features"
            :key="i"
            class="group relative rounded-2xl p-6 glass hover-lift animate-fade-up"
            :style="{ animationDelay: `${i * 80}ms` }"
          >
            <div class="h-12 w-12 rounded-xl bg-gradient-electric grid place-items-center shadow-neon">
              <component :is="f.icon" class="h-6 w-6 text-electric-foreground" />
            </div>
            <h3 class="mt-5 font-display text-xl">{{ f.title }}</h3>
            <p class="mt-2 text-sm text-muted-foreground">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CITY SHOWCASE -->
    <section class="relative py-24 px-6">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-14">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-xs font-medium text-electric">
            <Sparkles class="h-3 w-3" /> Mondes à Explorer
          </div>
          <h2 class="mt-4 font-display text-3xl md:text-5xl">Villes Légendaires du Bénin</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <Link
            v-for="c in cities"
            :key="c.name"
            :href="route('player.cities')"
            class="group relative overflow-hidden rounded-2xl glass hover-lift block aspect-[3/4]"
          >
            <img :src="c.img" :alt="c.name" loading="lazy"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/30 to-transparent" />
            <div class="absolute top-4 left-4 px-3 py-1 rounded-full glass text-xs text-electric font-bold uppercase tracking-widest">
              {{ c.tag }}
            </div>
            <div class="absolute bottom-6 left-6 right-6">
              <h3 class="font-display text-3xl">{{ c.name }}</h3>
              <div class="mt-2 flex items-center justify-between text-sm text-muted-foreground">
                <span>{{ c.missions }} missions</span>
                <span class="text-electric flex items-center gap-1">
                  Explorer <ArrowRight class="h-4 w-4" />
                </span>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="relative py-24 px-6">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-14">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-xs font-medium text-electric">
            <Sparkles class="h-3 w-3" /> Nos Explorateurs
          </div>
          <h2 class="mt-4 font-display text-3xl md:text-5xl">Paroles d'Aventure</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <div v-for="t in testimonials" :key="t.name" class="rounded-2xl p-6 glass hover-lift">
            <div class="flex gap-1 text-electric">
              <Star v-for="i in 5" :key="i" class="h-4 w-4 fill-current" />
            </div>
            <p class="mt-4 text-foreground/90">"{{ t.text }}"</p>
            <div class="mt-5 flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-gradient-electric grid place-items-center font-display font-black text-electric-foreground">
                {{ t.name.charAt(0) }}
              </div>
              <div>
                <div class="font-bold text-sm">{{ t.name }}</div>
                <div class="text-xs text-muted-foreground">{{ t.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="relative py-24 px-6">
      <div class="mx-auto max-w-5xl relative overflow-hidden rounded-3xl glass-strong p-10 md:p-16 text-center">
        <div class="absolute inset-0 grid-bg opacity-30" />
        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-electric/30 blur-3xl" />
        <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-purple-neon/30 blur-3xl" />
        <div class="relative">
          <Compass class="h-12 w-12 mx-auto text-electric animate-pulse-glow rounded-xl p-2" />
          <h2 class="mt-6 font-display text-3xl md:text-5xl">
            Votre Aventure <span class="text-electric neon-text">Commence Ici.</span>
          </h2>
          <p class="mt-4 text-muted-foreground max-w-2xl mx-auto">
            Créez votre compte explorateur gratuit. Débloquez votre première mission en 30 secondes.
          </p>
          <div class="mt-8 flex justify-center gap-4 flex-wrap">
            <NeonButton :href="route('register')" size="lg">Commencer <ArrowRight class="h-4 w-4" /></NeonButton>
            <NeonButton :href="route('player.cities')" variant="outline" size="lg">Parcourir les Villes</NeonButton>
          </div>
        </div>
      </div>
    </section>
  </SiteLayout>
</template>
