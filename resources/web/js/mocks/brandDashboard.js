/**
 * MOCK DATA — BRAND DASHBOARD v.2
 *
 * Snapshot de los 5 endpoints de /api/brand/analytics/* capturado el
 * 2026-05-07 contra el seeder de testing (BrandDashboardTestingSeeder).
 * Las shapes son literal a las del backend real, así que los componentes
 * Vue funcionan idéntico contra mock o real.
 *
 * ⚠️  ESTE ARCHIVO SE BORRA antes del primer deploy a producción.
 *     Se usa solo en development cuando los endpoints reales no
 *     están disponibles o la DB local no tiene seeder corrido.
 *
 * Para activar mocks:
 *   .env.local con VITE_USE_BRAND_MOCKS=true
 *
 * Para desactivar:
 *   borrar la variable o setearla a false en .env.local
 */

export const mockPerformance = {
  active_pursuers: {
    value: 48,
    delta_7d: 9.1,
    delta_label: '+9.1% vs last 7d',
  },
  trophies_forged: {
    value: 85,
    delta_30d: null,
    sparkline: [
      0, 2, 2, 0, 1, 4, 4, 2, 2, 2,
      3, 1, 0, 2, 2, 3, 4, 5, 5, 2,
      2, 1, 3, 9, 6, 4, 3, 4, 6, 1,
    ],
  },
  badges_granted: {
    value: 182,
    delta_30d: null,
  },
  cpt: {
    locked: true,
    label: 'Coming soon',
    tooltip: 'Cost per trophy will be available with billing in v.3',
  },
};

export const mockSecondaryMetrics = {
  total_badges_granted: {
    value: 182,
    label: 'verified actions',
  },
  cross_hall_overlap: [
    { brand_username: 'doritos_test', overlap_percent: 44 },
    { brand_username: 'redbull_test', overlap_percent: 28 },
    { brand_username: 'samsung_test', overlap_percent: 12 },
  ],
  multi_platform_users_percent: 58,
  achievement_velocity: {
    value: 0.1,
    label: 'per pursuer per day',
  },
};

export const mockAudience = {
  platforms_breakdown: [
    { platform: 'steam', user_count: 24, percent: 48 },
    { platform: 'riot', user_count: 17, percent: 34 },
    { platform: 'discord', user_count: 6, percent: 12 },
    { platform: 'strava', user_count: 3, percent: 6 },
  ],
  top_achievements: [
    {
      badge_id: '27a94ff4-2ff1-4680-b3f8-556f946bd610',
      badge_name: 'Diamond IV LoL',
      grants: 30,
      platform: 'riot',
    },
    {
      badge_id: 'eeea37d1-3c30-403e-aa47-f49344aeb931',
      badge_name: '100h Steam',
      grants: 26,
      platform: 'steam',
    },
    {
      badge_id: '75e20b1d-8e29-442b-80b6-0bd2d8ed85f9',
      badge_name: 'Steam Achievement Hunter',
      grants: 24,
      platform: 'steam',
    },
  ],
  keywords_cross_discord: [],
  funnel: {
    started_pursuit: 140,
    earned_first_badge: 50,
    forged_trophy: 85,
    conversion_start_to_forge_percent: 60.71,
  },
};

export const mockCampaigns = {
  data: [
    {
      trophy_id: '57f6ec62-bcfd-4cd6-9c8a-93727f27c1df',
      name: 'Promo Verano LATAM',
      status: 'draft',
      created_at: '2026-05-03T14:02:07.000000Z',
      pursuers: 0,
      forges: 0,
      conversion_percent: 0,
      thumbnail_url: '',
    },
    {
      trophy_id: '2033558d-a76b-492b-a772-6bae7c92731d',
      name: 'Domina LoL',
      status: 'active',
      created_at: '2026-04-14T14:02:07.000000Z',
      pursuers: 47,
      forges: 28,
      conversion_percent: 59.57,
      thumbnail_url: '',
    },
    {
      trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      name: 'Conecta Discord',
      status: 'active',
      created_at: '2026-04-10T14:02:07.000000Z',
      pursuers: 50,
      forges: 41,
      conversion_percent: 82,
      thumbnail_url: '',
    },
    {
      trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      name: '100h en Steam',
      status: 'active',
      created_at: '2026-03-24T14:02:07.000000Z',
      pursuers: 43,
      forges: 16,
      conversion_percent: 37.21,
      thumbnail_url: '',
    },
  ],
  meta: {
    total: 4,
    per_page: 10,
    current_page: 1,
  },
};

