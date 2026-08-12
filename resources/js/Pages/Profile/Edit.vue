<template>
  <div
    :class="[bgClass, textClass]"
    class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex"
  >
    <Head title="Profil" />
    <Starfield />

    <Sidebar
      :items="items"
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      @toggle="isSidebarOpen = !isSidebarOpen"
    >
      <template #icon="{ icon, isLight: light }">
        <SidebarIcon :icon="icon" :isLight="light" />
      </template>
    </Sidebar>

    <main class="relative z-10 w-full pb-16 transition-all duration-300 px-4 md:px-8">
      <ThemeSwitcher />

      <div class="flex flex-col items-center w-full min-h-[calc(100vh-4rem)] pt-16 md:pt-20">
        <h1 :class="['text-3xl md:text-4xl font-extrabold text-center mb-2', titleClass]">
          Mon profil
        </h1>
        <p class="mb-8 text-center opacity-70 text-sm md:text-base">
          Gérez vos informations iSpaceCoin
        </p>

        <!-- Carte identité -->
        <div
          class="w-full max-w-2xl mb-8 rounded-2xl p-6 shadow-xl ring-1 ring-white/10 backdrop-blur-lg"
          :class="isLight ? 'bg-white/80' : 'bg-white/5'"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg bg-gradient-to-br from-cyan-400 to-blue-600 text-white"
            >
              {{ initials }}
            </div>
            <div class="min-w-0">
              <p class="text-xl font-bold truncate">{{ user.name }}</p>
              <p class="text-sm opacity-70 truncate">{{ user.email }}</p>
              <p class="mt-1 text-xs">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 font-medium"
                  :class="
                    user.email_verified_at
                      ? 'bg-emerald-500/20 text-emerald-400'
                      : 'bg-yellow-500/20 text-yellow-400'
                  "
                >
                  {{ user.email_verified_at ? 'Email vérifié' : 'Email non vérifié' }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Onglets -->
        <div class="flex flex-wrap justify-center gap-2 mb-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            @click="activeTab = tab.id"
            :class="[
              'px-4 py-2 rounded-lg font-bold transition',
              activeTab === tab.id
                ? 'bg-cyan-500 text-black'
                : isLight
                  ? 'bg-black/10 hover:bg-black/20'
                  : 'bg-white/10 hover:bg-white/20',
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- Infos profil -->
        <div
          v-show="activeTab === 'info'"
          class="w-full max-w-xl rounded-2xl p-6 shadow-xl ring-1 ring-white/10 backdrop-blur-lg"
          :class="isLight ? 'bg-white/80' : 'bg-white/5'"
        >
          <h2 class="text-lg font-bold mb-1">Informations du compte</h2>
          <p class="text-sm opacity-70 mb-6">
            Mettez à jour votre nom et votre adresse e-mail.
          </p>

          <form @submit.prevent="submitProfile" class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-semibold mb-1">Nom</label>
              <input
                id="name"
                v-model="profileForm.name"
                type="text"
                required
                autocomplete="name"
                class="input-field"
                :class="isLight ? 'input-light' : ''"
              />
              <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-400">
                {{ profileForm.errors.name }}
              </p>
            </div>

            <div>
              <label for="email" class="block text-sm font-semibold mb-1">E-mail</label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                required
                autocomplete="username"
                class="input-field"
                :class="isLight ? 'input-light' : ''"
              />
              <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-400">
                {{ profileForm.errors.email }}
              </p>
            </div>

            <div
              v-if="mustVerifyEmail && !user.email_verified_at"
              class="rounded-lg p-3 text-sm"
              :class="isLight ? 'bg-amber-50 text-amber-900' : 'bg-amber-500/10 text-amber-200'"
            >
              <p>
                Votre e-mail n’est pas vérifié.
                <Link
                  :href="route('verification.send')"
                  method="post"
                  as="button"
                  class="underline font-semibold hover:opacity-80"
                >
                  Renvoyer le lien de vérification
                </Link>
              </p>
              <p
                v-if="status === 'verification-link-sent'"
                class="mt-2 font-medium text-emerald-400"
              >
                Un nouveau lien de vérification a été envoyé.
              </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button
                type="submit"
                class="btn-primary"
                :disabled="profileForm.processing"
                :class="{ 'opacity-50 cursor-not-allowed': profileForm.processing }"
              >
                {{ profileForm.processing ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
              <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0"
              >
                <p v-if="profileForm.recentlySuccessful" class="text-sm text-emerald-400 font-semibold">
                  Enregistré ✓
                </p>
              </Transition>
            </div>
          </form>
        </div>

        <!-- Mot de passe -->
        <div
          v-show="activeTab === 'password'"
          class="w-full max-w-xl rounded-2xl p-6 shadow-xl ring-1 ring-white/10 backdrop-blur-lg"
          :class="isLight ? 'bg-white/80' : 'bg-white/5'"
        >
          <h2 class="text-lg font-bold mb-1">Mot de passe</h2>
          <p class="text-sm opacity-70 mb-6">
            Choisissez un mot de passe long et unique pour sécuriser votre compte.
          </p>

          <form @submit.prevent="submitPassword" class="space-y-4">
            <div>
              <label for="current_password" class="block text-sm font-semibold mb-1">
                Mot de passe actuel
              </label>
              <input
                id="current_password"
                ref="currentPasswordInput"
                v-model="passwordForm.current_password"
                type="password"
                autocomplete="current-password"
                class="input-field"
                :class="isLight ? 'input-light' : ''"
              />
              <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-400">
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold mb-1">
                Nouveau mot de passe
              </label>
              <input
                id="password"
                ref="passwordInput"
                v-model="passwordForm.password"
                type="password"
                autocomplete="new-password"
                class="input-field"
                :class="isLight ? 'input-light' : ''"
              />
              <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-400">
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-semibold mb-1">
                Confirmer le mot de passe
              </label>
              <input
                id="password_confirmation"
                v-model="passwordForm.password_confirmation"
                type="password"
                autocomplete="new-password"
                class="input-field"
                :class="isLight ? 'input-light' : ''"
              />
              <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-400">
                {{ passwordForm.errors.password_confirmation }}
              </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button
                type="submit"
                class="btn-primary"
                :disabled="passwordForm.processing"
                :class="{ 'opacity-50 cursor-not-allowed': passwordForm.processing }"
              >
                {{ passwordForm.processing ? 'Mise à jour…' : 'Mettre à jour' }}
              </button>
              <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0"
              >
                <p v-if="passwordForm.recentlySuccessful" class="text-sm text-emerald-400 font-semibold">
                  Mot de passe mis à jour ✓
                </p>
              </Transition>
            </div>
          </form>
        </div>

        <!-- Zone danger -->
        <div
          v-show="activeTab === 'danger'"
          class="w-full max-w-xl rounded-2xl p-6 shadow-xl ring-1 ring-red-500/30 backdrop-blur-lg"
          :class="isLight ? 'bg-red-50/90' : 'bg-red-950/40'"
        >
          <h2 class="text-lg font-bold mb-1 text-red-400">Zone sensible</h2>
          <p class="text-sm opacity-80 mb-6">
            La suppression du compte est définitive. Soldes, historique et données associées
            seront effacés. Téléchargez ce dont vous avez besoin avant de continuer.
          </p>

          <button
            type="button"
            class="btn-danger"
            @click="confirmingDeletion = true"
          >
            Supprimer mon compte
          </button>
        </div>

        <!-- Déconnexion -->
        <div class="mt-10 mb-8">
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="px-6 py-2.5 rounded-lg font-semibold transition ring-1 ring-white/20"
            :class="isLight ? 'bg-black/10 hover:bg-black/20' : 'bg-white/10 hover:bg-white/20'"
          >
            Se déconnecter
          </Link>
        </div>
      </div>
    </main>

    <!-- Modal suppression -->
    <div
      v-if="confirmingDeletion"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeDeleteModal" />
      <div
        class="relative z-10 w-full max-w-md rounded-2xl p-6 shadow-2xl ring-1 ring-white/10"
        :class="isLight ? 'bg-white text-gray-900' : 'bg-slate-900 text-white'"
      >
        <h3 class="text-lg font-bold mb-2">Confirmer la suppression</h3>
        <p class="text-sm opacity-80 mb-4">
          Entrez votre mot de passe pour supprimer définitivement votre compte iSpaceCoin.
        </p>

        <label for="delete_password" class="block text-sm font-semibold mb-1">Mot de passe</label>
        <input
          id="delete_password"
          ref="deletePasswordInput"
          v-model="deleteForm.password"
          type="password"
          class="input-field"
          :class="isLight ? 'input-light' : ''"
          placeholder="Mot de passe"
          @keyup.enter="deleteAccount"
        />
        <p v-if="deleteForm.errors.password" class="mt-1 text-sm text-red-400">
          {{ deleteForm.errors.password }}
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-lg font-semibold"
            :class="isLight ? 'bg-gray-100 hover:bg-gray-200' : 'bg-white/10 hover:bg-white/20'"
            @click="closeDeleteModal"
          >
            Annuler
          </button>
          <button
            type="button"
            class="btn-danger !w-auto px-4"
            :disabled="deleteForm.processing"
            :class="{ 'opacity-50 cursor-not-allowed': deleteForm.processing }"
            @click="deleteAccount"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

import Sidebar from '@/Components/Sidebar.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import { useTheme } from '@/composables/useTheme'

defineProps({
  mustVerifyEmail: { type: Boolean, default: false },
  status: { type: String, default: null },
})

const { isLight, bgClass, textClass, titleClass } = useTheme()

const isSidebarOpen = ref(true)
const activeTab = ref('info')
const confirmingDeletion = ref(false)
const currentPasswordInput = ref(null)
const passwordInput = ref(null)
const deletePasswordInput = ref(null)

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
  { id: 'info', label: 'Informations' },
  { id: 'password', label: 'Mot de passe' },
  { id: 'danger', label: 'Zone sensible' },
]

const page = usePage()
const user = computed(() => page.props.auth.user)

const initials = computed(() => {
  const name = user.value?.name || '?'
  return name
    .split(/\s+/)
    .map((p) => p[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

const profileForm = useForm({
  name: user.value?.name || '',
  email: user.value?.email || '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const deleteForm = useForm({
  password: '',
})

function submitProfile() {
  profileForm.patch(route('profile.update'), { preserveScroll: true })
}

function submitPassword() {
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
    onError: () => {
      if (passwordForm.errors.password) {
        passwordForm.reset('password', 'password_confirmation')
        passwordInput.value?.focus()
      }
      if (passwordForm.errors.current_password) {
        passwordForm.reset('current_password')
        currentPasswordInput.value?.focus()
      }
    },
  })
}

function deleteAccount() {
  deleteForm.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeDeleteModal(),
    onError: () => deletePasswordInput.value?.focus(),
    onFinish: () => deleteForm.reset(),
  })
}

function closeDeleteModal() {
  confirmingDeletion.value = false
  deleteForm.clearErrors()
  deleteForm.reset()
}

watch(confirmingDeletion, async (open) => {
  if (open) {
    await nextTick()
    deletePasswordInput.value?.focus()
  }
})
</script>

<style scoped>
.input-field {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 4px;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: white;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-field:focus {
  border-color: rgba(6, 182, 212, 0.7);
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2);
}

.input-field.input-light {
  background: rgba(255, 255, 255, 0.95);
  border-color: rgba(0, 0, 0, 0.12);
  color: #0f172a;
}

.btn-primary {
  width: 100%;
  max-width: 220px;
  padding: 12px 16px;
  background: linear-gradient(to right, #06b6d4, #2563eb);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.15s, opacity 0.15s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-danger {
  width: 100%;
  max-width: 260px;
  padding: 12px 16px;
  background: linear-gradient(to right, #ef4444, #b91c1c);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.15s, opacity 0.15s;
}

.btn-danger:hover:not(:disabled) {
  transform: translateY(-1px);
}
</style>
