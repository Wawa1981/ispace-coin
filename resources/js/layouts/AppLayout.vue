<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-hidden transition-colors duration-700">
    <!-- Fond étoiles -->
    <Starfield />

    <!-- Boutons clair/sombre -->
    <div class="fixed top-4 right-4 z-50 flex gap-3">
      <button @click="cycleDarkTheme" class="w-12 h-12 rounded-full bg-white text-black">🎨</button>
      <button @click="updateAppearance(isLight ? 'dark' : 'light')" class="w-12 h-12 rounded-full bg-white text-black">
        {{ isLight ? '🌞' : '🌙' }}
      </button>
      <button @click="updateAppearance('system')" class="w-12 h-12 rounded-full bg-white text-black">Sys</button>
    </div>

    <!-- Ici sera rendu le contenu de chaque page -->
    <main class="relative z-10">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center" :class="bodyTextClass">
      © {{ new Date().getFullYear() }} CryptoBank — Tous droits réservés
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance'
import Starfield from '@/components/Starfield.vue'

const { bgClass, textClass, bodyTextClass, isLight, updateAppearance, cycleDarkTheme } = useAppearance()
</script>
