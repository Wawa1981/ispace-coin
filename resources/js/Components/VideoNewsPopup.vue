<template>
  <Teleport to="body">
    <Transition name="vnp">
      <div
        v-if="visible"
        ref="panelRef"
        class="vnp"
        :class="[
          isLight ? 'vnp--light' : 'vnp--dark',
          { 'is-dragging': dragging, 'is-resizing': resizing },
        ]"
        :style="panelStyle"
        role="dialog"
        aria-label="Actualité vidéo"
      >
        <div class="vnp__header" @pointerdown="onDragStart">
          <span
            class="vnp__badge"
            :class="{ 'vnp__badge--off': mode === 'live' && liveStatus === 'off' }"
          >
            {{
              mode === 'live'
                ? liveStatus === 'on'
                  ? '● LIVE'
                  : liveStatus === 'loading'
                    ? '… LIVE'
                    : '○ LIVE'
                : '▶ TV'
            }}
          </span>
          <img
            class="vnp__logo"
            :src="current.logo"
            :alt="current.title"
            draggable="false"
            @error="onLogoError"
          />
          <span
            class="vnp__label"
            :class="{ 'vnp__label--live': mode === 'live' }"
            title="Glisser pour déplacer"
          >{{ current.title }}</span>
          <button
            v-if="mode === 'live' && isMuted && (playerSrc || liveKind === 'hls')"
            type="button"
            class="vnp__sound"
            title="Activer le son"
            @pointerdown.stop
            @click.stop="enableSound"
          >
            🔊 Activer le son
          </button>
          <span class="vnp__drag-hint" title="Déplacer">⠿</span>
          <button
            type="button"
            class="vnp__close"
            aria-label="Fermer"
            @pointerdown.stop
            @click="close"
          >
            ×
          </button>
        </div>

        <div class="vnp__video">
          <div v-if="dragging || resizing" class="vnp__shield" />

          <!-- Lecteurs toujours montés (sous les overlays) -->
          <video
            v-show="mode === 'live' && liveKind === 'hls' && liveStatus === 'on'"
            ref="videoRef"
            class="vnp__iframe vnp__video-el"
            playsinline
            autoplay
            muted
            controls
          />

          <iframe
            v-if="playerSrc && (mode === 'latest' || (mode === 'live' && liveKind === 'youtube'))"
            ref="iframeRef"
            :key="'yt-' + playerKey"
            class="vnp__iframe"
            :src="playerSrc"
            title="Chaîne finance"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
            allowfullscreen
          />

          <!-- Overlays -->
          <div
            v-if="mode === 'live' && liveStatus === 'loading'"
            class="vnp__msg vnp__msg--overlay"
          >
            Recherche du direct…
          </div>

          <div
            v-else-if="mode === 'live' && liveStatus === 'off'"
            class="vnp__msg vnp__msg--overlay"
          >
            <p>Pas de direct en cours sur cette chaîne.</p>
            <button type="button" class="vnp__msg-btn" @click="setMode('latest')">
              Voir les dernières vidéos
            </button>
            <a
              v-if="current.sourceUrl"
              class="vnp__msg-btn vnp__msg-btn--link"
              :href="current.sourceUrl"
              target="_blank"
              rel="noopener noreferrer"
            >
              Site officiel
            </a>
          </div>
        </div>

        <div class="vnp__modes" @pointerdown.stop @click.stop>
          <button
            v-if="!current.liveDisabled"
            type="button"
            class="vnp__mode"
            :class="{ 'is-on': mode === 'live' }"
            @pointerdown.stop
            @click.stop.prevent="setMode('live')"
          >
            Live
          </button>
          <button
            type="button"
            class="vnp__mode"
            :class="{ 'is-on': mode === 'latest' || current.liveDisabled }"
            @pointerdown.stop
            @click.stop.prevent="setMode('latest')"
          >
            Dernières vidéos
          </button>
        </div>

        <div class="vnp__footer" @pointerdown.stop @click.stop>
          <div class="vnp__nav">
            <button type="button" class="vnp__chip" @pointerdown.stop @click.stop.prevent="prev">
              ‹
            </button>
            <span class="vnp__count">{{ index + 1 }}/{{ clips.length }}</span>
            <button type="button" class="vnp__chip" @pointerdown.stop @click.stop.prevent="next">
              ›
            </button>
          </div>
        </div>

        <div class="vnp__resize" title="Redimensionner" @pointerdown.stop="onResizeStart" />
      </div>
    </Transition>

    <button
      v-if="!visible"
      type="button"
      class="vnp-reopen"
      :class="isLight ? 'vnp-reopen--light' : 'vnp-reopen--dark'"
      @click="open"
    >
      ▶ TV Finance
    </button>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import Hls from 'hls.js'
