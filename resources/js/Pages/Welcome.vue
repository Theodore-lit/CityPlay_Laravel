<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { 
  ArrowRight, MapPin, Trophy, Sparkles, Compass, Zap, Star, Shield, Gamepad2, QrCode, Sword, Map as MapIcon
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

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

const heroTitle = ref(null);
const heroContent = ref(null);
const heroImage = ref(null);

onMounted(() => {
  // Hero Animations
  const tl = gsap.timeline();
  tl.from(heroImage.value, {
    scale: 1.2,
    duration: 2,
    ease: "power2.out"
  })
  .from(heroTitle.value, {
    y: 100,
    opacity: 0,
    duration: 1,
    ease: "power4.out"
  }, "-=1.5")
  .from(heroContent.value, {
    y: 50,
    opacity: 0,
    duration: 0.8,
    ease: "power3.out"
  }, "-=1");

  // Section Animations
  gsap.utils.toArray('.animate-on-scroll').forEach(section => {
    gsap.from(section, {
      scrollTrigger: {
        trigger: section,
        start: "top 80%",
      },
      y: 50,
      opacity: 0,
      duration: 1,
      ease: "power3.out"
    });
  });
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
</script>

<template>
  <Head title="CityPlay — Découvrez le Bénin par l'Aventure" />

  <SiteLayout>
    <section class="relative min-h-[90vh] overflow-hidden flex items-center">
      <div class="absolute inset-0" ref="heroImage">
        <img :src="heroImg" alt="Cinematic Benin skyline" class="h-full w-full object-cover opacity-60" />
        <div class="absolute inset-0 bg-gradient-to-b from-gaming-darker/40 via-gaming-darker/70 to-gaming-darker" />
        <div class="absolute inset-0 grid-bg opacity-30" />
      </div>
      
      <div class="relative mx-auto max-w-7xl px-6 w-full py-20">
        <div class="max-w-4xl">
          <div ref="heroTitle">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-[10px] uppercase tracking-[0.3em] font-black text-primary animate-pulse-soft mb-6 border-primary/20">
              <Sparkles class="h-3.5 w-3.5" /> L'Éveil de l'Explorateur
            </div>
            <h1 class="font-display text-5xl md:text-7xl lg:text-8xl font-black leading-[0.9] tracking-tighter uppercase italic">
              Vivez l'histoire <br/>
              <span class="text-transparent bg-clip-text bg-gradient-adventure">en mode réel.</span>
            </h1>
          </div>
          
          <div ref="heroContent" class="mt-8">
            <p class="max-w-xl text-lg md:text-xl text-muted-foreground/90 leading-relaxed font-medium">
              Le premier MMORPG touristique au Bénin. Transformez votre ville en terrain de jeu, résolvez des énigmes ancestrales et gravez votre nom au panthéon des héros.
            </p>
            
            <div class="mt-10 flex flex-wrap gap-5">
              <NeonButton :href="auth.user ? route('player.modes') : route('register')" size="xl" class="shadow-2xl shadow-primary/40 bg-gradient-adventure border-none px-10">
                LANCER L'AVENTURE <Sword class="ml-3 h-5 w-5" />
              </NeonButton>
              <NeonButton :href="route('player.cities')" variant="outline" size="xl" class="border-white/10 text-white/90 glass-strong">
                <MapIcon class="mr-3 h-5 w-5" /> EXPLORER LA CARTE
              </NeonButton>
            </div>

            <div class="mt-16 flex items-center gap-12">
              <div v-for="s in stats" :key="s.l" class="group">
                <div class="font-display text-3xl md:text-4xl text-white font-black group-hover:text-primary transition-colors">{{ s.v }}</div>
                <div class="text-[10px] uppercase tracking-[0.2em] text-muted-foreground font-bold">{{ s.l }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-50">
        <div class="w-[1px] h-12 bg-gradient-to-b from-primary to-transparent" />
        <span class="text-[8px] uppercase tracking-widest font-bold">Scroll</span>
      </div>
    </section>

    <!-- Adventure Preview Section -->
    <section class="relative py-32 px-6 overflow-hidden animate-on-scroll">
      <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-[600px] h-[600px] bg-primary/10 rounded-full blur-[120px] pointer-events-none" />
      
      <div class="mx-auto max-w-7xl">
        <div class="grid lg:grid-cols-2 gap-20 items-center">
          <div class="relative group">
            <div class="absolute -inset-4 bg-gradient-adventure rounded-[3rem] opacity-20 blur-2xl group-hover:opacity-30 transition-opacity" />
            <div class="relative rounded-[2.5rem] overflow-hidden border border-white/10 aspect-video lg:aspect-square">
              <img :src="ouidah" alt="Adventure Gameplay" class="w-full h-full object-cover" />
              <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-transparent to-transparent" />
              <div class="absolute bottom-10 left-10 right-10">
                <div class="glass p-6 rounded-2xl border-primary/20">
                  <div class="flex items-center gap-4 mb-3">
                    <div class="h-10 w-10 rounded-xl bg-primary/20 flex items-center justify-center">
                      <Zap class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <div class="text-[10px] font-black uppercase tracking-widest text-primary">Mission Active</div>
                      <div class="font-display text-lg uppercase">Le Secret du Python</div>
                    </div>
                  </div>
                  <div class="w-full bg-white/10 h-1 rounded-full overflow-hidden">
                    <div class="bg-primary h-full w-[65%]" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-bold text-primary uppercase mb-6">
              <MapPin class="h-3 w-3" /> Gameplay Immersif
            </div>
            <h2 class="font-display text-4xl md:text-6xl font-black uppercase tracking-tight leading-none mb-8">
              Votre ville est <br/>
              <span class="text-primary">votre donjon.</span>
            </h2>
            <p class="text-lg text-muted-foreground/80 leading-relaxed mb-10">
              Oubliez les guides touristiques ennuyeux. CityPlay utilise la géolocalisation pour créer une couche de jeu au-dessus du monde réel. Chaque monument est une énigme, chaque rue est un défi.
            </p>
            
            <div class="grid sm:grid-cols-2 gap-6">
              <div v-for="f in features.slice(0, 4)" :key="f.title" class="flex gap-4">
                <div class="shrink-0 h-10 w-10 rounded-xl glass border-primary/10 flex items-center justify-center text-primary">
                  <component :is="f.icon" class="h-5 w-5" />
                </div>
                <div>
                  <h4 class="font-display text-sm uppercase tracking-wider mb-1">{{ f.title }}</h4>
                  <p class="text-xs text-muted-foreground/60 leading-relaxed">{{ f.desc }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Legendary Cities -->
    <section class="relative py-32 px-6 bg-gaming-darker/30 animate-on-scroll">
      <div class="mx-auto max-w-7xl">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
          <div class="max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-[10px] font-bold text-primary uppercase mb-6">
              <Compass class="h-3 w-3" /> Mondes à Explorer
            </div>
            <h2 class="font-display text-4xl md:text-6xl font-black uppercase tracking-tight">Régions <span class="text-primary">Actives</span></h2>
          </div>
          <Link :href="route('player.cities')" class="group text-xs text-primary hover:text-white transition-all flex items-center gap-3 font-black uppercase tracking-[0.2em] glass px-6 py-4 rounded-2xl border-primary/20">
            Carte Complète <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
          </Link>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
          <Link
            v-for="c in cities"
            :key="c.name"
            :href="route('player.cities')"
            class="group relative overflow-hidden rounded-[2.5rem] glass hover:shadow-2xl hover:shadow-primary/20 transition-all duration-700 block aspect-[3/4]"
          >
            <img :src="c.img" :alt="c.name" loading="lazy"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110 grayscale-[0.5] group-hover:grayscale-0" />
            <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-gaming-darker/20 to-transparent opacity-80" />
            
            <div class="absolute inset-0 p-8 flex flex-col justify-end">
              <span class="text-[10px] text-primary font-black uppercase tracking-[0.3em] mb-2">{{ c.tag }}</span>
              <h3 class="font-display text-3xl md:text-4xl text-white uppercase italic group-hover:translate-x-2 transition-transform">{{ c.name }}</h3>
              
              <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-6">
                <div class="flex items-center gap-2">
                  <div class="flex -space-x-2">
                    <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-gaming-dark bg-gaming-darker flex items-center justify-center text-[8px] font-bold">
                      {{ ['🛡️', '⚔️', '🔮'][i-1] }}
                    </div>
                  </div>
                  <span class="text-[9px] font-black text-white/50 uppercase tracking-widest">{{ c.missions }} Missions</span>
                </div>
                <div class="h-10 w-10 rounded-full glass border-primary/30 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-black transition-all">
                  <ArrowRight class="h-5 w-5" />
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="relative py-32 px-6 overflow-hidden animate-on-scroll">
      <div class="mx-auto max-w-5xl relative">
        <div class="absolute inset-0 bg-gradient-adventure opacity-10 rounded-[4rem] blur-[100px]" />
        <div class="relative overflow-hidden rounded-[3rem] glass-strong border border-primary/20 p-12 md:p-24 text-center">
          <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
          
          <div class="max-w-2xl mx-auto relative z-10">
            <div class="h-20 w-20 mx-auto rounded-3xl bg-gradient-adventure p-0.5 mb-10 animate-float-game shadow-2xl shadow-primary/20">
              <div class="w-full h-full rounded-[1.4rem] bg-gaming-darker flex items-center justify-center">
                <Trophy class="h-10 w-10 text-primary" />
              </div>
            </div>
            
            <h2 class="font-display text-4xl md:text-6xl font-black uppercase tracking-tight mb-8">
              Prêt à graver <br/>
              <span class="text-primary">votre légende ?</span>
            </h2>
            
            <p class="text-lg text-muted-foreground/70 mb-12">
              Rejoignez des milliers d'explorateurs et commencez votre quête dès aujourd'hui. Le Bénin n'attend que vous.
            </p>
            
            <div class="flex flex-wrap justify-center gap-6">
              <NeonButton :href="route('register')" size="xl" class="bg-gradient-adventure border-none px-12 shadow-2xl shadow-primary/30">
                CRÉER MON AVATAR <ArrowRight class="ml-3 h-5 w-5" />
              </NeonButton>
              <NeonButton :href="route('player.leaderboard')" variant="outline" size="xl" class="glass border-white/10 px-12">
                CLASSEMENT
              </NeonButton>
            </div>
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
.bg-gradient-adventure {
  background: var(--primary-gradient);
}
</style>

<style scoped>
.font-display {
  letter-spacing: -0.02em;
}
.glass-dark {
  background: rgba(15, 15, 25, 0.6);
  backdrop-filter: blur(12px);
}
</style>