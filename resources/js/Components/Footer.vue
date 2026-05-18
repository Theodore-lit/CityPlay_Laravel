<script setup>
import { Link } from '@inertiajs/vue3';
import { Compass, Github, Twitter, Instagram } from 'lucide-vue-next';

const icons = [Twitter, Instagram, Github];
</script>

<template>
  <footer class="relative mt-24 border-t border-electric/20 glass-strong">
    <div class="mx-auto max-w-7xl px-6 py-12 grid gap-10 md:grid-cols-4">
      <div class="md:col-span-2">
        <div class="flex items-center gap-2">
          <div class="h-9 w-9 rounded-lg bg-white/5 border border-electric/20 grid place-items-center shadow-neon">
            <Compass class="h-5 w-5 text-electric neon-text" />
          </div>
          <span class="font-display font-black tracking-widest text-white">
            CITY<span class="text-electric neon-text">PLAY</span>
          </span>
        </div>
        <p class="mt-4 text-sm text-muted-foreground max-w-md">
          Un jeu d'exploration et de chasse au trésor futuriste. Découvrez l'âme du Bénin
          à travers des missions immersives, des énigmes et des aventures réelles.
        </p>
        <div class="mt-5 flex gap-3">
          <template v-for="(Icon, i) in icons" :key="i">
            <a
              href="#"
              class="h-9 w-9 grid place-items-center rounded-lg glass hover:border-electric hover:text-electric transition"
            >
              <component :is="Icon" class="h-4 w-4" />
            </a>
          </template>
        </div>
      </div>
      <div>
        <h4 class="font-display text-electric text-sm mb-3 uppercase">Explorer</h4>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li><Link href="/cities" class="hover:text-electric">Villes</Link></li>
          <li><Link href="/modes" class="hover:text-electric">Modes de Jeu</Link></li>
          <li><Link href="/leaderboard" class="hover:text-electric">Classement</Link></li>
          <li><Link href="/rewards" class="hover:text-electric">Récompenses</Link></li>
        </ul>
      </div>
      <div>
        <h4 class="font-display text-electric text-sm mb-3 uppercase">Compte</h4>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li><Link :href="route('login')" class="hover:text-electric">Connexion</Link></li>
          <li><Link :href="route('register')" class="hover:text-electric">Créer un compte</Link></li>
          <li><Link href="/dashboard" class="hover:text-electric">Tableau de bord</Link></li>
          <li><Link href="/admin" class="hover:text-electric">Admin</Link></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-electric/10 py-5 text-center text-xs text-muted-foreground">
      © {{ new Date().getFullYear() }} CityPlay — L'aventure vous attend.
    </div>
  </footer>
</template>
