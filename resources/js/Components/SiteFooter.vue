<template>
  <footer
    class="site-footer"
    :class="[
      isLight ? 'site-footer--light' : 'site-footer--dark',
      { 'site-footer--reveal': reveal, 'is-visible': !reveal || footerVisible },
    ]"
    :aria-hidden="reveal && !footerVisible ? true : undefined"
    @mouseenter="onFooterEnter"
    @mouseleave="onFooterLeave"
  >
    <div class="site-footer__inner">
      <div class="site-footer__grid">
        <!-- Brand gauche -->
        <div class="site-footer__brand-col">
          <div class="footer-brand-row">
            <BrandLogo media-class="footer-logo-media" />
            <p class="footer-brand">iSpaceCoin</p>
          </div>
          <p class="footer-tag">Info · Space · Coin</p>
          <p class="footer-about">
            Espace info finance &amp; marchés. Info uniquement — pas un conseil en investissement.
          </p>
          <!-- Réseaux sociaux (couleurs de marque) -->
          <div class="footer-social" aria-label="Réseaux sociaux">
            <a
              v-for="s in socials"
              :key="s.label"
              :href="s.href"
              class="footer-social__btn"
              :class="`footer-social__btn--${s.id}`"
              :target="s.href && s.href !== '#' ? '_blank' : undefined"
              :rel="s.href && s.href !== '#' ? 'noopener noreferrer' : undefined"
              :title="s.label"
              :aria-label="s.label"
            >
              <!-- SVG colorés -->
              <svg v-if="s.id === 'x'" class="footer-social__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.835L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/>
              </svg>
              <svg v-else-if="s.id === 'telegram'" class="footer-social__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
              </svg>
              <svg v-else-if="s.id === 'discord'" class="footer-social__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
              </svg>
              <svg v-else-if="s.id === 'youtube'" class="footer-social__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              <svg v-else-if="s.id === 'linkedin'" class="footer-social__svg" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Nav compacte -->
        <div>
          <p class="footer-col-title">Navigation</p>
          <ul class="footer-links">
            <li><a :href="href('news')">Actualités</a></li>
            <li>
              <a :href="href('markets')" @click="onMarketsClick">Cours</a>
            </li>
            <li><a :href="href('prevention')">Prévention</a></li>
            <li><Link :href="route('login')">Connexion</Link></li>
            <li><Link :href="route('register')">Inscription</Link></li>
          </ul>
        </div>

        <!-- Médias -->
        <div>
          <p class="footer-col-title">Médias &amp; data</p>
          <ul class="footer-links footer-links--2col">
            <li>
              <a href="https://www.coingecko.com" target="_blank" rel="noopener noreferrer">CoinGecko</a>
            </li>
            <li>
              <a href="https://www.cryptocompare.com" target="_blank" rel="noopener noreferrer">CryptoCompare</a>
            </li>
            <li>
              <a href="https://coinmarketcap.com" target="_blank" rel="noopener noreferrer">CoinMarketCap</a>
            </li>
            <li>
              <a href="https://www.bloomberg.com/crypto" target="_blank" rel="noopener noreferrer">Bloomberg</a>
            </li>
            <li>
              <a href="https://www.reuters.com/markets/" target="_blank" rel="noopener noreferrer">Reuters</a>
            </li>
            <li>
              <a href="https://www.ft.com" target="_blank" rel="noopener noreferrer">FT</a>
            </li>
          </ul>
        </div>

        <!-- Forums 2 colonnes -->
        <div>
          <p class="footer-col-title">Forums</p>
          <ul class="footer-links footer-links--2col">
            <li>
              <a href="https://bitcointalk.org" target="_blank" rel="noopener noreferrer">BitcoinTalk</a>
            </li>
            <li>
              <a href="https://www.reddit.com/r/CryptoCurrency/" target="_blank" rel="noopener noreferrer">r/Crypto</a>
            </li>
            <li>
              <a href="https://www.reddit.com/r/Bitcoin/" target="_blank" rel="noopener noreferrer">r/Bitcoin</a>
            </li>
            <li>
              <a href="https://www.reddit.com/r/finance/" target="_blank" rel="noopener noreferrer">r/finance</a>
            </li>
            <li>
              <a href="https://www.reddit.com/r/investing/" target="_blank" rel="noopener noreferrer">r/investing</a>
            </li>
            <li>
              <a href="https://www.tradingview.com/ideas/" target="_blank" rel="noopener noreferrer">TradingView</a>
            </li>
            <li>
              <a href="https://www.bogleheads.org/forum/" target="_blank" rel="noopener noreferrer">Bogleheads</a>
            </li>
            <li>
              <a href="https://forum.ethereum.org" target="_blank" rel="noopener noreferrer">Eth Forum</a>
            </li>
          </ul>
        </div>

        <!-- TV finance — même liste que VideoNewsPopup (financeChannels.js) -->
        <div class="site-footer__tv">
          <p class="footer-col-title">TV finance</p>
          <ul class="footer-links footer-links--2col footer-links--tv">
            <li v-for="ch in financeChannels" :key="ch.channelId">
              <a :href="ch.sourceUrl" target="_blank" rel="noopener noreferrer">{{ ch.title }}</a>
            </li>
          </ul>
        </div>

        <!-- Légal -->
        <div>
          <p class="footer-col-title">Infos</p>
          <ul class="footer-links">
            <li><a :href="href('prevention')">Risques</a></li>
            <li><span class="opacity-60">Mentions légales</span></li>
            <li><span class="opacity-60">Confidentialité</span></li>
            <li><span class="opacity-60">CGU</span></li>
          </ul>
        </div>
      </div>

      <div class="footer-divider" />

      <div class="site-footer__bottom">
        <p class="site-footer__copy">
          © {{ year }} iSpaceCoin. Info uniquement — ni offre ni conseil financier.
        </p>
        <div class="site-footer__chips">
          <a :href="href('news')" class="footer-chip">Actus</a>
          <a :href="href('prevention')" class="footer-chip">Prévention</a>
          <a :href="topHref" class="footer-chip">↑ Haut</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import BrandLogo from '@/Components/BrandLogo.vue'
