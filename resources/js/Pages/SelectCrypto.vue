<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 to-black p-6 text-white">
    <h1 class="text-2xl font-bold mb-6">Choisir une cryptomonnaie</h1>
    <div class="grid gap-4">
      <div 
        v-for="crypto in cryptos" 
        :key="crypto.symbol"
        class="flex items-center bg-gray-800 hover:bg-gray-700 p-4 rounded-xl cursor-pointer transition"
        @click="$inertia.visit(`/deposit/confirm/${crypto.symbol}`)"
      >
        <img :src="`/crypto-icons/${crypto.icon}`" alt="" class="w-10 h-10 mr-4"/>
        <div>
          <div class="text-lg font-semibold">{{ crypto.name }}</div>
          <div class="text-gray-400 text-sm">{{ crypto.symbol }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const cryptos = [
  { name: "Bitcoin", symbol: "BTC", icon: "btc.svg" },
  { name: "Ethereum", symbol: "ETH", icon: "eth.svg" },
  { name: "Tether", symbol: "USDT", icon: "usdt.svg" }
  // Ajoute les autres ici
]
</script>
