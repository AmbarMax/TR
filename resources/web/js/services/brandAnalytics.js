import api from '../api/api.js';
import {
  mockPerformance,
  mockSecondaryMetrics,
  mockAudience,
  mockCampaigns,
  mockActivity,
} from '../mocks/brandDashboard.js';

const useMocks = import.meta.env.VITE_USE_BRAND_MOCKS === 'true';

const fakeDelay = (data, ms = 400) =>
  new Promise(resolve => setTimeout(() => resolve({
    data,
    status: 200,
    headers: {},
    statusText: 'OK',
  }), ms));

export const fetchPerformance = () =>
  useMocks
    ? fakeDelay(mockPerformance)
    : api.get('/api/brand/analytics/performance');

export const fetchSecondaryMetrics = () =>
  useMocks
    ? fakeDelay(mockSecondaryMetrics)
    : api.get('/api/brand/analytics/secondary-metrics');

export const fetchAudience = () =>
  useMocks
    ? fakeDelay(mockAudience)
    : api.get('/api/brand/analytics/audience');

export const fetchCampaigns = (sort = 'created_at') =>
  useMocks
    ? fakeDelay(mockCampaigns)
    : api.get('/api/brand/analytics/campaigns', { params: { sort } });

export const fetchActivity = (limit = 20) =>
  useMocks
    ? fakeDelay(mockActivity)
    : api.get('/api/brand/analytics/activity', { params: { limit } });
