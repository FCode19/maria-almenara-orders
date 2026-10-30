(() => {
    const status = document.getElementById('api-result-status');
    const endpointLabel = document.getElementById('api-result-endpoint');
    const timeLabel = document.getElementById('api-result-time');
    const output = document.getElementById('api-result-body');

    const showResult = (state, endpoint, milliseconds, body) => {
        status.textContent = state.label;
        status.className = `api-result-status ${state.className}`;
        endpointLabel.textContent = endpoint;
        timeLabel.textContent = `${milliseconds} ms`;
        output.textContent = JSON.stringify(body, null, 2);
    };

    document.querySelectorAll('.api-run').forEach(button => {
        button.addEventListener('click', async () => {
            const card = button.closest('.api-card');
            const input = card.querySelector('.api-parameter');
            let endpoint = card.dataset.endpoint;
            if (input) {
                const value = input.value.trim();
                if (!value) {
                    input.focus();
                    showResult({label: 'Falta un dato', className: 'is-error'}, endpoint, 0, {status: 'error', message: 'Completa el parámetro para ejecutar la consulta.'});
                    return;
                }
                endpoint = input.name === 'code'
                    ? endpoint.replace('{code}', encodeURIComponent(value))
                    : `${endpoint}?consulta=${encodeURIComponent(value)}`;
            }

            button.disabled = true;
            button.textContent = 'Consultando…';
            status.textContent = 'Consultando';
            status.className = 'api-result-status is-loading';
            endpointLabel.textContent = endpoint;
            output.textContent = 'Esperando respuesta…';
            const started = performance.now();
            try {
                const response = await fetch(endpoint, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
                const elapsed = Math.round(performance.now() - started);
                let body;
                try { body = await response.json(); } catch { body = {status: 'error', message: 'La respuesta no contiene JSON válido.'}; }
                showResult(
                    response.ok ? {label: `${response.status} Disponible`, className: 'is-success'} : {label: `${response.status} Con error`, className: 'is-error'},
                    endpoint,
                    elapsed,
                    body
                );
            } catch (error) {
                showResult({label: 'Sin comunicación', className: 'is-error'}, endpoint, Math.round(performance.now() - started), {status: 'error', message: error.message});
            } finally {
                button.disabled = false;
                button.textContent = 'Probar';
            }
        });
    });
})();
