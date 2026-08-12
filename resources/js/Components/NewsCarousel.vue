<template>
  <section id="news" class="mt-6 mb-6">
    <div class="max-w-5xl mx-auto px-4">
      <div class="flex items-center justify-start gap-2 mb-3 flex-wrap">
        <h2 class="text-xl font-bold text-left">📰 Actualités Crypto</h2>
        <button
          type="button"
          class="markets-mini"
          @click="emit('toggle-markets')"
        >
          📈 Suivre les cours
        </button>
      </div>

      <div v-if="loading" class="text-left p-3 text-sm opacity-70">Chargement des actualités…</div>
      <div v-else-if="error" class="text-left p-3 text-sm text-red-400 flex items-center gap-3 flex-wrap">
        <span>{{ error }}</span>
        <button
          type="button"
          class="text-xs font-semibold underline opacity-90 hover:opacity-100"
          @click="fetchNews(1)"
        >
          Réessayer
        </button>
      </div>

      <template v-else>
        <!-- 6 cartes -->
        <div class="relative mb-4">
          <button type="button" class="nav left-0" @click="scrollCards(-1)">‹</button>
          <div class="flex gap-2 overflow-hidden justify-start">
            <a
              v-for="n in visibleCards"
              :key="'c' + n.id"
              :href="n.url"
              target="_blank"
              rel="noopener noreferrer"
              class="card"
            >
              <img
                :src="img(n)"
                alt=""
                class="w-full h-20 object-cover"
                loading="lazy"
                referrerpolicy="no-referrer"
                @error="onErr($event, n)"
              />
              <div class="p-1.5 flex-1 flex flex-col">
                <span class="text-[10px] font-semibold line-clamp-2 leading-snug">{{ n.title }}</span>
                <span class="text-blue-400 text-[10px] mt-auto text-right">→</span>
              </div>
            </a>
          </div>
          <button type="button" class="nav right-0" @click="scrollCards(1)">›</button>
        </div>

        <!-- 6 en long en dessous — un peu décalés à droite pour centrer avec la page -->
        <div class="space-y-1 max-w-3xl list-shift">
          <a
            v-for="n in visibleList"
            :key="'l' + n.id"
            :href="n.url"
            target="_blank"
            rel="noopener noreferrer"
            class="row"
          >
            <img
              :src="img(n)"
              alt=""
              class="row-img"
              loading="lazy"
              referrerpolicy="no-referrer"
              @error="onErr($event, n)"
            />
            <span class="text-[11px] font-medium line-clamp-2 flex-1 leading-snug">{{ n.title }}</span>
            <span class="text-cyan-400 text-xs shrink-0">→</span>
          </a>
        </div>
      </template>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useNews } from '@/composables/useNews'

const emit = defineEmits(['toggle-markets'])

const cardIndex = ref(0)
const broken = ref(new Set())
const { cardNews, listNews, loading, error, fetchNews } = useNews()

const visibleCards = computed(() => {
  const list = cardNews.value || []
  if (!list.length) return []
  return Array.from({ length: Math.min(6, list.length) }, (_, i) =>
    list[(cardIndex.value + i) % list.length],
  )
})

const visibleList = computed(() => (listNews.value || []).slice(0, 6))

function scrollCards(d) {
  const list = cardNews.value || []
  if (!list.length) return
  cardIndex.value = (cardIndex.value + d + list.length) % list.length
}

const NEUTRAL = '/image/coins/default.svg'

function img(n) {
  if (broken.value.has(n.id)) return NEUTRAL
  return n._resolvedImage || n.imageurl || NEUTRAL
}

function onErr(e, n) {
  if (broken.value.has(n.id)) return
  broken.value = new Set(broken.value).add(n.id)
  // Placeholder neutre — jamais le logo d’une autre crypto
  if (e?.target) e.target.src = NEUTRAL
}
</script>

<style scoped>
.nav {
  position: absolute;
  top: 40%;
  z-index: 10;
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 9999px;
  background: rgba(0, 0, 0, 0.4);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  line-height: 1;
}
.nav.left-0 { left: 0; }
.nav.right-0 { right: 0; }
.nav:hover { background: rgba(0, 0, 0, 0.6); }

.card {
  width: 150px;
  height: 175px;
  flex-shrink: 0;
  border-radius: 0.65rem;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  transition: transform 0.15s;
}
.card:hover { transform: scale(1.04); }

.row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.5rem;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.04);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
  transition: background 0.15s;
}
.row:hover { background: rgba(255, 255, 255, 0.08); }

.row-img {
  width: 2.75rem;
  height: 2rem;
  border-radius: 0.3rem;
  object-fit: cover;
  flex-shrink: 0;
}

.list-shift {
  margin-left: auto;
  margin-right: auto;
}

.markets-mini {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.3rem 0.6rem;
  border-radius: 9999px;
  white-space: nowrap;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: inset 0 0 0 1px rgba(34, 211, 238, 0.4);
  color: inherit;
  cursor: pointer;
  transition: background 0.15s, transform 0.15s;
  flex-shrink: 0;
}
.markets-mini:hover {
  background: rgba(34, 211, 238, 0.18);
  transform: scale(1.03);
}
</style>
