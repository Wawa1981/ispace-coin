<template>
  <div
    class="auth min-h-screen relative overflow-x-hidden transition-colors duration-700"
    :class="[bgClass, textClass, isLight ? 'auth--light' : 'auth--dark']"
  >
    <Starfield />
    <ThemeSwitcher :is-fixed="true" />

    <div class="auth__shell relative z-10">
      <!-- ═══ PANNEAU GAUCHE — dégradé bleu → violet ═══ -->
      <aside class="auth__brand" aria-hidden="false">
        <div class="auth__mesh" aria-hidden="true" />
        <div class="auth__sheen" aria-hidden="true" />
        <div class="auth__glow auth__glow--a" aria-hidden="true" />
        <div class="auth__glow auth__glow--b" aria-hidden="true" />
        <div class="auth__glow auth__glow--c" aria-hidden="true" />

        <div class="auth__brand-inner">
          <div class="auth__top">
            <Link href="/" class="auth__logo-link">
              <BrandLogo media-class="auth__logo-media" />
              <span class="auth__logo-word">
                <em>i</em>Space<span>Coin</span>
              </span>
            </Link>
            <p class="auth__top-tag">Votre univers capitalisé</p>
          </div>

          <div class="auth__brand-copy">
            <p class="auth__kicker">Info · Finance · Capital</p>
            <h2 class="auth__headline">
              Votre espace
              <span class="auth__headline-grad">info de la finance</span>
            </h2>
            <p class="auth__sub">
              Actualités, marchés et portefeuille, réunis au même endroit.
            </p>
            <p class="auth__services">
              <strong class="auth__brand-name">iSpaceCoin</strong>
              regroupe les flux d’actualités multi-sources, vous informe sur les cours des devises, fiat,
              stablecoins, crypto et actions, propose des outils d’échange et vous offre un service de suivi de
              portefeuille pour décider et gérer votre capital.
            </p>
          </div>

          <ul class="auth__perks">
            <li>
              <span class="auth__perk-ico">◈</span>
              Marchés en temps réel
            </li>
            <li>
              <span class="auth__perk-ico">◎</span>
              Actus multi-sources
            </li>
            <li>
              <span class="auth__perk-ico">⬡</span>
              Connexion sécurisée
            </li>
          </ul>
        </div>
      </aside>

      <!-- ═══ FORMULAIRE (droite) ═══ -->
      <main class="auth__panel">
        <div class="auth__panel-inner">
          <header class="auth__head">
            <div class="auth__mobile-brand">
              <BrandLogo media-class="auth__logo-media" />
              <span class="auth__logo-word auth__logo-word--mobile">
                <em>i</em>Space<span>Coin</span>
              </span>
            </div>
            <h1 class="auth__title">Bon retour</h1>
            <p class="auth__lead">
              Connectez-vous pour accéder à votre espace iSpaceCoin.
            </p>
          </header>

          <a href="/auth/google" class="auth__google">
            <span class="auth__google-ico" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 48 48">
                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.3-.4-3.5z"/>
                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 12 24 12c3.1 0 5.8 1.2 8 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 16.3 4 9.6 8.3 6.3 14.7z"/>
                <path fill="#4CAF50" d="M24 44c5.3 0 10.1-2 13.8-5.3l-6.4-5.4C29.3 34.9 26.8 36 24 36c-5.3 0-9.7-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.3 4.1-4.2 5.4l6.4 5.4C36.9 40.1 44 35 44 24c0-1.2-.1-2.3-.4-3.5z"/>
              </svg>
            </span>
            Continuer avec Google
          </a>

          <div class="auth__divider" role="separator">
            <span>ou par email</span>
          </div>

          <form class="auth__form" @submit.prevent="login">
            <div class="auth__field" :class="{ 'is-filled': form.email, 'is-error': form.errors.email }">
              <input
                id="login-email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder=" "
                class="auth__input"
              />
              <label for="login-email" class="auth__label">Adresse email</label>
              <p v-if="form.errors.email" class="auth__error">{{ form.errors.email }}</p>
            </div>

            <div class="auth__field" :class="{ 'is-filled': form.password, 'is-error': form.errors.password }">
              <input
                id="login-password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                placeholder=" "
                class="auth__input auth__input--pw"
              />
              <label for="login-password" class="auth__label">Mot de passe</label>
              <button
                type="button"
                class="auth__eye"
                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? 'Masquer' : 'Voir' }}
              </button>
              <p v-if="form.errors.password" class="auth__error">{{ form.errors.password }}</p>
            </div>

            <div class="auth__row">
              <label class="auth__remember">
                <input v-model="form.remember" type="checkbox" />
                <span>Se souvenir de moi</span>
              </label>
              <Link :href="route('password.request')" class="auth__forgot">
                Mot de passe oublié ?
              </Link>
            </div>

            <button type="submit" class="auth__submit" :disabled="form.processing">
              <span v-if="!form.processing">Se connecter</span>
              <span v-else class="auth__spinner" aria-label="Chargement" />
            </button>
          </form>

          <p class="auth__foot-link">
            Pas encore de compte ?
            <Link :href="route('register')" class="auth__foot-link-a">Créer un compte</Link>
          </p>
        </div>
      </main>
    </div>

    <SiteFooter :is-light="isLight" :reveal="true" :home-links="true" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import SiteFooter from '@/Components/SiteFooter.vue'
