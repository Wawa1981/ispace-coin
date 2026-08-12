<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 has-ticker">

    <Starfield />

    <main class="relative z-10 w-full" :style="{ paddingTop: 'calc(var(--ticker-h) + 14px)' }">

      <Ticker />

      <!-- Popup actu vidéo (style BFM / médias) -->
      <VideoNewsPopup :is-light="isLight" />

      <!-- Barre du haut -->
      <div class="absolute top-8 left-4 z-40 flex gap-3">
        <Link :href="route('login')" 
          :class="['btn-futuristic from-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-600', btnBorderClass]">
          Login
        </Link>
        <Link :href="route('register')" 
          :class="['btn-futuristic from-purple-600 to-pink-600 hover:from-pink-600 hover:to-purple-600', btnBorderClass]">
          Register
        </Link>
      </div>

      <!-- Switcher à droite -->
      <ThemeSwitcher />

      <!-- Brand wordmark : même taille + épaisseur 3D (extrusion Z) -->
      <section class="brand-stage min-h-[22vh] md:min-h-[20vh] flex flex-col items-center justify-center text-center px-4 py-6">
        <h1
          class="brand-wordmark brand-wordmark--spin"
          :class="isLight ? 'brand-wordmark--light' : 'brand-wordmark--dark'"
          aria-label="iSpaceCoin"
        >
          <!-- Tranches d’épaisseur (1px chacune) — pas plus grand, pas étiré -->
          <span
            v-for="z in thicknessLayers"
            :key="z"
            class="brand-slice"
            :class="{ 'brand-slice--face': z === 0 }"
            :style="{ '--z': z }"
            :aria-hidden="z !== 0"
          >
            <span class="brand-wordmark__i">i</span><span class="brand-wordmark__space">Space</span><span class="brand-wordmark__coin">Coin</span>
          </span>
        </h1>
        <p class="brand-tagline" :class="isLight ? 'brand-tagline--light' : 'brand-tagline--dark'">
          <span class="brand-tagline__info">Info</span>
          <span class="brand-tagline__dot">·</span>
          <span class="brand-tagline__space">Space</span>
          <span class="brand-tagline__dot">·</span>
          <span class="brand-tagline__coin">Coin</span>
        </p>
        <p class="hero-pitch" :class="bodyTextClass">
          Toutes les infos finance &amp; crypto des meilleurs sites d’actualité —
          <strong>un seul espace</strong>, plus besoin de jongler entre les onglets.
        </p>
      </section>

      <!-- Actus + cours : même conteneur max-w-5xl / px-4 pour l’alignement -->
      <NewsCarousel @toggle-markets="showMarkets = !showMarkets" />

      <section id="markets" class="max-w-5xl mx-auto px-4 mb-4">
        <div v-show="showMarkets" class="markets-panel">
          <CryptoTable
            :markets="markets"
            :isLight="isLight"
            :titleClass="titleClass"
          />
        </div>
      </section>

      <section id="prevention" :class="isLight ? 'bg-white/80' : 'bg-black/40'" class="pt-8 pb-16">
        <div class="max-w-3xl mx-auto px-6 leading-relaxed" :class="bodyTextClass">
          <h2 class="text-3xl font-bold mb-6">⚠ Alerte : Manipulation des Marchés Crypto – Ce que vous devez savoir</h2>
          <p class="mb-4">
            Le monde des cryptomonnaies est fascinant, mais aussi rempli de pièges.
            Derrière les grandes plateformes et les promesses de liberté financière se cache parfois une réalité brutale :
            des manipulations massives orchestrées par les plus puissants.
          </p>
          <p class="mb-4">
            Certaines plateformes sont souvent suspectées ou accusées de manipulations de marché, notamment dans l'univers des cryptomonnaies. Bien que ces pratiques soient difficiles à prouver, voici une liste des plateformes qui reviennent régulièrement dans les discussions sur ce sujet :
          </p>
          <ul class="list-disc pl-6 space-y-2 mb-4">
            <li><strong>Binance</strong> : La plus grande plateforme crypto au monde est souvent critiquée pour sa transparence. Bien qu’elle nie toute manipulation, des accusations de wash trading et de liquidations forcées sur les positions à effet de levier ont émergé.</li>
            <li><strong>FTX (avant sa faillite)</strong> : Avant son effondrement, FTX était accusée de manipuler les prix pour liquider les positions de ses utilisateurs. Sam Bankman-Fried, son fondateur, est également soupçonné d'avoir influencé les marchés via Alameda Research.</li>
            <li><strong>BitMEX</strong> : Historiquement, BitMEX a été critiquée pour des liquidations massives, causées par des mouvements violents sur son marché à effet de levier. Les "long squeezes" et "short squeezes" étaient fréquents.</li>
            <li><strong>Huobi et OKEx</strong> : Ces plateformes chinoises ont été pointées du doigt pour un manque de transparence et des volumes artificiellement gonflés via le wash trading.</li>
            <li><strong>KuCoin</strong> : Bien que plus petite, KuCoin est parfois accusée de manipuler la liquidité et les carnets d'ordres pour créer de fausses impressions de mouvements de marché.</li>
            <li><strong>Gate.io</strong> : Des soupçons de wash trading et de manipulation des prix y sont récurrents, notamment sur des altcoins peu liquides.</li>
            <li><strong>Bitfinex</strong> : Accusée à plusieurs reprises d’utiliser Tether (USDT) pour manipuler les prix de Bitcoin, notamment lors des grandes hausses.</li>
            <li><strong>Robinhood (pour les actions et crypto)</strong> : Pendant la saga GameStop, Robinhood a été accusée de bloquer volontairement les achats d’actions pour protéger des positions vendeuses importantes.</li>
          </ul>
          <p class="mb-4">
            C'est un jeu où les plus gros acteurs (baleines, institutions, et parfois même les plateformes) tirent les ficelles pour maximiser leurs gains au détriment des petits investisseurs. Ils utilisent la volatilité pour liquider les positions des traders trop confiants, que ce soit en short ou en long, en manipulant les prix avec des techniques bien rodées.
          </p>

          <h3 class="text-xl font-semibold mt-6 mb-2">❗ Comment ça fonctionne ?</h3>
          <p class="mb-4">
            Pour les manipulateurs, l’objectif est de capter un maximum de liquidités tout en empêchant la majorité des investisseurs de profiter des mouvements du marché. C’est pour ça qu’il est crucial d’être prudent, d’avoir une bonne gestion des risques, et de ne jamais suivre aveuglément les signaux apparents.
          </p>
          <ul class="list-disc pl-6 space-y-1">
            <li><strong>Ordres fantômes</strong> : Certains traders placent de gros ordres de vente qui n'ont pas l'intention d'être exécutés. Cela crée une illusion d'offre élevée, incitant d'autres à vendre. Une fois la pression retombée, ces ordres sont annulés, permettant au prix de grimper.</li>
            <li><strong>Wash trading</strong> : Les plateformes ou traders effectuent des transactions entre eux pour donner l'impression d'une forte activité, attirant ainsi plus d'acheteurs.</li>
            <li><strong>Liquidations forcées</strong> : En montant le prix artificiellement, ils peuvent forcer les positions short (vente à découvert) à se liquider, ce qui ajoute encore plus de pression acheteuse.</li>
          </ul>

          <h3 class="text-xl font-semibold mt-6 mb-2">✅ Comment vous protéger ?</h3>
          <ul class="list-disc pl-6 space-y-1">
            <li><strong>Utilisez des plateformes réglementées</strong> : privilégiez celles qui sont soumises à des autorités de régulation reconnues.</li>
            <li><strong>Prudence avec l’effet de levier</strong> : Les liquidations forcées sont l'un des outils préférés des manipulateurs.</li>
            <li><strong>Analysez le marché</strong> : Méfiez-vous des mouvements soudains sans actualité ou raison logique.</li>
            <li><strong>Diversifiez vos investissements</strong> : Ne laissez pas toutes vos positions sur une seule plateforme.</li>
          </ul>

          <h3 class="text-xl font-semibold mt-6 mb-2">Ces pratiques sont illégales</h3>
          <p class="mb-4">
            Ces pratiques, connues sous le nom de collusion ou manipulation de marché, sont illégales car elles faussent les règles du marché libre et nuisent aux investisseurs.
          </p>
          <ul class="list-disc pl-6 space-y-1 mb-4">
            <li><strong>Distorsion du marché</strong> : Ces pratiques empêchent le marché de fonctionner selon les principes de l'offre et de la demande.</li>
            <li><strong>Préjudice aux investisseurs</strong> : Les petits investisseurs et traders sont souvent les premières victimes de ces manipulations.</li>
            <li><strong>Perte de confiance</strong> : La manipulation du marché peut entraîner une perte de confiance dans le secteur, réduisant la participation des investisseurs légitimes.</li>
          </ul>

          <h3 class="text-xl font-semibold mt-6 mb-2">Encadrement légal</h3>
          <p class="mb-4">
            Dans la plupart des pays, il existe des régulateurs qui surveillent et sanctionnent ce type de pratiques :
          </p>
          <ul class="list-disc pl-6 space-y-1 mb-4">
            <li><strong>États-Unis</strong> : La Securities and Exchange Commission (SEC) et la Commodity Futures Trading Commission (CFTC) surveillent de près les marchés financiers et cryptographiques.</li>
            <li><strong>Europe</strong> : L'Autorité européenne des marchés financiers (ESMA) et les régulateurs nationaux, comme l'AMF en France, encadrent ces pratiques.</li>
          </ul>

          <h3 class="text-xl font-semibold mt-6 mb-2">Pourquoi ces pratiques continuent ?</h3>
          <ul class="list-disc pl-6 space-y-1 mb-4">
            <li><strong>Régulation insuffisante</strong> : Les cryptomonnaies, par leur nature décentralisée, échappent encore à une régulation stricte dans de nombreuses juridictions.</li>
            <li><strong>Opaque et mondial</strong> : Les plateformes crypto opèrent souvent à l'échelle mondiale, compliquant l'application des lois locales.</li>
            <li><strong>Manque de preuves</strong> : Il est difficile de prouver la collusion ou la manipulation, surtout avec des pratiques dissimulées.</li>
          </ul>

          <h3 class="text-xl font-semibold mt-6 mb-2">Que faire en tant qu'investisseur ?</h3>
          <ul class="list-disc pl-6 space-y-1 mb-4">
            <li><strong>Diversifiez vos plateformes</strong> : Évitez de concentrer vos fonds sur une seule plateforme.</li>
            <li><strong>Soyez attentif aux signaux de manipulation</strong> : Hausse ou baisse rapide sans actualité.</li>
            <li><strong>Privilégiez les plateformes réglementées</strong> : Optez pour celles supervisées par des autorités reconnues.</li>
            <li><strong>Rejoignez des communautés vigilantes</strong> : Les forums ou groupes spécialisés dénoncent souvent des pratiques suspectes.</li>
          </ul>

          <blockquote class="mt-6 italic opacity-80">
            « Ce n’est pas une conspiration : c’est un système. »
            — Préservez votre capital. Comprenez les règles du jeu. Ne devenez pas la cible.
          </blockquote>
          <p class="mt-4">
            Si de telles pratiques sont avérées, elles peuvent et doivent être dénoncées aux autorités compétentes.
          </p>
          <p>
            L'industrie a besoin de régulations plus solides pour éviter ces abus.
          </p>
        </div>
      </section>

      <SiteFooter :is-light="isLight" @open-markets="showMarkets = true" />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

