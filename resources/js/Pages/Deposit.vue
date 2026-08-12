<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex">
    <Starfield />

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
          Dépôt - iSpaceCoin
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
            @click="changeTab(tab)"
            :class="[
              'px-4 py-2 rounded-lg font-bold transition',
              activeTab === tab ? 'bg-cyan-500 text-black' : 'bg-white/10 hover:bg-white/20'
            ]"
          >
            {{ tab }}
          </button>
        </div>

        <div class="max-w-xl mx-auto bg-white/5 p-6 rounded-lg shadow-xl ring-1 ring-white/10">
          <form v-if="activeTab === 'Carte Visa / Mastercard'" @submit.prevent="submitDeposit('card')">
            <input v-model="card.number" type="text" placeholder="Numéro de carte" class="input-field" required />
            <input v-model="card.name" type="text" placeholder="Nom sur la carte" class="input-field" required />

            <div class="flex gap-4">
              <input v-model="card.expiry" type="text" placeholder="MM/AA" class="input-field flex-1" required />
              <input v-model="card.cvc" type="text" placeholder="CVC" class="input-field flex-1" required />
            </div>

            <input
              v-model="amount"
              type="number"
              step="0.01"
              min="0.01"
              placeholder="Montant (€)"
              class="input-field"
              required
            />

            <button type="submit" class="btn-primary">
              💳 Déposer
            </button>
          </form>

          <div v-if="activeTab === 'Virement Bancaire (SEPA / SWIFT)'">
            <p class="mb-2"><strong>Frais :</strong> 0 €</p>
            <p><strong>IBAN :</strong> FR76 3000 6000 0112 3456 7890 189</p>
            <p><strong>BIC/SWIFT :</strong> AGRIFRPPXXX</p>
            <p class="mt-2">Effectuez un virement, crédit sous 24-48h.</p>
          </div>

          <div v-if="activeTab === 'Portefeuille Crypto'">
            <select v-model="cryptoType" @change="fetchDepositAddress" class="input-field">
              <option value="BTC">Bitcoin (BTC)</option>
              <option value="ETH">Ethereum (ETH)</option>
              <option value="USDT">Tether (USDT)</option>
            </select>

            <p class="mt-2"><strong>Adresse de dépôt {{ cryptoType }} :</strong></p>

            <p class="bg-gray-800 p-2 rounded font-mono break-all min-h-[44px]">
              {{ cryptoAddress || 'Chargement adresse...' }}
            </p>

            <button @click="copyAddress" class="btn-primary mt-4" :disabled="!cryptoAddress">
              📋 Copier
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import Starfield from '@/Components/Starfield.vue'
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

const tabs = [
  'Carte Visa / Mastercard',
  'Virement Bancaire (SEPA / SWIFT)',
  'Portefeuille Crypto',
]

const activeTab = ref(tabs[0])

const amount = ref('')
const message = ref('')

const card = ref({
  number: '',
  name: '',
  expiry: '',
  cvc: '',
})

const cryptoType = ref('BTC')
const cryptoAddress = ref('')

function changeTab(tab) {
  activeTab.value = tab

  if (tab === 'Portefeuille Crypto') {
    fetchDepositAddress()
  }
}

async function fetchDepositAddress() {
  try {
    cryptoAddress.value = ''
    message.value = ''

    const res = await fetch(`/wallets/${cryptoType.value.toLowerCase()}/deposit-address`, {
      cache: 'no-store',
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await res.json()

    if (res.ok && data.address) {
      cryptoAddress.value = data.address
    } else {
      message.value = `❌ ${data.message || 'Impossible de récupérer l’adresse'}`
    }
  } catch (err) {
    console.error(err)
    message.value = '❌ Erreur récupération adresse dépôt'
  }
}

async function submitDeposit(method) {
  try {
    if (method === 'card') {
      const res = await fetch('/wallet/deposit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          amount: amount.value,
        }),
      })

      const data = await res.json()

      if (res.ok) {
        message.value = `✅ ${data.message || 'Dépôt effectué avec succès'}`
      } else {
        message.value = `❌ ${data.message || 'Erreur lors du dépôt'}`
      }
    }

    amount.value = ''
    card.value = {
      number: '',
      name: '',
      expiry: '',
      cvc: '',
    }
  } catch (err) {
    console.error(err)
    message.value = '❌ Impossible de traiter le dépôt'
  }
}

function copyAddress() {
  if (!cryptoAddress.value) {
    message.value = '❌ Aucune adresse à copier'
    return
  }

  navigator.clipboard.writeText(cryptoAddress.value)
  message.value = '✅ Adresse copiée !'
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
  background: rgba(255,255,255,0.1);
  border: none;
  border-radius: 6px;
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