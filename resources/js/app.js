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

window.verballySpeak = (text, onStart, onEnd) => {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        if (onEnd) onEnd();
        return;
    }

    try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 0.92;

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