import Ticker from '@/Components/Ticker.vue'
import Starfield from '@/Components/Starfield.vue'
import CryptoTable from '@/Components/CryptoTable.vue'
import NewsCarousel from '@/Components/NewsCarousel.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import VideoNewsPopup from '@/Components/VideoNewsPopup.vue'
import SiteFooter from '@/Components/SiteFooter.vue'

import { useTheme } from '@/composables/useTheme'
import { useCryptoMarkets } from '@/composables/useCryptoMarkets'

const { isLight, bgClass, textClass, titleClass, bodyTextClass, btnBorderClass, socialBtnClass } = useTheme()
const { markets } = useCryptoMarkets()

const showMarkets = ref(false)
const thicknessLayers = Array.from({ length: 16 }, (_, i) => i)
</script>

<style scoped>
.has-ticker { --ticker-h: 48px; }
@media (min-width:768px){ .has-ticker { --ticker-h:56px; } }

.brand-stage {
  perspective: 900px;
  perspective-origin: 50% 50%;
}

.brand-wordmark {
  font-family: 'Orbitron', 'Space Grotesk', system-ui, sans-serif;
  font-weight: 800;
  font-size: clamp(2.75rem, 8vw, 5.5rem);
  letter-spacing: 0.04em;
  line-height: 1.05;
  margin: 0;
  display: inline-block;
  position: relative;
  user-select: none;
  transform-style: preserve-3d;
  /* largeur/hauteur = une seule face (les tranches sont empilées en Z) */
}

