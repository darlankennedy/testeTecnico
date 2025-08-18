export const ROUTES = {
  LOGIN: '/login',
  DASHBOARD: '/dashboard',
  PROFILE: '/profile',
}


export const STORAGE_KEYS = {
  TOKEN: 'token',
  REFRESH_TOKEN: 'refreshToken',
  THEME: 'theme',
  USER: "user"
}

export const APP_CONFIG = {
  DEFAULT_LANGUAGE: 'pt-BR',
  SUPPORTED_LANGUAGES: ['pt-BR', 'en-US'],
  PAGINATION: {
    DEFAULT_PAGE: 1,
    DEFAULT_LIMIT: 10,
    MAX_LIMIT: 100,
  },
}

export const API_PATHS = {
  CSRF: '/sanctum/csrf-cookie',
  LOGIN: '/login',
  LOGOUT: '/logout',
  REGISTER: '/register',
  ME: '/me',


  USERS: '/users',
  USER_LISTENING: '/users/all',
  USERS_COUNT: '/users/count',
  USERS_WITHOUT_PRODUCTS_COUNT: '/users/with-products',
  USER_WITH_PRODUCTS_COUNT: '/users/with-products',
  PRODUCTS: '/products',
  PRODUCTS_COUNT: '/products/count',

  REPORTS_SUMMARY: 'reports/summary'


}