import { financeChannels } from '@/data/financeChannels'

defineProps({
  isLight: { type: Boolean, default: false },
})

const MIN_W = 280
const MIN_H = 220
const MAX_W = () => Math.min(900, window.innerWidth - 16)
const MAX_H = () => Math.min(700, window.innerHeight - 16)

/** Chaînes TV finance — source unique: data/financeChannels.js */
const clips = financeChannels

const visible = ref(false)
const index = ref(0)
const mode = ref('live')
/** @type {import('vue').Ref<'idle'|'loading'|'on'|'off'>} */
const liveStatus = ref('idle')
/** 'youtube' | 'hls' | null */
const liveKind = ref(null)
const liveVideoId = ref(null)
/** true = muet + bouton visible. false = son ON (reste ON même en changeant de chaîne) */
const isMuted = ref(true)
/** URL réellement chargée dans l’iframe (pas un flag cosmétique) */
const playerSrc = ref('')
const playerKey = ref(0)

const pos = ref({ x: 0, y: 0 })
const size = ref({ w: 360, h: 320 })
const dragging = ref(false)
const resizing = ref(false)
const panelRef = ref(null)
const iframeRef = ref(null)
const videoRef = ref(null)

let dragOrigin = null
let resizeOrigin = null
let liveAbort = null
/** Incrémente à chaque resolveLive — ignore les réponses obsolètes */
let liveGen = 0
/** @type {import('hls.js').default | null} */
let hlsInstance = null
/** URL HLS en attente tant que le <video> n’est pas dans le DOM */
let pendingHlsUrl = null

const current = computed(() => clips[index.value] || clips[0])

const panelStyle = computed(() => ({
  left: `${pos.value.x}px`,
  top: `${pos.value.y}px`,
  width: `${size.value.w}px`,
  height: `${size.value.h}px`,
  right: 'auto',
  bottom: 'auto',
}))

function liveEmbedUrl(videoId, muted) {
  // cb= force reload (évite image figée du live précédent)
  const cb = Date.now()
  return (
    `https://www.youtube.com/embed/${encodeURIComponent(videoId)}` +
    `?autoplay=1&mute=${muted ? 1 : 0}&playsinline=1&rel=0&modestbranding=1&controls=1&fs=1&cb=${cb}`
  )
}

function playlistEmbedUrl(channelId) {
  const uploadsPlaylist = 'UU' + channelId.slice(2)
  // mute suit isMuted (pas de coupure forcée entre chaînes)
  return (
    `https://www.youtube.com/embed?listType=playlist&list=${uploadsPlaylist}` +
    `&autoplay=1&mute=${isMuted.value ? 1 : 0}&playsinline=1&rel=0&modestbranding=1&controls=1`
  )
}

/** Commande réelle YouTube dans l’iframe */
function ytCommand(func, args = []) {
  const win = iframeRef.value?.contentWindow
  if (!win) return false
  try {
    win.postMessage(JSON.stringify({ event: 'listening', id: 1 }), '*')
    win.postMessage(JSON.stringify({ event: 'command', func, args }), '*')
    return true
  } catch {
    return false
  }
}

