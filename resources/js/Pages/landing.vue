je prefere ca idiot 
<template>
  <q-page class="bg-dark text-white overflow-hidden">
    <!-- Header avec navigation -->
    <div class="q-pa-lg">
      <div class="row justify-between items-center">
        <div class="flex items-center">
          <img src="/logo.svg" alt="QuantiX Logo" class="q-mr-sm" height="40" />
          <span class="text-h5 text-weight-bold">QuantiX</span>
        </div>
        <div class="row items-center">
          <q-btn flat label="Connexion" to="/login" class="q-mr-sm text-white" />
          <q-btn unelevated color="primary" label="S'inscrire" to="/register" class="text-weight-bold" />
        </div>
      </div>
    </div>

    <!-- Section Hero avec gradient animé -->
    <div class="hero-section relative-position q-pa-xl text-center">
      <div class="hero-gradient"></div>
      <div class="hero-content relative-position">
        <h1 class="text-h2 text-weight-bold q-mb-md gradient-text">
          L'AVENIR DU TRADING CRYPTO
        </h1>
        <p class="text-h5 q-mb-lg text-grey-3 max-width-md">
          Échangez, investissez et prospérez avec la plateforme crypto de nouvelle génération
        </p>
        
        <!-- Messages préventifs importants -->
        <div class="warning-banner q-mb-xl">
          <q-banner class="bg-orange-9 text-white rounded-borders q-mb-md">
            <template v-slot:avatar>
              <q-icon name="warning" color="white" />
            </template>
            <strong>ATTENTION :</strong> Les cryptomonnaies sont des actifs volatils. Ne jamais investir plus que ce que vous pouvez vous permettre de perdre.
          </q-banner>
        </div>

        <div class="row justify-center q-gutter-md">
          <q-btn 
            size="lg" 
            color="primary" 
            label="COMMENCER À TRADER" 
            to="/trading"
            class="text-weight-bold q-px-xl"
          />
          <q-btn 
            size="lg" 
            outline 
            color="white" 
            label="DÉCOUVRIR NOS FRAIS" 
            to="/fees"
            class="text-weight-bold q-px-xl"
          />
        </div>
      </div>
    </div>

    <!-- Section Slogans & Messages -->
    <div class="slogans-section q-pa-xl">
      <div class="row q-col-gutter-xl">
        <div class="col-12 col-md-4">
          <div class="slogan-card">
            <q-icon name="shield" size="3rem" color="primary" class="q-mb-md" />
            <h3 class="text-h5 text-weight-bold q-mb-sm">SÉCURITÉ MAXIMALE</h3>
            <p class="text-body1">Vos fonds sont protégés par une technologie de cryptage militaire et des protocoles de sécurité multicouches.</p>
            <div class="security-badge">
              <q-chip color="green" text-color="white" icon="verified" label="Audité & Certifié" />
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="slogan-card">
            <q-icon name="speed" size="3rem" color="primary" class="q-mb-md" />
            <h3 class="text-h5 text-weight-bold q-mb-sm">VITESSE ÉCLAIR</h3>
            <p class="text-body1">Exécution instantanée de vos ordres avec une latence ultra-faible. Saisissez chaque opportunité du marché.</p>
            <div class="speed-indicator">
              <q-chip color="blue" text-color="white" icon="flash_on" label="< 0.1s d'exécution" />
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="slogan-card">
            <q-icon name="trending_up" size="3rem" color="primary" class="q-mb-md" />
            <h3 class="text-h5 text-weight-bold q-mb-sm">PERFORMANCE OPTIMISÉE</h3>
            <p class="text-body1">Outils d'analyse avancés et API professionnelle pour maximiser vos stratégies de trading.</p>
            <div class="performance-badge">
              <q-chip color="purple" text-color="white" icon="analytics" label="API Pro incluse" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Messages Préventifs Détaillés -->
    <div class="prevention-section q-pa-xl bg-grey-9">
      <div class="text-center q-mb-xl">
        <h2 class="text-h3 text-weight-bold text-orange">TRADEZ EN TOUTE CONNAISSANCE</h2>
        <p class="text-h6 text-grey-3">Votre sécurité financière est notre priorité</p>
      </div>
      
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-6">
          <q-card class="prevention-card bg-red-10">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon name="error" class="q-mr-sm" />
                RISQUES IMPORTANTS
              </div>
              <ul class="text-body2">
                <li>Les cryptomonnaies peuvent perdre 90% de leur valeur en quelques jours</li>
                <li>Les marchés sont ouverts 24h/24, les pertes peuvent survenir à tout moment</li>
                <li>Aucun investissement n'est garanti, même avec les meilleures analyses</li>
                <li>Ne tradez jamais avec de l'argent emprunté</li>
              </ul>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-md-6">
          <q-card class="prevention-card bg-green-10">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon name="psychology" class="q-mr-sm" />
                CONSEILS ESSENTIELS
              </div>
              <ul class="text-body2">
                <li>Commencez toujours par de petits montants pour apprendre</li>
                <li>Diversifiez vos investissements, ne misez jamais tout sur un actif</li>
                <li>Définissez des limites de pertes avant chaque trade</li>
                <li>Formez-vous continuellement sur les marchés crypto</li>
              </ul>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Section Campagne Marketing -->
    <div class="campaign-section q-pa-xl">
      <div class="text-center q-mb-xl">
        <h2 class="text-h3 text-weight-bold gradient-text">REJOIGNEZ LA RÉVOLUTION QUANTIX</h2>
        <p class="text-h6 text-grey-3">Des milliers de traders nous font déjà confiance</p>
      </div>

      <div class="row q-col-gutter-xl items-center">
        <div class="col-12 col-md-6">
          <div class="campaign-stats">
            <div class="stat-item q-mb-lg">
              <div class="text-h3 text-weight-bold text-primary">500K+</div>
              <div class="text-body1 text-grey-3">Traders actifs</div>
            </div>
            <div class="stat-item q-mb-lg">
              <div class="text-h3 text-weight-bold text-primary">€2.5B+</div>
              <div class="text-body1 text-grey-3">Volume tradé quotidien</div>
            </div>
            <div class="stat-item">
              <div class="text-h3 text-weight-bold text-primary">99.9%</div>
              <div class="text-body1 text-grey-3">Uptime garantie</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="campaign-cta">
            <h3 class="text-h4 text-weight-bold q-mb-md">PRÊT À COMMENCER ?</h3>
            <p class="text-body1 q-mb-lg text-grey-3">
              Créez votre compte gratuitement et recevez 10€ de bonus de bienvenue*
            </p>
            <q-btn 
              size="xl" 
              color="primary" 
              label="CRÉER MON COMPTE" 
              to="/register"
              class="text-weight-bold full-width q-mb-md"
            />
            <p class="text-caption text-grey-5">*Conditions applicables - Voir nos conditions générales</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Liens Navigation -->
    <div class="navigation-section q-pa-xl bg-grey-10">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-8">
          <h3 class="text-h5 text-weight-bold q-mb-md">EXPLOREZ NOTRE PLATEFORME</h3>
          <div class="row q-col-gutter-md">
            <div class="col-6 col-md-3" v-for="link in navigationLinks" :key="link.label">
              <q-btn
                outline
                color="primary"
                :label="link.label"
                :to="link.href"
                class="full-width text-weight-bold"
                size="md"
              />
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <h3 class="text-h5 text-weight-bold q-mb-md">RESTEZ INFORMÉ</h3>
          <p class="text-body2 text-grey-4 q-mb-md">
            Recevez les dernières actualités crypto et nos analyses de marché
          </p>
          <div class="newsletter-form">
            <q-input
              dense
              outlined
              placeholder="Votre adresse email"
              class="q-mb-sm bg-grey-9 text-white"
              color="primary"
            />
            <q-btn 
              color="primary" 
              label="S'ABONNER" 
              class="full-width text-weight-bold"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Légal -->
    <div class="footer-section q-pa-xl border-top">
      <div class="row justify-between items-center q-mb-md">
        <div class="flex items-center">
          <img src="/logo.svg" alt="QuantiX" height="24" class="q-mr-sm" />
          <span class="text-weight-bold">QuantiX</span>
        </div>
        <div class="footer-links">
          <q-btn flat dense label="Politique" to="/policy" class="q-mr-sm text-grey-4" />
          <q-btn flat dense label="Conditions" to="/terms" class="q-mr-sm text-grey-4" />
          <q-btn flat dense label="Statuts" to="/statute" class="text-grey-4" />
        </div>
      </div>
      
      <div class="row justify-between items-center">
        <div class="text-grey-5 text-caption">
          &copy; 2025 Bionics LTE. Tous droits réservés. | Plateforme régulée et autorisée
        </div>
        <div class="contact-info">
          <q-btn flat dense icon="email" class="q-mr-sm text-grey-4" />
          <span class="text-grey-5 text-caption q-mr-md">contact@ariane.com</span>
          <q-btn flat dense icon="phone" class="text-grey-4" />
          <span class="text-grey-5 text-caption">+33 6 12 34 56 78</span>
        </div>
      </div>

      <!-- Liens système et aperçu -->
      <div class="system-links q-mt-md text-center">
        <q-btn flat dense label="Vue d'ensemble" to="/overview" class="q-mr-sm text-grey-4" />
        <q-btn flat dense label="État du système" to="/system" class="text-grey-4" />
      </div>
    </div>
  </q-page>
