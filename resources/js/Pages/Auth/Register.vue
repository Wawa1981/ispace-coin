<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex items-center justify-center px-4">
    
    <Starfield />
    <ThemeSwitcher />

    <div class="max-w-md w-full space-y-8 relative z-10 p-8 rounded-2xl shadow-2xl/20 backdrop-blur-sm"
      :class="isLight ? 'bg-white/85 ring-1 ring-black/5' : 'bg-black/30 ring-1 ring-white/10'">
      
      <div class="flex justify-center">
        <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg animate-pulse"
          :class="isLight ? 'bg-gradient-to-r from-blue-600 to-purple-600' : 'bg-gradient-to-r from-cyan-400 to-blue-600'">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zm0 0v10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5" />
          </svg>
        </div>
      </div>

      <h2 :class="['text-center text-3xl font-extrabold', titleClass, 'neon-text']">Register to CryptoBank</h2>
      
      <form @submit.prevent="submit" class="mt-8 space-y-6">
        <div class="space-y-4">
          <input v-model="form.name" type="text" placeholder="Name" required
            class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
            :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-pink-500' : 'bg-gray-800 text-white border-white/10 focus:ring-pink-500'"/>

          <input v-model="form.email" type="email" placeholder="Email" required
            class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
            :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-cyan-500' : 'bg-gray-800 text-white border-white/10 focus:ring-cyan-500'"/>

          <input v-model="form.password" type="password" placeholder="Password" required
            class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
            :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-indigo-500' : 'bg-gray-800 text-white border-white/10 focus:ring-indigo-500'"/>

          <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required
            class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
            :class="isLight ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-indigo-500' : 'bg-gray-800 text-white border-white/10 focus:ring-indigo-500'"/>
        </div>

        <div class="flex items-center justify-between text-sm mt-4" :class="bodyTextClass">
          <Link :href="route('login')" class="underline underline-offset-4 hover:opacity-80">Already registered?</Link>
        </div>

        <button type="submit"
          class="w-full py-3 rounded-lg font-semibold shadow-xl transition duration-300 bg-gradient-to-r hover:scale-[1.01]"
          :class="['from-blue-600 to-purple-600 hover:from-purple-600 hover:to-blue-500', btnBorderClass]">
          Register
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass, titleClass, bodyTextClass, btnBorderClass } = useTheme()

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<style scoped>
.neon-text {
  text-shadow: 0 0 5px #fff, 0 0 10px #0ff, 0 0 20px #0ff, 0 0 40px #0ff;
}
</style>

