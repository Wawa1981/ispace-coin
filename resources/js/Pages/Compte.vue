<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex">
    <Starfield />

    <Sidebar
      :items="items"
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      :tickerHeightPx="`${tickerHeight}px`"
      @toggle="isSidebarOpen = !isSidebarOpen"
    >
      <template #icon="{ icon, isLight }">
        <SidebarIcon :icon="icon" :isLight="isLight" />
      </template>
    </Sidebar>

    <main class="relative z-10 w-full pb-16 transition-all duration-300" :style="{ paddingTop: contentPadTopPx }">
      <ThemeSwitcher />
      <Ticker />

      <div class="flex flex-col items-center justify-start w-full min-h-[calc(100vh-10rem)] pt-20">
        <h1 :class="['text-3xl md:text-4xl font-extrabold mb-6 text-center', titleClass, 'neon-text-night']">
          Historique des Transactions
        </h1>

        <div class="mb-8">
          <WalletBalances />
        </div>

        <div class="mb-6 flex gap-3 items-center">
          <label class="font-semibold">Actif :</label>

          <select v-model="selectedAsset" @change="fetchTransactions" class="input-field w-40">
            <option value="ALL">Tous</option>
            <option value="EUR">Euro</option>
            <option value="BTC">Bitcoin</option>
            <option value="ETH">Ethereum</option>
            <option value="USDT">Tether</option>
          </select>
        </div>

        <div
          :class="isLight ? 'bg-white/80 backdrop-blur-lg' : 'bg-white/5 backdrop-blur-lg'"
          class="w-full max-w-5xl rounded-2xl shadow-xl ring-1 ring-white/10 overflow-hidden"
        >
          <div class="overflow-x-auto overflow-y-auto max-h-[48vh] scrollbox">
            <table class="min-w-full table-auto">
              <thead :class="isLight ? 'bg-gray-100/80 text-black' : 'bg-white/10 text-white'" class="sticky top-0 z-10">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Actif</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Type</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Montant</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Adresse / Référence</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Statut</th>
                </tr>
              </thead>

              <tbody :class="isLight ? 'divide-y divide-gray-200 text-black' : 'divide-y divide-white/10 text-white'">
                <tr v-for="t in filteredTransactions" :key="t.row_id" class="hover:bg-white/10 transition">
                  <td class="px-4 py-3">{{ formatDate(t.created_at) }}</td>
                  <td class="px-4 py-3 font-bold">{{ t.asset }}</td>
                  <td class="px-4 py-3">{{ t.type }}</td>
                  <td class="px-4 py-3">{{ formatAmount(t.amount, t.asset) }}</td>
                  <td class="px-4 py-3 font-mono text-xs break-all">{{ t.reference || '-' }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="statusClass(t.status)"
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium uppercase"
                    >
                      {{ t.status }}
                    </span>
                  </td>
                </tr>

                <tr v-if="filteredTransactions.length === 0">
                  <td colspan="6" class="text-center py-6">Aucune transaction trouvée</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

import Sidebar from '@/Components/Sidebar.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import Ticker from '@/Components/Ticker.vue'
import WalletBalances from '@/Components/WalletBalances.vue'

import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass, titleClass } = useTheme()

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

const isSidebarOpen = ref(true)

const selectedAsset = ref('ALL')
const transactions = ref([])

const assets = ['BTC', 'ETH', 'USDT']

function formatDate(date) {
  return new Date(date).toLocaleString('fr-FR')
}

function formatAmount(amount, asset) {
  if (asset === 'EUR') {
    return `${Number(amount || 0).toLocaleString('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })} €`
  }

  return `${Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 8,
  })} ${asset}`
}

function statusClass(status) {
  if (status === 'confirmed' || status === 'Succès' || status === 'internal_transfer') {
    return 'bg-emerald-500/20 text-emerald-400'
  }

  if (status === 'pending') {
    return 'bg-yellow-500/20 text-yellow-400'
  }

  return 'bg-red-500/20 text-red-400'
}

const filteredTransactions = computed(() => {
  if (selectedAsset.value === 'ALL') {
    return transactions.value
  }

  return transactions.value.filter(t => t.asset === selectedAsset.value)
})

async function fetchEuroTransactions() {
  const res = await fetch('/wallet/transactions', { cache: 'no-store' })
  const data = await res.json()

  if (!data.success) {
    return []
  }

  return data.transactions.map(t => ({
    row_id: `eur-${t.id}`,
    id: t.id,
    created_at: t.created_at,
    asset: 'EUR',
    type: t.type || 'transaction',
    amount: t.amount,
    reference: t.uuid || t.ref || null,
    status: t.status || 'Succès',
  }))
}

async function fetchCryptoTransactions(asset) {
  const res = await fetch(`/wallets/${asset.toLowerCase()}/transactions`, {
    cache: 'no-store',
    headers: {
      Accept: 'application/json',
    },
  })

  const data = await res.json()

  if (!data.success) {
    return []
  }

  const deposits = (data.deposits || []).map(t => ({
    row_id: `${asset}-deposit-${t.id}`,
    id: t.id,
    created_at: t.created_at,
    asset,
    type: 'deposit',
    amount: t.amount,
    reference: t.txid || t.from_address || null,
    status: t.status || 'confirmed',
  }))

  const withdraws = (data.withdraws || []).map(t => ({
    row_id: `${asset}-withdraw-${t.id}`,
    id: t.id,
    created_at: t.created_at,
    asset,
    type: t.status === 'internal_transfer' ? 'internal send' : 'withdraw',
    amount: `-${t.amount}`,
    reference: t.to_address || t.txid || null,
    status: t.status || 'pending',
  }))

  const incoming = (data.incoming || []).map(t => ({
    row_id: `${asset}-incoming-${t.id}`,
    id: t.id,
    created_at: t.created_at,
    asset,
    type: 'internal receive',
    amount: t.amount,
    reference: t.to_address || t.txid || null,
    status: t.status || 'internal_transfer',
  }))

  return [...deposits, ...withdraws, ...incoming]
}

async function fetchTransactions() {
  try {
    let rows = []

    if (selectedAsset.value === 'ALL' || selectedAsset.value === 'EUR') {
      rows = rows.concat(await fetchEuroTransactions())
    }

    const cryptoAssets = selectedAsset.value === 'ALL'
      ? assets
      : selectedAsset.value === 'EUR'
        ? []
        : [selectedAsset.value]

    for (const asset of cryptoAssets) {
      rows = rows.concat(await fetchCryptoTransactions(asset))
    }

    transactions.value = rows.sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at)
    })
  } catch (err) {
    console.error('Erreur fetch transactions:', err)
  }
}

const tickerHeight = ref(48)
const gapUnderTicker = ref(32)
const contentPadTopPx = computed(() => `${tickerHeight.value + gapUnderTicker.value}px`)

onMounted(fetchTransactions)
</script>

<style scoped>

.input-field {
  width: 100%;
  padding: 10px;
  margin-bottom: 12px;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 6px;
  color: white;
}

.input-field option {
  background: #0f172a;
  color: white;
}

table {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  width: 100%;
}

th {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 0.75rem;
}

td {
  padding: 0.75rem;
}

:deep(.scrollbox) {
  scrollbar-width: none;
  -ms-overflow-style: none;
  scrollbar-gutter: auto;
}

:deep(.scrollbox::-webkit-scrollbar) {
  width: 0;
  height: 0;
  display: none;
}

.neon-text-night {
  text-shadow:
    0 0 6px #fff,
    0 0 12px #0ff,
    0 0 24px #0ff,
    0 0 48px #0ff,
    0 0 96px #0ff;
}
</style>