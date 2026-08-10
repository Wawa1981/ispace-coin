<template>
  <!-- Conteneur principal -->
  <div
    :class="[bgClass, textClass]"
    class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex"
  >
    <!-- Fond animé -->
    <Starfield />

    <!-- Sidebar -->
    <Sidebar
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      :items="items"
      :tickerHeightPx="tickerHeightPx"
      @toggle="toggleSidebar"
    >
      <!-- Slot des icônes -->
      <template #icon="{ icon, isLight }">
        <SidebarIcon :icon="icon" :isLight="isLight" />
      </template>
    </Sidebar>

    <!-- Contenu principal -->
    <main
      class="relative z-10 w-full pb-16 transition-all duration-300 px-8"
      :style="{ paddingTop: contentPadTopPx }"
    >
      <!-- Bandeau crypto -->
      <Ticker />

      <!-- Boutons thème -->
      <div class="fixed top-4 right-4 z-50 flex gap-3">

        <!-- Changer style thème sombre -->
        <button
          @click="cycleDarkTheme"
          class="w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
          title="Changer le style du thème sombre"
        >
          🌍
        </button>

        <!-- Jour / nuit -->
        <button
          @click="toggleLight"
          class="w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
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
          Tableau de bord CryptoBank
        </h1>

        <!-- Composant des soldes -->
        <WalletBalances />

      </div>
    </main>
  </div>
</template>

<script setup>
// Import Vue
import { ref, computed } from 'vue'

// Composants
import Sidebar from '@/Components/Sidebar.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'
import Starfield from '@/Components/Starfield.vue'
import Ticker from '@/Components/Ticker.vue'
import WalletBalances from '@/Components/WalletBalances.vue'

// Gestion thème
import { useTheme } from '@/composables/useTheme'

// État sidebar
const isSidebarOpen = ref(true)

// Ouvrir / fermer sidebar
function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

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
  { route: 'dashboard', label: 'Dashboard', icon: 'home' },
  { route: 'achat', label: 'Achat', icon: 'hex' },
  { route: 'retrait', label: 'Retrait', icon: 'orbit' },
  { route: 'deposit', label: 'Dépôt', icon: 'spark' },
  { route: 'envoyer', label: 'Envoyer', icon: 'stack' },
  { route: 'echange', label: 'Échange', icon: 'flow' },
  { route: 'compte', label: 'Compte', icon: 'id' },
  { route: 'cartes', label: 'Cartes', icon: 'cards' },
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
</style>