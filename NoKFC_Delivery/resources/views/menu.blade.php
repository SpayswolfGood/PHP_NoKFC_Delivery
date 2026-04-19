<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Меню и заказы</title>
    @include('partials.panel-styles')
    <style>:root { --accent-soft: #fff4f6; --accent-border: #ffd6df; }</style>
</head>
<body>
<div class="panel-wrap">
    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Заказ еды на дом</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="flash" class="flash" style="display:none;" role="status"></div>

    <div class="grid-2">
        <section class="card">
            <div class="card-head">
                <span class="chip">Меню</span>
                <h2 class="card-title">Выберите блюда</h2>
            </div>
            <div class="field">
                <label for="address">Адрес доставки</label>
                <input id="address" type="text" placeholder="Город, улица, дом, квартира" autocomplete="street-address">
            </div>
            <div class="field">
                <label for="note">Комментарий к заказу</label>
                <input id="note" type="text" placeholder="Например, домофон, время">
            </div>
            <div id="dishes-wrap">
                <div class="empty-state">Загрузка меню…</div>
            </div>
            <hr class="sep">
            <button class="btn btn-primary btn-block" type="button" onclick="createOrder()">Оформить заказ</button>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="chip">Заказы</span>
                <h2 class="card-title">Мои заказы</h2>
            </div>
            <div id="orders-wrap" class="list-stack">
                <div class="empty-state">Загрузка…</div>
            </div>
        </section>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const flashEl = document.getElementById('flash');

const statusRu = {
    new: 'Новый',
    preparing: 'Готовится',
    confirmed: 'Готов к выдаче',
    on_the_way: 'В пути',
    delivered: 'Доставлен',
    cancelled: 'Отменён'
};

function showFlash(text, isError = false) {
    flashEl.style.display = 'block';
    flashEl.textContent = text;
    flashEl.classList.toggle('is-error', isError);
}

async function api(url, method = 'GET', body = null) {
    const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: body ? JSON.stringify(body) : null
    });

    if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        const msg = err.message || (err.errors ? Object.values(err.errors).flat().join(' ') : 'Ошибка запроса');
        throw new Error(msg);
    }

    if (response.status === 204) return null;
    return response.json();
}

function badgeClass(status) {
    if (status === 'delivered') return 'badge badge-ok';
    if (status === 'cancelled') return 'badge badge-warn';
    return 'badge';
}

async function loadDishes() {
    const wrap = document.getElementById('dishes-wrap');
    try {
        const dishes = await api('/app-api/customer/dishes');
        if (!dishes.length) {
            wrap.innerHTML = '<div class="empty-state">В меню пока нет активных блюд.</div>';
            return;
        }
        wrap.innerHTML = `<div class="dish-grid">${dishes.map((d) => `
            <article class="dish-card" data-id="${d.id}">
                <div class="dish-card-top">
                    <strong>${escapeHtml(d.name)}</strong>
                    <span class="price-tag">${Number(d.price).toFixed(2)} ₽</span>
                </div>
                <p class="dish-meta">${d.description ? escapeHtml(d.description) : 'Без описания'}</p>
                <label class="qty-row" style="cursor:pointer;">
                    <input type="checkbox" class="dish-check" value="${d.id}">
                    <span style="font-weight:700;font-size:13px;">В корзину</span>
                </label>
                <div class="qty-row">
                    <span class="muted">Кол-во</span>
                    <input type="number" class="dish-qty" data-id="${d.id}" value="1" min="1" aria-label="Количество">
                </div>
            </article>
        `).join('')}</div>`;
    } catch (e) {
        wrap.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        showFlash(e.message, true);
    }
}

async function loadOrders() {
    const wrap = document.getElementById('orders-wrap');
    try {
        const orders = await api('/app-api/customer/orders');
        if (!orders.length) {
            wrap.innerHTML = '<div class="empty-state">Вы ещё не делали заказов.</div>';
            return;
        }
        wrap.innerHTML = orders.map((o) => `
            <div class="list-item">
                <div>
                    <strong>№ ${o.id}</strong>
                    <span class="${badgeClass(o.status)}">${statusRu[o.status] || o.status}</span>
                    <div class="muted" style="margin-top:6px;">Сумма: <strong>${Number(o.total_amount).toFixed(2)} ₽</strong></div>
                    ${o.delivery_address ? `<div class="muted">${escapeHtml(o.delivery_address)}</div>` : ''}
                </div>
            </div>
        `).join('');
    } catch (e) {
        wrap.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        showFlash(e.message, true);
    }
}

async function createOrder() {
    const checked = Array.from(document.querySelectorAll('.dish-check:checked'));
    const items = checked.map((checkbox) => {
        const qty = document.querySelector(`.dish-qty[data-id="${checkbox.value}"]`);
        return { dish_id: Number(checkbox.value), quantity: Number(qty?.value || 1) };
    });

    if (!items.length) {
        showFlash('Выберите хотя бы одно блюдо.', true);
        return;
    }

    const addr = document.getElementById('address').value.trim();
    if (!addr) {
        showFlash('Укажите адрес доставки.', true);
        return;
    }

    try {
        await api('/app-api/customer/orders', 'POST', {
            delivery_address: addr,
            note: document.getElementById('note').value.trim() || null,
            items
        });
        showFlash('Заказ оформлен.', false);
        await loadOrders();
        document.querySelectorAll('.dish-check').forEach((el) => { el.checked = false; });
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

loadDishes().then(loadOrders);
</script>
</body>
</html>
