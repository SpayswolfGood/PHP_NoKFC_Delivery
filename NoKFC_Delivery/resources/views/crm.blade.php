<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Админ-панель</title>
    @include('partials.panel-styles')
</head>
<body>
<div class="panel-wrap">
    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Администрирование</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                <button class="btn btn-ghost btn-sm" type="button" onclick="loadAll()">Обновить</button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="status" class="flash">Готово. Добавьте блюда и сотрудников ниже.</div>

    <div class="grid-2">
        <section class="card">
            <div class="card-head">
                <span class="chip">Меню</span>
                <h2 class="card-title">Каталог блюд</h2>
            </div>
            <div class="field">
                <label for="dish-name">Название</label>
                <input id="dish-name" type="text" placeholder="Например, Баскет 6 крыльев" autocomplete="off">
            </div>
            <div class="row-2">
                <div class="field" style="margin-bottom:0;">
                    <label for="dish-price">Цена, ₽</label>
                    <input id="dish-price" type="number" step="0.01" min="0.01" placeholder="0.00">
                </div>
                <div class="field" style="margin-bottom:0;">
                    <label for="dish-active">В продаже</label>
                    <select id="dish-active">
                        <option value="1" selected>Да</option>
                        <option value="0">Нет (скрыто)</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="dish-description">Описание</label>
                <textarea id="dish-description" placeholder="Краткое описание для меню"></textarea>
            </div>
            <button class="btn btn-primary btn-block" type="button" onclick="createDish()">Добавить блюдо</button>
            <hr class="sep">
            <h3 class="card-title" style="font-size:1rem;margin-bottom:10px;">Список блюд</h3>
            <div id="dishes-list" class="list-stack">
                <div class="empty-state">Загрузка…</div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="chip">Персонал</span>
                <h2 class="card-title">Учётные записи</h2>
            </div>
            <div class="field">
                <label for="staff-name">Имя</label>
                <input id="staff-name" type="text" placeholder="ФИО или позывной">
            </div>
            <div class="field">
                <label for="staff-email">Email (логин)</label>
                <input id="staff-email" type="email" autocomplete="off" placeholder="staff@example.com">
            </div>
            <div class="field">
                <label for="staff-password">Пароль</label>
                <input id="staff-password" type="password" autocomplete="new-password" placeholder="Не менее 8 символов">
            </div>
            <div class="field">
                <label for="staff-role">Роль</label>
                <select id="staff-role">
                    <option value="kitchen">Кухня</option>
                    <option value="courier">Курьер</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            <button class="btn btn-primary btn-block" type="button" onclick="createStaff()">Создать сотрудника</button>
            <hr class="sep">
            <h3 class="card-title" style="font-size:1rem;margin-bottom:10px;">Сотрудники</h3>
            <div id="staff-list" class="list-stack">
                <div class="empty-state">Загрузка…</div>
            </div>
        </section>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const statusEl = document.getElementById('status');

const roleRu = {
    admin: 'Админ',
    kitchen: 'Кухня',
    courier: 'Курьер'
};

function setStatus(text, isError = false) {
    statusEl.style.display = 'block';
    statusEl.textContent = text;
    statusEl.classList.toggle('is-error', isError);
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

    if (response.status === 204) return null;

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        const msg = error.message || (error.errors ? Object.values(error.errors).flat().join(' ') : 'Ошибка запроса');
        throw new Error(msg);
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

async function loadDishes() {
    const list = document.getElementById('dishes-list');
    try {
        const dishes = await api('/app-api/admin/dishes');
        if (!dishes.length) {
            list.innerHTML = '<div class="empty-state">Блюд пока нет.</div>';
            return;
        }
        list.innerHTML = dishes.map((dish) => `
            <div class="list-item">
                <div style="flex:1;min-width:0;">
                    <strong>${escapeHtml(dish.name)}</strong>
                    <span class="badge" style="margin-left:8px;">${Number(dish.price).toFixed(2)} ₽</span>
                    ${dish.is_active ? '' : '<span class="badge badge-warn" style="margin-left:8px;">Скрыто</span>'}
                    <div class="muted" style="margin-top:6px;">${dish.description ? escapeHtml(dish.description) : 'Без описания'}</div>
                </div>
                <button type="button" class="btn btn-danger-outline btn-sm" onclick="deleteDish(${dish.id})">Удалить</button>
            </div>
        `).join('');
    } catch (e) {
        list.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        setStatus(e.message, true);
    }
}

async function createDish() {
    const name = document.getElementById('dish-name').value.trim();
    const price = Number(document.getElementById('dish-price').value);
    const description = document.getElementById('dish-description').value.trim();
    const is_active = document.getElementById('dish-active').value === '1';

    if (!name || !price || price <= 0) {
        setStatus('Укажите название и корректную цену.', true);
        return;
    }

    try {
        await api('/app-api/admin/dishes', 'POST', { name, price, description: description || null, is_active });
        setStatus('Блюдо добавлено.', false);
        document.getElementById('dish-name').value = '';
        document.getElementById('dish-price').value = '';
        document.getElementById('dish-description').value = '';
        await loadDishes();
    } catch (e) {
        setStatus(e.message, true);
    }
}

async function deleteDish(id) {
    if (!confirm('Удалить это блюдо?')) return;
    try {
        await api(`/app-api/admin/dishes/${id}`, 'DELETE');
        setStatus('Блюдо удалено.', false);
        await loadDishes();
    } catch (e) {
        setStatus(e.message, true);
    }
}

async function loadStaff() {
    const list = document.getElementById('staff-list');
    try {
        const staff = await api('/app-api/admin/staff');
        if (!staff.length) {
            list.innerHTML = '<div class="empty-state">Сотрудников пока нет.</div>';
            return;
        }
        list.innerHTML = staff.map((user) => `
            <div class="list-item">
                <div>
                    <strong>${escapeHtml(user.name)}</strong>
                    <span class="badge" style="margin-left:8px;">${roleRu[user.role] || user.role}</span>
                    <div class="muted" style="margin-top:6px;">${escapeHtml(user.email)}</div>
                </div>
                <button type="button" class="btn btn-danger-outline btn-sm" onclick="deleteStaff(${user.id})">Удалить</button>
            </div>
        `).join('');
    } catch (e) {
        list.innerHTML = `<div class="empty-state">${escapeHtml(e.message)}</div>`;
        setStatus(e.message, true);
    }
}

async function createStaff() {
    try {
        await api('/app-api/admin/staff', 'POST', {
            name: document.getElementById('staff-name').value.trim(),
            email: document.getElementById('staff-email').value.trim(),
            password: document.getElementById('staff-password').value,
            role: document.getElementById('staff-role').value
        });
        setStatus('Сотрудник создан.', false);
        document.getElementById('staff-name').value = '';
        document.getElementById('staff-email').value = '';
        document.getElementById('staff-password').value = '';
        await loadStaff();
    } catch (e) {
        setStatus(e.message, true);
    }
}

async function deleteStaff(id) {
    if (!confirm('Удалить учётную запись сотрудника?')) return;
    try {
        await api(`/app-api/admin/staff/${id}`, 'DELETE');
        setStatus('Запись удалена.', false);
        await loadStaff();
    } catch (e) {
        setStatus(e.message, true);
    }
}

async function loadAll() {
    try {
        await Promise.all([loadDishes(), loadStaff()]);
        setStatus('Данные обновлены.', false);
    } catch (e) {
        setStatus(e.message, true);
    }
}

loadAll();
</script>
</body>
</html>
