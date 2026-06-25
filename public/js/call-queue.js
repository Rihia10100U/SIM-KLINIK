// Amankan objek ucapan dari bug Garbage Collection Chrome agar tidak mati mendadak
window.suaraAntrianGlobalQueue = [];

async function playQueueSound(eventData) {
    // 1. Cek status suara dari localStorage (Sinkron dengan tombol di UI)
    if (localStorage.getItem('simklinik_suara_poli') === 'false') {
        return;
    }

    // 2. Ekstrak pesan teks dari event Livewire v3 (Mendukung named parameter & array payload)
    const message = eventData.message || (Array.isArray(eventData) ? eventData[0]?.message : null);
    if (!message) return;

    if (!window.speechSynthesis) {
        console.error("Browser tidak mendukung fitur Text-to-Speech.");
        return;
    }

    // 3. Hentikan paksa suara lama yang sedang berjalan jika ada panggilan baru masuk
    window.speechSynthesis.cancel();
    window.suaraAntrianGlobalQueue = [];

    // 4. Cari Suara Bahasa Indonesia (Sistem otomatis mendeteksi suara wanita terbaik)
    const voices = await getVoices();
    const idVoices = voices.filter(v => v.lang.includes("id"));
    const idWomanVoice = idVoices.length > 0 ? idVoices[idVoices.length - 1] : null;

    // Fungsi internal pembuat struktur ucapan
    const createUtterance = (text) => {
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = "id-ID";
        if (idWomanVoice) speech.voice = idWomanVoice;
        speech.rate = 0.83;  // Kecepatan ideal pengumuman instansi
        speech.pitch = 1.0;
        speech.volume = 1.0;
        return speech;
    };

    // --- Eksekusi Panggilan Pertama ---
    const speech1 = createUtterance(message);
    
    speech1.onend = () => {
        // Hapus dari memori pengaman setelah selesai bicara
        window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech1);

        // Jeda 1 detik, lalu lakukan panggilan ulang kedua (Pengulangan otomatis)
        setTimeout(() => {
            if (localStorage.getItem('simklinik_suara_poli') === 'false') return;

            const speech2 = createUtterance(message);
            speech2.onend = () => {
                window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech2);
            };

            window.suaraAntrianGlobalQueue.push(speech2);
            window.speechSynthesis.speak(speech2);
        }, 1000);
    };

    speech1.onerror = () => {
        window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech1);
    };

    // Masukkan ke array global agar tidak di-delete browser, lalu bunyikan!
    window.suaraAntrianGlobalQueue.push(speech1);
    window.speechSynthesis.speak(speech1);
}

// Fungsi pembantu untuk memuat daftar suara browser secara asinkron
function getVoices() {
    return new Promise((resolve) => {
        const voices = window.speechSynthesis.getVoices();
        if (voices.length) {
            resolve(voices);
            return;
        }
        const intervalId = setInterval(() => {
            const retryVoices = window.speechSynthesis.getVoices();
            if (retryVoices.length) {
                resolve(retryVoices);
                clearInterval(intervalId);
            }
        }, 10);
    });
}

// Jalankan Listener Event Livewire v3 saat aplikasi siap
document.addEventListener('livewire:initialized', () => {
    Livewire.on('queue-called', (event) => {
        playQueueSound(event);
    });
});