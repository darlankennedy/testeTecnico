function required(name: keyof ImportMetaEnv): string {
  const value = import.meta.env[name]
  if (!value) throw new Error(`[env] Missing ${name}. Did you set it in .env?`)
  return value
}

export const env = {
  apiUrl: required('VITE_API_URL'),
  backendUrl: import.meta.env.VITE_BACKEND_URL ?? undefined,
  appName: import.meta.env.VITE_APP_NAME ?? 'Meu App',
}

console.log(env)