/* Pause 3s face + 3s verso */
@keyframes brand-rotateY {
  0%,
  18.75% {
    transform: rotateY(0deg);
  }
  50% {
    transform: rotateY(180deg);
  }
  68.75% {
    transform: rotateY(180deg);
  }
  100% {
    transform: rotateY(360deg);
  }
}

.brand-wordmark--spin {
  animation: brand-rotateY 16s linear infinite;
  transform-style: preserve-3d;
}

/* Une tranche = même texte, décalé de 1px en profondeur */
.brand-slice {
  display: block;
  white-space: nowrap;
  transform-style: preserve-3d;
  transform: translateZ(calc(var(--z) * -1px));
}

/* Tranches arrière superposées */
.brand-slice:not(.brand-slice--face) {
  position: absolute;
  left: 0;
  top: 0;
  filter: brightness(0.72);
}

.brand-slice--face {
  position: relative;
  z-index: 2;
}

.brand-wordmark__i {
  font-weight: 700;
  background: linear-gradient(135deg, #22d3ee 0%, #67e8f9 45%, #a5f3fc 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  margin-right: 0.02em;
}

.brand-wordmark__space {
  background: linear-gradient(100deg, #e0f2fe 0%, #ffffff 40%, #c4b5fd 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.brand-wordmark__coin {
  font-weight: 700;
  letter-spacing: 0.06em;
  background: linear-gradient(100deg, #818cf8 0%, #c084fc 50%, #f0abfc 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* glow sur la face seulement — filter sur le parent casse le preserve-3d */
.brand-wordmark--dark .brand-slice--face {
  filter: drop-shadow(0 0 24px rgba(34, 211, 238, 0.35))
          drop-shadow(0 0 48px rgba(129, 140, 248, 0.2));
}

.brand-wordmark--light .brand-wordmark__i {
  background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.brand-wordmark--light .brand-wordmark__space {
  background: linear-gradient(100deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.brand-wordmark--light .brand-wordmark__coin {
  background: linear-gradient(100deg, #4f46e5 0%, #7c3aed 55%, #a855f7 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.brand-wordmark--light .brand-slice--face {
  filter: drop-shadow(0 2px 12px rgba(15, 23, 42, 0.12));
}

.brand-wordmark--light .brand-slice:not(.brand-slice--face) {
  filter: brightness(0.85);
}

.brand-tagline {
  /* Même police que le logo (Orbitron) */
  font-family: 'Orbitron', 'Space Grotesk', system-ui, sans-serif;
  font-weight: 600;
  font-size: clamp(0.7rem, 1.8vw, 0.9rem);
  letter-spacing: 0.28em;
  text-transform: uppercase;
  margin-top: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.55em;
}

.brand-tagline__dot {
  opacity: 0.5;
  letter-spacing: 0;
}

/* Couleurs alignées sur i / Space / Coin du logo */
.brand-tagline--dark .brand-tagline__info {
  color: #67e8f9;
}
.brand-tagline--dark .brand-tagline__space {
  color: #e2e8f0;
}
.brand-tagline--dark .brand-tagline__coin {
  color: #c4b5fd;
}

.brand-tagline--light .brand-tagline__info {
  color: #0891b2;
}
.brand-tagline--light .brand-tagline__space {
  color: #1e293b;
}
.brand-tagline--light .brand-tagline__coin {
  color: #7c3aed;
}

.hero-pitch {
  max-width: 34rem;
  margin: 1rem auto 0;
  font-size: clamp(0.9rem, 2vw, 1.05rem);
  line-height: 1.55;
  opacity: 0.88;
}

.markets-panel {
  animation: panel-in 0.25s ease;
}

@keyframes panel-in {
  from { opacity: 0; transform: translateY(-6px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-futuristic {
  @apply font-bold py-3 px-8 rounded-lg bg-gradient-to-r shadow-xl transition-all duration-300 transform hover:scale-110 border bg-opacity-90;
}

.social-btn {
  @apply text-sm px-3 py-1 rounded-lg border-2 transition-colors duration-200;
}
</style>
                     
