<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-900 to-black p-6 text-white">
    <div class="flex items-center mb-6">
      <img :src="`/crypto-icons/${crypto.icon}`" alt="" class="w-12 h-12 mr-4"/>
      <h1 class="text-2xl font-bold">{{ crypto.name }} ({{ crypto.symbol }})</h1>
    </div>

    <div class="mb-6">
      <label class="block mb-2">Montant</label>
      <input type="number" v-model="amount" class="w-full p-3 rounded-lg bg-gray-800 text-white" placeholder="0.00"/>
    </div>

    <div class="mb-6">
      <label class="block mb-2">Méthode de paiement</label>
      <div class="grid gap-3">
        <button v-for="m in methods" :key="m" class="bg-gray-800 hover:bg-gray-700 p-4 rounded-xl">
          {{ m }}
        </button>
      </div>
    </div>

    <button class="w-full bg-cyan-500 hover:bg-cyan-400 text-black font-bold py-3 rounded-xl">
      Confirmer l’achat
    </button>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const symbol = usePage().props.ziggy.routeParams.symbol // récupère le paramètre de l'URL
const cryptos = {
  BTC: { name: "Bitcoin", icon: "btc.svg", symbol: "BTC" },
  ETH: { name: "Ethereum", icon: "eth.svg", symbol: "ETH" },
  USDT: { name: "Tether", icon: "usdt.svg", symbol: "USDT" }
}

const crypto = cryptos[symbol]
const amount = ref('')
const methods = ["Visa", "MasterCard", "Virement SEPA", "Swift", "Portefeuille Crypto"]
</script>