function destroyHls() {
  if (hlsInstance) {
    try {
      hlsInstance.destroy()
    } catch {
      /* ignore */
    }
    hlsInstance = null
  }
  const v = videoRef.value
  if (v) {
    try {
      v.pause()
      v.removeAttribute('src')
      v.load()
    } catch {
      /* ignore */
    }
  }
}

function loadLive(videoId) {
  if (!videoId) return
  destroyHls()
  pendingHlsUrl = null
  liveKind.value = 'youtube'
  liveStatus.value = 'on'
  liveVideoId.value = videoId
  // Ne PAS forcer le mute : garder le choix son entre les chaînes
  playerKey.value += 1
  playerSrc.value = liveEmbedUrl(videoId, isMuted.value)
}

/**
 * Attache le flux HLS au <video> (doit être dans le DOM = popup visible).
 */
function attachHlsToVideo(url) {
  const video = videoRef.value
  if (!video || !url) return false

  destroyHls()
  video.muted = isMuted.value
  video.volume = isMuted.value ? 0 : 1
  video.playsInline = true

  if (Hls.isSupported()) {
    hlsInstance = new Hls({
      enableWorker: true,
      lowLatencyMode: true,
      xhrSetup(xhr) {
        // certains CDN regardent le referer
        try {
          xhr.withCredentials = false
        } catch {
          /* ignore */
        }
      },
    })
    hlsInstance.loadSource(url)
    hlsInstance.attachMedia(video)
    hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
      video.muted = isMuted.value
      video.volume = isMuted.value ? 0 : 1
      video.play().catch((err) => console.warn('HLS play:', err))
    })
    hlsInstance.on(Hls.Events.ERROR, (_e, data) => {
      if (!data?.fatal) return
      console.warn('HLS fatal:', data.type, data.details)
      destroyHls()
      pendingHlsUrl = null
      liveKind.value = null
      // 403 / geo / réseau → fallback YouTube (sans quitter le mode Live)
      fallbackFromOfficialLive(liveGen)
    })
    return true
  }

  if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari natif
    video.src = url
    video.addEventListener(
      'loadedmetadata',
      () => {
        video.muted = isMuted.value
        video.play().catch(() => {})
      },
      { once: true },
    )
    return true
  }

  return false
}

async function loadOfficialHls(url) {
  pendingHlsUrl = url
  liveKind.value = 'hls'
  liveVideoId.value = null
  playerSrc.value = ''
  liveStatus.value = 'on'

  // Popup fermée ? On attend open() / visible pour attacher
  if (!visible.value) return

  await nextTick()
  // 1–2 frames pour que v-show monte le media
  await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)))

  if (!attachHlsToVideo(url)) {
    // ref absente : retenter un peu
    for (let i = 0; i < 15; i++) {
      await new Promise((r) => setTimeout(r, 80))
      if (!visible.value || pendingHlsUrl !== url) return
      if (attachHlsToVideo(url)) return
    }
    console.warn('HLS: impossible d’attacher le lecteur')
    liveStatus.value = 'off'
    liveKind.value = null
    pendingHlsUrl = null
  }
}

function loadPlaylist() {
  destroyHls()
  liveKind.value = null
  liveVideoId.value = null
  const c = current.value
  if (!c?.channelId) {
    playerSrc.value = ''
    return
  }
  playerKey.value += 1
  playerSrc.value = playlistEmbedUrl(c.channelId)
}

/**
 * Activer le son : recharge l’embed mute=0 (geste user).
 */
function enableSound() {
  isMuted.value = false
  if (liveKind.value === 'hls') {
    const video = videoRef.value
    if (video) {
      video.muted = false
      video.volume = 1
      video.play().catch(() => {})
    }
    return
  }
  const vid = liveVideoId.value
  if (!vid) return
  playerKey.value += 1
  playerSrc.value = liveEmbedUrl(vid, false)
}

/**
 * Après échec du flux officiel (geo 403, etc.) :
 * YouTube live → sinon message off (SANS quitter le mode Live).
 */
