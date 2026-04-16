# LinkDiscord Vue Component — Instructions for Claude Code

## Context
TrophyRoom users can link their Discord account via a bot. When they type `/link` in Discord, they get a button that sends them to `https://app.ambar.gg/bot/link?discord_user_id=X&guild_id=Y&discord_username=Z`. The Laravel backend redirects them to `/#/link-discord?discord_user_id=X&guild_id=Y&discord_username=Z`.

We need a Vue page that handles this route and lets the user confirm the link.

## What to build

### 1. Vue Component: `LinkDiscord.vue`

Location: Look at existing pages in `resources/js/` to find where page components live (likely `resources/js/views/` or `resources/js/pages/`). Create `LinkDiscord.vue` in the same directory as other page components.

**Behavior:**
1. On mount, read query params: `discord_user_id`, `guild_id`, `discord_username`
2. If params are missing, show error message and link back to trophy-room
3. If user is NOT logged in (no JWT token in localStorage/Vuex), show message: "Log in to link your Discord account" with a button that goes to login, preserving the query params so they can come back after login
4. If user IS logged in, show a confirmation card:
   - "Link your Discord account"
   - "Do you want to link **{discord_username}** to your TrophyRoom profile?"
   - [Confirm] button → POST to `/api/bot/link/confirm` with JWT header
   - [Cancel] button → redirect to trophy-room
5. On success (201 or 200): show success message "Discord linked! You can close this tab and go back to Discord."
6. On error (409): show "This Discord account is already linked to another TrophyRoom user."
7. On error (404): show "Server not connected to TrophyRoom."

**API call:**
```javascript
POST /api/bot/link/confirm
Headers: { Authorization: Bearer {jwt_token} }
Body: {
    discord_user_id: "...",
    guild_id: "...",
    discord_username: "..."
}
```

**Style:** Match the existing app style (Tailwind + Bootstrap 4). Look at other pages for reference. Use the app's existing purple/dark theme.

### 2. Route Registration

Find the Vue Router config (likely `resources/js/router.js` or `resources/js/router/index.js`) and add:

```javascript
{
    path: '/link-discord',
    name: 'LinkDiscord',
    component: () => import('./views/LinkDiscord.vue'), // adjust path
    meta: { requiresAuth: false } // user might not be logged in yet
}
```

### 3. Important Notes

- Check how other components get the JWT token (localStorage, Vuex store, or cookies)
- Check how other components make authenticated API calls (axios interceptor, or manual header)
- Follow the exact same patterns
- The component should work on mobile since users come from Discord mobile app
- Keep it simple — one card, centered, with the Discord logo or a 🎮 emoji
- After successful link, don't redirect — just show success message since the user came from Discord and should go back there
