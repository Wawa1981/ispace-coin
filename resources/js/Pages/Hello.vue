<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 has-ticker">

    <Starfield />

    <main class="relative z-10 w-full" :style="{ paddingTop: 'calc(var(--ticker-h) + 14px)' }">

      <Ticker />

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

      <!-- Section titre + tableau -->
      <section class="min-h-[19vh] md:min-h-[16vh] flex flex-col items-center justify-center text-center px-4">
        <h1 :class="['text-5xl md:text-6xl font-extrabold animate-rotateY', titleClass]">
          Wallet Cryptobank
        </h1>
      </section>

      <CryptoTable
          :markets="markets"
          :isLight="isLight"
          :titleClass="titleClass" 
      />

      <!-- Widgets déplacés sous le tableau -->
      <div class="mt-10">
        <NewsCarousel />
      </div>

       <section id="prevention" :class="isLight ? 'bg-white/80' : 'bg-black/40'" class="py-20">
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

      <!-- Footer -->
      <footer :class="isLight ? 'bg-white' : 'bg-black/60'" class="py-10">
        <div class="max-w-5xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4" :class="bodyTextClass">
          <p class="opacity-80">© {{ new Date().getFullYear() }} CryptoBank — Tous droits réservés.</p>
          <div class="flex items-center gap-3">
            <a href="#" class="social-btn" :class="socialBtnClass" aria-label="Facebook">📘 Facebook</a>
            <a href="#" class="social-btn" :class="socialBtnClass" aria-label="Telegram">✈ Telegram</a>
            <a href="#" class="social-btn" :class="socialBtnClass" aria-label="GitHub">🐙 GitHub</a>
            <a href="#top" class="social-btn" :class="socialBtnClass" aria-label="Haut de page">⬆ Haut</a>
          </div>
        </div>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

// Importation des composants
import Ticker from '@/Components/Ticker.vue'
import Starfield from '@/Components/Starfield.vue'
import CryptoTable from '@/Components/CryptoTable.vue'
import NewsCarousel from '@/Components/NewsCarousel.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue' 

// Importation des composables
import { useTheme } from '@/composables/useTheme'
import { useCryptoMarkets } from '@/composables/useCryptoMarkets'

// Utilisation des composables
const { isLight, bgClass, textClass, titleClass, bodyTextClass, btnBorderClass, socialBtnClass, toggleLight, cycleDarkTheme } = useTheme()
const { markets } = useCryptoMarkets()
</script>

<style scoped>
/* Les styles spécifiques à cette page sont conservés ici */
.has-ticker { --ticker-h: 48px; }
@media (min-width:768px){ .has-ticker { --ticker-h:56px; } }

@keyframes rotateY {0%{transform:rotateY(0);}100%{transform:rotateY(360deg);} }
.animate-rotateY{ animation:rotateY 10s linear infinite; display:inline-block; transform-style:preserve-3d; }

.neon-text-night{ text-shadow:0 0 6px #fff,0 0 12px #0ff,0 0 24px #0ff,0 0 48px #0ff,0 0 96px #0ff; }
.title-gradient{ background:linear-gradient(90deg,#2563eb,#a855f7);-webkit-background-clip:text;background-clip:text;color:transparent;text-shadow:0 2px 6px rgba(0,0,0,0.08); }

.btn-futuristic{ @apply font-bold py-3 px-8 rounded-lg bg-gradient-to-r shadow-xl transition-all duration-300 transform hover:scale-110 border bg-opacity-90; }

.social-btn { @apply text-sm px-3 py-1 rounded-lg border-2 transition-colors duration-200; }
</style>
                     
