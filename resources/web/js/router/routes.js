import Login from "../pages/Login.vue";
import Signup from "../pages/Signup.vue";
import Profile from "../pages/Profile.vue";
import DashboardResolver from "../pages/DashboardResolver.vue";
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
import Feed from "../pages/Feed/Feed.vue";
import ResetTwoFactorAuth from "../pages/ResetTwoFactorAuth.vue";
import Rewards from "../pages/Rewards.vue";
import LinkDiscord from "../pages/LinkDiscord.vue";
import Hall from "../pages/Hall/Hall.vue";
import Admin from "../pages/Admin/Admin.vue";
import ManageBrands from "../pages/Admin/ManageBrands.vue";
import ManageRoles from "../pages/Admin/ManageRoles.vue";
import AuditLog from "../pages/Admin/AuditLog.vue";

const routes = [
    {
        path: '/',
        component: Main,
        children: [
            {
                path: '/dashboard',
                component: DashboardResolver,
                name: 'dashboard'
            },
            {
                path: '/profile',
                component: Profile,
                name: 'settings'
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
                redirect: { name: 'feed', query: { tab: 'community' } }
            },
            {
                path: '/rewards',
                component: Rewards,
                name: 'rewards'
            },
            {
                path: '/exchange',
                redirect: { name: 'rewards', query: { tab: 'shop' } }
            },
            {
                path: '/my-chests',
                redirect: { name: 'rewards', query: { tab: 'chests' } }
            },
            {
                path: '/brand-dashboard',
                component: () => import('../pages/BrandDashboard/BrandDashboard.vue'),
                name: 'brand-dashboard',
                meta: { requiresAuth: true }
            },
            {
                path: '/admin',
                component: Admin,
                meta: { requiresAdmin: true },
                children: [
                    { path: '', redirect: '/admin/brands' },
                    { path: 'brands', component: ManageBrands, name: 'admin.brands', meta: { requiresAdmin: true } },
                    { path: 'roles',  component: ManageRoles,  name: 'admin.roles',  meta: { requiresAdmin: true } },
                    { path: 'audit',  component: AuditLog,     name: 'admin.audit',  meta: { requiresAdmin: true } },
                ]
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
                redirect: to => ({ name: 'hall', params: { username: to.params.username } })
            },
            {
                path: '/link-discord',
                component: LinkDiscord,
                name: 'link-discord'
            },
            {
                path: '/:username',
                component: Hall,
                name: 'hall'
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