async function fallbackFromOfficialLive(gen) {
  if (gen !== liveGen) return
  const c = current.value
  if (c?.channelId || c?.altChannelIds?.length) {
    const ok = await resolveYoutubeLive(gen, c.channelId, c.altChannelIds || [])
    if (ok || gen !== liveGen) return
  }
  if (gen !== liveGen) return
  liveStatus.value = 'off'
  liveKind.value = null
  playerSrc.value = ''
}

/**
 * @returns {Promise<boolean>} true si un live YouTube a été trouvé
 */
async function resolveYoutubeLive(gen, channelId, altIds = []) {
  if (gen !== liveGen) return false

  liveAbort?.abort()
  liveAbort = new AbortController()
  const signal = liveAbort.signal

  const ids = [channelId, ...(Array.isArray(altIds) ? altIds : [])].filter(Boolean)
  const unique = [...new Set(ids)]

  // Timeout global : ne jamais rester bloqué sur « Recherche du direct… »
  const timeoutMs = 8000
  const timeoutPromise = new Promise((resolve) => {
    setTimeout(() => resolve({ __timeout: true }), timeoutMs)
  })

  try {
    for (const id of unique) {
      if (gen !== liveGen) return false
      const fetchPromise = fetch(`/api/tv/live?channelId=${encodeURIComponent(id)}`, {
        cache: 'no-store',
        signal,
      }).then(async (res) => {
        if (!res.ok) return null
        return res.json()
      })

      const json = await Promise.race([fetchPromise, timeoutPromise])
      if (json?.__timeout) {
        console.warn('TV live resolve timeout', id)
        break
      }
      if (gen !== liveGen) return false
      if (json?.live && json?.videoId) {
        loadLive(json.videoId)
        return true
      }
    }
    if (gen !== liveGen) return false
    liveVideoId.value = null
    playerSrc.value = ''
    liveKind.value = null
    return false
  } catch (e) {
    if (e?.name === 'AbortError') return false
    console.warn('TV live resolve:', e)
    if (gen !== liveGen) return false
    liveVideoId.value = null
    playerSrc.value = ''
    liveKind.value = null
    return false
  }
}

async function resolveLive() {
  const c = current.value
  const gen = ++liveGen

  liveAbort?.abort()
  destroyHls()
  pendingHlsUrl = null
  liveStatus.value = 'loading'
  liveVideoId.value = null
  playerSrc.value = ''
  liveKind.value = null
  // NE PAS reset isMuted

  if (!c) {
    liveStatus.value = 'off'
    return
  }

  // 1) HLS libre (BFM) en premier
  if (c.hlsUrl && !c.secureLive) {
    await loadOfficialHls(c.hlsUrl)
    if (gen !== liveGen) return
    // Si attach OK → liveKind=hls + status=on. Erreurs fatales → fallback async.
    if (liveStatus.value === 'on' && liveKind.value === 'hls') return
    // Attach raté → YouTube
  }

  // 2) YouTube live
  if (c.channelId || c.altChannelIds?.length) {
    const ok = await resolveYoutubeLive(gen, c.channelId, c.altChannelIds || [])
    if (gen !== liveGen) return
    if (ok) return
  }

  if (gen !== liveGen) return

  // 3) Chaînes type Kitco : souvent hors live → dernières vidéos auto (contenu dispo)
  if (c.preferLatestWhenOffline && c.channelId) {
    mode.value = 'latest'
    liveStatus.value = 'idle'
    liveKind.value = null
    loadPlaylist()
    return
  }

  // 4) Rien en live → rester en mode Live, écran off + actions
  liveStatus.value = 'off'
  liveKind.value = null
  playerSrc.value = ''
}

