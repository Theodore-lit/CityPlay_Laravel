<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Brain, Compass, ArrowRight, Zap, MapPin, Clock, Trophy } from 'lucide-vue-next';

const props = defineProps({
    quizzes: Array
});
</script>

<template>
  <Head title="Modes de Jeu — CityPlay" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 pb-28 md:pb-12">
      <div v-if="$page.props.flash.success" class="mb-8 p-4 rounded-2xl bg-success/20 border border-success/40 text-success text-center font-bold animate-fade-up">
        {{ $page.props.flash.success }}
      </div>

      <div class="text-center max-w-2xl mx-auto">
        <div class="text-xs text-electric uppercase tracking-widest font-bold">Choisissez votre voie</div>
        <h1 class="font-display text-3xl md:text-5xl mt-2">Comment allez-vous <span class="text-electric neon-text">explorer ?</span></h1>
        <p class="mt-3 text-muted-foreground">Deux modes uniques. Une aventure inoubliable.</p>
      </div>

      <div class="mt-12 grid gap-8 md:grid-cols-2">
        <!-- MODE QUIZ -->
        <div class="group relative overflow-hidden rounded-3xl glass-strong p-8 md:p-10 border border-electric/10">
          <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-electric/20 blur-3xl" />
          <div class="absolute inset-0 grid-bg opacity-10" />
          
          <div class="relative">
            <div class="h-16 w-16 rounded-2xl bg-gradient-electric grid place-items-center shadow-neon">
              <Brain class="h-8 w-8 text-electric-foreground" />
            </div>
            <h2 class="mt-6 font-display text-3xl text-white">Mode Quiz</h2>
            <p class="mt-3 text-muted-foreground text-sm leading-relaxed">
              Testez vos connaissances sur le Bénin. Histoire, culture, géographie : devenez un expert du patrimoine.
            </p>

            <div class="mt-8 space-y-4">
              <div v-for="quiz in quizzes" :key="quiz.id" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-electric/40 transition-all group/item">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-[10px] uppercase tracking-widest text-electric font-bold">{{ quiz.category }}</span>
                  <span class="flex items-center gap-1 text-xs text-muted-foreground"><Clock class="h-3 w-3" /> {{ quiz.time_limit }}s</span>
                </div>
                <h3 class="font-bold text-white mb-3">{{ quiz.title }}</h3>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-muted-foreground">{{ quiz.questions_count }} questions • <span class="text-electric">+{{ quiz.xp_reward }} XP</span></span>
                  <Link :href="route('player.quiz', quiz.id)">
                    <NeonButton size="sm" variant="outline">Jouer</NeonButton>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- MODE AVENTURE -->
        <div class="group relative overflow-hidden rounded-3xl glass-strong p-8 md:p-10 border border-purple-neon/10">
          <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-purple-neon/20 blur-3xl" />
          <div class="absolute inset-0 grid-bg opacity-10" />
          
          <div class="relative flex flex-col h-full">
            <div class="flex justify-between items-start">
                <div class="h-16 w-16 rounded-2xl bg-purple-neon grid place-items-center shadow-purple">
                    <Compass class="h-8 w-8 text-white" />
                </div>
                <div class="px-3 py-1 rounded-full bg-purple-neon/20 text-purple-neon text-[10px] font-display font-black tracking-widest border border-purple-neon/40 animate-pulse">
                    EN DIRECT
                </div>
            </div>
            
            <h2 class="mt-6 font-display text-3xl text-white">Mode Aventure</h2>
            <p class="mt-3 text-muted-foreground text-sm leading-relaxed">
              Vivez l'histoire sur le terrain. Missions GPS réelles, scan de QR codes historiques et énigmes immersives.
            </p>

            <div class="mt-8 grid grid-cols-2 gap-3">
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center">
                    <MapPin class="h-5 w-5 mx-auto text-electric mb-2" />
                    <div class="text-xl font-display text-white">GPS</div>
                    <div class="text-[10px] text-muted-foreground uppercase tracking-widest">Actif</div>
                </div>
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center">
                    <Zap class="h-5 w-5 mx-auto text-electric mb-2" />
                    <div class="text-xl font-display text-white">500+</div>
                    <div class="text-[10px] text-muted-foreground uppercase tracking-widest">XP/Lieu</div>
                </div>
            </div>

            <div class="mt-auto pt-10">
                <Link :href="route('player.cities')" class="block">
                    <NeonButton variant="purple" class="w-full">
                        Ouvrir la Carte <ArrowRight class="h-4 w-4 ml-2" />
                    </NeonButton>
                </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
    <MobileTabBar />
  </SiteLayout>
</template>
