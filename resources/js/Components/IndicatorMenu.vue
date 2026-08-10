<template>
  <div class="relative">
    <!-- Bouton principal -->
    <button @click="open = !open"
      class="px-3 py-1.5 rounded-md bg-green-900 hover:bg-red-700 text-sm font-medium">
      Indicateurs
    </button>

    <!-- Menu déroulant -->
    <div v-if="open" class="absolute left-0 mt-2 w-72 bg-gray-900 p-3 rounded shadow-lg z-50">
      <!-- Recherche -->
      <input v-model="search" placeholder="Rechercher..."
        class="w-full p-2 mb-2 rounded bg-gray-800 text-white text-sm focus:outline-none" />

      <!-- Liste scrollable -->
      <div class="max-h-60 overflow-y-auto">
        <div v-for="ind in filteredIndicators" :key="ind.key"
          class="flex items-center justify-between px-2 py-1 hover:bg-gray-700 rounded cursor-pointer">
          <span>{{ ind.name }}</span>
          <input type="checkbox" v-model="ind.active" @change="toggle(ind)" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const emit = defineEmits(['toggle-indicator'])

const open = ref(false)
const search = ref('')

// Liste d’indicateurs disponibles
const indicators = ref([
  { name: 'SMA (Moyenne mobile simple)', key: 'sma', active: false },
  { name: 'EMA (Moyenne mobile exponentielle)', key: 'ema', active: false },
  { name: 'RSI (Relative Strength Index)', key: 'rsi', active: false },
  { name: 'MACD', key: 'macd', active: false },
  { name: 'Bandes de Bollinger', key: 'bollinger', active: false },
])

// Filtrage dynamique par recherche
const filteredIndicators = computed(() =>
  indicators.value.filter(ind =>
    ind.name.toLowerCase().includes(search.value.toLowerCase())
  )
)

function toggle(ind) {
  emit('toggle-indicator', { key: ind.key, enabled: ind.active })
}
</script>

<style scoped>
/* petite ombre + style sobre */
</style>