export const mockActivity = {
  data: [
    {
      id: 'evt_grant_252',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_033', avatar_url: '' },
      target: { badge_name: 'Diamond IV LoL', platform: 'riot' },
      timestamp: '2026-05-07T14:02:07.000000Z',
      human_time: '1 hour ago',
    },
    {
      id: 'evt_grant_251',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_043', avatar_url: '' },
      target: { badge_name: '500h Steam', platform: 'steam' },
      timestamp: '2026-05-07T14:02:07.000000Z',
      human_time: '1 hour ago',
    },
    {
      id: 'evt_pursuit_14',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_003', avatar_url: '' },
      target: {
        trophy_name: 'Domina LoL',
        trophy_id: '2033558d-a76b-492b-a772-6bae7c92731d',
      },
      timestamp: '2026-05-07T14:02:07.000000Z',
      human_time: '1 hour ago',
    },
    {
      id: 'evt_pursuit_638',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_002', avatar_url: '' },
      target: {
        trophy_name: '100h en Steam',
        trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      },
      timestamp: '2026-05-07T14:02:07.000000Z',
      human_time: '1 hour ago',
    },
    {
      id: 'evt_pursuit_438',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_048', avatar_url: '' },
      target: {
        trophy_name: 'Conecta Discord',
        trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      },
      timestamp: '2026-05-07T14:02:07.000000Z',
      human_time: '1 hour ago',
    },
    {
      id: 'evt_pursuit_559',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_046', avatar_url: '' },
      target: {
        trophy_name: '100h en Steam',
        trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      },
      timestamp: '2026-05-07T08:13:41.000000Z',
      human_time: '6 hours ago',
    },
    {
      id: 'evt_pursuit_8',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_032', avatar_url: '' },
      target: {
        trophy_name: 'Domina LoL',
        trophy_id: '2033558d-a76b-492b-a772-6bae7c92731d',
      },
      timestamp: '2026-05-07T05:38:25.000000Z',
      human_time: '9 hours ago',
    },
    {
      id: 'evt_grant_248',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_014', avatar_url: '' },
      target: { badge_name: 'Discord Verified', platform: 'discord' },
      timestamp: '2026-05-07T04:38:24.000000Z',
      human_time: '10 hours ago',
    },
    {
      id: 'evt_grant_249',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_020', avatar_url: '' },
      target: { badge_name: 'Steam Achievement Hunter', platform: 'steam' },
      timestamp: '2026-05-07T04:05:13.000000Z',
      human_time: '11 hours ago',
    },
    {
      id: 'evt_pursuit_577',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_014', avatar_url: '' },
      target: {
        trophy_name: '100h en Steam',
        trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      },
      timestamp: '2026-05-07T01:11:49.000000Z',
      human_time: '13 hours ago',
    },
    {
      id: 'evt_pursuit_263',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_008', avatar_url: '' },
      target: {
        trophy_name: 'Conecta Discord',
        trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      },
      timestamp: '2026-05-07T01:05:07.000000Z',
      human_time: '14 hours ago',
    },
    {
      id: 'evt_pursuit_569',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_023', avatar_url: '' },
      target: {
        trophy_name: '100h en Steam',
        trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      },
      timestamp: '2026-05-06T23:54:47.000000Z',
      human_time: '15 hours ago',
    },
    {
      id: 'evt_pursuit_16',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_024', avatar_url: '' },
      target: {
        trophy_name: 'Domina LoL',
        trophy_id: '2033558d-a76b-492b-a772-6bae7c92731d',
      },
      timestamp: '2026-05-06T23:24:27.000000Z',
      human_time: '15 hours ago',
    },
    {
      id: 'evt_pursuit_211',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_034', avatar_url: '' },
      target: {
        trophy_name: 'Conecta Discord',
        trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      },
      timestamp: '2026-05-06T22:54:45.000000Z',
      human_time: '16 hours ago',
    },
    {
      id: 'evt_pursuit_220',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_015', avatar_url: '' },
      target: {
        trophy_name: 'Conecta Discord',
        trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      },
      timestamp: '2026-05-06T21:38:19.000000Z',
      human_time: '17 hours ago',
    },
    {
      id: 'evt_pursuit_601',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_048', avatar_url: '' },
      target: {
        trophy_name: '100h en Steam',
        trophy_id: 'b81ae91f-edf0-4a8a-ab05-1937d0467d13',
      },
      timestamp: '2026-05-06T21:15:37.000000Z',
      human_time: '17 hours ago',
    },
    {
      id: 'evt_pursuit_269',
      type: 'pursuer_started',
      icon: 'P',
      actor: { username: 'tp_027', avatar_url: '' },
      target: {
        trophy_name: 'Conecta Discord',
        trophy_id: 'eb88c6f8-7b12-49fb-bd97-24beca7f4e28',
      },
      timestamp: '2026-05-06T16:27:50.000000Z',
      human_time: '22 hours ago',
    },
    {
      id: 'evt_grant_243',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_018', avatar_url: '' },
      target: { badge_name: 'Master Tier LoL', platform: 'riot' },
      timestamp: '2026-05-06T16:26:27.000000Z',
      human_time: '22 hours ago',
    },
    {
      id: 'evt_grant_245',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_032', avatar_url: '' },
      target: { badge_name: 'Diamond IV LoL', platform: 'riot' },
      timestamp: '2026-05-06T11:53:48.000000Z',
      human_time: '1 day ago',
    },
    {
      id: 'evt_grant_231',
      type: 'badge_granted',
      icon: 'B',
      actor: { username: 'tp_001', avatar_url: '' },
      target: { badge_name: 'Master Tier LoL', platform: 'riot' },
      timestamp: '2026-05-06T11:31:40.000000Z',
      human_time: '1 day ago',
    },
  ],
};