function setMode(m) {
  // Chaînes sans Live (Fox / BNN / Kitco) → toujours dernières vidéos
  if (current.value?.liveDisabled) {
    m = 'latest'
  }
  mode.value = m
  if (m === 'live') {
    destroyHls()
    pendingHlsUrl = null
    resolveLive()
  } else {
    liveGen += 1
    liveAbort?.abort()
    liveStatus.value = 'idle'
    liveKind.value = null
    pendingHlsUrl = null
    destroyHls()
    loadPlaylist()
  }
}

/** Au changement de chaîne / ouverture : Live si dispo, sinon playlist */
function applyChannelDefaultMode() {
  if (current.value?.liveDisabled) {
    setMode('latest')
  } else {
    setMode('live')
  }
}

function onLogoError(e) {
  if (e.target.dataset.fb === '1') return
  e.target.dataset.fb = '1'
  e.target.src = '/image/coins/default.svg'
}

watch(index, () => {
  // Chaînes sans Live (Fox / BNN / Kitco) → dernières vidéos uniquement
  if (current.value?.liveDisabled) {
    mode.value = 'latest'
    liveGen += 1
    liveAbort?.abort()
    liveStatus.value = 'idle'
    liveKind.value = null
    pendingHlsUrl = null
    destroyHls()
    loadPlaylist()
    return
  }
  // Toutes les autres chaînes : TOUJOURS Live par défaut
  // (sinon on reste coincé sur « Dernières vidéos » après Fox/BNN/Kitco)
  mode.value = 'live'
  resolveLive()
})

function clampPos(x, y, w, h) {
  const maxX = Math.max(8, window.innerWidth - w - 8)
  const maxY = Math.max(8, window.innerHeight - h - 8)
  return {
    x: Math.min(Math.max(8, x), maxX),
    y: Math.min(Math.max(8, y), maxY),
  }
}

function defaultPlacement() {
  const w = Math.min(360, window.innerWidth - 24)
  const h = 320
  size.value = { w, h }
  pos.value = clampPos(window.innerWidth - w - 16, 120, w, h)
}

function onDragStart(e) {
  // Ne pas capturer si clic sur un bouton du header (close / son)
  if (e.button !== 0) return
  if (e.target?.closest?.('button, a')) return
  // Seuil de drag : ne bloque pas les clics
  dragOrigin = {
    mx: e.clientX,
    my: e.clientY,
    x: pos.value.x,
    y: pos.value.y,
    armed: true,
    active: false,
  }
  e.currentTarget.setPointerCapture?.(e.pointerId)
}

function onResizeStart(e) {
  if (e.button !== 0) return
  resizing.value = true
  resizeOrigin = {
    mx: e.clientX,
    my: e.clientY,
    w: size.value.w,
    h: size.value.h,
  }
  e.currentTarget.setPointerCapture?.(e.pointerId)
}

function onPointerMove(e) {
  if (dragOrigin?.armed && !dragOrigin.active) {
    const dx = Math.abs(e.clientX - dragOrigin.mx)
    const dy = Math.abs(e.clientY - dragOrigin.my)
    if (dx > 4 || dy > 4) {
      dragOrigin.active = true
      dragging.value = true
    }
  }
  if (dragging.value && dragOrigin?.active) {
    const nx = dragOrigin.x + (e.clientX - dragOrigin.mx)
    const ny = dragOrigin.y + (e.clientY - dragOrigin.my)
    pos.value = clampPos(nx, ny, size.value.w, size.value.h)
  }
  if (resizing.value && resizeOrigin) {
    let nw = resizeOrigin.w + (e.clientX - resizeOrigin.mx)
    let nh = resizeOrigin.h + (e.clientY - resizeOrigin.my)
    nw = Math.min(MAX_W(), Math.max(MIN_W, nw))
    nh = Math.min(MAX_H(), Math.max(MIN_H, nh))
    size.value = { w: nw, h: nh }
    pos.value = clampPos(pos.value.x, pos.value.y, nw, nh)
  }
}

function onPointerUp() {
  dragging.value = false
  resizing.value = false
  dragOrigin = null
  resizeOrigin = null
}