import { financeChannels } from '@/data/financeChannels'

const props = defineProps({
  isLight: { type: Boolean, default: false },
  /** true = footer fantôme (login) : apparaît bas / souris bas */
  reveal: { type: Boolean, default: false },
  /**
   * true = hors landing → ancres /#…
   * false = landing → #news, #markets…
   */
  homeLinks: { type: Boolean, default: false },
})

const emit = defineEmits(['open-markets'])

const year = new Date().getFullYear()
const footerVisible = ref(!props.reveal)
const footerPinned = ref(false)

/** Liens réseaux — remplace `#` par les vraies URLs */
const socials = [
  { id: 'x', label: 'X / Twitter', href: '#' },
  { id: 'telegram', label: 'Telegram', href: '#' },
  { id: 'discord', label: 'Discord', href: '#' },
  { id: 'youtube', label: 'YouTube', href: '#' },
  { id: 'linkedin', label: 'LinkedIn', href: '#' },
]

function href(id) {
  return props.homeLinks ? `/#${id}` : `#${id}`
}

const topHref = computed(() => (props.homeLinks ? '/' : '#top'))

function onMarketsClick(e) {
  if (!props.homeLinks) {
    e.preventDefault()
    emit('open-markets')
  }
}

function refreshFooter(e) {
  if (!props.reveal) {
    footerVisible.value = true
    return
  }
  if (footerPinned.value) {
    footerVisible.value = true
    return
  }
  const doc = document.documentElement
  const scrollable = doc.scrollHeight - window.innerHeight
  const nearPageEnd = scrollable > 8 && window.scrollY >= scrollable - 64
  const mouseNearBottom =
    e && typeof e.clientY === 'number' ? e.clientY >= window.innerHeight - 72 : false
  footerVisible.value = nearPageEnd || mouseNearBottom
}

function onFooterEnter() {
  if (!props.reveal) return
  footerPinned.value = true
  footerVisible.value = true
}

function onFooterLeave() {
  if (!props.reveal) return
  footerPinned.value = false
  refreshFooter()
}

function onScroll() {
  refreshFooter()
}

function onMouseMove(e) {
  refreshFooter(e)
}

onMounted(() => {
  if (!props.reveal) return
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('mousemove', onMouseMove, { passive: true })
  refreshFooter()
})

onUnmounted(() => {
  if (!props.reveal) return
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('mousemove', onMouseMove)
})
</script>

<style scoped>
.site-footer {
  margin-top: 0;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
  color: inherit;
  /* glass très léger — étoiles nettes à travers */
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.site-footer--dark {
  background: rgba(0, 0, 0, 0.14);
}

.site-footer--light {
  background: rgba(255, 255, 255, 0.12);
  border-top-color: rgba(15, 23, 42, 0.12);
}

/* Mode reveal (login) — même transparence */
.site-footer--reveal {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 40;
  max-height: min(38vh, 280px);
  overflow: auto;
  opacity: 0;
  transform: translateY(14px);
  pointer-events: none;
  transition:
    opacity 0.28s ease,
    transform 0.28s ease;
  background: rgba(8, 12, 24, 0.14);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  box-shadow: 0 -8px 20px rgba(0, 0, 0, 0.15);
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}
.site-footer--reveal.site-footer--light {
  background: rgba(255, 255, 255, 0.12);
  box-shadow: 0 -8px 20px rgba(15, 23, 42, 0.06);
  border-top-color: rgba(15, 23, 42, 0.12);
}
.site-footer--reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}
.site-footer--reveal::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 16px;
  pointer-events: auto;
}

