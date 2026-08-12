// src/composables/useNews.js
import { ref, onMounted } from 'vue'

/**
 * Actus via /api/news (Laravel cache + secours RSS).
 * cardNews = FR, listNews = EN
 */
export function useNews() {
  const cardNews = ref([])
  const listNews = ref([])
  const news = cardNews
  const loading = ref(true)
  const error = ref(null)

  const fetchNews = async () => {
    loading.value = true
    error.value = null

    try {
      const [frRes, enRes] = await Promise.all([
        fetch('/api/news?lang=FR', { cache: 'no-store' }),
        fetch('/api/news?lang=EN', { cache: 'no-store' }),
      ])

      if (!frRes.ok && !enRes.ok) {
        throw new Error('Impossible de charger les actualités.')
      }

      const frJson = frRes.ok ? await frRes.json() : { articles: [] }
      const enJson = enRes.ok ? await enRes.json() : { articles: [] }

      const frList = asArray(frJson.articles)
      const enList = asArray(enJson.articles)

      const enImages = enList
        .map((a) => a?.imageurl)
        .filter((u) => u && !isPlaceholder(u))

      let imgI = 0

      cardNews.value = frList.map((a) => {
        let imageurl = a?.imageurl
        if (isPlaceholder(imageurl)) {
          imageurl = enImages.length
            ? enImages[imgI++ % enImages.length]
            : '/image/coins/default.svg'
        }
        return {
          ...a,
          imageurl,
          _resolvedImage: normalizeUrl(imageurl),
        }
      })

      listNews.value = enList.map((a) => {
        const imageurl = isPlaceholder(a?.imageurl)
          ? '/image/coins/default.svg'
          : a.imageurl
        return {
          ...a,
          imageurl,
          _resolvedImage: normalizeUrl(imageurl),
        }
      })

      // Si FR vide mais EN OK → cartes aussi en EN pour ne pas avoir un bandeau vide
      if (!cardNews.value.length && listNews.value.length) {
        cardNews.value = listNews.value.slice(0, 12).map((a) => ({ ...a }))
      }
      if (!listNews.value.length && cardNews.value.length) {
        listNews.value = cardNews.value.slice(0, 12).map((a) => ({ ...a }))
      }

      if (!cardNews.value.length && !listNews.value.length) {
        error.value = 'Aucune actualité disponible pour le moment.'
      }
    } catch (e) {
      error.value = e?.message || 'Erreur de chargement des actualités.'
      cardNews.value = []
      listNews.value = []
    } finally {
      loading.value = false
    }
  }

  onMounted(fetchNews)

  return { news, cardNews, listNews, loading, error, fetchNews }
}

function asArray(value) {
  if (Array.isArray(value)) return value
  if (value && typeof value === 'object') return Object.values(value)
  return []
}

function isPlaceholder(url) {
  if (!url) return true
  const u = String(url).toLowerCase()
  return u.includes('default.png') || u.includes('default.jpg')
}

function normalizeUrl(url) {
  if (!url) return '/image/coins/default.svg'
  let u = String(url).trim()
  // URLs RSS parfois encodées : https%3A%2F%2F...
  if (u.includes('%3A%2F%2F') || u.includes('%3a%2f%2f')) {
    try {
      u = decodeURIComponent(u)
    } catch (_) {
      /* ignore */
    }
  }
  if (u.startsWith('//')) return `https:${u}`
  if (u.startsWith('/image')) return u
  if (u.startsWith('http://') || u.startsWith('https://')) return u
  if (u.startsWith('/')) return `https://www.cryptocompare.com${u}`
  return u
}
