<!-- DeleteUserForm.vue (ou le fichier que tu as collé) -->
<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref, onMounted, onUnmounted } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;
  nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  form.clearErrors();
  form.reset();
};

/* -------- Fond étoiles -------- */
const canvasRef = ref(null);

onMounted(() => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d', { alpha: true });
  const dpr = Math.min(window.devicePixelRatio || 1, 2);

  let W = 0, H = 0, running = true;
  const resize = () => {
    W = window.innerWidth; H = window.innerHeight;
    canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
    canvas.width = Math.floor(W * dpr); canvas.height = Math.floor(H * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  };
  resize();

  const rand = (a, b) => a + Math.random() * (b - a);
  const LAYERS = [
    { count: 120, speed: 0.05, size: [0.6, 1.2], twinkle: [0.25, 0.6] },
    { count: 80,  speed: 0.10, size: [0.8, 1.8], twinkle: [0.35, 0.8] },
    { count: 50,  speed: 0.18, size: [1.0, 2.2], twinkle: [0.45, 1.0] },
  ];
  const makeStar = (layer) => {
    const [smin, smax] = layer.size;
    const [tmin, tmax] = layer.twinkle;
    return {
      x: Math.random() * W, y: Math.random() * H,
      r0: rand(smin, smax), a0: rand(0.6, 1),
      vx: (Math.random() - 0.5) * layer.speed, vy: (Math.random() - 0.5) * layer.speed,
      twFreq: rand(0.6, 1.6), twAmp: rand(tmin, tmax), phase: Math.random() * Math.PI * 2,
    };
  };
  const layers = LAYERS.map(layer => ({ ...layer, stars: Array.from({ length: layer.count }, () => makeStar(layer)) }));

  const meteors = []; let meteorTimer = 0;
  const spawnMeteor = () => {
    meteors.push({ x: Math.random() * W, y: rand(-40, H * 0.3), vx: rand(6, 9), vy: rand(2, 4), life: 0, maxLife: rand(35, 55) });
  };

  const draw = () => {
    if (!running) return;
    ctx.clearRect(0, 0, W, H);
    const now = performance.now() / 1000;

    for (const layer of layers) {
      for (const s of layer.stars) {
        const tw = 0.5 + Math.sin(now * s.twFreq + s.phase) * 0.5;
        const a = Math.max(0.2, s.a0 * (0.5 + tw * s.twAmp));
        const r = Math.max(0.4, s.r0 * (0.7 + tw * 0.6));
        ctx.fillStyle = `rgba(255,255,255,${a})`;
        ctx.beginPath(); ctx.arc(s.x, s.y, r, 0, Math.PI * 2); ctx.fill();
        s.x += s.vx; s.y += s.vy;
        if (s.x < -2) s.x = W + 2; if (s.x > W + 2) s.x = -2;
        if (s.y < -2) s.y = H + 2; if (s.y > H + 2) s.y = -2;
      }
    }

    meteorTimer++;
    if (meteorTimer % 240 === 0 && meteors.length < 2) spawnMeteor();
    for (let i = meteors.length - 1; i >= 0; i--) {
      const m = meteors[i]; m.life++;
      const trail = 120;
      const grad = ctx.createLinearGradient(m.x, m.y, m.x - trail, m.y - trail * 0.5);
      grad.addColorStop(0, 'rgba(255,255,255,0.9)');
      grad.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.strokeStyle = grad; ctx.lineWidth = 2;
      ctx.beginPath(); ctx.moveTo(m.x, m.y); ctx.lineTo(m.x - trail, m.y - trail * 0.5); ctx.stroke();
      m.x += m.vx; m.y += m.vy;
      if (m.life > m.maxLife || m.x > W + 200 || m.y > H + 200) meteors.splice(i, 1);
    }

    requestAnimationFrame(draw);
  };
  draw();

  const onResize = () => resize();
  window.addEventListener('resize', onResize);
  onUnmounted(() => { running = false; window.removeEventListener('resize', onResize); });
});
</script>

<template>
  <!-- Fond bleu + étoiles, fixé à l’écran -->
  <div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-blue-950 via-slate-900 to-black"></div>
    <canvas ref="canvasRef" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
  </div>

  <!-- Ton contenu d’origine (inchangé) -->
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-gray-900">
        Delete Account
      </h2>

      <p class="mt-1 text-sm text-gray-600">
        Once your account is deleted, all of its resources and data will
        be permanently deleted. Before deleting your account, please
        download any data or information that you wish to retain.
      </p>
    </header>

    <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

    <Modal :show="confirmingUserDeletion" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Are you sure you want to delete your account?
        </h2>

        <p class="mt-1 text-sm text-gray-600">
          Once your account is deleted, all of its resources and data
          will be permanently deleted. Please enter your password to
          confirm you would like to permanently delete your account.
        </p>

        <div class="mt-6">
          <InputLabel for="password" value="Password" class="sr-only" />

          <TextInput
            id="password"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            class="mt-1 block w-3/4"
            placeholder="Password"
            @keyup.enter="deleteUser"
          />

          <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeModal">Cancel</SecondaryButton>

          <DangerButton
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            Delete Account
          </DangerButton>
        </div>
      </div>
    </Modal>
  </section>
</template>

<style scoped>
/* rien à ajouter ici */
</style>
