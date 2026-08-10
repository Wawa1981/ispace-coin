<template>
  <section id="news" class="mt-8">
    <div class="max-w-5xl mx-auto px-4 relative">
      <h2 class="text-xl md:text-2xl font-bold mb-3 text-center">📰 Actualités Crypto</h2>

      <div v-if="loading" class="text-center p-4">Chargement des actualités...</div>
      <div v-else-if="error" class="text-center p-4 text-red-500">Erreur : {{ error }}</div>

      <template v-else>
        <button @click="scrollNews(-1)"
          class="absolute left-0 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center shadow hover:bg-black/60 transition z-10">
          ‹
        </button>

        <div ref="newsContainer" class="flex gap-3 overflow-hidden scroll-smooth justify-center">
          <div v-for="n in visibleNews" :key="n.id"
            class="w-[180px] h-[220px] bg-white/5 backdrop-blur-md rounded-xl shadow ring-1 ring-white/10 flex flex-col overflow-hidden hover:scale-105 transition">
            <img :src="getNewsImageUrl(n.imageurl)"
              alt="news image" class="w-full h-24 object-cover" />
            <div class="flex-1 flex flex-col justify-between p-2">
              <h3 class="text-xs font-semibold line-clamp-2">{{ n.title }}</h3>
              <a :href="n.url" target="_blank" class="text-blue-400 text-xs font-medium mt-auto text-right">→</a>
            </div>
          </div>
        </div>

        <button @click="scrollNews(1)"
          class="absolute right-0 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center shadow hover:bg-black/60 transition z-10">
          ›
        </button>
      </template>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useNews } from '@/composables/useNews'; // ⭐️ Importe le composable

const newsContainer = ref(null);
const currentIndex = ref(0);

// ⭐️ Utilise le composable pour récupérer les données et l'état de chargement
const { news, loading, error } = useNews();

const visibleNews = computed(() => {
  if (!news.value.length) return [];
  const start = currentIndex.value;
  return Array.from({ length: 6 }, (_, i) => news.value[(start + i) % news.value.length]);
});

function scrollNews(direction) {
  if (!news.value.length) return;
  currentIndex.value = (currentIndex.value + direction + news.value.length) % news.value.length;
}

function getNewsImageUrl(url) {
  if (url && !url.startsWith('http')) {
    return 'https://www.cryptocompare.com' + url;
  }
  return url;
}
</script>
