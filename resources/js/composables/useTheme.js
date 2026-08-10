import { ref, computed, watch } from 'vue'

const isLight = ref(false)
const darkIndex = ref(1) // bleu par défaut
const darkThemes = ['theme-indigo', 'theme-blue', 'theme-fuchsia']

const bgClass   = computed(() => (isLight.value ? 'bg-white' : darkThemes[darkIndex.value]))
const textClass = computed(() => (isLight.value ? 'text-black' : 'text-white'))
const bodyTextClass = computed(() => (isLight.value ? 'text-gray-800' : 'text-gray-200'))
const btnBorderClass = computed(() => (isLight.value ? 'border-black/20' : 'border-white'))
const socialBtnClass = computed(() => (isLight.value ? 'border-black/20 hover:bg-black/5' : 'border-white/30 hover:bg-white/10'))
const titleClass = computed(() => (isLight.value ? 'title-gradient' : 'neon-text-night'))

function toggleLight() { isLight.value = !isLight.value }
function cycleDarkTheme() { if (!isLight.value) darkIndex.value = (darkIndex.value + 1) % darkThemes.length }

// persistence
const KEY = 'cbw_theme_v1'
try {
  const saved = JSON.parse(localStorage.getItem(KEY) || 'null')
  if (saved) {
    isLight.value = !!saved.isLight
    const idx = Number(saved.darkIndex)
    if (!Number.isNaN(idx)) darkIndex.value = Math.min(Math.max(idx, 0), darkThemes.length - 1)
  }
} catch {}

watch([isLight, darkIndex], ([l, d]) => {
  localStorage.setItem(KEY, JSON.stringify({ isLight: l, darkIndex: d }))
})

export function useTheme() {
  return {
    // state
    isLight, darkIndex,
    // classes
    bgClass, textClass, bodyTextClass, btnBorderClass, socialBtnClass, titleClass,
    // actions
    toggleLight, cycleDarkTheme
  }
}
                                
