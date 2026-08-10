<template>
  <div class="w-full lg:w-4/5 bg-gray-800 rounded-xl shadow-xl p-4 overflow-y-auto">
    <h2 class="text-lg font-semibold mb-4 text-center">Carnet d'ordres</h2>
    <div class="space-y-4">
      <div>
        <div class="grid grid-cols-3 gap-4 text-sm font-medium text-gray-400 mb-2">
          <span>Prix (USD)</span>
          <span>Quantité</span>
          <span>Total</span>
        </div>
        <div v-for="ask in asks" :key="ask.price" class="grid grid-cols-3 gap-4 text-sm text-red-400">
          <span>{{ fmtCurrency(ask.price) }}</span>
          <span>{{ ask.amount.toFixed(4) }}</span>
          <span>{{ fmtCurrency(ask.price * ask.amount) }}</span>
        </div>
      </div>
      <div class="text-center py-2 bg-gray-700 rounded-md font-medium">
        {{ fmtCurrency(currentPrice) }}
      </div>
      <div>
        <div v-for="bid in bids" :key="bid.price" class="grid grid-cols-3 gap-4 text-sm text-green-400">
          <span>{{ fmtCurrency(bid.price) }}</span>
          <span>{{ bid.amount.toFixed(4) }}</span>
          <span>{{ fmtCurrency(bid.price * bid.amount) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  currentPrice: {
    type: Number,
    required: false,
    default: 0
  }
});

const asks = ref([]);
const bids = ref([]);

function fmtCurrency(v) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'USD' }).format(v ?? 0);
}

// Simulation du chargement du carnet d'ordres
function loadOrderBook(price) {
  if (!price) return;
  asks.value = Array.from({ length: 10 }, (_, i) => ({
    price: price + (i + 1) * 10,
    amount: Math.random() * 5 + 0.1,
  })).sort((a, b) => a.price - b.price);

  bids.value = Array.from({ length: 10 }, (_, i) => ({
    price: price - (i + 1) * 10,
    amount: Math.random() * 5 + 0.1,
  })).sort((a, b) => b.price - a.price);
}

// Met à jour le carnet d'ordres lorsque le prix change
watch(() => props.currentPrice, (newPrice) => {
  loadOrderBook(newPrice);
}, { immediate: true });

onMounted(() => {
  // Une première simulation au cas où la prop n'est pas encore mise à jour
  if (!props.currentPrice) {
    loadOrderBook(60000);
  }
});
</script>
