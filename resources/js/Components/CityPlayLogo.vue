<template>
  <svg
    :class="cn('block', props.class)"
    viewBox="0 0 80 80"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <defs>
      <linearGradient id="cityplay-compass-grad" x1="40" y1="8" x2="40" y2="72" gradientUnits="userSpaceOnUse">
        <stop stop-color="oklch(0.78 0.18 232)" />
        <stop offset="1" stop-color="oklch(0.62 0.22 255)" />
      </linearGradient>
      <filter id="cityplay-compass-glow" x="-20%" y="-20%" width="140%" height="140%">
        <feGaussianBlur stdDeviation="2" result="blur" />
        <feMerge>
          <feMergeNode in="blur" />
          <feMergeNode in="SourceGraphic" />
        </feMerge>
      </filter>
    </defs>

    <!-- Anneau fixe -->
    <circle cx="40" cy="40" r="34" stroke="oklch(0.70 0.18 250 / 0.35)" stroke-width="1.5" />
    <circle cx="40" cy="40" r="27" stroke="oklch(0.70 0.18 250 / 0.2)" stroke-width="1" />

  </svg>
</template>

<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
