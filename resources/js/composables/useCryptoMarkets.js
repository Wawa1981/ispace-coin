import { ref, computed, onMounted, onUnmounted } from 'vue'

/**
 * Marchés crypto avec:
 * - pas de wipe des données si un refresh échoue
 * - loading uniquement au tout premier chargement (pas de data)
 * - lecture localStorage last-good immédiate (pas d'écran "chargement" inutile)
 * - refresh silencieux en arrière-plan
 */
export function useCryptoMarkets() {
  const STORAGE_KEY = 'ispace:markets:last_good'
  const markets = ref(readLocalMarkets())
  const loading = ref(markets.value.length === 0)
  const error = ref(null)
  const stale = ref(false)
  const source = ref(null)
  const query = ref('')
  const isTableCollapsed = ref(true)

  const MARKETS_URL = '/api/markets?vs=usd&per_page=100&page=1'

  function readLocalMarkets() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      if (!raw) return []
      const parsed = JSON.parse(raw)
      return Array.isArray(parsed?.data) ? parsed.data : []
    } catch {
      return []
    }
  }

  function persistLocal(list) {
    try {
      if (Array.isArray(list) && list.length > 0) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ data: list, at: Date.now() }))
      }
    } catch {
      /* quota / private mode */
    }
  }

  async function loadMarkets({ silent = false } = {}) {
    // loading seulement si on n'a encore rien à afficher
    if (!silent && markets.value.length === 0) {
      loading.value = true
    }
    // ne pas effacer error pendant un refresh silencieux si on a déjà des données
    if (!silent) {
      error.value = null
    }

    try {
      const res = await fetch(MARKETS_URL, { cache: 'no-store' })
      if (!res.ok) {
        throw new Error(`Erreur API: ${res.status} ${res.statusText}`)
      }
      const data = await res.json()
      const list = Array.isArray(data) ? data : []

      if (list.length > 0) {
        markets.value = list
        persistLocal(list)
        stale.value = res.headers.get('X-Price-Stale') === '1'
        source.value = res.headers.get('X-Price-Source') || null
        error.value = null
      } else if (markets.value.length === 0) {
        // vraiment rien nulle part
        error.value = 'Aucune donnée de marché disponible pour le moment.'
      } else {
        // refresh a renvoyé vide mais on garde l'ancien
        stale.value = true
      }
    } catch (e) {
      console.error('Erreur markets:', e)
      // On garde les anciens prix — message discret seulement si écran vraiment vide
      if (markets.value.length === 0) {
        error.value = 'Erreur lors du chargement des données. Nouvelle tentative…'
      } else {
        stale.value = true
      }
    } finally {
      loading.value = false
    }
  }

  const filteredMarkets = computed(() => {
    const q = query.value.trim().toLowerCase()
    let list = markets.value
    if (q) {
      list = list.filter(
        (c) =>
          (c.name || '').toLowerCase().includes(q) ||
          (c.symbol || '').toLowerCase().includes(q)
      )
    }
    return isTableCollapsed.value ? list.slice(0, 10) : list
  })

  function toggleTableCollapse() {
    isTableCollapsed.value = !isTableCollapsed.value
  }

  let refreshInterval = null
  onMounted(() => {
    // silent si on a déjà du last-good local
    loadMarkets({ silent: markets.value.length > 0 })
    refreshInterval = setInterval(() => loadMarkets({ silent: true }), 180000)
  })

  onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval)
  })

  return {
    markets,
    loading,
    error,
    stale,
    source,
    query,
    isTableCollapsed,
    filteredMarkets,
    toggleTableCollapse,
    loadMarkets,
  }
}
