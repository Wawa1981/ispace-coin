<template>
  <div :class="rootClass">
    <button @click="cycleDarkTheme"
      class="theme-orb w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
      title="Changer le style du thème sombre" aria-label="Thème sombre">
      🌍
    </button>
    
    <button @click="toggleLight"
      class="theme-orb w-12 h-12 rounded-full bg-gray backdrop-blur-md shadow-lg text-xl text-black flex items-center justify-center hover:scale-110 transition"
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
  inline: {
    type: Boolean,
    default: false,
  },
})

const rootClass = computed(() => {
  if (props.inline) return 'relative flex gap-3'
  return [props.isFixed ? 'fixed' : 'absolute', 'top-4 right-4 z-50 flex gap-3']
})
</script>

<style scoped>
@media (max-width: 767px) {
  .theme-orb {
    width: 2rem;
    height: 2rem;
    background: transparent;
    box-shadow: none;
    border: none;
    font-size: 1.15rem;
    color: inherit;
  }
  .theme-orb:hover {
    transform: none;
  }
}
</style>
