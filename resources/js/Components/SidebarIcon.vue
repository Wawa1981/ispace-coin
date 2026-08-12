<template>
  <svg
    class="sb-icon"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.65"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
  >
    <!-- Accueil -->
    <template v-if="icon === 'home'">
      <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z" />
    </template>

    <!-- Achat / hex -->
    <template v-else-if="icon === 'hex'">
      <path d="M12 3.2 19.2 7.4v9.2L12 20.8 4.8 16.6V7.4L12 3.2z" />
      <path d="M12 8v8M9 10.5 12 12l3-1.5" />
    </template>

    <!-- Retrait / orbit -->
    <template v-else-if="icon === 'orbit'">
      <circle cx="12" cy="12" r="3.2" />
      <ellipse cx="12" cy="12" rx="9" ry="4.2" transform="rotate(-24 12 12)" />
      <path d="M16.2 8.2 18 6.5M7.8 15.8 6 17.5" />
    </template>

    <!-- Dépôt / spark -->
    <template v-else-if="icon === 'spark'">
      <path d="M12 3v7M12 14v7M5 8l3.2 2.2M15.8 13.8 19 16M5 16l3.2-2.2M15.8 10.2 19 8" />
      <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none" />
    </template>

    <!-- Envoyer / stack -->
    <template v-else-if="icon === 'stack'">
      <path d="M4 14.5 12 18.5l8-4" />
      <path d="M4 10.5 12 14.5l8-4" />
      <path d="M4 6.5 12 10.5l8-4L12 2.5 4 6.5z" />
    </template>

    <!-- Échange / flow -->
    <template v-else-if="icon === 'flow'">
      <path d="M7 7h10l-2.5-2.5" />
      <path d="M17 17H7l2.5 2.5" />
      <path d="M7 7v4a4 4 0 0 0 4 4h6" />
      <path d="M17 17v-4a4 4 0 0 0-4-4H7" />
    </template>

    <!-- Compte / id -->
    <template v-else-if="icon === 'id'">
      <rect x="3.5" y="5" width="17" height="14" rx="2.2" />
      <circle cx="9" cy="12" r="2.1" />
      <path d="M13.2 10.2h4.3M13.2 13.8h3.2" />
    </template>

    <!-- Cartes -->
    <template v-else-if="icon === 'cards'">
      <rect x="3.5" y="7" width="14" height="10" rx="1.8" />
      <path d="M3.5 11h14" />
      <path d="M6.5 14.2h3.2" />
      <path d="M8.5 5.5h10.2a1.8 1.8 0 0 1 1.8 1.8V15" />
    </template>

    <!-- Marché / pulse -->
    <template v-else-if="icon === 'pulse'">
      <path d="M3 12h3.2l2-5 3.2 10 2.4-6.5H21" />
    </template>

    <!-- fallback -->
    <template v-else>
      <circle cx="12" cy="12" r="8" />
      <path d="M12 8v4l2.5 2.5" />
    </template>
  </svg>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true,
  },
  isLight: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.sb-icon {
  width: 1.15rem;
  height: 1.15rem;
  display: block;
  flex-shrink: 0;
}
</style>
