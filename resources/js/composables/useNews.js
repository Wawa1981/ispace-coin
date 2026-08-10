// src/composables/useNews.js
import { ref, onMounted } from 'vue';

export function useNews() {
  const news = ref([]);
  const loading = ref(true);
  const error = ref(null);

  const fetchNews = async () => {
    loading.value = true;
    error.value = null;

    const apiKey = import.meta.env.VITE_CRYPTOCOMPARE_KEY;
    if (!apiKey) {
      error.value = "Clé API CryptoCompare manquante.";
      loading.value = false;
      return;
    }

    try {
      const res = await fetch(`https://min-api.cryptocompare.com/data/v2/news/?lang=FR&api_key=${apiKey}`);
      if (!res.ok) {
        throw new Error('Erreur de réseau ou API.');
      }
      const json = await res.json();
      news.value = json.Data;
    } catch (e) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  };

  onMounted(fetchNews);

  return { news, loading, error, fetchNews };
}
