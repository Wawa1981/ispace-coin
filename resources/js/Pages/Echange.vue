<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex flex-col">
    <Starfield />

    <div class="page-body flex flex-1 min-h-0 w-full">
    <Sidebar
      :items="items"
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      @toggle="isSidebarOpen = !isSidebarOpen"
    >
      <template #icon="{ icon, isLight }">
        <SidebarIcon :icon="icon" :isLight="isLight" />
      </template>
    </Sidebar>

    <main class="relative z-10 w-full pb-16 transition-all duration-300 px-8">
      <ThemeSwitcher />

      <div class="flex flex-col items-center justify-start w-full min-h-[calc(100vh-10rem)] pt-20">
        <h1 :class="['text-3xl font-extrabold text-center mb-8', titleClass]">
          Échange de cryptomonnaies
        </h1>

        <div class="mb-8">
          <WalletBalances />
        </div>

        <div class="max-w-xl w-full bg-white/5 p-6 rounded-lg shadow-xl ring-1 ring-dark">
          <div class="flex flex-col gap-4">
            <label>De :</label>
            <select v-model="fromCoin" class="input-field">
              <option value="EUR">Euro (EUR)</option>
              <option value="BTC">Bitcoin (BTC)</option>
              <option value="ETH">Ethereum (ETH)</option>
              <option value="USDT">Tether (USDT)</option>
            </select>

            <p class="text-sm font-semibold">
              Solde disponible :
              {{ formatAmount(selectedFromBalance, fromCoin) }} {{ fromCoin }}
            </p>

            <label>Vers :</label>
            <select v-model="toCoin" class="input-field">
              <option value="EUR">Euro (EUR)</option>
              <option value="BTC">Bitcoin (BTC)</option>
              <option value="ETH">Ethereum (ETH)</option>
              <option value="USDT">Tether (USDT)</option>
            </select>

            <p class="text-sm font-semibold">
              Solde actuel destination :
              {{ formatAmount(selectedToBalance, toCoin) }} {{ toCoin }}
            </p>

            <label>Montant :</label>
            <input
              v-model="amount"
              type="number"
              step="0.00000001"
              min="0.00000001"
              placeholder="Montant"
              class="input-field"
            />

            <div class="mt-4 text-center text-lg font-bold">
              Résultat estimé : {{ formatAmount(convertAmount, toCoin) }} {{ toCoin }}
            </div>

            <p
              v-if="Number(amount) > Number(selectedFromBalance)"
              class="text-red-400 font-semibold text-center"
            >
              Solde insuffisant.
            </p>

            <p
              v-if="fromCoin === toCoin"
              class="text-red-400 font-semibold text-center"
            >
              Impossible d’échanger la même devise.
            </p>

            <p
              v-if="message"
              class="text-center font-semibold"
              :class="message.startsWith('✅') ? 'text-green-400' : 'text-red-400'"
            >
              {{ message }}
            </p>

            <button
              class="btn-primary mt-6"
              @click="submitExchange"
              :disabled="isExchangeDisabled"
              :class="isExchangeDisabled ? 'opacity-50 cursor-not-allowed' : ''"
            >
              🔁 Échanger
            </button>
          </div>
        </div>
      </div>
    </main>

    </div>
    <SiteFooter :is-light="isLight" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

import Starfield from '@/Components/Starfield.vue'
import SiteFooter from '@/Components/SiteFooter.vue'
import Sidebar from '@/Components/Sidebar.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import WalletBalances from '@/Components/WalletBalances.vue'

import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass, titleClass } = useTheme()

const isSidebarOpen = ref(true)

const items = [
  { route: 'dashboard', label: 'Tableau de bord', icon: 'home' },
  { route: 'achat', label: 'Achat', icon: 'hex' },
  { route: 'retrait', label: 'Retrait', icon: 'orbit' },
  { route: 'deposit', label: 'Dépôt', icon: 'spark' },
  { route: 'envoyer', label: 'Envoyer', icon: 'stack' },
  { route: 'echange', label: 'Échange', icon: 'flow' },
  { route: 'compte', label: 'Compte', icon: 'id' },
  { route: 'cartes', label: 'Cartes', icon: 'cards' },
  { route: 'crypto', label: 'Marché', icon: 'pulse' },
]

const balance = ref(0)
const cryptoBalances = ref([])

const fromCoin = ref('BTC')
const toCoin = ref('USDT')
const amount = ref('')
const message = ref('')

const pricesUsd = ref({})

function formatAmount(amount, asset) {
  if (asset === 'EUR') {
    return Number(amount || 0).toLocaleString('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  }

  return Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 8,
  })
}

async function fetchPrices() {
  try {
    const res = await fetch('/api/price?ids=bitcoin,ethereum,tether&vs_currencies=usd')

    const data = await res.json()

    pricesUsd.value = {
      EUR: 1.08,
      BTC: data.bitcoin?.usd || 0,
      ETH: data.ethereum?.usd || 0,
      USDT: data.tether?.usd || 1,
    }
  } catch (err) {
    console.error('Erreur prix crypto:', err)
  }
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

function getBalanceForCurrency(currency) {
  if (currency === 'EUR') {
    return Number(balance.value || 0)
  }

  const found = cryptoBalances.value.find(
    crypto => crypto.currency === currency
  )

  return Number(found?.amount || 0)
}

const selectedFromBalance = computed(() => {
  return getBalanceForCurrency(fromCoin.value)
})

const selectedToBalance = computed(() => {
  return getBalanceForCurrency(toCoin.value)
})

const convertAmount = computed(() => {
  if (!fromCoin.value || !toCoin.value || !amount.value) {
    return 0
  }

  const fromPrice = pricesUsd.value[fromCoin.value] || 0
  const toPrice = pricesUsd.value[toCoin.value] || 0

  if (!fromPrice || !toPrice) {
    return 0
  }

  return (Number(amount.value) * fromPrice) / toPrice
})

const isExchangeDisabled = computed(() => {
  return (
    Number(amount.value) <= 0 ||
    Number(amount.value) > Number(selectedFromBalance.value) ||
    fromCoin.value === toCoin.value ||
    Number(convertAmount.value) <= 0
  )
})

async function submitExchange() {
  if (isExchangeDisabled.value) {
    return
  }

  try {
    const res = await fetch('/wallet/exchange', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          .content,
      },
      body: JSON.stringify({
        from: fromCoin.value,
        to: toCoin.value,
        amount: amount.value,
        converted_amount: Number(convertAmount.value).toFixed(8),
      }),
    })

    const data = await res.json()

    if (res.ok) {
      message.value = `✅ ${data.message || 'Échange effectué'}`
      amount.value = ''
      await fetchBalance()
    } else {
      message.value = `❌ ${data.message || 'Erreur lors de l’échange'}`
    }
  } catch (err) {
    console.error(err)
    message.value = '❌ Impossible de traiter l’échange'
  }
}

onMounted(() => {
  fetchBalance()
  fetchPrices()
})
</script>

<style scoped>
.neon-text-night {
  text-shadow:
    0 0 6px #fff,
    0 0 12px #0ff,
    0 0 24px #0ff,
    0 0 48px #0ff,
    0 0 96px #0ff;
}

.title-gradient {
  background: linear-gradient(90deg, #2563eb, #a855f7);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.input-field {
  width: 100%;
  padding: 10px;
  margin-bottom: 12px;
  background: rgba(255,255,255,0.1);
  border: none;
  border-radius: 6px;
  color: blue;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background: linear-gradient(to right, #06b6d4, #2563eb);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.btn-primary:hover {
  transform: scale(1.02);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>