async function open() {
  if (pos.value.x === 0 && pos.value.y === 0) defaultPlacement()
  visible.value = true
  await nextTick()
  // Toujours repartir sur le mode par défaut de la chaîne (Live sauf liveDisabled)
  applyChannelDefaultMode()
}
function close() {
  visible.value = false
  destroyHls()
  // garder pendingHlsUrl pour ré-attacher à la réouverture
}
function next() {
  index.value = (index.value + 1) % clips.length
  // watch(index) gère le mode Live / latest
}
function prev() {
  index.value = (index.value - 1 + clips.length) % clips.length
}

function onWinResize() {
  size.value = {
    w: Math.min(size.value.w, MAX_W()),
    h: Math.min(size.value.h, MAX_H()),
  }
  pos.value = clampPos(pos.value.x, pos.value.y, size.value.w, size.value.h)
}

onMounted(() => {
  defaultPlacement()
  window.addEventListener('pointermove', onPointerMove)
  window.addEventListener('pointerup', onPointerUp)
  window.addEventListener('resize', onWinResize)

  // Afficher la popup PUIS lancer le Live (mode par défaut)
  setTimeout(async () => {
    visible.value = true
    await nextTick()
    applyChannelDefaultMode()
  }, 600)
})

onUnmounted(() => {
  liveGen += 1
  liveAbort?.abort()
  destroyHls()
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', onPointerUp)
  window.removeEventListener('resize', onWinResize)
})
</script>

<style scoped>
.vnp {
  position: fixed;
  z-index: 80;
  display: flex;
  flex-direction: column;
  border-radius: 0.75rem;
  overflow: hidden;
  min-width: 280px;
  min-height: 220px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(12px);
  user-select: none;
}

.vnp.is-dragging,
.vnp.is-resizing {
  opacity: 0.96;
}

.vnp--dark {
  background: rgba(15, 23, 42, 0.95);
  box-shadow:
    0 12px 40px rgba(0, 0, 0, 0.5),
    inset 0 0 0 1px rgba(34, 211, 238, 0.25);
  color: #e2e8f0;
}

.vnp--light {
  background: rgba(255, 255, 255, 0.98);
  box-shadow:
    0 12px 40px rgba(15, 23, 42, 0.18),
    inset 0 0 0 1px rgba(15, 23, 42, 0.08);
  color: #0f172a;
}

.vnp__header {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.5rem;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: grab;
  flex-shrink: 0;
  touch-action: none;
}

.vnp.is-dragging .vnp__header {
  cursor: grabbing;
}

.vnp__badge {
  flex-shrink: 0;
  font-size: 0.6rem;
  padding: 0.15rem 0.4rem;
  border-radius: 9999px;
  background: #dc2626;
  color: #fff;
}
.vnp__badge--off {
  background: #64748b;
}

.vnp__logo {
  flex-shrink: 0;
  width: 1.35rem;
  height: 1.35rem;
  border-radius: 9999px;
  object-fit: cover;
  background: rgba(255, 255, 255, 0.12);
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.15);
  pointer-events: none;
}

.vnp__label {
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  opacity: 0.95;
  font-weight: 700;
}
.vnp__label--live {
  opacity: 0.75;
  font-weight: 600;
}

.vnp__drag-hint {
  opacity: 0.45;
  font-size: 0.85rem;
  letter-spacing: -0.1em;
  flex-shrink: 0;
}

.vnp__close {
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 0.35rem;
  font-size: 1.1rem;
  line-height: 1;
  opacity: 0.7;
  cursor: pointer;
}
.vnp__close:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.1);
}

.vnp__video {
  position: relative;
  flex: 1 1 auto;
  min-height: 140px;
  width: 100%;
  background: #000;
  overflow: hidden;
  isolation: isolate;
  z-index: 0;
}

.vnp__iframe {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
  background: #000;
  /* L’iframe YouTube ne doit pas voler les clics des boutons hors zone vidéo */
  z-index: 0;
}
.vnp__video-el {
  object-fit: contain;
}

