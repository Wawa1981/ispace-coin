<template>
  <video
    ref="el"
    class="brand-logo"
    :class="mediaClass"
    src="/logo-isc.mp4"
    autoplay
    muted
    loop
    playsinline
    preload="metadata"
    :aria-hidden="alt ? undefined : true"
    :aria-label="alt || undefined"
    @loadeddata="forceMute"
    @play="forceMute"
    @volumechange="forceMute"
  />
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  /** classes de taille / style du parent (ex. footer-logo-media, auth__logo-media) */
  mediaClass: {
    type: [String, Array, Object],
    default: '',
  },
  alt: {
    type: String,
    default: '',
  },
})

const el = ref(null)

function forceMute() {
  const v = el.value
  if (!v) return
  v.muted = true
  v.defaultMuted = true
  v.volume = 0
}
</script>

<style scoped>
.brand-logo {
  display: block;
  object-fit: cover;
  flex-shrink: 0;
}
</style>
