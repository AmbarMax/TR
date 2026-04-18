import Login from "../pages/Login.vue";
import Signup from "../pages/Signup.vue";
import Profile from "../pages/Profile.vue";
import Dashboard from "../pages/Dashboard.vue";
import Main from "../Main.vue";
import Auth from "../Auth.vue";
import Validate from "../pages/Validate.vue"
import Forge from "../pages/Forge.vue";
import TrophyRoom from "../pages/TrophyRoom.vue";
import Notifications from "../pages/Notifications.vue";
import VirtualHall from "../pages/VirtualHall/VirtualHall.vue";
import ForgotPassword from "../pages/ForgotPassword.vue";
import ResetPassword from "../pages/ResetPassword.vue";
import SinglePage from "../SinglePage.vue";
import Network from "../pages/Network/Network.vue";
import Feed from "../pages/Feed/Feed.vue";
import ResetTwoFactorAuth from "../pages/ResetTwoFactorAuth.vue";
import Exchange from "../pages/Exchange.vue";
import MyChests from "../pages/MyChests.vue";
import LinkDiscord from "../pages/LinkDiscord.vue";

const routes = [
    {
        path: '/',
        component: Main,
        children: [
            {
                path: '/dashboard',
                component: Dashboard,
                name: 'dashboard'
            },
            {
                path: '/profile',
                component: Profile,
                name: 'profile'
            },
            {
                path: '/validate',
                component: Validate,
                name: 'validate'
            },
            {
                path: '/forge',
                component: Forge,
                name: 'forge'
            },
            {
                path: '/feed',
                component: Feed,
                name: 'feed'
            },
            {
                path: '/trophy-room',
                component: TrophyRoom,
                name: 'trophy-room'
            },
            {
                path: '/notifications',
                component: Notifications,
                name: 'notifications'
            },
            {
                path: '/network',
                component: Network,
                name: 'network'
            },
            {
                path: '/exchange',
                component: Exchange,
                name: 'exchange'
            },
            {
                path: '/my-chests',
                component: MyChests,
                name: 'my-chests'
            },
            {
                path: '/brand-dashboard',
                component: () => import('../pages/BrandDashboard/BrandDashboard.vue'),
                name: 'brand-dashboard',
                meta: { requiresAuth: true }
            }
        ]
    },
    {
        path: '/',
        component: Auth,
        children: [
            {
                path: '/login',
                component: Login,
                name: 'login'
            },
            {
                path: '/sign-up',
                component: Signup,
                name: 'sign-up'
            },
            {
                path: '/forgot-password',
                component: ForgotPassword,
                name: 'forgot-password'
            },
            {
                path: '/reset-password',
                component: ResetPassword,
                name: 'reset-password'
            },
            {
                path: '/reset-2fa',
                component: ResetTwoFactorAuth,
                name: 'reset-2fa'
            }
        ]
    },
    {
        path: '/',
        component: SinglePage,
        children: [
            {
                path: `/virtual-hall/:username`,
                component: VirtualHall,
                name: 'virtual-hall'
            },
            {
                path: '/link-discord',
                component: LinkDiscord,
                name: 'link-discord'
            },
        ]
    },
    {
        path: '',
        redirect: '/dashboard',
    },
    {
        path: '/:catchAll(.*)',
        redirect: '/login',
    },
];

export default routes;