import BrandLogo from '@/Components/BrandLogo.vue'
import { useTheme } from '@/composables/useTheme'

const { isLight, bgClass, textClass } = useTheme()
const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function login() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<style scoped>
/* Layout */
.auth {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.auth__shell {
  display: grid;
  min-height: 100vh;
  min-height: 100dvh;
  grid-template-columns: 1fr;
  flex: 1 0 auto;
}

@media (min-width: 960px) {
  .auth__shell {
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
  }
}

/* ═══════════════════════════════════════════
   PANNEAU GAUCHE — dégradé BLEU → VIOLET pro
   transparent pour les étoiles (pas de grain)
   ═══════════════════════════════════════════ */
.auth__brand {
  position: relative;
  display: flex;
  align-items: stretch;
  overflow: hidden;
  color: #eef2ff;
  isolation: isolate;
  /* base : dégradé diagonal complet, semi-transparent */
  background: linear-gradient(
    155deg,
    rgba(29, 78, 216, 0.55) 0%,
    rgba(37, 99, 235, 0.42) 18%,
    rgba(79, 70, 229, 0.4) 42%,
    rgba(124, 58, 237, 0.42) 68%,
    rgba(168, 85, 247, 0.28) 88%,
    rgba(15, 12, 40, 0.12) 100%
  );
  min-height: 42vh;
  border: 0;
  box-shadow: none;
  filter: none;
  text-shadow: none;
}

@media (min-width: 960px) {
  .auth__brand {
    min-height: 100%;
    border-right: 1px solid rgba(165, 180, 252, 0.2);
  }
}

/* couche mesh : profondeur + lumières croisées */
.auth__mesh {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(ellipse 85% 65% at 12% 18%, rgba(96, 165, 250, 0.45), transparent 58%),
    radial-gradient(ellipse 70% 55% at 88% 28%, rgba(129, 140, 248, 0.38), transparent 52%),
    radial-gradient(ellipse 75% 55% at 55% 92%, rgba(192, 132, 252, 0.4), transparent 55%),
    radial-gradient(ellipse 50% 40% at 40% 50%, rgba(59, 130, 246, 0.12), transparent 70%);
}

/* reflets diagonaux (sheen) — pro, sans pixel */
.auth__sheen {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    linear-gradient(
      115deg,
      transparent 0%,
      rgba(255, 255, 255, 0.07) 28%,
      transparent 42%,
      transparent 58%,
      rgba(196, 181, 253, 0.08) 72%,
      transparent 100%
    );
  mix-blend-mode: soft-light;
}

/* glows animés doux */
.auth__glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
  filter: blur(64px);
  will-change: transform;
  animation: brand-float 16s ease-in-out infinite;
}
.auth__glow--a {
  width: min(52vw, 420px);
  height: min(52vw, 420px);
  top: -18%;
  left: -12%;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.75) 0%, rgba(37, 99, 235, 0.35) 45%, transparent 70%);
  opacity: 0.7;
}
.auth__glow--b {
  width: min(48vw, 380px);
  height: min(48vw, 380px);
  bottom: -14%;
  right: -10%;
  background: radial-gradient(circle, rgba(192, 132, 252, 0.7) 0%, rgba(124, 58, 237, 0.35) 45%, transparent 70%);
  opacity: 0.65;
  animation-delay: -5s;
}
.auth__glow--c {
  width: min(36vw, 280px);
  height: min(36vw, 280px);
  top: 42%;
  left: 38%;
  background: radial-gradient(circle, rgba(129, 140, 248, 0.55) 0%, transparent 70%);
  opacity: 0.45;
  animation-delay: -10s;
  filter: blur(80px);
}

