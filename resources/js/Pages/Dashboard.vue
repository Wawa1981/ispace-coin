<template>
  <!-- Conteneur principal -->
  <div
    :class="[bgClass, textClass]"
    class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex flex-col"
  >
    <!-- Fond animé -->
    <Starfield />

    <!-- Sidebar -->
    <div class="page-body flex flex-1 min-h-0 w-full">
    <Sidebar
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      :items="items"
      :tickerHeightPx="tickerHeightPx"
      @toggle="toggleSidebar"
      @close="closeSidebar"
    >
      <!-- Slot des icônes -->
      <template #icon="{ icon, isLight }">
        <SidebarIcon :icon="icon" :isLight="isLight" />
      </template>
    </Sidebar>

    <!-- Contenu principal -->
    <main
      class="relative z-10 w-full pb-16 transition-all duration-300 px-4 md:px-8"
      :style="{ paddingTop: contentPadTopPx }"
    >
      <!-- Bandeau crypto -->
      <Ticker />

      <!-- Boutons thème -->
      <div class="fixed top-4 right-4 z-50 flex gap-3">

        <!-- Changer style thème sombre -->
        <button
          @click="cycleDarkTheme"
          class="theme-orb w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
          title="Changer le style du thème sombre"
        >
          🌍
        </button>

        <!-- Jour / nuit -->
        <button
          @click="toggleLight"
          class="theme-orb w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
        >
          {{ isLight ? '🌖' : '🌕' }}
        </button>
      </div>

      <!-- Zone centrale -->
      <div
        class="flex flex-col items-center justify-center w-full min-h-[calc(100vh-14rem)]"
      >

        <!-- Titre -->
        <h1
          :class="[
            'text-3xl font-extrabold text-center mb-8',
            titleClass
          ]"
        >
          CryptoActus
        </h1>

        <p class="text-center max-w-xl mb-8 opacity-80">
          Marchés et actus crypto. CryptoActus ne garde pas de fonds et ne propose plus de dépôt, de retrait ni d’échange.
        </p>

        <Link
          :href="route('crypto')"
          class="px-6 py-3 rounded-full font-semibold bg-indigo-600 text-white"
        >
          Voir le marché
        </Link>

      </div>
    </main>

    </div>
    <SiteFooter :is-light="isLight" />
  </div>
</template>

<script setup>
// Import Vue
import { ref, computed } from 'vue'

// Composants
import { Link } from '@inertiajs/vue3'
import SiteFooter from '@/Components/SiteFooter.vue'
import Sidebar from '@/Components/Sidebar.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'
import Starfield from '@/Components/Starfield.vue'
import Ticker from '@/Components/Ticker.vue'

// Gestion thème
import { useTheme } from '@/composables/useTheme'
import { useSidebar } from '@/composables/useSidebar'

const { isSidebarOpen, toggleSidebar, closeSidebar } = useSidebar()

// Variables du thème
const {
  isLight,
  bgClass,
  textClass,
  titleClass,
  toggleLight,
  cycleDarkTheme,
} = useTheme()

// Menu sidebar
const items = [
  { route: 'dashboard', label: 'Tableau de bord', icon: 'home' },
  { route: 'crypto', label: 'Marché', icon: 'pulse' },
]

// Taille ticker
const tickerHeight = ref(48)

// Espace sous ticker
const gapUnderTicker = ref(32)

// Valeur CSS ticker
const tickerHeightPx = computed(() => `${tickerHeight.value}px`)

// Padding dynamique
const contentPadTopPx = computed(
  () => `${tickerHeight.value + gapUnderTicker.value}px`
)
</script>

<style scoped>
/* Effet néon */
.neon-text-night {
  text-shadow:
    0 0 6px #fff,
    0 0 12px #0ff,
    0 0 24px #0ff,
    0 0 48px #0ff,
    0 0 96px #0ff;
}

/* Dégradé titre */
.title-gradient {
  background: linear-gradient(90deg, #2563eb, #a855f7);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

@media (max-width: 767px) {
  .theme-orb {
    width: 2rem;
    height: 2rem;
    background: transparent;
    box-shadow: none;
    border: none;
    font-size: 1.15rem;
    color: inherit;
  }
}
</style>