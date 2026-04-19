<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Кухня</title>
    @include('partials.panel-styles')
    <style>
        :root {
            --accent: #c2410c;
            --accent-soft: #fff7ed;
            --accent-border: #fed7aa;
        }
    </style>
</head>
<body>
<div class="panel-wrap">
    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Панель кухни</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill">{{ auth()->user()->name }}</span>
                <button class="btn btn-ghost btn-sm" type="button" onclick="loadOrders()">Обновить</button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="flash" class="flash" style="display:none;"></div>

    <section class="card">
        <div class="card-head">
            <span class="chip">Очередь</span>
            <h2 class="card-title">Заказы в работе</h2>
        </div>
        <div id="orders-wrap" class="list-stack">
            <div class="empty-state">Загрузка…</div>
        </div>
    </section>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const flashEl = document.getElementById('flash');

const statusRu = {
    new: 'Новый',
    preparing: 'Готовится',
    confirmed: 'Готов к выдаче'
};

function showFlash(text, isError = false) {
    flashEl.style.display = 'block';
    flashEl.textContent = text;
    flashEl.classList.toggle('is-error', isError);
}

async function api(url, method = 'GET') {
    const response = await fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    });
    if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        throw new Error(err.message || 'Ошибка запроса');
    }
    return response.json();
}

async function loadOrders() {
    const wrap = document.getElementById('orders-wrap');
    try {
        const orders = await api('/app-api/kitchen/orders');
        if (!orders.length) {
            wrap.innerHTML = '<div class="empty-state">Нет заказов в очереди.</div>';
            return;
        }
        wrap.innerHTML = orders.map((order) => {
            const items = (order.items || []).map((i) =>
                `${i.quantity}× ${i.dish ? i.dish.name : 'блюдо'}`
            ).join(', ') || '—';
            let actions = '';
            if (order.status === 'new') {
                actions = `<button type="button" class="btn btn-primary btn-sm" onclick="setPreparing(${order.id})">Начать готовить</button>`;
            } else if (order.status === 'preparing') {
                actions = `<button type="button" class="btn btn-primary btn-sm" onclick="setReady(${order.id})">Готово к выдаче</button>`;
            }
            return `
                <div class="list-item">
                    <div style="flex:1;min-width:200px;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <strong>№ ${order.id}</strong>
                            <span class="badge">${statusRu[order.status] || order.status}</span>
                        </div>
                        <div class="muted" style="margin-top:6px;">${escapeHtml(order.delivery_address || '')}</div>
                        <div class="muted" style="margin-top:4px;">${escapeHtml(items)}</div>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">${actions}</div>
                </div>
            `;
        }).join('');
    } catch (e) {
        wrap.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        showFlash(e.message, true);
    }
}

async function setPreparing(id) {
    try {
        await api(`/app-api/kitchen/orders/${id}/preparing`, 'PATCH');
        showFlash('Заказ переведён в «Готовится».', false);
        await loadOrders();
    } catch (e) {
        showFlash(e.message, true);
    }
}

async function setReady(id) {
    try {
        await api(`/app-api/kitchen/orders/${id}/ready`, 'PATCH');
        showFlash('Заказ готов к выдаче курьеру.', false);
        await loadOrders();
    } catch (e) {
        showFlash(e.message, true);
    }
}

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

loadOrders();
</script>
</body>
</html>