@keyframes brand-float {
  0%,
  100% {
    transform: translate(0, 0) scale(1);
  }
  50% {
    transform: translate(14px, -16px) scale(1.06);
  }
}

.auth__brand-inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 100%;
  padding: clamp(1.25rem, 4vw, 3.25rem);
  min-height: 100%;
  gap: 1.25rem;
}

@media (max-width: 959px) {
  .auth__brand-copy {
    margin: 1rem 0 !important;
  }
  .auth__headline {
    font-size: 1.55rem !important;
  }
  .auth__perks {
    flex-direction: row !important;
    flex-wrap: wrap;
    gap: 0.5rem 0.85rem !important;
  }
  .auth__perks li {
    font-size: 0.78rem !important;
  }
}

.auth__top {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.auth__top-tag {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 500;
  color: rgba(224, 231, 255, 0.78);
  line-height: 1.3;
}

.auth__logo-link {
  display: inline-flex;
  align-items: center;
  gap: 0.7rem;
  text-decoration: none;
  color: inherit;
  width: fit-content;
  flex-shrink: 0;
}

:deep(.auth__logo-media) {
  width: 2.5rem;
  height: 2.5rem;
  object-fit: cover;
  border-radius: 0.55rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: none;
  display: block;
  flex-shrink: 0;
}

.auth__logo-word {
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: #f8fafc;
}
.auth__logo-word em {
  font-style: normal;
  color: #60a5fa;
}
.auth__logo-word span {
  color: #c4b5fd;
}
.auth__logo-word--mobile em {
  color: #60a5fa;
}
.auth__logo-word--mobile span {
  color: #c4b5fd;
}

.auth__brand-copy {
  max-width: 26rem;
  margin: 3rem 0;
}

.auth__kicker {
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: rgba(199, 210, 254, 0.8);
  margin: 0 0 1rem;
  padding: 0;
  border: 0;
  background: none;
  border-radius: 0;
}

.auth__headline {
  font-size: clamp(2rem, 3.4vw, 2.85rem);
  font-weight: 700;
  line-height: 1.12;
  letter-spacing: -0.035em;
  margin: 0 0 1rem;
  color: #f8fafc;
}

.auth__headline-grad {
  display: block;
  background: linear-gradient(110deg, #38bdf8 0%, #818cf8 40%, #a78bfa 70%, #c084fc 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.auth__sub {
  font-size: 1rem;
  line-height: 1.6;
  color: rgba(224, 231, 255, 0.78);
  margin: 0;
  max-width: 22rem;
}

.auth__services {
  margin: 0.55rem 0 0;
  max-width: 22rem;
  font-size: 1rem;
  line-height: 1.6;
  font-weight: 400;
  color: rgba(199, 210, 254, 0.72);
}

.auth__brand-name {
  font-weight: 700;
  background: linear-gradient(110deg, #38bdf8 0%, #818cf8 45%, #c084fc 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.auth__perks {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  max-width: 26rem;
}

.auth__perks li {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 0.88rem;
  color: rgba(238, 242, 255, 0.9);
  font-weight: 500;
  padding: 0;
  border: 0;
  border-radius: 0;
  background: none;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
}

.auth__perk-ico {
  display: inline-flex;
  width: 1.5rem;
  height: 1.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.4rem;
  font-size: 0.7rem;
  flex-shrink: 0;
  background: rgba(59, 130, 246, 0.18);
  border: 1px solid rgba(129, 140, 248, 0.35);
  color: #93c5fd;
}

/* ═══════════════════════════════════════════
   FORMULAIRE (droite) — glass propre
   ═══════════════════════════════════════════ */
.auth__panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5.5rem 1.25rem 3rem;
  background: transparent;
  position: relative;
}

.auth__panel-inner {
  width: 100%;
  max-width: 380px;
  padding: 1.6rem 1.4rem;
  border-radius: 1.15rem;
  /* transparent pour les étoiles */
  background: rgba(8, 12, 24, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.16);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  box-shadow: none;
  color: #f4f4f5;
}

.auth--light .auth__panel-inner {
  background: rgba(255, 255, 255, 0.22);
  border-color: rgba(15, 23, 42, 0.1);
  color: #0f172a;
}

.auth__mobile-brand {
  display: none;
}

@media (max-width: 959px) {
  .auth__mobile-brand {
    display: none; /* brand déjà en haut via panneau gauche */
  }
}

.auth__head {
  margin-bottom: 1.5rem;
}

.auth__title {
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.15;
  margin: 0 0 0.45rem;
}

.auth__lead {
  margin: 0;
  font-size: 0.925rem;
  line-height: 1.5;
  opacity: 0.75;
}

.auth__google {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  width: 100%;
  height: 2.9rem;
  border-radius: 0.75rem;
  background: #fff;
  color: #111;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.auth__google:hover {
  filter: brightness(0.98);
}
.auth__google-ico {
  display: flex;
  line-height: 0;
}

.auth__divider {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin: 1.25rem 0;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  opacity: 0.55;
}
.auth__divider::before,
.auth__divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: currentColor;
  opacity: 0.25;
}

.auth__form {
  display: flex;
  flex-direction: column;
  gap: 0.95rem;
}

.auth__field {
  position: relative;
}

.auth__input {
  width: 100%;
  height: 3.25rem;
  padding: 1.15rem 0.95rem 0.45rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(0, 0, 0, 0.22);
  color: inherit;
  font-size: 0.95rem;
  outline: none;
}
.auth--light .auth__input {
  border-color: rgba(15, 23, 42, 0.12);
  background: rgba(255, 255, 255, 0.55);
}
.auth__input--pw {
  padding-right: 4.2rem;
}
/* focus = violet (bouton) — pas cyan */
.auth__input:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
}
.auth--light .auth__input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
}
.auth__input::placeholder {
  color: transparent;
}

