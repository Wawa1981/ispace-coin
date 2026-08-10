<template>
  <canvas ref="canvasRef" class="pointer-events-none fixed inset-0 w-full h-full z-0"></canvas>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { isLight } = useTheme()
const canvasRef = ref(null)

onMounted(() => {
  const canvas = canvasRef.value
  const ctx = canvas.getContext('2d', { alpha: true })
  const dpr = Math.min(window.devicePixelRatio || 1, 2)

  let W = 0, H = 0, running = true

  // ✅ Version unique et propre de resize
  const resize = () => {
    W = window.innerWidth; H = window.innerHeight
    canvas.style.width = W + 'px'; canvas.style.height = H + 'px'
    canvas.width = Math.floor(W * dpr); canvas.height = Math.floor(H * dpr)
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
  }
  resize()

  const LAYERS = [
    { count: 120, speed: 0.05, size: [0.6, 1.2], twinkle: [0.25, 0.6] },
    { count: 80,  speed: 0.10, size: [0.8, 1.8], twinkle: [0.35, 0.8] },
    { count: 50,  speed: 0.18, size: [1.0, 2.2], twinkle: [0.45, 1.0] },
  ]
  const rand = (a,b) => a + Math.random()*(b-a)
  const makeStar = (layer) => {
    const [smin, smax] = layer.size
    const [tmin, tmax] = layer.twinkle
    return {
      x: Math.random()*W, y: Math.random()*H,
      r0: rand(smin, smax), r: 0,
      a0: rand(0.6, 1), a: 0,
      vx: (Math.random()-0.5)*layer.speed, vy: (Math.random()-0.5)*layer.speed,
      twFreq: rand(0.6, 1.6), twAmp: rand(tmin, tmax), phase: Math.random()*Math.PI*2,
      hue: Math.random() < 0.12 ? rand(180, 300) : null
    }
  }
  const layers = LAYERS.map(l => ({ ...l, stars: Array.from({length: l.count}, () => makeStar(l)) }))
  const meteors = []; let meteorTimer = 0
  const spawnMeteor = () => {
    const fromTop = Math.random() < 0.5
    meteors.push({ x: Math.random()*W, y: fromTop ? rand(-40,0) : rand(0, H*0.3), vx: rand(6,9), vy: rand(2,4), life: 0, maxLife: rand(35,55) })
  }

  const draw = () => {
    if (!running) return
    ctx.clearRect(0,0,W,H)
    const now = performance.now()/1000

    for (const layer of layers) {
      for (const s of layer.stars) {
        const tw = 0.5 + Math.sin(now*s.twFreq + s.phase)*0.5
        s.a = Math.max(0.2, s.a0*(0.5 + tw*s.twAmp))
        s.r = Math.max(0.4, s.r0*(0.7 + tw*0.6))

        if (isLight.value)
          ctx.fillStyle = s.hue ? `hsla(${s.hue} 60% 35% / ${Math.min(0.9, s.a)})` : `rgba(0,0,0,${Math.min(0.7, s.a)})`
        else
          ctx.fillStyle = s.hue ? `hsla(${s.hue} 100% 80% / ${s.a})` : `rgba(255,255,255,${s.a})`

        ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI*2); ctx.fill()
        s.x += s.vx; s.y += s.vy
        if (s.x < -2) s.x = W+2; if (s.x > W+2) s.x = -2
        if (s.y < -2) s.y = H+2; if (s.y > H+2) s.y = -2
      }
    }

    meteorTimer++
    if (meteorTimer % 240 === 0 && meteors.length < 2) spawnMeteor()
    for (let i = meteors.length-1; i >= 0; i--) {
      const m = meteors[i]; m.life++
      const trail = 120
      const grad = ctx.createLinearGradient(m.x, m.y, m.x - trail, m.y - trail*0.5)
      grad.addColorStop(0, isLight.value ? 'rgba(0,0,0,0.7)' : 'rgba(255,255,255,0.9)')
      grad.addColorStop(1, 'rgba(0,0,0,0)')
      ctx.strokeStyle = grad; ctx.lineWidth = 2
      ctx.beginPath(); ctx.moveTo(m.x, m.y); ctx.lineTo(m.x - trail, m.y - trail*0.5); ctx.stroke()
      m.x += m.vx; m.y += m.vy
      if (m.life > m.maxLife || m.x > W+200 || m.y > H+200) meteors.splice(i,1)
    }

    requestAnimationFrame(draw)
  }
  draw()

  // ✅ Un seul resize utilisé
  const onResize = () => resize()
  window.addEventListener('resize', onResize)

  onUnmounted(() => {
    running = false
    window.removeEventListener('resize', onResize)
  })
})
</script>
