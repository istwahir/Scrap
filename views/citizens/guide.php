<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Guide - Kiambu Recycling & Scraps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 45px -25px rgba(15, 118, 110, 0.7);
        }
        .hero-gradient {
            background: radial-gradient(120% 120% at 50% 0%, rgba(16, 185, 129, 0.25) 0%, transparent 60%),
                        linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #020617 100%);
        }
        .grid-fade {
            background-image: linear-gradient(rgba(99, 102, 241, 0.12) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(56, 189, 248, 0.12) 1px, transparent 1px);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <header class="hero-gradient relative overflow-hidden pb-20">
        <div class="absolute inset-0 grid-fade opacity-20"></div>
        <div class="relative z-10 mx-auto max-w-6xl px-6">
            <div class="grid gap-10 pt-24 lg:grid-cols-[1.15fr,0.85fr] lg:items-center">
                <div class="space-y-10">
                    <div class="inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-1 text-sm text-emerald-200">
                        Recycling playbook • updated weekly
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Master recycling in Kiambu like a pro.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg text-emerald-100/80">
                            Learn how to sort, schedule, and get rewarded for responsible recycling. Explore material guidelines,
                            interactive tips, and answers to the questions our community asks the most.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <button id="openVideo" class="group inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition hover:scale-[1.02]">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-500/10 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white">
                                ▶
                            </span>
                            Watch how it works (90s)
                        </button>
                        <a href="#steps" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-emerald-300/60">
                            View quick-start steps
                            <span aria-hidden="true">↓</span>
                        </a>
                    </div>
                </div>

                <div class="glass-card relative overflow-hidden rounded-3xl p-8">
                    <div class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-emerald-400/20 blur-3xl"></div>
                    <div class="absolute -bottom-8 right-0 h-36 w-36 rounded-full bg-sky-400/20 blur-3xl"></div>
                    <div class="relative grid gap-6">
                        <div class="text-xs uppercase tracking-[0.35em] text-emerald-200/70">Impact snapshot</div>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="space-y-2">
                                <div class="text-3xl font-semibold" data-counter data-target="12845">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Pickups completed</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl font-semibold" data-counter data-target="276">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Tonnes diverted</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl font-semibold" data-counter data-target="5420">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Active recyclers</div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-emerald-100/80">
                            “I schedule pickups in seconds and watch my rewards grow. The tips here helped us reduce our household waste by 45%.”
                            <span class="text-white font-medium">— Carol, Runda</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="relative z-10 -mt-12 space-y-20 pb-24">
        <section id="steps" class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white sm:text-3xl">Your 4-step playbook</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Follow these core steps every time you recycle. Expand each card to reveal pro tips and quick wins shared by our top contributors.
                        </p>
                    </div>
                    <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Average completion time · 18 minutes</div>
                </div>

                <div class="mt-10 grid gap-5 lg:grid-cols-4">
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">Step 01</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Sort with color codes</h3>
                        <p class="mt-3 text-sm text-slate-300">
                            Use reusable bags or crates labeled Plastic, Paper, Metal, Glass, and E-waste. Snap a photo once sorted.
                        </p>
                        <details class="mt-5 text-sm text-emerald-100/80">
                            <summary class="cursor-pointer text-emerald-300">Pro tip</summary>
                            Keep a “confused bin” for items you’re unsure about. Consult the materials filter below before collection day.
                        </details>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">Step 02</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Prep & flatten</h3>
                        <p class="mt-3 text-sm text-slate-300">
                            Rinse containers, remove labels where possible, and flatten boxes to maximize collector space.
                        </p>
                        <details class="mt-5 text-sm text-emerald-100/80">
                            <summary class="cursor-pointer text-emerald-300">Pro tip</summary>
                            Microwave stubborn labels for 20 seconds before peeling. It loosens glue and saves time.
                        </details>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">Step 03</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Schedule pickup</h3>
                        <p class="mt-3 text-sm text-slate-300">
                            Open the dashboard, choose “Request collection”, confirm weight & photos, then select a time slot.
                        </p>
                        <details class="mt-5 text-sm text-emerald-100/80">
                            <summary class="cursor-pointer text-emerald-300">Pro tip</summary>
                            Morning slots fill faster. Book 24 hours ahead or set a recurring pickup to lock your favourite window.
                        </details>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">Step 04</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Track rewards</h3>
                        <p class="mt-3 text-sm text-slate-300">
                            Points land instantly after a completed pickup. Redeem for cash, airtime, or eco-goodies.
                        </p>
                        <details class="mt-5 text-sm text-emerald-100/80">
                            <summary class="cursor-pointer text-emerald-300">Pro tip</summary>
                            Boost your total by joining community drives or inviting neighbours with your referral link.
                        </details>
                    </article>
                </div>
            </div>
        </section>

        <section id="materials" class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white sm:text-3xl">What goes where?</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Filter by material category and see what we accept, what we don’t, and the preparation tips our collectors recommend.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2" id="materialFilter"></div>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3" id="materialCards"></div>

                <div class="mt-10 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Still unsure about an item?</h3>
                            <p class="text-sm text-emerald-100/80">Pick a material type to instantly see how to prepare it.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <select id="materialLookup" class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-2 text-sm text-white focus:border-emerald-300 focus:outline-none">
                                <option value="">Choose a material…</option>
                            </select>
                            <div id="lookupResult" class="text-sm text-emerald-100/90 sm:max-w-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tips" class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white sm:text-3xl">Eco habits that stick</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Rotate through daily, weekly, and creative eco challenges. Tap the arrows or let the carousel autoplay every 8 seconds.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="previousTips" class="grid h-10 w-10 place-items-center rounded-full border border-white/20 text-white transition hover:border-emerald-300/60">←</button>
                        <button id="nextTips" class="grid h-10 w-10 place-items-center rounded-full border border-white/20 text-white transition hover:border-emerald-300/60">→</button>
                    </div>
                </div>

                <div class="mt-10 overflow-hidden">
                    <div id="tipsCarousel" class="grid gap-6 transition-transform duration-500 ease-out sm:grid-cols-3"></div>
                </div>

                <div id="tipsDots" class="mt-6 flex justify-center gap-2"></div>
            </div>
        </section>

        <section id="faq" class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white sm:text-3xl">Frequently asked</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Expand any question to reveal detailed guidance from our support crew.
                        </p>
                    </div>
                    <button id="expandAllFaq" class="rounded-full border border-white/15 px-5 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-300/60">
                        Expand all
                    </button>
                </div>

                <div class="mt-10 space-y-4" id="faqAccordion"></div>
            </div>
        </section>
    </main>

    <div id="videoModal" class="fixed inset-0 z-50 hidden place-items-center bg-slate-950/80 backdrop-blur">
        <div class="relative w-[90%] max-width-3xl overflow-hidden rounded-3xl border border-white/10 bg-slate-900/90 shadow-2xl">
            <button id="closeVideo" class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white transition hover:border-emerald-300/60">
                ✕
            </button>
            <div class="aspect-video w-full bg-black">
                <iframe id="guideVideo" class="h-full w-full" src="https://www.youtube.com/embed/7qQSWcMC3vU?enablejsapi=1"
                        title="Recycling guide video" allow="autoplay"></iframe>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        const materialData = {
            plastic: {
                label: 'Plastic',
                color: 'from-emerald-400 to-emerald-600',
                accepted: ['PET bottles', 'HDPE containers', 'Clean plastic bags', 'Food containers', 'Bottle caps'],
                rejected: ['PVC pipes', 'Styrofoam', 'Medical waste', 'Greasy packaging'],
                tip: 'Remove caps, rinse, and compress bottles. Bundle soft plastics together in one bag.'
            },
            paper: {
                label: 'Paper',
                color: 'from-sky-400 to-sky-600',
                accepted: ['Newspapers', 'Cardboard boxes', 'Office paper', 'Magazines', 'Books'],
                rejected: ['Waxed paper', 'Food-soiled paper', 'Tissues', 'Laminated sheets'],
                tip: 'Flatten boxes and keep paper dry. Store magazines flat to avoid tearing.'
            },
            metal: {
                label: 'Metal',
                color: 'from-slate-400 to-slate-600',
                accepted: ['Aluminium cans', 'Steel cans', 'Clean foil', 'Scrap metal', 'Metal lids'],
                rejected: ['Paint tins', 'Aerosol cans', 'Lithium batteries', 'Electronics'],
                tip: 'Rinse cans and remove paper sleeves. Keep batteries aside for e-waste collection.'
            },
            glass: {
                label: 'Glass',
                color: 'from-amber-400 to-amber-600',
                accepted: ['Clear bottles', 'Coloured bottles', 'Jars', 'Non-tempered glassware'],
                rejected: ['Mirrors', 'Light bulbs', 'Window panes', 'Ceramics'],
                tip: 'Rinse, dry, and separate by colour where possible. Remove lids and corks.'
            },
            electronics: {
                label: 'Electronics',
                color: 'from-purple-400 to-purple-600',
                accepted: ['Mobile phones', 'Laptops', 'Cables', 'Chargers', 'Small appliances'],
                rejected: ['Large appliances', 'Damaged batteries', 'Medical devices', 'CRT monitors'],
                tip: 'Back up your data, wipe devices, and tape battery terminals before handing over.'
            }
        };

        const tipSlides = [
            {
                title: 'Daily eco wins',
                color: 'from-emerald-500/20 to-emerald-600/10',
                tips: ['Use reusable shopping bags and jars', 'Switch to LED bulbs and unplug chargers', 'Carry a refillable water bottle']
            },
            {
                title: 'Weekly reset',
                color: 'from-sky-500/20 to-sky-600/10',
                tips: ['Plan meals to avoid food waste', 'Audit your bin to see what can be reduced', 'Repair or donate items before replacing']
            },
            {
                title: 'Community power',
                color: 'from-amber-500/20 to-amber-600/10',
                tips: ['Host a micro clean-up on your street', 'Invite neighbours to share a collector slot', 'Share your recycling wins online']
            }
        ];

        const faqData = [
            {
                question: 'How does the collection process work?',
                answer: 'Submit a request from your dashboard, upload photos, and pick a time slot. A verified collector confirms, picks up, and you track progress live.'
            },
            {
                question: 'How do I earn and redeem points?',
                answer: 'Points are calculated from material weight, type, and consistency streaks. Redeem in the Rewards tab for M-Pesa cash, airtime, or partner vouchers.'
            },
            {
                question: 'Can I schedule recurring pickups?',
                answer: 'Yes. When requesting a collection, toggle “Repeat pickup”, choose frequency, and we’ll auto-book your preferred collector.'
            },
            {
                question: 'What happens if my items are rejected?',
                answer: 'Collectors leave a note with the reason and suggested fix. Adjust the items and reschedule at no extra cost.'
            },
            {
                question: 'How can I become a collector?',
                answer: 'Apply via the Collectors portal, upload your documents, and complete a quick orientation. We approve within 2 business days.'
            }
        ];

        const filterWrapper = document.getElementById('materialFilter');
        const materialCards = document.getElementById('materialCards');
        const materialLookup = document.getElementById('materialLookup');
        const lookupResult = document.getElementById('lookupResult');
        const tipsCarousel = document.getElementById('tipsCarousel');
        const tipsDots = document.getElementById('tipsDots');
        const faqAccordion = document.getElementById('faqAccordion');
        const expandAllFaq = document.getElementById('expandAllFaq');

        let activeMaterial = 'all';
        let activeSlide = 0;
        let tipsInterval;

        function renderMaterials() {
            materialCards.innerHTML = '';
            const entries = Object.entries(materialData);

            entries
                .filter(([key]) => activeMaterial === 'all' || key === activeMaterial)
                .forEach(([key, material]) => {
                    const card = document.createElement('article');
                    card.className = 'relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/20';
                    card.innerHTML = `
                        <div class="absolute -right-10 -top-12 h-40 w-40 rounded-full bg-gradient-to-tr ${material.color} opacity-20 blur-3xl"></div>
                        <div class="relative">
                            <div class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs uppercase tracking-[0.35em] text-white/75">${material.label}</div>
                            <div class="mt-6 grid gap-6 text-sm text-slate-200">
                                <div>
                                    <h3 class="text-sm font-semibold text-emerald-200">Accepted</h3>
                                    <ul class="mt-2 space-y-2 text-slate-300">
                                        ${material.accepted.map(item => `<li class="flex items-center gap-2"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>${item}</li>`).join('')}
                                    </ul>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-amber-200">Not accepted</h3>
                                    <ul class="mt-2 space-y-2 text-slate-300">
                                        ${material.rejected.map(item => `<li class="flex items-center gap-2"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-400"></span>${item}</li>`).join('')}
                                    </ul>
                                </div>
                                <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-xs text-emerald-100/90">
                                    ${material.tip}
                                </div>
                            </div>
                        </div>`;
                    materialCards.appendChild(card);
                });
        }

        function renderMaterialFilters() {
            filterWrapper.innerHTML = '';
            const allButton = document.createElement('button');
            allButton.textContent = 'All materials';
            allButton.className = `rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] transition ${activeMaterial === 'all' ? 'border-emerald-300 text-emerald-200 bg-emerald-500/10' : 'border-white/15 text-white/70 hover:border-emerald-300/40 hover:text-emerald-200'}`;
            allButton.addEventListener('click', () => {
                activeMaterial = 'all';
                renderMaterialFilters();
                renderMaterials();
            });
            filterWrapper.appendChild(allButton);

            Object.entries(materialData).forEach(([key, { label }]) => {
                const button = document.createElement('button');
                button.textContent = label;
                button.className = `rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] transition ${activeMaterial === key ? 'border-emerald-300 text-emerald-200 bg-emerald-500/10' : 'border-white/15 text-white/70 hover:border-emerald-300/40 hover:text-emerald-200'}`;
                button.addEventListener('click', () => {
                    activeMaterial = key;
                    renderMaterialFilters();
                    renderMaterials();
                });
                filterWrapper.appendChild(button);
            });
        }

        function populateMaterialLookup() {
            materialLookup.innerHTML = '<option value="">Choose a material…</option>';
            Object.entries(materialData).forEach(([key, material]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = material.label;
                materialLookup.appendChild(option);
            });
        }

        materialLookup.addEventListener('change', () => {
            const value = materialLookup.value;
            if (!value) {
                lookupResult.textContent = '';
                return;
            }
            const material = materialData[value];
            lookupResult.textContent = material.tip;
        });

        function renderTips() {
            tipsCarousel.innerHTML = '';
            tipSlides.forEach((slide, index) => {
                const card = document.createElement('article');
                card.className = `rounded-3xl border border-white/10 bg-gradient-to-br ${slide.color} p-6 shadow-lg shadow-black/20 transition`;
                card.style.transform = `translateX(${(index - activeSlide) * 105}%)`;
                card.innerHTML = `
                    <div class="text-xs uppercase tracking-[0.3em] text-white/70">${slide.title}</div>
                    <ul class="mt-6 space-y-3 text-sm text-white/85">
                        ${slide.tips.map(tip => `<li class="flex items-start gap-3"><span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-white/70"></span>${tip}</li>`).join('')}
                    </ul>`;
                tipsCarousel.appendChild(card);
            });

            tipsDots.innerHTML = '';
            tipSlides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.className = `h-2.5 w-7 rounded-full transition ${activeSlide === index ? 'bg-emerald-400' : 'bg-white/15 hover:bg-white/30'}`;
                dot.addEventListener('click', () => {
                    activeSlide = index;
                    renderTips();
                    restartTipsInterval();
                });
                tipsDots.appendChild(dot);
            });
        }

        function nextTip() {
            activeSlide = (activeSlide + 1) % tipSlides.length;
            renderTips();
        }

        function previousTip() {
            activeSlide = (activeSlide - 1 + tipSlides.length) % tipSlides.length;
            renderTips();
        }

        function restartTipsInterval() {
            clearInterval(tipsInterval);
            tipsInterval = setInterval(nextTip, 8000);
        }

        document.getElementById('nextTips').addEventListener('click', () => {
            nextTip();
            restartTipsInterval();
        });

        document.getElementById('previousTips').addEventListener('click', () => {
            previousTip();
            restartTipsInterval();
        });

        function renderFaq() {
            faqAccordion.innerHTML = '';
            faqData.forEach(({ question, answer }, index) => {
                const item = document.createElement('div');
                item.className = 'rounded-2xl border border-white/10 bg-white/5 p-6';
                item.innerHTML = `
                    <button class="flex w-full items-center justify-between gap-4 text-left text-sm font-semibold text-white" data-faq-toggle>
                        <span>${question}</span>
                        <span class="text-lg text-emerald-300" aria-hidden="true">+</span>
                    </button>
                    <div class="mt-4 hidden text-sm text-slate-300" data-faq-content>
                        ${answer}
                    </div>`;
                const toggle = item.querySelector('[data-faq-toggle]');
                const content = item.querySelector('[data-faq-content]');
                toggle.addEventListener('click', () => {
                    const expanded = content.classList.toggle('hidden');
                    toggle.querySelector('span:last-child').textContent = expanded ? '−' : '+';
                });
                if (index === 0) {
                    content.classList.remove('hidden');
                    toggle.querySelector('span:last-child').textContent = '−';
                }
                faqAccordion.appendChild(item);
            });
        }

        expandAllFaq.addEventListener('click', () => {
            const contents = faqAccordion.querySelectorAll('[data-faq-content]');
            const toggles = faqAccordion.querySelectorAll('[data-faq-toggle] span:last-child');
            const shouldExpand = expandAllFaq.dataset.state !== 'expanded';
            contents.forEach(content => {
                content.classList.toggle('hidden', !shouldExpand);
            });
            toggles.forEach(toggle => {
                toggle.textContent = shouldExpand ? '−' : '+';
            });
            expandAllFaq.dataset.state = shouldExpand ? 'expanded' : 'collapsed';
            expandAllFaq.textContent = shouldExpand ? 'Collapse all' : 'Expand all';
        });

        function animateCounters() {
            const counters = document.querySelectorAll('[data-counter]');
            const observer = new IntersectionObserver(
                entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const element = entry.target;
                            const target = parseInt(element.dataset.target, 10);
                            let current = 0;
                            const increment = Math.ceil(target / 120);
                            const updateCounter = () => {
                                current += increment;
                                if (current >= target) {
                                    element.textContent = target.toLocaleString();
                                } else {
                                    element.textContent = current.toLocaleString();
                                    requestAnimationFrame(updateCounter);
                                }
                            };
                            requestAnimationFrame(updateCounter);
                            observer.unobserve(element);
                        }
                    });
                },
                { threshold: 0.6 }
            );
            counters.forEach(counter => observer.observe(counter));
        }

        const videoModal = document.getElementById('videoModal');
        const openVideo = document.getElementById('openVideo');
        const closeVideo = document.getElementById('closeVideo');
        const guideVideo = document.getElementById('guideVideo');

        function toggleModal(show) {
            videoModal.classList.toggle('hidden', !show);
            if (!show) {
                guideVideo.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":[]}', '*');
            }
        }

        openVideo.addEventListener('click', () => toggleModal(true));
        closeVideo.addEventListener('click', () => toggleModal(false));
        videoModal.addEventListener('click', event => {
            if (event.target === videoModal) {
                toggleModal(false);
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !videoModal.classList.contains('hidden')) {
                toggleModal(false);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            renderMaterialFilters();
            renderMaterials();
            populateMaterialLookup();
            renderTips();
            renderFaq();
            animateCounters();
            tipsInterval = setInterval(nextTip, 8000);
        });
    </script>
</body>
</html>