<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { ref, onMounted } from 'vue';
import { Sun, Moon } from 'lucide-vue-next';

const props = defineProps({
  hideFooter: {
    type: Boolean,
    default: false,
  }
});

const isDark = ref(true);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('light', !isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    isDark.value = false;
    document.documentElement.classList.add('light');
  }
});
</script>

<template>
  <div class="min-h-screen flex flex-col transition-colors duration-500 bg-background text-foreground">
    <Navbar />
    
    <!-- Theme Toggle Floating Button -->
    <button 
      @click="toggleTheme" 
      class="fixed right-6 bottom-24 md:bottom-10 z-[60] h-12 w-12 rounded-full glass-strong border border-electric/30 flex items-center justify-center text-electric shadow-neon hover:scale-110 transition-all"
      aria-label="Changer le thème"
    >
      <Sun v-if="isDark" class="h-5 w-5" />
      <Moon v-else class="h-5 w-5" />
    </button>

    <main class="flex-1">
      <slot />
    </main>
    <Footer v-if="!hideFooter" />
  </div>
</template>
