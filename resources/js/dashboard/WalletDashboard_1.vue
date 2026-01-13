<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Toast Notifications -->
      <div
        v-if="toast.show"
        :class="[
          'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 min-w-[300px] max-w-md animate-slide-in',
          toast.type === 'success' ? 'bg-green-600' : toast.type === 'error' ? 'bg-red-600' : 'bg-blue-600'
        ]"
      >
        <span class="text-white font-medium">{{ toast.message }}</span>
        <button @click="toast.show = false" class="ml-auto text-white hover:text-gray-200">×</button>
      </div>

      <!-- Header -->
      <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <h1 class="text-3xl sm:text-4xl font-bold text-white">{{ t('app.name') }}</h1>
        <div class="flex items-center gap-3 flex-shrink-0">
          <!-- Language Switcher - Always visible and prominent -->
          <div class="flex-shrink-0">
            <language-switcher @language-changed="handleLanguageChange"></language-switcher>
          </div>
          
          <!-- User info and logout - Only when logged in -->
          <template v-if="isLoggedIn">
            <div class="text-right hidden sm:block">
              <p class="text-white font-medium text-sm">{{ user?.name || user?.email }}</p>
              <p class="text-xs text-gray-400">{{ t('auth.logged_in') }}</p>
            </div>
            <button
              @click="handleLogout"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span class="hidden sm:inline">{{ t('auth.logout') }}</span>
            </button>
          </template>
        </div>
      </div>

    <!-- Login Form -->
      <div v-if="!isLoggedIn" class="max-w-md mx-auto">
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 shadow-2xl">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600/20 rounded-full mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">{{ t('app.welcome') }}</h2>
            <p class="text-gray-400 text-sm">{{ t('app.sign_in') }}</p>
          </div>
          <form @submit.prevent="login" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('auth.email') }}</label>
      <input
        v-model="loginForm.email"
        type="email"
                :placeholder="t('auth.email')"
                required
                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
      />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('auth.password') }}</label>
      <input
        v-model="loginForm.password"
        type="password"
                :placeholder="t('auth.password')"
                required
                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
      />
            </div>
      <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-3 rounded-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ t('auth.logging_in') }}
              </span>
              <span v-else>{{ t('auth.login') }}</span>
      </button>
            <p v-if="error" class="text-red-400 text-sm text-center mt-2 bg-red-500/10 border border-red-500/20 rounded-lg p-2">{{ error }}</p>
          </form>
    </div>
      </div>

      <!-- Wallet Dashboard (Authenticated) -->
      <div v-else class="space-y-6">
        <!-- Wallet Info Card -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 shadow-2xl">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h2 class="text-2xl font-bold text-white">{{ t('wallet.wallet_information') }}</h2>
              <p class="text-sm text-gray-400 mt-1">{{ t('wallet.manage_wallet') }}</p>
            </div>
            <div class="flex gap-2">
              <button
                v-if="wallet"
                @click="fetchWallet"
                :disabled="loadingWallet"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
                :title="t('wallet.refresh_wallet')"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'animate-spin': loadingWallet }">
                  <polyline points="23 4 23 10 17 10"></polyline>
                  <polyline points="1 20 1 14 7 14"></polyline>
                  <path d="M3.51 9a9 9 0 0 1 14.85-3.7L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                {{ t('wallet.refresh') }}
              </button>
              <button
                v-if="!wallet || !wallet.id"
                @click="bindWallet"
                :disabled="bindingWallet"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
              >
                <svg v-if="bindingWallet" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                  <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                  <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                </svg>
                <span v-if="bindingWallet">{{ t('wallet.binding') }}</span>
                <span v-else>{{ t('wallet.bind_wallet') }}</span>
              </button>
            </div>
          </div>

          <div v-if="loadingWallet" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
          </div>
          <div v-else-if="wallet && wallet.id" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white/5 rounded-lg p-5 border border-white/10 hover:border-purple-500/50 transition-colors">
              <div class="flex items-center gap-2 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <p class="text-sm text-gray-400">{{ t('wallet.masked_account') }}</p>
              </div>
              <p class="text-lg font-semibold text-white">{{ wallet.masked || 'N/A' }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-lg p-5 border border-green-500/30">
              <div class="flex items-center gap-2 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400">
                  <line x1="12" y1="1" x2="12" y2="23"></line>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <p class="text-sm text-gray-300">{{ t('wallet.balance') }}</p>
              </div>
              <p class="text-3xl font-bold text-green-400">{{ formatCurrency(wallet.balance || 0) }}</p>
            </div>
            <div class="bg-white/5 rounded-lg p-5 border border-white/10 hover:border-purple-500/50 transition-colors">
              <div class="flex items-center gap-2 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <p class="text-sm text-gray-400">{{ t('wallet.status') }}</p>
              </div>
              <p class="text-lg font-semibold text-white">
                <span :class="wallet.token ? 'text-green-400' : 'text-yellow-400'">
                  {{ wallet.token ? t('wallet.active') : t('wallet.not_bound') }}
                </span>
              </p>
            </div>
          </div>
          <div v-else class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 mx-auto mb-4">
              <rect x="2" y="6" width="20" height="12" rx="2"></rect>
              <path d="M12 12h.01"></path>
            </svg>
            <p class="text-gray-400 text-lg mb-2">{{ t('wallet.no_wallet') }}</p>
            <p class="text-gray-500 text-sm">{{ t('wallet.bind_to_start') }}</p>
          </div>
        </div>

        <!-- Top Up Section -->
        <div v-if="wallet && wallet.id" class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 shadow-2xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-blue-500/20 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white">{{ t('topup.top_up_wallet') }}</h2>
              <p class="text-sm text-gray-400">{{ t('topup.add_funds') }}</p>
            </div>
          </div>
          <form @submit.prevent="handleTopUp" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <input
                v-model.number="topUpForm.amount"
                type="number"
                min="10"
                step="0.01"
                :placeholder="t('topup.enter_amount')"
                required
                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
              />
            </div>
            <button
              type="submit"
              :disabled="toppingUp || !topUpForm.amount || topUpForm.amount < 10"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 font-semibold"
            >
              <svg v-if="toppingUp" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              <span v-if="toppingUp">{{ t('topup.processing') }}</span>
              <span v-else>{{ t('topup.top_up') }}</span>
            </button>
          </form>
        </div>

        <!-- Transactions Section -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 shadow-2xl">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h2 class="text-2xl font-bold text-white">{{ t('transactions.transactions') }}</h2>
              <p class="text-sm text-gray-400 mt-1">{{ t('transactions.view_history') }}</p>
            </div>
            <button
              @click="fetchTransactions(currentPage)"
              :disabled="loadingTransactions"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors text-sm flex items-center gap-2 disabled:opacity-50"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'animate-spin': loadingTransactions }">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.7L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
              </svg>
              {{ t('wallet.refresh') }}
            </button>
          </div>

          <!-- Filters -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <select
              v-model="filters.type"
              @change="fetchTransactions(1)"
              class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
            >
              <option value="">{{ t('transactions.all_types') }}</option>
              <option value="credit">{{ t('transactions.type') }}: Credit</option>
              <option value="debit">{{ t('transactions.type') }}: Debit</option>
              <option value="refund">{{ t('transactions.type') }}: Refund</option>
            </select>
            <select
              v-model="filters.status"
              @change="fetchTransactions(1)"
              class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
            >
              <option value="">{{ t('transactions.all_status') }}</option>
              <option value="pending">{{ t('transactions.status') }}: Pending</option>
              <option value="success">{{ t('transactions.status') }}: Success</option>
              <option value="failed">{{ t('transactions.status') }}: Failed</option>
            </select>
            <input
              v-model="filters.from_date"
              type="date"
              @change="fetchTransactions(1)"
              class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
              :placeholder="t('transactions.from_date')"
            />
            <input
              v-model="filters.to_date"
              type="date"
              @change="fetchTransactions(1)"
              class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
              :placeholder="t('transactions.to_date')"
            />
          </div>

          <!-- Transactions Table -->
          <div v-if="loadingTransactions" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
          </div>
          <div v-else-if="transactions.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 mx-auto mb-4">
              <line x1="12" y1="2" x2="12" y2="6"></line>
              <line x1="12" y1="18" x2="12" y2="22"></line>
              <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
              <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
              <line x1="2" y1="12" x2="6" y2="12"></line>
              <line x1="18" y1="12" x2="22" y2="12"></line>
              <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
              <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
            </svg>
            <p class="text-gray-400 text-lg mb-2">{{ t('transactions.no_transactions') }}</p>
            <p class="text-gray-500 text-sm">{{ t('transactions.adjust_filters') }}</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-white/5 border-b border-white/10">
                <tr>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.id') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.type') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.amount') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.status') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.payment_id') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.date') }}</th>
                  <th class="px-4 py-3 text-sm font-semibold text-gray-300">{{ t('transactions.actions') }}</th>
            </tr>
          </thead>
          <tbody>
                <tr
                  v-for="trx in transactions"
                  :key="trx.id"
                  class="border-b border-white/10 hover:bg-white/5 transition-colors"
                >
                  <td class="px-4 py-3 text-white font-mono text-sm">#{{ trx.id }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="{
                        'bg-green-500/20 text-green-400 border-green-500/30': trx.type === 'credit',
                        'bg-red-500/20 text-red-400 border-red-500/30': trx.type === 'debit',
                        'bg-yellow-500/20 text-yellow-400 border-yellow-500/30': trx.type === 'refund',
                      }"
                      class="px-3 py-1 rounded-full text-xs font-semibold capitalize border"
                    >
                      {{ trx.type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-white font-semibold">{{ formatCurrency(trx.amount) }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="{
                        'bg-yellow-500/20 text-yellow-400 border-yellow-500/30': trx.status === 'pending',
                        'bg-green-500/20 text-green-400 border-green-500/30': trx.status === 'success',
                        'bg-red-500/20 text-red-400 border-red-500/30': trx.status === 'failed',
                      }"
                      class="px-3 py-1 rounded-full text-xs font-semibold capitalize border"
                    >
                      {{ trx.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-white text-sm font-mono">{{ trx.payment_id || 'N/A' }}</td>
                  <td class="px-4 py-3 text-gray-400 text-sm">{{ formatDate(trx.created_at) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <button
                        v-if="trx.type === 'credit' && trx.status === 'success'"
                        @click="openRefundModal(trx)"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded text-xs transition-colors flex items-center gap-1"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                          <path d="M21 3v5h-5"></path>
                          <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.63-2.8L3 16"></path>
                        </svg>
                        {{ t('transactions.refund') }}
                      </button>
                      <button
                        v-if="trx.type === 'credit' && trx.status === 'pending' && trx.payment_id"
                        @click="checkPaymentStatus(trx.payment_id)"
                        :disabled="checkingPayment"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs transition-colors flex items-center gap-1 disabled:opacity-50"
                      >
                        <svg v-if="checkingPayment" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                          <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        {{ t('transactions.check_status') }}
                      </button>
                    </div>
              </td>
            </tr>
          </tbody>
        </table>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-6">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ t('transactions.previous') }}
              </button>
              <span class="text-white px-4">
                {{ t('transactions.page') }} {{ pagination.current_page }} {{ t('transactions.of') }} {{ pagination.last_page }}
              </span>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ t('transactions.next') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Refund Modal -->
      <div
        v-if="showRefundModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="closeRefundModal"
      >
        <div class="bg-slate-800 rounded-2xl p-6 max-w-md w-full mx-4 border border-white/20 shadow-2xl">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-white">{{ t('refund.process_refund') }}</h3>
            <button @click="closeRefundModal" class="text-gray-400 hover:text-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <form @submit.prevent="handleRefund" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('refund.original_amount') }}</label>
              <input
                :value="formatCurrency(selectedTransaction?.amount || 0)"
                disabled
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-gray-400"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('refund.refund_amount') }}</label>
              <input
                v-model.number="refundForm.amount"
                type="number"
                :max="selectedTransaction?.amount"
                min="1"
                step="0.01"
                required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
              />
              <p class="text-xs text-gray-400 mt-1">{{ t('refund.maximum') }}: {{ formatCurrency(selectedTransaction?.amount || 0) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('refund.reason') }}</label>
              <textarea
                v-model="refundForm.reason"
                required
                rows="3"
                :placeholder="t('refund.enter_reason')"
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
              ></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button
                type="button"
                @click="closeRefundModal"
                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors"
              >
                {{ t('refund.cancel') }}
              </button>
              <button
                type="submit"
                :disabled="processingRefund"
                class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <svg v-if="processingRefund" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="processingRefund">{{ t('refund.processing') }}</span>
                <span v-else>{{ t('refund.process') }}</span>
      </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../services/api';
import i18n from '../services/i18n';

export default {
  name: 'WalletDashboard',
  data() {
    return {
      // Login
      loginForm: { email: '', password: '' },
      isLoggedIn: false,
      user: null,
      loading: false,
      error: '',

      // Wallet
      wallet: null,
      loadingWallet: false,
      bindingWallet: false,

      // Top Up
      topUpForm: { amount: null },
      toppingUp: false,

      // Transactions
      transactions: [],
      loadingTransactions: false,
      filters: {
        type: '',
        status: '',
        from_date: '',
        to_date: '',
      },
      pagination: null,
      currentPage: 1,

      // Refund
      showRefundModal: false,
      selectedTransaction: null,
      refundForm: {
        amount: null,
        reason: '',
      },
      processingRefund: false,

      // Toast
      toast: {
        show: false,
        message: '',
        type: 'success', // success, error, info
      },
      
      // i18n
      currentLanguage: 'en',
    };
  },
  mounted() {
    this.checkAuth();
    this.currentLanguage = i18n.getLanguage();
    
    // Listen for language changes
    window.addEventListener('languageChanged', this.handleLanguageChange);
    
    // Listen for wallet binding success from popup
    window.addEventListener('message', this.handleWalletBindingMessage);
    
    // Load translations on mount
    i18n.loadTranslations();
  },
  beforeUnmount() {
    window.removeEventListener('languageChanged', this.handleLanguageChange);
    window.removeEventListener('message', this.handleWalletBindingMessage);
  },
  methods: {
    // Translation helper
    t(key, params = {}) {
      return i18n.t(key, params);
    },
    handleLanguageChange(event) {
      this.currentLanguage = event.detail.language;
      // Force re-render by updating a reactive property
      this.$forceUpdate();
    },
    
    // Handle wallet binding success message from popup
    handleWalletBindingMessage(event) {
      // Security: Only accept messages from same origin
      if (event.origin !== window.location.origin) {
        return;
      }
      
      if (event.data && event.data.type === 'wallet-bound-success') {
        // Wallet binding successful, refresh wallet data
        this.showToast(event.data.message || this.t('wallet.bind_initiated'), 'success');
        
        // Refresh wallet after a short delay to ensure backend has processed
        setTimeout(() => {
          this.fetchWallet();
        }, 1000);
      } else if (event.data && event.data.type === 'wallet-bound-cancelled') {
        // Wallet binding cancelled
        this.showToast(event.data.message || 'Agreement cancelled', 'error');
      }
    },
    
    // Toast notification
    showToast(message, type = 'success') {
      this.toast = { show: true, message, type };
      setTimeout(() => {
        this.toast.show = false;
      }, 5000);
    },

    // Authentication
    async checkAuth() {
      const token = localStorage.getItem('auth_token');
      const userStr = localStorage.getItem('user');

      if (token && userStr) {
        try {
          this.user = JSON.parse(userStr);
          this.isLoggedIn = true;
          await this.fetchWallet();
          await this.fetchTransactions();
        } catch (error) {
          console.error('Error checking auth:', error);
          this.logout();
        }
      }
    },

    async login() {
      try {
        this.error = '';
        this.loading = true;

        const response = await api.post('/login', this.loginForm);

        if (response.data.token) {
          localStorage.setItem('auth_token', response.data.token);
          localStorage.setItem('user', JSON.stringify(response.data.user));
          this.user = response.data.user;
        this.isLoggedIn = true;

          this.showToast(this.t('auth.login_successful'), 'success');

          // Fetch wallet and transactions
        await this.fetchWallet();
        await this.fetchTransactions();
        }
      } catch (err) {
        console.error('Login error:', err);
        this.error = err.response?.data?.message || this.t('auth.login_failed');
        this.showToast(this.error, 'error');
      } finally {
        this.loading = false;
      }
    },

    async handleLogout() {
      try {
        await api.post('/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.logout();
        this.showToast(this.t('auth.logged_out'), 'info');
      }
    },

    logout() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      this.isLoggedIn = false;
      this.user = null;
      this.wallet = null;
      this.transactions = [];
    },

    // Wallet Operations
    async fetchWallet() {
      try {
        this.loadingWallet = true;
        const response = await api.get('/wallet');
        
        // Handle null or empty response
        if (!response.data || !response.data.id) {
          this.wallet = null;
        } else {
          this.wallet = response.data;
        }
      } catch (err) {
        console.error('Fetch wallet error:', err);
        if (err.response?.status === 404) {
          this.wallet = null;
        } else {
          // Don't show error toast for 404 (no wallet is normal)
          if (err.response?.status !== 404) {
            this.showToast(this.t('wallet.load_failed'), 'error');
          }
          this.wallet = null;
        }
      } finally {
        this.loadingWallet = false;
      }
    },

    async bindWallet() {
      try {
        this.bindingWallet = true;
        const response = await api.post('/wallet/bind');
        
        // If response contains a redirect URL, open it in a new window
        if (response.data.bkashURL) {
          // Open bKash popup for agreement
          const popup = window.open(
            response.data.bkashURL,
            'bKashAgreement',
            'width=500,height=600,scrollbars=yes,resizable=yes'
          );
          
          // Monitor popup for closure
          const checkPopup = setInterval(() => {
            if (popup.closed) {
              clearInterval(checkPopup);
              // Popup closed, refresh wallet to check if binding was successful
              setTimeout(() => {
                this.fetchWallet();
              }, 1000);
            }
          }, 500);
          
          this.showToast(this.t('wallet.bind_complete_popup'), 'info');
        } else {
          this.showToast(this.t('wallet.bind_initiated'), 'info');
        }
      } catch (err) {
        console.error('Bind wallet error:', err);
        const errorMsg = err.response?.data?.message || this.t('wallet.bind_failed');
        this.showToast(errorMsg, 'error');
      } finally {
        this.bindingWallet = false;
      }
    },

    async handleTopUp() {
        if (!this.topUpForm.amount || this.topUpForm.amount < 10) {
        this.showToast(this.t('topup.min_amount'), 'error');
        return;
      }

      try {
        this.toppingUp = true;
        const response = await api.post('/wallet/topup', {
          amount: this.topUpForm.amount,
        });

        // Payment processed in background - update UI
        if (response.data.message === 'Top-up successful') {
          this.showToast(this.t('topup.successful'), 'success');
          this.topUpForm.amount = null;
          
          // Refresh wallet and transactions
          await Promise.all([
            this.fetchWallet(),
            this.fetchTransactions(this.currentPage)
          ]);
        } else {
          this.showToast('Top-up processed', 'info');
        }
      } catch (err) {
        console.error('Top up error:', err);
        const errorMsg = err.response?.data?.message || 'Failed to process top-up';
        this.showToast(errorMsg, 'error');
        
        // Refresh transactions to show failed status
        await this.fetchTransactions(this.currentPage);
      } finally {
        this.toppingUp = false;
      }
    },

    async checkPaymentStatus(paymentId) {
      if (!paymentId || this.checkingPayment) return;

      try {
        this.checkingPayment = true;
        this.showToast(this.t('payment.checking_status'), 'info');

        const response = await api.post('/wallet/payment/check', {
          payment_id: paymentId,
        });

        if (response.data.status === 'success') {
          this.showToast(this.t('payment.successful'), 'success');
          // Refresh wallet and transactions
          await Promise.all([
            this.fetchWallet(),
            this.fetchTransactions(this.currentPage)
          ]);
        } else if (response.data.status === 'failed') {
          this.showToast(this.t('payment.failed') + ': ' + (response.data.message || 'Unknown error'), 'error');
          // Refresh transactions to show failed status
          await this.fetchTransactions(this.currentPage);
        } else {
          // Still processing, check again after a delay
          setTimeout(() => {
            this.checkPaymentStatus(paymentId);
          }, 3000);
        }
      } catch (err) {
        console.error('Check payment status error:', err);
        const errorMsg = err.response?.data?.message || 'Failed to check payment status';
        
        // If transaction not found, it might still be processing
        if (err.response?.status === 404) {
          this.showToast(this.t('payment.still_processing'), 'info');
          setTimeout(() => {
            this.checkPaymentStatus(paymentId);
          }, 3000);
        } else {
          this.showToast(errorMsg, 'error');
        }
      } finally {
        this.checkingPayment = false;
      }
    },

    // Transactions
    async fetchTransactions(page = 1) {
      try {
        this.loadingTransactions = true;
        this.currentPage = page;

        const params = {
          page,
          per_page: 10,
        };

        if (this.filters.type) params.type = this.filters.type;
        if (this.filters.status) params.status = this.filters.status;
        if (this.filters.from_date) params.from_date = this.filters.from_date;
        if (this.filters.to_date) params.to_date = this.filters.to_date;

        const response = await api.get('/wallet/transactions', { params });
        this.transactions = response.data.data || [];
        this.pagination = response.data.meta || null;
      } catch (err) {
        console.error('Fetch transactions error:', err);
      this.transactions = [];
        this.showToast(this.t('common.error'), 'error');
      } finally {
        this.loadingTransactions = false;
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchTransactions(page);
      }
    },

    // Refund
    openRefundModal(transaction) {
      this.selectedTransaction = transaction;
      this.refundForm.amount = transaction.amount;
      this.refundForm.reason = '';
      this.showRefundModal = true;
    },

    closeRefundModal() {
      this.showRefundModal = false;
      this.selectedTransaction = null;
      this.refundForm = { amount: null, reason: '' };
    },

    async handleRefund() {
      if (!this.selectedTransaction) return;

      if (this.refundForm.amount > this.selectedTransaction.amount) {
        this.showToast(this.t('refund.amount_exceeded'), 'error');
        return;
      }

      if (!this.refundForm.reason || this.refundForm.reason.trim() === '') {
        this.showToast(this.t('refund.reason_required'), 'error');
        return;
      }

      try {
        this.processingRefund = true;
        await api.post('/wallet/refund', {
          transaction_id: this.selectedTransaction.id,
          amount: this.refundForm.amount,
          reason: this.refundForm.reason,
        });

        this.showToast(this.t('refund.successful'), 'success');
        this.closeRefundModal();
        await this.fetchWallet(); // Refresh wallet balance
        await this.fetchTransactions(this.currentPage); // Refresh transactions
      } catch (err) {
        console.error('Refund error:', err);
        const errorMsg = err.response?.data?.message || 'Failed to process refund';
        this.showToast(errorMsg, 'error');
      } finally {
        this.processingRefund = false;
      }
    },

    // Utilities
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
      }).format(amount || 0);
    },

    formatDate(date) {
      return new Date(date).toLocaleString('en-BD', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
};
</script>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Toast animation */
@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style>
