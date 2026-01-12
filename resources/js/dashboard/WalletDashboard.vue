<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Wallet Dashboard</h1>

    <!-- Login Form -->
    <div v-if="!isLoggedIn" class="mb-6 max-w-sm">
      <h2 class="font-semibold mb-2">Login</h2>
      <input
        v-model="loginForm.email"
        type="email"
        placeholder="Email"
        class="border p-2 w-full mb-2 rounded"
      />
      <input
        v-model="loginForm.password"
        type="password"
        placeholder="Password"
        class="border p-2 w-full mb-2 rounded"
      />
      <button
        @click="login"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Login
      </button>
      <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>

    <!-- Wallet Info + Transactions -->
    <div v-else>
      <!-- Wallet Info -->
      <div class="bg-white shadow rounded p-4 mb-6">
        <h2 class="font-semibold mb-2">Wallet Info</h2>
        <p><strong>Masked:</strong> {{ wallet.masked }}</p>
        <p><strong>Token:</strong> {{ wallet.token }}</p>
        <p><strong>Balance:</strong> {{ wallet.balance.toFixed(2) }} BDT</p>
      </div>

      <!-- Transactions -->
      <div class="bg-white shadow rounded p-4">
        <h2 class="font-semibold mb-2">Transactions</h2>

        <div v-if="loading" class="text-gray-500">Loading transactions...</div>

        <table v-else class="min-w-full border border-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left">ID</th>
              <th class="px-3 py-2 text-left">Type</th>
              <th class="px-3 py-2 text-left">Amount</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-left">Created At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="trx in transactions" :key="trx.id" class="border-t">
              <td class="px-3 py-2">{{ trx.id }}</td>
              <td class="px-3 py-2 capitalize">{{ trx.type }}</td>
              <td class="px-3 py-2">{{ trx.amount.toFixed(2) }}</td>
              <td class="px-3 py-2 capitalize">{{ trx.status }}</td>
              <td class="px-3 py-2">{{ formatDate(trx.created_at) }}</td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="5" class="text-center px-3 py-2 text-gray-500">
                No transactions yet
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <button
        @click="logout"
        class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
      >
        Logout
      </button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "WalletDashboard",
  data() {
    return {
      loginForm: { email: "", password: "" },
      wallet: {},
      transactions: [],
      isLoggedIn: false,
      loading: false,
      error: "",
    };
  },
  methods: {
    async login() {
      try {
        this.error = "";
        // Get CSRF cookie first
        await axios.get("/sanctum/csrf-cookie", { withCredentials: true });

        // Login API
        await axios.post("/api/v1/login", this.loginForm, {
          withCredentials: true,
        });

        this.isLoggedIn = true;

        // Fetch wallet + transactions
        await this.fetchWallet();
        await this.fetchTransactions();
      } catch (err) {
        console.error(err);
        this.error =
          err.response?.data?.message || "Login failed, check credentials";
      }
    },
    async fetchWallet() {
      try {
        const res = await axios.get("/api/v1/wallet", {
          withCredentials: true,
        });
        this.wallet = res.data.wallet || res.data;
      } catch (err) {
        console.error(err);
      }
    },
    async fetchTransactions() {
      try {
        this.loading = true;
        const res = await axios.get("/api/v1/transactions", {
          withCredentials: true,
        });
        this.transactions = res.data.data || [];
      } catch (err) {
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    logout() {
      this.isLoggedIn = false;
      this.wallet = {};
      this.transactions = [];
    },
    formatDate(date) {
      return new Date(date).toLocaleString();
    },
  },
};
</script>

<style scoped>
/* Optional: scrollable table */
table tbody {
  display: block;
  max-height: 400px;
  overflow-y: auto;
}
table thead,
table tbody tr {
  display: table;
  width: 100%;
  table-layout: fixed;
}
</style>
