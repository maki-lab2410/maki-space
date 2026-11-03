<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Schedule Monitor – {{ $settings['nama_lab'] ?? 'Maki Space' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap');
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            background-color: #F5F5F7;
            color: #1e293b;
            overflow-x: hidden;
        }

        .font-mono-num {
            font-family: 'JetBrains Mono', monospace;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        /* Subtle glowing ring animations for light mode */
        @keyframes pulse-ring-red {
            0%, 100% { box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.25), 0 10px 25px -5px rgba(244, 63, 94, 0.15); }
            50% { box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.45), 0 14px 30px -5px rgba(244, 63, 94, 0.25); }
        }

        .card-active-occupied {
            animation: pulse-ring-red 3s infinite;
        }

        /* Custom scrollbar for schedule list */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F5F5F7] text-slate-800 flex flex-col justify-between selection:bg-blue-600 selection:text-white"
      x-data="liveMonitor()"
      x-init="initMonitor()">

    <!-- ─── TOP HEADER BAR ────────────────────────────────────────────────── -->
    <header class="bg-white border-b border-gray-200 px-4 lg:px-8 py-3 sticky top-0 z-50 shadow-xs">
        <div class="max-w-[1920px] mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Logo & Title -->
            <div class="flex items-center gap-3.5">
                @if(!empty($settings['logo']) && file_exists(public_path($settings['logo'])))
                    <img src="{{ asset($settings['logo']) }}" alt="Logo" class="h-10 lg:h-12 w-auto object-contain drop-shadow-xs">
                @else
                    <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-xl shadow-sm">
                        M
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg lg:text-xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                            {{ $settings['nama_lab'] ?? 'Maki Space' }}
                            <span class="text-[10px] tracking-wider uppercase font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                LIVE MONITOR
                            </span>
                        </h1>
                    </div>
                    <p class="text-xs text-gray-500 font-medium truncate max-w-[320px] lg:max-w-md">
                        {{ $settings['nama_instansi'] ?? 'Nama Instansi / Fakultas' }}
                    </p>
                </div>
            </div>

            <!-- Real-Time Clock & Date (Light Container) -->
            <div class="flex items-center gap-4 bg-gray-50 px-4 lg:px-5 py-2 rounded-2xl border border-gray-200 shadow-2xs">
                <div class="text-right">
                    <p class="text-xs lg:text-sm font-bold text-gray-800" x-text="currentDateFormatted">Loading date...</p>
                    <p class="text-[10px] text-emerald-600 font-bold flex items-center justify-end gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>LIVE CONNECTED</span>
                    </p>
                </div>
                <div class="h-8 w-px bg-gray-200"></div>
                <div class="text-2xl lg:text-3xl font-black text-gray-900 font-mono-num tracking-tight" x-text="currentTime">
                    00:00:00
                </div>
            </div>

            <!-- Controls (Filter, Fullscreen, Back to Calendar) -->
            <div class="flex items-center gap-2">
                <!-- Room Filter Pills -->
                <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 text-xs">
                    <button @click="filterRoom = 'all'"
                            :class="filterRoom === 'all' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white'"
                            class="px-3 py-1.5 rounded-lg transition text-xs whitespace-nowrap">
                        All Rooms
                    </button>
                    <template x-for="r in roomsData" :key="r.id">
                        <button @click="filterRoom = r.name"
                                :class="filterRoom === r.name ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white'"
                                class="px-2.5 py-1.5 rounded-lg transition text-xs flex items-center gap-1.5 whitespace-nowrap">
                            <span class="w-2.5 h-2.5 rounded-full ring-1 ring-black/10 shrink-0" :style="'background-color:' + r.color_hex"></span>
                            <span x-text="r.code"></span>
                        </button>
                    </template>
                </div>

                <!-- Fullscreen Toggle -->
                <button @click="toggleFullscreen()"
                        title="Fullscreen Mode (F11)"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 transition flex items-center gap-1.5 text-xs font-semibold shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    <span class="hidden lg:inline" x-text="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'">Fullscreen</span>
                </button>

                <!-- Back to Calendar -->
                <a href="{{ url('/') }}"
                   title="Back to Calendar"
                   class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 transition flex items-center gap-1.5 text-xs font-semibold shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="hidden lg:inline">Calendar</span>
                </a>
            </div>

        </div>
    </header>

    <!-- ─── MAIN MONITOR CONTENT (MULTI-ROOM GRID) ────────────────────────── -->
    <main class="flex-1 max-w-[1920px] w-full mx-auto p-4 lg:p-6 flex flex-col justify-center">

        <!-- Loading State -->
        <div x-show="loading && roomsData.length === 0" class="flex-1 flex flex-col items-center justify-center py-24">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
            <p class="text-sm font-semibold text-gray-500">Syncing live schedule data...</p>
        </div>

        <!-- Room Cards Grid -->
        <div x-show="roomsData.length > 0"
             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 items-stretch">

            <template x-for="room in filteredRooms" :key="room.id">
                <div class="bg-white rounded-2xl border transition-all duration-300 flex flex-col justify-between overflow-hidden relative shadow-sm hover:shadow-md"
                     :class="room.is_occupied 
                        ? 'border-rose-300 card-active-occupied ring-1 ring-rose-400/30' 
                        : 'border-gray-200 hover:border-gray-300'">

                    <!-- Decorative Room Accent Ribbon (matches room color) -->
                    <div class="h-2 w-full" :style="'background-color:' + room.color_hex"></div>

                    <!-- Room Header Section -->
                    <div class="p-5 pb-3">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <span class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider px-2 py-0.5 rounded-md font-mono-num mb-1.5 border shadow-2xs"
                                      :style="'background-color:' + room.color_hex + '15; border-color:' + room.color_hex + '40; color:' + room.color_hex">
                                    <span class="w-2 h-2 rounded-full ring-1 ring-black/10 shrink-0" :style="'background-color:' + room.color_hex"></span>
                                    <span x-text="room.code"></span>
                                </span>
                                <h2 class="text-lg lg:text-xl font-black text-gray-900 tracking-tight leading-tight" x-text="room.name"></h2>
                            </div>

                            <!-- Live Occupancy Badge -->
                            <div class="shrink-0">
                                <template x-if="room.is_occupied">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                        <span>OCCUPIED</span>
                                    </span>
                                </template>
                                <template x-if="!room.is_occupied">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>AVAILABLE</span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Status Display: Ongoing Session OR Available -->
                    <div class="px-5 py-3">
                        <!-- IF OCCUPIED -->
                        <template x-if="room.is_occupied && room.current">
                            <div class="bg-rose-50/80 rounded-xl p-4 border border-rose-200/90 relative overflow-hidden">
                                <div class="flex items-center justify-between text-xs font-bold text-rose-700 mb-2">
                                    <span class="uppercase tracking-wider flex items-center gap-1">
                                        <span>🔴</span> In Progress
                                    </span>
                                    <span class="font-mono-num bg-rose-100 text-rose-900 font-bold px-2 py-0.5 rounded-md text-[11px]"
                                          x-text="room.current.start_time + ' - ' + room.current.end_time">
                                    </span>
                                </div>

                                <h3 class="text-base lg:text-lg font-black text-gray-900 mb-2 leading-snug line-clamp-2"
                                    x-text="room.current.course"></h3>

                                <div class="space-y-1 text-xs text-gray-600">
                                    <p class="flex items-center gap-2">
                                        <span class="text-gray-400">👤</span>
                                        <span class="font-bold text-gray-800" x-text="room.current.lecturer"></span>
                                    </p>
                                    <template x-if="room.current.class && room.current.class !== '-'">
                                        <p class="flex items-center gap-2">
                                            <span class="text-gray-400">👥</span>
                                            <span>Class: <strong class="text-gray-900 font-bold" x-text="room.current.class"></strong></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- IF AVAILABLE / VACANT -->
                        <template x-if="!room.is_occupied">
                            <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200 text-center">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center text-base font-black mb-2 shadow-2xs">
                                    ✓
                                </div>
                                <h4 class="text-sm font-bold text-emerald-800 mb-0.5">Room Available</h4>
                                <p class="text-xs text-gray-500">No active lectures at this moment.</p>

                                <!-- Next upcoming session preview -->
                                <template x-if="room.upcoming">
                                    <div class="mt-3 pt-3 border-t border-gray-200 text-left bg-amber-50/70 p-2.5 rounded-lg border border-amber-200/80">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800 mb-1 flex items-center justify-between">
                                            <span>⏳ Upcoming Schedule</span>
                                            <span class="font-mono-num font-bold text-amber-900" x-text="room.upcoming.start_time + ' - ' + room.upcoming.end_time"></span>
                                        </p>
                                        <p class="text-xs font-bold text-gray-900 truncate" x-text="room.upcoming.course"></p>
                                        <p class="text-[11px] text-gray-600 truncate" x-text="room.upcoming.lecturer"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Today's Schedule Timeline for this Room -->
                    <div class="p-5 pt-2 flex-1 flex flex-col justify-end">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                                <span>📋</span> Today's Schedule
                            </h4>
                            <span class="text-[10px] text-gray-400 font-bold" x-text="room.schedules.length + ' session(s)'"></span>
                        </div>

                        <!-- Schedule Items List -->
                        <div class="space-y-1.5 max-h-[190px] overflow-y-auto custom-scroll pr-1">
                            <template x-if="room.schedules.length === 0">
                                <div class="text-center py-6 text-gray-400 text-xs italic">
                                    No scheduled lectures today.
                                </div>
                            </template>

                            <template x-for="sch in room.schedules" :key="sch.id">
                                <div class="p-2 rounded-xl text-xs flex items-center justify-between gap-2 transition"
                                     :class="{
                                         'bg-rose-50 border border-rose-200 text-gray-900 font-semibold shadow-2xs': sch.status === 'ongoing',
                                         'bg-white border border-gray-200 text-gray-800 hover:bg-gray-50 shadow-2xs': sch.status === 'upcoming',
                                         'bg-gray-50 border border-gray-100 text-gray-400 opacity-60': sch.status === 'past'
                                     }">
                                    <div class="truncate flex-1">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span x-show="sch.status === 'ongoing'" class="text-rose-600 font-bold">▶</span>
                                            <span x-show="sch.status === 'past'" class="text-emerald-600 text-[10px] font-bold">✓</span>
                                            <span class="truncate font-bold" x-text="sch.course"></span>
                                        </div>
                                        <p class="text-[10px] text-gray-500 truncate" x-text="sch.lecturer + (sch.class && sch.class !== '-' ? ' (' + sch.class + ')' : '')"></p>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="font-mono-num text-[11px] font-bold px-1.5 py-0.5 rounded"
                                              :class="sch.status === 'ongoing' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-700'"
                                              x-text="sch.start_time + ' - ' + sch.end_time">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Card Footer Status -->
                    <div class="bg-gray-50 border-t border-gray-100 px-5 py-2.5 flex items-center justify-between text-[11px] text-gray-500">
                        <span>Monitor Status</span>
                        <span class="font-bold flex items-center gap-1.5"
                              :class="room.is_occupied ? 'text-rose-700' : 'text-emerald-700'">
                            <span class="w-2 h-2 rounded-full" :class="room.is_occupied ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'"></span>
                            <span x-text="room.is_occupied ? 'In Session' : 'Available'"></span>
                        </span>
                    </div>

                </div>
            </template>

        </div>

    </main>

    <!-- ─── FOOTER BAR (TICKER & INFO) ────────────────────────────────────── -->
    <footer class="bg-white border-t border-gray-200 px-6 py-2.5 text-xs text-gray-500 flex flex-col md:flex-row items-center justify-between gap-2 shadow-xs">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
            <span class="font-bold text-gray-800">{{ $settings['nama_lab'] ?? 'Maki Space' }}</span>
            <span>&bull;</span>
            <span>Real-time Live Schedule Display for Laboratory Monitors</span>
        </div>

        <div class="flex items-center gap-4 text-[11px]">
            <span>Lab Contact: <strong class="text-gray-800">{{ $settings['kontak_laboran'] ?? '-' }}</strong></span>
            <span>&bull;</span>
            <span class="flex items-center gap-1.5 text-gray-500">
                <span>Auto-syncs every 15 seconds</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
            </span>
        </div>
    </footer>

    <!-- ─── ALPINE.JS CONTROLLER SCRIPT ───────────────────────────────────── -->
    <script>
        function liveMonitor() {
            return {
                currentTime: '00:00:00',
                currentDateFormatted: '',
                filterRoom: '{{ $selectedRoom }}' || 'all',
                roomsData: [],
                loading: true,
                isFullscreen: false,
                syncInterval: null,
                clockInterval: null,

                initMonitor() {
                    this.updateClock();
                    this.clockInterval = setInterval(() => {
                        this.updateClock();
                    }, 1000);

                    // Fetch data immediately
                    this.fetchData();

                    // Auto sync every 15 seconds
                    this.syncInterval = setInterval(() => {
                        this.fetchData();
                    }, 15000);

                    // Listen to fullscreenchange
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                },

                updateClock() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    this.currentTime = `${hours}:${minutes}:${seconds}`;

                    if (!this.currentDateFormatted) {
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        this.currentDateFormatted = now.toLocaleDateString('en-US', options);
                    }
                },

                async fetchData() {
                    try {
                        const res = await fetch('{{ route("booking.live-data") }}?date={{ $selectedDate }}');
                        const data = await res.json();
                        this.roomsData = data.rooms || [];
                        if (data.date_text) {
                            this.currentDateFormatted = data.date_text;
                        }
                        this.loading = false;
                    } catch (err) {
                        console.error('Error fetching live data:', err);
                        this.loading = false;
                    }
                },

                get filteredRooms() {
                    if (this.filterRoom === 'all') {
                        return this.roomsData;
                    }
                    return this.roomsData.filter(r => r.name === this.filterRoom);
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error(`Error attempting to enable fullscreen: ${err.message}`);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
