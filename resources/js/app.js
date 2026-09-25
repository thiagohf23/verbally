window.toggleVerballyTheme = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('verbally_theme', isDark ? 'dark' : 'light');
    window.dispatchEvent(new CustomEvent('verbally-theme-changed', { detail: { isDark } }));
};

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('verbally_theme')) {
        document.documentElement.classList.toggle('dark', e.matches);
    }
});

let verballyVoices = [];
if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
    const updateVoices = () => {
        try {
            verballyVoices = window.speechSynthesis.getVoices() || [];
        } catch (_) {}
    };
    updateVoices();
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
        window.speechSynthesis.onvoiceschanged = updateVoices;
    }
}

window.verballySpeak = (text, onStart, onEnd, options = {}) => {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        if (onEnd) onEnd();
        return;
    }

    try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = options.lang || 'en-US';
        utterance.rate = options.rate || 0.92;

        if (verballyVoices.length > 0) {
            const enVoice = verballyVoices.find(v => v.lang === 'en-US' && (v.name.includes('Natural') || v.name.includes('Google') || v.name.includes('Samantha') || v.name.includes('Alex')))
                || verballyVoices.find(v => v.lang && v.lang.startsWith('en'));
            if (enVoice) {
                utterance.voice = enVoice;
            }
        }

        if (onStart) utterance.onstart = onStart;
        utterance.onend = () => {
            if (onEnd) onEnd();
        };
        utterance.onerror = () => {
            if (onEnd) onEnd();
        };

        window.speechSynthesis.speak(utterance);
    } catch (_) {
        if (onEnd) onEnd();
    }
};

window.verballyStopSpeaking = () => {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        try {
            window.speechSynthesis.cancel();
        } catch (_) {}
    }
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('verballyConversation', () => ({
        playbackRate: Number(localStorage.getItem('verbally_playback_rate') || 0.9),
        revealedTranscripts: {},
        recordingSupported: Boolean(navigator.mediaDevices?.getUserMedia && window.MediaRecorder),
        recording: false,
        uploading: false,
        uploadProgress: 0,
        recordingSeconds: 0,
        liveTranscript: '',
        finalTranscript: '',
        mediaRecorder: null,
        mediaStream: null,
        recognition: null,
        chunks: [],
        recordingStartedAt: null,
        recordingTimer: null,

        setPlaybackRate(rate) {
            this.playbackRate = Number(rate);
            localStorage.setItem('verbally_playback_rate', String(rate));
        },

        toggleTranscript(messageId) {
            this.revealedTranscripts[messageId] = !this.revealedTranscripts[messageId];
        },

        isTranscriptVisible(messageId) {
            return Boolean(this.revealedTranscripts[messageId]);
        },

        speak(text, messageId = null, trackReplay = true) {
            if (!text) return;
            if (trackReplay && messageId !== null) this.$wire.noteReplay();
            window.verballySpeak(text, null, null, { rate: this.playbackRate });
        },

        playPartnerEvent(detail) {
            this.$nextTick(() => {
                const stream = document.getElementById('chat-stream');
                if (stream) stream.scrollTo({ top: stream.scrollHeight, behavior: 'smooth' });
                this.speak(detail.text, detail.messageId, false);
            });
        },

        async startRecording() {
            if (!this.recordingSupported || this.recording || this.uploading) return;

            try {
                this.mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.chunks = [];
                this.liveTranscript = '';
                this.finalTranscript = '';
                const preferredType = ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg'].find((type) => MediaRecorder.isTypeSupported(type));
                this.mediaRecorder = preferredType ? new MediaRecorder(this.mediaStream, { mimeType: preferredType }) : new MediaRecorder(this.mediaStream);
                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) this.chunks.push(event.data);
                };
                this.mediaRecorder.onstop = () => this.uploadRecording();
                this.startBrowserRecognition();
                this.mediaRecorder.start(250);
                this.recording = true;
                this.recordingStartedAt = Date.now();
                this.recordingSeconds = 0;
                this.recordingTimer = window.setInterval(() => {
                    this.recordingSeconds = Math.round((Date.now() - this.recordingStartedAt) / 1000);
                    if (this.recordingSeconds >= 120) this.stopRecording();
                }, 500);
            } catch (_) {
                this.cleanupRecording();
                window.alert('Não foi possível acessar o microfone. Verifique a permissão do navegador.');
            }
        },

        startBrowserRecognition() {
            const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!Recognition) return;

            this.recognition = new Recognition();
            this.recognition.lang = 'en-US';
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.maxAlternatives = 3;
            this.recognition.onresult = (event) => {
                let interim = '';
                for (let index = event.resultIndex; index < event.results.length; index += 1) {
                    const words = event.results[index][0].transcript;
                    if (event.results[index].isFinal) this.finalTranscript += `${words} `;
                    else interim += words;
                }
                this.liveTranscript = `${this.finalTranscript}${interim}`.trim();
            };

            try { this.recognition.start(); } catch (_) {}
        },

        stopRecording() {
            if (!this.recording) return;
            this.recording = false;
            if (this.recordingTimer) window.clearInterval(this.recordingTimer);
            if (this.recognition) {
                try { this.recognition.stop(); } catch (_) {}
            }
            if (this.mediaRecorder?.state !== 'inactive') this.mediaRecorder.stop();
        },

        uploadRecording() {
            const mimeType = this.mediaRecorder?.mimeType || 'audio/webm';
            const blob = new Blob(this.chunks, { type: mimeType });
            const file = new File([blob], 'verbally-response.webm', { type: mimeType });
            const duration = Math.max(1, Math.round((Date.now() - this.recordingStartedAt) / 1000));
            const browserTranscript = (this.finalTranscript || this.liveTranscript).trim();

            this.uploading = true;
            this.uploadProgress = 0;
            this.$wire.upload(
                'voiceRecording',
                file,
                () => {
                    this.$wire.sendVoiceMessage(browserTranscript, duration).finally(() => {
                        this.uploading = false;
                        this.cleanupRecording();
                    });
                },
                () => {
                    this.uploading = false;
                    this.cleanupRecording();
                    window.alert('A gravação não pôde ser enviada. Tente novamente.');
                },
                (event) => { this.uploadProgress = event.detail.progress; },
            );
            this.mediaStream?.getTracks().forEach((track) => track.stop());
        },

        cleanupRecording() {
            if (this.recordingTimer) window.clearInterval(this.recordingTimer);
            this.mediaStream?.getTracks().forEach((track) => track.stop());
            this.recording = false;
            this.recordingSeconds = 0;
            this.mediaRecorder = null;
            this.mediaStream = null;
            this.recognition = null;
            this.chunks = [];
        },
    }));

    window.Alpine.data('verballyListening', () => ({
        transcriptVisible: false,
        playing: false,
        playbackRate: Number(localStorage.getItem('verbally_playback_rate') || 0.9),

        playEncodedDialogue(encodedLines) {
            if (this.playing) {
                window.verballyStopSpeaking();
                this.playing = false;
                return;
            }

            const lines = JSON.parse(window.atob(encodedLines));
            this.playing = true;
            this.playLine(lines, 0);
        },

        playLine(lines, index) {
            if (!this.playing || index >= lines.length) {
                this.playing = false;
                return;
            }

            window.verballySpeak(
                lines[index].text,
                null,
                () => {
                    window.setTimeout(() => this.playLine(lines, index + 1), 350);
                },
                { rate: this.playbackRate },
            );
        },
    }));
});