.auth__label {
  position: absolute;
  left: 0.95rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.9rem;
  opacity: 0.55;
  pointer-events: none;
  transition: 0.15s ease;
}
.auth__field.is-filled .auth__label,
.auth__field:focus-within .auth__label {
  top: 0.55rem;
  transform: none;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  opacity: 0.95;
  color: #a78bfa;
}
.auth--light .auth__field.is-filled .auth__label,
.auth--light .auth__field:focus-within .auth__label {
  color: #7c3aed;
}

.auth__error {
  margin: 0.35rem 0 0;
  font-size: 0.75rem;
  color: #f87171;
}

.auth__eye {
  position: absolute;
  right: 0.7rem;
  top: 50%;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: inherit;
  font-size: 0.72rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.35rem 0.4rem;
  opacity: 0.55;
}
.auth__eye:hover {
  opacity: 1;
}

.auth__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-top: 0.15rem;
}

.auth__remember {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.82rem;
  opacity: 0.8;
  cursor: pointer;
  user-select: none;
}

.auth__forgot {
  font-size: 0.82rem;
  font-weight: 600;
  color: #a78bfa;
  text-decoration: none;
}
.auth--light .auth__forgot {
  color: #7c3aed;
}
.auth__forgot:hover {
  text-decoration: underline;
  text-underline-offset: 3px;
}

/* BOUTON — seul élément violet / blue-purple */
.auth__submit {
  margin-top: 0.35rem;
  height: 3rem;
  width: 100%;
  border: 0;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: -0.01em;
  cursor: pointer;
  color: #fff;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  box-shadow: 0 8px 20px -8px rgba(124, 58, 237, 0.45);
  transition: filter 0.15s ease, transform 0.15s ease, opacity 0.15s ease;
}
.auth__submit:hover:not(:disabled) {
  filter: brightness(1.06);
  transform: translateY(-1px);
}
.auth__submit:disabled {
  opacity: 0.65;
  cursor: wait;
}

.auth__spinner {
  display: inline-block;
  width: 1.1rem;
  height: 1.1rem;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.auth__foot-link {
  margin: 1.5rem 0 0;
  text-align: center;
  font-size: 0.875rem;
  opacity: 0.75;
}
.auth__foot-link-a {
  font-weight: 700;
  color: #a78bfa;
  text-decoration: none;
  margin-left: 0.25rem;
}
.auth--light .auth__foot-link-a {
  color: #7c3aed;
}
.auth__foot-link-a:hover {
  text-decoration: underline;
  text-underline-offset: 3px;
}
</style>
