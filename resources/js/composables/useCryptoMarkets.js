import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useCryptoMarkets() {
  const markets = ref([])
  const loading = ref(true)
  const error = ref(null)
  const query = ref('')
  const isTableCollapsed = ref(true)

  const COINGECKO_API_URL = '/api/markets?vs=usd&per_page=100&page=1'

  async function loadMarkets() {
    loading.value = true
    error.value = null
    try {
      const res = await fetch(COINGECKO_API_URL, { cache: 'no-store' })
      if (!res.ok) {
        throw new Error(`Erreur API: ${res.status} ${res.statusText}`)
      }
      const data = await res.json()
      markets.value = Array.isArray(data) ? data : []
    } catch (e) {
      console.error('Erreur markets:', e)
      error.value = 'Erreur lors du chargement des données. Veuillez réessayer plus tard.'
    } finally {
      loading.value = false
    }
  }

  const filteredMarkets = computed(() => {
    const q = query.value.trim().toLowerCase()
    let list = markets.value
    if (q) {
      list = list.filter(c => (c.name || '').toLowerCase().includes(q) || (c.symbol || '').toLowerCase().includes(q))
    }
    return isTableCollapsed.value ? list.slice(0, 10) : list
  })

  function toggleTableCollapse() {
    isTableCollapsed.value = !isTableCollapsed.value
  }

  // Gère le chargement initial et le rafraîchissement
  let refreshInterval = null
  onMounted(() => {
    loadMarkets()
    refreshInterval = setInterval(loadMarkets, 180000) // 3 minutes
  })

  onUnmounted(() => {
    if (refreshInterval) {
      clearInterval(refreshInterval)
    }
  })

  return {
    // État
    markets,
    loading,
    error,
    query,
    isTableCollapsed,

    // Propriétés calculées
    filteredMarkets,

    // Actions
    toggleTableCollapse,
    loadMarkets
  }
}
