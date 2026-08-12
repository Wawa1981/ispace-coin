<template>
  <div class="crypto-prices-container">
    <h2 class="text-xl font-bold mb-4">Prix des cryptomonnaies</h2>
    
    <!-- loading seulement s'il n'y a encore rien à montrer -->
    <div v-if="loading && !filteredMarkets.length" class="text-center p-4">
      <p>Chargement des prix...</p>
    </div>

    <div v-else-if="error && !filteredMarkets.length" class="text-center p-4 text-red-500">
      <p>Erreur: {{ error }}</p>
    </div>

    <ul v-else-if="filteredMarkets.length" class="space-y-2">
      <li v-for="crypto in filteredMarkets" :key="crypto.id" class="flex items-center space-x-2 p-2 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-sm">
        <img :src="crypto.image" :alt="crypto.name" class="w-6 h-6 rounded-full" @error="(e) => { e.target.src = '/image/coins/default.svg' }">
        <span>
          <strong class="font-medium">{{ crypto.name }} ({{ crypto.symbol?.toUpperCase() }})</strong>:
          {{ fmtPrice(crypto.current_price) }} USD
          <span :class="{'text-emerald-500': crypto.price_change_percentage_24h > 0, 'text-red-500': crypto.price_change_percentage_24h < 0}">
             ({{ Number(crypto.price_change_percentage_24h || 0).toFixed(2) }}%)
          </span>
        </span>
      </li>
    </ul>

    <div v-else class="text-center p-4 text-gray-500">
      <p>Aucune donnée de marché disponible.</p>
    </div>
  </div>
</template>

<script setup>
import { useCryptoMarkets } from '@/composables/useCryptoMarkets'

// Utilisation du composable pour récupérer l'état et les données
const { markets, loading, error, query, filteredMarkets, isTableCollapsed, toggleTableCollapse } = useCryptoMarkets()

// Fonction de formatage (peut être déplacée dans un composable si elle est utilisée ailleurs)
function fmtPrice(v) { 
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v ?? 0) 
}
</script>

<style scoped>
/* Styles spécifiques au composant si nécessaire */
</style>
