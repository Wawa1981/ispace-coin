<template>
  <aside
    :class="isSidebarOpen ? 'w-64' : 'w-24'"
    class="relative z-40 h-screen overflow-hidden transition-all duration-300 backdrop-blur-2xl ring-1 ring-white/10
           shadow-[0_10px_40px_rgba(0,0,0,0.35)] flex flex-col items-center pt-24"
    :aria-expanded="isSidebarOpen"
    :style="{ paddingTop: `calc(${tickerHeightPx} + 2rem)` }"
  >
    <div class="fixed top-6 left-6 z-50 flex items-center gap-4">
      <button @click="$emit('toggle')"
        class="p-3 rounded-full bg-white/10 backdrop-blur-md shadow-lg text-white/90 hover:bg-white/20 transition-all duration-300"
        :class="{ 'rotate-180': isSidebarOpen }"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
        </svg>
      </button>

      <!-- Bouton Profil -->
      <a href="/profile" 
        class="p-3 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition-all duration-300 flex items-center"
        :class="isSidebarOpen ? 'px-4' : ''"
        title="Profil"
      >
        <svg class="w-5 h-5" :class="isSidebarOpen ? 'mr-2' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span v-if="isSidebarOpen" class="font-bold">Profil</span>
      </a>
    </div>

    <!-- Logo -->
    <div class="mb-8 flex items-center justify-center">
      <div class="w-12 h-12 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zm0 0v10"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5"/>
        </svg>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-grow flex flex-col items-start px-2 py-4 gap-4 w-full">
      <a
        v-for="i in items"
        :key="i.route"
        :href="`/${i.route}`"
        :aria-label="i.label"
        :title="i.label"
        class="flex items-center gap-4 py-3 px-4 rounded-xl w-full text-sm font-semibold transition-transform duration-300 hover:bg-white/10"
        :class="isSidebarOpen ? '' : 'justify-center'"
      >
        <slot name="icon" :icon="i.icon" :isLight="isLight"></slot>
        <span :class="isSidebarOpen ? 'opacity-100' : 'opacity-0 absolute -z-10'" class="transition-opacity duration-200">
          {{ i.label }}
        </span>
      </a>
    </nav>
  </aside>
</template>

<script setup>
defineProps({
  isSidebarOpen: Boolean,
  isLight: Boolean,
  items: Array,
  tickerHeightPx: String
})

defineEmits(['toggle'])
</script>
