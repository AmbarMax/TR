export const PENDING_INTENT_KEY = "pending_hall_intent";
export const PENDING_INTENT_TTL_MS = 30 * 60 * 1000; // 30 minutes

export function savePendingIntent({ type, payload }) {
    if (!type || !payload) return;
    const now = Date.now();
    const intent = {
        type,
        payload,
        timestamp: now,
        expires_at: now + PENDING_INTENT_TTL_MS,
    };
    try {
        localStorage.setItem(PENDING_INTENT_KEY, JSON.stringify(intent));
    } catch (e) {
        // localStorage blocked / quota — silently no-op.
    }
}

export function readPendingIntent() {
    const raw = (() => {
        try { return localStorage.getItem(PENDING_INTENT_KEY); } catch (e) { return null; }
    })();
    if (!raw) return null;
    try {
        const intent = JSON.parse(raw);
        if (!intent || typeof intent !== "object") {
            clearPendingIntent();
            return null;
        }
        if (!intent.expires_at || intent.expires_at < Date.now()) {
            clearPendingIntent();
            return null;
        }
        return intent;
    } catch (e) {
        clearPendingIntent();
        return null;
    }
}

export function clearPendingIntent() {
    try { localStorage.removeItem(PENDING_INTENT_KEY); } catch (e) { /* ignore */ }
}

/**
 * Consume the pending intent (if any), execute the captured action, and
 * navigate to the hall the user was on. Returns true if an intent was
 * consumed — caller should skip its default redirect.
 */
export async function consumePendingIntent(api, router) {
    const intent = readPendingIntent();
    if (!intent) return false;

    const hallUsername = intent.payload?.hallUsername;

    try {
        if (intent.type === "pursuit" && intent.payload?.trophyId) {
            await api.post("/api/pursuits", { trophy_id: intent.payload.trophyId });
        } else if (intent.type === "follow" && hallUsername) {
            await api.post(`/api/users/${encodeURIComponent(hallUsername)}/follow`);
        }
    } catch (e) {
        // Already-pursuing / already-following / network hiccup — fall through
        // to the hall redirect so the user lands somewhere meaningful.
        console.warn("[pending-intent] action failed", e);
    }

    clearPendingIntent();

    if (hallUsername) {
        router.push(`/${encodeURIComponent(hallUsername)}`);
        return true;
    }
    return false;
}
