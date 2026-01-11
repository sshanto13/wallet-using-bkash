<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestAg Systems | Laravel Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        bkash: {
                            DEFAULT: '#E2136E',
                            dim: 'rgba(226, 19, 110, 0.1)',
                            glow: 'rgba(226, 19, 110, 0.4)',
                        },
                        dark: {
                            bg: '#020617',
                            card: '#0F172A',
                            border: '#1E293B'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #020617; 
            color: #94a3b8;
            background-image: radial-gradient(circle at 50% 0%, rgba(226, 19, 110, 0.15) 0%, transparent 50%);
        }
        
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            border-color: rgba(226, 19, 110, 0.5);
            box-shadow: 0 0 30px -10px rgba(226, 19, 110, 0.3);
            transform: translateY(-2px);
        }

        .step-marker {
            box-shadow: 0 0 0 4px #020617;
        }

        pre { 
            background: #000000 !important; 
            border: 1px solid #1e293b;
            border-radius: 0.75rem;
        }
        
        .highlight-text {
            background: linear-gradient(120deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <main class="flex-grow max-w-5xl mx-auto px-6 py-12 w-full">
        
        <div class="text-center mb-16 relative">
            
            <div class="flex flex-wrap justify-center gap-3 mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 text-slate-300 text-xs font-medium border border-white/10 hover:bg-white/10 transition-colors cursor-default">
                    <span class="text-bkash font-bold">#TestAg Systems</span>
                    <span class="w-px h-3 bg-white/10"></span>
                    <span>Laravel Test</span>
                </div>
                
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-500/10 text-rose-400 text-xs font-medium border border-rose-500/20 hover:bg-rose-500/20 transition-colors cursor-default animate-pulse-slow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Deadline: 2 Days</span>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tight leading-[1.1]">
                Wallet Integration <br/>
                <span class="highlight-text">Architecture</span>
            </h1>
            
            <p class="text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed mb-10">
                Design a resilient wallet system using bKash Tokenized Checkout. 
                Prioritize <span class="text-white font-medium">atomic transactions</span>, <span class="text-white font-medium">idempotency</span>, and <span class="text-white font-medium">automated reconciliation</span>.
            </p>

            <div class="flex flex-col items-center gap-4">
                <a href="https://merchantdemo.sandbox.bka.sh/tokenized-checkout/version/v2" target="_blank" 
                   class="group inline-flex items-center gap-2 bg-white text-black px-6 py-3 rounded-lg font-bold hover:bg-bkash hover:text-white transition-all duration-300">
                    Access Public Sandbox
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
                
                <p class="text-xs text-slate-500 font-medium">
                    <span class="text-rose-400">*</span> You are not restricted to the public sandbox. If you have official bKash API access, you are encouraged to use it.
                </p>
            </div>

            <div class="mt-12 mx-auto max-w-3xl glass-card rounded-2xl p-8 text-left border-l-4 border-l-emerald-500">
                <div class="flex items-center gap-3 mb-6 border-b border-white/5 pb-4">
                    <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-lg">Public Sandbox Credentials</h3>
                        <p class="text-xs text-slate-400">Use these if you do not have your own official API credentials.</p>
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Whitelisted Numbers</div>
                        <div class="font-mono text-sm text-slate-300 space-y-1.5">
                            <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 01929918378</div>
                            <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 01619777283</div>
                            <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 01619777282</div>
                            <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 01823074817</div>
                            <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 01770618575</div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">OTP (Static)</div>
                            <div class="font-mono text-2xl font-bold text-white tracking-[0.2em] bg-white/5 inline-block px-4 py-2 rounded-lg border border-white/10">123456</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">PIN</div>
                            <div class="font-mono text-2xl font-bold text-white tracking-[0.2em] bg-white/5 inline-block px-4 py-2 rounded-lg border border-white/10">12121</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-20">
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-rose-500 font-bold mb-1 group-hover:scale-105 transition-transform">Laravel</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">Core API</div>
            </div>
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-emerald-400 font-bold mb-1 group-hover:scale-105 transition-transform">Vue.js</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">Frontend</div>
            </div>
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-sky-400 font-bold mb-1 group-hover:scale-105 transition-transform">Tailwind</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">UI Kit</div>
            </div>
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-red-500 font-bold mb-1 group-hover:scale-105 transition-transform">Redis</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">Cache & Locks</div>
            </div>
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-blue-400 font-bold mb-1 group-hover:scale-105 transition-transform">MySQL</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">Storage</div>
            </div>
            <div class="glass-card p-5 rounded-xl text-center group">
                <div class="text-green-500 font-bold mb-1 group-hover:scale-105 transition-transform">Nginx</div>
                <div class="text-[10px] uppercase tracking-widest text-slate-500">Web Server</div>
            </div>
        </div>

        <div class="relative border-l border-white/10 ml-4 md:ml-10 pl-8 space-y-16 pb-4">

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 border border-slate-600 text-xs text-slate-400 font-bold">00</span>
                
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                    Identity & Localization
                </h2>
                
                <div class="glass-card p-8 rounded-2xl">
                    <p class="leading-relaxed mb-6">
                        <strong class="text-white">Core User Session.</strong> Implement a secure authentication layer. 
                        Wallet balances and agreement tokens must be strictly scoped to the user.
                    </p>

                    <div class="flex items-start gap-4 p-4 rounded-lg bg-emerald-500/5 border border-emerald-500/10 mb-4">
                        <div class="p-2 bg-emerald-500/10 rounded-md text-emerald-400 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-emerald-400 text-sm mb-1">Localization (i18n) Required</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                The application must support switching between <strong class="text-white">English</strong> and <strong class="text-white">Bangla</strong>. 
                                Use standard <strong>Laravel Localization</strong> (PHP/JSON files). Implement a language toggle and persist preference.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <span class="text-xs font-mono bg-white/5 px-2 py-1 rounded text-slate-400">LocalStorage</span>
                        <span class="text-xs font-mono bg-white/5 px-2 py-1 rounded text-slate-400">Sanctum/Passport</span>
                        <span class="text-xs font-mono bg-white/5 px-2 py-1 rounded text-slate-400">Laravel Localization</span>
                    </div>
                </div>
            </section>

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-bkash border border-bkash text-xs text-white font-bold">01</span>
                
                <h2 class="text-2xl font-bold text-white mb-6">Agreement Binding</h2>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <p class="text-slate-400">
                            Execute <code class="text-bkash bg-bkash/10 px-1 rounded">createAgreement</code> to generate the binding flow. 
                            Upon success, store the returned <code class="text-white">agreementId</code> permanently.
                        </p>
                        <div class="text-xs text-slate-500 italic border-l-2 border-slate-700 pl-3">
                            "This ID is the master key for future charges. Encrypt it at rest."
                        </div>
                    </div>
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-bkash to-purple-600 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <pre class="relative font-mono text-xs text-slate-300 p-4 overflow-x-auto">
// Store the Agreement
$wallet = Wallet::create([
    'user_id' => $user->id,
    'token'   => $response['agreementId'], 
    'masked'  => $response['payerAccount']
]);</pre>
                    </div>
                </div>
            </section>

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-bkash border border-bkash text-xs text-white font-bold">02</span>
                
                <h2 class="text-2xl font-bold text-white mb-6">Balance Injection</h2>
                
                <div class="glass-card p-8 rounded-2xl mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-bold text-white text-lg">Payment w/ Agreement</h3>
                            <p class="text-sm text-slate-500">Charge the user without OTP re-entry.</p>
                        </div>
                        <code class="text-xs bg-black/50 px-3 py-2 rounded border border-white/10 text-emerald-400 font-mono">
                            POST /tokenized-checkout/payment-with-agreement/create
                        </code>
                    </div>

                    <div class="bg-orange-500/10 border border-orange-500/20 p-4 rounded-lg flex gap-4 items-start">
                        <div class="mt-1 text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-orange-400 uppercase tracking-wide mb-1">Critical: Flow & Lock Strategy</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Use <strong class="text-white">Redis</strong> to implement an atomic lock (<code>setnx</code>) during the payment process. 
                                Prevent double-submissions (e.g., user clicking "Pay" twice).
                                <br/>
                                Handle <span class="text-white">success</span>, <span class="text-white">failure</span>, and <span class="text-white">cancel</span> redirects gracefully.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-bkash border border-bkash text-xs text-white font-bold">03</span>
                
                <h2 class="text-2xl font-bold text-white mb-6">Refund Logic</h2>
                
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div class="glass-card p-6 rounded-2xl">
                        <p class="text-sm text-slate-400 mb-0">
                            Refunds require the original <code class="text-white">trxId</code> and <code class="text-white">paymentId</code>. 
                            The system must support partial refunds and deduct the amount from the internal <strong class="text-white">wallet records</strong>.
                        </p>
                    </div>
                    <pre class="font-mono text-xs text-slate-300 p-4">
$payload = [
    'paymentId'    => $pId,
    'trxId'        => $trx,
    'amount'       => '50.00',
    'reason'       => 'User request'
];</pre>
                </div>
            </section>

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 border border-slate-600 text-xs text-slate-400 font-bold">04</span>
                
                <h2 class="text-2xl font-bold text-white mb-6">History & Statements</h2>
                
                <div class="glass-card p-8 rounded-2xl border-l-4 border-l-blue-500">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="space-y-6">
                            <div>
                                <h3 class="font-bold text-white mb-1 flex items-center gap-2">
                                    <span class="text-blue-500">01.</span> Transaction History UI
                                </h3>
                                <p class="text-sm text-slate-400 leading-relaxed">
                                    Build a comprehensive, paginated view listing all wallet activities (Credits, Debits, Refunds). 
                                    Users must be able to view their balance history directly on the dashboard.
                                </p>
                            </div>
                            <div>
                                <h3 class="font-bold text-white mb-1 flex items-center gap-2">
                                    <span class="text-blue-500">02.</span> PDF Statement Action
                                </h3>
                                <p class="text-sm text-slate-400 leading-relaxed">
                                    Implement a <strong class="text-white">"Download Statement"</strong> button within the history view. 
                                    This action must trigger the <strong class="text-white">Gotenberg</strong> microservice to render the <strong class="text-white">transaction data</strong> into a high-fidelity PDF file.
                                </p>
                            </div>
                        </div>
                        <div class="text-right hidden md:block shrink-0 bg-blue-950/20 p-4 rounded-lg border border-blue-500/10">
                            <div class="text-xs font-mono text-slate-500 mb-1">REQUIRED SERVICE</div>
                            <div class="text-lg font-bold text-white tracking-wide">GOTENBERG</div>
                            <div class="text-[10px] text-rose-400 mt-2 font-mono border-t border-white/5 pt-2">NO DOMPDF ALLOWED</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative">
                <span class="step-marker absolute -left-[41px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 border border-indigo-500 text-xs text-white font-bold">05</span>
                
                <h2 class="text-2xl font-bold text-white mb-6">Deployment & Infrastructure</h2>
                
                <div class="bg-indigo-950/30 border border-indigo-500/20 p-8 rounded-2xl relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>

                    <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-4">Flexible Infrastructure</h3>
                            <p class="text-slate-400 text-sm leading-relaxed max-w-xl">
                                We focus on results. While <strong class="text-indigo-400">Redis</strong> and <strong class="text-indigo-400">Gotenberg</strong> must run via Docker (as they are microservices), the core Application, Web Server, and Database can be dockerized <em class="text-white">OR</em> set up locally/on a server.
                            </p>
                        </div>
                        
                        <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-lg shrink-0">
                            <h4 class="text-emerald-400 font-bold text-xs uppercase tracking-widest mb-1">Top Priority</h4>
                            <div class="text-white font-bold">Working Live Demo</div>
                            <div class="text-[10px] text-slate-400 mt-1">Hosting method irrelevant.<br>Must be testable via URL.</div>
                        </div>
                    </div>

                    <div class="bg-black/40 rounded-lg p-5 font-mono text-xs border border-white/5 space-y-3">
                        <div class="flex gap-2 text-slate-500 font-bold border-b border-white/5 pb-2 mb-2">
                            <span>#</span> Service Requirements
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div class="space-y-3">
                                <div class="text-[10px] uppercase tracking-widest text-indigo-400 font-bold mb-2">Docker Mandatory</div>
                                <div class="flex items-center gap-2 text-white">
                                    <span class="text-indigo-500">⦿</span> Redis (Cache/Locks)
                                </div>
                                <div class="flex items-center gap-2 text-white">
                                    <span class="text-indigo-500">⦿</span> Gotenberg (PDF)
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-2">Flexible (Docker or Self-Hosted)</div>
                                <div class="flex items-center gap-2 text-slate-300">
                                    <span class="text-slate-600">⦿</span> Laravel App (PHP 8.2)
                                </div>
                                <div class="flex items-center gap-2 text-slate-300">
                                    <span class="text-slate-600">⦿</span> MySQL 8.0
                                </div>
                                <div class="flex items-center gap-2 text-slate-300">
                                    <span class="text-slate-600">⦿</span> Nginx / Apache
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <div class="mt-20 border-t border-white/5 pt-12">
            <h3 class="text-xl font-bold text-white mb-8 text-center">Submission Protocol</h3>

            <div class="max-w-4xl mx-auto mb-12 p-5 rounded-xl bg-gradient-to-r from-rose-950/30 to-transparent border border-rose-500/20 flex items-start gap-4 backdrop-blur-sm">
                <div class="p-3 bg-rose-500/10 rounded-lg text-rose-500 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-rose-400 text-sm uppercase tracking-wider mb-2">Authenticity & AI Tools</h4>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        We understand AI tools (ChatGPT, Copilot) are part of modern development. You are free to use them for assistance, but <strong class="text-white">do not let them write the core logic for you</strong>. 
                        The interview will focus heavily on <em>why</em> you structured the code the way you did. 
                        <span class="block mt-2 text-rose-400 font-medium">You must be able to explain every line of your implementation. Code you cannot explain is effectively code you didn't write.</span>
                    </p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="p-6 rounded-xl bg-white/5 border border-white/5">
                    <h4 class="font-bold text-white mb-2">Public Repository</h4>
                    <p class="text-xs text-slate-400">Host on GitHub. Ensure <code>.gitignore</code> is properly configured (no vendor/node_modules).</p>
                </div>
                <div class="p-6 rounded-xl bg-white/5 border border-white/5">
                    <h4 class="font-bold text-white mb-2">Clean Commit Log</h4>
                    <p class="text-xs text-slate-400">Atomic commits with descriptive messages. No "WIP" or "Fixed bug" spam.</p>
                </div>
                <div class="p-6 rounded-xl bg-white/5 border border-white/5">
                    <h4 class="font-bold text-white mb-2">Documentation</h4>
                    <p class="text-xs text-slate-400">Comprehensive README.md detailing architecture choices and startup instructions.</p>
                </div>
            </div>
            <div class="text-center mt-12 text-xs font-mono text-slate-600 uppercase tracking-widest">
                Copyright © 1995 - 2026 TestAg Systems PLC. All Right Reserved.
            </div>
        </div>

    </main>

</body>
</html>