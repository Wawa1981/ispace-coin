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

      <div class="flex flex-col items-center justify-center w-full min-h-[calc(100vh-14rem)]">
        <h1 :class="['text-3xl font-extrabold text-center mb-8', titleClass]">
          Envoyer - iSpaceCoin
        </h1>

        <div class="mb-8">
          <WalletBalances />
        </div>

        <p
          v-if="message"
          class="mb-8 font-semibold text-center"
          :class="message.startsWith('✅') ? 'text-green-400' : 'text-red-400'"
        >
          {{ message }}
        </p>

        <div class="flex justify-center gap-4 mb-8">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="activeTab = tab"
            :class="[
              'px-4 py-2 rounded-lg font-bold transition',
              activeTab === tab ? 'bg-cyan-500 text-black' : 'bg-white/10 hover:bg-white/20'
            ]"
          >
            {{ tab }}
          </button>
        </div>

        <div class="max-w-xl mx-auto bg-white/5 p-6 rounded-lg shadow-xl ring-1 ring-white/10">
          <form v-if="activeTab === 'Vers un utilisateur'" @submit.prevent="submitSend('user')">
            <select v-model="sendAsset" class="input-field">
              <option value="EUR">Euro (EUR)</option>
              <option value="BTC">Bitcoin (BTC)</option>
              <option value="ETH">Ethereum (ETH)</option>
              <option value="USDT">Tether (USDT)</option>
            </select>

            <p class="text-sm font-semibold mb-3">
              Solde disponible :
              {{ formatAmount(selectedSendAssetBalance, sendAsset) }} {{ sendAsset }}
            </p>

            <input
              v-model="recipientEmail"
              type="email"
              placeholder="Email du destinataire"
              class="input-field"
              required
            />

            <input
              v-model="amount"
              type="number"
              step="0.00000001"
              min="0.00000001"
              placeholder="Montant"
              class="input-field"
              required
            />

            <button
              type="submit"
              class="btn-primary"
              :disabled="Number(amount) <= 0 || Number(amount) > Number(selectedSendAssetBalance)"
              :class="Number(amount) <= 0 || Number(amount) > Number(selectedSendAssetBalance) ? 'opacity-50 cursor-not-allowed' : ''"
            >
              📤 Envoyer
            </button>
          </form>

          <form v-if="activeTab === 'Vers portefeuille crypto'" @submit.prevent="submitSend('crypto')">
            <div class="flex items-center gap-2 mb-4">
              <img v-if="cryptoType === 'BTC'" src="/images/btc.png" alt="BTC" class="w-6 h-6" />
              <img v-if="cryptoType === 'ETH'" src="/images/eth.png" alt="ETH" class="w-6 h-6" />
              <img v-if="cryptoType === 'USDT'" src="/images/usdt.png" alt="USDT" class="w-6 h-6" />

              <select v-model="cryptoType" class="input-field flex-1">
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
                <option value="USDT">Tether (USDT)</option>
              </select>
            </div>

            <p class="text-sm font-semibold mb-3">
              Solde disponible :
              {{ formatAmount(selectedCryptoBalance, cryptoType) }} {{ cryptoType }}
            </p>

            <input
              v-model="cryptoAddress"
              type="text"
              placeholder="Adresse du portefeuille"
              class="input-field"
              required
            />

            <input
              v-model="amount"
              type="number"
              step="0.00000001"
              min="0.00000001"
              placeholder="Montant"
              class="input-field"
              required
            />

            <button
              type="submit"
              class="btn-primary"
              :disabled="Number(amount) <= 0 || Number(amount) > Number(selectedCryptoBalance)"
              :class="Number(amount) <= 0 || Number(amount) > Number(selectedCryptoBalance) ? 'opacity-50 cursor-not-allowed' : ''"
            >
              📤 Envoyer
            </button>
          </form>
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

const tabs = ['Vers un utilisateur', 'Vers portefeuille crypto']
const activeTab = ref(tabs[0])

const balance = ref(0)
const cryptoBalances = ref([])

const amount = ref('')
const recipientEmail = ref('')
const sendAsset = ref('EUR')

const cryptoType = ref('BTC')
const cryptoAddress = ref('')

const message = ref('')

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

function getBalanceForAsset(asset) {
  if (asset === 'EUR') {
    return Number(balance.value || 0)
  }

  const found = cryptoBalances.value.find(
    crypto => crypto.currency === asset
  )

  return Number(found?.amount || 0)
}

const selectedSendAssetBalance = computed(() => {
  return getBalanceForAsset(sendAsset.value)
})

const selectedCryptoBalance = computed(() => {
  return getBalanceForAsset(cryptoType.value)
})

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

async function submitSend(method) {
  try {
    let url = ''
    let body = {}

    if (method === 'user') {
      url = '/wallet/transfer-asset'

      body = {
        email: recipientEmail.value,
        asset: sendAsset.value,
        amount: amount.value,
      }
    } else if (method === 'crypto') {
      url = `/wallets/${cryptoType.value.toLowerCase()}/withdraw`

      body = {
        to: cryptoAddress.value,
        amount: amount.value,
      }
    }

    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          .content,
      },
      body: JSON.stringify(body),
    })

    const data = await res.json()

    if (res.ok) {
      message.value = `✅ ${data.message || 'Opération réussie'}`
      await fetchBalance()
    } else {
      message.value = `❌ ${data.message || 'Erreur'}`
    }

    amount.value = ''
    recipientEmail.value = ''
    cryptoAddress.value = ''
  } catch (err) {
    console.error('Erreur:', err)
    message.value = '❌ Impossible de traiter l’opération'
  }
}
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
  text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

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