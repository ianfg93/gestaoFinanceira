export function formatCurrency(value: number | null | undefined, currency = 'BRL'): string {
  if (value == null) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(value)
}

export function formatDate(date: string | null | undefined): string {
  if (!date) return '-'
  const [year, month, day] = date.split('-')
  return `${day}/${month}/${year}`
}

export function formatMonth(ym: string): string {
  const [year, month] = ym.split('-')
  const months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
  return `${months[parseInt(month) - 1]} ${year}`
}

export function currentMonth(): string {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

export function prevMonth(ym: string): string {
  const [y, m] = ym.split('-').map(Number)
  const d = new Date(y, m - 2, 1)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

export function nextMonth(ym: string): string {
  const [y, m] = ym.split('-').map(Number)
  const d = new Date(y, m, 1)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

export const STATUS_LABELS: Record<string, string> = {
  pending:   'Pendente',
  paid:      'Pago',
  overdue:   'Atrasado',
  cancelled: 'Cancelado',
  partial:   'Parcial',
}

export const TYPE_LABELS: Record<string, string> = {
  expense:    'Despesa',
  income:     'Receita',
  investment: 'Investimento',
}

export const STATUS_CLASSES: Record<string, string> = {
  pending:   'badge-yellow',
  paid:      'badge-green',
  overdue:   'badge-red',
  cancelled: 'badge-gray',
  partial:   'badge-blue',
}
