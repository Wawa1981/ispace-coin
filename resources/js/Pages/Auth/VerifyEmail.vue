<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ status: String })
const form = useForm({})
const submit = () => form.post(route('verification.send'))
const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

// étoiles
const canvasRef = ref(null)
onMounted(() => {
  const c = canvasRef.value
  const ctx = c.getContext('2d', { alpha: true })
  let W = innerWidth, H = innerHeight, run = true
  const resize = () => { W = innerWidth; H = innerHeight; c.width = W; c.height = H }
  resize()

  const N = 180
  const stars = Array.from({ length: N }, () => ({
    x: Math.random()*W, y: Math.random()*H,
    r: 0.6 + Math.random()*1.8,
    f: 0.6 + Math.random()*1.4, p: Math.random()*Math.PI*2
  }))

  const draw = (t=0) => {
    if (!run) return
    ctx.clearRect(0,0,W,H)
    ctx.fillStyle = '#fff'
    for (const s of stars) {
      const a = 0.4 + 0.6*Math.sin(t*0.002*s.f + s.p)
      ctx.globalAlpha = a
      ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI*2); ctx.fill()
    }
    requestAnimationFrame(draw)
  }
  draw()

  addEventListener('resize', resize)
  onUnmounted(()=>{ run=false; removeEventListener('resize', resize) })
})
</script>

<template>
  <Head title="Vérification de l’e-mail" />

  <!-- FOND BLEU + ÉTOILES -->
  <div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-blue-950 via-slate-900 to-black"></div>
    <canvas ref="canvasRef" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
  </div>

  <!-- CONTENU -->
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-white">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <div class="w-14 h-14 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zm0 0v10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5" />
          </svg>
        </div>
      </div>

      <!-- Carte sombre translucide -->
      <div class="bg-white/5 backdrop-blur-md rounded-2xl ring-1 ring-white/10 p-6 shadow-2xl">
        <div class="mb-4 text-sm leading-relaxed">
          Merci pour votre inscription ! Avant de commencer, vérifiez votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.
          Si vous ne l’avez pas reçu, nous pouvons vous en renvoyer un.
        </div>

        <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-emerald-400">
          ✅ Un nouveau lien de vérification vient d’être envoyé.
        </div>

        <form @submit.prevent="submit" class="mt-4 flex items-center justify-between gap-4">
          <PrimaryButton :disabled="form.processing">Renvoyer l’e-mail</PrimaryButton>
          <Link
            :href="route('logout')" method="post" as="button"
            class="rounded-md text-sm underline text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-0"
          >
            Se déconnecter
          </Link>
        </form>
      </div>
    </div>
  </div>
</template>
