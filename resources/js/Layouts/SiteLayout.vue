<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { ref, onMounted } from 'vue';
import { Sun, Moon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  hideFooter: {
    type: Boolean,
    default: false,
  },
  isHUD: {
    type: Boolean,
    default: false,
  },
  isMapPage: {
    type: Boolean,
    default: false,
  }
});

const isDark = ref(true);

const updateDOM = (dark) => {
  if (dark) {
    document.documentElement.classList.add('dark');
    document.documentElement.classList.remove('light');
  } else {
    document.documentElement.classList.add('light');
    document.documentElement.classList.remove('dark');
  }
};

const toggleTheme = () => {
  isDark.value = !isDark.value;
  updateDOM(isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    isDark.value = false;
  }
  updateDOM(isDark.value);
});
</script>

<template>
  <div :class="cn(
    'min-h-screen flex flex-col transition-colors duration-500 bg-background text-foreground',
    isHUD && 'hud-container'
  )">
    <Navbar v-if="!isHUD" />

    <!-- Theme Toggle Floating Button -->
    <button
      v-if="!isHUD"
      @click="toggleTheme"
      class="fixed right-6 bottom-24 md:bottom-10 z-[60] h-12 w-12 rounded-full bg-card border border-border flex items-center justify-center text-primary shadow-lg hover:scale-110 transition-all"
      aria-label="Changer le thème"
    >
      <Sun v-if="isDark" class="h-5 w-5" />
      <Moon v-else class="h-5 w-5" />
    </button>

    <main :class="cn('flex-1', isHUD && 'relative z-10')">
      <!-- HUD Vignetting Layer -->
      <div v-if="isHUD && !isMapPage" class="hud-vignette pointer-events-none" />
      
      <slot />
    </main>
    <Footer v-if="!hideFooter && !isHUD" />
  </div>
</template>
