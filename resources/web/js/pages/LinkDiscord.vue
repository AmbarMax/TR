<template>
    <div class="link-discord-wrapper">
        <div class="link-discord-card">

            <!-- Missing params -->
            <div v-if="state === 'invalid'">
                <div class="link-discord-icon">⚠️</div>
                <h2 class="link-discord-title">Invalid link</h2>
                <p class="link-discord-text">This link is missing required parameters. Please use the link provided by the Discord bot.</p>
                <router-link to="/trophy-room" class="link-discord-btn link-discord-btn--secondary">
                    Go to TrophyRoom
                </router-link>
            </div>

            <!-- Not logged in -->
            <div v-else-if="state === 'unauthenticated'">
                <div class="link-discord-icon">🎮</div>
                <h2 class="link-discord-title">Link your Discord account</h2>
                <p class="link-discord-text">Log in to your TrophyRoom account to link <strong>{{ discord_username || 'your Discord account' }}</strong>.</p>
                <button class="link-discord-btn link-discord-btn--primary" @click="goToLogin">
                    Log in to TrophyRoom
                </button>
            </div>

            <!-- Confirm link -->
            <div v-else-if="state === 'confirm'">
                <div class="link-discord-icon">🎮</div>
                <h2 class="link-discord-title">Link your Discord account</h2>
                <p class="link-discord-text">
                    Do you want to link <strong>{{ discord_username }}</strong> to your TrophyRoom profile?
                </p>
                <div class="link-discord-actions">
                    <button class="link-discord-btn link-discord-btn--primary" @click="confirmLink" :disabled="loading">
                        <span v-if="loading">Linking...</span>
                        <span v-else>Confirm</span>
                    </button>
                    <router-link to="/trophy-room" class="link-discord-btn link-discord-btn--secondary">
                        Cancel
                    </router-link>
                </div>
                <p v-if="errorMessage" class="link-discord-error">{{ errorMessage }}</p>
            </div>

            <!-- Success -->
            <div v-else-if="state === 'success'">
                <div class="link-discord-icon">✅</div>
                <h2 class="link-discord-title">Discord linked!</h2>
                <p class="link-discord-text">You can close this tab and go back to Discord.</p>
            </div>

        </div>
    </div>
</template>

<script>
import api from '../api/api.js';
import router from '../router/router.js';

export default {
    data() {
        return {
            state: 'confirm',
            discord_user_id: null,
            guild_id: null,
            discord_username: null,
            loading: false,
            errorMessage: null,
        }
    },
    methods: {
        goToLogin() {
            localStorage.setItem('link_discord_params', JSON.stringify({
                discord_user_id: this.discord_user_id,
                guild_id: this.guild_id,
                discord_username: this.discord_username,
            }));
            router.push('/login');
        },
        confirmLink() {
            this.loading = true;
            this.errorMessage = null;
            api.post('/api/bot/link/confirm', {
                discord_user_id: this.discord_user_id,
                guild_id: this.guild_id,
                discord_username: this.discord_username,
            }).then(response => {
                if (response.status === 200 || response.status === 201) {
                    localStorage.removeItem('link_discord_params');
                    this.state = 'success';
                }
            }).catch(error => {
                const status = error.response?.status;
                if (status === 409) {
                    this.errorMessage = 'This Discord account is already linked to another TrophyRoom user.';
                } else if (status === 404) {
                    this.errorMessage = 'Server not connected to TrophyRoom.';
                } else {
                    this.errorMessage = 'Something went wrong. Please try again.';
                }
            }).finally(() => {
                this.loading = false;
            });
        },
    },
    mounted() {
        let discord_user_id = this.$route.query.discord_user_id;
        let guild_id        = this.$route.query.guild_id;
        let discord_username = this.$route.query.discord_username;

        // If no query params, check if we're coming back from login with stored params
        if (!discord_user_id || !guild_id) {
            const stored = localStorage.getItem('link_discord_params');
            if (stored) {
                try {
                    const parsed = JSON.parse(stored);
                    discord_user_id  = parsed.discord_user_id;
                    guild_id         = parsed.guild_id;
                    discord_username = parsed.discord_username;
                } catch (e) {
                    // ignore
                }
            }
        }

        if (!discord_user_id || !guild_id) {
            this.state = 'invalid';
            return;
        }

        this.discord_user_id  = discord_user_id;
        this.guild_id         = guild_id;
        this.discord_username = discord_username || '';

        const token = localStorage.getItem('access_token');
        if (!token) {
            this.state = 'unauthenticated';
            return;
        }

        this.state = 'confirm';
    }
}
</script>

<style scoped>
.link-discord-wrapper {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.link-discord-card {
    background: #1e1e2e;
    border: 1px solid #2e2e4e;
    border-radius: 16px;
    padding: 48px 40px;
    max-width: 440px;
    width: 100%;
    text-align: center;
}

.link-discord-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.link-discord-title {
    color: #ffffff;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 12px;
}

.link-discord-text {
    color: #a0a0b8;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 28px;
}

.link-discord-text strong {
    color: #ffffff;
}

.link-discord-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.link-discord-btn {
    display: block;
    width: 100%;
    padding: 14px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    border: none;
    transition: opacity 0.2s;
}

.link-discord-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.link-discord-btn--primary {
    background: #7c3aed;
    color: #ffffff;
}

.link-discord-btn--primary:hover:not(:disabled) {
    opacity: 0.85;
}

.link-discord-btn--secondary {
    background: transparent;
    color: #a0a0b8;
    border: 1px solid #2e2e4e;
}

.link-discord-btn--secondary:hover {
    opacity: 0.75;
}

.link-discord-error {
    color: #f87171;
    font-size: 14px;
    margin-top: 16px;
}
</style>
