// Amankan objek ucapan dari bug Garbage Collection Chrome agar tidak mati mendadak
window.suaraAntrianGlobalQueue = [];

// Fungsi untuk mengatur volume video kiosk
function setVideoVolume(volume) {
    const video = document.getElementById('kiosk-video');
    if (video) video.volume = volume;
}

async function playQueueSound(eventData) {
    // 1. Cek status suara dari localStorage (Sinkron dengan tombol di UI)
    const key = eventData._suaraKey || 'simklinik_suara_poli';
    if (localStorage.getItem(key) === 'false') return;

    // 2. Ekstrak pesan teks dari event Livewire
    const message = eventData.message || (Array.isArray(eventData) ? eventData[0]?.message : null);
    if (!message) return;

    if (!window.speechSynthesis) {
        console.error("Browser tidak mendukung fitur Text-to-Speech.");
        return;
    }

    // 3. Hentikan paksa suara lama yang sedang berjalan jika ada panggilan baru masuk
    window.speechSynthesis.cancel();
    window.suaraAntrianGlobalQueue = [];

    // 4. Cari Suara Bahasa Indonesia
    const voices = await getVoices();
    const idVoices = voices.filter(v => v.lang.includes("id"));
    const idWomanVoice = idVoices.length > 0 ? idVoices[idVoices.length - 1] : null;

    // Fungsi internal pembuat struktur ucapan
    const createUtterance = (text) => {
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = "id-ID";
        if (idWomanVoice) speech.voice = idWomanVoice;
        speech.rate = 0.83;
        speech.pitch = 1.0;
        speech.volume = 1.0;
        return speech;
    };

    // --- Eksekusi Panggilan Pertama ---
    const speech1 = createUtterance(message);

    speech1.onstart = () => {
        setVideoVolume(0.15);
    };

    speech1.onend = () => {
        window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech1);

        setTimeout(() => {
            if (localStorage.getItem(key) === 'false') {
                setVideoVolume(1.0);
                return;
            }

            const speech2 = createUtterance(message);
            speech2.onend = () => {
                window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech2);
                setVideoVolume(1.0);
            };
            speech2.onerror = () => {
                setVideoVolume(1.0);
            };

            window.suaraAntrianGlobalQueue.push(speech2);
            window.speechSynthesis.speak(speech2);
        }, 1000);
    };

    speech1.onerror = () => {
        window.suaraAntrianGlobalQueue = window.suaraAntrianGlobalQueue.filter(s => s !== speech1);
        setVideoVolume(1.0);
    };

    window.suaraAntrianGlobalQueue.push(speech1);
    window.speechSynthesis.speak(speech1);
}

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

document.addEventListener('livewire:initialized', () => {
    Livewire.on('queue-called', (event) => {
        playQueueSound(event);
    });
});
