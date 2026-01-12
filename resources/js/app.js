import '../css/app.css';
import { createApp } from 'vue';
import WalletDashboard from './dashboard/WalletDashboard.vue';

const app = createApp({});
app.component('wallet-dashboard', WalletDashboard);
app.mount('#app');