.site-footer__inner {
  width: 100%;
  margin: 0;
  padding: 1rem 1.15rem 0.75rem;
}

.site-footer__grid {
  display: grid;
  /* brand | nav | médias | forums | TV (plus large) | infos */
  grid-template-columns: minmax(9.5rem, 0.95fr) 0.7fr 0.95fr 1.05fr 1.25fr 0.65fr;
  gap: 0.75rem 1rem;
  margin-bottom: 0.75rem;
  align-items: start;
}

@media (max-width: 1100px) {
  .site-footer__grid {
    grid-template-columns: 1fr 1fr 1fr;
  }
  .site-footer__brand-col {
    grid-column: 1 / -1;
  }
  .site-footer__tv {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .site-footer__grid {
    grid-template-columns: 1fr 1fr;
  }
  .site-footer__tv {
    grid-column: 1 / -1;
  }
}

/* Brand gauche */
.site-footer__brand-col {
  justify-self: start;
  text-align: left;
  min-width: 0;
}

.footer-brand-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
:deep(.footer-logo-media) {
  width: 1.85rem;
  height: 1.85rem;
  object-fit: cover;
  border-radius: 0.4rem;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.06);
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12);
  display: block;
}
.footer-brand {
  font-family: 'Orbitron', 'Space Grotesk', system-ui, sans-serif;
  font-weight: 800;
  font-size: 1.05rem;
  letter-spacing: 0.05em;
  margin: 0;
  background: linear-gradient(100deg, #22d3ee, #a78bfa);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.footer-tag {
  font-family: 'Orbitron', system-ui, sans-serif;
  font-size: 0.58rem;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  opacity: 0.6;
  margin: 0.15rem 0 0;
}

.footer-about {
  font-size: 0.72rem;
  opacity: 0.65;
  margin: 0.4rem 0 0;
  line-height: 1.4;
  max-width: 13.5rem;
}

/* Social — couleurs de marque bien visibles */
.footer-social {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-top: 0.6rem;
}
.footer-social__btn {
  width: 2rem;
  height: 2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.45rem;
  text-decoration: none;
  transition:
    transform 0.15s,
    filter 0.15s,
    box-shadow 0.15s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
.footer-social__btn:hover {
  transform: translateY(-2px) scale(1.06);
  filter: brightness(1.12);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
}
.footer-social__svg {
  width: 1.05rem;
  height: 1.05rem;
  display: block;
}

/* X */
.footer-social__btn--x {
  background: #111;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.2);
}
/* Telegram */
.footer-social__btn--telegram {
  background: #229ed9;
  color: #fff;
}
/* Discord */
.footer-social__btn--discord {
  background: #5865f2;
  color: #fff;
}
/* YouTube */
.footer-social__btn--youtube {
  background: #ff0000;
  color: #fff;
}
/* LinkedIn */
.footer-social__btn--linkedin {
  background: #0a66c2;
  color: #fff;
}

.footer-col-title {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  opacity: 0.8;
  margin: 0 0 0.4rem;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.22rem;
}

/* 2 colonnes = 2 rangées de liens (TV / forums / médias) */
.footer-links--2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  column-gap: 0.65rem;
  row-gap: 0.2rem;
}

.footer-links a,
.footer-links span {
  font-size: 0.75rem;
  opacity: 0.72;
  text-decoration: none;
  color: inherit;
  transition:
    opacity 0.15s,
    color 0.15s;
  white-space: nowrap;
}

.footer-links a:hover {
  opacity: 1;
  color: #22d3ee;
}

.site-footer--light .footer-links a:hover {
  color: #4f46e5;
}

.footer-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
}

.site-footer--light .footer-divider {
  background: rgba(15, 23, 42, 0.08);
}

.site-footer__bottom {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 0.55rem;
}

@media (min-width: 768px) {
  .site-footer__bottom {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.site-footer__copy {
  margin: 0;
  font-size: 0.68rem;
  line-height: 1.4;
  opacity: 0.55;
  max-width: 36rem;
}

.site-footer__chips {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: wrap;
}

.footer-chip {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.22rem 0.5rem;
  border-radius: 9999px;
  text-decoration: none;
  color: inherit;
  background: rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
  transition: background 0.15s;
}

.footer-chip:hover {
  background: rgba(34, 211, 238, 0.15);
}

.site-footer--light .footer-chip {
  background: rgba(15, 23, 42, 0.04);
  box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.1);
}
</style>
