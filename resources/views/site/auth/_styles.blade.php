<style>
    .auth-page { padding: 52px 0 12px; }
    .auth-shell { width: min(100%, 820px); margin: 0 auto; }
    .auth-shell.login-width { width: min(100%, 460px); }
    .auth-heading { margin-bottom: 25px; text-align: center; }
    .auth-heading h1 { margin: 0 0 8px; color: #20242b; font-size: clamp(27px, 4vw, 35px); letter-spacing: -.8px; }
    .auth-heading p { margin: 0; color: var(--muted); font-size: 14px; }
    .auth-card { padding: 31px; border: 1px solid var(--border); border-radius: 17px; background: white; box-shadow: 0 15px 45px rgba(31,41,55,.07); }
    .auth-section + .auth-section { margin-top: 29px; padding-top: 26px; border-top: 1px solid var(--border); }
    .auth-section-title { display: flex; align-items: center; gap: 10px; margin: 0 0 19px; color: #252a32; font-size: 16px; }
    .auth-section-title span { width: 31px; height: 31px; display: grid; place-items: center; border-radius: 9px; background: var(--primary-soft); color: var(--primary); font-size: 13px; }
    .auth-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; }
    .auth-grid.three { grid-template-columns: minmax(0, 1.6fr) minmax(100px, .55fr) minmax(0, 1fr); }
    .field.full { grid-column: 1 / -1; }
    .field label { display: block; margin-bottom: 7px; color: #343a43; font-size: 12px; font-weight: 750; }
    .field input { width: 100%; height: 45px; padding: 0 13px; border: 1px solid #dfe1e6; border-radius: 9px; outline: 0; background: #fafafa; color: var(--text); font-size: 14px; }
    .field input:focus { border-color: rgba(240,78,35,.65); background: white; box-shadow: 0 0 0 3px rgba(240,78,35,.09); }
    .field input[aria-invalid="true"] { border-color: #dc2626; }
    .field-error { margin: 6px 0 0; color: #c62828; font-size: 11px; }
    .auth-alert { margin-bottom: 18px; padding: 12px 14px; border: 1px solid #fecaca; border-radius: 9px; background: #fff1f2; color: #b42318; font-size: 13px; }
    .auth-actions { margin-top: 25px; }
    .auth-submit { width: 100%; min-height: 47px; border: 0; border-radius: 9px; background: var(--cta); color: white; font-weight: 800; cursor: pointer; box-shadow: 0 8px 20px rgba(232,39,26,.18); }
    .auth-submit:hover { background: var(--cta-dark); }
    .auth-links { display: flex; justify-content: space-between; gap: 15px; margin-top: 17px; color: var(--muted); font-size: 12px; }
    .auth-links.center { justify-content: center; }
    .auth-links a { color: var(--primary); font-weight: 700; text-decoration: none; }
    .auth-links a:hover { text-decoration: underline; }
    .required-note { margin: 18px 0 0; color: #8b919b; font-size: 11px; }

    @media (max-width: 650px) {
        .auth-page { padding-top: 30px; }
        .auth-card { padding: 23px 18px; }
        .auth-grid, .auth-grid.three { grid-template-columns: 1fr; }
        .field.full { grid-column: auto; }
    }
</style>
