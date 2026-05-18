<script setup>
import { Link } from '@inertiajs/vue3';
import { Compass, Github, Twitter, Instagram } from 'lucide-vue-next';

const icons = [Twitter, Instagram, Github];
</script>

<template>
  <footer class="relative mt-24 border-t border-border bg-card">
    <div class="mx-auto max-w-7xl px-6 py-12 grid gap-10 md:grid-cols-4">
      <div class="md:col-span-2">
        <div class="flex items-center gap-2">
          <div class="h-9 w-9 rounded-lg bg-primary/10 border border-primary/20 grid place-items-center">
            <Compass class="h-5 w-5 text-primary neon-text" />
          </div>
          <span class="font-display font-black tracking-widest text-foreground">
            CITY<span class="text-primary neon-text">PLAY</span>
          </span>
        </div>
        <p class="mt-4 text-sm text-muted-foreground max-w-md">
          Un jeu d'exploration et de chasse au trésor. Découvrez l'âme du Bénin
          à travers des missions immersives, des énigmes et des aventures réelles.
        </p>
        <div class="mt-5 flex gap-3">
          <template v-for="(Icon, i) in icons" :key="i">
            <a
              href="#"
              class="h-9 w-9 grid place-items-center rounded-lg border border-border bg-muted hover:border-primary hover:text-primary transition-colors"
            >
              <component :is="Icon" class="h-4 w-4" />
            </a>
          </template>
        </div>
      </div>
      <div>
        <h4 class="font-display text-primary text-sm mb-3 uppercase font-bold tracking-wider">Explorer</h4>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li><Link href="/cities" class="hover:text-primary transition-colors">Villes</Link></li>
          <li><Link href="/modes" class="hover:text-primary transition-colors">Modes de Jeu</Link></li>
          <li><Link href="/leaderboard" class="hover:text-primary transition-colors">Classement</Link></li>
          <li><Link href="/rewards" class="hover:text-primary transition-colors">Récompenses</Link></li>
        </ul>
      </div>
      <div>
        <h4 class="font-display text-primary text-sm mb-3 uppercase font-bold tracking-wider">Compte</h4>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li><Link :href="route('login')" class="hover:text-primary transition-colors">Connexion</Link></li>
          <li><Link :href="route('register')" class="hover:text-primary transition-colors">Créer un compte</Link></li>
          <li><Link href="/dashboard" class="hover:text-primary transition-colors">Tableau de bord</Link></li>
          <li><Link href="/admin" class="hover:text-primary transition-colors">Admin</Link></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-electric/10 py-5 text-center text-xs text-muted-foreground">
      © {{ new Date().getFullYear() }} CityPlay — L'aventure vous attend.
    </div>
  </footer>
</template>
