<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex">
    <Starfield />

    <Sidebar
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      :items="items"
      @toggle="toggleSidebar"
    >
        <template #icon="{ icon, isLight }">
            <SidebarIcon :icon="icon" :isLight="isLight" />
        </template>
    </Sidebar>

    <main class="relative z-10 w-full pb-16 transition-all duration-300 px-8">
        <ThemeSwitcher />
        <div class="flex flex-col items-center justify-start w-full min-h-[calc(100vh-10rem)] pt-20">
            <h1 :class="['text-3xl font-extrabold text-center mb-12', titleClass]">Cartes de paiement iSpaceCoin</h1>
            <div class="relative w-96 h-56 perspective mb-16" @mousemove="onCardMove" @mouseleave="resetCard">
                <div class="absolute inset-0 rounded-2xl shadow-2xl transform-style-3d transition-transform duration-300 overflow-hidden" :style="{ transform: `rotateY(${cardRotateY}deg) rotateX(${cardRotateX}deg)` }">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-700 p-6 flex flex-col justify-between text-white">
                        <div class="flex justify-between text-sm">
                            <span class="font-bold tracking-wide">iSpaceCoin</span>
                            <img src="/images/visa.png" alt="Visa" class="h-6" />
                        </div>
                        <div class="text-2xl font-mono tracking-widest">**** **** **** 1234</div>
                        <div class="flex justify-between text-sm">
                            <span>VALID THRU 12/27</span>
                            <span class="font-bold">BOUHADDI</span>
                        </div>
                    </div>
                </div>
            </div>
            <h2 class="text-xl font-bold mb-6">Nos partenaires</h2>
            <div class="w-full max-w-4xl grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="flex items-center justify-center p-4 bg-white/10 rounded-xl shadow hover:scale-105 transition">
                    <img src="/image/solaris.png" alt="Solaris" class="h-10 object-contain" />
                </div>
                <div class="flex items-center justify-center p-4 bg-white/10 rounded-xl shadow hover:scale-105 transition">
                    <img src="/image/stripe.png" alt="Stripe" class="h-10 object-contain" />
                </div>
                <div class="flex items-center justify-center p-4 bg-white/10 rounded-xl shadow hover:scale-105 transition">
                    <img src="/image/visa.png" alt="Visa" class="h-10 object-contain" />
                </div>
                <div class="flex items-center justify-center p-4 bg-white/10 rounded-xl shadow hover:scale-105 transition">
                    <img src="/image/mastercard.png" alt="Mastercard" class="h-10 object-contain" />
                </div>
            </div>
        </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

import Sidebar from '@/Components/Sidebar.vue'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue' // <-- Ajout de l'import

import { useTheme } from '@/composables/useTheme'

/* Sidebar */
const isSidebarOpen = ref(true)
function toggleSidebar() { isSidebarOpen.value = !isSidebarOpen.value }

/* Thème (utilisé via le composable) */
const { isLight, bgClass, textClass, titleClass } = useTheme()

/* Items */
const items = [
    { route: 'dashboard', label: 'Tableau de bord', icon: 'home' },
    { route: 'achat', label: 'Achat', icon: 'hex' },
    { route: 'retrait', label: 'Retrait', icon: 'orbit' },
    { route: 'deposit', label: 'Dépôt', icon: 'spark' },
    { route: 'envoyer', label: 'Envoyer', icon: 'stack' },
    { route: 'echange', label: 'Échange', icon: 'flow' },
    { route: 'compte', label: 'Compte', icon: 'id' },
    { route: 'cartes', label: 'Cartes', icon: 'cards' },
    { route: 'crypto', label: 'Marché', icon: 'pulse' },
]

/* Animation carte */
const cardRotateX = ref(0)
const cardRotateY = ref(0)
function onCardMove(e) {
    const rect = e.currentTarget.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top
    const centerX = rect.width / 2
    const centerY = rect.height / 2
    cardRotateY.value = ((x - centerX) / centerX) * 10
    cardRotateX.value = -((y - centerY) / centerY) * 10
}
function resetCard() {
    cardRotateX.value = 0
    cardRotateY.value = 0
}
</script>

<style scoped>
/* Les styles restent inchangés */
.perspective { perspective: 1000px; }
.transform-style-3d { transform-style: preserve-3d; }
.neon-text-night {
    text-shadow: 0 0 6px #fff, 0 0 12px #0ff, 0 0 24px #0ff, 0 0 48px #0ff, 0 0 96px #0ff;
}
.title-gradient {
    background: linear-gradient(90deg, #2563eb, #a855f7);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
</style>