</template>

<script setup>
const navigationLinks = [
  { label: "Trading", href: "/trading" },
  { label: "Frais", href: "/fees" },
  { label: "API", href: "/api" },
  { label: "Contact", href: "/contact" }
];
</script>

<style scoped>
/* Animations et gradients */
.hero-section {
  min-height: 70vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.hero-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #00bcd4 0%, #1a1a1a 50%, #673ab7 100%);
  opacity: 0.3;
  animation: gradientShift 8s ease-in-out infinite;
}

@keyframes gradientShift {
  0%, 100% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
  50% { transform: translateX(-30%) translateY(-30%) rotate(180deg); }
}

.gradient-text {
  background: linear-gradient(135deg, #00bcd4, #673ab7);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-content {
  z-index: 2;
  max-width: 900px;
}

.max-width-md {
  max-width: 600px;
  margin: 0 auto;
}

/* Cards et sections */
.slogan-card {
  text-align: center;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
}

.slogan-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 188, 212, 0.3);
}

.prevention-card {
  border-radius: 16px;
  border: 2px solid currentColor;
}

.campaign-stats .stat-item {
  text-align: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  backdrop-filter: blur(5px);
}

.campaign-cta {
  background: rgba(0, 188, 212, 0.1);
  padding: 2rem;
  border-radius: 16px;
  border: 1px solid rgba(0, 188, 212, 0.3);
}

/* Warning banner */
.warning-banner {
  max-width: 800px;
  margin: 0 auto;
}

/* Newsletter form */
.newsletter-form {
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(5px);
}

/* Borders et separateurs */
.border-top {
  border-top: 1px solid #444;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-section {
    min-height: 60vh;
    padding: 2rem 1rem;
  }
  
  .text-h2 {
    font-size: 2rem;
  }
  
  .slogan-card {
    padding: 1.5rem;
  }
}

/* Animations supplémentaires */
.q-btn {
  transition: all 0.3s ease;
}

.q-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

/* Puces et listes */
ul {
  padding-left: 1.2rem;
}

li {
  margin-bottom: 0.5rem;
  line-height: 1.5;
}
</style>
