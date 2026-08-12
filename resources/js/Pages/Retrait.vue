<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex">
    <Starfield />

    <Ticker />

    <ThemeSwitcher />

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

    <main
      class="relative z-10 w-full pb-16 transition-all duration-300 px-8"
      :style="{ paddingTop: 'calc(2rem + 48px)' }"
    >
      <section class="py-6">
        <div class="max-w-5xl mx-auto px-4">
          <div class="flex items-center justify-center">
            <h1 :class="['text-3xl md:text-4xl font-extrabold mb-6 text-center', titleClass]">
              Retrait - iSpaceCoin
            </h1>
          </div>

          <div class="mb-8">
            <WalletBalances />
          </div>

          <p
            v-if="message"
            class="text-center mb-6 font-semibold"
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

          <div
            :class="isLight ? 'bg-white/80' : 'bg-white/5'"
            class="max-w-xl mx-auto backdrop-blur-lg rounded-2xl shadow-xl ring-1 ring-white/10 p-6"
          >
            <form
              v-if="activeTab === 'Virement Bancaire (SEPA / SWIFT)'"
              @submit.prevent="submitWithdraw('bank')"
            >
              <input
                v-model="iban"
                type="text"
                placeholder="IBAN"
                class="input-field"
                required
              />

              <input
                v-model="bic"
                type="text"
                placeholder="BIC / SWIFT"
                class="input-field"
                required
              />

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
                🏦 Retirer
              </button>
            </form>

            <form
              v-if="activeTab === 'Portefeuille Crypto'"
              @submit.prevent="submitWithdraw('crypto')"
            >
              <div class="flex items-center gap-2 mb-4">
                <img
                  v-if="cryptoType === 'BTC'"
                  src="/images/btc.png"
                  alt="BTC"
                  class="w-6 h-6"
                />

                <img
                  v-if="cryptoType === 'ETH'"
                  src="/images/eth.png"
                  alt="ETH"
                  class="w-6 h-6"
                />

                <img
                  v-if="cryptoType === 'USDT'"
                  src="/images/usdt.png"
                  alt="USDT"
                  class="w-6 h-6"
                />

                <select v-model="cryptoType" class="input-field flex-1">
                  <option value="BTC">Bitcoin (BTC)</option>
                  <option value="ETH">Ethereum (ETH)</option>
                  <option value="USDT">Tether (USDT)</option>
                </select>
              </div>

              <p class="text-sm font-semibold mb-3">
                Solde disponible :
                {{ formatCryptoAmount(selectedCryptoBalance) }} {{ cryptoType }}
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
                💸 Retirer
              </button>
            </form>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

import Starfield from '@/Components/Starfield.vue'
import Ticker from '@/Components/Ticker.vue'
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
  'Virement Bancaire (SEPA / SWIFT)',
  'Portefeuille Crypto',
]

const activeTab = ref(tabs[0])

const balance = ref(0)
const cryptoBalances = ref([])

const amount = ref('')
const iban = ref('')
const bic = ref('')

const cryptoType = ref('BTC')
const cryptoAddress = ref('')

const message = ref('')

function formatCryptoAmount(amount) {
  return Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 8,
  })
}

function getBalanceForAsset(asset) {
  const found = cryptoBalances.value.find(
    crypto => crypto.currency === asset
  )

  return Number(found?.amount || 0)
}

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
    console.error(err)
  }
}

async function submitWithdraw(method) {
  try {
    let url = ''
    let body = {}

    if (method === 'bank') {
      url = '/wallet/withdraw'

      body = {
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
      message.value = `✅ ${data.message || 'Retrait effectué avec succès'}`
      await fetchBalance()
    } else {
      message.value = `❌ ${data.message || 'Erreur lors du retrait'}`
    }

    amount.value = ''
    iban.value = ''
    bic.value = ''
    cryptoAddress.value = ''
  } catch (err) {
    console.error(err)
    message.value = '❌ Impossible de traiter le retrait'
  }
}

onMounted(() => {
  fetchBalance()
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
  text-shadow: 0 2px 6px rgba(0,0,0,0.08);
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