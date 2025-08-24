<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .kpis { display: flex; gap: 12px; margin-bottom: 16px; }
        .card { border: 1px solid #ccc; padding: 10px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #eee; padding: 8px; text-align: left; }
        th:nth-child(2), td:nth-child(2) { text-align: center; }
        th:last-child, td:last-child { text-align: right; }
    </style>
</head>
<body>
<h2>Relatório — Resumo</h2>

<div class="kpis">
    <div class="card">Usuários (total): <strong>{{ $metrics['usersTotal'] }}</strong></div>
    <div class="card">Produtos (total): <strong>{{ $metrics['productsTotal'] }}</strong></div>
    <div class="card">Usuários sem Produtos: <strong>{{ $metrics['usersNoProduct'] }}</strong></div>
</div>

<h3>Resumo por usuário</h3>
<table>
    <thead>
    <tr><th>Usuário</th><th>Qtd. Produtos</th><th>Valor Total</th></tr>
    </thead>
    <tbody>
    @foreach ($topUsers as $u)
        <tr>
            <td>{{ $u['name'] }}</td>
            <td>{{ $u['products_count'] }}</td>
            <td>{{ number_format($u['products_total_value'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