.vnp__shield {
  position: absolute;
  inset: 0;
  z-index: 2;
  cursor: inherit;
}

.vnp__msg {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  padding: 1rem;
  text-align: center;
  font-size: 0.75rem;
  color: #cbd5e1;
  background: #0f172a;
}
.vnp__msg--overlay {
  z-index: 3;
}
.vnp__msg-btn {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.35rem 0.7rem;
  border-radius: 9999px;
  background: rgba(34, 211, 238, 0.2);
  border: 1px solid rgba(34, 211, 238, 0.45);
  color: #e2e8f0;
  cursor: pointer;
}
.vnp__msg-btn:hover {
  background: rgba(34, 211, 238, 0.35);
}
a.vnp__msg-btn--link {
  text-decoration: none;
  display: inline-block;
}

.vnp__sound {
  flex-shrink: 0;
  font-size: 0.58rem;
  font-weight: 800;
  padding: 0.18rem 0.45rem;
  border-radius: 9999px;
  cursor: pointer;
  border: 1px solid rgba(167, 243, 208, 0.55);
  background: rgba(22, 163, 74, 0.95);
  color: #f0fdf4;
  white-space: nowrap;
  line-height: 1.2;
}
.vnp__sound:hover {
  background: rgba(34, 197, 94, 0.98);
  filter: brightness(1.05);
}

.vnp__modes {
  display: flex;
  gap: 0.35rem;
  padding: 0.35rem 0.5rem 0;
  flex-shrink: 0;
  position: relative;
  z-index: 20;
  pointer-events: auto;
}

.vnp__mode {
  font-size: 0.6rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 9999px;
  opacity: 0.65;
  background: rgba(255, 255, 255, 0.06);
  cursor: pointer;
  border: 0;
  color: inherit;
  pointer-events: auto;
  position: relative;
  z-index: 21;
}
.vnp__mode.is-on {
  opacity: 1;
  background: rgba(34, 211, 238, 0.25);
  box-shadow: inset 0 0 0 1px rgba(34, 211, 238, 0.45);
}

.vnp__footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.45rem;
  padding: 0.4rem 0.55rem 0.55rem;
  font-size: 0.65rem;
  flex-shrink: 0;
  position: relative;
  z-index: 20;
  pointer-events: auto;
}

.vnp__nav {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.vnp__chip {
  width: 1.35rem;
  height: 1.35rem;
  border-radius: 0.3rem;
  background: rgba(255, 255, 255, 0.08);
  font-size: 0.85rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 0;
  color: inherit;
  pointer-events: auto;
  position: relative;
  z-index: 21;
}
.vnp__chip:hover {
  background: rgba(34, 211, 238, 0.25);
}

.vnp__count {
  opacity: 0.55;
  font-variant-numeric: tabular-nums;
  min-width: 2.2rem;
  text-align: center;
}

.vnp__resize {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 18px;
  height: 18px;
  cursor: nwse-resize;
  touch-action: none;
  z-index: 25;
  background: linear-gradient(135deg, transparent 50%, rgba(34, 211, 238, 0.75) 50%);
  border-bottom-right-radius: 0.75rem;
  opacity: 0.85;
}
.vnp__resize:hover {
  opacity: 1;
}

.vnp-reopen {
  position: fixed;
  right: 1rem;
  top: 7.5rem;
  z-index: 80;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.45rem 0.75rem;
  border-radius: 9999px;
  cursor: pointer;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.vnp-reopen--dark {
  background: rgba(15, 23, 42, 0.95);
  color: #e2e8f0;
  box-shadow:
    0 6px 20px rgba(0, 0, 0, 0.35),
    inset 0 0 0 1px rgba(220, 38, 38, 0.5);
}

.vnp-reopen--light {
  background: #fff;
  color: #0f172a;
}

.vnp-enter-active,
.vnp-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease;
}
.vnp-enter-from,
.vnp-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.96);
}
</style>
