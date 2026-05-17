const wizard = (() => {
    const steps = [
        { id: 'requirements', label: 'Server Requirements', desc: 'Verify your server meets all requirements.' },
        { id: 'permissions', label: 'Permissions & Symlinks', desc: 'Check directory permissions and symlink support.' },
        { id: 'database', label: 'Database', desc: 'Enter your database credentials and test the connection.' },
{ id: 'installation', label: 'Installation', desc: 'Run migrations, link storage, and finalise setup.' },
        { id: 'complete', label: 'Complete', desc: 'Your application is ready to use.' },
    ];

    let currentIndex = 0;
    let completedSet = new Set();
    const wizardState = {
        db: { host: '127.0.0.1', name: '', username: '', password: '', tested: false, empty: false, acknowledged: false },
    };

    const stepsNav = document.getElementById('steps-nav');
    const stepContent = document.getElementById('step-content');

    const iconPass = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`;
    const iconFail = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>`;
    const iconWarn = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`;

    const renderCheckList = (items) => {
        const renderItem = (item) => {
            let statusClass = 'pass';
            let icon = iconPass;
            if (!item.status) {
                statusClass = item.required ? 'fail' : 'warn';
                icon = item.required ? iconFail : iconWarn;
            }
            const optional = !item.required ? `<span class="req-optional">Optional</span>` : '';
            const detail = item.detail ? `<span class="req-detail">${item.detail}</span>` : '';
            return `<div class="req-item"><div class="req-label">${item.label}${detail}${optional}</div><span class="req-status ${statusClass}">${icon}</span></div>`;
        };
        return `<div class="req-list">${items.map(renderItem).join('')}</div>`;
    };

    const renderers = {
        requirements() {
            return renderCheckList(window.__serverRequirements || []);
        },
        permissions() {
            return renderCheckList(window.__permissions || []);
        },
        database() {
            const db = wizardState.db;
            const resultHtml = (() => {
                if (!db.tested) return '';
                if (db.error) {
                    return `<div class="db-result error"><strong>Connection failed</strong><span>${db.error}</span></div>`;
                }
                if (db.empty) {
                    return `<div class="db-result success"><strong>Connected</strong><span>Database is empty and ready for use.</span></div>`;
                }
                const checked = db.acknowledged ? 'checked' : '';
                return `<div class="db-result warning">
<strong>Database is not empty</strong>
<span>Running the installer will override the existing data. All current data will be lost.</span>
<label class="db-acknowledge"><input type="checkbox" id="db-ack" ${checked}> I understand and want to proceed</label>
</div>`;
            })();

            return `<div class="form-grid">
<div class="form-field">
<label class="field-label">Host <span class="field-required">*</span></label>
<input class="field-input" id="db-host" type="text" value="${db.host}" placeholder="localhost" autocomplete="off">
</div>
<div class="form-field">
<label class="field-label">Database Name <span class="field-required">*</span></label>
<input class="field-input" id="db-name" type="text" value="${db.name}" placeholder="my_database" autocomplete="off">
</div>
<div class="form-field">
<label class="field-label">Username <span class="field-required">*</span></label>
<input class="field-input" id="db-username" type="text" value="${db.username}" placeholder="root" autocomplete="off">
</div>
<div class="form-field">
<label class="field-label">Password</label>
<input class="field-input" id="db-password" type="password" value="${db.password}" placeholder="Leave blank if none" autocomplete="new-password">
</div>
</div>
<div class="db-test-row">
<button class="btn btn-secondary" id="btn-test-db">Test Connection</button>
</div>
${resultHtml}`;
        },
    };

    function canProceed() {
        const step = steps[currentIndex];
        if (step.id === 'requirements') {
            return (window.__serverRequirements || []).filter(i => i.required).every(i => i.status);
        }
        if (step.id === 'permissions') {
            return (window.__permissions || []).filter(i => i.required).every(i => i.status);
        }
        if (step.id === 'database') {
            const db = wizardState.db;
            if (!db.tested || db.error) return false;
            return db.empty || db.acknowledged;
        }
        return true;
    }

    function renderNav() {
        stepsNav.innerHTML = steps.map((step, index) => {
            const isActive = index === currentIndex;
            const isCompleted = completedSet.has(index);
            const isReachable = isCompleted || index < currentIndex;

            let classes = 'step-item';
            if (isActive) classes += ' active';
            if (isCompleted) classes += ' completed';
            if (isReachable && !isActive) classes += ' reachable';

            return `<div class="${classes}" data-index="${index}"><span class="step-label">${step.label}</span></div>`;
        }).join('');

        stepsNav.querySelectorAll('.step-item.reachable').forEach(el => {
            el.addEventListener('click', () => goTo(parseInt(el.dataset.index)));
        });
    }

    function renderContent() {
        const step = steps[currentIndex];
        const isFirst = currentIndex === 0;
        const isLast = currentIndex === steps.length - 1;
        const proceed = canProceed();

        const backBtn = !isFirst ? `<button class="btn btn-secondary" id="btn-back">Back</button>` : '';
        const nextBtn = !isLast ? `<button class="btn btn-primary" id="btn-next" ${!proceed ? 'disabled' : ''}>Continue</button>` : '';
        const finishBtn = isLast ? `<button class="btn btn-primary" id="btn-finish">Finish setup</button>` : '';

        stepContent.innerHTML = `<div class="step-panel">
<div class="panel-header">
<span class="step-eyebrow">Step ${currentIndex + 1} of ${steps.length}</span>
<h2 class="panel-title">${step.label}</h2>
<p class="panel-desc">${step.desc}</p>
</div>
<div class="panel-body" id="panel-body"></div>
<div class="panel-footer">
<div class="step-actions"><div>${backBtn}</div><div>${nextBtn}${finishBtn}</div></div>
</div>
</div>`;

        if (renderers[step.id]) {
            document.getElementById('panel-body').innerHTML = renderers[step.id]();
        }

        document.getElementById('btn-next')?.addEventListener('click', next);
        document.getElementById('btn-back')?.addEventListener('click', back);
        document.getElementById('btn-finish')?.addEventListener('click', finish);

        if (steps[currentIndex].id === 'database') {
            const syncDbFields = () => {
                wizardState.db.host = document.getElementById('db-host')?.value ?? '';
                wizardState.db.name = document.getElementById('db-name')?.value ?? '';
                wizardState.db.username = document.getElementById('db-username')?.value ?? '';
                wizardState.db.password = document.getElementById('db-password')?.value ?? '';
            };
            ['db-host', 'db-name', 'db-username', 'db-password'].forEach(id => {
                document.getElementById(id)?.addEventListener('input', () => {
                    syncDbFields();
                    wizardState.db.tested = false;
                    wizardState.db.error = null;
                    document.getElementById('btn-next') && (document.getElementById('btn-next').disabled = true);
                });
            });
            document.getElementById('btn-test-db')?.addEventListener('click', async () => {
                syncDbFields();
                const btn = document.getElementById('btn-test-db');
                btn.disabled = true;
                btn.textContent = 'Testing…';
                try {
                    const res = await fetch(`${window.__baseUrl}/index.php?action=test-db`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            host: wizardState.db.host,
                            name: wizardState.db.name,
                            username: wizardState.db.username,
                            password: wizardState.db.password,
                        }),
                    });
                    const data = await res.json();
                    wizardState.db.tested = true;
                    if (data.ok) {
                        wizardState.db.empty = data.empty;
                        wizardState.db.error = null;
                        wizardState.db.acknowledged = false;
                    } else {
                        wizardState.db.error = data.message || 'Connection failed.';
                        wizardState.db.empty = false;
                    }
                } catch {
                    wizardState.db.tested = true;
                    wizardState.db.error = 'Request failed. Check server connectivity.';
                }
                renderContent();
            });
            document.getElementById('db-ack')?.addEventListener('change', e => {
                wizardState.db.acknowledged = e.target.checked;
                const nextBtn = document.getElementById('btn-next');
                if (nextBtn) nextBtn.disabled = !canProceed();
            });
        }
    }

    function goTo(index) {
        currentIndex = index;
        renderNav();
        renderContent();
    }

    function next() {
        if (!canProceed()) return;
        completedSet.add(currentIndex);
        if (currentIndex < steps.length - 1) goTo(currentIndex + 1);
    }

    function back() {
        if (currentIndex > 0) goTo(currentIndex - 1);
    }

    function finish() {
        completedSet.add(currentIndex);
        renderNav();
    }

    function init() {
        renderNav();
        renderContent();
    }

    return { init, goTo, next, back };
})();

document.addEventListener('DOMContentLoaded', () => wizard.init());
