<style>
    :root {
        --brand-red: #e4002b;
        --brand-red-dark: #bf0024;
        --brand-black: #111111;
        --brand-white: #ffffff;
        --brand-bg: #f4f4f4;
        --brand-border: #e7e7e7;
        --accent: var(--brand-red);
        --accent-soft: #fff4f6;
        --accent-border: #ffd6df;
        --success: #0f766e;
        --success-bg: #ecfdf5;
        --warn: #b45309;
        --warn-bg: #fffbeb;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        background: linear-gradient(180deg, #fbfbfb 0%, #f0f0f0 100%);
        color: var(--brand-black);
        min-height: 100vh;
    }
    .panel-wrap {
        max-width: 1180px;
        margin: 0 auto;
        padding: 24px 16px 48px;
    }
    .panel-header {
        background: var(--brand-white);
        border: 1px solid var(--brand-border);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.07);
    }
    .panel-header-strip {
        height: 10px;
        background: repeating-linear-gradient(
            90deg,
            var(--accent),
            var(--accent) 28px,
            var(--brand-white) 28px,
            var(--brand-white) 56px
        );
    }
    .panel-header-inner {
        padding: 18px 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }
    .panel-brand { display: flex; flex-direction: column; gap: 4px; }
    .panel-title {
        margin: 0;
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: 900;
        letter-spacing: 0.02em;
        color: var(--accent);
    }
    .panel-subtitle {
        margin: 0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 700;
        color: #5c5c5c;
    }
    .panel-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .user-pill {
        font-size: 13px;
        font-weight: 700;
        color: #4b4b4b;
        background: var(--accent-soft);
        border: 1px solid var(--accent-border);
        border-radius: 999px;
        padding: 8px 14px;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: transform 0.12s ease, filter 0.12s ease, box-shadow 0.12s ease;
    }
    .btn:active { transform: translateY(1px); }
    .btn-primary {
        background: linear-gradient(180deg, var(--brand-red), var(--brand-red-dark));
        color: var(--brand-white);
        box-shadow: 0 4px 14px rgba(228, 0, 43, 0.35);
    }
    .btn-primary:hover { filter: brightness(1.06); }
    .btn-ghost {
        background: var(--brand-white);
        color: var(--brand-black);
        border: 1px solid #d0d0d0;
    }
    .btn-ghost:hover { background: #fafafa; }
    .btn-danger-outline {
        background: #fff5f5;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .btn-danger-outline:hover { background: #fee2e2; }
    .btn-sm { padding: 8px 12px; font-size: 13px; }
    .btn-block { width: 100%; }
    .flash {
        margin: 0 0 18px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        border: 1px solid var(--accent-border);
        background: var(--accent-soft);
        color: #7e2435;
    }
    .flash.is-error {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #9f1239;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 18px;
    }
    .card {
        background: var(--brand-white);
        border-radius: 16px;
        border: 1px solid var(--brand-border);
        padding: 18px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }
    .card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .chip {
        background: var(--accent);
        color: var(--brand-white);
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        padding: 4px 10px;
        text-transform: uppercase;
    }
    .card-title { margin: 0; font-size: 1.15rem; font-weight: 800; }
    .field { margin-bottom: 12px; }
    .field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #555;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .field input,
    .field select,
    .field textarea {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d9d9d9;
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        color: var(--brand-black);
    }
    .field textarea { min-height: 72px; resize: vertical; }
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: 2px solid #ffd4de;
        border-color: var(--brand-red);
    }
    .row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    @media (max-width: 520px) {
        .row-2 { grid-template-columns: 1fr; }
    }
    hr.sep {
        border: 0;
        height: 1px;
        background: var(--brand-border);
        margin: 16px 0;
    }
    .list-stack { display: flex; flex-direction: column; gap: 10px; }
    .list-item {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid var(--brand-border);
        background: #fafafa;
    }
    .list-item strong { font-size: 15px; }
    .muted { color: #6b6b6b; font-size: 13px; }
    .badge {
        display: inline-block;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: var(--accent-soft);
        color: #9f1736;
        border: 1px solid var(--accent-border);
    }
    .badge-ok { background: var(--success-bg); color: var(--success); border-color: #a7f3d0; }
    .badge-warn { background: var(--warn-bg); color: var(--warn); border-color: #fde68a; }
    .dish-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px;
    }
    .dish-card {
        border: 1px solid var(--brand-border);
        border-radius: 14px;
        padding: 14px;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
    .dish-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }
    .price-tag {
        font-weight: 900;
        font-size: 1.1rem;
        color: var(--accent);
        white-space: nowrap;
    }
    .dish-meta { font-size: 13px; color: #666; line-height: 1.4; min-height: 2.8em; }
    .qty-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .qty-row input[type="number"] {
        width: 72px;
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #d9d9d9;
    }
    .empty-state {
        text-align: center;
        padding: 28px 16px;
        color: #888;
        font-size: 14px;
        border: 1px dashed var(--brand-border);
        border-radius: 12px;
        background: #fafafa;
    }
</style>
