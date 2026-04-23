@if(config('app.show_login_helper') && config('app.admin_email') && config('app.admin_password'))
    <div
        x-data="{
            fill(selector, value) {
                const el = document.querySelector(selector);
                if (!el) return;
                const setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                setter.call(el, value);
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                el.dispatchEvent(new Event('blur', { bubbles: true }));
            },
            fillEmail() { this.fill('input[type=email], input[wire\\:model=data\\.email], #data\\.email', @js(config('app.admin_email'))); },
            fillPassword() { this.fill('input[type=password], input[wire\\:model=data\\.password], #data\\.password', @js(config('app.admin_password'))); },
            fillBoth() { this.fillEmail(); this.fillPassword(); },
        }"
        style="margin-top: 1.5rem; padding: 1rem; background: #F7F8FA; border: 1px solid #E2E5EA; border-radius: 8px;"
    >
        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #8B95A5; margin-bottom: 0.75rem; text-align: center;">
            Credenciales de acceso · click para rellenar
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <button
                type="button"
                x-on:click="fillEmail()"
                style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.875rem; background: #FFFFFF; border: 1px solid #E2E5EA; border-radius: 6px; cursor: pointer; transition: all 150ms ease-out; width: 100%; text-align: left;"
                onmouseover="this.style.borderColor='#00243D'; this.style.background='#F0F2F5';"
                onmouseout="this.style.borderColor='#E2E5EA'; this.style.background='#FFFFFF';"
            >
                <span style="font-size: 11px; font-weight: 600; color: #8B95A5; text-transform: uppercase; letter-spacing: 0.05em;">Email</span>
                <span style="font-size: 13px; font-family: ui-monospace, 'SF Mono', Menlo, monospace; color: #1A1D21;">{{ config('app.admin_email') }}</span>
            </button>

            <button
                type="button"
                x-on:click="fillPassword()"
                style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.875rem; background: #FFFFFF; border: 1px solid #E2E5EA; border-radius: 6px; cursor: pointer; transition: all 150ms ease-out; width: 100%; text-align: left;"
                onmouseover="this.style.borderColor='#00243D'; this.style.background='#F0F2F5';"
                onmouseout="this.style.borderColor='#E2E5EA'; this.style.background='#FFFFFF';"
            >
                <span style="font-size: 11px; font-weight: 600; color: #8B95A5; text-transform: uppercase; letter-spacing: 0.05em;">Contraseña</span>
                <span style="font-size: 13px; font-family: ui-monospace, 'SF Mono', Menlo, monospace; color: #1A1D21;">{{ config('app.admin_password') }}</span>
            </button>

            <button
                type="button"
                x-on:click="fillBoth()"
                style="padding: 0.5rem 0.875rem; background: #00243D; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; transition: background 150ms ease-out;"
                onmouseover="this.style.background='#003B64';"
                onmouseout="this.style.background='#00243D';"
            >
                Rellenar ambos
            </button>
        </div>

        <div style="margin-top: 0.75rem; font-size: 10px; color: #B8C0CC; text-align: center;">
            Solo visible en desarrollo · deshabilitar en producción con <code style="font-family: ui-monospace, 'SF Mono', Menlo, monospace;">SHOW_LOGIN_HELPER=false</code>
        </div>
    </div>
@endif
