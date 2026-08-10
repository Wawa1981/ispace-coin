<template>
  <div :class="[bgClass, textClass]" class="min-h-screen flex items-center justify-center px-4 transition-colors duration-500">
    <ThemeSwitcher />
    <Starfield />

    <div class="max-w-md w-full p-8 rounded-lg shadow-xl relative z-10"
      :class="isLight ? 'bg-white/90 ring-1 ring-black/5' : 'bg-black/40 ring-1 ring-white/10'">
      
      <h2 :class="['text-center text-2xl font-bold mb-4', titleClass]">Mot de passe oublié</h2>

      <p :class="['text-sm text-center mb-4', bodyTextClass]">
        Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
      </p>

      <div v-if="status" class="text-green-400 text-sm text-center font-medium mt-4">
        {{ status }}
      </div>
      
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <input
            v-model="form.email"
            type="email"
            placeholder="Adresse email"
            required
            class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
            :class="isLight 
              ? 'bg-gray-100 text-gray-900 border-black/10 focus:ring-blue-500' 
              : 'bg-gray-800 text-white border-white/10 focus:ring-cyan-500'"
          />
          <div class="text-sm text-red-400 mt-2" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>

        <button type="submit"
          :disabled="form.processing"
          class="w-full py-3 rounded-lg font-semibold shadow-xl transition duration-300 bg-gradient-to-r hover:scale-[1.01]"
          :class="['from-blue-600 to-purple-600 hover:from-purple-600 hover:to-blue-500', btnBorderClass]">
          Envoyer le lien de réinitialisation
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import Starfield from '@/Components/Starfield.vue'
import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass, titleClass, bodyTextClass, btnBorderClass } = useTheme()

defineProps({
  status: String,
})

const form = useForm({ email: '' })

const submit = () => {
  form.post(route('password.email'))
}
</script>
