// resources/js/composables/useAppearance.ts
import { ref, computed, watch, onMounted } from 'vue'

/* ========= Types ========= */
export type Appearance = 'light' | 'dark' | 'system'

/* ========= State global (singleton) ========= */
const appearance = ref<Appearance>('system') // clair/sombre/système
const darkVariant = ref<number>(1)           // 0=violet, 1=bleu (par défaut), 2=fuchsia

const darkThemes = [
  'bg-gradient-to-br from-indigo-900 via-purple-900 to-black', // 0
  'bg-gradient-to-b from-blue-950 via-slate-900 to-black',     // 1 (défaut)
  'bg-gradient-to-tr from-fuchsia-900 via-purple-900 to-black' // 2
] as const

/* ========= Persistance ========= */
const STORAGE_KEY = 'cbw_appearance_v1'
const VARIANT_KEY = 'cbw_dark_variant_v1'

const setCookie = (name: string, value: string, days = 365) => {
  if (typeof document === 'undefined') return
  const maxAge = days * 24 * 60 * 60
  document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`
}

const mediaQuery = () => {
  if (typeof window === 'undefined') return null
  return window.matchMedia('(prefers-color-scheme: dark)')
}

/* ========= Application du thème sur <html> ========= */
function applyTheme(a: Appearance) {
  if (typeof document === 'undefined' || typeof window === 'undefined') return

  if (a === 'system') {
    const mql = mediaQuery()
    const systemDark = !!mql?.matches
    document.documentElement.classList.toggle('dark', systemDark)
  } else {
    document.documentElement.classList.toggle('dark', a === 'dark')
  }
}

/* ========= Initialisation (à appeler au boot) ========= */
export function initializeTheme() {
  if (typeof window === 'undefined') return

  try {
    const savedA = (localStorage.getItem(STORAGE_KEY) as Appearance | null) ?? 'system'
    const savedV = Number(localStorage.getItem(VARIANT_KEY) ?? 1)

    if (savedA === 'light' || savedA === 'dark' || savedA === 'system') {
      appearance.value = savedA
    }
    if (!Number.isNaN(savedV)) {
      darkVariant.value = Math.min(Math.max(savedV, 0), darkThemes.length - 1)
    }
  } catch {
    /* noop */
  }

  applyTheme(appearance.value)

  // Suivre les changements de thème système si on est en "system"
  mediaQuery()?.addEventListener('change', () => {
    if (appearance.value === 'system') applyTheme('system')
  })
}

/* ========= Classes Tailwind prêtes à l’emploi ========= */
const isLight = computed(() => {
  if (appearance.value === 'system') {
    // on déduit via media query
    if (typeof window === 'undefined') return false
    return !mediaQuery()?.matches
  }
  return appearance.value === 'light'
})

export const bgClass = computed(() => (isLight.value ? 'bg-white' : darkThemes[darkVariant.value]))
export const textClass = computed(() => (isLight.value ? 'text-black' : 'text-white'))
export const bodyTextClass = computed(() => (isLight.value ? 'text-gray-800' : 'text-gray-200'))
export const btnBorderClass = computed(() => (isLight.value ? 'border-black/20' : 'border-white'))
export const socialBtnClass = computed(() => (isLight.value ? 'border-black/20 hover:bg-black/5' : 'border-white/30 hover:bg-white/10'))
export const titleClass = computed(() => (isLight.value ? 'title-gradient' : 'neon-text-night'))

/* ========= Actions ========= */
export function updateAppearance(value: Appearance) {
  appearance.value = value
  try {
    localStorage.setItem(STORAGE_KEY, value)
    setCookie('appearance', value)
  } catch { /* noop */ }
  applyTheme(value)
}

export function cycleDarkTheme() {
  // ne change la variante que si on est visuellement en sombre
  const currentlyLight = isLight.value
  if (!currentlyLight) {
    darkVariant.value = (darkVariant.value + 1) % darkThemes.length
    try { localStorage.setItem(VARIANT_KEY, String(darkVariant.value)) } catch { /* noop */ }
  }
}

/* ========= Hook composable ========= */
export function useAppearance() {
  // si on entre dans un composant monté côté client, on synchronise depuis le storage
  onMounted(() => {
    try {
      const savedA = (localStorage.getItem(STORAGE_KEY) as Appearance | null)
      if (savedA === 'light' || savedA === 'dark' || savedA === 'system') appearance.value = savedA
      const savedV = Number(localStorage.getItem(VARIANT_KEY) ?? darkVariant.value)
      if (!Number.isNaN(savedV)) darkVariant.value = Math.min(Math.max(savedV, 0), darkThemes.length - 1)
    } catch { /* noop */ }
  })

  // expose tout ce qu’il faut aux composants
  return {
    // état
    appearance, isLight, darkVariant,
    // classes
    bgClass, textClass, bodyTextClass, btnBorderClass, socialBtnClass, titleClass,
    // actions
    updateAppearance, cycleDarkTheme,
  }
}
