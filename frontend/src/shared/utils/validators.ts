export function validarCPF(cpf: string): boolean {
  if (!cpf) return false

  const clean = cpf.replace(/\D/g, '')
  if (clean.length !== 11) return false
  if (/^(\d)\1{10}$/.test(clean)) return false

  let soma = 0
  for (let i = 0; i < 9; i++) soma += parseInt(clean[i]) * (10 - i)
  let resto = (soma * 10) % 11
  if (resto === 10) resto = 0
  if (resto !== parseInt(clean[9])) return false

  soma = 0
  for (let i = 0; i < 10; i++) soma += parseInt(clean[i]) * (11 - i)
  resto = (soma * 10) % 11
  if (resto === 10) resto = 0
  if (resto !== parseInt(clean[10])) return false

  return true
}
