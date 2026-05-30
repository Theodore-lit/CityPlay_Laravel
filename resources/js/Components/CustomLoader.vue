<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { isDisplayed } from '@/stores/loader';

const overlayRef = ref(null); // Référence de l'overlay global
const compassNeedleRef = ref(null); // Référence de l'aiguille pour l'animation

let rotationTween = null; // Instance de l'animation GSAP

/**
 * Configure l'animation de rotation infinie de l'aiguille.
 * Utilise svgOrigin pour garantir une rotation centrée.
 */
const setupCompassRotation = () => {
  if (!compassNeedleRef.value) return;

  gsap.set(compassNeedleRef.value, {
    svgOrigin: '40 40',
  });

  rotationTween = gsap.to(compassNeedleRef.value, {
    rotation: 360,
    repeat: -1,
    duration: 2,
    ease: 'none',
    paused: true,
  });
};

/**
 * Gère l'apparition/disparition progressive de l'overlay.
 */
const fade = (show) => {
  if (!overlayRef.value) return;

  gsap.to(overlayRef.value, {
    opacity: show ? 1 : 0,
    duration: show ? 0.35 : 0.28,
    ease: 'power2.out',
    pointerEvents: show ? 'auto' : 'none',
  });
};

/**
 * Déclenche ou met en pause l'animation de l'aiguille.
 */
const setAnimating = (active) => {
  if (active) {
    rotationTween?.play();
  } else {
    rotationTween?.pause();
  }
};

onMounted(() => {
  gsap.set(overlayRef.value, { opacity: 0, pointerEvents: 'none' });
  setupCompassRotation();

  if (isDisplayed.value) {
    fade(true);
    setAnimating(true);
  }
});

watch(isDisplayed, (show) => {
  fade(show);
  setAnimating(show);
});

onBeforeUnmount(() => {
  rotationTween?.kill();
  gsap.killTweensOf([overlayRef.value, compassNeedleRef.value]);
});
</script>

<template>
  <Teleport to="body">
    <div
      ref="overlayRef"
      class="loader-overlay fixed inset-0 z-[9999] flex items-center justify-center"
      role="status"
      aria-live="polite"
      :aria-busy="isDisplayed"
      aria-label="Chargement en cours"
    >
      <div class="flex flex-col items-center gap-4">
        <div
          class="relative flex h-28 w-28 items-center justify-center rounded-2xl border border-primary/20 shadow-[0_8px_32px_oklch(0.70_0.18_250/0.12)]"
        >
          <svg
            class="h-16 w-16"
            viewBox="0 0 80 80"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <defs>
              <linearGradient id="loader-compass-grad" x1="40" y1="10" x2="40" y2="70" gradientUnits="userSpaceOnUse">
                <stop stop-color="#67e8f9" />
                <stop offset="1" stop-color="#3b82f6" />
              </linearGradient>
            </defs>

            <!-- Cadre fixe : ne tourne pas -->
            <circle cx="40" cy="40" r="34" stroke="rgba(103,232,249,0.35)" stroke-width="1.5" />
            <circle cx="40" cy="40" r="27" stroke="rgba(103,232,249,0.18)" stroke-width="1" />
            <text x="40" y="17" text-anchor="middle" fill="#67e8f9" font-size="7" font-weight="700">N</text>

            <!-- Aiguille : rotation sur place uniquement -->
            <g ref="compassNeedleRef" class="compass-needle">
              <path
                d="M40 12 L44 40 L40 68 L36 40 Z"
                fill="url(#loader-compass-grad)"
                filter="drop-shadow(0 0 6px rgba(103,232,249,0.5))"
              />
              <circle cx="40" cy="40" r="4.5" fill="#0ea5e9" stroke="#e0f2fe" stroke-width="1" />
            </g>
          </svg>
        </div>
        <p class="font-display text-xs font-black uppercase tracking-[0.35em] animate-pulse text-foreground/80">
          Découvrez le Bénin avec City<span class="text-primary">Play</span>
        </p>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.loader-overlay {
  background: oklch(1 0 0 / 0.12);
  backdrop-filter: blur(13px) saturate(140%);
  -webkit-backdrop-filter: blur(13px) saturate(140%)
}

:global(.dark) .loader-overlay {
  background: oklch(0.12 0.02 240 / 0.28);
}

.compass-needle {
  /* On laisse GSAP gérer la rotation via svgOrigin pour plus de précision */
}
</style>
