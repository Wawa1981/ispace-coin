<template>
  <div class="text-center">
    <p class="text-xl font-bold">
      💰 Solde actuel : {{ formatEuro(balance) }} €
    </p>

    <div
      v-if="cryptoBalances.length > 0"
      class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3"
    >
      <div
        v-for="crypto in cryptoBalances"
        :key="crypto.currency"
        class="bg-white/10 rounded-lg px-4 py-2 font-semibold"
      >
        {{ crypto.currency }} : {{ formatCryptoAmount(crypto.amount) }}
      </div>
    </div>

    <p v-else class="mt-4 text-sm opacity-70">
      Aucun solde crypto trouvé.
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const balance = ref(0)
const cryptoBalances = ref([])

function formatEuro(amount) {
  return Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function formatCryptoAmount(amount) {
  return Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 8,
  })
}

async function fetchBalance() {
  try {
    const res = await fetch('/wallet', {
      cache: 'no-store',
    })

    const data = await res.json()

    balance.value = data?.balance ?? 0
    cryptoBalances.value = data?.crypto_balances ?? []
  } catch (err) {
    console.error('Erreur solde:', err)
  }
}

onMounted(fetchBalance)

defineExpose({
  fetchBalance,
})
</script>
