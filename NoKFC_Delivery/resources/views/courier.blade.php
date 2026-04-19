<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Курьер</title>
    @include('partials.panel-styles')
    <style>
        :root {
            --accent: #1d4ed8;
            --accent-soft: #eff6ff;
            --accent-border: #bfdbfe;
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
                <p class="panel-subtitle">Панель курьера</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill">{{ auth()->user()->name }}</span>
                <button class="btn btn-ghost btn-sm" type="button" onclick="loadAll()">Обновить</button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="flash" class="flash" style="display:none;"></div>

    <div class="grid-2">
        <section class="card">
            <div class="card-head">
                <span class="chip">Лента</span>
                <h2 class="card-title">Свободные заказы</h2>
            </div>
            <div id="available-wrap" class="list-stack">
                <div class="empty-state">Загрузка…</div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="chip">Мои</span>
                <h2 class="card-title">Мои доставки</h2>
            </div>
            <div id="mine-wrap" class="list-stack">
                <div class="empty-state">Загрузка…</div>
            </div>
        </section>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const flashEl = document.getElementById('flash');

const statusRu = {
    confirmed: 'Готов к выдаче',
    on_the_way: 'В пути',
    delivered: 'Доставлен'
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

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function loadAll() {
    const av = document.getElementById('available-wrap');
    const mine = document.getElementById('mine-wrap');
    try {
        const [available, myOrders] = await Promise.all([
            api('/app-api/courier/orders/available'),
            api('/app-api/courier/orders/my')
        ]);

        if (!available.length) {
            av.innerHTML = '<div class="empty-state">Нет заказов в очереди на доставку.</div>';
        } else {
            av.innerHTML = available.map((order) => `
                <div class="list-item">
                    <div>
                        <strong>№ ${order.id}</strong>
                        <div class="muted" style="margin-top:6px;">${escapeHtml(order.delivery_address || '')}</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="claim(${order.id})">Взять заказ</button>
                </div>
            `).join('');
        }

        if (!myOrders.length) {
            mine.innerHTML = '<div class="empty-state">Пока нет активных доставок.</div>';
        } else {
            mine.innerHTML = myOrders.map((order) => {
                const canDeliver = order.status === 'on_the_way';
                return `
                    <div class="list-item">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <strong>№ ${order.id}</strong>
                                <span class="badge ${order.status === 'delivered' ? 'badge-ok' : ''}">${statusRu[order.status] || order.status}</span>
                            </div>
                            <div class="muted" style="margin-top:6px;">${escapeHtml(order.delivery_address || '')}</div>
                        </div>
                        ${canDeliver ? `<button type="button" class="btn btn-primary btn-sm" onclick="deliver(${order.id})">Доставлено</button>` : ''}
                    </div>
                `;
            }).join('');
        }
    } catch (e) {
        av.innerHTML = mine.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        showFlash(e.message, true);
    }
}

async function claim(id) {
    try {
        await api(`/app-api/courier/orders/${id}/claim`, 'PATCH');
        showFlash('Заказ принят в доставку.', false);
        await loadAll();
    } catch (e) {
        showFlash(e.message, true);
    }
}

async function deliver(id) {
    try {
        await api(`/app-api/courier/orders/${id}/deliver`, 'PATCH');
        showFlash('Заказ отмечен как доставленный.', false);
        await loadAll();
    } catch (e) {
        showFlash(e.message, true);
    }
}

loadAll();
</script>
</body>
</html>
