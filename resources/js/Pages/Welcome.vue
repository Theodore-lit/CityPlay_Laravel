<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import HUDButton from '@/Components/HUDButton.vue';
import { 
  ArrowRight, MapPin, Trophy, Sparkles, Compass, Zap, Star, Shield, Gamepad2, QrCode, Play 
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

  <SiteLayout isHUD>
    <section class="relative min-h-screen overflow-hidden flex items-center">
      <div class="absolute inset-0">
        <img :src="heroImg" alt="Cinematic Benin skyline" class="h-full w-full object-cover opacity-30 scale-110" />
        <div class="absolute inset-0 bg-zinc-950/80" />
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-zinc-950/50 to-zinc-950" />
        <div class="absolute inset-0 grid-bg opacity-30" />
      </div>
      
      <!-- HUD Decorative Elements -->
      <div class="absolute top-10 left-10 hidden lg:block animate-fade-in">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl border border-primary/40 flex items-center justify-center bg-primary/5">
                <Compass class="h-6 w-6 text-primary animate-spin-slow" />
            </div>
            <div>
                <div class="text-[10px] font-black text-white uppercase tracking-[0.4em]">SYSTEM_VERSION_4.0</div>
                <div class="text-[8px] font-black text-primary uppercase tracking-[0.2em] mt-1">LAT: 6.3654° N // LONG: 2.4183° E</div>
            </div>
        </div>
      </div>

      <div class="absolute bottom-10 right-10 hidden lg:block text-right">
        <div class="text-[8px] font-black text-white/20 uppercase tracking-[0.5em] mb-4">REPUBLIQUE_DU_BENIN // EXPLORATION_UNIT</div>
        <div class="flex gap-2 justify-end">
            <div v-for="i in 5" :key="i" class="h-1 w-8 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-primary/40 w-1/2 animate-pulse" :style="{ animationDelay: `${i * 200}ms` }" />
            </div>
        </div>
      </div>
      
      <div class="relative mx-auto max-w-7xl px-8 w-full z-10">
        <div class="max-w-4xl animate-fade-up">
          <div class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full glass border-white/10 text-[10px] uppercase tracking-[0.3em] font-black text-primary">
            <div class="h-2 w-2 rounded-full bg-primary animate-ping" />
            Saison 01 // L'Éveil des Légendes
          </div>
          
          <h1 class="mt-8 font-display text-5xl md:text-8xl lg:text-9xl font-black leading-[0.85] tracking-tighter uppercase italic">
            EXPLOREZ LE <br>
            <span class="text-primary drop-shadow-[0_0_30px_rgba(6,182,212,0.5)]">BÉNIN</span>
          </h1>
          
          <p class="mt-8 max-w-xl text-sm md:text-lg text-white/60 font-medium leading-relaxed uppercase tracking-wider">
            Un jeu d'aventure touristique cinématique haute fidélité. <br class="hidden md:block">
            Transformez votre réalité en terrain de jeu légendaire.
          </p>
          
          <div class="mt-12 flex flex-wrap gap-6">
            <HUDButton :href="auth.user ? route('player.modes') : route('register')" class="h-16 px-10 scale-110 origin-left">
                <div class="flex items-center gap-3">
                    <Play class="h-5 w-5 fill-current" />
                    <span class="text-sm">COMMENCER_L_AVENTURE</span>
                </div>
            </HUDButton>
            
            <HUDButton :href="route('player.cities')" variant="magenta" class="h-16 px-10 border-white/10">
                <div class="flex items-center gap-3">
                    <MapPin class="h-5 w-5" />
                    <span class="text-sm">VOIR_LES_DESTINATIONS</span>
                </div>
            </HUDButton>
          </div>

          <div class="mt-20 grid grid-cols-3 gap-8 max-w-xl">
            <div v-for="s in stats" :key="s.l" class="relative group">
              <div class="absolute -left-4 top-0 w-px h-full bg-gradient-to-b from-primary/40 to-transparent" />
              <div class="font-display text-3xl md:text-4xl text-white font-black italic tracking-tighter group-hover:text-primary transition-colors">{{ s.v }}</div>
              <div class="text-[9px] uppercase tracking-[0.4em] text-white/40 font-black mt-2">{{ s.l }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES WITH HUD CARDS -->
    <section class="relative py-32 px-8 bg-zinc-950">
      <div class="mx-auto max-w-7xl">
        <div class="flex flex-col items-center text-center mb-24">
            <div class="h-12 w-px bg-gradient-to-b from-primary/60 to-transparent mb-8" />
            <h2 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
                MÉCANIQUES DE <span class="text-primary">JEU</span>
            </h2>
            <div class="text-[10px] font-black tracking-[0.5em] text-white/20 uppercase mt-4">GAMEPLAY_CORE_SYSTEMS // ACTIVE</div>
        </div>
        
        <div class="grid gap-8 md:grid-cols-3">
          <div
            v-for="(f, i) in features"
            :key="i"
            class="group relative neon-border-box p-10 rounded-[2.5rem] transition-all duration-500 hover:scale-[1.02] overflow-hidden"
          >
            <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
            <div class="h-14 w-14 rounded-2xl bg-primary/10 border border-primary/20 grid place-items-center mb-8 group-hover:bg-primary/20 transition-all">
              <component :is="f.icon" class="h-7 w-7 text-primary" />
            </div>
            <h3 class="font-display text-xl font-black text-white uppercase italic tracking-tight">{{ f.title }}</h3>
            <p class="mt-4 text-[11px] leading-relaxed text-white/40 font-bold uppercase tracking-widest">{{ f.desc }}</p>
            
            <!-- Tech decoration -->
            <div class="absolute bottom-4 right-6 text-[8px] font-black text-white/10 tracking-[0.3em]">0{{ i + 1 }}</div>
          </div>
        </div>
      </div>
    </section>

    <section class="relative py-32 px-8">
      <div class="mx-auto max-w-7xl">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8">
          <div>
            <h2 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white">
                VILLES <span class="text-primary">LÉGENDAIRES</span>
            </h2>
            <div class="text-[10px] font-black tracking-[0.5em] text-white/20 uppercase mt-4">SECTOR_EXPLORATION_MAP // 05_REGIONS</div>
          </div>
          <Link :href="route('player.cities')" class="hud-nav-item px-6 py-2 text-[10px] font-black">
            TOUTES_LES_DESTINATIONS <ArrowRight class="h-3 w-3 inline ml-2" />
          </Link>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
          <Link
            v-for="c in cities"
            :key="c.name"
            :href="route('player.cities')"
            class="group relative overflow-hidden rounded-[2.5rem] border-2 border-white/5 hover:border-primary/40 transition-all duration-700 block aspect-[4/5]"
          >
            <img :src="c.img" :alt="c.name" loading="lazy"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110 grayscale-[0.3] group-hover:grayscale-0" />
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/20 to-transparent opacity-90" />
            <div class="absolute inset-0 grid-bg opacity-20 group-hover:opacity-40 transition-opacity" />
            
            <div class="absolute bottom-8 left-8 right-8">
              <span class="text-[10px] text-primary font-black uppercase tracking-[0.4em]">{{ c.tag }}</span>
              <h3 class="font-display text-4xl mt-2 text-white font-black italic uppercase tracking-tighter">{{ c.name }}</h3>
              <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-6">
                <span class="text-[9px] text-white/40 font-black tracking-widest uppercase">{{ c.missions }} MISSIONS_ACTIVES</span>
                <div class="h-10 w-10 rounded-full border border-primary/20 flex items-center justify-center group-hover:bg-primary/10 transition-all">
                    <ArrowRight class="h-4 w-4 text-primary group-hover:translate-x-1 transition-transform" />
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <section class="relative py-32 px-8 mb-20">
      <div class="mx-auto max-w-6xl relative overflow-hidden rounded-[3rem] neon-border-box p-16 md:p-24 text-center">
        <div class="absolute inset-0 grid-bg opacity-20" />
        <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
        
        <div class="relative z-10">
          <div class="h-16 w-16 mx-auto rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-10">
            <Compass class="h-8 w-8 text-primary animate-pulse" />
          </div>
          <h2 class="font-display text-4xl md:text-7xl font-black uppercase italic tracking-tighter text-white leading-none">
            VOTRE AVENTURE <br>
            <span class="text-primary drop-shadow-[0_0_20px_#06b6d4]">COMMENCE ICI</span>
          </h2>
          <p class="mt-8 text-[11px] font-bold text-white/40 uppercase tracking-[0.3em] max-w-2xl mx-auto leading-relaxed">
            CRÉEZ VOTRE IDENTIFIANT EXPLORATEUR ET DÉBLOQUEZ VOTRE PREMIÈRE MISSION EN MOINS DE 60 SECONDES.
          </p>
          
          <div class="mt-16 flex justify-center gap-8 flex-wrap">
            <HUDButton :href="route('register')" class="h-16 px-12 scale-110">
              S_INSCRIRE_MAINTENANT
            </HUDButton>
            <HUDButton :href="route('player.cities')" variant="magenta" class="h-16 px-12 border-white/10">
              EXPLORER_LES_VILLES
            </HUDButton>
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