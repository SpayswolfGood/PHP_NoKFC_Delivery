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

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .grid-2 {
                grid-template-columns: 1.2fr 0.8fr;
            }
        }

        .ingredient-item {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 16px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        @media (min-width: 480px) {
            .ingredient-item {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .ingredient-item:last-child {
            border-bottom: none;
        }

        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        .toggle-wrap input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .inv-input {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: .875rem;
        }

        .inv-input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn-danger-xs {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.15s;
        }

        .btn-danger-xs:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-success-xs {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.15s;
        }

        .btn-success-xs:hover {
            background: #e0f2fe;
            color: #0369a1;
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
                    <button class="btn btn-ghost btn-sm" type="button" onclick="refreshAll()">Обновить всё</button>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button class="btn btn-ghost" type="submit">Выйти</button>
                    </form>
                </div>
            </div>
        </header>

        <div id="flash" class="flash"
            style="display:none; background:#dcfce7; color:#15803d; border-left:4px solid #16a34a; padding:12px 24px; font-size:0.9rem; font-weight:500; margin-bottom: 16px;">
        </div>

        <div class="grid-2">
            <section class="card">
                <div class="card-head">
                    <span class="chip">Очередь</span>
                    <h2 class="card-title">Заказы в работе</h2>
                </div>
                <div id="orders-wrap" class="list-stack">
                    <div class="empty-state">Загрузка заказов…</div>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <span class="chip">Склад</span>
                    <h2 class="card-title">Наличие и CRUD ингредиентов</h2>
                </div>

                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; margin-bottom: 16px;">
                    <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: #334155;">Новый
                        ингредиент</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <input type="text" id="new-ing-name" placeholder="Название (например, Томаты)" class="inv-input"
                            style="flex: 2; min-width: 150px;">
                        <input type="number" id="new-ing-qty" placeholder="Кол-во" value="0" min="0" class="inv-input"
                            style="flex: 1; min-width: 70px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="createIngredient()"
                            style="background: var(--accent); border:none;">Добавить</button>
                    </div>
                </div>

                <div id="ingredients-wrap" class="list-stack">
                    <div class="empty-state">Загрузка склада…</div>
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
            confirmed: 'Готов к выдаче'
        };

        function showFlash(text, isError = false) {
            flashEl.style.display = 'block';
            flashEl.textContent = text;
            if (isError) {
                flashEl.style.background = '#fee2e2';
                flashEl.style.color = '#b91c1c';
                flashEl.style.borderLeft = '4px solid #ef4444';
            } else {
                flashEl.style.background = '#dcfce7';
                flashEl.style.color = '#15803d';
                flashEl.style.borderLeft = '4px solid #16a34a';
            }
            setTimeout(() => { flashEl.style.display = 'none'; }, 5000);
        }

        async function api(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };
            if (body) options.body = JSON.stringify(body);

            const response = await fetch(url, options);
            if (!response.ok) {
                const err = await response.json().catch(() => ({}));
                throw new Error(err.message || 'Ошибка запроса');
            }
            return response.status !== 204 ? response.json() : null;
        }

        function escapeHtml(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        /* ── Заказы ── */
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
                        i.quantity + '× ' + (i.dish ? i.dish.name : 'блюдо')
                    ).join(', ') || '—';

                    let actions = '';
                    if (order.status === 'new') {
                        actions = '<button type="button" class="btn btn-primary btn-sm" onclick="setPreparing(' + order.id + ')">Начать готовить</button>';
                    } else if (order.status === 'preparing') {
                        actions = '<button type="button" class="btn btn-primary btn-sm" onclick="setReady(' + order.id + ')">Готово к выдаче</button>';
                    }
                    return '' +
                        '<div class="list-item">' +
                        '<div style="flex:1;min-width:200px;">' +
                        '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
                        '<strong>№ ' + order.id + '</strong>' +
                        '<span class="badge">' + (statusRu[order.status] || order.status) + '</span>' +
                        '</div>' +
                        '<div class="muted" style="margin-top:6px;">' + escapeHtml(order.delivery_address || '') + '</div>' +
                        '<div class="muted" style="margin-top:4px;">' + escapeHtml(items) + '</div>' +
                        '</div>' +
                        '<div style="display:flex;gap:8px;flex-wrap:wrap;">' + actions + '</div>' +
                        '</div>';
                }).join('');
            } catch (e) {
                wrap.innerHTML = '<div class="empty-state">' + escapeHtml(e.message) + '</div>';
                showFlash(e.message, true);
            }
        }

        async function setPreparing(id) {
            try {
                await api('/app-api/kitchen/orders/' + id + '/preparing', 'PATCH');
                showFlash('Заказ переведён в «Готовится».', false);
                await loadOrders();
            } catch (e) { showFlash(e.message, true); }
        }

        async function setReady(id) {
            try {
                await api('/app-api/kitchen/orders/' + id + '/ready', 'PATCH');
                showFlash('Заказ готов к выдаче курьеру.', false);
                await loadOrders();
            } catch (e) { showFlash(e.message, true); }
        }

        async function loadIngredients() {
            const wrap = document.getElementById('ingredients-wrap');
            try {
                const ingredients = await api('/app-api/kitchen/ingredients');
                if (!ingredients.length) {
                    wrap.innerHTML = '<div class="empty-state">Список ингредиентов пуст.</div>';
                    return;
                }
                wrap.innerHTML = ingredients.map((ing) => {
                    return '' +
                        '<div class="ingredient-item" id="ing-row-' + ing.id + '">' +
                        '<div style="display:flex; flex-direction:column; gap:4px; flex:1;">' +
                        '<input type="text" id="ing-name-' + ing.id + '" value="' + escapeHtml(ing.name) + '" class="inv-input" style="font-weight:500; font-size:0.95rem; border:1px solid transparent; padding:2px 4px; background:transparent;" onfocus="this.style.borderColor=\'#cbd5e1\'" onblur="updateIngredientInline(' + ing.id + ')">' +
                        '<div style="display:flex; align-items:center; gap:8px; margin-top:2px;">' +
                        '<span class="muted" style="font-size:0.8rem;">Кол-во:</span>' +
                        '<input type="number" id="ing-qty-' + ing.id + '" value="' + ing.quantity + '" min="0" class="inv-input" style="width:70px; padding:2px 6px; font-size:0.85rem;" onchange="updateIngredientInline(' + ing.id + ')">' +
                        '</div>' +
                        '</div>' +
                        '<div style="display:flex; align-items:center; gap:12px; margin-top:6px;">' +
                        '<label class="toggle-wrap">' +
                        '<input type="checkbox" id="ing-check-' + ing.id + '" ' + (ing.is_available ? 'checked' : '') + ' onchange="updateIngredientInline(' + ing.id + ')">' +
                        '<span class="muted" style="font-size:0.8rem;">' + (ing.is_available ? 'В наличии' : 'Нет') + '</span>' +
                        '</label>' +
                        '<button type="button" class="btn-danger-xs" onclick="deleteIngredient(' + ing.id + ')">Удалить</button>' +
                        '</div>' +
                        '</div>';
                }).join('');
            } catch (e) {
                wrap.innerHTML = '<div class="empty-state">' + escapeHtml(e.message) + '</div>';
            }
        }

        async function createIngredient() {
            const nameInput = document.getElementById('new-ing-name');
            const qtyInput = document.getElementById('new-ing-qty');
            const name = nameInput.value.trim();
            const quantity = parseInt(qtyInput.value) || 0;

            if (!name) {
                showFlash('Введите название ингредиента', true);
                return;
            }

            try {
                await api('/app-api/kitchen/ingredients', 'POST', { name, quantity, is_available: quantity > 0 });
                showFlash('Ингредиент успешно добавлен на склад.', false);
                nameInput.value = '';
                qtyInput.value = '0';
                await loadIngredients();
            } catch (e) {
                showFlash(e.message, true);
            }
        }

        async function updateIngredientInline(id) {
            const name = document.getElementById('ing-name-' + id).value.trim();
            const quantity = parseInt(document.getElementById('ing-qty-' + id).value) || 0;
            const is_available = document.getElementById('ing-check-' + id).checked;

            if (!name) {
                showFlash('Название ингредиента не может быть пустым', true);
                await loadIngredients();
                return;
            }

            try {
                await api('/app-api/kitchen/ingredients/' + id, 'PUT', { name, quantity, is_available });
                showFlash('Данные ингредиента обновлены.', false);
                const labelText = document.querySelector('#ing-row-' + id + ' .toggle-wrap span');
                if (labelText) labelText.textContent = is_available ? 'В наличии' : 'Нет';
            } catch (e) {
                showFlash(e.message, true);
                await loadIngredients();
            }
        }

        async function deleteIngredient(id) {
            if (!confirm('Вы уверены, что хотите полностью удалить этот ингредиент со склада?')) return;
            try {
                await api('/app-api/kitchen/ingredients/' + id, 'DELETE');
                showFlash('Ингредиент удален.', false);
                await loadIngredients();
            } catch (e) {
                showFlash(e.message, true);
            }
        }

        function refreshAll() {
            loadOrders();
            loadIngredients();
        }

        refreshAll();
    </script>
</body>

</html>