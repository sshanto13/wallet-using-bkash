import '../css/app.css';
import { createApp } from 'vue';
import WalletDashboard from './dashboard/WalletDashboard.vue';
import LanguageSwitcher from './components/LanguageSwitcher.vue';
import i18n from './services/i18n';

// Create app
const app = createApp({});

// Register components
app.component('wallet-dashboard', WalletDashboard);
app.component('language-switcher', LanguageSwitcher);

// Make i18n available globally
app.config.globalProperties.$t = (key, params) => i18n.t(key, params);
app.config.globalProperties.$i18n = i18n;

// Initialize i18n (non-blocking)
i18n.loadTranslations().catch(err => {
  console.warn('Failed to load translations:', err);
});

// Mount app immediately
app.mount('#app');