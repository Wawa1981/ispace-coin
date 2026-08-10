<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700">
    <Starfield />
    <ThemeSwitcher />

    <main class="relative z-10 w-full min-h-screen flex items-center justify-center px-4">
      <div class="w-full max-w-md rounded-2xl shadow-2xl/20 backdrop-blur-sm p-8"
        :class="[isLight ? 'bg-white/85 ring-1 ring-black/5' : 'bg-black/30 ring-1 ring-white/10']">

        <div class="flex justify-center mb-6">
          <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg"
            :class="isLight ? 'bg-gradient-to-r from-blue-600 to-purple-600' : 'bg-gradient-to-r from-cyan-400 to-blue-600'">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zm0 0v10" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5" />
            </svg>
          </div>
        </div>

        <h1 :class="['text-center text-3xl font-extrabold mb-8', titleClass]">Wallet CryptoBank</h1>

        <form class="space-y-6" @submit.prevent="login">
          <div class="space-y-4">
            <input v-model="form.email" type="email" placeholder="Email" required autocomplete="email"
              class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
              :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-blue-500' : 'bg-gray-800 text-white border-white/10 focus:ring-blue-400'"/>

            <input v-model="form.password" type="password" placeholder="Mot de passe" required autocomplete="current-password"
              class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
              :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-purple-500' : 'bg-gray-800 text-white border-white/10 focus:ring-purple-400'"/>
          </div>

          <div class="flex justify-between items-center text-sm" :class="bodyTextClass">
            <label class="inline-flex items-center gap-2 select-none">
              <input type="checkbox" v-model="form.remember" class="h-4 w-4 rounded border"
                :class="isLight ? 'border-black/20 text-blue-600' : 'border-white/30 text-blue-400'">
              <span>Se souvenir de moi</span>
            </label>
            <Link :href="route('password.request')" class="underline underline-offset-4 hover:opacity-80">
              Mot de passe oublié ?
            </Link>
          </div>

          <button type="submit"
            class="w-full py-3 rounded-lg font-semibold shadow-xl transition duration-300 bg-gradient-to-r hover:scale-[1.01]"
            :class="['from-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-600', btnBorderClass]">
            Connexion
          </button>

          <p class="text-center text-sm" :class="bodyTextClass">
            Pas encore de compte ?
            <Link :href="route('register')" class="underline underline-offset-4 hover:opacity-80">Créer un compte</Link>
          </p>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass, titleClass, bodyTextClass, btnBorderClass } = useTheme();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const login = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
.title-gradient {
  background: linear-gradient(90deg, #2563eb, #a855f7);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-shadow: 0 2px 6px rgba(0,0,0,0.08);
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
