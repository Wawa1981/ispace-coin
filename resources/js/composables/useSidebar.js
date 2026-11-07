import { ref, onMounted, onUnmounted } from 'vue'

/** Aligné sur Tailwind `md` */
export const SIDEBAR_MD = 768

export function isMobileViewport() {
  return typeof window !== 'undefined' && window.matchMedia(`(max-width: ${SIDEBAR_MD - 1}px)`).matches
}

/**
 * Ouverte sur desktop, fermée sous md.
 * Au passage du breakpoint : desktop = ouverte, mobile = fermée (drawer).
 */
export function useSidebar() {
  const startMobile = isMobileViewport()
  const isMobile = ref(startMobile)
  const isSidebarOpen = ref(!startMobile)

  function applyBreakpoint(mobile) {
    isMobile.value = mobile
    isSidebarOpen.value = !mobile
  }

  function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value
  }

  function closeSidebar() {
    isSidebarOpen.value = false
  }

  function onMqChange(e) {
    applyBreakpoint(e.matches)
  }

  let mq = null

  onMounted(() => {
    applyBreakpoint(isMobileViewport())
    mq = window.matchMedia(`(max-width: ${SIDEBAR_MD - 1}px)`)
    mq.addEventListener('change', onMqChange)
  })

  onUnmounted(() => {
    mq?.removeEventListener('change', onMqChange)
  })

  return { isSidebarOpen, isMobile, toggleSidebar, closeSidebar }
}
