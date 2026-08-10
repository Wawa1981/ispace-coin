<template>
  <div :class="[positionClass, 'top-4 right-4 z-50 flex gap-3']">
    <button @click="cycleDarkTheme"
      class="w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
      title="Changer le style du thème sombre" aria-label="Thème sombre">
      🌍
    </button>
    
    <button @click="toggleLight"
      class="w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
      :title="isLight ? 'Passer en Nuit' : 'Passer en Jour'"
      :aria-label="isLight ? 'Passer en Nuit' : 'Passer en Jour'">
      {{ isLight ? '🌖' : '🌕' }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { isLight, toggleLight, cycleDarkTheme } = useTheme()

const props = defineProps({
  isFixed: {
    type: Boolean,
    default: false,
  },
})

const positionClass = computed(() => {
  return props.isFixed ? 'fixed' : 'absolute'
})
</script>
